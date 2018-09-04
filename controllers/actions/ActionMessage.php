<?php

/*
 * This file is part of the yii2-chat project.
 *
 * yii2-chat project <https://bitbucket.org/freelancerua/yii2-chat/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace freelancerua\yii2\chat\controllers\actions;

use Yii;
use yii\base\Action;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class ActionMessage extends Action
{
    use traits\MessageTrait;
    
    /**
    * Get message by id
    * @param integer $id Message id
    */
    public function run($id)
    {
        // $uid = Yii::$app->user->id;
        $request = Yii::$app->request;
        if($request->isAjax) {
            // Get message
            $message = $this->_findMessage($id);
            
            // Send response as JSON
            $response = Yii::$app->getResponse();
            $response->format = Response::FORMAT_JSON;
            $response->data = $message->toArray();
            return $response;
        }
        
        throw new BadRequestHttpException('Only AJAX request allowed.');
    }
}
