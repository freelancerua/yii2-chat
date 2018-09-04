<?php

/*
 * This file is part of the yii2-chat project.
 *
 * yii2-chat project <https://bitbucket.org/freelancerua/yii2-chat/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace freelancerua\yii2\chat;

use Yii;
use yii\base\Module as BaseModule;
use yii\base\InvalidConfigException;

/**
 * This is the main module class for the yii2-chat.
 *
 * @author Dmytro S. <freelancerua@protonmail.com>
 */
class Module extends BaseModule
{
    /**
     * {@inheritdoc} 
     */
    public $db = 'db';
    
    /**
     * {@inheritdoc}
     */
    public $defaultRoute = 'chat/index';

    /**
     * Default chat assets folder
     * @var string
     */
    public $assets = '@vendor/freelancerua/yii2-chat/assets';
    
    /**
     * Default chat style file
     * @var string
     */
    public $styleFile = 'css/chat.css';
    
    /**
     * Default chat js file
     * @var string
     */
    public $jsFile = 'js/chat.js';
    
    /**
     * App user class
     * @var string
     */
    public $userClass = null;
    
    /**
     * Format message date when send and update state
     * @var string
     */
    public $jsDateFormat = 'DD/MM/YYYY H:mm:ss';
    
    /**
     * Format message date with PHP
     * @var string
     */
    public $phpDateFormat = 'php:d/m/Y H:m:s';
    
    /**
     * Socket IO server address
     * @var string
     */
    public $socketAddress = null;
    
    /**
     * Redis instance name
     * @var type 
     */
    public $redis = 'redis';
    
    /**
     * Redis DB host
     * @var string
     */
    public $redisHost = '127.0.0.1';
    
    /**
     * Redis DB port
     * @var integer
     */
    public $redisPort = 6379;
    
    /**
     * Redis DB
     * @var integer
     */
    public $redisDb = 0;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        // Check user interface 
        if(!(Yii::createObject($this->userClass) instanceof IChatInterface)) {
            throw new InvalidConfigException('User class mast implement IChatInterface.');
        }
        // Check socket address
        if(!$this->socketAddress) {
            throw new InvalidConfigException('Socket address must be set.');
        }
        // Check user class
        if(!$this->userClass) {
            throw new InvalidConfigException('User class must be set.');
        }
        
        // Set translatons
        $this->registerTranslations();
        // Initialize the module with the configuration loaded from config.php
        Yii::configure($this, require __DIR__ . '/config.php');
        // Set components with module level configuration
        Yii::$app->setComponents([
            "{$this->redis}" => [
                'class' => 'yii\redis\Connection',
                'hostname' => $this->redisHost,
                'port' => $this->redisPort,
                'database' => $this->redisDb,
            ],
        ]);
    }

    /**
     * Set translation files for module
     */
    public function registerTranslations()
    {
        Yii::$app->i18n->translations['modules/chat/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@vendor/freelancerua/yii2-chat/messages',
            'fileMap' => [
                'chat' => 'chat.php',
            ],
        ];
    }

    /**
     * Translate module strings
     * @param string $category
     * @param string $message
     * @param array $params
     * @param string $language
     * @return string|null
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('modules/chat/' . $category, $message, $params, $language);
    }
    
    /**
     * @return string
     */
    public function getDb()
    {
        return \Yii::$app->get($this->db);
    }
}
