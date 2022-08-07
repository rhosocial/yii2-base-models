<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2022 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests\data\ar\redis;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\models\BaseRedisMessageModel;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class RedisMessage extends BaseRedisMessageModel
{
    public function __construct($config = array())
    {
        $this->hostClass = User::class;
        parent::__construct($config);
    }

    /**
     * Friendly to IDE.
     * @return \rhosocial\base\models\queries\BaseRedisMessageQuery
     */
    public static function find()
    {
        return parent::find();
    }
}
