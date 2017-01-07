<?php
/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license https://vistart.me/license/
 */
/**
 * This is the configuration file for the Yii2 unit tests.
 * You can override configuration values by creating a `config.local.php` file
 * and manipulate the `$config` variable.
 * For example to change MySQL username and password your `config.local.php` should
 * contain the following:
 *
  <?php
  $config['databases']['mysql']['username'] = 'yiitest';
  $config['databases']['mysql']['password'] = 'changeme';
 */
$config = [
    'databases' => [
        'mysql' => [
            'dsn' => 'mysql:host=localhost;dbname=yii2-base-models',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
    'redis' => [
        'class' => 'yii\redis\Connection',
        'hostname' => 'localhost',
        'port' => 6379,
        'database' => 0,
    ],
    'mongodb' => [
        'class' => 'yii\mongodb\Connection',
        'dsn' => "mongodb://rho_user:Map374Grin3617@localhost:27017/rho",
    ],
    'cache' => [
        'class' => 'yii\redis\Cache',
        'redis' => 'redis',
        'keyPrefix' => 'test_',
    ],
    'multiDomainsManager' => [
        'class' => 'rhosocial\base\models\tests\data\ar\MultiDomainsManager',
    ],
];
if (is_file(__DIR__ . '/config.local.php')) {
    include(__DIR__ . '/config.local.php');
}
return $config;