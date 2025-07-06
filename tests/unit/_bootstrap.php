<?php
// tests/unit/_bootstrap.php

// Set up environment for unit tests
defined('YII_ENV') or define('YII_ENV', 'test');
defined('YII_DEBUG') or define('YII_DEBUG', true);

// Adjust the paths according to your project structure.
// These paths assume this _bootstrap.php is in tests/unit/
require_once(dirname(__DIR__, 2) . '/vendor/autoload.php'); // Corrected path to vendor/autoload.php
require_once(dirname(__DIR__, 2) . '/vendor/yiisoft/yii2/Yii.php'); // Corrected path to yii2/Yii.php

// Set Yii class aliases for the 'tests' namespace to help with autoloading, especially for fixtures.
// This makes sure @tests points to the correct directory.
Yii::setAlias('@tests', dirname(__DIR__));
// @app should point to your application's root directory (where composer.json, web, models, etc. are)
Yii::setAlias('@app', dirname(__DIR__, 2));

// The Codeception Yii2 module (configured in unit.suite.yml and codeception.yml)
// should handle the actual application instantiation using the config specified
// in codeception.yml (modules: config: Yii2: configFile: 'config/test.php').
// This bootstrap's main job is to ensure Yii basic environment (like YII_ENV, YII_DEBUG)
// and core aliases (@tests, @app) are set up before Codeception's Yii2 module kicks in.

// If you still face issues with Yii::$app being null or components not configured,
// it might indicate a problem with how Codeception Yii2 module loads 'config/test.php'
// or with the contents of 'config/test.php' itself.
// Ensure 'config/test.php' correctly merges 'config/test_db.php' and sets up
// essential components like 'db', 'user', 'security', and 'authManager'.

// For example, to ensure the security component is available for password hashing in User model tests:
// Make sure 'config/test.php' has a 'components' => ['security' => [...]] section.
// It usually inherits this from the main config which is then overridden by test-specific settings.

?>
