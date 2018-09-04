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

class ActionDialog extends Action
{
    use traits\DialogTrait;
    
    /**
    * Get dialog by id
    * @param integer $id Dialog id
    */
    public function run($id)
    {
        // $uid = Yii::$app->user->id;
        $request = Yii::$app->request;
        if($request->isAjax) {
            // Get Dialog
            $dialog = $this->_findDialog($id);
            
            // Send response as JSON
            $response = Yii::$app->getResponse();
            $response->format = Response::FORMAT_JSON;
            $response->data = $dialog->toArray();
            return $response;
        }
        
        throw new BadRequestHttpException('Only AJAX request allowed.');
    }
}
