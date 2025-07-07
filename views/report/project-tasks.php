<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Task;

/* @var $this yii\web\View */
/* @var $projectsProvider yii\data\ActiveDataProvider */

$this->title = 'Project Tasks Report';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="report-project-tasks">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $projectsProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            [
                'attribute' => 'createdBy.username', // Assuming a 'createdBy' relation in Project model
                'label' => 'Created By',
            ],
            [
                'attribute' => 'tasks',
                'format' => 'raw',
                'label' => 'Tasks in Project',
                'value' => function ($model) {
                    if (empty($model->tasks)) {
                        return '<span class="text-muted">No tasks in this project</span>';
                    }
                    $taskLinks = [];
                    foreach ($model->tasks as $task) {
                        $taskLinks[] = Html::a(Html::encode($task->title), ['task/view', 'id' => $task->id])
                            . ' (' . Html::encode($task->status->label ?? 'N/A') . ')' // Assuming status relation in Task model
                            . ($task->assignedTo ? ' - Assigned to: ' . Html::encode($task->assignedTo->username) : ' - Unassigned'); // Assuming assignedTo relation
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
