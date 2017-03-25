<?php

$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'core' => 'api\modules\core\Module',
        'v1' => 'api\modules\versions\v1\Module',
    ],
    'components' => [        
//        'user' => [
//            'identityClass' => 'common\entities\User',
//            'enableAutoLogin' => true,
//            'loginUrl' => ['error/forbidden']
//        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],

        'request'=>[
            'baseUrl'=>'/api',
        ],

        'errorHandler' => [
            'errorAction' => 'error/index',
        ],

        'urlManager' => require(__DIR__ . '/urls.php'),
    ],
    'params' => $params,
];



