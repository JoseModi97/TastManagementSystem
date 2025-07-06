<?php

namespace app\controllers;

use Yii;
use app\models\Task;
use app\models\TaskSearch;
use app\models\Project; // For finding project context
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;

/**
 * TaskController implements the CRUD actions for Task model.
 */
class TaskController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [ // All actions require authenticated user
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    // Specific permissions will be checked within actions
                ],
            ],
        ];
    }

    /**
     * Lists all Task models.
     * If project_id is given, tasks for that project are listed (user must own project).
     * Otherwise, a global list of tasks (related to user by project ownership or assignment) is shown.
     * 'viewTask' permission is implicitly required by '@' role, actual filtering done in logic.
     * @param int|null $project_id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionIndex($project_id = null)
    {
        $searchModel = new TaskSearch();
        $queryParams = Yii::$app->request->queryParams;
        $project = null;

        if ($project_id !== null) {
            $project = $this->findProjectModel($project_id);
            // Check if current user can view tasks for this project (e.g. is owner)
            // This uses a simplified check; RBAC 'viewProject' would be better
            if (!Yii::$app->user->can('updateOwnProject', ['project' => $project]) && !Yii::$app->user->can('admin')) {
                 throw new ForbiddenHttpException('You are not authorized to view tasks for this project.');
            }
            $queryParams['TaskSearch']['project_id'] = $project_id;
            $this->view->params['project'] = $project;
            $dataProvider = $searchModel->search($queryParams);
        } else {
            // Global task list: Filter for tasks in user's projects or assigned to user
            // This logic is already in place from previous step
            $dataProvider = $searchModel->search($queryParams);
            $dataProvider->query->joinWith('project as p')
                                ->andWhere(['OR',
                                    ['p.created_by' => Yii::$app->user->id],
                                    ['task.assigned_to' => Yii::$app->user->id]
                                ]);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'project' => $project, // Pass $project which might be null
        ]);
    }

    /**
     * Displays a single Task model.
     * User must have 'viewTask' permission (or be project owner).
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ForbiddenHttpException if user is not authorized
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        // Check if user can view this task (is project owner or has 'viewTask' permission)
        if (!Yii::$app->user->can('viewTask', ['task' => $model]) &&
            !Yii::$app->user->can('updateOwnProject', ['project' => $model->project]) && // Project owner check
            !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('You are not authorized to view this task.');
        }
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Task model.
     * User must have 'createTask' permission and be able to modify the project (e.g. project owner).
     * @param int $project_id The ID of the project this task belongs to.
     * @return mixed
     * @throws NotFoundHttpException if the project cannot be found
     * @throws ForbiddenHttpException if user is not authorized
     */
    public function actionCreate($project_id)
    {
        $project = $this->findProjectModel($project_id);
        // User must have 'createTask' AND be able to modify this specific project (e.g. owner)
        if (!Yii::$app->user->can('createTask') ||
            (!Yii::$app->user->can('updateOwnProject', ['project' => $project]) && !Yii::$app->user->can('admin'))) {
            throw new ForbiddenHttpException('You are not authorized to add tasks to this project.');
        }

        $model = new Task();
        $model->project_id = $project_id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Task created successfully.');
            return $this->redirect(['project/view', 'id' => $project_id]);
        }

        return $this->render('create', [
            'model' => $model,
            'project' => $project,
        ]);
    }

    /**
     * Updates an existing Task model.
     * User must have 'updateTask' (admin) or 'updateOwnTask' (project owner for tasks in their project).
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ForbiddenHttpException if user is not authorized
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        // User must have general 'updateTask' or specific 'updateOwnTask' for this task (via project ownership)
        if (!Yii::$app->user->can('updateTask', ['task' => $model]) &&
            !Yii::$app->user->can('updateOwnTask', ['task' => $model])) { // 'task' => $model for AuthorRule on project
            throw new ForbiddenHttpException('You are not authorized to update this task.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Task updated successfully.');
            return $this->redirect(['project/view', 'id' => $model->project_id]);
        }

        return $this->render('update', [
            'model' => $model,
            'project' => $model->project,
        ]);
    }

    /**
     * Deletes an existing Task model.
     * User must have 'deleteTask' (admin) or 'deleteOwnTask' (project owner for tasks in their project).
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ForbiddenHttpException if user is not authorized
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $project_id = $model->project_id;

        if (!Yii::$app->user->can('deleteTask', ['task' => $model]) &&
            !Yii::$app->user->can('deleteOwnTask', ['task' => $model])) { // 'task' => $model for AuthorRule on project
            throw new ForbiddenHttpException('You are not authorized to delete this task.');
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Task deleted successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Error deleting task.');
        }

        return $this->redirect(['project/view', 'id' => $project_id]);
    }

    /**
     * Finds the Task model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Task the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Task::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested task does not exist.');
    }

    /**
     * Finds the Project model based on its primary key value.
     * @param int $id
     * @return Project the loaded model
     * @throws NotFoundHttpException if the project model cannot be found
     */
    protected function findProjectModel($id)
    {
        if (($model = Project::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested project does not exist.');
    }
}
