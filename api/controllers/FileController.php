<?php

namespace api\controllers;

use api\modules\core\base\ApiController;
use common\repository\storage\SettingStorageRepository;

class FileController extends ApiController
{
    const ERROR_INCORRECT_FIELDS = 1000;

    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    public function actionGetFileLink()
    {
        $settingStorageRepository = new SettingStorageRepository();

        $link = $settingStorageRepository->findValue('linkOnFile');

        $error = $settingStorageRepository->findValue('failCreateFile');

        if (!empty($error)) {

            $this->addData(['fail' => true, 'success' => false]);
        }

        if (!is_null($link)) {

            $this->addData(['link' => $link, 'success' => true]);
        }
    }
}