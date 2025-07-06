<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\User $user */
/** @var array $currentUserRolesMap (name => description of currently assigned roles) */
/** @var array $assignableRolesList (name => description of roles that can be added) */
/** @var yii\base\DynamicModel $roleAddModel */

$this->title = 'Manage Roles for User: ' . Html::encode($user->username);
$this->params['breadcrumbs'][] = ['label' => 'User Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($user->username), 'url' => '#'];
$this->params['breadcrumbs'][] = 'Manage Roles';
?>
<div class="user-management-assign-role">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <strong>User ID:</strong> <?= Html::encode($user->id) ?><br>
        <strong>Username:</strong> <?= Html::encode($user->username) ?><br>
        <strong>Email:</strong> <?= Html::encode($user->email) ?>
    </p>

    <hr>

    <h4>Currently Assigned Roles:</h4>
    <?php if (!empty($currentUserRolesMap)): ?>
        <ul class="list-group mb-3">
            <?php foreach ($currentUserRolesMap as $roleName => $roleDescription): ?>
                <li class="list-group-item">
                    <?= Html::encode($roleDescription) ?> (<?= Html::encode($roleName) ?>)
                    <?php
                    // Add a revoke button, but prevent admin from revoking own admin role
                    $canRevoke = true;
                    if ($roleName === 'admin' && $user->id === Yii::$app->user->id) {
                        $canRevoke = false;
                    }
                    // For now, we don't have a separate revoke action, this form only adds.
                    // If a revoke button is needed, it would call a different action.
                    // Example: if ($canRevoke) { echo Html::a('Revoke', ['revoke-role', 'userId' => $user->id, 'roleName' => $roleName], ['class' => 'btn btn-xs btn-danger float-end', 'data-method' => 'post']); }
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No roles currently assigned.</p>
    <?php endif; ?>

    <hr>

    <?php $form = ActiveForm::begin(); ?>

    <?php if (!empty($assignableRolesList)): ?>
        <h4>Assign Additional Roles:</h4>
        <?= $form->field($roleAddModel, 'roles_to_add[]')->checkboxList($assignableRolesList, [
            'item' => function ($index, $label, $name, $checked, $value) use ($roleAddModel) {
                // $label here is the role description, $value is the role name
                return Html::checkbox($name, $checked, [
                    'value' => $value,
                    'label' => '<label for="' . Html::getInputId($roleAddModel, 'roles_to_add[]') . '_' . $index . '">' . Html::encode($label) . ' (' . Html::encode($value) . ')</label>',
                    'id' => Html::getInputId($roleAddModel, 'roles_to_add[]') . '_' . $index,
                    'class' => 'form-check-input',
                ]);
            },
            'separator' => '<br>',
            'class' => 'form-check',
        ])->label(false) // Label is provided by <h4> ?>
         <div class="form-group mt-3">
            <?= Html::submitButton('Add Selected Roles', ['class' => 'btn btn-success']) ?>
        </div>
    <?php else: ?>
        <p>No additional roles available to assign.</p>
    <?php endif; ?>


    <div class="form-group mt-4">
        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
