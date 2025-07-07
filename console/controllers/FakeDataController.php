<?php

namespace app\console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use Faker\Factory;
use app\models\User; // Assuming you have User model, adjust if necessary
use app\models\Project; // Assuming you have Project model
use app\models\Task; // Assuming you have Task model
use app\models\TaskPriority; // Assuming you have TaskPriority model
use app\models\TaskStatus; // Assuming you have TaskStatus model
// Add TaskHistory model if you implement that part

class FakeDataController extends Controller
{
    /**
     * Loads fake data into the database.
     * @param int $userCount Number of users to create.
     * @param int $projectPerUserCount Maximum number of projects per user.
     * @param int $taskPerProjectCount Maximum number of tasks per project.
     * @return int Exit code.
     */
    public function actionLoad(int $userCount = 10, int $projectPerUserCount = 2, int $taskPerProjectCount = 5)
    {
        $faker = Factory::create();
        $this->stdout("Starting fake data generation...\n");

        $transaction = Yii::$app->db->beginTransaction();
        $fakerUserCredentials = []; // Array to store username:password pairs

        try {
            $this->stdout("Generating Users...\n");
            $userIds = [];
            for ($i = 0; $i < $userCount; $i++) {
                $user = new User();
                $user->username = $faker->unique()->userName;
                $user->email = $faker->unique()->email;
                $plainPassword = $faker->password(8, 20); // Generate a password
                $user->password_hash = Yii::$app->security->generatePasswordHash($plainPassword);
                $user->auth_key = Yii::$app->security->generateRandomString();
                $user->created_at = time();
                $user->updated_at = time();
                if ($user->save()) {
                    $userIds[] = $user->id;
                    // Store username and plain password for logging
                    $fakerUserCredentials[] = ['username' => $user->username, 'password' => $plainPassword];
                    $this->stdout("Generated User: {$user->username} (ID: {$user->id})\n", \yii\helpers\Console::FG_GREEN);
                } else {
                    $this->stderr("Failed to save user: " . print_r($user->errors, true) . "\n", \yii\helpers\Console::FG_RED);
                }
            }
            $this->stdout("Generated " . count($userIds) . " users.\n\n");

            // Write Faker user credentials to a file
            if (!empty($fakerUserCredentials)) {
                $logPath = Yii::getAlias('@runtime/logs');
                if (!is_dir($logPath)) {
                    \yii\helpers\FileHelper::createDirectory($logPath);
                }
                $credentialFile = $logPath . '/faker_user_credentials.txt';
                $content = "Faker Generated User Credentials (" . date('Y-m-d H:i:s') . "):\n";
                foreach ($fakerUserCredentials as $cred) {
                    $content .= "Username: " . $cred['username'] . ", Password: " . $cred['password'] . "\n";
                }
                if (file_put_contents($credentialFile, $content, FILE_APPEND | LOCK_EX)) {
                    $this->stdout("Faker user credentials saved to: {$credentialFile}\n\n", \yii\helpers\Console::FG_YELLOW);
                } else {
                    $this->stderr("Failed to write faker user credentials to: {$credentialFile}\n\n", \yii\helpers\Console::FG_RED);
                }
            }

            if (empty($userIds)) {
                $this->stderr("No users were generated. Aborting project and task generation.\n", \yii\helpers\Console::FG_RED);
                $transaction->rollBack();
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $this->stdout("Generating Projects...\n");
            $projectIds = [];
            foreach ($userIds as $userId) {
                for ($j = 0; $j < $faker->numberBetween(1, $projectPerUserCount); $j++) {
                    $project = new Project();
                    $project->name = $faker->bs . ' Project';
                    $project->description = $faker->realText(200);
                    $project->created_by = $userId;
                    $project->created_at = time();
                    $project->updated_at = time();
                    if ($project->save()) {
                        $projectIds[] = $project->id;
                        $this->stdout("Generated Project: {$project->name} (ID: {$project->id}) for User ID: {$userId}\n", \yii\helpers\Console::FG_GREEN);
                    } else {
                        $this->stderr("Failed to save project: " . print_r($project->errors, true) . "\n", \yii\helpers\Console::FG_RED);
                    }
                }
            }
            $this->stdout("Generated " . count($projectIds) . " projects.\n\n");

            if (empty($projectIds)) {
                $this->stderr("No projects were generated. Aborting task generation.\n", \yii\helpers\Console::FG_RED);
                // No need to rollback here if users were successfully created and we want to keep them.
                // However, if projects are essential for tasks, it makes sense.
                // For now, let's assume we can proceed without projects if none were made, tasks just won't be created.
            }

            $this->stdout("Fetching Task Priorities and Statuses...\n");
            $taskPriorities = TaskPriority::find()->all();
            $taskPriorityIds = \yii\helpers\ArrayHelper::getColumn($taskPriorities, 'id');
            if (empty($taskPriorityIds)) {
                 $this->stderr("No task priorities found in the database. Please ensure they are seeded.\n", \yii\helpers\Console::FG_RED);
                 $transaction->rollBack();
                 return ExitCode::UNSPECIFIED_ERROR;
            }
            $this->stdout("Found " . count($taskPriorityIds) . " task priorities.\n");

            $taskStatuses = TaskStatus::find()->all();
            $taskStatusIds = \yii\helpers\ArrayHelper::getColumn($taskStatuses, 'id');
            if (empty($taskStatusIds)) {
                 $this->stderr("No task statuses found in the database. Please ensure they are seeded.\n", \yii\helpers\Console::FG_RED);
                 $transaction->rollBack();
                 return ExitCode::UNSPECIFIED_ERROR;
            }
            $this->stdout("Found " . count($taskStatusIds) . " task statuses.\n\n");


            if (!empty($projectIds)) {
                $this->stdout("Generating Tasks...\n");
                $taskCount = 0;
                foreach ($projectIds as $projectId) {
                    for ($k = 0; $k < $faker->numberBetween(1, $taskPerProjectCount); $k++) {
                        $task = new Task();
                        $task->title = $faker->sentence(4);
                        $task->description = $faker->realText(150);
                        $task->project_id = $projectId;

                        // Assign to a random user or leave unassigned
                        $task->assigned_to = $faker->boolean(75) ? $faker->randomElement($userIds) : null;

                        $task->priority_id = $faker->randomElement($taskPriorityIds);
                        $task->status_id = $faker->randomElement($taskStatusIds);
                        $task->due_date = $faker->dateTimeBetween('now', '+1 year')->format('Y-m-d H:i:s');
                        $task->created_at = time();
                        $task->updated_at = time();

                        if ($task->save()) {
                            $taskCount++;
                            $this->stdout("Generated Task: '{$task->title}' (ID: {$task->id}) for Project ID: {$projectId}\n", \yii\helpers\Console::FG_GREEN);
                        } else {
                            $this->stderr("Failed to save task: " . print_r($task->errors, true) . "\n", \yii\helpers\Console::FG_RED);
                        }
                    }
                }
                $this->stdout("Generated {$taskCount} tasks.\n\n");
            } else {
                $this->stdout("Skipping task generation as no projects were created.\n\n");
            }

            $transaction->commit();
            $this->stdout("Fake data generation completed successfully.\n", \yii\helpers\Console::FG_GREEN);
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->stderr("An error occurred: " . $e->getMessage() . "\n", \yii\helpers\Console::FG_RED);
            $this->stderr($e->getTraceAsString() . "\n", \yii\helpers\Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
