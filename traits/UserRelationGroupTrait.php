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

use yii\base\Event;

/**
 * Classes using this trait must be used in conjunction with classes using [[UserRelationTrait]].
 * $contentAttribute 关系组名称。
 * $contentTypeAttribute 关系组类型。
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
trait UserRelationGroupTrait
{

    public string|false $relationClass;

    /**
     * Attach events associated with user relation group.
     */
    public function initUserRelationGroupEvents(): void
    {
        $this->on(static::EVENT_BEFORE_DELETE, [$this, 'onDeleteGroup']);
    }

    /**
     * the event triggered before deleting group.
     * I do not remove group's guid from groupsAttribute which contains the guid
     * of group to be deleted.
     * @param Event $event
     */
    public function onDeleteGroup($event): void
    {
    }
}
