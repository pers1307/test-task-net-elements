<?php

return [
    'class' => 'yii\web\UrlManager',
    'baseUrl' => '/api',
    'scriptUrl' => '/api/index.php',
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        'v<ver:\d+>/<controller>.<action>' => 'v<ver>/<controller>/<action>',
        '<controller>.<action>' => '<controller>/<action>',
    ],
];