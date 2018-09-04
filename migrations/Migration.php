<?php

/*
 * This file is part of the yii2-chat project.
 *
 * yii2-chat project <https://bitbucket.org/freelancerua/yii2-chat/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace freelancerua\yii2\chat\migrations;

use yii\db\Migration as BaseMigration;

/**
 * This is the migration class for the yii2-chat.
 * 
 * @property string $userTable set by migration controller
 *
 * @author Dmytro S. <freelancerua@protonmail.com>
 */
class Migration extends BaseMigration
{
    public $userTable = null;
}
