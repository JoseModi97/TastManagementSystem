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
    public function filterTasks(array $tasks)
    {
        if (empty($this->task_title) && empty($this->task_status_id) && empty($this->task_priority_id) && empty($this->task_assigned_to)) {
            return $tasks;
        }

        return array_filter($tasks, function ($task) {
            /** @var \app\models\Task $task */
            if (!empty($this->task_title) && stripos($task->title, $this->task_title) === false) {
                return false;
            }
            if (!empty($this->task_status_id) && $task->status_id != $this->task_status_id) {
                return false;
            }
            if (!empty($this->task_priority_id) && $task->priority_id != $this->task_priority_id) {
                return false;
            }
            if (!empty($this->task_assigned_to) && $task->assigned_to != $this->task_assigned_to) {
                return false;
            }
            return true;
        });
    }
}
