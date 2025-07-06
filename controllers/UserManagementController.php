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

        $allSystemRoles = $authManager->getRoles();
        $allSystemRolesMap = ArrayHelper::map($allSystemRoles, 'name', 'description'); // name => description

        $currentUserRoleObjects = $authManager->getRolesByUser($user->id);
        $currentUserRolesMap = ArrayHelper::map($currentUserRoleObjects, 'name', 'description'); // name => description

        // Roles available for assignment (all system roles MINUS current user roles)
        $assignableRolesList = array_diff_key($allSystemRolesMap, $currentUserRolesMap);

        // Dynamic model for roles to be added via checkboxes
        $roleAddModel = new \yii\base\DynamicModel(['roles_to_add' => null]); // Attribute name changed
        // Ensure roles_to_add is treated as an array, even if only one or none are selected
        $roleAddModel->addRule(['roles_to_add'], 'each', ['rule' => ['in', 'range' => array_keys($assignableRolesList)]]);
        $roleAddModel->addRule(['roles_to_add'], 'default', ['value' => []]); // Default to empty array if nothing submitted


        if ($roleAddModel->load(Yii::$app->request->post()) && $roleAddModel->validate()) {
            $rolesToAdd = (array) $roleAddModel->roles_to_add; // Ensure it's an array
            $assignedCount = 0;

            if (!empty($rolesToAdd)) {
                foreach ($rolesToAdd as $roleName) {
                    // Safety check: Prevent admin from assigning 'admin' to self if they are already admin
                    // (though the UI should prevent this by not listing it as assignable)
                    if ($roleName === 'admin' && $user->id === Yii::$app->user->id && isset($currentUserRolesMap['admin'])) {
                        Yii::$app->session->addFlash('info', 'Admin role is already assigned to yourself.');
                        continue;
                    }

                    $role = $authManager->getRole($roleName);
                    if ($role && !$authManager->checkAccess($user->id, $roleName)) { // Check if not already assigned (double check)
                        try {
                            $authManager->assign($role, $user->id);
                            $assignedCount++;
                        } catch (\Exception $e) {
                            Yii::$app->session->setFlash('error', "Failed to assign role '{$roleName}': " . $e->getMessage());
                        }
                    }
                }
            }

            if ($assignedCount > 0) {
                 Yii::$app->session->setFlash('success', "Successfully added {$assignedCount} role(s) to {$user->username}.");
            } else {
                 Yii::$app->session->setFlash('info', "No new roles were selected or assigned to {$user->username}.");
            }
            // Redirect back to the same page to see the updated list of assigned roles and available roles
            return $this->redirect(['assign-role', 'id' => $user->id]);
        }

        return $this->render('assign-role', [
            'user' => $user,
            'currentUserRolesMap' => $currentUserRolesMap, // Pass currently assigned roles for display as text
            'assignableRolesList' => $assignableRolesList, // Pass only assignable roles for checkboxes
            'roleAddModel' => $roleAddModel,          // Pass the new model for the form
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
