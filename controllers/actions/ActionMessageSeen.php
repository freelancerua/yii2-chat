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
use yii\web\Response;
use yii\helpers\Json;
use freelancerua\yii2\chat\Module;
use yii\web\UnprocessableEntityHttpException;

class ActionMessageSeen extends Action
{
    use traits\MessageTrait;
    
    /**
     * Redis channel to notify about seen message
     * @var string
     */
    public $channel = 'message_seen';

    /**
    * {@inheritdoc}
    */
    public function run()
    {
        // $uid = Yii::$app->user->id;
        $request = Yii::$app->request;
        $id = (int) $request->post('id');
        if($request->isAjax) {
            // Get message
            $message = $this->_findMessage($id);
            
            $message->seen = 1;
            if(!$message->save()) {
                throw new UnprocessableEntityHttpException('Message change \'seen\' state failed.');
            }
            
            // Send notification over Redis
            $notificationData = Json::encode($message->toArray(['id', 'dialogId', 'senderId'],
                    ['recipientId']));
            Yii::$app->{Module::getInstance()->redis}->executeCommand('PUBLISH', [
                'channel' => $this->channel,
                'message' => $notificationData,
            ]);
            
            // Send response as JSON
            $response = Yii::$app->getResponse();
            $response->format = Response::FORMAT_JSON;
            $response->data = [
                'success' => true,
            ];
            return $response;
        }
        
        throw new BadRequestHttpException('Only AJAX request allowed.');
    }
}
