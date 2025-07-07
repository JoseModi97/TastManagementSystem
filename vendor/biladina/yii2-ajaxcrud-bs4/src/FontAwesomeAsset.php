<?php

namespace yii2ajaxcrud\ajaxcrud;

use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@vendor/fortawesome/font-awesome';

    public $css = [
        'css/all.min.css',
    ];
    
    public $js = [
        'js/all.min.js'
    ];
}