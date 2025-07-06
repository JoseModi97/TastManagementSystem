<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%project}}`
 * - `{{%user}}`
 * - `{{%task_priority}}`
 * - `{{%task_status}}`
 */
class m240726_000005_create_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'description' => $this->text(),
            'project_id' => $this->integer()->notNull(),
            'assigned_to' => $this->integer(), // Can be null if unassigned
            'priority_id' => $this->integer()->notNull(),
            'status_id' => $this->integer()->notNull(),
            'due_date' => $this->dateTime(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        // Create indexes for foreign keys
        $this->createIndex('{{%idx-task-project_id}}', '{{%task}}', 'project_id');
        $this->createIndex('{{%idx-task-assigned_to}}', '{{%task}}', 'assigned_to');
        $this->createIndex('{{%idx-task-priority_id}}', '{{%task}}', 'priority_id');
        $this->createIndex('{{%idx-task-status_id}}', '{{%task}}', 'status_id');

        // Add foreign keys
        $this->addForeignKey('{{%fk-task-project_id}}', '{{%task}}', 'project_id', '{{%project}}', 'id', 'CASCADE');
        $this->addForeignKey('{{%fk-task-assigned_to}}', '{{%task}}', 'assigned_to', '{{%user}}', 'id', 'SET NULL'); // If user is deleted, task becomes unassigned
        $this->addForeignKey('{{%fk-task-priority_id}}', '{{%task}}', 'priority_id', '{{%task_priority}}', 'id', 'RESTRICT'); // Prevent deleting priority if in use
        $this->addForeignKey('{{%fk-task-status_id}}', '{{%task}}', 'status_id', '{{%task_status}}', 'id', 'RESTRICT'); // Prevent deleting status if in use
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign keys
        $this->dropForeignKey('{{%fk-task-project_id}}', '{{%task}}');
        $this->dropForeignKey('{{%fk-task-assigned_to}}', '{{%task}}');
        $this->dropForeignKey('{{%fk-task-priority_id}}', '{{%task}}');
        $this->dropForeignKey('{{%fk-task-status_id}}', '{{%task}}');

        // Drop indexes
        $this->dropIndex('{{%idx-task-project_id}}', '{{%task}}');
        $this->dropIndex('{{%idx-task-assigned_to}}', '{{%task}}');
        $this->dropIndex('{{%idx-task-priority_id}}', '{{%task}}');
        $this->dropIndex('{{%idx-task-status_id}}', '{{%task}}');

        $this->dropTable('{{%task}}');
    }
}
