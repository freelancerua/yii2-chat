<?php

/*
 * This file is part of the yii2-chat project.
 *
 * yii2-chat project <https://bitbucket.org/freelancerua/yii2-chat/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace freelancerua\yii2\chat\controllers\actions;

use Yii;
use yii\base\Action;
use freelancerua\yii2\chat\models\Dialog;
use freelancerua\yii2\chat\events\DialogEvent;
use yii\web\BadRequestHttpException;
use freelancerua\yii2\chat\controllers\ChatController;
use yii\helpers\Json;
use yii\web\UnprocessableEntityHttpException;
use freelancerua\yii2\chat\Module;

//Class-level event

class ActionIndex extends Action
{
    /**
     * Redis channel  to notify about seen dialog
     * @var string
     */
    public $channel = 'dialog_seen';
    
   /**
    * Get chat view with data
    * @param integer $id User id to start dialog
    */
    public function run($id = 0)
    {
        $uid = Yii::$app->user->id;
        // Check if dialog need to be created
        if($id > 0 && $uid !== $id && !$this->_hasDialogWithUserId($id, $uid)) {
            // Trigger event 
            $this->trigger(ChatController::EVENT_DIALOG_BEFORE_CREATE, 
                    new DialogEvent(['dialog' => null]));
            // Try to create new dialog
            if(!($dialog = Dialog::createEmpty($uid, $id))) {
                throw new BadRequestHttpException('Cannot start new dialog.');
            }
            // Trigger event
            $this->trigger(ChatController::EVENT_DIALOG_AFTER_CREATE, 
                    new DialogEvent(['dialog' => $dialog]));
        }
        
        // Get all dialogs
        $dialogs = [];
        $query = Dialog::find()->where(['OR',
            ['target_id' => $uid],
            ['created_by' => $uid]
        ])->orderBy(['last_message_id' => SORT_DESC]);
        
        foreach ($query->all() as $index => $item) {
            // Find selected(default) dialog dialog. 
            // It can be not only first dialog in array
            // So find it and set as read
            if((($id === 0 && $index === 0)
                   || ($item->target_id === $id || $item->created_by === $id))
                   && ($item->myUnreadCount > 0)) {
                // Set seen
                if(!$item->setSeen()) {
                    throw new UnprocessableEntityHttpException('Filed to set deafault dialog messages as seen.');
                }
                
                //Notify over redis
                $notificationData = Json::encode([
                    'dialog' => $item->toArray(['id', 'createdBy', 'targetId']), 
                    'notifyBy' => $uid
                ]);
                Yii::$app->{Module::getInstance()->redis}->executeCommand('PUBLISH', [
                    'channel' => $this->channel,
                    'message' => $notificationData,
                ]);
            }
            
            // Convert dialog to array and expand(calculate) fields()
            $dialogs[] = $item->toArray();
        }
        
        return $this->controller->render('index', [
            'dialogs' => Json::encode($dialogs),
            'uid' => $uid,
            'default' => $id,
        ]);
    }
    
    /**
     * Check if dialog exist
     * @param integer $id User id
     * @param integer $uid Current user id
     */
    protected function _hasDialogWithUserId($id, $uid)
    {
        return Dialog::find()
                ->where(['created_by' => $id, 'target_id' => $uid])
                ->orWhere(['created_by' => $uid, 'target_id' => $id])
                ->count() > 0;
    }
}
