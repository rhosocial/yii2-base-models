<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests;

use yii\db\Connection;
use rhosocial\base\models\tests\data\ar\User;

class MongoTestCase extends TestCase
{
    protected static function prepareUser()
    {
        $user = new User(['password' => '123456']);
        if (!$user->register()) {
            $this->fail();
        }
        return $user;
    }
    
    public function setUp()
    {
        $databases = self::getParam('databases');
        $params = isset($databases['mysql']) ? $databases['mysql'] : null;
        if ($params === null) {
            $this->markTestSkipped('No mysql server connection configured.');
        }
        $connection = new Connection($params);
        $redis = self::getParam('redis');
        $mongodb = self::getParam('mongodb');
        $cacheParams = self::getParam('cache');
        
        $this->mockWebApplication(['components' => ['redis' => $redis, 'mongodb' => $mongodb, 'db' => $connection, 'cache' => $cacheParams]]);
    }
}