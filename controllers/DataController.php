<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl; // Optional: If you want to restrict access

class DataController extends Controller
{
    /**
     * {@inheritdoc}
     */
    // Optional: Add access control if needed
    /*
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Example: only authenticated users
                    ],
                ],
            ],
        ];
    }
    */

    /**
     * Displays the data loading page.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Runs the fake data generation command.
     *
     * @return Response
     */
    public function actionLoadData()
    {
        // Define default parameters for the console command
        $userCount = Yii::$app->request->get('userCount', 10);
        $projectPerUserCount = Yii::$app->request->get('projectPerUserCount', 2);
        $taskPerProjectCount = Yii::$app->request->get('taskPerProjectCount', 5);

        // It's generally safer to run console commands via shell_exec or similar,
        // especially if they produce a lot of output or take a long time.
        // Yii::$app->runAction() can have issues with output buffering and execution time
        // in a web context for long-running console tasks.
        // However, for simplicity in this context, we'll try with runAction first.
        // Ensure your console controller (FakeDataController) is mapped in console/config/main.php

        // Construct the command path. Adjust if your yii script is located elsewhere.
        $yiiCommand = Yii::getAlias('@app/yii'); // Or specific path to your yii script

        // Using shell_exec for better isolation and handling of console output/errors
        $command = "php {$yiiCommand} fake-data/load --userCount={$userCount} --projectPerUserCount={$projectPerUserCount} --taskPerProjectCount={$taskPerProjectCount}";

        // For security, ensure parameters are integers if they come from user input
        $userCountSafe = (int)$userCount;
        $projectPerUserCountSafe = (int)$projectPerUserCount;
        $taskPerProjectCountSafe = (int)$taskPerProjectCount;

        $commandSafe = "php {$yiiCommand} fake-data/load --userCount={$userCountSafe} --projectPerUserCount={$projectPerUserCountSafe} --taskPerProjectCount={$taskPerProjectCountSafe} 2>&1"; // 2>&1 to capture stderr

        $output = [];
        $return_var = null;
        exec($commandSafe, $output, $return_var);

        if ($return_var === 0) {
            Yii::$app->session->setFlash('success', 'Fake data generation process initiated successfully. Output: <pre>' . implode("\n", $output) . '</pre>');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to initiate fake data generation. Return code: ' . $return_var . '. Output: <pre>' . implode("\n", $output) . '</pre>');
        }

        return $this->redirect(['index']);
    }
}
