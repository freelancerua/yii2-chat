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
 * Class m180812_141114_block_list
 */
class m180812_141114_block_list extends Migration
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
        
        $this->createTable('{{%block_list}}', [
            'id' => $this->primaryKey(),
            'blocker_id' => $this->integer()->notNull(),
            'blocked_id' => $this->integer()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
        ], $tableOptions);
        
        // Create blocker id index
        $this->createIndex('idx-block_list-status', '{{%block_list}}', [
            'status'
        ], false);
        
        // Create blocker id index
        $this->createIndex('idx-block_list-blocker_id', '{{%block_list}}', [
            'blocker_id'
        ], false);

        // Add blocker foreign key with user table
        $this->addForeignKey('fk-block_list-blocker_id',
            '{{%block_list}}', ['blocker_id'],
            $this->userTable, ['id'], 'CASCADE');
        
        // Create blocked id index
        $this->createIndex('idx-block_list-blocked_id', '{{%block_list}}', [
            'blocked_id'
        ], false);

        // Add blocked foreign key with user table
        $this->addForeignKey('fk-block_list-blocked_id',
            '{{%block_list}}', ['blocked_id'],
            $this->userTable, ['id'], 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-block_list-blocked_id', '{{%block_list}}');
        $this->dropIndex('idx-block_list-blocked_id','{{%block_list}}');
        $this->dropForeignKey('fk-block_list-blocker_id', '{{%block_list}}');
        $this->dropIndex('idx-block_list-blocker_id','{{%block_list}}');
        $this->dropIndex('idx-block_list-status','{{%block_list}}');
        $this->dropTable('{{%block_list}}');
    }
}
