<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Task $model */
/** @var app\models\Project $project */

$this->title = 'Create Task for Project: ' . $project->name;
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['project/index']];
$this->params['breadcrumbs'][] = ['label' => $project->name, 'url' => ['project/view', 'id' => $project->id]];
$this->params['breadcrumbs'][] = 'Create Task';
?>
<div class="task-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'project' => $project, // Pass project to form for context
    ]) ?>

</div>
