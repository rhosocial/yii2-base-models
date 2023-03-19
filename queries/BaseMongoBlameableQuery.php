<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2023 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\queries;

use MongoDB\BSON\Binary;
use rhosocial\base\models\models\BaseUserModel;
use rhosocial\base\models\traits\BlameableQueryTrait;
use Yii;

/**
 * Description of BaseMongoBlameableQuery
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class BaseMongoBlameableQuery extends BaseMongoEntityQuery
{
    use BlameableQueryTrait;

    /**
     * Attach current identity to createdBy condition.
     * @param BaseUserModel|null $identity
     * @return $this
     */
    public function byIdentity(BaseUserModel $identity = null)
    {
        if (!$identity) {
            $identity = Yii::$app->user->identity;
        }
        if (method_exists($identity, 'canGetProperty') && !$identity->canGetProperty('guid')) {
            return $this;
        }
        return $this->createdBy($identity->getGUID());
    }
}
