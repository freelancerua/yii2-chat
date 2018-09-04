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
use yii\web\BadRequestHttpException;
use yii\web\UnprocessableEntityHttpException;
use freelancerua\yii2\chat\models\Message;
use RuntimeException;
use yii\web\Response;
use yii\helpers\HtmlPurifier;
use yii\helpers\Json;
use freelancerua\yii2\chat\Module;
use freelancerua\yii2\chat\controllers\ChatController;
use freelancerua\yii2\chat\events\MessageEvent;

class ActionSend extends Action
{
    use traits\DialogTrait;
    
    /**
     * Redis channel to notify about new message
     * @var string
     */
    public $channel = 'message';
    
   /**
    * Send message
    */
    public function run()
    {
        $uid = Yii::$app->user->id;
        $request = Yii::$app->request;
        if($request->isAjax) {
            // Get dialog
            $dialog = $this->_findDialog($request->post('dialogId'));
            
            // Get message text and check is not empty
            $messageText = $request->post('text');
            if(!$messageText) {
                throw new UnprocessableEntityHttpException('Message text cannot be empty.');
            }
            
            // Create new message
            $message = new Message();
            $message->sender_id = $uid;
            $message->recipient_id = ($dialog->created_by === $uid) 
                    ? $dialog->target_id
                    : $dialog->created_by;
            $message->seen = false;
            $message->remove_by_recipient = false;
            $message->remove_by_sender = false;
            $message->dialog_id = $dialog->id;
            $message->body = HtmlPurifier::process($messageText);
            
            // Save data in transaction
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $this->trigger(ChatController::EVENT_MESSAGE_BEFORE_SEND,
                        new MessageEvent(['message' => $message]));
                if(!$message->save()) {
                    throw new RuntimeException('Save message error.');
                }
                // Set last message id
                $dialog->last_message_id = $message->id;
                if(!$dialog->save()) {
                     throw new RuntimeException('Update dialog error.');
                }
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
            $this->trigger(ChatController::EVENT_MESSAGE_AFTER_SEND,
                        new MessageEvent(['message' => $message]));
            
            // Send notification over Redis
            $notificationData = Json::encode($message->toArray(['id', 'dialogId'],
                    ['recipientId']));
            Yii::$app->{Module::getInstance()->redis}->executeCommand('PUBLISH', [
                'channel' => $this->channel,
                'message' => $notificationData,
            ]);
            
            // Send response as JSON
            $response = Yii::$app->getResponse();
            $response->format = Response::FORMAT_JSON;
            $response->data = [
                'id' => $message->id, 
                'fakeId' => intval($request->post('fakeId')),
                'dialogId' => $dialog->id,
                'time' => $message->created_at,
            ];
            return $response;
        }
        
        throw new BadRequestHttpException('Only AJAX request allowed.');
    }
}
