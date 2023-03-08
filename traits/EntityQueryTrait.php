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

use rhosocial\base\helpers\Number;
use rhosocial\base\models\models\BaseEntityModel;
use rhosocial\base\models\models\BaseMongoEntityModel;
use rhosocial\base\models\models\BaseRedisEntityModel;
use yii\db\ActiveQuery;

/**
 * This trait is used for building entity query class for entity model.
 *
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
trait EntityQueryTrait
{
    use QueryTrait;

    /**
     * @var BaseEntityModel|BaseMongoEntityModel|BaseRedisEntityModel
     */
    public mixed $noInitModel;

    /**
     * Build model without any initializations.
     */
    public function buildNoInitModel(): void
    {
        if (empty($this->noInitModel) && is_string($this->modelClass)) {
            $modelClass = $this->modelClass;
            $this->noInitModel = $modelClass::buildNoInitModel();
        }
    }

    /**
     * Specify guid attribute.
     * @param mixed $guid
     * @param false|string $like false, 'like', 'or like', 'not like', 'or not like'.
     * @return $this
     */
    public function guid(mixed $guid, false|string $like = false): static
    {
        /* @var $this ActiveQuery */
        $model = $this->noInitModel;
        return $this->likeCondition($guid, $model->guidAttribute, $like);
    }

    /**
     * Specify id attribute.
     * @param mixed $id
     * @param false|string $like false, 'like', 'or like', 'not like', 'or not like'.
     * @return $this
     */
    public function id(mixed $id, false|string $like = false): static
    {
        /* @var $this ActiveQuery */
        $model = $this->noInitModel;
        return $this->likeCondition($id, $model->idAttribute, $like);
    }

    /**
     * Specify GUID or ID attribute.
     * Scalar parameter is acceptable only.
     * Please do not pass an array to the first parameter.
     * @param int|string $param
     * @param bool|string $like false, 'like', 'or like', 'not like', 'or not like'.
     * @return $this
     */
    public function guidOrId(int|string $param, bool|string $like = false): static
    {
        if (is_string($param) && (preg_match(Number::GUID_REGEX, $param) || strlen($param) == 16)) {
            return $this->guid($param, $like);
        }
        return $this->id($param, $like);
    }

    /**
     * Specify creation time range.
     * @param string|null $start
     * @param string|null $end
     * @return $this
     */
    public function createdAt(?string $start = null, ?string $end = null): static
    {
        /* @var $this ActiveQuery */
        $model = $this->noInitModel;
        if (!is_string($model->createdAtAttribute) || empty($model->createdAtAttribute)) {
            return $this;
        }
        return static::range($this, $model->createdAtAttribute, $start, $end);
    }

    /**
     * Specify creation time as today (in locally).
     * @return $this
     */
    public function createdAtToday(): static
    {
        /* @var $this ActiveQuery */
        $model = $this->noInitModel;
        $start = strtotime(date('Y-m-d'));
        $end = $start + 86400;
        if ($model->timeFormat == BaseEntityModel::TIME_FORMAT_DATETIME) {
            $start = gmdate('Y-m-d H:i:s', $start);
            $end = gmdate('Y-m-d H:i:s', $end);
        }
        return $this->createdAt($start, $end);
    }

    /**
     * Specify order by creation time.
     * @param int|string $sort only 'SORT_ASC' and 'SORT_DESC' are acceptable.
     * @return $this
     */
    public function orderByCreatedAt(int|string $sort = SORT_ASC): static
    {
        /* @var $this ActiveQuery */
        $model = $this->noInitModel;
        if (!is_string($model->createdAtAttribute) || empty($model->createdAtAttribute)) {
            return $this;
        }
        return $this->addOrderBy([$model->createdAtAttribute => $sort]);
    }

    /**
     * Specify last updated time range.
     * @param string|null $start
     * @param string|null $end
     * @return $this
     */
    public function updatedAt(?string $start = null, ?string $end = null): static
    {
        /* @var $this ActiveQuery */
        $model = $this->noInitModel;
        if (!is_string($model->updatedAtAttribute) || empty($model->updatedAtAttribute)) {
            return $this;
        }
        return static::range($this, $model->updatedAtAttribute, $start, $end);
    }

    /**
     * Specify last updated time as today (in locally).
     * @return $this
     */
    public function updatedAtToday(): static
    {
        /* @var $this ActiveQuery */
        $model = $this->noInitModel;
        $start = strtotime(date('Y-m-d'));
        $end = $start + 86400;
        if ($model->timeFormat == BaseEntityModel::TIME_FORMAT_DATETIME) {
            $start = gmdate('Y-m-d H:i:s', $start);
            $end = gmdate('Y-m-d H:i:s', $end);
        }
        return $this->updatedAt($start, $end);
    }

    /**
     * Specify order by update time.
     * @param int|string $sort only 'SORT_ASC' and 'SORT_DESC' are acceptable.
     * @return $this
     */
    public function orderByUpdatedAt(int|string $sort = SORT_ASC): static
    {
        /* @var $this ActiveQuery */
        $model = $this->noInitModel;
        if (!is_string($model->updatedAtAttribute) || empty($model->updatedAtAttribute)) {
            return $this;
        }
        return $this->addOrderBy([$model->updatedAtAttribute => $sort]);
    }

    public static $pageAll = 'all';
    public static $defaultPageSize = 10;
    
    /**
     * Specify page condition.
     * @param int|string|null $pageSize It will return all models if it is 'all',
     * or it will be regarded as sum of models.
     * @param ?int $currentPage The current page number if it is integer begun with 0.
     * @return $this
     */
    public function page(int|string|null $pageSize = 10, ?int $currentPage = 0): static
    {
        if ($pageSize === static::$pageAll) {
            return $this;
        }
        /* normalize $currentPage and $currentPage */
        if (!is_numeric($currentPage) || $currentPage < 0) {
            $currentPage = 0;
        }
        $currentPage = (int) $currentPage;
        if (!is_numeric($pageSize) || $pageSize < 1) {
            $pageSize = static::$defaultPageSize;
        }
        $pageSize = (int) $pageSize;
        return $this->limit($pageSize)->offset($pageSize * $currentPage);
    }
}
