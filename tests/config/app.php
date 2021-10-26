<?php
$pgsql = [
    'class'=>\yii\db\Connection::class,
    'dsn' => 'pgsql:host=postgres;dbname=testdb',
    'username' => 'dbuser',
    'password' => 'dbpass',
    'charset' => 'utf8',
    //'tablePrefix' => 'test_'
];
$mysql = [
    'class'=>\yii\db\Connection::class,
    'dsn' => 'mysql:host=mysql;dbname=testdb',
    'username' => 'dbuser',
    'password' => 'dbpass',
    'charset' => 'utf8',
    //'tablePrefix' => 'test_'
];
return [
    'id' => 'app-test',
    'sourceLanguage' => 'en-US',
    'timeZone' => 'UTC',
    'basePath' => dirname(__DIR__) . '/tmp/docker_app',
    'runtimePath' => dirname(__DIR__) . '/tmp',
    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    //'bootstrap'=>['log'],
    'language'       => 'ru',
    'charset'        => 'utf-8',
    'container'=>[
        'definitions'=>[],
        'singletons'=>[]
    ],
    'components' => [
        'db'=>$mysql,
        'pgdb'=>$pgsql,
    ],
    'modules'=>[
    ],
    'params'=>[],
    'controllerMap'=>[
        'migrate'=>[
            'class'=>\yii\console\controllers\MigrateController::class,
            'migrationPath' => '@tests/migrations/mysql',
            'db' => 'db'
        ],
        'pgmigrate'=>[
            'class'=>\yii\console\controllers\MigrateController::class,
            'migrationPath' => '@tests/migrations/pg',
            'db' => 'pgdb'
        ],
    ]
];