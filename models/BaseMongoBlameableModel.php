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

namespace rhosocial\base\models\models;

use MongoDB\BSON\Binary;
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
    public function getHost()
    {
        $hostClass = $this->hostClass;
        $user = $hostClass::buildNoInitModel();
        /* @var BaseUserModel $user */
        return $this->hasOne($hostClass::className(), ['refGUID' => 'createdByAttribute']);
    }
    
    public function getCreatedByAttribute()
    {
        $createdByAttribute = $this->createdByAttribute;
        return (!is_string($createdByAttribute) || empty($createdByAttribute)) ? null : $this->$createdByAttribute->getData();
    }
    
    /**
     * 
     * @param IdentityInterface $host
     * @return boolean
     */
    public function setHost($host)
    {
        if ($host instanceof $this->hostClass || $host instanceof IdentityInterface) {
            return $this->{$this->createdByAttribute} = new Binary($host->getGUID(), Binary::TYPE_UUID);
        }
        if (is_string($host) && preg_match(Number::GUID_REGEX, $host)) {
            return $this->{$this->createdByAttribute} = new Binary(Number::guid_bin($host), Binary::TYPE_UUID);
        }
        if (strlen($host) == 16) {
            return $this->{$this->createdByAttribute} = new Binary($host, Binary::TYPE_UUID);
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
        $hostClass = $this->hostClass;
        $host = $hostClass::buildNoInitModel();
        /* @var $user BaseUserModel */
        return $this->hasOne($hostClass::className(), ['refGUID' => 'updatedByAttribute']);
    }
    
    public function getUpdatedByAttribute()
    {
        $updatedByAttribute = $this->updatedByAttribute;
        return (!is_string($updatedByAttribute) || empty($updatedByAttribute)) ? null : $this->$updatedByAttribute->getData();
    }
    
    /**
     * 
     * @param IdentityInterface $user
     * @return boolean
     */
    public function setUpdater($updater)
    {
        if (!is_string($this->updatedByAttribute) || empty($this->updatedByAttribute)) {
            return false;
        }
        if ($updater instanceof $this->hostClass || $updater instanceof IdentityInterface) {
            return $this->{$this->updatedByAttribute} = new Binary($updater->getGUID(), Binary::TYPE_UUID);
        }
        if (is_string($updater) && preg_match(Number::GUID_REGEX, $updater)) {
            return $this->{$this->updatedByAttribute} = new Binary(Number::guid_bin($updater), Binary::TYPE_UUID);
        }
        if (strlen($updater) == 16) {
            return $this->{$this->updatedByAttribute} = new Binary($updater, Binary::TYPE_UUID);
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
