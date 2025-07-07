<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $chartData array */
/* @var $totalTasks int */

$this->title = 'Task Status Report';
$this->params['breadcrumbs'][] = $this->title;

// Prepare data for Chart.js
$chartJsLabels = json_encode($chartData['labels']);
$chartJsCounts = json_encode($chartData['counts']);

$this->registerJsFile(Yii::getAlias('@web/sb-admin-2/vendor/chart.js/Chart.min.js'), ['position' => View::POS_END, 'depends' => [\yii\web\YiiAsset::class]]);
$js = <<<JS
// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

// Pie Chart Example
var ctx = document.getElementById("taskStatusPieChart");
var taskStatusPieChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: $chartJsLabels,
    datasets: [{
      data: $chartJsCounts,
      backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69'], // Add more colors if more statuses
      hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#c73e28', '#606268', '#404148'],
      hoverBorderColor: "rgba(234, 236, 244, 1)",
    }],
  },
  options: {
    maintainAspectRatio: false,
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      caretPadding: 10,
      callbacks: {
        label: function(tooltipItem, data) {
          var dataset = data.datasets[tooltipItem.datasetIndex];
          var currentValue = dataset.data[tooltipItem.index];
          var percentage = parseFloat((currentValue / $totalTasks * 100).toFixed(2));
          return data.labels[tooltipItem.index] + ': ' + currentValue + ' (' + percentage + '%)';
        }
      }
    },
    legend: {
      display: true,
      position: 'bottom'
    },
    cutoutPercentage: 70, // Makes it a doughnut chart
  },
});
JS;
$this->registerJs($js);

?>
<div class="report-task-status">

    <h1><?= Html::encode($this->title) ?></h1>
    <p>Total tasks: <?= $totalTasks ?></p>

    <div class="row">
        <div class="col-lg-8">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'summary' => '', // Hide summary as we have total above and chart
                'columns' => [
                    // ['class' => 'yii\grid\SerialColumn'], // Not very useful with few items
                    'label:text:Status',
                    'taskCount:integer:Number of Tasks',
                    'percentage:decimal:Percentage (%)',
                    [
                        'label' => 'Tasks',
                        'format' => 'raw',
                        'value' => function ($data) {
                            if (empty($data['tasks'])) {
                                return '<span class="text-muted">No tasks with this status</span>';
                            }
                            $taskLinks = [];
                            foreach ($data['tasks'] as $task) {
                                /** @var \app\models\Task $task */
                                $taskLinks[] = Html::a(Html::encode($task->title), ['task/view', 'id' => $task->id]);
                            }
                            return implode('<br>', $taskLinks);
                        },
                    ],
                ],
            ]); ?>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Task Status Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4">
                        <canvas id="taskStatusPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
