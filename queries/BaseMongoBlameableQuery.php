<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2017 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\queries;

use MongoDB\BSON\Binary;
use rhosocial\base\models\traits\BlameableQueryTrait;

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
     * Specify creator(s).
     * @param string|array $guid
     * @return $this
     */
    public function createdBy($guid)
    {
        $model = $this->noInitModel;
        if (!is_string($model->createdByAttribute)) {
            return $this;
        }
        if ($guid instanceof BaseUserModel) {
            $guid = $guid->getGUID();
        }
        return $this->andWhere([$model->createdByAttribute => new Binary($guid, Binary::TYPE_UUID)]);
    }

    /**
     * Specify last updater(s).
     * @param string|array $guid
     * @return $this
     */
    public function updatedBy($guid)
    {
        $model = $this->noInitModel;
        if (!is_string($model->updatedByAttribute)) {
            return $this;
        }
        if ($guid instanceof BaseUserModel) {
            $guid = $guid->getGUID();
        }
        return $this->andWhere([$model->updatedByAttribute => new Binary($guid, Binary::TYPE_UUID)]);
    }

    /**
     * Attach current identity to createdBy condition.
     * @param BaseUserModel $identity
     * @return $this
     */
    public function byIdentity($identity = null)
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
