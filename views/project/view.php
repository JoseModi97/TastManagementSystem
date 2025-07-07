<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Project $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="project-view">

    <?php /* <h1><?= Html::encode($this->title) ?></h1> Modal title is set by controller */ ?>

    <?php /* Action buttons are typically in the modal footer or main grid for ajaxcrud
    <p>
        <?php if ($model->created_by === Yii::$app->user->id || Yii::$app->user->can('updateProject', ['project' => $model])): ?>
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary', 'role'=>'modal-remote']) ?>
        <?php endif; ?>
        <?php if ($model->created_by === Yii::$app->user->id || Yii::$app->user->can('deleteProject', ['project' => $model])): ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'role'=>'modal-remote', // For ajax deletion, if desired from view page directly
                'data-confirm'=>false, 'data-method'=>false,
                'data-request-method'=>'post',
                'data-confirm-title'=>'Are you sure?',
                'data-confirm-message'=>'Are you sure want to delete this item'
            ]) ?>
        <?php endif; ?>
    </p>
    */ ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'description:ntext',
            [
                'attribute' => 'created_by',
                'value' => $model->createdBy ? $model->createdBy->username : null,
            ],
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

    <hr>
    <h2>Tasks in this Project</h2>
    <p>
        <?php if ($model->created_by === Yii::$app->user->id): ?>
            <?= Html::a('Create New Task for this Project', ['task/create', 'project_id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?php endif; ?>
    </p>

    <?php
    $taskDataProvider = new \yii\data\ActiveDataProvider([
        'query' => $model->getTasks()->with(['assignedTo', 'priority', 'status']), // Eager load related data
        'pagination' => [
            'pageSize' => 10,
        ],
        'sort' => [
            'defaultOrder' => [
                'status_id' => SORT_ASC,
                'priority_id' => SORT_DESC,
                'due_date' => SORT_ASC,
            ]
        ]
    ]);

    echo \yii\grid\GridView::widget([
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'dataProvider' => $taskDataProvider,
        'summary' => '', // Optionally hide the summary
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'title',
            [
                'attribute' => 'assigned_to',
                'value' => function($task) { return $task->assignedTo ? $task->assignedTo->username : 'N/A'; }
            ],
            [
                'attribute' => 'priority_id',
                'value' => function($task) { return $task->priority ? $task->priority->label : 'N/A'; }
            ],
            [
                'attribute' => 'status_id',
                'value' => function($task) { return $task->status ? $task->status->label : 'N/A'; }
            ],
            'due_date:date',
            [
                'class' => 'yii\grid\ActionColumn',
                'controller' => 'task', // Point actions to TaskController
                'template' => '{view} {update} {delete}',
                'visibleButtons' => [
                    // Only show update/delete if current user created the project
                    'update' => function ($model, $key, $index) {
                        return $model->project->created_by === Yii::$app->user->id;
                    },
                    'delete' => function ($model, $key, $index) {
                        return $model->project->created_by === Yii::$app->user->id;
                    },
                ]
            ],
        ],
    ]);
    ?>

</div>
