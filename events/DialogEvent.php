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
 * This is the dialog event class for the yii2-chat.
 *
 * @author Dmytro S. <freelancerua@protonmail.com>
 */
class DialogEvent extends BaseEvent
{
    /**
     * @var freelancerua\yii2\chat\models\Dialog 
     */
    private $__dialog;
    
    /**
     * @return freelancerua\yii2\chat\models\Dialog;
     */
    function getDialog() 
    {
        return $this->__dialog;
    }
    
    /**
     * @param freelancerua\yii2\chat\models\Dialog $dialog
     */
    function setDialog($dialog) 
    {
        $this->__dialog = $dialog;
    }
}
