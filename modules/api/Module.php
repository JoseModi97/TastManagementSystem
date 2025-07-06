<?php

namespace app\modules\api;

/**
 * api module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\api\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Custom initialization code goes here
        // For example, to use JSON for request parsing and response formatting for the entire module:
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        // \Yii::$app->request->parsers = [
        //     'application/json' => 'yii\web\JsonParser',
        // ];

        // It's often better to set response format and request parsers
        // in a base API controller's behaviors or init method to avoid
        // affecting other parts of the application if this module is accessed via web normally.
        // However, for a dedicated API module, this can be a global setting.
    }
}
