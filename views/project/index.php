<?php

use app\models\Project;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\ProjectSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Projects';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Project', ['create'], [
            'class' => 'btn btn-success ajax-modal-link',
            'data-modal-title' => 'Create New Project'
        ]) ?>
    </p>

    <?php Pjax::begin(['id' => 'project-grid-pjax']); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'id' => 'project-grid', // Added an ID for the GridView widget itself if needed
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            'name',
            'description:ntext',
            [
                'attribute' => 'created_by',
                'value' => function ($model) {
                    return $model->createdBy ? $model->createdBy->username : null;
                },
                // Optional: Filter by username if you have a join in ProjectSearch
                // 'filter' => Html::activeTextInput($searchModel, 'createdByUsername'),
            ],
            'created_at:datetime',
            //'updated_at:datetime',
            [
                'class' => ActionColumn::class,
                'urlCreator' => function ($action, Project $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                },
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<span class="fas fa-eye"></span>', $url, [
                            'title' => Yii::t('yii', 'View'),
                            'class' => 'ajax-modal-link',
                            'data-modal-title' => 'View Project: ' . $model->name,
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        // Only show update if user has permission (example, can be more granular)
                        if (Yii::$app->user->can('updateProject', ['project' => $model]) || Yii::$app->user->can('updateOwnProject', ['project' => $model])) {
                            return Html::a('<span class="fas fa-pencil-alt"></span>', $url, [
                                'title' => Yii::t('yii', 'Update'),
                                'class' => 'ajax-modal-link',
                                'data-modal-title' => 'Update Project: ' . $model->name,
                            ]);
                        }
                        return '';
                    },
                    'delete' => function ($url, $model, $key) {
                         // Only show delete if user has permission
                        if (Yii::$app->user->can('deleteProject', ['project' => $model]) || Yii::$app->user->can('deleteOwnProject', ['project' => $model])) {
                            return Html::a('<span class="fas fa-trash"></span>', $url, [
                                'title' => Yii::t('yii', 'Delete'),
                                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                'data-method' => 'post',
                                // 'class' => 'ajax-delete-link', // If we want custom JS confirmation/AJAX delete
                                // 'data-pjax-container' => 'project-grid-pjax', // For AJAX delete
                            ]);
                        }
                        return '';
                    }
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
