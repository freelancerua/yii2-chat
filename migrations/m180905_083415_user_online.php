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
class m180905_083415_user_online extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->userTable, 'chat_is_online',
                $this->integer()->defaultValue(0));
    }
    
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->userTable, 'chat_is_online');
    }
}
