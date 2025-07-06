<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\User;

/**
 * Manages users.
 */
class UserController extends Controller
{
    /**
     * Assigns a role to a user.
     * @param string $roleName The name of the role to assign.
     * @param int $userId The ID of the user.
     * @return int Exit code.
     */
    public function actionAssignRole($roleName, $userId)
    {
        $user = User::findOne($userId);
        if (!$user) {
            $this->stdout("Error: User with ID {$userId} not found.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);

        if (!$role) {
            $this->stdout("Error: Role '{$roleName}' not found. Make sure RBAC is initialized.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        try {
            // Revoke any existing roles first to prevent issues if re-assigning or changing roles.
            // This is a simple approach; more complex logic might be needed for multi-role systems.
            $auth->revokeAll($userId);

            $auth->assign($role, $userId);
            $this->stdout("Role '{$roleName}' assigned successfully to user {$user->username} (ID: {$userId}).\n");
            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stdout("Error assigning role: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * A convenience method to assign the 'admin' role to a user.
     * Example: php yii user/assign-admin 1
     * @param int $userId The ID of the user to make admin.
     * @return int Exit code.
     */
    public function actionAssignAdmin($userId)
    {
        return $this->actionAssignRole('admin', $userId);
    }
}
