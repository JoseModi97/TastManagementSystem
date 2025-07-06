<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_status}}`.
 */
class m240726_000004_create_task_status_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task_status}}', [
            'id' => $this->primaryKey(),
            'label' => $this->string()->notNull(),
        ]);

        // Seed initial data
        $this->batchInsert('{{%task_status}}', ['label'], [
            ['To Do'],
            ['In Progress'],
            ['Done'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%task_status}}');
    }
}
