<?php

/*
 * This file is part of the yii2-chat project.
 *
 * yii2-chat project <https://bitbucket.org/freelancerua/yii2-chat/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace freelancerua\yii2\chat\controllers;

use Yii;
use yii\web\Controller as BaseController;
use yii\filters\VerbFilter;
use freelancerua\yii2\chat\Module;
use yii\helpers\Json;
use yii\filters\AccessControl;

/**
 * This is the main controller class for the yii2-chat.
 *
 * @author Dmytro S. <freelancerua@protonmail.com>
 */
class ChatController extends BaseController
{
    /**
     * Event is triggered before creating new dialog.
     * Triggered with \freelancerua\yii2\chat\events\DialogEvent.
     */
    const EVENT_DIALOG_BEFORE_CREATE = 'dialogBeforeCreate';
    
    /**
     * Event is triggered after creating new dialog.
     * Triggered with \freelancerua\yii2\chat\events\DialogEvent.
     */
    const EVENT_DIALOG_AFTER_CREATE = 'dialogAfterCreate';
    
    /**
     * Event is triggered before sending new message.
     * Triggered with \freelancerua\yii2\chat\events\MessageEvent.
     */
    const EVENT_MESSAGE_BEFORE_SEND = 'messageBeforeSend'; // 'Send' mean that message merely has been save to DB
    
    /**
     * Event is triggered after sending new message.
     * Triggered with \freelancerua\yii2\chat\events\MessageEvent.
     */
    const EVENT_MESSAGE_AFTER_SEND = 'messageAfterSend'; // 'Send' mean that message merely has been save to DB
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['send', 'message', 'message-seen', 'dialog', 'dialog-seen'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'send' => ['POST'],
                    'message' => ['GET'],
                    'message-seen' => ['POST'],
                    'dialog' => ['GET'],
                    'dalog-seen' => ['POST'],
                ],
            ],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function actions() 
    {
        return [
            'index' => [
                'class' => actions\ActionIndex::class,
                'on ' . self::EVENT_DIALOG_AFTER_CREATE => function($event) {
                    /* @var $event \freelancerua\yii2\chat\events\DialogEvent */
                    $notificationData = Json::encode($event->dialog->toArray([
                        'id',
                        'createdBy',
                        'targetId',
                    ]));
                    Yii::$app->{Module::getInstance()->redis}->executeCommand('PUBLISH', [
                        'channel' => 'dialog',
                        'message' => $notificationData,
                    ]);
                },
            ],
            'send' => actions\ActionSend::class,
            'message' => actions\ActionMessage::class,
            'message-seen' => actions\ActionMessageSeen::class,
            'dialog' => actions\ActionDialog::class,
            'dialog-seen' => actions\ActionDialogSeen::class,
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        Yii::$app->user->identity->setOnline();
        
        if(!parent::beforeAction($action)) {
            return false;
        }
        return true;
    }
}
