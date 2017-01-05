<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\models;

use rhosocial\base\models\queries\BaseRedisBlameableQuery;
use rhosocial\base\models\traits\BlameableTrait;

/**
 * Description of BaseRedisBlameableModel
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseRedisBlameableModel extends BaseRedisEntityModel
{
    use BlameableTrait;

    /**
     * Initialize the blameable model.
     * If query class is not specified, [[BaseRedisBlameableQuery]] will be taken.
     */
    public function init()
    {
        if (!is_string($this->queryClass)) {
            $this->queryClass = BaseRedisBlameableQuery::class;
        }
        if ($this->skipInit) {
            return;
        }
        $this->initBlameableEvents();
        parent::init();
    }

    /**
     * Get the query class with specified identity.
     * @param BaseUserModel $identity
     * @return BaseRedisBlameableQuery
     */
    public static function findByIdentity($identity = null)
    {
        return static::find()->byIdentity($identity);
    }
}
