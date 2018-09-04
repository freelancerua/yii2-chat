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

class ActionDialogSeen extends Action
{
    use traits\DialogTrait;
    
    /**
     * Redis channel  to notify about seen dialog
     * @var string
     */
    public $channel = 'dialog_seen';

    /**
    * {@inheritdoc}
    */
    public function run()
    {
        $uid = Yii::$app->user->id;
        $request = Yii::$app->request;
        $id = (int) $request->post('id');
        if($request->isAjax) {
            // Get dialog
            $dialog = $this->_findDialog($id);
            
            // Set seen
            if(!$dialog->setSeen()) {
                throw new UnprocessableEntityHttpException('Update dialog messages as seen failed.');
            }
            
            // Send notification over Redis
            $notificationData = Json::encode([
                'dialog' => $dialog->toArray(['id', 'createdBy', 'targetId']), 
                'notifyBy' => $uid
            ]);
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
