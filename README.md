# Installation
Add to composer.json
```
"freelancerua/yii2-chat": "@dev"
```

# Configuration
1. Apply migrations. To apply migration set config in console application as following:
```
'controllerMap' => [
    // Migrations for the yii2-chat extension
    'migrate-chat' => [
        'class' => \freelancerua\yii2\chat\migrations\MigrationController::class,
        'migrationNamespaces' => ['freelancerua\yii2\chat\migrations'],
        'migrationPath' => null,
        'migrationTable' => 'migration_chat',
        'userTable' => '{{%user}}', // Change it if user table has different name  
    ],
],
```
Now you can run:
```
./yii migrate-chat/up
```

2. Setup Redis. For Ubuntu you can follow this instruction:
https://www.digitalocean.com/community/tutorials/how-to-install-and-configure-redis-on-ubuntu-16-04

3. Configure yii2-chat extension:
```
modules' => [
    'chat' => [
        'class' => \freelancerua\yii2\chat\Module::class,
        'socketAddress' => 'http://[domain|IP]:[port]' // Required
        ...
    ],
],
```

4. Follow this instructions to update/setup Node.js to version 10.x:
```
https://github.com/nodesource/distributions#debinstall
```

5. Install pm2 manager:
```
npm install pm2 -g
```

6. In terminal go to 
```
[@vendor]/freelancerua/yii2-chat/node/
```

7. Update modules:
```
npm update
```

8. Copy config.example.json to config.json and setup confg values:
```
{
    "serverPort": 8890,
    "serverIP": "[IP]", // Exact as your domain IP or IP set in socketAddress
    "redisPort": 6379,
    "redisName": "127.0.0.1",
    "redisAuth": null
}
```

9. Start and add a process to the pm2 process list:
```
pm2 start [@vendor]/freelancerua/yii2-chat/node/server.js --name yii2-chat-server
```
