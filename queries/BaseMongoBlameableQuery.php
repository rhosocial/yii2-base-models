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

namespace rhosocial\base\models\queries;

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
            $guid = $guid->getReadableGUID();
        }
        return $this->andWhere([$model->createdByAttribute => $guid]);
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
            $guid = $guid->getReadableGUID();
        }
        return $this->andWhere([$model->updatedByAttribute => $guid]);
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
        if (!$identity || !$identity->canGetProperty('guid')) {
            return $this;
        }
        return $this->createdBy($identity->getReadableGUID());
    }
}
