<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap5\Progress; // Using Bootstrap 5 Progress bar

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */

$this->title = 'Project Progress Overview';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="report-project-progress-overview">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'name',
                'label' => 'Project Name',
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a(Html::encode($data['name']), ['project/view', 'id' => $data['id']]);
                }
            ],
            [
                'attribute' => 'created_by_username',
                'label' => 'Created By'
            ],
            [
                'attribute' => 'total_tasks',
                'label' => 'Total Tasks',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'done_tasks',
                'label' => 'Tasks Done',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'percentage_complete',
                'label' => 'Progress (%)',
                'format' => 'raw',
                'value' => function ($data) {
                    $percentage = $data['percentage_complete'];
                    return Progress::widget([
                        'percent' => (int)$percentage, // Cast to int here
                        'label' => $percentage . '%', // Label can still show float
                        'barOptions' => ['class' => 'bg-success'], // Adjust class as needed
                    ]);
                },
                'headerOptions' => ['style' => 'width: 150px;'], // Give progress bar some space
            ],
            [
                'attribute' => 'overdue_tasks',
                'label' => 'Overdue Tasks',
                'format' => 'raw',
                'value' => function($data) {
                    if ($data['overdue_tasks'] > 0) {
                        return Html::tag('span', $data['overdue_tasks'], ['class' => 'badge bg-danger']);
                    }
                    return $data['overdue_tasks'];
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>
