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
use yii\db\ActiveRecord as BaseActiveRecord;
use yii\behaviors\TimestampBehavior;
use freelancerua\yii2\chat\Module;
use freelancerua\yii2\chat\models\Message;

/**
 * This is the dialog model class for the yii2-chat.
 * 
 * @property integer $id
 * @property integer $created_by
 * @property integer $target_id
 * @property integer $last_message_id
 * @property boolean $target_view
 * @property boolean $remove_by_creator
 * @property boolean $remove_by_target
 * @property integer $created_at
 * @property integer $updated_at
 * @property User $target
 * @property User $creator
 * @property Message[] $messages
 * @property Message $lastMessage
 * @property string $lastMessageText
 * @property string $lastMessageDate
 * @property integer $myUnreadCount Unread message count (User context)
 * @property string $oppositeName Opposite user name (User context)
 * @property string $oppositeImage Opposite user image (User context)
 * @property boolean $oppositeIsOnline Is opposite user online (User context)
 * 
 * @author Dmytro S. <freelancerua@protonmail.com>
 */
class Dialog extends BaseActiveRecord
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
        return '{{%dialog}}';
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTarget() 
    {
        return $this->hasOne(Module::getInstance()->userClass,
                ['id' => 'target_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreator() 
    {
        return $this->hasOne(Module::getInstance()->userClass,
                ['id' => 'created_by']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(Message::class, ['dialog_id' => 'id']);
    }
    
    /**
     * User context method!
     * @return \yii\db\ActiveQuery
     */
    public function getOppositeName()
    {
        return $this->_opposite()->chatName;
    }
    
    /**
     * User context method!
     * @return \yii\db\ActiveQuery
     */
    public function getOppositeImage()
    {
        return $this->_opposite()->chatImage;
    }
    
    
    /**
     * User context method!
     * Get unread messages count
     * @return integer
     */
    public function getMyUnreadCount()
    {
        return $this->getMessages()
                ->andWhere(['recipient_id' => Yii::$app->user->id])
                ->andWhere(['seen' => 0])->count();
    }
    
    /**
     * Is opposite user online or not
     * @return boolean
     */
    public function getOppositeIsOnline()
    {
        return boolval($this->_opposite()->isOnline);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLastMessage()
    {
        return $this->hasOne(Message::class,
                ['id' => 'last_message_id']);
    }

    /**
     * Last message date
     * @return string
     */
    public function getLastMessageDate() 
    {
        return Yii::$app->formatter->asDate(
                $this->lastMessage->created_at ?? 0,
                Module::getInstance()->phpDateFormat);
    }
    
    /**
     * Last message
     * @return string
     */
    public function getLastMessageText() 
    {
        return $this->lastMessage->body ?? Module::t('chat', 'empty');
    }
    
    /**
     * Get opposite user
     * @return User
     */
    protected function _opposite() 
    {
        return (($this->target_id === Yii::$app->user->id)
                ? $this->creator
                : $this->target); 
    }
    
    /**
     * User context method!
     * Set dialog message as seen
     * @return boolean
     */
    public function setSeen()
    {
        return Message::setSeenAll($this->id);
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return [
            'id',
            'createdBy' => 'created_by',
            'targetId' => 'target_id',
            'name' => 'oppositeName',
            'img' => 'oppositeImage',
            'messages',
            'lastMessageId' => 'last_message_id',
            'lastMessage' => 'lastMessageText',
            'lastMessageDate',
            'unreadCount' => 'myUnreadCount',
            'online' => 'oppositeIsOnline',
        ];
    }
    
    /**
     * Create an empty dialog
     * @param integer $uid
     * @param integer $targer
     * @return Dialog|null
     */
    public static function createEmpty($uid, $targer)
    {
        // Check $target user exist and not banned
        $user = call_user_func_array([Module::getInstance()->userClass, 'findOne'], [$uid]);
        if(!$user) { return null; }
        
        $dialog  = new Dialog();
        $dialog->created_by = $uid;
        $dialog->target_id = $targer;
        $dialog->last_message_id = 0;
        $dialog->target_view = true;
        $dialog->remove_by_creator = false;
        $dialog->remove_by_target = false;
        
        return $dialog->save() ? $dialog : null;
    }
    
    /**
     * Get active(first) dialog id
     * @return integer
     */
    public static function getActiveDialogId()
    {
        $dialog = self::find()
                ->orderBy(['last_message_id' => SORT_DESC])
                ->one();
        
        return ($dialog ? $dialog->id : 0);
    }
}
