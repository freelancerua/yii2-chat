<?php

/*
 * This file is part of the yii2-chat project.
 *
 * yii2-chat project <https://bitbucket.org/freelancerua/yii2-chat/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace freelancerua\yii2\chat\events;

use yii\base\Event as BaseEvent;

/**
 * This is the message event class for the yii2-chat.
 *
 * @author Dmytro S. <freelancerua@protonmail.com>
 */
class MessageEvent extends BaseEvent
{
    /**
     * @var freelancerua\yii2\chat\models\Message 
     */
    private $__message;
    
    /**
     * @return freelancerua\yii2\chat\models\Message
     */
    function getMessage() 
    {
        return $this->__message;
    }
    
    /**
     * @param freelancerua\yii2\chat\models\Message $message
     */
    function setMessage($message) 
    {
        $this->__message = $message;
    }
}
