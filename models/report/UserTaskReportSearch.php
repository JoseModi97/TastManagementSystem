<?php

namespace app\models\report;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\User;
use app\models\Task;
use app\models\TaskStatus;
use app\models\TaskPriority;

class UserTaskReportSearch extends User
{
    public $task_title;
    public $task_status_id;
    public $task_priority_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        // Combine rules from User model (for username, email) and new task-related filters
        // User model already has rules for username, email.
        // We only need to add rules for the new attributes.
        return [
            [['id'], 'integer'], // From User model
            [['username', 'email'], 'safe'], // From User model
            [['task_title'], 'safe'],
            [['task_status_id', 'task_priority_id'], 'integer'],
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
        $query = User::find()->with('tasks'); // Eager load tasks

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // Grid filtering conditions for User attributes
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'username', $this->username])
              ->andFilterWhere(['like', 'email', $this->email]);

        // Grid filtering conditions for Task attributes
        // This requires joining with the tasks table
        if ($this->task_title || $this->task_status_id || $this->task_priority_id) {
            $query->joinWith(['tasks reportTasks' => function ($q) {
                $q->from(['reportTasks' => Task::tableName()]); // Alias the tasks table for this specific join
            }]); // Use an alias for tasks relation in this context

            if ($this->task_title) {
                $query->andWhere(['like', 'reportTasks.title', $this->task_title]);
            }
            if ($this->task_status_id) {
                $query->andWhere(['reportTasks.status_id' => $this->task_status_id]);
            }
            if ($this->task_priority_id) {
                $query->andWhere(['reportTasks.priority_id' => $this->task_priority_id]);
            }
            // Ensure that users without tasks that match the criteria are not shown,
            // or if they have other tasks, those tasks might not be filtered.
            // The current setup will filter users based on whether *any* of their tasks match.
            // If a user must have tasks matching ALL criteria, the query becomes more complex.
            // For now, this filters users who have at least one task matching the filter.
            $query->distinct(); // Important to avoid duplicate users if they have multiple matching tasks
        }


        return $dataProvider;
    }
}
