<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'name' => 'TMIS', // Added application name
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        'gridview' => [
            'class' => 'kartik\grid\Module',
            // uncomment the following to enable the export menu
            'exportConfig' => [
                \kartik\grid\GridView::CSV => [
                    'label' => 'CSV',
                    'icon' => 'file-text',
                    'iconOptions' => ['class' => 'text-primary'],
                    'showHeader' => true,
                    'showPageSummary' => true,
                    'showFooter' => true,
                    'showCaption' => true,
                    'filename' => 'export',
                    'alertMsg' => 'The CSV export file will be generated for download.',
                    'options' => ['title' => 'Comma Separated Values'],
                ],
                \kartik\grid\GridView::EXCEL => [
                    'label' => 'Excel',
                    'icon' => 'file-excel',
                    'iconOptions' => ['class' => 'text-success'],
                    'showHeader' => true,
                    'showPageSummary' => true,
                    'showFooter' => true,
                    'showCaption' => true,
                    'filename' => 'export',
                    'alertMsg' => 'The EXCEL export file will be generated for download.',
                    'options' => ['title' => 'Microsoft Excel 95+'],
                ],
                \kartik\grid\GridView::PDF => [
                    'label' => 'PDF',
                    'icon' => 'file-pdf',
                    'iconOptions' => ['class' => 'text-danger'],
                    'showHeader' => true,
                    'showPageSummary' => true,
                    'showFooter' => true,
                    'showCaption' => true,
                    'filename' => 'export',
                    'alertMsg' => 'The PDF export file will be generated for download.',
                    'options' => ['title' => 'Portable Document Format'],
                ],
                \kartik\grid\GridView::TEXT => [
                    'label' => 'Text',
                    'icon' => 'file-text',
                    'iconOptions' => ['class' => 'text-info'],
                    'showHeader' => true,
                    'showPageSummary' => true,
                    'showFooter' => true,
                    'showCaption' => true,
                    'filename' => 'export',
                    'alertMsg' => 'The TEXT export file will be generated for download.',
                    'options' => ['title' => 'Plain Text Format'],
                ],

            ],
        ],
        'api' => [ // Added API module
            'class' => 'app\modules\api\Module',
        ],
    ],
    'components' => [
        'i18n' => [
            'translations' => [
                'yii2-ajaxcrud' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@yii2ajaxcrud/ajaxcrud/messages',
                    'sourceLanguage' => 'en',
                ],
            ]
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'GP0uttJUcL3GQ-5Qlruw3yUxCH-nUokP',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            // uncomment if you want to cache RBAC items (requires cache component)
            // 'cache' => 'cache',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // Standard web rules (if any) would go here or remain if already present
                // Example: '<controller:\w+>/<action:\w+>' => '<controller>/<action>',

                // API v1 rules
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/v1/project' => 'api/project', //  /api/v1/projects routes to api/project controller
                        'api/v1/task'    => 'api/task',    //  /api/v1/tasks routes to api/task controller
                    ],
                    'pluralize' => true, // Default is true, so /api/v1/project maps to 'projects' endpoint
                    'extraPatterns' => [ // Optional: for custom actions not covered by REST standard
                        // 'GET <id>/tasks' => 'tasks', // Example for ProjectController's actionTasks($id)
                    ],
                    // 'tokens' => [ // Optional: if you need to customize <id> pattern
                    //     '{id}' => '<id:\\d[\\d,]*>',
                    // ]
                ],
                // Add other non-API rules below or above the API rule block as needed.
            ],
        ],

    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
