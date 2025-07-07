<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm; // Will change to yii\widgets\ActiveForm if issues arise with BS5 version
use yii\helpers\Html;

$this->context->layout = 'login'; // Use the new login layout
$this->title = 'Login';
// No breadcrumbs for login page typically

// It's important to ensure that the assets from sb-admin-2 are correctly loaded.
// This is handled in the new views/layouts/login.php
?>
<div class="row">
    <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
    <div class="col-lg-6">
        <div class="p-5">
            <div class="text-center">
                <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
            </div>

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'options' => ['class' => 'user'],
                'fieldConfig' => [
                    'template' => "{input}\n{error}", // SB Admin 2 structure doesn't typically show labels above inputs here
                    'inputOptions' => ['class' => 'form-control form-control-user'],
                    'errorOptions' => ['class' => 'invalid-feedback text-danger small d-block'], // Make errors visible
                ],
            ]); ?>

            <?= $form->field($model, 'username', [
                'inputOptions' => [
                    'class' => 'form-control form-control-user',
                    'placeholder' => 'Enter Email Address...', // Assuming username is email
                    'autofocus' => true
                ]
            ])->label(false) ?>

            <?= $form->field($model, 'password', [
                'inputOptions' => [
                    'class' => 'form-control form-control-user',
                    'placeholder' => 'Password'
                ]
            ])->passwordInput()->label(false) ?>

            <?= $form->field($model, 'rememberMe', [
                'template' => "<div class=\"form-group\"><div class=\"custom-control custom-checkbox small\">{input}{label}\n{error}</div></div>",
                'labelOptions' => ['class' => 'custom-control-label'],
                'inputOptions' => ['class' => 'custom-control-input'],
            ])->checkbox() ?>

            <?= Html::submitButton('Login', ['class' => 'btn btn-primary btn-user btn-block', 'name' => 'login-button']) ?>

            <hr>
            <?php ActiveForm::end(); ?>
            <hr>
            <div class="text-center">
                <?= Html::a('Forgot Password?', ['site/request-password-reset'], ['class' => 'small']) ?>
            </div>
            <div class="text-center">
                <?= Html::a('Create an Account!', ['site/signup'], ['class' => 'small']) ?>
            </div>
        </div>
    </div>
</div>
