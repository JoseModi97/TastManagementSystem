<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use app\models\TaskHistory;

/* @var $this yii\web\View */
/* @var $taskHistoryProvider yii\data\ActiveDataProvider */

$this->title = 'Task History Report';
$this->params['breadcrumbs'][] = $this->title;

$user = Yii::$app->user->identity; // Get current user identity
?>
<div class="report-task-history">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $taskHistoryProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            [
                'attribute' => 'task_id',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a(Html::encode($model->task->title ?? 'N/A'), ['task/view', 'id' => $model->task_id]);
                },
            ],
            [
                'attribute' => 'user_id',
                'label' => 'Changed By',
                'value' => function ($model) {
                    // If old_value is null, it's likely a creation event or initial setting
                    if ($model->old_value === null && $model->user_id === null) {
                        return '<span class="text-muted">System (Initial)</span>';
                    }
                    return $model->user ? Html::encode($model->user->username) : '<span class="text-muted">System/Unknown</span>';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'changed_at',
                'format' => ['datetime', 'php:Y-m-d H:i:s'],
            ],
            'attribute',
            [
                'attribute' => 'old_value',
                'value' => function($model) {
                    return $model->old_value_label ?: $model->old_value;
                }
            ],
            [
                'attribute' => 'new_value',
                'value' => function($model) {
                    return $model->new_value_label ?: $model->new_value;
                }
            ],
        ],
    ]); ?>

</div>
