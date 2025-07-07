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
            'options' => ['class' => 'form-inline'],
        ]); ?>

        <?= $form->field($taskFilterModel, 'task_title', [
            'template' => '{input}',
            'options' => ['class' => 'mr-2 mb-2']
        ])->textInput(['placeholder' => 'Filter Task Title', 'class' => 'form-control form-control-sm']) ?>

        <?= $form->field($taskFilterModel, 'task_status_id', [
            'template' => '{input}',
            'options' => ['class' => 'mr-2 mb-2']
        ])->dropDownList(ArrayHelper::map(TaskStatus::find()->all(), 'id', 'label'), ['prompt' => 'Any Status', 'class' => 'form-control form-control-sm']) ?>

        <?= $form->field($taskFilterModel, 'task_priority_id', [
            'template' => '{input}',
            'options' => ['class' => 'mr-2 mb-2']
        ])->dropDownList(ArrayHelper::map(TaskPriority::find()->orderBy('weight')->all(), 'id', 'label'), ['prompt' => 'Any Priority', 'class' => 'form-control form-control-sm']) ?>

        <?= $form->field($taskFilterModel, 'task_assigned_to', [
            'template' => '{input}',
            'options' => ['class' => 'mr-2 mb-2']
        ])->dropDownList(ArrayHelper::map(User::find()->orderBy('username')->all(), 'id', 'username'), ['prompt' => 'Any User', 'class' => 'form-control form-control-sm']) ?>

        <div class="form-group mr-2 mb-2">
            <?= Html::submitButton('Filter Tasks', ['class' => 'btn btn-primary btn-sm']) ?>
            <?= Html::a('Reset Filters', ['report/project-tasks'], ['class' => 'btn btn-outline-secondary btn-sm ml-1']) ?>
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
                    $filteredTasks = $taskFilterModel->filterTasks($projectModel->tasks);
                    if (empty($filteredTasks)) {
                        if (empty($taskFilterModel->task_title) && empty($taskFilterModel->task_status_id) && empty($taskFilterModel->task_priority_id) && empty($taskFilterModel->task_assigned_to)) {
                            return '<span class="text-muted">No tasks in this project</span>';
                        }
                        return '<span class="text-muted">No tasks match current filters</span>';
                    }
                    $taskLinks = [];
                    foreach ($filteredTasks as $task) {
                        $taskLinks[] = Html::a(Html::encode($task->title), ['task/view', 'id' => $task->id])
                            . ' (' . Html::encode($task->status->label ?? 'N/A') . ')'
                            . ' (' . Html::encode($task->priority->label ?? 'N/A') . ')'
                            . ($task->assignedTo ? ' - Assigned: ' . Html::encode($task->assignedTo->username) : ' - Unassigned');
                    }
                    return implode('<br>', $taskLinks);
                },
            ],
            [
                'attribute' => 'created_at',
                'format' => ['datetime', 'php:Y-m-d H:i:s'],
            ],
        ],
    ]); ?>

</div>
                'format' => ['datetime', 'php:Y-m-d H:i:s'],
            ],
        ],
    ]); ?>

</div>
