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
                    [
                        'allow' => true,
                        'roles' => ['@'], // Allow authenticated users
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Task models for a specific project or all tasks if no project_id.
     * @param int|null $project_id
     * @return mixed
     */
    public function actionIndex($project_id = null)
    {
        $searchModel = new TaskSearch();
        $queryParams = Yii::$app->request->queryParams;

        if ($project_id !== null) {
            $project = $this->findProjectModel($project_id); // Ensure project exists
             // Check if current user is the creator of the project
            if ($project->created_by !== Yii::$app->user->id) {
                throw new ForbiddenHttpException('You are not authorized to view tasks for this project.');
            }
            $queryParams['TaskSearch']['project_id'] = $project_id;
            $this->view->params['project'] = $project; // Pass project to view for context
        } else {
            // Optionally, if you want a global task list, ensure users can only see their relevant tasks.
            // For now, let's assume tasks are primarily viewed in project context or a user-specific context.
            // If listing all tasks, you might want to filter by tasks assigned to user or in projects they own.
            // $queryParams['TaskSearch']['assigned_to'] = Yii::$app->user->id; // Example
        }

        $dataProvider = $searchModel->search($queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'project' => $project ?? null,
        ]);
    }

    /**
     * Displays a single Task model.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ForbiddenHttpException if user is not authorized
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        // Ensure user can only view tasks from their projects
        if ($model->project->created_by !== Yii::$app->user->id) {
            throw new ForbiddenHttpException('You are not authorized to view this task.');
        }
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Task model.
     * If creation is successful, the browser will be redirected to the 'view' page or project view.
     * @param int $project_id The ID of the project this task belongs to.
     * @return mixed
     * @throws NotFoundHttpException if the project cannot be found
     * @throws ForbiddenHttpException if user is not authorized to add task to this project
     */
    public function actionCreate($project_id)
    {
        $project = $this->findProjectModel($project_id);
        if ($project->created_by !== Yii::$app->user->id) {
            throw new ForbiddenHttpException('You are not authorized to add tasks to this project.');
        }

        $model = new Task();
        $model->project_id = $project_id; // Pre-assign project_id

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Task created successfully.');
            return $this->redirect(['project/view', 'id' => $project_id]); // Redirect to project view
        }

        return $this->render('create', [
            'model' => $model,
            'project' => $project,
        ]);
    }

    /**
     * Updates an existing Task model.
     * If update is successful, the browser will be redirected to the 'view' page or project view.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ForbiddenHttpException if user is not authorized
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->project->created_by !== Yii::$app->user->id) {
            throw new ForbiddenHttpException('You are not authorized to update this task.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Task updated successfully.');
            return $this->redirect(['project/view', 'id' => $model->project_id]); // Redirect to project view
        }

        return $this->render('update', [
            'model' => $model,
            'project' => $model->project,
        ]);
    }

    /**
     * Deletes an existing Task model.
     * If deletion is successful, the browser will be redirected to the 'index' or project view page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ForbiddenHttpException if user is not authorized
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $project_id = $model->project_id; // Store before deleting

        if ($model->project->created_by !== Yii::$app->user->id) {
            throw new ForbiddenHttpException('You are not authorized to delete this task.');
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Task deleted successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Error deleting task.');
        }

        return $this->redirect(['project/view', 'id' => $project_id]); // Redirect to project view
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
