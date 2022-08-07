<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link http://vistart.me/
 * @copyright Copyright (c) 2016 - 2022 vistart
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
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseUserRelationGroupModel extends BaseBlameableModel
{
    use UserRelationGroupTrait;

    public $confirmationAttribute = false;
    public $descriptionAttribute = 'description';
    
    /**
     * @var false This feature does not need to record IP address. 
     */
    public $enableIP = false;
    
    public $idCreatorCombinatedUnique = true;
    
    /**
     * @var false This feature does not need to record the user who update this group. 
     */
    public $updatedByAttribute = false;

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
