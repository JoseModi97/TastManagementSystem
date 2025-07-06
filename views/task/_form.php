<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\User;
use app\models\TaskPriority;
use app\models\TaskStatus;
use yii\helpers\ArrayHelper;
use kartik\date\DatePicker; // Using kartik-v/yii2-widget-datepicker

/** @var yii\web\View $this */
/** @var app\models\Task $model */
/** @var yii\widgets\ActiveForm $form */
/** @var app\models\Project $project (passed from controller for context) */

// Get lists for dropdowns
$userList = ArrayHelper::map(User::find()->orderBy('username ASC')->all(), 'id', 'username');
$priorityList = TaskPriority::getPriorityList();
$statusList = TaskStatus::getStatusList();

?>

<div class="task-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?php /* echo $form->field($model, 'project_id')->textInput() */ ?>
    <?php // Project ID is set in controller, or could be a hidden field if needed.
          // For this form, we assume project_id is already set on the model. ?>
    <p><strong>Project:</strong> <?= Html::encode($project->name ?? $model->project->name) ?></p>


    <?= $form->field($model, 'assigned_to')->dropDownList($userList, ['prompt' => 'Select User...']) ?>

    <?= $form->field($model, 'priority_id')->dropDownList($priorityList, ['prompt' => 'Select Priority...']) ?>

    <?= $form->field($model, 'status_id')->dropDownList($statusList, ['prompt' => 'Select Status...']) ?>

    <?= $form->field($model, 'due_date')->widget(DatePicker::class, [
        'options' => ['placeholder' => 'Select due date ...'],
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd', // Matches DATETIME format in DB, time part can be ignored or set
            'todayHighlight' => true,
            'autoclose' => true,
        ]
    ]) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Save Task', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
