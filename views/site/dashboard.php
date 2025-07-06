<?php

/** @var yii\web\View $this */
/** @var int $projectCount */
/** @var int $tasksAssignedCount */
/** @var int $activeTasksInOwnedProjectsCount */
/** @var app\models\Task[] $recentlyDueTasks */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-dashboard">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Welcome back, <strong><?= Html::encode(Yii::$app->user->identity->username) ?></strong>!</p>

    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">My Projects</h5>
                    <p class="card-text display-4"><?= Html::encode($projectCount) ?></p>
                    <?= Html::a('View My Projects &raquo;', ['project/index'], ['class' => 'btn btn-outline-primary']) ?>
                    <?= Html::a('Create New Project &raquo;', ['project/create'], ['class' => 'btn btn-success mt-2']) ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Tasks Assigned to Me</h5>
                    <p class="card-text display-4"><?= Html::encode($tasksAssignedCount) ?></p>
                     <?= Html::a('View My Assigned Tasks &raquo;', ['task/index', 'TaskSearch[assigned_to]' => Yii::$app->user->id], ['class' => 'btn btn-outline-primary']) ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Active Tasks in My Projects</h5>
                    <p class="card-text display-4"><?= Html::encode($activeTasksInOwnedProjectsCount) ?></p>
                     <?php // Link could go to project index or a pre-filtered task list for user's projects' active tasks ?>
                     <?= Html::a('View All Tasks &raquo;', ['task/index'], ['class' => 'btn btn-outline-primary']) ?>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <h3>Tasks Due Soon or Overdue (Assigned to You)</h3>
    <?php if (!empty($recentlyDueTasks)): ?>
        <ul class="list-group">
            <?php foreach ($recentlyDueTasks as $task): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <?= Html::a(Html::encode($task->title), ['task/view', 'id' => $task->id]) ?>
                        <small class="d-block text-muted">
                            Project: <?= Html::a(Html::encode($task->project->name), ['project/view', 'id' => $task->project_id]) ?>
                            | Due: <?= Yii::$app->formatter->asDate($task->due_date) ?>
                            <?php if ($task->status): ?>
                                | Status: <span class="badge bg-info"><?= Html::encode($task->status->label) ?></span>
                            <?php endif; ?>
                        </small>
                    </div>
                    <?php
                        $dueDate = new DateTime($task->due_date);
                        $now = new DateTime();
                        $isOverdue = $dueDate < $now && $task->status->label !== 'Done'; // Check if not done
                    ?>
                    <?php if ($isOverdue): ?>
                        <span class="badge bg-danger rounded-pill">Overdue</span>
                    <?php elseif ($task->status->label === 'Done'): ?>
                        <span class="badge bg-success rounded-pill">Done</span>
                    <?php else: ?>
                         <span class="badge bg-warning rounded-pill">Due Soon</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No tasks assigned to you are due soon or overdue.</p>
    <?php endif; ?>

</div>
