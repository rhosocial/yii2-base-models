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

namespace rhosocial\base\models\tests\data\ar\mongodb;

use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\models\BaseMongoMessageModel;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class MongoMessage extends BaseMongoMessageModel
{
    public function __construct($config = array())
    {
        $this->hostClass = User::class;
        parent::__construct($config);
    }

    /**
     * Friendly to IDE.
     * @return \rhosocial\base\models\queries\BaseMongoMessageQuery
     */
    public static function find()
    {
        return parent::find();
    }
}
