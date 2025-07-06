<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;
use app\models\User;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'User Management';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-management-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'username',
            'email:email',
            [
                'attribute' => 'roles',
                'label' => 'Roles',
                'value' => function ($model) {
                    /** @var User $model */
                    return $model->getRoleNames();
                },
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
            ],
            // 'updated_at:datetime',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{assign-role}',
                'buttons' => [
                    'assign-role' => function ($url, $model, $key) {
                        return Html::a('Assign Role', ['assign-role', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-primary',
                            'title' => 'Assign/Update Role',
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>

</div>
