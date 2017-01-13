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

namespace rhosocial\base\models\models;

use rhosocial\base\models\queries\BaseMongoMessageQuery;
use rhosocial\base\models\traits\MessageTrait;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseMongoMessageModel extends BaseMongoBlameableModel
{
    use MessageTrait;
    
    public $updatedByAttribute = false;
    public $expiredAt = 604800; // 7 days.
    
    public function init()
    {
        if (!is_string($this->queryClass) || empty($this->queryClass)) {
            $this->queryClass = BaseMongoMessageQuery::class;
        }
        if ($this->skipInit) {
            return;
        }
        $this->initMessageEvents();
        parent::init();
    }
}