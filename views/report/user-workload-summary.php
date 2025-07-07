<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */

$this->title = 'User Workload Summary';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="report-user-workload-summary">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'username',
                'label' => 'User',
                'value' => function ($data) {
                    return Html::encode($data['username']) . ' (' . Html::encode($data['email']) . ')';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'tasks_total',
                'label' => 'Total Tasks',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'tasks_to_do',
                'label' => 'To Do',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'tasks_in_progress',
                'label' => 'In Progress',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'tasks_done',
                'label' => 'Done',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            // 'tasks_other', // Optionally display 'other' status tasks
            [
                'attribute' => 'workload_score',
                'label' => 'Workload Score',
                'headerOptions' => ['class' => 'text-right'],
                'contentOptions' => ['class' => 'text-right font-weight-bold'],
            ],
        ],
    ]); ?>

</div>
