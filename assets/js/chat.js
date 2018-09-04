/*
 * This file is part of the yii2-chat project.
 *
 * yii2-chat project <https://bitbucket.org/freelancerua/yii2-chat/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(document).ready(function() {
       
    
    const store = new Vuex.Store({
        state: {
            dialogs: []
        },
        getters: {
            // Get max last message id
            fakeId: function(state) {
                var id = 0;
                state.dialogs.forEach(function(el)  {
                    if(el.lastMessageId > id) {
                        id = el.lastMessageId;
                    }
                });
                return (id + 1);
            }
        },
        mutations: {
            // Set dialogs at all
            set: function(state, dialogs) {
                state.dialogs = dialogs;
            },
            // Add message to dialog
            message: function(state, data) {
                var message = data.message;
                // Find dialog
                var dialog = state.dialogs.find(function(el) {
                    return el.id === message.dialogId;
                }); if(!dialog) return;
                
                // Set unreadCount to +1 if dialog is not selected
                if(parseInt(dialog.id) !== parseInt(data.selectedDialogId)) {
                    dialog.unreadCount = parseInt(dialog.unreadCount) +  1;
                }
                
                dialog.lastMessageId = message.id;
                dialog.lastMessage = message.text;
                dialog.lastMessageDate = message.date;
                dialog.messages.push(message);
            },
            // Set message sent
            setSend: function(state, data) {
                // Find dialog
                var dialog = state.dialogs.find(function(el) {
                    return el.id === data.dialogId;
                }); if(!dialog) return;
                
                // Find message
                var message = dialog.messages.find(function(el) {
                   return el.id === data.fakeId;
                }); if(!message) return;
                
                // Set data
                dialog.lastMessageId = data.id;
                message.id = data.id;
                message.sent = true;
                message.date = moment.unix(data.time).format(window.f_jsDataFormat);
            },
            // Set message error
            setError: function(state, data) {
                // Find dialog
                var dialog = state.dialogs.find(function(el) {
                    return el.id === parseInt(data.dialogId);
                }); if(!dialog) return;
                
                // Find message
                var message = dialog.messages.find(function(el) {
                   return el.id === parseInt(data.fakeId);
                }); if(!message) return;
                
                // Set data
                message.error = true;
                message.date = moment().format(window.f_jsDataFormat);
            },
            // Set message seen
            setSeen: function(state, data) {
                // Find dialog
                var dialog = state.dialogs.find(function(el) {
                    return el.id === parseInt(data.dialogId);
                }); if(!dialog) return;
                
                // Find message
                var message = dialog.messages.find(function(el) {
                   return el.id === parseInt(data.messageId);
                }); if(!message) return;
                
                message.seen = true;
                message.date = moment().format(window.f_jsDataFormat);
            },
            // Set every message (not seen) in dialog as seen
            setDialogSeen: function(state, dialogId) {
                // Find dialog
                var dialog = state.dialogs.find(function(el) {
                    return el.id === parseInt(dialogId);
                }); if(!dialog) return;
                // Filter not seen messages
                var messages = dialog.messages.filter(message => (!message.seen
                        || message.seen === 'false'
                        || message.seen === 0
                        || message.seen === '0'));
                // Set each message as seen
                messages.forEach(function(el){
                   el.seen = true; 
                });
            },
            // Add new dialog
            dialog: function(state, data) {
                state.dialogs.push(data);
            }
        }
    });
    
    const data = {
        uid: window.f_uid, // Set from Yii
        formText: '',
        searchDialog: '',
        awaitScrollUp: true,
        selectedDialog: {
            id: 0,
            name: ''
        }
    };
    
    const chat = new Vue({
        el: '#f-yii2-chat',
        store,
        data: data,
        created: function () {
            // Set messages
            if(window.f_dialogs && window.f_dialogs.length > 0) {
                this.$store.commit('set', window.f_dialogs);
                var dialog = null;
                if(window.f_default > 0) {
                    dialog = this.$store.state.dialogs.find(function(el) {
                        return el.createdBy === window.f_default
                            || el.targetId === window.f_default;
                    }); 
                }
                if(!dialog) { dialog = this.$store.state.dialogs[0]; }
                this.selectedDialog.id = dialog.id;
                this.selectedDialog.name = dialog.name;
            }
            this.$nextTick(function () {
                // Scroll messagesto last
                var objDiv = document.getElementById("f-message-list-container");
                objDiv.scrollTop = objDiv.scrollHeight;
                this.awaitScrollUp = false;
            });
        },
        updated: function () {
            this.$nextTick(function () {
                // Scroll messages to last if view awaiting
                if(this.awaitScrollUp) {
                    var objDiv = document.getElementById("f-message-list-container");
                    objDiv.scrollTop = objDiv.scrollHeight;
                    this.awaitScrollUp = false;
                }
            });
        },
        computed: {
            messages: function() {
                if(this.$store.state.dialogs.length > 0) {
                    var dialog = this.$store.state.dialogs.find(function(el) {
                        return el.id === this.selectedDialog.id;
                    }.bind(this)); if(!dialog) return [];
                    return dialog.messages;
                }
                return [];
            },
            dialogs: function() {
                return this.$store.state.dialogs.filter(dialog => {
                    return dialog.name.toLowerCase().includes(this.searchDialog.toLowerCase());
                }).sort((a,b) => {
                    return b.lastMessageId - a.lastMessageId;
                });;
            },
            isSendDisabled: function() {
                return (this.formText.length === 0);
            }
        },
        methods: {
            // Change selected dialog on click
            onDialogClick: function(item, event) {
                if(item.id !== this.selectedDialog.id) {
                    this.selectedDialog.id = item.id;
                    this.selectedDialog.name = item.name;
                    
                    // Send dialog seen notification
                    if(item.unreadCount > 0) {
                        // It's important to set this headers for post request
                        const headers = {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': yii.getCsrfToken()
                        };
                        var requestData = new URLSearchParams();
                        requestData.append('id', item.id);
                        requestData.append(yii.getCsrfParam(), yii.getCsrfToken());
                        // Send request
                        axios({
                           method: 'POST',
                           url: window.f_dialogSeenUrl, 
                           headers: headers,
                           data: requestData
                        })
                        .then(function(response) {
                            // do nothing
                        })
                        .catch(function(error) {
                           console.log(error);
                        });
                    }
                    
                    // Clear unread counter
                    item.unreadCount = 0;
                    // Scroll message list to last message here !!!
                    this.awaitScrollUp = true;
                }
            },
            // Send new message to dialog
            onSubmitClick: function(event) {
                var self = this;
                
                if(self.formText.length === 0) {
                    console.log('Message empty');
                    return;
                }
                // Clean text
                self.formText = self.formText.trim();
                
                // Cretate message and push to storage
                const fakeId = self.$store.getters.fakeId;
                var message = {
                    id: fakeId,
                    dialogId: self.selectedDialog.id,
                    senderId: self.uid,
                    text: self.formText.replace(/(?:\r\n|\r|\n)/g, '<br>'),
                    date: moment().format(window.f_jsDataFormat),
                    seen: false,
                    sent: false,
                    error: false
                };
                self.awaitScrollUp = true;
                self.$store.commit('message', {
                    message: message,
                    selectedDialogId: self.selectedDialog.id,
                });
                
                // Build data
                var requestData = new URLSearchParams();
                requestData.append('dialogId', self.selectedDialog.id);
                requestData.append('text', self.formText);
                requestData.append('fakeId', fakeId);
                requestData.append(yii.getCsrfParam(), yii.getCsrfToken());
                
                // It's important to set this headers
                const headers = {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': yii.getCsrfToken()
                };
                // Send request
                axios({
                   method: 'POST',
                   url: window.f_sendUrl, 
                   headers: headers,
                   data: requestData
                })
                .then(function(response) {
                    self.$store.commit('setSend', response.data);
                }.bind(self))
                .catch(function(error) {
                    var data = JSON.parse('{"' 
                        + error.config.data.replace(/&/g, '","').replace(/=/g,'":"') + '"}', 
                        function(key, value) { 
                            return key=== "" ? value:decodeURIComponent(value);
                        }
                    );
                    self.$store.commit('setError', data);
                }.bind(self));
                
                // Clear form text
                self.formText = '';
                
                // Do not send form
                if (event) {
                    event.preventDefault();
                }
            }
        }
    });
    
    // Socket listener
    const socket = io.connect(window.f_socketAddress, {
        query: 'uid=' + window.f_uid
    });
    
    // Listen new dialog
    socket.on('dialog', function (data) {
        const dialogId = parseInt(data);
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        axios({
            method: 'GET',
            url: window.f_dialogUrl + dialogId, 
            headers: headers
        })
        .then(function(response) {
            chat.$store.commit('dialog', response.data);
            // If dialog is first
            if(chat.$store.state.dialogs.length === 1) {
                chat.selectedDialog.id = response.data.id;
                chat.selectedDialog.name = response.data.name;
            }
        }.bind(chat))
        .catch(function(error) {
            console.log(error);
        });
    });
    
    // Listen new message
    socket.on('message', function(data) {
        const messageId = parseInt(data);
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        axios({
            method: 'GET',
            url: window.f_messageUrl + messageId, 
            headers: headers
        })
        .then(function(response) {
            chat.$store.commit('message', {
                message: response.data, 
                selectedDialogId: chat.selectedDialog.id
            });
            chat.awaitScrollUp = true;
            // If message chat is active now, send notification about seen
            if(response.data.dialogId === chat.selectedDialog.id) {
                // It's important to set this headers for post request
                const headers = {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': yii.getCsrfToken()
                };
                var requestData = new URLSearchParams();
                requestData.append('id', response.data.id);
                requestData.append(yii.getCsrfParam(), yii.getCsrfToken());
                // Send request
                axios({
                   method: 'POST',
                   url: window.f_messageSeenUrl, 
                   headers: headers,
                   data: requestData
                })
                .then(function(response) {
                    // do nothing
                })
                .catch(function(error) {
                   console.log(error);
                });
            } // else {
              //   chat.$store.commit('incrementUnread', response.data.dialogId);
            // }
        }.bind(chat))
        .catch(function(error) {
            console.log(error);
        });
    });
    
    // Listen message seen
    socket.on('message_seen', function(data) {
        chat.$store.commit('setSeen', data);
    }.bind(chat));
    
    // Listen dialog seen
    socket.on('dialog_seen', function(data) {
        console.log(data); 
        chat.$store.commit('setDialogSeen', data);
    }.bind(chat));
});
