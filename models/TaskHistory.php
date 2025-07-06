<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\models\User;
use app\models\Task;

/**
 * This is the model class for table "{{%task_history}}".
 *
 * @property int $id
 * @property int $task_id
 * @property int|null $user_id
 * @property int $changed_at
 * @property string $attribute
 * @property string|null $old_value
 * @property string|null $new_value
 * @property string|null $old_value_label
 * @property string|null $new_value_label
 *
 * @property Task $task
 * @property User $user
 */
class TaskHistory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%task_history}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'changed_at',
                'updatedAtAttribute' => false, // No 'updated_at' field for history records
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['task_id', 'attribute'], 'required'],
            [['task_id', 'user_id', 'changed_at'], 'integer'],
            [['old_value', 'new_value', 'old_value_label', 'new_value_label'], 'string'],
            [['attribute'], 'string', 'max' => 255],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::class, 'targetAttribute' => ['task_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => 'Task ID',
            'user_id' => 'Changed By',
            'changed_at' => 'Changed At',
            'attribute' => 'Attribute Changed',
            'old_value' => 'Old Value',
            'new_value' => 'New Value',
            'old_value_label' => 'Old Label',
            'new_value_label' => 'New Label',
        ];
    }

    /**
     * Gets query for [[Task]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Returns a human-readable description of the change.
     * @return string
     */
    public function getChangeDescription()
    {
        // Ensure Html helper is used if this method is called in a view context directly
        // For now, assuming it might be used in other contexts, so avoiding direct Html::encode here.
        // Consider Html::encode in the view when displaying.
        $userDisplay = $this->user ? $this->user->username : 'System';
        $changedAt = Yii::$app->formatter->asDatetime($this->changed_at);
        // $attributeLabel = $this->attributeLabels()[$this->attribute] ?? $this->attribute;
        $readableAttribute = str_replace('_id', '', $this->attribute); // Basic readability for FKs
        $readableAttribute = str_replace('_', ' ', $readableAttribute);
        $readableAttribute = ucwords($readableAttribute);


        $oldDisplay = $this->old_value_label ?: ($this->old_value ?: 'empty');
        // Ensure 'nothing' is used if new_value and new_value_label are both empty/null
        $newDisplay = $this->new_value_label ?: ($this->new_value ?: 'empty');
        if (empty(trim((string)$this->new_value_label)) && empty(trim((string)$this->new_value))) {
            $newDisplay = 'empty';
        }


        return "{$userDisplay} changed {$readableAttribute} from '{$oldDisplay}' to '{$newDisplay}' on " . Yii::$app->formatter->asDate($this->changed_at) . ".";
    }
}
