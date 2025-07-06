<?php

namespace app\rbac;

use yii\rbac\Rule;
use app\models\Project;
use app\models\Task;

/**
 * Checks if authorID matches user passed via params.
 * The item (Project or Task) whose `created_by` attribute is being checked should be passed in $params.
 * For example: Yii::$app->user->can('updateOwnProject', ['project' => $projectModel]);
 */
class AuthorRule extends Rule
{
    public $name = 'isAuthor'; // Name of the rule

    /**
     * @param string|int $user the user ID.
     * @param \yii\rbac\Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     *                      It is expected that $params an array containing the model instance,
     *                      e.g., ['project' => $projectModel] or ['task' => $taskModel] or ['model' => $genericModel]
     * @return bool a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        $model = null;
        if (isset($params['project']) && $params['project'] instanceof Project) {
            $model = $params['project'];
        } elseif (isset($params['task']) && $params['task'] instanceof Task) {
            // For a task, the 'isAuthor' rule typically refers to the author of the project
            // the task belongs to, as tasks themselves don't have a 'created_by' field in our schema.
            $model = $params['task']->project;
        } elseif (isset($params['model']) && isset($params['model']->created_by)) {
            // Generic fallback if a model with created_by is passed directly
            // This could be useful if the rule is applied to other types of models in the future.
            $model = $params['model'];
        }

        if (!$model) {
            // If no relevant model is passed, or the model type is unexpected, deny access.
            return false;
        }

        // Check if the model has a created_by attribute
        // This check is vital because $task->project might be null if the relation failed or task has no project
        if (!isset($model->created_by)) {
            return false;
        }

        return $model->created_by == $user;
    }
}
