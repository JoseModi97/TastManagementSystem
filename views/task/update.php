<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Task $model */
/** @var app\models\Project $project (passed from controller) */


$this->title = 'Update Task: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['project/index']];
$this->params['breadcrumbs'][] = ['label' => $model->project->name, 'url' => ['project/view', 'id' => $model->project_id]];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="task-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'project' => $project ?? $model->project, // Pass project to form for context
    ]) ?>

</div>
