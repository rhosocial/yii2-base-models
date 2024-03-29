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

namespace rhosocial\base\models\traits;

use yii\db\ActiveQuery;

/**
 * This trait attach two base conditions.
 * 
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
trait QueryTrait
{
    /**
     * Attach like condition.
     * @param mixed $value
     * @param string $attribute
     * @param string|false $like false, 'like', 'or like', 'not like', 'or not like'.
     * @return $this
     */
    protected function likeCondition($value, $attribute, $like = false): static
    {
        if (!is_string($attribute) || empty($attribute)) {
            return $this;
        }
        if ($like !== false) {
            return $this->andWhere([$like, $attribute, $value]);
        }
        return $this->andWhere([$attribute => $value]);
    }

    /**
     * Specify range with $attribute to $query.
     * @param ActiveQuery $query
     * @param string $attribute
     * @param null $start
     * @param null $end
     * @return ActiveQuery
     */
    protected static function range($query, $attribute, $start = null, $end = null): ActiveQuery
    {
        if (!empty($start)) {
            $query = $query->andWhere(['>=', $attribute, $start]);
        }
        if (!empty($end)) {
            $query = $query->andWhere(['<', $attribute, $end]);
        }
        return $query;
    }
}