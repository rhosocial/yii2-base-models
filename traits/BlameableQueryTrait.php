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

namespace rhosocial\base\models\traits;

use rhosocial\base\models\models\BaseUserModel;
use Yii;

/**
 * This trait is used for building blameable query class for blameable model,
 * which would be attached three conditions.
 * For example:
 * ```php
 * class BlameableQuery {
 *     use BlameableQueryTrait;
 * }
 * ```
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait BlameableQueryTrait
{
    use QueryTrait;

    /**
     * Specify confirmation.
     * @param boolean $isConfirmed
     * @return $this
     */
    public function confirmed($isConfirmed = true)
    {
        $model = $this->noInitModel;
        if (!is_string($model->confirmationAttribute)) {
            return $this;
        }
        return $this->andWhere([$model->confirmationAttribute => $isConfirmed]);
    }

    /**
     * Specify content.
     * @param mixed $content
     * @param false|string $like false, 'like', 'or like', 'not like', 'or not like'.
     * @return $this
     */
    public function content($content, $like = false)
    {
        $model = $this->noInitModel;
        return $this->likeCondition($content, $model->contentAttribute, $like);
    }

    /**
     * Specify parent.
     * @param array|string $guid parent guid or array of them. non-parent if
     * empty. If you don't want to specify parent, please do not access this
     * method.
     * @return $this
     */
    public function parentGuid($guid)
    {
        $model = $this->noInitModel;
        if (!is_string($model->parentAttribute)) {
            return $this;
        }
        if ($guid instanceof $model) {
            $guid = $guid->getGUID();
        }
        return $this->andWhere([$model->parentAttribute => $guid]);
    }

    /**
     * Specify creator(s).
     * @param string|array $guid
     * @return $this
     */
    public function createdBy($guid)
    {
        $model = $this->noInitModel;
        if (!is_string($model->createdByAttribute) || empty($model->createdByAttribute)) {
            return $this;
        }
        if ($guid instanceof BaseUserModel) {
            $guid = $guid->getGUID();
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
            $guid = $guid->getGUID();
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
        if (!$identity || (method_exists($identity, 'canGetProperty') && !$identity->canGetProperty('guid'))) {
            return $this;
        }
        return $this->createdBy($identity->getGUID());
    }
}
