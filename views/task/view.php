<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Task $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['project/index']];
$this->params['breadcrumbs'][] = ['label' => $model->project->name, 'url' => ['project/view', 'id' => $model->project_id]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="task-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php // Add authorization check if needed, e.g., only project owner or assignee can edit task
        if ($model->project->created_by === Yii::$app->user->id || ($model->assigned_to === Yii::$app->user->id)) : ?>
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this task?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            'description:ntext',
            [
                'attribute' => 'project_id',
                'value' => $model->project ? Html::a(Html::encode($model->project->name), ['project/view', 'id' => $model->project_id]) : null,
                'format' => 'raw',
            ],
            [
                'attribute' => 'assigned_to',
                'value' => $model->assignedTo ? Html::encode($model->assignedTo->username) : 'Not assigned',
            ],
            [
                'attribute' => 'priority_id',
                'value' => $model->priority ? Html::encode($model->priority->label) : null,
            ],
            [
                'attribute' => 'status_id',
                'value' => $model->status ? Html::encode($model->status->label) : null,
            ],
            'due_date:date', // Using 'date' format, can be 'datetime' if time is important
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
