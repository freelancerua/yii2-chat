<?php

/*
 * This file is part of the yii2-chat project.
 *
 * yii2-chat project <https://bitbucket.org/freelancerua/yii2-chat/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* @var $this yii\web\View */
/* @var $dialogs[] */
/* @var $uid integer */
/* @var $default integer */

use yii\helpers\Html;
use freelancerua\yii2\chat\Module;
use freelancerua\yii2\chat\ChatAsset;
use yii\web\View;
use yii\helpers\Url;

// Build Urls
$moduleId = Module::getInstance()->id;
$sendUrl = Url::to([sprintf('/%s/chat/send', $moduleId)]);  
$dialogUrl = Url::to([sprintf('/%s/chat/dialog', $moduleId), 'id' => '']);
$dialogSeenUrl = Url::to([sprintf('/%s/chat/dialog-seen', $moduleId)]);
$messageUrl = Url::to([sprintf('/%s/chat/message', $moduleId), 'id' => '']);
$messageSeenUrl = Url::to([sprintf('/%s/chat/message-seen', $moduleId)]);
$jsDataFormat = Module::getInstance()->jsDateFormat;
$socketAddress = Module::getInstance()->socketAddress;

// Set init js values
$js = <<<JS
    window.f_uid = $uid;
    window.f_dialogs = $dialogs;
    window.f_sendUrl = '$sendUrl';
    window.f_dialogUrl = '$dialogUrl';
    window.f_dialogSeenUrl = '$dialogSeenUrl';
    window.f_messageUrl = '$messageUrl';
    window.f_messageSeenUrl = '$messageSeenUrl';
    window.f_jsDataFormat = '$jsDataFormat';
    window.f_socketAddress = '$socketAddress';
    window.f_default = $default;
JS;
$this->registerJs($js, View::POS_HEAD);

ChatAsset::register($this);

$this->title = Module::t('chat', 'Chat');
?>
<div id="f-yii2-chat" class="f-yii2-chat">
    <h3 class="hclass">
        <?= Html::encode($this->title) ?>
    </h3>
    <div class="f-chat-container">
        <div class="f-dialog">
            <div class="f-header">
                <div class="f-dialog-search">
                    <?= Html::input('search', 'f-dialog-search', null, [
                        'placeholder' => Module::t('chat', 'Search dialog ...'),
                        'v-model' => 'searchDialog',
                    ]) ?>
                </div>
            </div>
            <div class="f-body">
               <ul>
                    <li v-for="(item, index) in dialogs">
                        <div v-bind:class="[(selectedDialog.id === item.id) ? 'f-selected-dialog' : '']"
                             v-on:click="onDialogClick(item, $event)">
                            <div v-if="item.img !== null" 
                                 class="f-img-wrap f-flex-item">
                                <img v-bind:src="item.img"
                                     class="f-userimage" />
                            </div>
                            <div class="f-dialog-content f-flex-item">
                                <div class="f-dialog-name-wrap">
                                    <span class="f-name">{{ item.name }}</span>
                                    <span v-if="item.online === true" 
                                          class="f-online">&nbsp;</span>
                                </div>
                                <div>
                                    <span class="f-last-message">{{ item.lastMessage }}</span>
                                </div>
                            </div>
                            <span v-if="item.unreadCount > 0" class="f-unread-count">
                                {{ item.unreadCount }}
                            </span>
                        </div>
                    </li>
                    <li class="f-no-dialogs" v-if="this.$store.state.dialogs.length === 0">
                        <p>
                            <?= Module::t('chat', 'You have no dialogs yet') ?>
                        </p>
                    </li>
              </ul>
            </div>
        </div>
        <div class="f-message">
            <div class="f-header">
                <h4>
                    {{ selectedDialog.name }}
                </h4>
            </div>
            <div id="f-message-list-container" class="f-body">
                <ul>
                    <li v-for="(item, index) in messages">
                        <div v-bind:class="[(item.senderId === uid) ? 'f-outgoing' : 'f-incoming']">
                            <p class="f-message-text">
                                <span class="f-message-wrap" v-html="item.text">
                                    {{ item.text }}
                                </span>
                            </p>
                            <div class="f-message-details">
                                <span v-if="(item.senderId === uid) && item.error" 
                                      class="f-message-deatils-error icon-cancel">
                                </span>
                                <span v-if="(item.senderId === uid) && !item.error && !item.sent" 
                                      class="f-message-deatils-not-sent icon-spin6 animate-spin">
                                </span>
                                <span v-if="(item.senderId === uid) && !item.error && item.sent && !item.seen" 
                                      class="f-message-deatils-sent icon-ok">
                                </span>
                                <span v-if="(item.senderId === uid) && !item.error && item.sent && item.seen"
                                      class="f-message-deatils-seen icon-eye">
                                </span>
                                <span class="f-message-deatils-time">
                                    {{ item.date }}
                                </span>
                                <div v-if="item.senderId === uid" class="clearfix"></div>
                            </div>
                        </div>
                    </li>
                    <li class="f-no-message" v-if="messages.length === 0">
                        <p>
                            <?= Module::t('chat', 'This dialog has no message') ?>
                        </p>
                    </li>
                </ul>
            </div>
            <div class="f-footer">
                <form id="f-yii2-chat-form">
                    <textarea id="f-form-content" v-model="formText"
                        placeholder="<?= Module::t('chat', 'Input message here ...') ?>"
                              rows="3" maxlength="1024">
                        
                    </textarea>
                    <button v-bind:disabled="isSendDisabled" v-on:click="onSubmitClick"
                        type="submit"><?= Module::t('chat', 'Send') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
