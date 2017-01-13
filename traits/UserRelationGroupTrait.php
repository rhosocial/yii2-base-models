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

/**
 * 使用此 trait 的类必须与使用 UserRelationTrait 的类配合使用。
 * $contentAttribute 关系组名称。
 * $contentTypeAttribute 关系组类型。
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait UserRelationGroupTrait
{

    public $relationClass;

    /**
     * Attach events associated with user relation group.
     */
    public function initUserRelationGroupEvents()
    {
        $this->on(static::EVENT_BEFORE_DELETE, [$this, 'onDeleteGroup']);
    }

    /**
     * the event triggered before deleting group.
     * I do not remove group's guid from groupsAttribute which contains the guid
     * of group to be deleted.
     * @param \yii\base\Event $event
     */
    public function onDeleteGroup($event)
    {
    }
}
