<?php

namespace app\controllers;

use Yii;
use app\models\Project;
use app\models\ProjectSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * ProjectController implements the CRUD actions for Project model.
 */
class ProjectController extends Controller
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
                    // Specific permissions will be checked within actions using Yii::$app->user->can()
                ],
            ],
        ];
    }

    /**
     * Lists all Project models.
     * User must have 'viewProject' permission (implicitly, as all actions require '@')
     * or this could be further filtered by ProjectSearch based on user's own projects if 'viewProject' is too broad.
     * @return mixed
     */
    public function actionIndex()
    {
        // For now, any authenticated user can see the project list page.
        // Filtering of *which* projects they see can be handled by ProjectSearch
        // or by adding a specific 'listProjects' permission if needed.
        // If 'viewProject' implies viewing any project, this is fine.
        // If 'viewProject' should be for specific projects (e.g. own/member),
        // then this index might need a more general permission like 'accessProjectModule'.
        // For simplicity, we assume '@' can see the index, and ProjectSearch might filter by ownership.

        $searchModel = new ProjectSearch();
        // The ProjectSearch model can be modified to filter by created_by for non-admins
        // if we don't want users to see all projects by default.
        // For now, it shows all, which an admin might want.
        // A 'user' role might see a filtered list if ProjectSearch is adapted.
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Project model.
     * User must have 'viewProject' permission for this specific project.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        // While 'viewProject' permission might exist, AuthorRule is for 'updateOwn/deleteOwn'.
        // For viewing, if projects are not public, a check like created_by or membership is needed.
        // Assuming for now that if a user can get to this action (role '@'), they can view.
        // More granular view control (e.g. 'viewOwnProject', 'viewMemberProject') could be added.
        if (!Yii::$app->user->can('viewProject', ['project' => $model]) && $model->created_by !== Yii::$app->user->id && !Yii::$app->user->can('admin')) {
             // Fallback for basic user: can view own projects. Admins can view any.
             // A dedicated 'viewOwnProject' or rule-based 'viewProject' is cleaner.
            if ($model->created_by !== Yii::$app->user->id) {
                 throw new \yii\web\ForbiddenHttpException('You are not allowed to view this project.');
            }
        }


        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Project model.
     * User must have 'createProject' permission.
     * @return mixed
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCreate()
    {
        if (!Yii::$app->user->can('createProject')) {
            throw new \yii\web\ForbiddenHttpException('You are not allowed to create projects.');
        }

        $model = new Project();
        $model->created_by = Yii::$app->user->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Project created successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Project model.
     * User must have 'updateProject' (for any project) OR 'updateOwnProject' (for their own).
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can('updateProject', ['project' => $model]) && !Yii::$app->user->can('updateOwnProject', ['project' => $model])) {
            throw new \yii\web\ForbiddenHttpException('You are not allowed to update this project.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Project updated successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Project model.
     * User must have 'deleteProject' (for any project) OR 'deleteOwnProject' (for their own).
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can('deleteProject', ['project' => $model]) && !Yii::$app->user->can('deleteOwnProject', ['project' => $model])) {
            throw new \yii\web\ForbiddenHttpException('You are not allowed to delete this project.');
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Project deleted successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Error deleting project.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Project model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Project the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Project::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
