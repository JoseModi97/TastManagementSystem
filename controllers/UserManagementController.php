<?php

namespace app\controllers;

use Yii;
use app\models\User;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * UserManagementController implements actions for managing users and their roles.
 */
class UserManagementController extends Controller
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
                    'delete' => ['POST'], // If you add a delete user action
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'], // Only users with 'admin' role can access this controller
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'username' => SORT_ASC
                ]
            ]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model and allows role assignment.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionAssignRole($id)
    {
        $user = $this->findModel($id);
        $authManager = Yii::$app->authManager;

        $allRoles = $authManager->getRoles();
        $allRolesList = ArrayHelper::map($allRoles, 'name', 'description'); // For dropdown

        // Get current roles for the user
        $userRolesObjects = $authManager->getRolesByUser($user->id);
        // Get just the names for pre-selection in form
        $userRoleNames = array_keys($userRolesObjects);

        // Using a simple model for form handling; Yii::$app->request->post('roles') could also be used
        $roleAssignmentModel = new \yii\base\DynamicModel(['roles' => $userRoleNames]);
        $roleAssignmentModel->addRule(['roles'], 'each', ['rule' => ['in', 'range' => array_keys($allRolesList)]]);


        if ($roleAssignmentModel->load(Yii::$app->request->post()) && $roleAssignmentModel->validate()) {
            $authManager->revokeAll($user->id); // Revoke all existing roles first

            if (!empty($roleAssignmentModel->roles)) {
                foreach ((array)$roleAssignmentModel->roles as $roleName) { // Ensure it's an array
                    $role = $authManager->getRole($roleName);
                    if ($role) {
                        try {
                            $authManager->assign($role, $user->id);
                        } catch (\Exception $e) {
                            Yii::$app->session->setFlash('error', "Failed to assign role {$roleName}: " . $e->getMessage());
                        }
                    }
                }
            }
            Yii::$app->session->setFlash('success', "User roles updated successfully for {$user->username}.");
            return $this->redirect(['index']);
        }

        return $this->render('assign-role', [
            'user' => $user,
            'allRolesList' => $allRolesList,
            'roleAssignmentModel' => $roleAssignmentModel,
        ]);
    }


    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested user does not exist.');
    }
}
