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

use rhosocial\base\models\models\BaseRedisEntityModel;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class RedisEntity extends BaseRedisEntityModel
{
    public $guidAttribute = false;
    public $idAttribute = 'alpha2';

    public function attributes()
    {
        return array_merge(parent::attributes(), ['content']);
    }
}
