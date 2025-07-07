<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\Task;
use app\models\Project;
use app\models\TaskStatus;
use app\models\TaskPriority;
use app\models\TaskHistory;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * ReportController implements the actions for various reports.
 */
class ReportController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Allow authenticated users
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Displays a report of users and their assigned tasks.
     * @return string
     */
    public function actionUserTasks()
    {
        $searchModel = new \app\models\report\UserTaskReportSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('user-tasks', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a report of projects and their tasks.
     * @return string
     */
    /**
     * Displays a report of task statuses.
     * @return string
     */
    public function actionTaskStatus()
    {
        $statuses = TaskStatus::find()->with('tasks')->all();
        $totalTasks = 0;
        $statusData = [];

        foreach ($statuses as $status) {
            $taskCount = count($status->tasks);
            $totalTasks += $taskCount;
            $statusData[] = [
                'id' => $status->id,
                'label' => $status->label,
                'taskCount' => $taskCount,
                'tasks' => $status->tasks, // Keep tasks for detailed view if needed
            ];
        }

        $chartData = [
            'labels' => [],
            'counts' => [],
            'percentages' => [],
        ];

        foreach ($statusData as $key => $data) {
            $percentage = ($totalTasks > 0) ? round(($data['taskCount'] / $totalTasks) * 100, 2) : 0;
            $statusData[$key]['percentage'] = $percentage;
            $chartData['labels'][] = $data['label'];
            $chartData['counts'][] = $data['taskCount'];
            $chartData['percentages'][] = $percentage; // Could also be used in chart tooltips
        }

        // Using ArrayDataProvider as we've processed the data
        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $statusData,
            'pagination' => false, // Typically not many statuses, so pagination might be off
            'sort' => [
                'attributes' => ['label', 'taskCount', 'percentage'],
                'defaultOrder' => ['taskCount' => SORT_DESC],
            ]
        ]);

        return $this->render('task-status', [
            'dataProvider' => $dataProvider,
            'chartData' => $chartData,
            'totalTasks' => $totalTasks,
        ]);
    }

    public function actionExportUserTasksCsv()
    {
        $searchModel = new \app\models\report\UserTaskReportSearch();
        // Load persistent filter state if possible, or use current GET params
        // For simplicity, using current GET params for filtering the export.
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false; // Export all matching records

        $users = $dataProvider->getModels();

        $filename = "user_tasks_report_" . date('YmdHis') . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Add BOM to fix UTF-8 in Excel
        fputs($output, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

        // Header row
        fputcsv($output, [
            'User ID', 'Username', 'Email',
            'Task ID', 'Task Title', 'Task Status', 'Task Priority', 'Task Due Date'
        ]);

        foreach ($users as $user) {
            /** @var \app\models\User $user */
            if (empty($user->tasks)) {
                fputcsv($output, [$user->id, $user->username, $user->email, '', '', '', '', '']);
            } else {
                // Filter tasks for the current user based on searchModel criteria
                // This re-applies the task-specific filters to the tasks of each user
                $filteredTasks = [];
                if ($searchModel->task_title || $searchModel->task_status_id || $searchModel->task_priority_id) {
                    foreach($user->tasks as $task) {
                        $match = true;
                        if ($searchModel->task_title && stripos($task->title, $searchModel->task_title) === false) {
                            $match = false;
                        }
                        if ($searchModel->task_status_id && $task->status_id != $searchModel->task_status_id) {
                            $match = false;
                        }
                        if ($searchModel->task_priority_id && $task->priority_id != $searchModel->task_priority_id) {
                            $match = false;
                        }
                        if ($match) {
                            $filteredTasks[] = $task;
                        }
                    }
                } else {
                    $filteredTasks = $user->tasks;
                }

                if (empty($filteredTasks) && ($searchModel->task_title || $searchModel->task_status_id || $searchModel->task_priority_id)) {
                    // User matches overall criteria, but no tasks match sub-filter for this specific user
                     fputcsv($output, [$user->id, $user->username, $user->email, '(No tasks match current task filters for this user)', '', '', '', '']);
                } elseif (empty($filteredTasks)) {
                    // User has no tasks at all (should have been caught by the first if, but as a fallback)
                     fputcsv($output, [$user->id, $user->username, $user->email, '(No tasks assigned)', '', '', '', '']);
                } else {
                    foreach ($filteredTasks as $task) {
                        /** @var \app\models\Task $task */
                        fputcsv($output, [
                            $user->id,
                            $user->username,
                            $user->email,
                            $task->id,
                            $task->title,
                            $task->status->label ?? 'N/A',
                            $task->priority->label ?? 'N/A',
                            $task->due_date ? Yii::$app->formatter->asDate($task->due_date, 'php:Y-m-d') : '',
                        ]);
                    }
                }
            }
        }

        fclose($output);
        Yii::$app->end();
    }

    public function actionUserWorkloadSummary()
    {
        $users = User::find()->with(['tasks.priority', 'tasks.status'])->all();
        $workloadData = [];

        // Get status IDs for 'To Do', 'In Progress', 'Done'
        // This assumes specific labels. A more robust way might be to use constants or config.
        $toDoStatusId = TaskStatus::findOne(['label' => 'To Do'])->id ?? null;
        $inProgressStatusId = TaskStatus::findOne(['label' => 'In Progress'])->id ?? null;
        $doneStatusId = TaskStatus::findOne(['label' => 'Done'])->id ?? null;

        foreach ($users as $user) {
            /** @var User $user */
            $counts = [
                'to_do' => 0,
                'in_progress' => 0,
                'done' => 0,
                'other' => 0,
            ];
            $workloadScore = 0;
            $totalAssignedTasks = count($user->tasks);

            foreach ($user->tasks as $task) {
                /** @var Task $task */
                if ($task->status_id == $toDoStatusId) {
                    $counts['to_do']++;
                } elseif ($task->status_id == $inProgressStatusId) {
                    $counts['in_progress']++;
                } elseif ($task->status_id == $doneStatusId) {
                    $counts['done']++;
                } else {
                    $counts['other']++;
                }
                // Calculate workload score: sum of (priority weight for non-done tasks)
                if ($task->status_id != $doneStatusId && $task->priority) {
                    $workloadScore += $task->priority->weight ?? 0;
                }
            }

            $workloadData[] = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'tasks_total' => $totalAssignedTasks,
                'tasks_to_do' => $counts['to_do'],
                'tasks_in_progress' => $counts['in_progress'],
                'tasks_done' => $counts['done'],
                'tasks_other' => $counts['other'],
                'workload_score' => $workloadScore,
            ];
        }

        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $workloadData,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'username', 'email', 'tasks_total', 'tasks_to_do',
                    'tasks_in_progress', 'tasks_done', 'workload_score'
                ],
                'defaultOrder' => ['workload_score' => SORT_DESC, 'username' => SORT_ASC],
            ],
        ]);

        return $this->render('user-workload-summary', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionProjectProgressOverview()
    {
        $projects = Project::find()->with(['tasks.status'])->all();
        $progressData = [];

        $doneStatusId = TaskStatus::findOne(['label' => 'Done'])->id ?? null;
        $today = date('Y-m-d H:i:s');

        foreach ($projects as $project) {
            /** @var Project $project */
            $totalProjectTasks = count($project->tasks);
            $doneTasksCount = 0;
            $overdueTasksCount = 0;

            foreach ($project->tasks as $task) {
                /** @var Task $task */
                if ($task->status_id == $doneStatusId) {
                    $doneTasksCount++;
                }
                // Check for overdue: not done and due date is past
                if ($task->status_id != $doneStatusId && $task->due_date && $task->due_date < $today) {
                    $overdueTasksCount++;
                }
            }

            $percentageComplete = ($totalProjectTasks > 0) ? round(($doneTasksCount / $totalProjectTasks) * 100, 2) : 0;

            $progressData[] = [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'created_by_username' => $project->createdBy->username ?? 'N/A',
                'total_tasks' => $totalProjectTasks,
                'done_tasks' => $doneTasksCount,
                'percentage_complete' => $percentageComplete,
                'overdue_tasks' => $overdueTasksCount,
            ];
        }

        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $progressData,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'name', 'total_tasks', 'done_tasks',
                    'percentage_complete', 'overdue_tasks'
                ],
                'defaultOrder' => ['name' => SORT_ASC],
            ],
        ]);

        return $this->render('project-progress-overview', [
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Displays a report of task priorities.
     * @return string
     */
    public function actionTaskPriority()
    {
        $priorities = TaskPriority::find()->with('tasks')->orderBy('weight DESC')->all();
        $totalTasks = 0;
        $priorityData = [];

        foreach ($priorities as $priority) {
            $taskCount = count($priority->tasks);
            $totalTasks += $taskCount;
            $priorityData[] = [
                'id' => $priority->id,
                'label' => $priority->label,
                'weight' => $priority->weight,
                'taskCount' => $taskCount,
                'tasks' => $priority->tasks,
            ];
        }

        $chartData = [
            'labels' => [],
            'counts' => [],
        ];

        foreach ($priorityData as $key => $data) {
            $percentage = ($totalTasks > 0) ? round(($data['taskCount'] / $totalTasks) * 100, 2) : 0;
            $priorityData[$key]['percentage'] = $percentage;
            $chartData['labels'][] = $data['label'];
            $chartData['counts'][] = $data['taskCount'];
        }

        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $priorityData,
            'pagination' => false,
            'sort' => [
                'attributes' => ['label', 'weight', 'taskCount', 'percentage'],
                'defaultOrder' => ['weight' => SORT_DESC],
            ]
        ]);

        return $this->render('task-priority', [
            'dataProvider' => $dataProvider,
            'chartData' => $chartData,
            'totalTasks' => $totalTasks,
        ]);
    }

    /**
     * Displays a report of task history.
     * @return string
     */
    public function actionTaskHistory()
    {
        $taskHistoryProvider = new ActiveDataProvider([
            'query' => \app\models\TaskHistory::find()
                        ->joinWith(['task', 'user']) // Eager load related task and user
                        ->orderBy(['changed_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        return $this->render('task-history', [
            'taskHistoryProvider' => $taskHistoryProvider,
        ]);
    }
}
