<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link http://vistart.me/
 * @copyright Copyright (c) 2016 - 2023 vistart
 * @license http://vistart.me/license/
 */

namespace rhosocial\base\models\models;

use rhosocial\base\models\queries\BaseBlameableQuery;
use rhosocial\base\models\traits\UserRelationGroupTrait;

/**
 * This abstract class is used for building user relation group.
 * This model is a record of user relation group, if you want to know the details
 * about creating, updating, finding and removing a group, please see [[BaseUserRelationModel]].
 *
 * $contentAttribute name of user relation group.
 * $contentTypeAttribute type of user relation group.
 * 
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseUserRelationGroupModel extends BaseBlameableModel
{
    use UserRelationGroupTrait;

    public string|false $confirmationAttribute = false;
    public string|false $descriptionAttribute = 'description';
    
    /**
     * @var int This feature does not need to record IP address.
     */
    public int $enableIP = self::IP_DISABLED;
    
    public bool $idCreatorCombinatedUnique = true;
    
    /**
     * @var string|false This feature does not need to record the user who update this group.
     */
    public string|false $updatedByAttribute = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!is_string($this->queryClass)) {
            $this->queryClass = BaseBlameableQuery::class;
        }
        if ($this->skipInit) {
            return;
        }
        $this->initUserRelationGroupEvents();
        parent::init();
    }
}
