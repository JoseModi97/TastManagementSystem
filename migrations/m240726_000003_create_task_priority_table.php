<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_priority}}`.
 */
class m240726_000003_create_task_priority_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task_priority}}', [
            'id' => $this->primaryKey(),
            'label' => $this->string()->notNull(),
            'weight' => $this->integer()->defaultValue(0), // Default weight for ordering
        ]);

        // Seed initial data
        $this->batchInsert('{{%task_priority}}', ['label', 'weight'], [
            ['Low', 10],
            ['Medium', 20],
            ['High', 30],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%task_priority}}');
    }
}
