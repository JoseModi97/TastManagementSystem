<?php

use app\models\Task;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\TaskStatus; // For dropdown filter
use app\models\TaskPriority; // For dropdown filter
use app\models\User; // For dropdown filter
use yii\helpers\ArrayHelper; // For dropdown filter

/** @var yii\web\View $this */
/** @var app\models\TaskSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\Project $project (optional, if viewing tasks for a specific project) */


if ($project) {
    $this->title = 'Tasks for Project: ' . Html::encode($project->name);
    $this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['project/index']];
    $this->params['breadcrumbs'][] = ['label' => Html::encode($project->name), 'url' => ['project/view', 'id' => $project->id]];
    $this->params['breadcrumbs'][] = 'Tasks';
} else {
    $this->title = 'All Tasks'; // Or a more appropriate title for a global task list
    $this->params['breadcrumbs'][] = $this->title;
}
?>
<div class="task-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($project): ?>
    <p>
        <?= Html::a('Create Task for this Project', ['create', 'project_id' => $project->id], ['class' => 'btn btn-success']) ?>
    </p>
    <?php endif; ?>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); // Full search form can be added if needed ?>

    <?= GridView::widget([
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            'title',
            [
                'attribute' => 'projectName', // Virtual attribute from TaskSearch
                'value' => 'project.name',
                'label' => 'Project',
                'visible' => !$project, // Only show project column if not already in a project specific view
                'filter' => !$project ? Html::activeTextInput($searchModel, 'projectName', ['class' => 'form-control']) : false,
            ],
            'description:ntext',
            [
                'attribute' => 'assignedToUsername', // Virtual attribute from TaskSearch
                'value' => 'assignedTo.username',
                'label' => 'Assigned To',
                'filter' => ArrayHelper::map(User::find()->asArray()->all(), 'username', 'username'),
            ],
            [
                'attribute' => 'priorityLabel', // Virtual attribute from TaskSearch
                'value' => 'priority.label',
                'label' => 'Priority',
                'filter' => TaskPriority::getPriorityList(),
            ],
            [
                'attribute' => 'statusLabel', // Virtual attribute from TaskSearch
                'value' => 'status.label',
                'label' => 'Status',
                'filter' => TaskStatus::getStatusList(),
            ],
            'due_date:date',
            // 'created_at:datetime',
            // 'updated_at:datetime',
            [
                'class' => ActionColumn::class,
                'urlCreator' => function ($action, Task $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
