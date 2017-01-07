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

namespace rhosocial\base\models\traits;

/**
 * This trait is used for building message query class for message model.
 * 
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait MessageQueryTrait
{
    use MutualQueryTrait;
    
    /**
     * Specify unread message.
     * @return \static $this
     */
    public function unread()
    {
        $model = $this->noInitModel;
        return $this->likeCondition($model->initDatetime(), $model->readAtAttribute);
    }

    /**
     * Specify read message.
     * @return \static $this
     */
    public function read()
    {
        $model = $this->noInitModel;
        return $this->likeCondition($model->initDatetime(), $model->readAtAttribute, 'not in');
    }
    
    /**
     * Specify unreceived message.
     * @return \static $this
     */
    public function unreceived()
    {
        $model = $this->noInitModel;
        return $this->likeCondition($model->initDatetime(), $model->receivedAtAttribute);
    }
    
    /**
     * Specify received message.
     * @return \static $this
     */
    public function received()
    {
        $model = $this->noInitModel;
        return $this->likeCondition($model->initDatetime(), $model->receivedAtAttribute, 'not in');
    }
}