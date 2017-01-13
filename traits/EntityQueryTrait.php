<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2017 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\traits;

/**
 * This trait is used for building entity query class for entity model.
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait EntityQueryTrait
{
    use QueryTrait;
    
    public $noInitModel;

    /**
     * Build model without any initializations.
     */
    public function buildNoInitModel()
    {
        if (empty($this->noInitModel) && is_string($this->modelClass)) {
            $modelClass = $this->modelClass;
            $this->noInitModel = $modelClass::buildNoInitModel();
        }
    }
    
    /**
     * Specify guid attribute.
     * @param string|array $guid
     * @param false|string $like false, 'like', 'or like', 'not like', 'or not like'.
     * @return $this
     */
    public function guid($guid, $like = false)
    {
        $model = $this->noInitModel;
        return $this->likeCondition((string)$guid, $model->guidAttribute, $like);
    }
    
    /**
     * Specify id attribute.
     * @param string|integer|array $id
     * @param false|string $like false, 'like', 'or like', 'not like', 'or not like'.
     * @return $this
     */
    public function id($id, $like = false)
    {
        $model = $this->noInitModel;
        return $this->likeCondition($id, $model->idAttribute, $like);
    }
    
    /**
     * Specify create time range.
     * @param string $start
     * @param string $end
     * @return $this
     */
    public function createdAt($start = null, $end = null)
    {
        $model = $this->noInitModel;
        /* @var $model static */
        if (!is_string($model->createdAtAttribute) || empty($model->createdAtAttribute)) {
            return $this;
        }
        return static::range($this, $model->createdAtAttribute, $start, $end);
    }
    
    /**
     * Specify update time range.
     * @param string $start 
     * @param string $end
     * @return $this
     */
    public function updatedAt($start = null, $end = null)
    {
        $model = $this->noInitModel;
        /* @var $model static */
        if (!is_string($model->updatedAtAttribute) || empty($model->updatedAtAttribute)) {
            return $this;
        }
        return static::range($this, $model->updatedAtAttribute, $start, $end);
    }
    public static $pageAll = 'all';
    public static $defaultPageSize = 10;
    
    /**
     * Specify page condition.
     * @param string|int $pageSize It will return all models if it is 'all',
     * or it will be regarded as sum of models.
     * @param int $currentPage The current page number if it is integer begun with 0.
     * @return $this
     */
    public function page($pageSize = 10, $currentPage = 0)
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
