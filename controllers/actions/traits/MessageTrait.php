<?php

/*
 * This file is part of the yii2-chat project.
 *
 * yii2-chat project <https://bitbucket.org/freelancerua/yii2-chat/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace freelancerua\yii2\chat\controllers\actions\traits;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use freelancerua\yii2\chat\models\Message;

trait MessageTrait
{
    /**
     * Find message by id
     * @param integer $id
     * @return Message
     * @throws NotFoundHttpException
     */
    protected function _findMessage($id)
    {
        $message = Message::findOne((int)$id);
        if(!$message) {
            throw new NotFoundHttpException('Message not found.');
        }
        
        // Check youser has access to dialog
        $this->_checkAccess($message, Yii::$app->user->id);
        
        return $message;
    }
    
    /**
     * Check user has access to message
     * @param Message $message
     * @param integer $uid
     * @throws ForbiddenHttpException
     */
    protected function _checkAccess($message, $uid)
    {
        if($message->sender_id !== $uid && $message->recipient_id !== $uid) {
            throw new ForbiddenHttpException('You have no access to the message.');
        }
    }
}
