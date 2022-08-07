<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2022 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\queries;

use rhosocial\base\models\traits\EntityQueryTrait;

/**
 * Description of BaseRedisEntityQuery
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class BaseRedisEntityQuery extends \yii\redis\ActiveQuery
{
    use EntityQueryTrait;
    
    protected static function range($query, $attribute, $start = null, $end = null)
    {
        if (!isset($attribute, $start, $end)) {
            throw new \yii\db\Exception("`attribute`, `start` and `end` must be specified.");
        }
        return $query->andWhere(['between', $attribute, $start, $end]);
    }

    public function init()
    {
        $this->buildNoInitModel();
        parent::init();
    }
}
