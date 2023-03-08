<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2023 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests\data\ar;

use rhosocial\base\models\queries\BaseRedisBlameableQuery;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class RedisBlameable extends \rhosocial\base\models\models\BaseRedisBlameableModel
{
    public string|false $guidAttribute = false;
    public string|false $idAttribute = 'alpha2';

    public function init() {
        $this->hostClass = User::class;
        parent::init();
    }

    public static function primaryKey()
    {
        return ['alpha2'];
    }

    /**
     * Friendly to IDE.
     * @return BaseRedisBlameableQuery
     */
    public static function find()
    {
        return parent::find();
    }
}
