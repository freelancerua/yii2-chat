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

class ChatAsset extends AssetBundle
{
    /**
     * {@inheritdoc}
     */
    public function init() 
    {
        $this->sourcePath = Module::getInstance()->assets;
        $this->css[] = Module::getInstance()->styleFile;
        $this->css[] = 'css/fontello.css';
        $this->css[] = 'css/animation.css';
        $this->js[] = Module::getInstance()->jsFile;
        parent::init();
    }
    
    /**
     * {@inheritdoc}
     */
    public $depends = [
        // ComponentsAsset already require jQuery
        'freelancerua\yii2\chat\ComponentsAsset',
    ];
    
    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
}
