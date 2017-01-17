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
     * @param BaseUserModel|string $user initiator
     * @param BaseUserModel|string $other recipient.
     * @param Connection $database
     * @return 
     */
    public function opposite($user, $other, $database = null)
    {
        $model = $this->noInitModel;
        if ($user instanceof BaseUserModel) {
            $user = $user->getGUID();
        }
        if ($other instanceof BaseUserModel) {
            $other = $other->getGUID();
        }
        return $this->andWhere([$model->createdByAttribute => $other, $model->otherGuidAttribute => $user])->one($database);
    }

    /**
     * Get all the opposites.
     * @param string $user initator.
     * @param array $others all recipients.
     * @param Connection $database
     * @return array instances.
     */
    public function opposites($user, $others = [], $database = null)
    {
        $model = $this->noInitModel;
        if ($user instanceof BaseUserModel) {
            $user = $user->getGUID();
        }
        $query = $this->andWhere([$model->otherGuidAttribute => $user]);
        if (!empty($others)) {
            if ($others instanceof BaseUserModel) {
                $others = [$others->getGUID()];
            } elseif (is_array($others)) {
                $others = BaseUserModel::compositeGUIDs($others);
            }
            $query = $query->andWhere([$model->createdByAttribute => array_values($others)]);
        }
        return $query->all($database);
    }

    /**
     * Specify initiators.
     * @param string|array $users the guid of initiator if string, or guid array
     * of initiators if array.
     * @return \static $this
     */
    public function initiators($users = [])
    {
        if (empty($users)) {
            return $this;
        }
        $model = $this->noInitModel;
        if ($users instanceof BaseUserModel) {
            $users = $users->getGUID();
        } elseif (is_array($users)) {
            $users = BaseUserModel::compositeGUIDs($users);
        }
        return $this->andWhere([$model->createdByAttribute => $users]);
    }

    /**
     * Specify recipients.
     * @param string|array $users the guid of recipient if string, or guid array
     * of recipients if array.
     * @return \static $this
     */
    public function recipients($users = [])
    {
        if (empty($users)) {
            return $this;
        }
        $model = $this->noInitModel;
        if ($users instanceof BaseUserModel) {
            $users = $users->getGUID();
        } elseif (is_array($users)) {
            $users = BaseUserModel::compositeGUIDs($users);
        }
        return $this->andWhere([$model->otherGuidAttribute => $users]);
    }
}
