<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\User $user */
/** @var array $allRolesList (name => description) */
/** @var yii\base\DynamicModel $roleAssignmentModel */

$this->title = 'Assign Roles to User: ' . Html::encode($user->username);
$this->params['breadcrumbs'][] = ['label' => 'User Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($user->username), 'url' => '#']; // Could link to a user view page if one exists
$this->params['breadcrumbs'][] = 'Assign Roles';
?>
<div class="user-management-assign-role">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <strong>User ID:</strong> <?= Html::encode($user->id) ?><br>
        <strong>Username:</strong> <?= Html::encode($user->username) ?><br>
        <strong>Email:</strong> <?= Html::encode($user->email) ?>
    </p>

    <hr>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($roleAssignmentModel, 'roles[]')->checkboxList($allRolesList, [
        'item' => function ($index, $label, $name, $checked, $value) use ($roleAssignmentModel) { // Added: use ($roleAssignmentModel)
            // $label here is the role description, $value is the role name
            return Html::checkbox($name, $checked, [
                'value' => $value,
                'label' => '<label for="' . Html::getInputId($roleAssignmentModel, 'roles[]') . '_' . $index . '">' . Html::encode($label) . ' (' . Html::encode($value) . ')</label>',
                'id' => Html::getInputId($roleAssignmentModel, 'roles[]') . '_' . $index,
                'class' => 'form-check-input',
            ]);
        },
        'separator' => '<br>',
        'class' => 'form-check', // Add class to the container of checkboxes
    ])->label('Assign Roles (Select all that apply)') ?>
    <?php /*
        // Alternative using a multi-select dropdown if preferred, though checkboxes are often clearer for roles.
        // echo $form->field($roleAssignmentModel, 'roles')->listBox($allRolesList, ['multiple' => true, 'size' => count($allRolesList)]);
    */?>


    <div class="form-group mt-3">
        <?= Html::submitButton('Save Roles', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
