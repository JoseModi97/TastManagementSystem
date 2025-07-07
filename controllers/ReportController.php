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
        $usersProvider = new ActiveDataProvider([
            'query' => User::find()->with('tasks'),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('user-tasks', [
            'usersProvider' => $usersProvider,
        ]);
    }

    /**
     * Displays a report of projects and their tasks.
     * @return string
     */
    public function actionProjectTasks()
    {
        $projectsProvider = new ActiveDataProvider([
            'query' => \app\models\Project::find()->with('tasks'),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('project-tasks', [
            'projectsProvider' => $projectsProvider,
        ]);
    }

    /**
     * Displays a report of task statuses.
     * @return string
     */
    public function actionTaskStatus()
    {
        $taskStatusProvider = new ActiveDataProvider([
            'query' => \app\models\TaskStatus::find()->with('tasks'),
            'pagination' => [
                'pageSize' => 10, // Usually not many statuses
            ],
        ]);

        return $this->render('task-status', [
            'taskStatusProvider' => $taskStatusProvider,
        ]);
    }

    /**
     * Displays a report of task priorities.
     * @return string
     */
    public function actionTaskPriority()
    {
        $taskPriorityProvider = new ActiveDataProvider([
            'query' => \app\models\TaskPriority::find()->with('tasks')->orderBy('weight DESC'),
            'pagination' => [
                'pageSize' => 10, // Usually not many priorities
            ],
        ]);

        return $this->render('task-priority', [
            'taskPriorityProvider' => $taskPriorityProvider,
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
