<?php
$pgsql = [
    'class'=>\yii\db\Connection::class,
    'dsn' => getenv('PG_DSN'),
    'username' => getenv('PG_USER'),
    'password' => getenv('PG_PASSWORD'),
    'charset' => 'utf8',
    //'tablePrefix' => 'test_'
];
$mysql = [
    'class'=>\yii\db\Connection::class,
    'dsn' => getenv('MYSQL_DSN'),
    'username' => getenv('MYSQL_USER'),
    'password' => getenv('MYSQL_PASSWORD'),
    'charset' => 'utf8',
    //'tablePrefix' => 'test_'
];
return [
    'id' => 'app-test',
    'basePath' => dirname(__DIR__),
    'sourceLanguage' => 'en-US',
    'timeZone'            => 'Europe/Moscow',
    'language'       => 'ru',
    'charset'        => 'utf-8',
    'bootstrap'=>['log'],
    'aliases'=>[],
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
    'params'=>[]
];