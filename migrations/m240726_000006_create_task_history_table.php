<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_history}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%task}}`
 * - `{{%user}}`
 */
class m240726_000006_create_task_history_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task_history}}', [
            'id' => $this->primaryKey(),
            'task_id' => $this->integer()->notNull(),
            'user_id' => $this->integer(), // Nullable if system change or user deleted
            'changed_at' => $this->integer()->notNull(),
            'attribute' => $this->string()->notNull(),
            'old_value' => $this->text(),
            'new_value' => $this->text(),
            'old_value_label' => $this->text(), // For human-readable old values of FKs
            'new_value_label' => $this->text(), // For human-readable new values of FKs
        ]);

        // creates index for column `task_id`
        $this->createIndex(
            '{{%idx-task_history-task_id}}',
            '{{%task_history}}',
            'task_id'
        );

        // add foreign key for table `{{%task}}`
        $this->addForeignKey(
            '{{%fk-task_history-task_id}}',
            '{{%task_history}}',
            'task_id',
            '{{%task}}',
            'id',
            'CASCADE' // If task is deleted, its history is also deleted
        );

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-task_history-user_id}}',
            '{{%task_history}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-task_history-user_id}}',
            '{{%task_history}}',
            'user_id',
            '{{%user}}',
            'id',
            'SET NULL' // If user is deleted, history record remains but user_id is set to null
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%task}}`
        $this->dropForeignKey(
            '{{%fk-task_history-task_id}}',
            '{{%task_history}}'
        );

        // drops index for column `task_id`
        $this->dropIndex(
            '{{%idx-task_history-task_id}}',
            '{{%task_history}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-task_history-user_id}}',
            '{{%task_history}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-task_history-user_id}}',
            '{{%task_history}}'
        );

        $this->dropTable('{{%task_history}}');
    }
}
