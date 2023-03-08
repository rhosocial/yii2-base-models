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

/**
 * This trait is used for building message query class for message model.
 * 
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
trait MessageQueryTrait
{
    use MutualQueryTrait;
    
    /**
     * Specify unread message.
     * @return static $this
     */
    public function unread(): static
    {
        $model = $this->noInitModel;
        return $this->likeCondition($model->initDatetime(), $model->readAtAttribute);
    }

    /**
     * Specify read message.
     * @return static $this
     */
    public function read(): static
    {
        $model = $this->noInitModel;
        return $this->likeCondition($model->initDatetime(), $model->readAtAttribute, 'not in');
    }
    
    /**
     * Specify unreceived message.
     * @return static $this
     */
    public function unreceived(): static
    {
        $model = $this->noInitModel;
        return $this->likeCondition($model->initDatetime(), $model->receivedAtAttribute);
    }
    
    /**
     * Specify received message.
     * @return static $this
     */
    public function received(): static
    {
        $model = $this->noInitModel;
        return $this->likeCondition($model->initDatetime(), $model->receivedAtAttribute, 'not in');
    }
}