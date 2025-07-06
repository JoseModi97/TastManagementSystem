<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Task;

/**
 * TaskSearch represents the model behind the search form of `app\models\Task`.
 */
class TaskSearch extends Task
{
    public $projectName; // For sorting/filtering by project name
    public $assignedToUsername; // For sorting/filtering by assigned user's name
    public $priorityLabel;
    public $statusLabel;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'project_id', 'assigned_to', 'priority_id', 'status_id', 'created_at', 'updated_at'], 'integer'],
            [['title', 'description', 'due_date', 'projectName', 'assignedToUsername', 'priorityLabel', 'statusLabel'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Task::find();

        // Important: join with related tables to enable sorting/filtering by related data
        $query->joinWith(['project', 'assignedTo', 'priority', 'status']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'id',
                    'title',
                    'description',
                    'due_date',
                    'created_at',
                    'projectName' => [
                        'asc' => ['project.name' => SORT_ASC],
                        'desc' => ['project.name' => SORT_DESC],
                        'label' => 'Project'
                    ],
                    'assignedToUsername' => [
                        'asc' => ['user.username' => SORT_ASC],
                        'desc' => ['user.username' => SORT_DESC],
                        'label' => 'Assigned To'
                    ],
                    'priorityLabel' => [
                        'asc' => ['task_priority.label' => SORT_ASC],
                        'desc' => ['task_priority.label' => SORT_DESC],
                        'label' => 'Priority'
                    ],
                    'statusLabel' => [
                        'asc' => ['task_status.label' => SORT_ASC],
                        'desc' => ['task_status.label' => SORT_DESC],
                        'label' => 'Status'
                    ],
                ],
                'defaultOrder' => [
                    'due_date' => SORT_ASC,
                    'title' => SORT_ASC,
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'task.id' => $this->id, // Use task.id to avoid ambiguity
            'task.project_id' => $this->project_id,
            'task.assigned_to' => $this->assigned_to,
            'task.priority_id' => $this->priority_id,
            'task.status_id' => $this->status_id,
            'task.created_at' => $this->created_at,
            'task.updated_at' => $this->updated_at,
        ]);

        if ($this->due_date) {
            $query->andFilterWhere(['like', 'task.due_date', explode(' ', $this->due_date)[0]]); // Compare only date part
        }

        $query->andFilterWhere(['like', 'task.title', $this->title])
            ->andFilterWhere(['like', 'task.description', $this->description])
            ->andFilterWhere(['like', 'project.name', $this->projectName])
            ->andFilterWhere(['like', 'user.username', $this->assignedToUsername])
            ->andFilterWhere(['like', 'task_priority.label', $this->priorityLabel])
            ->andFilterWhere(['like', 'task_status.label', $this->statusLabel]);


        return $dataProvider;
    }
}
