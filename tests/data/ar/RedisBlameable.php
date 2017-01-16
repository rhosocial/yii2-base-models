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

namespace rhosocial\base\models\tests\data\ar;

/**
 * @author vistart <i@vistart.me>
 */
class RedisBlameable extends \rhosocial\base\models\models\BaseRedisBlameableModel
{
    public $guidAttribute = false;
    public $idAttribute = 'alpha2';
    
    public static function primaryKey()
    {
        return ['alpha2'];
    }
    
    /**
     * Friendly to IDE.
     * @return \rhosocial\base\models\queries\BaseRedisBlameableQuery
     */
    public static function find()
    {
        return parent::find();
    }
}