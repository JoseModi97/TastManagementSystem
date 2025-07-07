<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $chartData array */
/* @var $totalTasks int */

$this->title = 'Task Priority Report';
$this->params['breadcrumbs'][] = $this->title;

// Prepare data for Chart.js
$chartJsLabels = json_encode($chartData['labels']);
$chartJsCounts = json_encode($chartData['counts']);

// Ensure Chart.js is registered (usually done in main layout or AppAsset, but good to have here if specific)
$this->registerJsFile(Yii::getAlias('@web/sb-admin-2/vendor/chart.js/Chart.min.js'), ['position' => View::POS_END, 'depends' => [\yii\web\YiiAsset::class]]);

$js = <<<JS
// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

function number_format(number, decimals, dec_point, thousands_sep) {
  // *     example: number_format(1234.56, 2, ',', ' ');
  // *     return: '1 234,56'
  number = (number + '').replace(',', '').replace(' ', '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function(n, prec) {
      var k = Math.pow(10, prec);
      return '' + Math.round(n * k) / k;
    };
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }
  return s.join(dec);
}

// Bar Chart Example
var ctx = document.getElementById("taskPriorityBarChart");
var taskPriorityBarChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: $chartJsLabels,
    datasets: [{
      label: "Number of Tasks",
      backgroundColor: "#4e73df",
      hoverBackgroundColor: "#2e59d9",
      borderColor: "#4e73df",
      data: $chartJsCounts,
      maxBarThickness: 50,
    }],
  },
  options: {
    maintainAspectRatio: false,
    layout: {
      padding: {
        left: 10,
        right: 25,
        top: 25,
        bottom: 0
      }
    },
    scales: {
      xAxes: [{
        time: {
          unit: 'month' // Not applicable here, but part of SB Admin 2's example
        },
        gridLines: {
          display: false,
          drawBorder: false
        },
        ticks: {
          maxTicksLimit: 6
        }
      }],
      yAxes: [{
        ticks: {
          min: 0,
          //max: 15000, // Set dynamically or remove for auto-scaling
          maxTicksLimit: 5,
          padding: 10,
          // Include a dollar sign in the ticks
          callback: function(value, index, values) {
            return number_format(value); // For task counts
          }
        },
        gridLines: {
          color: "rgb(234, 236, 244)",
          zeroLineColor: "rgb(234, 236, 244)",
          drawBorder: false,
          borderDash: [2],
          zeroLineBorderDash: [2]
        }
      }],
    },
    legend: {
      display: false // Label is in dataset
    },
    tooltips: {
      titleMarginBottom: 10,
      titleFontColor: '#6e707e',
      titleFontSize: 14,
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      caretPadding: 10,
      callbacks: {
        label: function(tooltipItem, chart) {
          var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
          var percentage = parseFloat((tooltipItem.yLabel / $totalTasks * 100).toFixed(2));
          if (isNaN(percentage)) percentage = 0; // handle totalTasks = 0
          return datasetLabel + ': ' + number_format(tooltipItem.yLabel) + ' (' + percentage + '%)';
        }
      }
    },
  }
});
JS;
$this->registerJs($js);
?>
<div class="report-task-priority">

    <h1><?= Html::encode($this->title) ?></h1>
    <p>Total tasks: <?= $totalTasks ?></p>

    <div class="row">
        <div class="col-lg-8">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'summary' => '',
                'columns' => [
                    'label:text:Priority',
                    'weight:integer:Weight',
                    'taskCount:integer:Number of Tasks',
                    'percentage:decimal:Percentage (%)',
                    [
                        'label' => 'Tasks',
                        'format' => 'raw',
                        'value' => function ($data) {
                            if (empty($data['tasks'])) {
                                return '<span class="text-muted">No tasks with this priority</span>';
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
                    <h6 class="m-0 font-weight-bold text-primary">Task Priority Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar" style="height: 320px;"> {/* Adjust height as needed */}
                        <canvas id="taskPriorityBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
