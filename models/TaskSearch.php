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
        // Aliasing to avoid conflicts if 'project' is joined again in controller
        $query->joinWith(['project p', 'assignedTo u', 'priority pri', 'status s']);


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
                        'asc' => ['p.name' => SORT_ASC], // Use alias
                        'desc' => ['p.name' => SORT_DESC], // Use alias
                        'label' => 'Project'
                    ],
                    'assignedToUsername' => [
                        'asc' => ['u.username' => SORT_ASC], // Use alias
                        'desc' => ['u.username' => SORT_DESC], // Use alias
                        'label' => 'Assigned To'
                    ],
                    'priorityLabel' => [
                        'asc' => ['pri.label' => SORT_ASC], // Use alias
                        'desc' => ['pri.label' => SORT_DESC], // Use alias
                        'label' => 'Priority'
                    ],
                    'statusLabel' => [
                        'asc' => ['s.label' => SORT_ASC], // Use alias
                        'desc' => ['s.label' => SORT_DESC], // Use alias
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
            ->andFilterWhere(['like', 'p.name', $this->projectName]) // Use alias
            ->andFilterWhere(['like', 'u.username', $this->assignedToUsername]) // Use alias
            ->andFilterWhere(['like', 'pri.label', $this->priorityLabel]) // Use alias
            ->andFilterWhere(['like', 's.label', $this->statusLabel]); // Use alias


        return $dataProvider;
    }
}
