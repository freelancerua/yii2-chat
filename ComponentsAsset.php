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

use Yii;
use yii\web\AssetBundle;

class ComponentsAsset extends AssetBundle
{
    /**
     * {@inheritdoc}
     */
    public $sourcePath = '@bower';
    
    /**
     * {@inheritdoc}
     */
    public $js = [
        'socket.io-client/dist/socket.io.slim.js',
        'vue/dist/vue.min.js',
        'vuex/dist/vuex.min.js',
        'axios/dist/axios.min.js',
        'momentjs/min/moment.min.js',
    ];
    
    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'only' => [
            'socket.io-client/dist/socket.io.slim.js',
            'socket.io-client/dist/socket.io.slim.js.map',
            'vue/dist/vue.min.js',
            'vuex/dist/vuex.min.js',
            'axios/dist/axios.min.js',
            'axios/dist/axios.min.map',
            'momentjs/min/moment.min.js',
        ]
    ];
    
    /**
     * {@inheritdoc}
     */
    public $depends = [
        // YiiAsset already require jQuery
        'yii\web\YiiAsset',
    ];
}
