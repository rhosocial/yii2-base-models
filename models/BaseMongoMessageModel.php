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

namespace rhosocial\base\models\models;

use MongoDB\BSON\Binary;
use rhosocial\base\models\queries\BaseMongoMessageQuery;
use rhosocial\base\models\queries\BaseUserQuery;
use rhosocial\base\models\traits\MessageTrait;
use yii\base\InvalidConfigException;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseMongoMessageModel extends BaseMongoBlameableModel
{
    use MessageTrait;
    
    public string|false $updatedAtAttribute = false;
    public string|false $updatedByAttribute = false;
    public int $expiredAt = 604800; // 7 days.
    
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