<?php

/*
 * This file is part of the yii2-chat project.
 *
 * yii2-chat project <https://bitbucket.org/freelancerua/yii2-chat/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use freelancerua\yii2\chat\migrations\Migration;

/**
 * Class m180812_141109_message
 */
class m180812_141109_message extends Migration
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
        
        $this->createTable('{{%message}}', [
            'id' => $this->primaryKey(),
            'dialog_id' => $this->integer()->notNull()->comment('Target dialog id'),
            'sender_id' => $this->integer()->notNull()->comment('User ID who had sent message'),
            'recipient_id' => $this->integer()->notNull()->comment('User ID to which message sent'),
            'body' => $this->binary(),
            'seen' => $this->smallInteger()->defaultValue(0),
            'remove_by_sender' => $this->smallInteger()->notNull()->defaultValue(0),
            'remove_by_recipient' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
        
        // Create seen index
        $this->createIndex('idx-message-seen', '{{%message}}', [
            'seen'
        ], false);
        
        // Create dialog id index
        $this->createIndex('idx-message-dialog_id', '{{%message}}', [
            'dialog_id'
        ], false);

        // Add creator foreign key with user table
        $this->addForeignKey('fk-message-dialog_id',
            '{{%message}}', ['dialog_id'],
            '{{%dialog}}', ['id'], 'CASCADE');
        
        // Create sender id index
        $this->createIndex('idx-message-sender_id', '{{%message}}', [
            'sender_id'
        ], false);

        // Add sender foreign key with user table
        $this->addForeignKey('fk-message-sender_id',
            '{{%message}}', ['sender_id'],
            $this->userTable, ['id'], 'CASCADE');
        
        // Create recipient id index
        $this->createIndex('idx-message-recipient_id', '{{%message}}', [
            'recipient_id'
        ], false);

        // Add recipient foreign key with user table
        $this->addForeignKey('fk-message-recipient_id',
            '{{%message}}', ['recipient_id'],
            $this->userTable, ['id'], 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-message-recipient_id', '{{%message}}');
        $this->dropIndex('idx-message-recipient_id','{{%message}}');
        $this->dropForeignKey('fk-message-sender_id', '{{%message}}');
        $this->dropIndex('idx-message-sender_id','{{%message}}');
        $this->dropForeignKey('fk-message-dialog_id', '{{%message}}');
        $this->dropIndex('idx-message-dialog_id','{{%message}}');
        $this->dropIndex('idx-message-seen','{{%message}}');
        $this->dropTable('{{%message}}');
    }
}
