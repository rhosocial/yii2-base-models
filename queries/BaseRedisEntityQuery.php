<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link http://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license http://vistart.me/license/
 */

namespace rhosocial\base\models\queries;

use rhosocial\base\models\traits\EntityQueryTrait;

/**
 * Description of BaseRedisEntityQuery
 *
 * @version 2.0
 * @author vistart <i@vistart.me>
 */
class BaseRedisEntityQuery extends \yii\redis\ActiveQuery
{
    use EntityQueryTrait;

    public function init()
    {
        $this->buildNoInitModel();
        parent::init();
    }
}
