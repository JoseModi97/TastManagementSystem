<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Task;
use app\models\TaskStatus;
use app\models\TaskPriority;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel app\models\report\UserTaskReportSearch */

$this->title = 'User Tasks Report';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="report-user-tasks">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Export to CSV', array_merge(['report/export-user-tasks-csv'], Yii::$app->request->get()), ['class' => 'btn btn-success', 'data-pjax' => 0]) ?>
    </p>

    <?php // echo $this->render('_search-user-tasks', ['model' => $searchModel]); // Optional: if you want a separate search form ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // User fields with filtering
            [
                'attribute' => 'id',
                'options' => ['style' => 'width: 80px;'],
            ],
            [
                'attribute' => 'username',
                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'Filter...'],
            ],
            [
                'attribute' => 'email',
                'format' => 'email',
                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'Filter...'],
            ],

            // Task related filters - these will filter USERS based on their tasks' properties
            // The display of tasks itself might need adjustment if filters are applied
            // This column is primarily for display of tasks, not for filtering via its header.
            // Filtering for task properties is done via separate filter attributes in UserTaskReportSearch
            [
                'label' => 'Task Title Filter', // Custom label for clarity
                'attribute' => 'task_title', // Corresponds to UserTaskReportSearch public property
                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'placeholder' => 'Task Title...'],
                'value' => function() { return null; }, // No direct value needed, filter only
                'headerOptions' => ['title' => 'Filter users by task title'],
            ],
            [
                'label' => 'Task Status Filter',
                'attribute' => 'task_status_id',
                'filter' => ArrayHelper::map(TaskStatus::find()->asArray()->all(), 'id', 'label'),
                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'prompt' => 'Any Status'],
                'value' => function() { return null; }, // No direct value needed, filter only
                'headerOptions' => ['title' => 'Filter users by task status'],
            ],
            [
                'label' => 'Task Priority Filter',
                'attribute' => 'task_priority_id',
                'filter' => ArrayHelper::map(TaskPriority::find()->orderBy('weight')->asArray()->all(), 'id', 'label'),
                'filterInputOptions' => ['class' => 'form-control form-control-sm', 'prompt' => 'Any Priority'],
                'value' => function() { return null; }, // No direct value needed, filter only
                'headerOptions' => ['title' => 'Filter users by task priority'],
            ],

            // Display of Assigned Tasks (remains largely the same)
            // Note: If task filters are applied, this list of tasks might not reflect those filters directly
            // as the primary query is on Users. The UserTaskReportSearch->search() method
            // filters USERS who have tasks matching the criteria.
            [
                'attribute' => 'tasks',
                'format' => 'raw',
                'label' => 'Assigned Tasks (matching filters, if any)',
                'value' => function ($userModel) use ($searchModel) {
                    // If task filters are active, we might want to show only matching tasks
                    // or indicate that the list is filtered.
                    // For simplicity, we'll show all tasks of the filtered users.
                    // A more advanced version could re-filter $userModel->tasks here.
                    if (empty($userModel->tasks)) {
                        return '<span class="text-muted">No tasks assigned</span>';
                    }
                    $taskLinks = [];
                    foreach ($userModel->tasks as $task) {
                        // Optionally, re-check if this task matches current task filters
                        $matchesFilter = true;
                        if (!empty($searchModel->task_title) && stripos($task->title, $searchModel->task_title) === false) {
                            $matchesFilter = false;
                        }
                        if (!empty($searchModel->task_status_id) && $task->status_id != $searchModel->task_status_id) {
                            $matchesFilter = false;
                        }
                        if (!empty($searchModel->task_priority_id) && $task->priority_id != $searchModel->task_priority_id) {
                            $matchesFilter = false;
                        }

                        if ($matchesFilter || (!$searchModel->task_title && !$searchModel->task_status_id && !$searchModel->task_priority_id)) {
                             $taskLinks[] = Html::a(Html::encode($task->title), ['task/view', 'id' => $task->id])
                                . ' (' . Html::encode($task->status->label ?? 'N/A')
                                . ' - ' . Html::encode($task->priority->label ?? 'N/A') . ')';
                        }
                    }
                    if (empty($taskLinks) && (!empty($searchModel->task_title) || !empty($searchModel->task_status_id) || !empty($searchModel->task_priority_id)) ) {
                        return '<span class="text-muted">No tasks match current filters for this user</span>';
                    } elseif (empty($taskLinks)) {
                         return '<span class="text-muted">No tasks assigned</span>';
                    }
                    return implode('<br>', $taskLinks);
                },
                'enableSorting' => false, // Task list itself is not sortable this way
            ],
            [
                'attribute' => 'created_at',
                'filter' => false, // Disable filter for created_at for now
                'format' => ['datetime', 'php:Y-m-d H:i:s'],
            ],
        ],
    ]); ?>

</div>
