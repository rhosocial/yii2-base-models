<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\models;

use rhosocial\base\helpers\Number;
use rhosocial\base\models\models\BaseUserModel;
use rhosocial\base\models\queries\BaseMongoBlameableQuery;
use rhosocial\base\models\traits\BlameableTrait;
use yii\web\IdentityInterface;

/**
 * Description of BaseMongoBlameableModel
 *
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseMongoBlameableModel extends BaseMongoEntityModel
{
    use BlameableTrait;

    /**
     * Initialize the blameable model.
     * If query class is not specified, [[BaseMongoBlameableQuery]] will be taken.
     */
    public function init()
    {
        if (!is_string($this->queryClass) || empty($this->queryClass)) {
            $this->queryClass = BaseMongoBlameableQuery::class;
        }
        if ($this->skipInit) {
            return;
        }
        $this->initBlameableEvents();
        parent::init();
    }

    /**
     * Get the query class with specified identity.
     * @param BaseUserModel $identity
     * @return BaseMongoBlameableQuery
     */
    public static function findByIdentity($identity = null)
    {
        return static::find()->byIdentity($identity);
    }

    /**
     * Because every document has a `MongoId" class, this class no longer needs GUID feature.
     * @var boolean determines whether enable the GUID features.
     */
    public $guidAttribute = false;
    public $idAttribute = '_id';

    /**
     * @inheritdoc
     * You can override this method if enabled fields cannot meet your requirements.
     * @return array
     */
    public function attributes()
    {
        return $this->enabledFields();
    }

    /**
     * Get blame who owned this blameable model.
     * NOTICE! This method will not check whether `$userClass` exists. You should
     * specify it in `init()` method.
     * @return BaseUserQuery user.
     */
    public function getUser()
    {
        $userClass = $this->userClass;
        $user = $userClass::buildNoInitModel();
        /* @var BaseUserModel $user */
        return $this->hasOne($userClass::className(), [$user->readableGuidAttribute => $this->createdByAttribute]);
    }
    
    /**
     * 
     * @param IdentityInterface $user
     * @return boolean
     */
    public function setUser($user)
    {
        if ($user instanceof $this->userClass || $user instanceof IdentityInterface) {
            return $this->{$this->createdByAttribute} = $user->getReadableGUID();
        }
        if (is_string($user) && preg_match(Number::GUID_REGEX, $user)) {
            return $this->{$this->createdByAttribute} = $user;
        }
        if (strlen($user) == 16) {
            return $this->{$this->createdByAttribute} = Number::guid(false, false, $user);
        }
        return false;
    }

    /**
     * Get updater who updated this blameable model recently.
     * NOTICE! This method will not check whether `$userClass` exists. You should
     * specify it in `init()` method.
     * @return BaseUserQuery user.
     */
    public function getUpdater()
    {
        if (!is_string($this->updatedByAttribute) || empty($this->updatedByAttribute)) {
            return null;
        }
        $userClass = $this->userClass;
        $user = $userClass::buildNoInitModel();
        /* @var $user BaseUserModel */
        return $this->hasOne($userClass::className(), [$user->readableGuidAttribute => $this->updatedByAttribute]);
    }
    
    /**
     * 
     * @param IdentityInterface $user
     * @return boolean
     */
    public function setUpdater($user)
    {
        if (!is_string($this->updatedByAttribute) || empty($this->updatedByAttribute)) {
            return false;
        }
        if ($user instanceof $this->userClass || $user instanceof IdentityInterface) {
            return $this->{$this->updatedByAttribute} = $user->getReadableGUID();
        }
        if (is_string($user) && preg_match(Number::GUID_REGEX, $user)) {
            return $this->{$this->updatedByAttribute} = $user;
        }
        if (strlen($user) == 16) {
            return $this->{$this->updatedByAttribute} = Number::guid(false, false, $user);
        }
        return false;
    }

    /**
     * Return the current user's GUID if current model doesn't specify the owner
     * yet, or return the owner's GUID if current model has been specified.
     * This method is ONLY used for being triggered by event. DO NOT call,
     * override or modify it directly, unless you know the consequences.
     * @param ModelEvent $event
     * @return string the GUID of current user or the owner.
     */
    public function onGetCurrentUserGuid($event)
    {
        $sender = $event->sender;
        /* @var $sender static */
        if (isset($sender->attributes[$sender->createdByAttribute])) {
            return $sender->attributes[$sender->createdByAttribute];
        }
        $identity = \Yii::$app->user->identity;
        /* @var BaseUserModel $identity */
        if ($identity) {
            return $identity->getReadableGUID();
        }
    }
}
