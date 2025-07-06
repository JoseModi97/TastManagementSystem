<?php

namespace app\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\TaskHistory;
use app\models\User;
use app\models\TaskStatus;
use app\models\TaskPriority;

class TaskChangeBehavior extends Behavior
{
    public $attributesToTrack = [
        'title',
        'description',
        'assigned_to',
        'priority_id',
        'status_id',
        'due_date'
    ];

    // To map attribute names to their respective label-providing relations
    private $attributeRelationMap = [
        'assigned_to' => 'assignedTo', // Relation name in Task model
        'priority_id' => 'priority',   // Relation name in Task model
        'status_id'   => 'status',     // Relation name in Task model
    ];

    // To map attribute names to the specific property of the related model that holds the label
    private $relationLabelPropertyMap = [
        'assigned_to' => 'username', // e.g., $task->assignedTo->username
        'priority_id' => 'label',    // e.g., $task->priority->label
        'status_id'   => 'label',    // e.g., $task->status->label
    ];


    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            // ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert', // For creation event if needed
        ];
    }

    public function afterUpdate($event)
    {
        /** @var \app\models\Task $owner */
        $owner = $this->owner;
        $changedAttributes = $event->changedAttributes; // Attributes that were actually changed

        foreach ($this->attributesToTrack as $attribute) {
            if (array_key_exists($attribute, $changedAttributes)) {
                $history = new TaskHistory();
                $history->task_id = $owner->id;
                $history->user_id = Yii::$app->user && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
                $history->attribute = $attribute;

                $oldValue = $changedAttributes[$attribute];
                $newValue = $owner->{$attribute};

                $history->old_value = is_array($oldValue) || is_object($oldValue) ? json_encode($oldValue) : (string)$oldValue;
                $history->new_value = is_array($newValue) || is_object($newValue) ? json_encode($newValue) : (string)$newValue;

                // Handle labels for FKs
                if (isset($this->attributeRelationMap[$attribute])) {
                    $relationName = $this->attributeRelationMap[$attribute];
                    $labelProperty = $this->relationLabelPropertyMap[$attribute];

                    // Old Label: Need to fetch the related model based on the old ID
                    if ($oldValue !== null) {
                        $relatedModelClass = $owner->getRelation($relationName)->modelClass;
                        $oldRelatedModel = $relatedModelClass::findOne($oldValue);
                        if ($oldRelatedModel && isset($oldRelatedModel->{$labelProperty})) {
                            $history->old_value_label = (string)$oldRelatedModel->{$labelProperty};
                        } else {
                             $history->old_value_label = 'N/A (ID: ' . $oldValue . ')';
                        }
                    }

                    // New Label: Can get from the current owner's relation
                    if ($newValue !== null && $owner->{$relationName} && isset($owner->{$relationName}->{$labelProperty})) {
                         $history->new_value_label = (string)$owner->{$relationName}->{$labelProperty};
                    } elseif ($newValue !== null) {
                        $history->new_value_label = 'N/A (ID: ' . $newValue . ')';
                    }
                }

                // Specific handling for due_date formatting if values are not null
                if ($attribute === 'due_date') {
                    if ($oldValue) {
                        $history->old_value = Yii::$app->formatter->asDate($oldValue, 'yyyy-MM-dd');
                    }
                    if ($newValue) {
                        $history->new_value = Yii::$app->formatter->asDate($newValue, 'yyyy-MM-dd');
                    }
                }


                if (!$history->save()) {
                    Yii::error("Failed to save task history for task ID {$owner->id}, attribute {$attribute}: " . print_r($history->errors, true));
                }
            }
        }
    }

    // Example for logging creation event if needed
    /*
    public function afterInsert($event)
    {
        $owner = $this->owner;
        $history = new TaskHistory();
        $history->task_id = $owner->id;
        $history->user_id = Yii::$app->user && !Yii::$app->user->isGuest ? Yii::$app->user->id : null;
        $history->attribute = 'task_created'; // Special attribute name
        $history->new_value = 'Task was created';
        // old_value can be null or some initial state representation

        if (!$history->save()) {
            Yii::error("Failed to save task creation history for task ID {$owner->id}: " . print_r($history->errors, true));
        }
    }
    */
}
