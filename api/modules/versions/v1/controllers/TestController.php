<?php

namespace api\modules\versions\v1\controllers;


class TestController extends \api\controllers\TestController {

    public function actionIndex() {
        $this->addData([
            'version' => 'v1'
        ]);
    }
}