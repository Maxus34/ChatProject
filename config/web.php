<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'layout' => 'app_layout',
    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ],
        'chat' => [
            'class' => 'app\modules\chat\Module',
            'defaultRoute' => 'default/index',
            'layout' => 'chat_layout',
            'components' => [

            ]
        ],
    ],
	'components' => [
        'request' => [
			'baseUrl' => '',
            'cookieValidationKey' => 'qazxswedcdcfvriudcushkcsulkvlvrevrege',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['user/login'],
        ],
        'authManager' => [
            'class' => '\yii\rbac\DbManager',
            'defaultRoles' => ['guest']
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host'  => 'smtp.gmail.com',
                'username' => 'mxs34post@gmail.com',
                'password' => 'Mxs34SecretPassword',
                'port'     => '587',
                'encryption' => 'tls'
            ],
            'useFileTransport' => false,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),    
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [

                'chat/<id:\d+>' => '/chat/default/view',
                'chat' => '/chat/default',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
		'allowedIPs' => ['::1', '127.0.0.1', '192.168.33.*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
		'allowedIPs' => ['::1', '127.0.0.1', '192.168.33.*'],
    ];
}

return $config;
