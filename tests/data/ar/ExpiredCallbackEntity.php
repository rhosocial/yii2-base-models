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

use yii\base\ModelEvent;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class ExpiredCallbackEntity extends ExpiredEntity
{
    public function init()
    {
        $this->expiredRemovingCallback = [$this, 'removingCallback'];
        $this->on(static::$eventExpiredRemoved, [$this, 'checkInitDatetime']);
        parent::init();
    }

    public function checkInitDatetime($event)
    {
        $sender = $event->sender;
        /* @var $sender static */
        return $sender->isInitDatetime($sender->getCreatedAt());
    }

    public function removingCallback($model)
    {
        /* @var $sender ExpiredEntity */
        return $model->getInitDatetime(new ModelEvent(['sender' => $model]));
    }
}
