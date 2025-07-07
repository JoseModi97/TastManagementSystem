<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Task;
use app\models\TaskStatus;
use app\models\TaskPriority;
use app\models\User;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $projectsProvider yii\data\ActiveDataProvider */
/* @var $taskFilterModel app\models\report\ProjectTaskFilter */

$this->title = 'Project Tasks Report';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="report-project-tasks">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Export to CSV', array_merge(['report/export-project-tasks-csv'], Yii::$app->request->get()), ['class' => 'btn btn-success mb-3', 'data-pjax' => 0]) ?>
    </p>

    <div class="task-filters mb-3 p-3 border rounded">
        <?php $form = ActiveForm::begin([
            'action' => ['report/project-tasks'],
            'method' => 'get',
            // 'options' => ['class' => 'form-inline'], // Removed form-inline
        ]); ?>

        <div class="row">
            <div class="col-md-3 mb-2">
                <?= $form->field($taskFilterModel, 'task_title', [
                    'template' => '{input}', // Consider adding {label} if appropriate, or ensure placeholder is clear
                    'inputOptions' => ['placeholder' => 'Filter Task Title', 'class' => 'form-control form-control-sm']
                ])->textInput() ?>
            </div>
            <div class="col-md-2 mb-2">
                <?= $form->field($taskFilterModel, 'task_status_id', [
                    'template' => '{input}',
                    'inputOptions' => ['class' => 'form-control form-control-sm']
                ])->dropDownList(ArrayHelper::map(TaskStatus::find()->all(), 'id', 'label'), ['prompt' => 'Any Status']) ?>
            </div>
            <div class="col-md-2 mb-2">
                <?= $form->field($taskFilterModel, 'task_priority_id', [
                    'template' => '{input}',
                    'inputOptions' => ['class' => 'form-control form-control-sm']
                ])->dropDownList(ArrayHelper::map(TaskPriority::find()->orderBy('weight')->all(), 'id', 'label'), ['prompt' => 'Any Priority']) ?>
            </div>
            <div class="col-md-2 mb-2">
                <?= $form->field($taskFilterModel, 'task_assigned_to', [
                    'template' => '{input}',
                    'inputOptions' => ['class' => 'form-control form-control-sm']
                ])->dropDownList(ArrayHelper::map(User::find()->orderBy('username')->all(), 'id', 'username'), ['prompt' => 'Any User']) ?>
            </div>
            <div class="col-md-3 mb-2">
                <div class="form-group"> <?php /* Encapsulate buttons for proper alignment and spacing */?>
                    <?= Html::submitButton('Filter Tasks', ['class' => 'btn btn-primary btn-sm']) ?>
                    <?= Html::a('Reset Filters', ['report/project-tasks'], ['class' => 'btn btn-outline-secondary btn-sm ml-2']) /* Increased margin for reset button */?>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>


    <?= GridView::widget([
        'dataProvider' => $projectsProvider,
        // 'filterModel' => $projectSearchModel, // If you had a search model for projects themselves
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // Project Info
            [
                'attribute' => 'id',
                'options' => ['style' => 'width: 70px;'],
            ],
            'name',
            [
                'attribute' => 'createdBy.username',
                'label' => 'Created By',
            ],

            // Filtered Tasks display
            [
                'attribute' => 'tasks',
                'format' => 'raw',
                'label' => 'Tasks in Project (filtered)',
                'value' => function ($projectModel) use ($taskFilterModel) {
                    // Create a query for tasks related to the current project
                    $taskQuery = $projectModel->getTasks()
                                            ->with(['status', 'priority', 'assignedTo']); // Eager load relations for display

                    // Apply filters from $taskFilterModel to this query
                    $taskFilterModel->applyTaskFiltersToQuery($taskQuery);

                    // Create a new ActiveDataProvider for these tasks
                    $tasksDataProvider = new \yii\data\ActiveDataProvider([
                        'query' => $taskQuery,
                        'pagination' => false, // Display all matching tasks for this project
                        'sort' => [ // Optional: default sort for tasks within each project
                            'defaultOrder' => [
                                'priority_id' => SORT_ASC, // Example: High priority first
                                'title' => SORT_ASC
                            ]
                        ]
                    ]);

                    $filteredTasks = $tasksDataProvider->getModels();

                    if (empty($filteredTasks)) {
                        // Check if any filters were actually active
                        $filtersApplied = !empty(trim($taskFilterModel->task_title)) ||
                                          !empty($taskFilterModel->task_status_id) ||
                                          !empty($taskFilterModel->task_priority_id) ||
                                          !empty($taskFilterModel->task_assigned_to);

                        if (!$filtersApplied && $projectModel->getTasks()->count() == 0) { // Original check: project has no tasks at all
                             return '<span class="text-muted">No tasks in this project</span>';
                        } elseif ($filtersApplied) {
                            return '<span class="text-muted">No tasks match current filters</span>';
                        } else {
                            // No filters applied, but $filteredTasks is empty.
                            // This implies the project has tasks, but they were somehow filtered out by an empty filter state.
                            // This case should ideally not be reached if logic is correct.
                            // Or, it means project has no tasks, and no filters were applied.
                             return '<span class="text-muted">No tasks in this project</span>';
                        }
                    }

                    $taskLinks = [];
                    foreach ($filteredTasks as $task) {
                        /** @var \app\models\Task $task */
                        $taskLinks[] = Html::a(Html::encode($task->title), ['task/view', 'id' => $task->id])
                            . ' (' . Html::encode($task->status->label ?? 'N/A') . ')'
                            . ' (' . Html::encode($task->priority->label ?? 'N/A') . ')'
                            . ($task->assignedTo ? ' - Assigned: ' . Html::encode($task->assignedTo->username) : ' - Unassigned');
                    }
                    return '<div>' . implode('</div><div style="margin-top: 0.25rem;">', $taskLinks) . '</div>';
                },
            ],
            [
                'attribute' => 'created_at',
                'format' => ['datetime', 'php:Y-m-d H:i:s'],
            ],
        ],
    ]); ?>

</div>