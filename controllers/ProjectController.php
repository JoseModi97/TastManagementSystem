<?php

namespace app\controllers;

use Yii;
use app\models\Project;
use app\models\ProjectSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response; // Added for AJAX JSON responses

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
        // Permission check
        if (!Yii::$app->user->can('viewProject', ['project' => $model]) && $model->created_by !== Yii::$app->user->id && !Yii::$app->user->can('admin')) {
            if ($model->created_by !== Yii::$app->user->id) {
                 throw new \yii\web\ForbiddenHttpException('You are not allowed to view this project.');
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('view', [
                'model' => $model,
            ]);
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Project model.
     * If creation is successful, the browser will be redirected to the 'view' page for non-AJAX
     * or return JSON success for AJAX.
     * @return string|Response|array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCreate()
    {
        if (!Yii::$app->user->can('createProject')) {
            throw new \yii\web\ForbiddenHttpException('You are not allowed to create projects.');
        }

        $model = new Project();
        $model->created_by = Yii::$app->user->id;
        $request = Yii::$app->request;

        if ($request->isAjax && $request->isGet) {
            return $this->renderAjax('_form', [ // Or 'create' if it only contains the form
                'model' => $model,
            ]);
        }

        if ($model->load($request->post())) {
            if ($model->save()) {
                if ($request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['success' => true, 'message' => 'Project created successfully.', 'pjaxReload' => '#project-grid-pjax'];
                } else {
                    Yii::$app->session->setFlash('success', 'Project created successfully.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } else {
                if ($request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    // It's important to send back the form with errors
                    return ['success' => false, 'content' => $this->renderAjax('_form', ['model' => $model])];
                }
                // Non-AJAX validation error will re-render the 'create' view with errors by default
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Project model.
     * If update is successful, the browser will be redirected to the 'view' page for non-AJAX
     * or return JSON success for AJAX.
     * @param int $id ID
     * @return string|Response|array
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $request = Yii::$app->request;

        if (!Yii::$app->user->can('updateProject', ['project' => $model]) && !Yii::$app->user->can('updateOwnProject', ['project' => $model])) {
            throw new \yii\web\ForbiddenHttpException('You are not allowed to update this project.');
        }

        if ($request->isAjax && $request->isGet) {
            return $this->renderAjax('_form', [ // Or 'update' if it only contains the form
                'model' => $model,
            ]);
        }

        if ($model->load($request->post())) {
            if ($model->save()) {
                if ($request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['success' => true, 'message' => 'Project updated successfully.', 'pjaxReload' => '#project-grid-pjax'];
                } else {
                    Yii::$app->session->setFlash('success', 'Project updated successfully.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } else {
                 if ($request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['success' => false, 'content' => $this->renderAjax('_form', ['model' => $model])];
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Project model.
     * If deletion is successful, the browser will be redirected to the 'index' page for non-AJAX
     * or return JSON success for AJAX.
     * @param int $id ID
     * @return Response|array
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $request = Yii::$app->request;

        if (!Yii::$app->user->can('deleteProject', ['project' => $model]) && !Yii::$app->user->can('deleteOwnProject', ['project' => $model])) {
            throw new \yii\web\ForbiddenHttpException('You are not allowed to delete this project.');
        }

        if ($model->delete()) {
            if ($request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => true, 'message' => 'Project deleted successfully.', 'pjaxReload' => '#project-grid-pjax'];
            } else {
                Yii::$app->session->setFlash('success', 'Project deleted successfully.');
            }
        } else {
            if ($request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => false, 'message' => 'Error deleting project.'];
            } else {
                Yii::$app->session->setFlash('error', 'Error deleting project.');
            }
        }

        return $this->redirect(['index']); // Fallback for non-AJAX or if AJAX response wasn't fully handled client-side
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
