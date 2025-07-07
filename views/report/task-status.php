<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $taskStatusProvider yii\data\ActiveDataProvider */

$this->title = 'Task Status Report';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="report-task-status">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $taskStatusProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'label',
            [
                'label' => 'Number of Tasks',
                'value' => function ($model) {
                    return count($model->tasks);
                },
            ],
            [
                'label' => 'Tasks',
                'format' => 'raw',
                'value' => function ($model) {
                    if (empty($model->tasks)) {
                        return '<span class="text-muted">No tasks with this status</span>';
                    }
                    $taskLinks = [];
                    foreach ($model->tasks as $task) {
                        $taskLinks[] = Html::a(Html::encode($task->title), ['task/view', 'id' => $task->id]);
                    }
                    return implode('<br>', $taskLinks);
                },
            ],
        ],
    ]); ?>

</div>
