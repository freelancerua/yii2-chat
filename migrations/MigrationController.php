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

use Yii;
use yii\console\controllers\MigrateController as BaseMigrateController;
use yii\helpers\Console;

/**
 * This is the migration controller class for the yii2-chat.
 * 
 * @property string $userTable User table name ( default {{%user}} )
 *
 * @author Dmytro S. <freelancerua@protonmail.com>
 */
class MigrationController extends BaseMigrateController
{
    /**
     * @var string user table name 
     */
    public $userTable = '{{%user}}';
    
    /**
     * {@inheritdoc}
     */
    public $migrationTable = '{{%migration_chat}}';
    
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action) && $this->_checkUserTable()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function createMigration($class)
    {
        $this->includeMigrationFile($class);

        return Yii::createObject([
            'class' => $class,
            'db' => $this->db,
            'compact' => $this->compact,
            'userTable' => $this->userTable,
        ]);
    }
    
    /**
     * Check user table exist
     * @return boolean
     */
    protected function _checkUserTable()
    {
        if(Yii::$app->db->getTableSchema($this->userTable, true) === null) {
            $this->stdout(sprintf("User table %s not exist!\n", $this->userTable), Console::FG_RED);
            return false;
        }
        
        return true;
    }
}
