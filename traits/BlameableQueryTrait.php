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
     * @param bool $isConfirmed
     * @return $this
     */
    public function confirmed(bool $isConfirmed = true): static
    {
        $model = $this->noInitModel;
        /* @var $model static */
        if (!is_string($model->confirmationAttribute)) {
            return $this;
        }
        return $this->andWhere([$model->confirmationAttribute => $isConfirmed ? 1 : 0]);
    }

    /**
     * Specify content.
     * @param mixed $content
     * @param false|string $like false, 'like', 'or like', 'not like', 'or not like'.
     * @return $this
     */
    public function content(mixed $content, false|string $like = false): static
    {
        $model = $this->noInitModel;
        /* @var $model static */
        return $this->likeCondition($content, $model->contentAttribute, $like);
    }

    /**
     * Specify parent.
     * @param array|string|BlameableQueryTrait $guid parent guid or array of them. non-parent if
     * empty. If you don't want to specify parent, please do not access this
     * method.
     * @return $this
     */
    public function parentGuid(mixed $guid): static
    {
        $model = $this->noInitModel;
        /* @var $model static */
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
    public function createdBy($guid): static
    {
        $model = $this->noInitModel;
        /* @var $model static */
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
    public function updatedBy($guid): static
    {
        $model = $this->noInitModel;
        /* @var $model static */
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
     * @param BaseUserModel|null $identity
     * @return $this
     */
    public function byIdentity(?BaseUserModel $identity = null): static
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
