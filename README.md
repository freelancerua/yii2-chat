# Migration
To apply migration set config in console application as following:
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

Follow this instructions to update/setup Node.js to version 10.x
https://github.com/nodesource/distributions#debinstall

Install pm2 manager:
```
npm install pm2 -g
```

Setup Redis
For Ubuntu you can follow this instruction:
https://www.digitalocean.com/community/tutorials/how-to-install-and-configure-redis-on-ubuntu-16-04

Setup and configure Yii2-redis extension:
https://github.com/yiisoft/yii2-redis
