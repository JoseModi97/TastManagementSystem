<?php

use app\models\Project;
use yii\helpers\Html;
use yii\helpers\Url;
// use yii\grid\ActionColumn; // Will be replaced by kartik\grid\ActionColumn
// use yii\grid\GridView; // Will be replaced by kartik\grid\GridView
use yii\widgets\Pjax; // Keep Pjax for now, AjaxCrud might manage its own Pjax or work with this one.
use kartik\grid\GridView;
use biladina\ajaxcrud\CrudAsset;
use biladina\ajaxcrud\BulkButtonWidget;

/** @var yii\web\View $this */
/** @var app\models\ProjectSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Projects';
$this->params['breadcrumbs'][] = $this->title;

// Register AjaxCrud assets
CrudAsset::register($this);

?>
<div class="project-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(['id'=>'crud-datatable-pjax']); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'id' => 'crud-datatable',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax'=>true, // Pjax is enabled for kartik\grid\GridView
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            // 'id',
            'name',
            'description:ntext',
            [
                'attribute' => 'created_by',
                'value' => function ($model) {
                    return $model->createdBy ? $model->createdBy->username : null;
                },
            ],
            'created_at:datetime',
            //'updated_at:datetime',
            [
                'class' => 'kartik\grid\ActionColumn',
                'dropdown' => false, // Disable dropdown for actions
                'vAlign'=>'middle',
                'urlCreator' => function($action, $model, $key, $index) {
                        return Url::to([$action,'id'=>$key]);
                },
                'viewOptions'=>['role'=>'modal-remote','title'=>'View','data-toggle'=>'tooltip'],
                'updateOptions'=>['role'=>'modal-remote','title'=>'Update', 'data-toggle'=>'tooltip'],
                'deleteOptions'=>['role'=>'modal-remote','title'=>'Delete',
                                  'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                                  'data-request-method'=>'post',
                                  'data-toggle'=>'tooltip',
                                  'data-confirm-title'=>'Are you sure?',
                                  'data-confirm-message'=>'Are you sure want to delete this item'],
            ],
        ],
        'toolbar'=> [
            ['content'=>
                Html::a('<i class="fas fa-plus"></i> Add Project', ['create'],
                ['role'=>'modal-remote','title'=> 'Create new Projects','class'=>'btn btn-success']) .
                Html::a('<i class="fas fa-redo"></i>', [''],
                ['data-pjax'=>1, 'class'=>'btn btn-outline-secondary', 'title'=>'Reset Grid']).
                '{toggleData}'.
                '{export}'
            ],
        ],
        'striped' => true,
        'condensed' => true,
        'responsive' => true,
        'panel' => [
            'type' => GridView::TYPE_PRIMARY, // Use TYPE_PRIMARY or other constants from GridView
            'heading' => '<i class="fas fa-list"></i> Projects listing',
            'before'=>'<em>* Resize table columns just like a spreadsheet by dragging the column edges.</em>',
            'after'=>BulkButtonWidget::widget([
                        'buttons'=>Html::a('<i class="fas fa-trash"></i>&nbsp; Delete All',
                        ["bulk-delete"] ,
                        [
                            "class"=>"btn btn-danger btn-xs",
                            'role'=>'modal-remote-bulk',
                            'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                            'data-request-method'=>'post',
                            'data-confirm-title'=>'Are you sure?',
                            'data-confirm-message'=>'Are you sure want to delete this item'
                        ]),
                    ]).
                    '<div class="clearfix"></div>',
        ]
    ])?>

    <?php Pjax::end(); ?>

</div>

<?php \kartik\widgets\Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"", // Leave blank since footer is provided by controller via JSON response
    "options" => [ // Additional options for the modal
        "tabindex" => false // important for Select2 to work properly
    ],
])?>
<?php \kartik\widgets\Modal::end(); ?>
