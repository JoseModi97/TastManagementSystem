<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */ // Consider yii\widgets\ActiveForm if BS5 conflicts
/** @var app\models\SignupForm $model */

use yii\bootstrap5\ActiveForm; // Or yii\widgets\ActiveForm
use yii\helpers\Html;

$this->context->layout = 'login'; // Reusing login layout
$this->title = 'Create an Account';
// No breadcrumbs for this layout typically
?>
<div class="row">
    <div class="col-lg-5 d-none d-lg-block bg-register-image"></div>
    <div class="col-lg-7">
        <div class="p-5">
            <div class="text-center">
                <h1 class="h4 text-gray-900 mb-4"><?= Html::encode($this->title) ?></h1>
            </div>

            <?php $form = ActiveForm::begin([
                'id' => 'form-signup',
                'options' => ['class' => 'user'],
                'fieldConfig' => [
                    'template' => "{input}\n{error}", // No labels above inputs typically
                    'inputOptions' => ['class' => 'form-control form-control-user'],
                    'errorOptions' => ['class' => 'invalid-feedback text-danger small d-block'],
                ],
            ]); ?>

            <?= $form->field($model, 'username', [
                'inputOptions' => [
                    'class' => 'form-control form-control-user',
                    'placeholder' => 'Username',
                    'autofocus' => true,
                ]
            ])->label(false) ?>

            <?= $form->field($model, 'email', [
                'inputOptions' => [
                    'class' => 'form-control form-control-user',
                    'placeholder' => 'Email Address',
                ]
            ])->textInput(['type' => 'email'])->label(false) ?>

            <div class="form-group row">
                <div class="col-sm-6 mb-3 mb-sm-0">
                    <?= $form->field($model, 'password', [
                        'inputOptions' => [
                            'class' => 'form-control form-control-user',
                            'placeholder' => 'Password'
                        ]
                    ])->passwordInput()->label(false) ?>
                </div>
                <div class="col-sm-6">
                    <?= $form->field($model, 'password_repeat', [
                        'inputOptions' => [
                            'class' => 'form-control form-control-user',
                            'placeholder' => 'Repeat Password'
                        ]
                    ])->passwordInput()->label(false) ?>
                </div>
            </div>

            <?= Html::submitButton('Register Account', ['class' => 'btn btn-primary btn-user btn-block', 'name' => 'signup-button']) ?>

            <hr>
            <?php ActiveForm::end(); ?>
            <hr>
            <div class="text-center">
                <?= Html::a('Forgot Password?', ['site/request-password-reset'], ['class' => 'small']) ?>
            </div>
            <div class="text-center">
                <?= Html::a('Already have an account? Login!', ['site/login'], ['class' => 'small']) ?>
            </div>
        </div>
    </div>
</div>
