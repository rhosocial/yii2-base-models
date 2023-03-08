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
use yii\db\Connection;

/**
 * This trait is used for building query class which contains mutual relation operations.
 *
 * @version 0.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
trait MutualQueryTrait
{

    /**
     * Get the opposite relation.
     * @param array|BaseUserModel|string $user initiator
     * @param array|BaseUserModel|string $other recipient.
     * @param Connection|null $database
     * @return mixed It's type depends on {$model->class}.
     */
    public function opposite($user, $other, ?Connection $database = null): mixed
    {
        $model = $this->noInitModel;
        return $this->andWhere(
            [$model->createdByAttribute => BaseUserModel::compositeGUIDs($other),
        $model->otherGuidAttribute => BaseUserModel::compositeGUIDs($user)])->one($database);
    }

    /**
     * Get all the opposites.
     * @param array|string $user initiator.
     * @param array $others all recipients.
     * @param Connection|null $database
     * @return array instances.
     */
    public function opposites($user, $others = [], ?Connection $database = null): array
    {
        $model = $this->noInitModel;
        $query = $this->andWhere([$model->otherGuidAttribute => BaseUserModel::compositeGUIDs($user)]);
        /* @var $query static */
        if (!empty($others)) {
            $query = $query->andWhere([$model->createdByAttribute => BaseUserModel::compositeGUIDs($others)]);
        }
        return $query->all($database);
    }

    /**
     * Specify initiators.
     * @param string|array $users the guid of initiator if strung, or guid array
     * of initiators if arrayed.
     * @return static $this
     */
    public function initiators($users = []): static
    {
        if (empty($users)) {
            return $this;
        }
        $model = $this->noInitModel;
        return $this->andWhere([$model->createdByAttribute => BaseUserModel::compositeGUIDs($users)]);
    }

    /**
     * Specify recipients.
     * @param string|array $users the guid of recipient if strung, or guid array
     * of recipients if arrayed.
     * @return static $this
     */
    public function recipients($users = []): static
    {
        if (empty($users)) {
            return $this;
        }
        $model = $this->noInitModel;
        return $this->andWhere([$model->otherGuidAttribute => BaseUserModel::compositeGUIDs($users)]);
    }
}
