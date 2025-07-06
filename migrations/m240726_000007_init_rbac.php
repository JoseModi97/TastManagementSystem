<?php

use yii\db\Migration;

/**
 * Initializes RBAC tables.
 */
class m240726_000007_init_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $authManager = Yii::$app->authManager;
        $this->assertNotNull($authManager, 'authManager is not configured.');
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // auth_rule
        $this->createTable($authManager->ruleTable, [
            'name' => $this->string(64)->notNull(),
            'data' => $this->binary(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY ([[name]])',
        ], $tableOptions);

        // auth_item
        $this->createTable($authManager->itemTable, [
            'name' => $this->string(64)->notNull(),
            'type' => $this->smallInteger()->notNull(),
            'description' => $this->text(),
            'rule_name' => $this->string(64),
            'data' => $this->binary(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY ([[name]])',
            'FOREIGN KEY ([[rule_name]]) REFERENCES ' . $authManager->ruleTable . ' ([[name]])' .
                ($this->db->driverName === 'sqlite' ? '' : ' ON DELETE SET NULL ON UPDATE CASCADE'),
        ], $tableOptions);
        $this->createIndex('idx-auth_item-type', $authManager->itemTable, 'type');

        // auth_item_child
        $this->createTable($authManager->itemChildTable, [
            'parent' => $this->string(64)->notNull(),
            'child' => $this->string(64)->notNull(),
            'PRIMARY KEY ([[parent]], [[child]])',
            'FOREIGN KEY ([[parent]]) REFERENCES ' . $authManager->itemTable . ' ([[name]])' .
                ($this->db->driverName === 'sqlite' ? '' : ' ON DELETE CASCADE ON UPDATE CASCADE'),
            'FOREIGN KEY ([[child]]) REFERENCES ' . $authManager->itemTable . ' ([[name]])' .
                ($this->db->driverName === 'sqlite' ? '' : ' ON DELETE CASCADE ON UPDATE CASCADE'),
        ], $tableOptions);

        // auth_assignment
        $this->createTable($authManager->assignmentTable, [
            'item_name' => $this->string(64)->notNull(),
            'user_id' => $this->integer()->notNull(), // Changed to integer to match user.id type
            'created_at' => $this->integer(),
            'PRIMARY KEY ([[item_name]], [[user_id]])',
            'FOREIGN KEY ([[item_name]]) REFERENCES ' . $authManager->itemTable . ' ([[name]])' .
                ($this->db->driverName === 'sqlite' ? '' : ' ON DELETE CASCADE ON UPDATE CASCADE'),
            // Add direct FK to user table as our user IDs are integers
             'FOREIGN KEY ([[user_id]]) REFERENCES {{%user}} ([[id]])' .
                ($this->db->driverName === 'sqlite' ? '' : ' ON DELETE CASCADE ON UPDATE CASCADE'),
        ], $tableOptions);
        // Index on user_id is already part of PK or covered by FK for some DBs, but explicit can be good.
        // $this->createIndex('idx-auth_assignment-user_id', $authManager->assignmentTable, 'user_id'); // This index might be redundant if user_id is part of PK with item_name.

        // For SQLite, enable foreign key support if not already enabled
        if ($this->db->driverName === 'sqlite') {
            $this->execute('PRAGMA foreign_keys = ON;');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $authManager = Yii::$app->authManager;
        $this->assertNotNull($authManager, 'authManager is not configured.');

        $this->dropTable($authManager->assignmentTable);
        $this->dropTable($authManager->itemChildTable);
        $this->dropTable($authManager->itemTable);
        $this->dropTable($authManager->ruleTable);
    }
}
