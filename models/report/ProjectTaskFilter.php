<?php

namespace app\models\report;

use yii\base\Model;
use app\models\TaskStatus;
use app\models\TaskPriority;
use app\models\User;

class ProjectTaskFilter extends Model
{
    public $task_title;
    public $task_status_id;
    public $task_priority_id;
    public $task_assigned_to;

    public function rules()
    {
        return [
            [['task_title'], 'safe'],
            [['task_status_id', 'task_priority_id', 'task_assigned_to'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'task_title' => 'Filter Task Title',
            'task_status_id' => 'Filter Task Status',
            'task_priority_id' => 'Filter Task Priority',
            'task_assigned_to' => 'Filter Assigned To',
        ];
    }

    /**
     * Filters an array of Task objects.
     * @param \app\models\Task[] $tasks
     * @return \app\models\Task[]
     */
    /**
     * Applies the filter conditions to a given ActiveQuery instance.
     * @param \yii\db\ActiveQuery $query The ActiveQuery to be filtered.
     */
    public function applyTaskFiltersToQuery(\yii\db\ActiveQuery $query)
    {
        $searchTitle = !empty($this->task_title) ? trim($this->task_title) : null;

        if (!empty($searchTitle)) {
            $query->andFilterWhere(['like', 'task.title', $searchTitle]);
        }
        if (!empty($this->task_status_id)) {
            $query->andFilterWhere(['task.status_id' => $this->task_status_id]);
        }
        if (!empty($this->task_priority_id)) {
            $query->andFilterWhere(['task.priority_id' => $this->task_priority_id]);
        }
        if (!empty($this->task_assigned_to)) {
            $query->andFilterWhere(['task.assigned_to' => $this->task_assigned_to]);
        }
        // If you want to explicitly filter for unassigned tasks when a special value is passed
        // else if ($this->task_assigned_to === 'unassigned') { // Assuming 'unassigned' is a value from dropdown
        //     $query->andWhere(['task.assigned_to' => null]);
        // }
    }
}
