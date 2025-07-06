<?php

namespace tests\unit\fixtures;

use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
    public $modelClass = 'app\models\User';
    // public $dataFile = '@tests/unit/fixtures/data/user.php'; // Path to your data file
    // By convention, if $dataFile is not set, it will look for a file named 'user.php' (table name)
    // in the directory specified by yii\test\Fixture::dataPath, which defaults to @tests/fixtures/data
    // or for unit tests, it might be @tests/unit/fixtures/data. Let's be explicit.

    public function beforeLoad()
    {
        parent::beforeLoad();
        // Yii::$app->db->createCommand()->checkIntegrity(false)->execute(); // For some DBs if FK constraints cause issues
    }

    public function afterLoad()
    {
        parent::afterLoad();
        // Yii::$app->db->createCommand()->checkIntegrity(true)->execute();

        // After loading fixtures, if RBAC is involved and you need default roles assigned
        // for these test users, you might assign them here.
        // However, for pure model unit tests, this might be out of scope.
        // For LoginFormTest or SignupFormTest, this might be more relevant if done in _bootstrap or a base test class.
        /*
        $auth = Yii::$app->authManager;
        $userRole = $auth->getRole('user');
        if ($userRole) {
            foreach ($this->data as $alias => $row) {
                if (!$auth->checkAccess($row['id'], 'user')) { // Check if not already assigned (e.g. by signup)
                    $auth->assign($userRole, $row['id']);
                }
            }
        }
        $adminRole = $auth->getRole('admin');
        if ($adminRole && isset($this->data['adminUser']['id'])) {
             if (!$auth->checkAccess($this->data['adminUser']['id'], 'admin')) {
                $auth->assign($adminRole, $this->data['adminUser']['id']);
            }
        }
        */
    }

    // If your user.php data file is in tests/unit/fixtures/data/user.php
    // and your fixture path configuration in unit.suite.yml or codeception.yml
    // doesn't automatically point there, you might need to override dataPath.
    // public function getDataPath()
    // {
    //     return Yii::getAlias('@tests/unit/fixtures/data');
    // }

    // Or, more commonly, set $dataFile directly:
    // public function init()
    // {
    //     parent::init();
    //     // $this->dataFile = Yii::getAlias('@tests/unit/fixtures/data/user.php');
    // }
    // Let's try setting $dataFile as a public property to see if it changes instantiation timing issues.
    // And use a more direct relative path approach for dataFile if Yii aliases are problematic during early instantiation.
    public $dataFile = __DIR__ . '/data/user.php';
}
