<?php

use yii\db\Migration;
use app\rbac\AuthorRule;

/**
 * Class m240726_000008_add_rbac_data
 * Populates RBAC with initial roles, permissions, and rules.
 */
class m240726_000008_add_rbac_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $this->assertNotNull($auth, 'authManager is not configured.');

        // 0. Add the AuthorRule
        $authorRule = new AuthorRule();
        $auth->add($authorRule);

        // 1. Define Permissions
        // Project Permissions
        $createProject = $auth->createPermission('createProject');
        $createProject->description = 'Create a project';
        $auth->add($createProject);

        $viewProject = $auth->createPermission('viewProject');
        $viewProject->description = 'View project details';
        $auth->add($viewProject);

        $updateOwnProject = $auth->createPermission('updateOwnProject');
        $updateOwnProject->description = 'Update own project';
        $updateOwnProject->ruleName = $authorRule->name;
        $auth->add($updateOwnProject);

        $updateProject = $auth->createPermission('updateProject');
        $updateProject->description = 'Update any project (Admin)';
        $auth->add($updateProject);

        $deleteOwnProject = $auth->createPermission('deleteOwnProject');
        $deleteOwnProject->description = 'Delete own project';
        $deleteOwnProject->ruleName = $authorRule->name;
        $auth->add($deleteOwnProject);

        $deleteProject = $auth->createPermission('deleteProject');
        $deleteProject->description = 'Delete any project (Admin)';
        $auth->add($deleteProject);

        // Task Permissions
        $createTask = $auth->createPermission('createTask'); // Rule might be needed if not anyone can create task in any project
        $createTask->description = 'Create a task';
        $auth->add($createTask);

        $viewTask = $auth->createPermission('viewTask');
        $viewTask->description = 'View task details';
        $auth->add($viewTask);

        $updateOwnTask = $auth->createPermission('updateOwnTask'); // Rule: task is in own project OR assigned to user
        $updateOwnTask->description = 'Update task in own project or assigned to self';
        $updateOwnTask->ruleName = $authorRule->name; // Using AuthorRule, implies task's project's author
        $auth->add($updateOwnTask);

        $updateTask = $auth->createPermission('updateTask');
        $updateTask->description = 'Update any task (Admin)';
        $auth->add($updateTask);

        $deleteOwnTask = $auth->createPermission('deleteOwnTask');
        $deleteOwnTask->description = 'Delete task in own project or assigned to self';
        $deleteOwnTask->ruleName = $authorRule->name; // Using AuthorRule, implies task's project's author
        $auth->add($deleteOwnTask);

        $deleteTask = $auth->createPermission('deleteTask');
        $deleteTask->description = 'Delete any task (Admin)';
        $auth->add($deleteTask);

        $assignTaskUser = $auth->createPermission('assignTaskUser');
        $assignTaskUser->description = 'Assign/Reassign user to a task';
        $auth->add($assignTaskUser);

        $viewTaskHistory = $auth->createPermission('viewTaskHistory');
        $viewTaskHistory->description = 'View history of a task';
        $auth->add($viewTaskHistory);

        // General UI Permissions
        $viewDashboard = $auth->createPermission('viewDashboard');
        $viewDashboard->description = 'View user dashboard';
        $auth->add($viewDashboard);

        // Admin Permissions
        $manageUsers = $auth->createPermission('manageUsers');
        $manageUsers->description = 'Manage users (Admin)';
        $auth->add($manageUsers);


        // 2. Define Roles
        // Role: user
        $userRole = $auth->createRole('user');
        $userRole->description = 'Basic authenticated user';
        $auth->add($userRole);
        $auth->addChild($userRole, $viewDashboard);
        $auth->addChild($userRole, $createProject);
        $auth->addChild($userRole, $updateOwnProject);
        $auth->addChild($userRole, $deleteOwnProject);
        $auth->addChild($userRole, $viewProject); // Assuming users can view projects they created or are part of (more complex rule later)

        $auth->addChild($userRole, $createTask); // Create task (will be restricted by controller to own projects)
        $auth->addChild($userRole, $viewTask);   // View task (controller will restrict to own projects/assigned tasks)
        $auth->addChild($userRole, $updateOwnTask);
        $auth->addChild($userRole, $deleteOwnTask);
        $auth->addChild($userRole, $viewTaskHistory); // View history for tasks they can view

        // Role: admin
        $adminRole = $auth->createRole('admin');
        $adminRole->description = 'Administrator';
        $auth->add($adminRole);
        $auth->addChild($adminRole, $userRole); // Admin inherits all 'user' permissions

        $auth->addChild($adminRole, $updateProject); // Can update ANY project
        $auth->addChild($adminRole, $deleteProject); // Can delete ANY project
        $auth->addChild($adminRole, $updateTask);    // Can update ANY task
        $auth->addChild($adminRole, $deleteTask);    // Can delete ANY task
        $auth->addChild($adminRole, $assignTaskUser); // Can assign users to tasks
        $auth->addChild($adminRole, $manageUsers);   // Can manage users

        // 3. Assign a default admin user (Optional, for initial setup)
        // Replace '1' with the ID of your admin user.
        // This should ideally be done via a user management interface or a separate setup script.
        // For a migration, it's okay for initial seeding if user ID 1 is known to be admin.
        /*
        $adminUser = \app\models\User::findOne(1);
        if ($adminUser) {
            $auth->assign($adminRole, $adminUser->id);
        }
        */
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $this->assertNotNull($auth, 'authManager is not configured.');

        // Remove assignments first if any were made directly in up() for default users.
        // Example: $auth->revokeAll(1); // if admin role was assigned to user 1

        // Remove roles
        $auth->remove($auth->getRole('admin'));
        $auth->remove($auth->getRole('user'));

        // Remove permissions
        $auth->remove($auth->getPermission('manageUsers'));
        $auth->remove($auth->getPermission('viewDashboard'));
        $auth->remove($auth->getPermission('viewTaskHistory'));
        $auth->remove($auth->getPermission('assignTaskUser'));
        $auth->remove($auth->getPermission('deleteTask'));
        $auth->remove($auth->getPermission('deleteOwnTask'));
        $auth->remove($auth->getPermission('updateTask'));
        $auth->remove($auth->getPermission('updateOwnTask'));
        $auth->remove($auth->getPermission('viewTask'));
        $auth->remove($auth->getPermission('createTask'));
        $auth->remove($auth->getPermission('deleteProject'));
        $auth->remove($auth->getPermission('deleteOwnProject'));
        $auth->remove($auth->getPermission('updateProject'));
        $auth->remove($auth->getPermission('updateOwnProject'));
        $auth->remove($auth->getPermission('viewProject'));
        $auth->remove($auth->getPermission('createProject'));

        // Remove the rule
        $auth->remove($auth->getRule('isAuthor'));

        echo "m240726_000008_add_rbac_data reverted.\n";
        return true;
    }
}
