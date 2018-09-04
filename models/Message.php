<?php

/*
 * This file is part of the yii2-chat project.
 *
 * yii2-chat project <https://bitbucket.org/freelancerua/yii2-chat/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace freelancerua\yii2\chat\models;

use Yii;
use freelancerua\yii2\chat\models\Dialog;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord as BaseActiveRecord;
use freelancerua\yii2\chat\Module;

/**
 * This is the message model class for the yii2-chat.
 *
 * @property integer $id
 * @property integer $dialog_id 
 * @property integer $sender_id
 * @property integer $recipient_id
 * @property string $body
 * @property boolean $seen
 * @property boolean $remove_by_sender
 * @property booealn $remove_by_recipient
 * @property integer $created_at
 * @property integer $updated_at
 * @property Dialog $dialog
 * @property User $sender
 * @property user $recipient
 * @preperty String $date Formated date of dialog (created_at)
 * @preopery boolean $error Always false if message created
 * @property boolean $sent Always true if message created 
 * @property string $preBody nl2br() text
 * 
 * @author Dmytro S. <freelancerua@protonmail.com>
 */
class Message extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function behaviors() 
    {
        return [
            TimestampBehavior::class,
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'message';
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDialog()
    {
        return $this->hasOne(Dialog::class, ['id' => 'dialog_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSender()
    {
        return $this->hasOne(Module::getInstance()->userClass, ['id' => 'sender_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecipient()
    {
        return $this->hasOne(Module::getInstance()->userClass, ['id' => 'recipient_id']);
    }
    
    /**
     * Formated created date as string
     * @return string
     */
    public function getDate()
    {
        return Yii::$app->formatter->asDate($this->created_at, 
                Module::getInstance()->phpDateFormat);
    }
    
    /**
     * Saved message sent always
     * @return boolean
     */
    public function getSent()
    {
        return true;
    }
    
    /**
     * Saved message has no errors
     * @return boolean
     */
    public function getError()
    {
        return false;
    }

    /**
     * Replace all new line to <br> in text
     * @return string
     */
    public function getPreBody()
    {
        return nl2br($this->body);
    }
    
    /**
     * {@inheritdoc}
     */
    public function fields() 
    {
        return [
            'id',
            'dialogId' => 'dialog_id',
            'senderId' => 'sender_id',
            'text' => 'preBody',
            'date',
            'seen',
            'sent',
            'error',
        ];
    }
    
    /**
     * If $userId not set method will User context!
     * Set all message where user recipient seen
     * @param integer $dialogId Dialog id
     * @param integer $userId
     * @return boolean
     */
    public static function setSeenAll($dialogId = 0, $userId = 0) 
    {
        $condition = [
            'recipient_id' => (($userId > 0)
                ? $userId
                : Yii::$app->user->id),
            'seen' => 0,
        ];
        
        if($dialogId > 0) {
            $condition['dialog_id'] = $dialogId;
        }
        
        return (self::updateAll(['seen' => 1], ['and', $condition]) >= 0);
    }
    
    /**
     * {@inheritdoc}
     */
    public function extraFields() 
    {
        return [
            'recipientId' => 'recipient_id',
        ];
    }
    
    /**
     * User context function!
     * Get count unread messages when Yii::$app->user
     * is recipient of this message.
     * @return integer
     */
    public static function unreadCount()
    {
        return self::find()->where([
            'recipient_id' => Yii::$app->user->id,
            'seen' => 0,
        ])->count();
    }
}
