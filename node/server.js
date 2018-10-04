var fs = require('fs');
var app = require('express')();
var redis = require('redis');

// Load config
var config = require('./config.json');

// Crete server instance
var instace = (config.ssl ? require('https') : require('http'));


// Server
var options = (config.ssl ?  {
   key: fs.readFileSync(config.key), // path_to/privkey.pem
   cert: fs.readFileSync(config.cert) // path_to/fullchain.pem
} : { });

// Http(s) server
var serverPort = config.serverPort;
var serverName = config.serverIp;
var server = (config.ssl  ? instance.createServer(options, app).listen(serverPort, serverName)
: instance.createServer(app).listen(serverPort, serverName));

// Sockekt.IO
var io = require('socket.io')(server);
io.on('connection', function (socket) {

    // Get user id
    const uid = parseInt(socket.handshake.query['uid']);
    
    if(uid === 0) {
        console.log('Invalid query');
        socket.close();
        return;
    }
    
    console.log('New client connected: ' + uid);
    
    // Connect to redis
    const redisPort = config.redisPort;
    const redisName = config.redisIp;
    const redisAuth = config.redisAuth; // Redis password
    var redisClient = redis.createClient(redisPort, redisName);
    if(redisAuth !== '') {
        redisClient.auth(redisAuth);
    }
    
    const NEW_DIALOG_CHANNEL = 'dialog';
    const DIALOG_SEEN_CHANNEL = 'dialog_seen';
    const NEW_MESSAGE_CHANNEL= 'message';
    const MESSAGE_SEEN_CHANNEL = 'message_seen';
    
    // Subscribe
    redisClient.subscribe(NEW_DIALOG_CHANNEL);
    redisClient.subscribe(DIALOG_SEEN_CHANNEL);
    redisClient.subscribe(NEW_MESSAGE_CHANNEL);
    redisClient.subscribe(MESSAGE_SEEN_CHANNEL);

    redisClient.on('message', function(channel, message) {
        var obj = JSON.parse(message);
        if(channel === NEW_DIALOG_CHANNEL) {
            console.log('New dialog: ' + obj.id 
                    + '. Created By: ' + obj.createdBy
                    + '. Target Id: ' + obj.targetId
                    + '. In channel: ' + channel
                    + '. My uid is : ' + uid);
            if(parseInt(obj.targetId) === uid) {
                socket.emit(channel, obj.id);
            }
        } else if(channel === DIALOG_SEEN_CHANNEL) {
            console.log('Dialog seen: ' + obj.dialog.id 
                    + '. Created By: ' + obj.dialog.createdBy
                    + '. Targer ID: ' + obj.dialog.targetId
                    + '. Notify By: ' + obj.notifyBy
                    + '. In channel: ' + channel
                    + '. My uid is : ' + uid);
            if((parseInt(obj.dialog.createdBy) === uid 
                    || parseInt(obj.dialog.targetId) === uid)
                    && parseInt(obj.notifyBy) !== uid ) {
                socket.emit(channel, obj.dialog.id);
            }
        } else if(channel === NEW_MESSAGE_CHANNEL) {
            console.log('New message: ' + obj.id
                    + '. In channel: ' + channel
                    + '. Resipient ID: ' + obj.recipientId
                    + '. Dialog ID: ' + obj.dialogId
                    + '. My uid is : ' + uid);
            if(parseInt(obj.recipientId) === uid) {
                socket.emit(channel, obj.id);
            }
        } else if(channel === MESSAGE_SEEN_CHANNEL) {
            console.log('Message seen: ' + obj.id
                    + '. Dialog ID: ' + obj.dialogId
                    + '. Sender ID: ' + obj.senderId
                    + '. Recipient ID: ' + obj.recipientId
                    + '. In channel: ' + channel
                    + '. My uid is : ' + uid);
            if(parseInt(obj.senderId) === uid) {
                socket.emit(channel, { dialogId: obj.dialogId, messageId: obj.id });
            }
        }
    });


    socket.on('disconnect', function() {
        console.log('Client disconected: ' + uid);
        redisClient.quit();
    });

});
