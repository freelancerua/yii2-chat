<?php

/*
 * This file is part of the yii2-chat project.
 *
 * yii2-chat project <https://bitbucket.org/freelancerua/yii2-chat/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace freelancerua\yii2\chat\controllers\actions\traits;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use freelancerua\yii2\chat\models\Dialog;

trait DialogTrait
{
   /**
     * Find dialog by id
     * @param integer $id
     * @return Dialog
     * @throws NotFoundHttpException
     */
    protected function _findDialog($id)
    {
        $dialog = Dialog::findOne((int)$id);
        if(!$dialog) {
            throw new NotFoundHttpException('Dialog not found.');
        }
        
        // Check youser has access to dialog
        $this->_checkAccess($dialog, Yii::$app->user->id);
        
        return $dialog;
    }
    
    /**
     * Check user has access to dialog
     * @param Dialog $dialog
     * @param integer $uid
     * @throws ForbiddenHttpException
     */
    protected function _checkAccess($dialog, $uid)
    {
        if($dialog->created_by !== $uid && $dialog->target_id !== $uid) {
            throw new ForbiddenHttpException('You have no access to the dialog.');
        }
    }
}
