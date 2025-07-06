<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%task_priority}}".
 *
 * @property int $id
 * @property string $label
 * @property int|null $weight
 */
class TaskPriority extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%task_priority}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['label'], 'required'],
            [['weight'], 'integer'],
            [['label'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'label' => 'Label',
            'weight' => 'Weight',
        ];
    }

    /**
     * Get all priorities as an array suitable for dropdowns (id => label).
     * @return array
     */
    public static function getPriorityList()
    {
        return ArrayHelper::map(static::find()->orderBy('weight ASC, label ASC')->all(), 'id', 'label');
    }
}
