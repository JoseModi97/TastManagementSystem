<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Task;

/* @var $this yii\web\View */
/* @var $usersProvider yii\data\ActiveDataProvider */

$this->title = 'User Tasks Report';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="report-user-tasks">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $usersProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'username',
            'email:email',
            [
                'attribute' => 'tasks',
                'format' => 'raw',
                'label' => 'Assigned Tasks',
                'value' => function ($model) {
                    if (empty($model->tasks)) {
                        return '<span class="text-muted">No tasks assigned</span>';
                    }
                    $taskLinks = [];
                    foreach ($model->tasks as $task) {
                        $taskLinks[] = Html::a(Html::encode($task->title), ['task/view', 'id' => $task->id]);
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
