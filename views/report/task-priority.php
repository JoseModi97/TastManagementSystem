<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $taskPriorityProvider yii\data\ActiveDataProvider */

$this->title = 'Task Priority Report';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="report-task-priority">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $taskPriorityProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'label',
            'weight',
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
                        return '<span class="text-muted">No tasks with this priority</span>';
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
