<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2022 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\traits;

use rhosocial\base\models\models\BaseUserModel;

/**
 * This trait is used for building query class which contains mutual relation operations.
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait MutualQueryTrait
{

    /**
     * Get the opposite relation.
     * @param array|BaseUserModel|string $user initiator
     * @param array|BaseUserModel|string $other recipient.
     * @param Connection $database
     * @return mixed It's type depends on {$model->class}.
     */
    public function opposite($user, $other, $database = null)
    {
        $model = $this->noInitModel;
        return $this->andWhere(
            [$model->createdByAttribute => BaseUserModel::compositeGUIDs($other),
        $model->otherGuidAttribute => BaseUserModel::compositeGUIDs($user)])->one($database);
    }

    /**
     * Get all the opposites.
     * @param array|string $user initator.
     * @param array $others all recipients.
     * @param Connection $database
     * @return array instances.
     */
    public function opposites($user, $others = [], $database = null)
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
     * @param string|array $users the guid of initiator if string, or guid array
     * of initiators if array.
     * @return static $this
     */
    public function initiators($users = [])
    {
        if (empty($users)) {
            return $this;
        }
        $model = $this->noInitModel;
        return $this->andWhere([$model->createdByAttribute => BaseUserModel::compositeGUIDs($users)]);
    }

    /**
     * Specify recipients.
     * @param string|array $users the guid of recipient if string, or guid array
     * of recipients if array.
     * @return static $this
     */
    public function recipients($users = [])
    {
        if (empty($users)) {
            return $this;
        }
        $model = $this->noInitModel;
        return $this->andWhere([$model->otherGuidAttribute => BaseUserModel::compositeGUIDs($users)]);
    }
}
