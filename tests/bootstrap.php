<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require_once(__DIR__ . '/../vendor/autoload.php');
/**
 * Load application environment from .env file
 */
$dotenv = new \Dotenv\Dotenv(__DIR__);
$dotenv->load();
require_once(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');


\Yii::setAlias('@tests', __DIR__);
