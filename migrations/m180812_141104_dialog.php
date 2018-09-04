<?php

use freelancerua\yii2\chat\migrations\Migration;

/**
 * Class m180812_141104_dialog
 */
class m180812_141104_dialog extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%dialog}}', [
            'id' => $this->primaryKey(),
            'created_by' => $this->integer()->notNull()->comment('User ID who had initialized dialog'),
            'target_id' => $this->integer()->notNull()->comment('User ID with witch dialog is started'),
            'last_message_id' => $this->integer()->notNull(),
            'target_view' => $this->smallInteger()->notNull()->defaultValue(0)
                ->comment('View a dialog for target user even if there is no message in'),
            'remove_by_creator' => $this->smallInteger()->notNull()->defaultValue(0),
            'remove_by_target' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
        
        // Create creator id index
        $this->createIndex('idx-dialog-created_by', '{{%dialog}}', [
            'created_by'
        ], false);

        // Add creator foreign key with user table
        $this->addForeignKey('fk-dialog-created_by',
            '{{%dialog}}', ['created_by'],
            $this->userTable, ['id'], 'CASCADE');
        
        // Create target id index
        $this->createIndex('idx-dialog-target_id', '{{%dialog}}', [
            'target_id'
        ], false);

        // Add target foreign key with user table
        $this->addForeignKey('fk-dialog-target_id',
            '{{%dialog}}', ['target_id'],
            $this->userTable, ['id'], 'CASCADE');
        
        // Create last message id index
        $this->createIndex('idx-dialog-last_message_id', '{{%dialog}}', [
            'last_message_id'
        ], false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-dialog-last_message_id', '{{%dialog}}');
        $this->dropForeignKey('fk-dialog-target_id', '{{%dialog}}');
        $this->dropIndex('idx-dialog-target_id', '{{%dialog}}');
        $this->dropForeignKey('fk-dialog-created_by', '{{%dialog}}');
        $this->dropIndex('idx-dialog-created_by', '{{%dialog}}');
        $this->dropTable('{{%dialog}}');
    }
}
