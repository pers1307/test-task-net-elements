<?php
/**
 * @author bmxnt <bmxnt@mail.ru>
 */

namespace api\controllers;
use api\modules\core\base\ApiController;
use Yii;

class ErrorController extends ApiController {
    public function actionIndex() {
        Yii::$app->response->setStatusCode(404);
        return $this->errorAction(404);
    }

    public function actionForbidden() {
        Yii::$app->response->setStatusCode(403);
        return $this->errorAction(403);
    }
}