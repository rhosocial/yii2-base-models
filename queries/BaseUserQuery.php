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

/**
 * Description of BaseUserQuery
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class BaseUserQuery extends BaseEntityQuery
{

    /**
     * Specify active status.
     * @param integer $active
     * @return static
     */
    public function active($active)
    {
        $model = $this->noInitModel;
        if (!is_string($model->statusAttribute)) {
            return $this;
        }
        return $this->andWhere([$model->statusAttribute => $active]);
    }

    /**
     * Specify source.
     * @param null|string|array $source
     * @return static
     */
    public function source($source = null)
    {
        $model = $this->noInitModel;
        if (!is_string($model->sourceAttribute)) {
            return $this;
        }
        if (!is_string($source)) {
            $modelClass = $this->modelClass;
            $source = $modelClass::$sourceSelf;
        }
        return $this->andWhere([$model->sourceAttribute => $source]);
    }
}
