<?php

/** @var yii\web\View $this */
use yii\helpers\Html;

$this->title = 'Task Management System';
?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent mt-5 mb-5">
        <h1 class="display-4">Welcome to the Task Management System!</h1>

        <p class="lead">Your efficient solution for managing tasks, to-do lists, and project timelines.</p>

        <?php if (Yii::$app->user->isGuest): ?>
            <p>
                <?= Html::a('Login', ['site/login'], ['class' => 'btn btn-lg btn-primary']) ?>
                <?= Html::a('Signup', ['site/signup'], ['class' => 'btn btn-lg btn-success']) ?>
            </p>
        <?php else: ?>
            <p>
                <?= Html::a('Go to Dashboard', ['site/index'], ['class' => 'btn btn-lg btn-info']) ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-4 mb-3">
                <h2>Organize Projects</h2>

                <p>Create and manage your projects with ease. Break down complex work into manageable pieces and keep track of overall progress.</p>

                <p><?= Html::a('Learn More &raquo;', ['site/about'], ['class' => 'btn btn-outline-secondary']) ?></p>
            </div>
            <div class="col-lg-4 mb-3">
                <h2>Track Tasks</h2>

                <p>Assign tasks, set priorities, and monitor statuses. Never miss a deadline with clear task organization and due date tracking.</p>

                 <p><?= Html::a('Learn More &raquo;', ['site/about'], ['class' => 'btn btn-outline-secondary']) ?></p>
            </div>
            <div class="col-lg-4">
                <h2>Collaborate</h2>

                <p>Designed for individuals and teams. Assign tasks to users and ensure everyone is on the same page. (Full collaboration features coming soon!)</p>

                 <p><?= Html::a('Learn More &raquo;', ['site/about'], ['class' => 'btn btn-outline-secondary']) ?></p>
            </div>
        </div>

    </div>
</div>
