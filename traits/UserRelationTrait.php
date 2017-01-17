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
use rhosocial\base\models\traits\MultipleBlameableTrait as mb;
use yii\base\ModelEvent;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;
use yii\db\Connection;
use yii\db\IntegrityException;

/**
 * Relation features.
 * This trait should be used in user relation model which is extended from
 * [[BaseBlameableModel]], and is specified `$userClass` property. And the user
 * class should be extended from [[BaseUserModel]], or any other classes used
 * [[UserTrait]].
 * Notice: Several methods associated with "inserting", "updating" and "removing" may
 * involve more DB operations, I strongly recommend those methods to be placed in
 * transaction execution, in order to ensure data consistency.
 * If you want to use group feature, the class used [[UserRelationGroupTrait]]
 * must be used coordinately.
 * @property array $groupGuids the guid array of all groups which owned by current relation.
 * @property-read array $favoriteRules
 * @property boolean $isFavorite
 * @property-read static $opposite
 * @property-read array $otherGuidRules
 * @property string $remark
 * @property-read array $remarkRules
 * @property-read array $userRelationRules
 * @property-read mixed $group
 * @property-read array $groupMembers
 * @property array $groupGuids
 * @property-read array $allGroups
 * @property-read array $nonGroupMembers
 * @property-read integer $groupsCount
 * @property-read array $groupsRules
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait UserRelationTrait
{
    use mb,
        MutualTrait {
        mb::addBlame as addGroup;
        mb::createBlame as createGroup;
        mb::addOrCreateBlame as addOrCreateGroup;
        mb::removeBlame as removeGroup;
        mb::removeAllBlames as removeAllGroups;
        mb::getBlame as getGroup;
        mb::getOrCreateBlame as getOrCreateGroup;
        mb::getBlameds as getGroupMembers;
        mb::getBlameGuids as getGroupGuids;
        mb::setBlameGuids as setGroupGuids;
        mb::getAllBlames as getAllGroups;
        mb::getNonBlameds as getNonGroupMembers;
        mb::getBlamesCount as getGroupsCount;
        mb::getMultipleBlameableAttributeRules as getGroupsRules;
    }

    /**
     * @var string
     */
    public $remarkAttribute = 'remark';
    public static $relationSingle = 0;
    public static $relationMutual = 1;
    public $relationType = 1;
    public $relationTypes = [
        0 => 'Single',
        1 => 'Mutual',
    ];

    /**
     * @var string the attribute name of which determines the relation type.
     */
    public $mutualTypeAttribute = 'type';
    public static $mutualTypeNormal = 0x00;
    public static $mutualTypeSuspend = 0x01;

    /**
     * @var array Mutual types.
     */
    public static $mutualTypes = [
        0x00 => 'Normal',
        0x01 => 'Suspend',
    ];

    /**
     * @var string the attribute name of which determines the `favorite` field.
     */
    public $favoriteAttribute = 'favorite';

    /**
     * Permit to build self relation.
     * @var boolean 
     */
    public $relationSelf = false;

    /**
     * Get whether this relation is favorite or not.
     * @return boolean
     */
    public function getIsFavorite()
    {
        $favoriteAttribute = $this->favoriteAttribute;
        return (is_string($favoriteAttribute) && !empty($favoriteAttribute)) ? (int) $this->$favoriteAttribute > 0 : null;
    }

    /**
     * Set favorite.
     * @param boolean $fav
     */
    public function setIsFavorite($fav)
    {
        $favoriteAttribute = $this->favoriteAttribute;
        return (is_string($favoriteAttribute) && !empty($favoriteAttribute)) ? $this->$favoriteAttribute = ($fav ? 1 : 0) : null;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), $this->getUserRelationRules());
    }

    /**
     * Validation rules associated with user relation.
     * @return array rules.
     */
    public function getUserRelationRules()
    {
        $rules = [];
        if ($this->relationType == static::$relationMutual) {
            $rules = [
                [[$this->mutualTypeAttribute], 'in', 'range' => array_keys(static::$mutualTypes)],
                [[$this->mutualTypeAttribute], 'default', 'value' => static::$mutualTypeNormal],
            ];
        }
        return array_merge($rules, $this->getRemarkRules(), $this->getFavoriteRules(), $this->getGroupsRules(), $this->getOtherGuidRules());
    }

    /**
     * Get remark.
     * @return string remark.
     */
    public function getRemark()
    {
        $remarkAttribute = $this->remarkAttribute;
        return is_string($remarkAttribute) ? $this->$remarkAttribute : null;
    }

    /**
     * Set remark.
     * @param string $remark
     * @return string remark.
     */
    public function setRemark($remark)
    {
        $remarkAttribute = $this->remarkAttribute;
        return is_string($remarkAttribute) ? $this->$remarkAttribute = $remark : null;
    }

    /**
     * Validation rules associated with remark attribute.
     * @return array rules.
     */
    public function getRemarkRules()
    {
        return is_string($this->remarkAttribute) ? [
            [[$this->remarkAttribute], 'string'],
            [[$this->remarkAttribute], 'default', 'value' => ''],
            ] : [];
    }

    /**
     * Validation rules associated with favorites attribute.
     * @return array rules.
     */
    public function getFavoriteRules()
    {
        return is_string($this->favoriteAttribute) ? [
            [[$this->favoriteAttribute], 'boolean'],
            [[$this->favoriteAttribute], 'default', 'value' => 0],
            ] : [];
    }

    /**
     * Validation rules associated with other guid attribute.
     * @return array rules.
     */
    public function getOtherGuidRules()
    {
        $rules = array_merge($this->getMutualRules(), [
            [[$this->otherGuidAttribute, $this->createdByAttribute], 'unique', 'targetAttribute' => [$this->otherGuidAttribute, $this->createdByAttribute]],
        ]);
        return $rules;
    }

    /**
     * Attach events associated with user relation.
     */
    public function initUserRelationEvents()
    {
        $this->on(static::EVENT_INIT, [$this, 'onInitBlamesLimit']);
        $this->on(static::$eventNewRecordCreated, [$this, 'onInitGroups']);
        $this->on(static::$eventNewRecordCreated, [$this, 'onInitRemark']);
        $this->on(static::$eventMultipleBlamesChanged, [$this, 'onBlamesChanged']);
        $this->on(static::EVENT_AFTER_INSERT, [$this, 'onInsertRelation']);
        $this->on(static::EVENT_AFTER_UPDATE, [$this, 'onUpdateRelation']);
        $this->on(static::EVENT_AFTER_DELETE, [$this, 'onDeleteRelation']);
    }

    /**
     * Get opposite relation against self.
     * @return static
     */
    public function getOpposite()
    {
        if ($this->isNewRecord) {
            return null;
        }
        return static::find()->opposite($this->initiator, $this->recipient);
    }

    /**
     * Check whether the initiator is followed by recipient.
     * @param BaseUserModel $initiator
     * @param BaseUserModel $recipient
     * @return boolean
     */
    public static function isFollowed($initiator, $recipient)
    {
        return static::find()->initiators($recipient)->recipients($initiator)->exists();
    }

    /**
     * Check whether the initiator is following recipient.
     * @param BaseUserModel $initiator
     * @param BaseUserModel $recipient
     * @return boolean
     */
    public static function isFollowing($initiator, $recipient)
    {
        return static::find()->initiators($initiator)->recipients($recipient)->exists();
    }

    /**
     * Check whether the initiator is following and followed by recipient mutually (Single Relation).
     * Or check whether the initiator and recipient are friend whatever the mutual type is normal or suspend.
     * @param BaseUserModel $initiator
     * @param BaseUserModel $recipient
     * @return boolean
     */
    public static function isMutual($initiator, $recipient)
    {
        return static::isFollowed($initiator, $recipient) && static::isFollowing($initiator, $recipient);
    }

    /**
     * Check whether the initiator is following and followed by recipient mutually (Single Relation).
     * Or check whether the initiator and recipient are friend if the mutual type is normal.
     * @param BaseUserModel $initiator
     * @param BaseUserModel $recipient
     * @return boolean
     */
    public static function isFriend($initiator, $recipient)
    {
        $query = static::find();
        $model = $query->noInitModel;
        /* @var $model static */
        if ($model->relationType == static::$relationSingle) {
            return static::isMutual($initiator, $recipient);
        }
        if ($model->relationType == static::$relationMutual) {
            $relation = static::find()->initiators($initiator)->recipients($recipient)->andWhere([$model->mutualTypeAttribute => static::$mutualTypeNormal])->exists();
            $inverse = static::find()->recipients($initiator)->initiators($recipient)->andWhere([$model->mutualTypeAttribute => static::$mutualTypeNormal])->exists();
            return $relation && $inverse;
        }
        return false;
    }

    /**
     * Build new or return existed suspend mutual relation, or return null if
     * current type is not mutual.
     * @see buildRelation()
     * @param BaseUserModel|string $user Initiator or its GUID.
     * @param BaseUserModel|string $other Recipient or its GUID.
     * @return static The relation will be
     * given if exists, or return a new relation.
     */
    public static function buildSuspendRelation($user, $other)
    {
        $relation = static::buildRelation($user, $other);
        if (!$relation || $relation->relationType != static::$relationMutual) {
            return null;
        }
        $btAttribute = $relation->mutualTypeAttribute;
        $relation->$btAttribute = static::$mutualTypeSuspend;
        return $relation;
    }

    /**
     * Build new or return existed normal relation.
     * The status of mutual relation will be changed to normal if it is not. 
     * @see buildRelation()
     * @param BaseUserModel|string $user Initiator or its GUID.
     * @param BaseUserModel|string $other Recipient or its GUID.
     * @return static The relation will be
     * given if exists, or return a new relation.
     */
    public static function buildNormalRelation($user, $other)
    {
        $relation = static::buildRelation($user, $other);
        if (!$relation) {
            return null;
        }
        if ($relation->relationType == static::$relationMutual) {
            $btAttribute = $relation->mutualTypeAttribute;
            $relation->$btAttribute = static::$mutualTypeNormal;
        }
        return $relation;
    }

    /**
     * Build new or return existed relation between initiator and recipient.
     * If relation between initiator and recipient is not found, new relation will
     * be built. If initiator and recipient are the same one and it is not allowed
     * to build self relation, null will be given.
     * If you want to know whether the relation exists, you can check the return
     * value of `getIsNewRecord()` method.
     * @param BaseUserModel|string $user Initiator or its GUID.
     * @param BaseUserModel|string $other Recipient or its GUID.
     * @return static The relation will be
     * given if exists, or return a new relation. Or return null if not allowed
     * to build self relation,
     */
    protected static function buildRelation($user, $other)
    {
        $relationQuery = static::find()->initiators($user)->recipients($other);
        $noInit = $relationQuery->noInitModel;
        $relation = $relationQuery->one();
        if (!$relation) {
            $createdByAttribute = $noInit->createdByAttribute;
            $otherGuidAttribute = $noInit->otherGuidAttribute;
            $userClass = $noInit->userClass;
            if ($user instanceof BaseUserModel) {
                $userClass = $userClass ? : $user->className();
                $user = $user->getGUID();
            }
            if ($other instanceof BaseUserModel) {
                $other = $other->getGUID();
            }
            if (!$noInit->relationSelf && $user == $other) {
                return null;
            }
            $relation = new static([$createdByAttribute => $user, $otherGuidAttribute => $other, 'userClass' => $userClass]);
        }
        return $relation;
    }

    /**
     * Build opposite relation throughout the current relation. The opposite
     * relation will be given if existed.
     * @param static $relation
     * @return static
     */
    protected static function buildOppositeRelation($relation)
    {
        if (!$relation) {
            return null;
        }
        $createdByAttribute = $relation->createdByAttribute;
        $otherGuidAttribute = $relation->otherGuidAttribute;
        $opposite = static::buildRelation($relation->$otherGuidAttribute, $relation->$createdByAttribute);
        if ($relation->relationType == static::$relationSingle) {
            $opposite->relationType = static::$relationSingle;
        } elseif ($relation->relationType == static::$relationMutual) {
            $mutualTypeAttribute = $relation->mutualTypeAttribute;
            $opposite->$mutualTypeAttribute = $relation->$mutualTypeAttribute;
        }
        return $opposite;
    }
    
    /**
     * Insert relation, the process is placed in a transaction.
     * Note: This feature only support relational databases and skip all errors.
     * If you don't want to use transaction or database doesn't support it,
     * please use `save()` directly.
     * @param static $relation
     * @param Connection $db
     * @return boolean
     * @throws InvalidValueException
     * @throws InvalidConfigException
     * @throws IntegrityException
     */
    public static function insertRelation($relation, Connection $db = null)
    {
        if (!$relation || !($relation instanceof static)) {
            return false;
        }
        if (!$relation->getIsNewRecord()) {
            throw new InvalidValueException('This relation is not new one.');
        }
        if (!$db && isset(\Yii::$app->db) && \Yii::$ap->db instanceof Connection) {
            $db = \Yii::$app->db;
        }
        if (!$db) {
            throw new InvalidConfigException('Invalid database connection.');
        }
        /* @var $db Connection */
        $transaction = $db->beginTransaction();
        try {
            if (!$relation->save()) {
                throw new IntegrityException('Relation insert failed.');
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            return false;
        }
        return true;
    }

    /**
     * Remove myself.
     * @return integer|false The number of relations removed, or false if the remove
     * is unsuccessful for some reason. Note that it is possible the number of relations
     * removed is 0, even though the remove execution is successful.
     */
    public function remove()
    {
        return $this->delete();
    }

    /**
     * Remove first relation between initiator(s) and recipient(s).
     * @param BaseUserModel|string|array $user Initiator or its guid, or array of them.
     * @param BaseUserModel|string|array $other Recipient or its guid, or array of them.
     * @return integer|false The number of relations removed.
     */
    public static function removeOneRelation($user, $other)
    {
        return static::find()->initiators($user)->recipients($other)->one()->remove();
    }

    /**
     * Remove all relations between initiator(s) and recipient(s).
     * @param BaseUserModel|string|array $user Initiator or its guid, or array of them.
     * @param BaseUserModel|string|array $other Recipient or its guid, or array of them.
     * @return integer The number of relations removed.
     */
    public static function removeAllRelations($user, $other)
    {
        $rni = static::buildNoInitModel();
        $createdByAttribute = $rni->createdByAttribute;
        $otherGuidAttribute = $rni->otherGuidAttribute;
        return static::deleteAll([$createdByAttribute => BaseUserModel::compositeGUIDs($user), $otherGuidAttribute => BaseUserModel::compositeGUIDs($other)]);
    }

    /**
     * Get first relation between initiator(s) and recipient(s).
     * @param BaseUserModel|string|array $user Initiator or its guid, or array of them.
     * @param BaseUserModel|string|array $other Recipient or its guid, or array of them.
     * @return static
     */
    public static function findOneRelation($user, $other)
    {
        return static::find()->initiators($user)->recipients($other)->one();
    }

    /**
     * Get first opposite relation between initiator(s) and recipient(s).
     * @param BaseUserModel|string $user Initiator or its guid, or array of them.
     * @param BaseUserModel|string $other Recipient or its guid, or array of them.
     * @return static
     */
    public static function findOneOppositeRelation($user, $other)
    {
        return static::find()->initiators($other)->recipients($user)->one();
    }

    /**
     * Get user's or users' all relations, or by specified groups.
     * @param BaseUserModel|string|array $user Initiator or its GUID, or Initiators or their GUIDs.
     * @param BaseUserRelationGroupModel|string|array|null $groups UserRelationGroup
     * or its guid, or array of them. If you do not want to delimit the groups, please assign null.
     * @return array all eligible relations
     */
    public static function findOnesAllRelations($user, $groups = null)
    {
        return static::find()->initiators($user)->groups($groups)->all();
    }

    /**
     * Initialize groups attribute.
     * @param ModelEvent $event
     */
    public function onInitGroups($event)
    {
        $sender = $event->sender;
        $sender->removeAllGroups();
    }

    /**
     * Initialize remark attribute.
     * @param ModelEvent $event
     */
    public function onInitRemark($event)
    {
        $sender = $event->sender;
        $remarkAttribute = $sender->remarkAttribute;
        is_string($remarkAttribute) ? $sender->$remarkAttribute = '' : null;
    }

    /**
     * The event triggered after insert new relation.
     * The opposite relation should be inserted without triggering events
     * simultaneously after new relation inserted,
     * @param ModelEvent $event
     * @throws IntegrityException throw if insert failed.
     */
    public function onInsertRelation($event)
    {
        $sender = $event->sender;
        if ($sender->relationType == static::$relationMutual) {
            $opposite = static::buildOppositeRelation($sender);
            $opposite->off(static::EVENT_AFTER_INSERT, [$opposite, 'onInsertRelation']);
            if (!$opposite->save()) {
                $opposite->recordWarnings();
                throw new IntegrityException('Reverse relation insert failed.');
            }
            $opposite->on(static::EVENT_AFTER_INSERT, [$opposite, 'onInsertRelation']);
        }
    }

    /**
     * The event triggered after update relation.
     * The opposite relation should be updated without triggering events
     * simultaneously after existed relation removed.
     * @param ModelEvent $event
     * @throw IntegrityException throw if update failed.
     */
    public function onUpdateRelation($event)
    {
        $sender = $event->sender;
        if ($sender->relationType == static::$relationMutual) {
            $opposite = static::buildOppositeRelation($sender);
            $opposite->off(static::EVENT_AFTER_UPDATE, [$opposite, 'onUpdateRelation']);
            if (!$opposite->save()) {
                $opposite->recordWarnings();
                throw new IntegrityException('Reverse relation update failed.');
            }
            $opposite->on(static::EVENT_AFTER_UPDATE, [$opposite, 'onUpdateRelation']);
        }
    }

    /**
     * The event triggered after delete relation.
     * The opposite relation should be deleted without triggering events
     * simultaneously after existed relation removed.
     * @param ModelEvent $event
     */
    public function onDeleteRelation($event)
    {
        $sender = $event->sender;
        if ($sender->relationType == static::$relationMutual) {
            $createdByAttribute = $sender->createdByAttribute;
            $otherGuidAttribute = $sender->otherGuidAttribute;
            $sender->off(static::EVENT_AFTER_DELETE, [$sender, 'onDeleteRelation']);
            static::removeAllRelations($sender->$otherGuidAttribute, $sender->$createdByAttribute);
            $sender->on(static::EVENT_AFTER_DELETE, [$sender, 'onDeleteRelation']);
        }
    }
}
