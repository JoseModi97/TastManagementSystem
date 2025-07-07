<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\SignupForm;
use app\models\Project; // Added for dashboard
use app\models\Task; // Added for dashboard
use app\models\User;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'index'], // Index is now also controlled for dashboard
                'rules' => [
                    [
                        'actions' => ['logout', 'index'], // Logged in users can access index (dashboard) and logout
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    // Login, Signup, About, Contact, Error are implicitly allowed for guests if not listed here
                    // and no default deny rule is present.
                    // For explicit guest access to index (if it were not a dashboard):
                    // [
                    // 'actions' => ['index', 'login', 'signup', 'contact', 'about', 'error', 'captcha'],
                    // 'allow' => true,
                    // 'roles' => ['?'], // Guest users
                    // ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->render('index');
        }

        // For logged-in users, show dashboard
        $userId = Yii::$app->user->id;

        $projectCount = Project::find()->where(['created_by' => $userId])->count();
        $tasksAssignedCount = Task::find()->where(['assigned_to' => $userId])->count();

        // More complex: tasks in user's projects that are not done
        $activeTasksInOwnedProjectsCount = Task::find()
            ->joinWith('project p')
            ->joinWith('status s')
            ->where(['p.created_by' => $userId])
            ->andWhere(['!=', 's.label', 'Done']) // Assuming 'Done' is the label for completed status
            ->count();

        // Recently Due Tasks (e.g., due in next 7 days or overdue, assigned to user)
        $recentlyDueTasks = Task::find()
            ->where(['assigned_to' => $userId])
            ->andWhere(['is not', 'due_date', null])
            ->andWhere(['<=', 'due_date', date('Y-m-d H:i:s', strtotime('+7 days'))])
            ->joinWith('status s') // to exclude done tasks
            ->andWhere(['!=', 's.label', 'Done'])
            ->orderBy(['due_date' => SORT_ASC])
            ->limit(5)
            ->all();

        return $this->render('dashboard', [
            'projectCount' => $projectCount,
            'tasksAssignedCount' => $tasksAssignedCount,
            'activeTasksInOwnedProjectsCount' => $activeTasksInOwnedProjectsCount,
            'recentlyDueTasks' => $recentlyDueTasks, // This is for the list, might be reusable or need a different query for chart
            'overallTaskStatusData' => $this->getOverallTaskStatusData(),
            'tasksNearingDeadlineData' => $this->getTasksNearingDeadlineData(),
            'userTaskLoadData' => $this->getUserTaskLoadData(),
            'projectProgressData' => $this->getProjectProgressData(),
            'taskPriorityData' => $this->getTaskPriorityData(),
        ]);
    }

    private function getProjectProgressData($projectLimit = 10)
    {
        $projects = Project::find()->with(['tasks.status'])->orderBy(['created_at' => SORT_DESC])->limit($projectLimit)->all();
        $progressData = [];

        $doneStatusId = \app\models\TaskStatus::findOne(['label' => 'Done'])->id ?? null;

        $labels = [];
        $percentages = [];
        $backgroundColors = [];
        $borderColors = [];
        $colorPalette = [
            ['bg' => 'rgba(255, 99, 132, 0.5)', 'border' => 'rgba(255, 99, 132, 1)'],
            ['bg' => 'rgba(54, 162, 235, 0.5)', 'border' => 'rgba(54, 162, 235, 1)'],
            ['bg' => 'rgba(255, 206, 86, 0.5)', 'border' => 'rgba(255, 206, 86, 1)'],
            ['bg' => 'rgba(75, 192, 192, 0.5)', 'border' => 'rgba(75, 192, 192, 1)'],
            ['bg' => 'rgba(153, 102, 255, 0.5)', 'border' => 'rgba(153, 102, 255, 1)'],
            ['bg' => 'rgba(255, 159, 64, 0.5)', 'border' => 'rgba(255, 159, 64, 1)'],
        ];
        $colorIndex = 0;

        foreach ($projects as $project) {
            /** @var Project $project */
            $totalProjectTasks = count($project->tasks);
            $doneTasksCount = 0;

            foreach ($project->tasks as $task) {
                /** @var Task $task */
                if ($task->status_id == $doneStatusId) {
                    $doneTasksCount++;
                }
            }

            $percentageComplete = ($totalProjectTasks > 0) ? round(($doneTasksCount / $totalProjectTasks) * 100, 0) : 0;

            $labels[] = $project->name;
            $percentages[] = $percentageComplete;

            $color = $colorPalette[$colorIndex % count($colorPalette)];
            $backgroundColors[] = $color['bg'];
            $borderColors[] = $color['border'];
            $colorIndex++;
        }

        return [
            'labels' => $labels,
            'data' => $percentages,
            'backgroundColors' => $backgroundColors,
            'borderColors' => $borderColors,
        ];
    }

    private function getTaskPriorityData()
    {
        $priorities = \app\models\TaskPriority::find()->with('tasks')->orderBy('weight DESC')->all();
        $priorityData = [];
        $totalTasks = 0;

        $labels = [];
        $counts = [];
        // Using a predefined set of colors, similar to getOverallTaskStatusData
        $backgroundColors = ['#f6c23e', '#e74a3b', '#36b9cc', '#1cc88a', '#4e73df', '#858796']; // Example colors
        $hoverBackgroundColors = ['#dda20a', '#c73e28', '#2c9faf', '#17a673', '#2e59d9', '#606268'];
        $colorIndex = 0;

        foreach ($priorities as $priority) {
            $taskCount = count($priority->tasks);
            $labels[] = $priority->label;
            $counts[] = $taskCount;
            $totalTasks += $taskCount;
        }

        // Ensure we have enough colors, repeat if necessary, or use a color generation function
        $finalBackgroundColors = [];
        $finalHoverBackgroundColors = [];
        for ($i = 0; $i < count($labels); $i++) {
            $finalBackgroundColors[] = $backgroundColors[$i % count($backgroundColors)];
            $finalHoverBackgroundColors[] = $hoverBackgroundColors[$i % count($hoverBackgroundColors)];
        }


        return [
            'labels' => $labels,
            'counts' => $counts,
            'backgroundColors' => $finalBackgroundColors,
            'hoverBackgroundColors' => $finalHoverBackgroundColors,
            'totalTasks' => $totalTasks, // Useful for percentage calculation in tooltips
        ];
    }

    private function getOverallTaskStatusData()
    {
        $statusCounts = Task::find()
            ->select(['status_id', 'COUNT(*) as count'])
            ->joinWith('status s', false) // Join with status table
            ->groupBy('status_id')
            ->asArray()
            ->all();

        $statusLabels = \app\models\TaskStatus::find()->select(['id', 'label'])->asArray()->indexBy('id')->all();

        $labels = [];
        $counts = [];
        $backgroundColors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796']; // Default SB Admin 2 Colors
        $hoverBackgroundColors = ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#c73e28', '#606268'];
        $colorIndex = 0;

        $dataForChart = [];

        foreach ($statusCounts as $statusCount) {
            $label = $statusLabels[$statusCount['status_id']]['label'] ?? 'Unknown Status';
            $labels[] = $label;
            $counts[] = (int)$statusCount['count'];
            $dataForChart[] = [ // For easier passing if needed elsewhere, or if GridView was used
                'label' => $label,
                'count' => (int)$statusCount['count'],
            ];
        }

        return [
            'labels' => $labels,
            'counts' => $counts,
            'backgroundColors' => array_slice($backgroundColors, 0, count($labels)), // Ensure enough colors
            'hoverBackgroundColors' => array_slice($hoverBackgroundColors, 0, count($labels)),
            // 'dataForTable' => $dataForChart, // If we wanted to show a table too
        ];
    }

    private function getTasksNearingDeadlineData($daysLimit = 7, $taskLimit = 5)
    {
        $doneStatusLabel = 'Done'; // Assuming 'Done' is the label for completed status
        $today = new \DateTime();
        $deadlineDate = (new \DateTime())->modify("+$daysLimit days");

        $tasks = Task::find()
            ->joinWith(['status s', 'project p']) // Eager load status and project
            ->where(['!=', 's.label', $doneStatusLabel])
            ->andWhere(['is not', 'task.due_date', null])
            ->andWhere(['between', 'task.due_date', $today->format('Y-m-d H:i:s'), $deadlineDate->format('Y-m-d H:i:s')])
            ->orderBy(['task.due_date' => SORT_ASC])
            ->limit($taskLimit)
            ->all();

        $labels = [];
        $daysRemainingValues = []; // Will store days remaining
        $backgroundColors = [];
        $borderColors = [];

        $colorPalette = [ // For different bars
            ['bg' => 'rgba(255, 99, 132, 0.5)', 'border' => 'rgba(255, 99, 132, 1)'], // Red
            ['bg' => 'rgba(255, 159, 64, 0.5)', 'border' => 'rgba(255, 159, 64, 1)'], // Orange
            ['bg' => 'rgba(255, 205, 86, 0.5)', 'border' => 'rgba(255, 205, 86, 1)'], // Yellow
            ['bg' => 'rgba(75, 192, 192, 0.5)', 'border' => 'rgba(75, 192, 192, 1)'], // Green
            ['bg' => 'rgba(54, 162, 235, 0.5)', 'border' => 'rgba(54, 162, 235, 1)'], // Blue
        ];
        $colorIndex = 0;

        foreach ($tasks as $task) {
            /** @var Task $task */
            $dueDate = new \DateTime($task->due_date);
            $interval = $today->diff($dueDate);
            $days = (int)$interval->format('%r%a'); // %r gives sign, %a total days

            // Label: Task Title (Project Name)
            $labels[] = $task->title . " (" . ($task->project->name ?? 'N/A') . ")";
            $daysRemainingValues[] = $days; // Days remaining

            // Assign colors from palette
            $color = $colorPalette[$colorIndex % count($colorPalette)];
            $backgroundColors[] = $color['bg'];
            $borderColors[] = $color['border'];
            $colorIndex++;
        }

        return [
            'labels' => $labels,
            'data' => $daysRemainingValues,
            'backgroundColors' => $backgroundColors,
            'borderColors' => $borderColors,
        ];
    }

    private function getUserTaskLoadData($userLimit = 10)
    {
        $doneStatusLabel = 'Done';

        // Find users and count their active tasks
        // This could be inefficient if there are many users and many tasks.
        // A more optimized query might directly count tasks per user.
        $users = User::find()
            ->select(['user.id', 'user.username', 'COUNT(t.id) AS active_task_count'])
            ->from(['user' => User::tableName()])
            ->leftJoin(['t' => Task::tableName()], 't.assigned_to = user.id')
            ->leftJoin(['s' => \app\models\TaskStatus::tableName()], 's.id = t.status_id')
            ->where(['!=', 's.label', $doneStatusLabel])
            ->orWhere(['s.label' => null]) // Include tasks that might not have a status or status link yet
            ->groupBy(['user.id', 'user.username'])
            ->orderBy(['active_task_count' => SORT_DESC])
            ->limit($userLimit)
            ->asArray()
            ->all();

        // Fallback for users with no active tasks to ensure they can be listed if needed,
        // or to correctly represent users with zero active tasks if not using LEFT JOIN count.
        // The current query with COUNT and GROUP BY should handle users with 0 active tasks if they have any tasks at all.
        // If a user has NO tasks assigned ever, they won't appear. This is usually fine for a "task load" chart.

        $labels = [];
        $counts = [];
        $backgroundColors = [];
        $borderColors = [];

        $colorPalette = [
            ['bg' => 'rgba(75, 192, 192, 0.5)', 'border' => 'rgba(75, 192, 192, 1)'], // Teal
            ['bg' => 'rgba(54, 162, 235, 0.5)', 'border' => 'rgba(54, 162, 235, 1)'], // Blue
            ['bg' => 'rgba(255, 206, 86, 0.5)', 'border' => 'rgba(255, 206, 86, 1)'], // Yellow
            ['bg' => 'rgba(153, 102, 255, 0.5)', 'border' => 'rgba(153, 102, 255, 1)'], // Purple
            ['bg' => 'rgba(255, 159, 64, 0.5)', 'border' => 'rgba(255, 159, 64, 1)'], // Orange
            ['bg' => 'rgba(199, 199, 199, 0.5)', 'border' => 'rgba(199, 199, 199, 1)'], // Grey
            ['bg' => 'rgba(255, 99, 132, 0.5)', 'border' => 'rgba(255, 99, 132, 1)'], // Red
        ];
        $colorIndex = 0;

        foreach ($users as $user) {
            $labels[] = $user['username'];
            $counts[] = (int)$user['active_task_count']; // Count comes from the query

            $color = $colorPalette[$colorIndex % count($colorPalette)];
            $backgroundColors[] = $color['bg'];
            $borderColors[] = $color['border'];
            $colorIndex++;
        }

        return [
            'labels' => $labels,
            'data' => $counts,
            'backgroundColors' => $backgroundColors,
            'borderColors' => $borderColors,
        ];
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please login.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }
}
