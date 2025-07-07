<?php

/** @var yii\web\View $this */
/** @var int $projectCount */
/** @var int $tasksAssignedCount */
/** @var int $activeTasksInOwnedProjectsCount */
/** @var app\models\Task[] $recentlyDueTasks */
/** @var array $overallTaskStatusData */
/** @var array $tasksNearingDeadlineData */
/** @var array $userTaskLoadData */

use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;

// Ensure new variables from controller are documented here for clarity
/** @var string $userName */
/** @var app\models\Task[] $hotListTasks */
/** @var int $dueTodayCount */
/** @var int $overdueCount */
/** @var int $activeEngagementsCount */
/** @var int $missionAccomplishedCount */
/** @var array $projectPulseData */

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;

$priorityLabels = [1 => 'Low', 2 => 'Medium', 3 => 'High']; // For Hot List display

?>
<div class="site-dashboard">
    <!-- Personalized Greeting -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= Html::encode($this->title) ?></h1>
        <?php // Potential spot for a "Generate Report" button later ?>
    </div>
    <p class="mb-4">Welcome back, <strong><?= Html::encode($userName) ?></strong>! Here's your mission briefing.</p>

    <!-- Mission Control Phase 1 Row -->
    <div class="row">
        <!-- Hot List Card/Column -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Your Hot List (Next 3)</div>
                            <?php if (!empty($hotListTasks)): ?>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($hotListTasks as $task): ?>
                                        <li class="mb-1">
                                            <?= Html::a(Html::encode($task->title), ['task/view', 'id' => $task->id], ['class' => 'text-gray-800']) ?>
                                            <small class="d-block text-muted">
                                                Due: <?= Yii::$app->formatter->asDate($task->due_date, 'medium') ?>
                                                (Priority: <?= Html::encode($priorityLabels[$task->priority_id] ?? $task->priority_id) ?>)
                                            </small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">All clear!</div>
                                <p class="text-muted mb-0">No immediate urgent tasks.</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-rocket fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Launchpad Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Today's Launchpad (Due Today)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= Html::encode($dueTodayCount) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Red Alerts! Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Red Alerts! (Overdue)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= Html::encode($overdueCount) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Mission Control Phase 1 Row -->

    <!-- Mission Control Phase 2 Summary Cards Row -->
    <div class="row">
        <!-- Active Engagements Card -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Engagements (Projects)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= Html::encode($activeEngagementsCount ?? 0) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-briefcase fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mission Accomplished! Card -->
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Missions Accomplished! (Tasks This Week)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= Html::encode($missionAccomplishedCount ?? 0) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Mission Control Phase 2 Summary Cards Row -->

    <!-- Project Pulse Section -->
    <div class="row">
        <div class="col-lg-12 mb-4"> <!-- Full width for this section -->
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Project Pulse</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($projectPulseData)): ?>
                        <?php foreach ($projectPulseData as $projectData): ?>
                            <h4 class="small font-weight-bold">
                                <?= Html::a(Html::encode($projectData['name']), ['project/view', 'id' => $projectData['id']], ['class'=>"text-dark"]) ?>
                                <span class="float-right"><?= $projectData['percentage'] ?>%</span>
                            </h4>
                            <div class="progress mb-2">
                                <div class="progress-bar
                                    <?php
                                    if ($projectData['percentage'] < 30) echo 'bg-danger';
                                    elseif ($projectData['percentage'] < 70) echo 'bg-warning';
                                    else echo 'bg-success';
                                    ?>
                                    " role="progressbar" style="width: <?= $projectData['percentage'] ?>%"
                                     aria-valuenow="<?= $projectData['percentage'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-muted mt-3">No specific project pulse data to display currently.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- End Project Pulse Section -->

    <hr class="my-4">

    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">My Projects</h5>
                    <p class="card-text display-4"><?= Html::encode($projectCount) ?></p>
                    <?= Html::a('View My Projects &raquo;', ['project/index'], ['class' => 'btn btn-outline-primary']) ?>
                    <?= Html::a('Create New Project &raquo;', ['project/create'], ['class' => 'btn btn-success mt-2']) ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Tasks Assigned to Me</h5>
                    <p class="card-text display-4"><?= Html::encode($tasksAssignedCount) ?></p>
                     <?= Html::a('View My Assigned Tasks &raquo;', ['task/index', 'TaskSearch[assigned_to]' => Yii::$app->user->id], ['class' => 'btn btn-outline-primary']) ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Active Tasks in My Projects</h5>
                    <p class="card-text display-4"><?= Html::encode($activeTasksInOwnedProjectsCount) ?></p>
                     <?php // Link could go to project index or a pre-filtered task list for user's projects' active tasks ?>
                     <?= Html::a('View All Tasks &raquo;', ['task/index'], ['class' => 'btn btn-outline-primary']) ?>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <h3>Tasks Due Soon or Overdue (Assigned to You)</h3>
    <?php if (!empty($recentlyDueTasks)): ?>
        <ul class="list-group">
            <?php foreach ($recentlyDueTasks as $task): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <?= Html::a(Html::encode($task->title), ['task/view', 'id' => $task->id]) ?>
                        <small class="d-block text-muted">
                            Project: <?= Html::a(Html::encode($task->project->name), ['project/view', 'id' => $task->project_id]) ?>
                            | Due: <?= Yii::$app->formatter->asDate($task->due_date) ?>
                            <?php if ($task->status): ?>
                                | Status: <span class="badge bg-info"><?= Html::encode($task->status->label) ?></span>
                            <?php endif; ?>
                        </small>
                    </div>
                    <?php
                        $dueDate = new DateTime($task->due_date);
                        $now = new DateTime();
                        $isOverdue = $dueDate < $now && $task->status->label !== 'Done'; // Check if not done
                    ?>
                    <?php if ($isOverdue): ?>
                        <span class="badge bg-danger rounded-pill">Overdue</span>
                    <?php elseif ($task->status->label === 'Done'): ?>
                        <span class="badge bg-success rounded-pill">Done</span>
                    <?php else: ?>
                         <span class="badge bg-warning rounded-pill">Due Soon</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No tasks assigned to you are due soon or overdue.</p>
    <?php endif; ?>

    <hr class="my-4">

    <div class="row">
        <!-- Overall Task Status Pie Chart -->
        <div class="col-xl-6 col-lg-7">
            <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Overall Task Status</h6>
                    <?php /*
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Dropdown Header:</div>
                            <a class="dropdown-item" href="#">Action</a>
                            <a class="dropdown-item" href="#">Another action</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">Something else here</a>
                        </div>
                    </div>
                    */ ?>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="overallTaskStatusPieChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <?php
                        $legendItems = [];
                        if (isset($overallTaskStatusData['labels']) && is_array($overallTaskStatusData['labels'])) {
                            foreach ($overallTaskStatusData['labels'] as $index => $label) {
                                $color = $overallTaskStatusData['backgroundColors'][$index] ?? '#ccc';
                                $legendItems[] = '<span class="mr-2"><i class="fas fa-circle" style="color:' . $color . '"></i> ' . Html::encode($label) . '</span>';
                            }
                        }
                        echo implode("\n", $legendItems);
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Placeholder for another chart -->
        <div class="col-xl-6 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Tasks Nearing Deadline (Next 7 Days)</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($tasksNearingDeadlineData) && !empty($tasksNearingDeadlineData['labels'])): ?>
                        <div class="chart-bar"> {/* Using chart-bar for consistency, though it's horizontal */}
                            <canvas id="tasksNearingDeadlineChart"></canvas>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted mt-4 mb-4">No tasks nearing deadline in the next 7 days.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="row">
        <!-- User Task Load Bar Chart -->
        <div class="col-lg-12"> 
            <?php
            /* Full width for this chart potentially */
            ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Task Load (Active Tasks)</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($userTaskLoadData) && !empty($userTaskLoadData['labels'])): ?>
                        <div class="chart-bar">
                            <canvas id="userTaskLoadChart"></canvas>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted mt-4 mb-4">No user task load data available or no active tasks found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="row">
        <!-- Project Progress Bar Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Project Progress Overview (Recent Projects)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="projectProgressChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Priority Pie Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Task Priority Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="taskPriorityPieChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small" id="taskPriorityPieChartLegend">
                        <?php /* Legend will be populated by JS if needed, or use Chart.js legend */ ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
// Ensure new variables are passed from controller
/** @var array $projectProgressData */
/** @var array $taskPriorityData */

if (!empty($overallTaskStatusData) && !empty($overallTaskStatusData['labels'])) {
    $chartJsLabels = json_encode($overallTaskStatusData['labels']);
    $chartJsCounts = json_encode($overallTaskStatusData['counts']);
    $chartJsBackgroundColors = json_encode($overallTaskStatusData['backgroundColors']);
    $chartJsHoverBackgroundColors = json_encode($overallTaskStatusData['hoverBackgroundColors']);
    $totalOverallTasks = array_sum($overallTaskStatusData['counts']);

    // Ensure Chart.js is registered (it might be registered multiple times if not careful, Yii handles this)
    $this->registerJsFile(Yii::getAlias('@web/sb-admin-2/vendor/chart.js/Chart.min.js'), ['position' => View::POS_END, 'depends' => [\yii\web\YiiAsset::class]]);

    $jsOverallPie = <<<JS
// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

// Overall Task Status Pie Chart
if (document.getElementById("overallTaskStatusPieChart")) {
    var ctxOverallPie = document.getElementById("overallTaskStatusPieChart");
    var overallTaskStatusPieChart = new Chart(ctxOverallPie, {
      type: 'doughnut', // or 'pie'
      data: {
        labels: $chartJsLabels,
        datasets: [{
          data: $chartJsCounts,
          backgroundColor: $chartJsBackgroundColors,
          hoverBackgroundColor: $chartJsHoverBackgroundColors,
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
              var percentage = parseFloat((currentValue / $totalOverallTasks * 100).toFixed(2));
              if (isNaN(percentage)) percentage = 0;
              return data.labels[tooltipItem.index] + ': ' + currentValue + ' (' + percentage + '%)';
            }
          }
        },
        legend: {
          display: false // Custom legend is built below the chart
        },
        cutoutPercentage: 80, // For doughnut chart
      },
    });
}
JS;
    $this->registerJs($jsOverallPie, View::POS_READY, 'overallTaskStatusPieChartScript');
}

if (!empty($tasksNearingDeadlineData) && !empty($tasksNearingDeadlineData['labels'])) {
    $deadlineLabels = json_encode($tasksNearingDeadlineData['labels']);
    $deadlineData = json_encode($tasksNearingDeadlineData['data']); // Days remaining
    $deadlineBgColors = json_encode($tasksNearingDeadlineData['backgroundColors']);
    $deadlineBorderColors = json_encode($tasksNearingDeadlineData['borderColors']);

    // Ensure Chart.js is registered (it might be registered multiple times if not careful, Yii handles this)
    $this->registerJsFile(Yii::getAlias('@web/sb-admin-2/vendor/chart.js/Chart.min.js'), ['position' => View::POS_END, 'depends' => [\yii\web\YiiAsset::class]]);

    $jsDeadlineBar = <<<JS
// Tasks Nearing Deadline Horizontal Bar Chart
if (document.getElementById("tasksNearingDeadlineChart")) {
    var ctxDeadlineBar = document.getElementById("tasksNearingDeadlineChart");
    var tasksNearingDeadlineChart = new Chart(ctxDeadlineBar, {
        type: 'horizontalBar',
        data: {
            labels: $deadlineLabels,
            datasets: [{
                label: 'Days Remaining',
                data: $deadlineData,
                backgroundColor: $deadlineBgColors,
                borderColor: $deadlineBorderColors,
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false, // Important for custom height
            scales: {
                xAxes: [{
                    ticks: {
                        beginAtZero: true,
                        // You might want to format ticks if they represent days, e.g., add " days"
                        callback: function(value, index, values) {
                            return value + ' days';
                        }
                    },
                     gridLines: {
                      display: true,
                      drawBorder: false
                    }
                }],
                yAxes: [{
                    ticks: {
                        // autoSkip: false, // Show all labels if space permits
                        // maxRotation: 0,
                        // minRotation: 0
                    },
                     gridLines: {
                      display: false, // Hide Y-axis grid lines for cleaner look
                      drawBorder: false
                    }
                }]
            },
            legend: {
                display: false // Hiding legend as dataset label is clear enough
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var label = data.datasets[tooltipItem.datasetIndex].label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += tooltipItem.xLabel + ' days remaining';
                        return label;
                    }
                }
            },
            // Adjust height of the chart container dynamically or via CSS
            // For example, if you have a fixed height container:
            // responsive: true,
            // onResize: function(chart, newSize) {
            //     // Adjust canvas height based on number of items
            //     var numItems = chart.data.labels.length;
            //     var newHeight = numItems * 40; // 40px per item, adjust as needed
            //     chart.canvas.parentNode.style.height = newHeight + 'px';
            // }
        }
    });
    // Initial resize trigger if using onResize for dynamic height
    // if(tasksNearingDeadlineChart.options.onResize) {
    //    tasksNearingDeadlineChart.options.onResize(tasksNearingDeadlineChart, null);
    // }
}
JS;
    $this->registerJs($jsDeadlineBar, View::POS_READY, 'tasksNearingDeadlineChartScript');
}

if (!empty($userTaskLoadData) && !empty($userTaskLoadData['labels'])) {
    $userLoadLabels = json_encode($userTaskLoadData['labels']);
    $userLoadData = json_encode($userTaskLoadData['data']);
    $userLoadBgColors = json_encode($userTaskLoadData['backgroundColors']);
    $userLoadBorderColors = json_encode($userTaskLoadData['borderColors']);

    // Ensure Chart.js is registered
    $this->registerJsFile(Yii::getAlias('@web/sb-admin-2/vendor/chart.js/Chart.min.js'), ['position' => View::POS_END, 'depends' => [\yii\web\YiiAsset::class]]);

    $jsUserLoadBar = <<<JS
// User Task Load Bar Chart
if (document.getElementById("userTaskLoadChart")) {
    var ctxUserLoadBar = document.getElementById("userTaskLoadChart");
    var userTaskLoadChart = new Chart(ctxUserLoadBar, {
        type: 'bar', // Vertical bar chart
        data: {
            labels: $userLoadLabels,
            datasets: [{
                label: 'Active Tasks',
                data: $userLoadData,
                backgroundColor: $userLoadBgColors,
                borderColor: $userLoadBorderColors,
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        stepSize: 1 // Ensure integer steps for task counts
                    }
                }],
                xAxes: [{
                     gridLines: {
                        display: false
                    }
                }]
            },
            legend: {
                display: true, // Show legend as dataset label is 'Active Tasks'
                position: 'top'
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var label = data.datasets[tooltipItem.datasetIndex].label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += tooltipItem.yLabel + ' tasks';
                        return label;
                    }
                }
            }
        }
    });
}
JS;
    $this->registerJs($jsUserLoadBar, View::POS_READY, 'userTaskLoadChartScript');
}

if (!empty($projectProgressData) && !empty($projectProgressData['labels'])) {
    $projectProgressLabels = json_encode($projectProgressData['labels']);
    $projectProgressValues = json_encode($projectProgressData['data']);
    $projectProgressBgColors = json_encode($projectProgressData['backgroundColors']);
    $projectProgressBorderColors = json_encode($projectProgressData['borderColors']);

    $this->registerJsFile(Yii::getAlias('@web/sb-admin-2/vendor/chart.js/Chart.min.js'), ['position' => View::POS_END, 'depends' => [\yii\web\YiiAsset::class]]);

    $jsProjectProgress = <<<JS
if (document.getElementById("projectProgressChart")) {
    var ctxProjectProgress = document.getElementById("projectProgressChart");
    var projectProgressChart = new Chart(ctxProjectProgress, {
        type: 'bar',
        data: {
            labels: $projectProgressLabels,
            datasets: [{
                label: 'Progress %',
                data: $projectProgressValues,
                backgroundColor: $projectProgressBgColors,
                borderColor: $projectProgressBorderColors,
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        max: 100, // Percentage
                        callback: function(value) { return value + "%" }
                    }
                }],
                xAxes: [{
                     gridLines: { display: false },
                     ticks: { autoSkip: false, maxRotation: 45, minRotation: 45 } // Rotate labels if long
                }]
            },
            legend: { display: false },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var label = data.datasets[tooltipItem.datasetIndex].label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += tooltipItem.yLabel + '%';
                        return label;
                    }
                }
            }
        }
    });
}
JS;
    $this->registerJs($jsProjectProgress, View::POS_READY, 'projectProgressChartScript');
}

if (!empty($taskPriorityData) && !empty($taskPriorityData['labels'])) {
    $priorityLabels = json_encode($taskPriorityData['labels']);
    $priorityCounts = json_encode($taskPriorityData['counts']);
    $priorityBackgroundColors = json_encode($taskPriorityData['backgroundColors']);
    $priorityHoverBackgroundColors = json_encode($taskPriorityData['hoverBackgroundColors']);
    $totalPriorityTasks = $taskPriorityData['totalTasks'] ?? array_sum($taskPriorityData['counts']); // Fallback if totalTasks not passed

    $this->registerJsFile(Yii::getAlias('@web/sb-admin-2/vendor/chart.js/Chart.min.js'), ['position' => View::POS_END, 'depends' => [\yii\web\YiiAsset::class]]);

    $jsTaskPriorityPie = <<<JS
if (document.getElementById("taskPriorityPieChart")) {
    var ctxPriorityPie = document.getElementById("taskPriorityPieChart");
    var taskPriorityPieChart = new Chart(ctxPriorityPie, {
      type: 'doughnut',
      data: {
        labels: $priorityLabels,
        datasets: [{
          data: $priorityCounts,
          backgroundColor: $priorityBackgroundColors,
          hoverBackgroundColor: $priorityHoverBackgroundColors,
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
              var percentage = parseFloat((currentValue / $totalPriorityTasks * 100).toFixed(2));
              if (isNaN(percentage) || $totalPriorityTasks === 0) percentage = 0;
              return data.labels[tooltipItem.index] + ': ' + currentValue + ' (' + percentage + '%)';
            }
          }
        },
        legend: {
          display: true, // Display default legend for this one, or build custom like overallTaskStatus
          position: 'bottom',
        },
        cutoutPercentage: 80,
      },
    });

    // Optional: Custom legend generation if Chart.js default is not sufficient
    // var legendContainer = document.getElementById('taskPriorityPieChartLegend');
    // if (legendContainer) { // Check if legend container exists
    //     var legendHTML = "";
    //     taskPriorityPieChart.data.labels.forEach(function(label, index) {
    //         var color = taskPriorityPieChart.data.datasets[0].backgroundColor[index];
    //         legendHTML += '<span class="mr-2"><i class="fas fa-circle" style="color:' + color + '"></i> ' + label + '</span>';
    //     });
    //     legendContainer.innerHTML = legendHTML;
    // }
}
JS;
    $this->registerJs($jsTaskPriorityPie, View::POS_READY, 'taskPriorityPieChartScript');
}
?>
