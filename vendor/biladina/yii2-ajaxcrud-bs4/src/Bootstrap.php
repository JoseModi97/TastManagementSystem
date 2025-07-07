<?php

namespace yii2ajaxcrud\ajaxcrud;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;

/**
 * @author John Martin <john.itvn@gmail.com>
 * @since 1.0
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        Yii::setAlias("@ajaxcrud", __DIR__);
        Yii::setAlias("@yii2ajaxcrud/ajaxcrud", __DIR__);
        if ($app->hasModule('gii'))
        {
            if (!isset($app->getModule('gii')->generators['ajaxcrud']))
            {
                $app->getModule('gii')->generators['ajaxcrud'] = 'yii2ajaxcrud\ajaxcrud\generators\Generator';
            }
        }
    }

}
