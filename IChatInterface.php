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

interface IChatInterface 
{
    public function getChatImage();
    public function getChatName();
    public function getIsOnline();
    public function setIsOnline();
}
