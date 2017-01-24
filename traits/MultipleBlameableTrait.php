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

use rhosocial\base\helpers\Number;
use rhosocial\base\models\events\MultipleBlameableEvent;
use rhosocial\base\models\models\BaseUserModel;
use yii\base\ModelEvent;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;

/**
 * 一个模型的某个属性可能对应多个责任者，该 trait 用于处理此种情况。此种情况违反
 * 了关系型数据库第一范式，因此此 trait 只适用于责任者属性修改不频繁的场景，在开
 * 发时必须严格测试数据一致性，并同时考量性能。
 * Basic Principles:
 * <ol>
 * <li>when adding blame, it will check whether each of blames including to be
 * added is valid.
 * </li>
 * <li>when removing blame, as well as counting, getting or setting list of them,
 * it will also check whether each of blames is valid.
 * </li>
 * <li>By default, once blame was deleted, the guid of it is not removed from
 * list of blames immediately. It will check blame if valid when adding, removing,
 * counting, getting and setting it. You can define a blame model and attach it
 * events triggered when inserting, updating and deleting a blame, then disable
 * checking the validity of blames.
 * </li>
 * </ol>
 * Notice:
 * <ol>
 * <li>You must specify two properties: $multiBlamesClass and $multiBlamesAttribute.
 * <ul>
 * <li>$multiBlamesClass specify the class name of blame.</li>
 * <li>$multiBlamesAttribute specify the field name of blames.</li>
 * </ul>
 * </li>
 * <li>You should rename the following methods to be needed optionally.</li>
 * </ol>
 * @property-read array $multiBlamesAttributeRules
 * @property string[] $blameGuids
 * @property-read array $allBlames
 * @property-read array $nonBlameds
 * @property-read integer $blamesCount
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait MultipleBlameableTrait
{

    /**
     * @var string class name of multiple blameable class.
     */
    public $multiBlamesClass = '';

    /**
     * @var string name of multiple blameable attribute.
     */
    public $multiBlamesAttribute = 'blames';

    /**
     * @var integer the limit of blames. it should be greater than or equal 1, and
     * less than or equal 10.
     */
    public $blamesLimit = 10;

    /**
     * @var boolean determines whether blames list has been changed.
     */
    public $blamesChanged = false;

    /**
     * @var string event name.
     */
    public static $eventMultipleBlamesChanged = 'multipleBlamesChanged';

    /**
     * Get the rules associated with multiple blameable attribute.
     * @return array rules.
     */
    public function getMultipleBlameableAttributeRules()
    {
        return is_string($this->multiBlamesAttribute) ? [
            [[$this->multiBlamesAttribute], 'string', 'max' => $this->blamesLimit * 16],
            [[$this->multiBlamesAttribute], 'default', 'value' => ''],
            ] : [];
    }

    /**
     * Add specified blame.
     * @param {$this->multiBlamesClass}|string $blame
     * @return false|array
     * @throws InvalidParamException if blame existed.
     * @throws InvalidCallException if blame limit reached.
     */
    public function addBlame($blame)
    {
        if (!is_string($this->multiBlamesAttribute)) {
            return false;
        }
        $blameGuid = '';
        if (is_string($blame)) {
            $blameGuid = $blame;
        }
        if ($blame instanceof $this->multiBlamesClass) {
            $blameGuid = $blame->getGUID();
        }
        $blameGuids = $this->getBlameGuids(true);
        if (array_search($blameGuid, $blameGuids)) {
            throw new InvalidParamException('the blame has existed.');
        }
        if ($this->getBlamesCount() >= $this->blamesLimit) {
            throw new InvalidCallException("the limit($this->blamesLimit) of blames has been reached.");
        }
        $blameGuids[] = $blameGuid;
        $this->setBlameGuids($blameGuids);
        return $this->getBlameGuids();
    }

    /**
     * Create blame.
     * @param BaseUserModel $user who will own this blame.
     * @param array $config blame class configuration array.
     * @return {$this->multiBlamesClass}
     */
    public static function createBlame($user, $config = [])
    {
        if (!($user instanceof BaseUserModel)) {
            $message = 'the type of user instance must be the extended class of BaseUserModel.';
            throw new InvalidParamException($message);
        }
        $mbClass = static::buildNoInitModel();
        $mbi = $mbClass->multiBlamesClass;
        return $user->create($mbi::className(), $config);
    }

    /**
     * Add specified blame, or create it before adding if doesn't exist.
     * But you should save the blame instance before adding it, or the operation
     * will fail.
     * @param {$this->multiBlamesClass}|string|array $blame
     * It will be regarded as blame's guid if it is a string. And assign the
     * reference parameter $blame the instance if it existed, or create one if not
     * found.
     * If it is {$this->multiBlamesClass} instance and existed, then will add it, or
     * false will be given if it is not found in database. So if you want to add
     * blame instance, you should save it before adding.
     * If it is a array, it will be regarded as configuration array of blame.
     * Notice! This parameter passed by reference, so it must be a variable!
     * @param BaseUserModel $user whose blame.
     * If null, it will take this blameable model's user.
     * @return false|array false if blame created failed or not enable this feature.
     * blames guid array if created and added successfully.
     * @throws InvalidConfigException
     * @throws InvalidParamException
     * @see addBlame()
     */
    public function addOrCreateBlame(&$blame = null, $user = null)
    {
        if (!is_string($this->multiBlamesClass)) {
            throw new InvalidConfigException('$multiBlamesClass must be specified if you want to use multiple blameable features.');
        }
        if (is_array($blame) || $blame === null) {
            if ($user == null) {
                $user = $this->host;
            }
            $blame = static::getOrCreateBlame($blame, $user);
            if (!$blame->save()) {
                return false;
            }
            return $this->addBlame($blame->getGUID());
        }
        $blameGuid = '';
        if (is_string($blame)) {
            $blameGuid = $blame;
        }
        if ($blame instanceof $this->multiBlamesClass) {
            $blameGuid = $blame->getGUID();
        }
        if (($mbi = static::getBlame($blameGuid)) !== null) {
            return $this->addBlame($mbi);
        }
        return false;
    }

    /**
     * Remove specified blame.
     * @param {$this->multiBlamesClass}|string $blame
     * @return false|array all guids in json format.
     */
    public function removeBlame($blame)
    {
        if (!is_string($this->multiBlamesAttribute)) {
            return false;
        }
        $blameGuid = '';
        if (is_string($blame)) {
            $blameGuid = $blame;
        }
        if ($blame instanceof $this->multiBlamesClass) {
            $blameGuid = $blame->getGUID();
        }
        $blameGuids = $this->getBlameGuids(true);
        if (($key = array_search($blameGuid, $blameGuids)) !== false) {
            unset($blameGuids[$key]);
            $this->setBlameGuids($blameGuids);
        }
        return $this->getBlameGuids();
    }

    /**
     * Remove all blames.
     */
    public function removeAllBlames()
    {
        $this->setBlameGuids();
    }

    /**
     * Count the blames.
     * @return integer
     */
    public function getBlamesCount()
    {
        return count($this->getBlameGuids(true));
    }

    /**
     * Get the guid array of blames. it may check all guids if valid before return.
     * @param boolean $checkValid determines whether checking the blame is valid.
     * @return array all guids in json format.
     */
    public function getBlameGuids($checkValid = false)
    {
        $multiBlamesAttribute = $this->multiBlamesAttribute;
        if ($multiBlamesAttribute === false) {
            return [];
        }
        $guids = Number::divide_guid_bin($this->$multiBlamesAttribute);
        if ($checkValid) {
            $guids = $this->unsetInvalidBlames($guids);
        }
        return $guids;
    }

    /**
     * Event triggered when blames list changed.
     * @param MultipleBlameableEvent $event
     */
    public function onBlamesChanged($event)
    {
        $sender = $event->sender;
        $sender->blamesChanged = $event->blamesChanged;
    }

    /**
     * Remove invalid blame guid from provided guid array.
     * @param array $guids guid array of blames.
     * @return array guid array of blames unset invalid.
     */
    protected function unsetInvalidBlames($guids)
    {
        $unchecked = $guids;
        $multiBlamesClass = $this->multiBlamesClass;
        $mbi = $multiBlamesClass::buildNoInitModel();
        foreach ($guids as $key => $guid) {
            $blame = $multiBlamesClass::find()->where([$mbi->guidAttribute => $guid])->exists();
            if (!$blame) {
                unset($guids[$key]);
            }
        }
        $diff = array_diff($unchecked, $guids);
        $eventName = static::$eventMultipleBlamesChanged;
        $this->trigger($eventName, new MultipleBlameableEvent(['blamesChanged' => !empty($diff)]));
        return $guids;
    }

    /**
     * Set the guid array of blames, it may check all guids if valid.
     * @param array $guids guid array of blames.
     * @param boolean $checkValid determines whether checking the blame is valid.
     * @return false|array all guids.
     */
    public function setBlameGuids($guids = [], $checkValid = true)
    {
        if (!is_array($guids) || $this->multiBlamesAttribute === false) {
            return null;
        }
        if ($checkValid) {
            $guids = $this->unsetInvalidBlames($guids);
        }
        $multiBlamesAttribute = $this->multiBlamesAttribute;
        $this->$multiBlamesAttribute = Number::composite_guid($guids);
        return $guids;
    }

    /**
     * Get blame.
     * @param string $blameGuid
     * @return {$this->multiBlamesClass}
     */
    public static function getBlame($blameGuid)
    {
        $self = static::buildNoInitModel();
        if (empty($blameGuid) || empty($self->multiBlamesClass) || !is_string($self->multiBlamesClass) || $self->multiBlamesAttribute === false) {
            return null;
        }
        $mbClass = $self->multiBlamesClass;
        return $mbClass::findOne($blameGuid);
    }
    
    /**
     * Get all blames that owned this relation.
     * @return array
     */
    public function getOwnBlames()
    {
        $guids = $this->getBlameGuids(true);
        $class = $this->multiBlamesClass;
        return $class::findAll($guids);
    }
    
    /**
     * Set blames which would own this relation.
     * @param array $blames
     */
    public function setOwnBlames($blames)
    {
        $guids = static::compositeGUIDs($blames);
        return $this->setBlameGuids($guids, true);
    }
    
    /**
     * Check blame owned this.
     * @param {$this->multiBlamesClass} $blame
     * @return boolean
     */
    public function IsBlameOwned($blame)
    {
        if (!($blame instanceof $this->multiBlamesClass) || $blame->getIsNewRecord() || !static::getBlame($blame->getGUID())) {
            return false;
        }
        return array_search($blame->getGUID(), $this->getBlameGuids(true));
    }

    /**
     * Get or create blame.
     * @param string|array|null $blameGuid
     * @param BaseUserModel $user
     * @return {$this->multiBlamesClass}|null
     */
    public static function getOrCreateBlame($blameGuid, $user = null)
    {
        if (is_string($blameGuid)) {
            return static::getBlame($blameGuid);
        }
        if (is_array($blameGuid) || $blameGuid === null) {
            return static::createBlame($user, $blameGuid);
        }
        return null;
    }

    /**
     * Get all ones to be blamed by `$blame`.
     * @param {$this->multiBlamesClass} $blame
     * @return array
     */
    public static function getBlameds($blame)
    {
        if (is_string($blame)) {
            $blame = static::getBlame($blame);
        }
        if (empty($blame)) {
            return [];
        }
        $noInit = static::buildNoInitModel();
        /* @var $noInit static */
        return static::find()->createdBy($blame->host)->andWhere(['like', $noInit->multiBlamesAttribute, $blame->getGUID()])->all();
    }

    /**
     * Get all the blames of record.
     * @param BaseUserModel $user
     * @return array all blames.
     */
    public static function getAllBlames($user)
    {
        $noInit = static::buildNoInitModel();
        if (empty($noInit->multiBlamesClass) ||
            !is_string($noInit->multiBlamesClass) ||
            $noInit->multiBlamesAttribute === false) {
            return null;
        }
        $multiBlamesClass = $noInit->multiBlamesClass;
        $createdByAttribute = $noInit->createdByAttribute;
        return $multiBlamesClass::findAll([$createdByAttribute => $user->getGUID()]);
    }

    /**
     * Get all records which without any blames.
     * @param BaseUserModel $user
     * @return array all non-blameds.
     */
    public static function getNonBlameds($user = null)
    {
        $noInit = static::buildNoInitModel();
        /* @var $noInit static */
        $createdByAttribute = $noInit->createdByAttribute;
        if (!$user) {
            $user = \Yii::$app->user->identity;
        }
        $cond = [
            $createdByAttribute => $user->getGUID(),
            $noInit->multiBlamesAttribute => ''
        ];
        return static::find()->where($cond)->all();
    }

    /**
     * Initialize blames limit.
     * @param ModelEvent $event
     */
    public function onInitBlamesLimit($event)
    {
        $sender = $event->sender;
        if (!is_int($sender->blamesLimit) || $sender->blamesLimit < 1 || $sender->blamesLimit > 64) {
            $sender->blamesLimit = 10;
        }
    }
    
    /**
     * Get blames which do not own any model.
     * @param BaseUserModel $user
     */
    public static function getEmptyBlames($user)
    {
        $blames = static::getAllBlames($user);
        $emptyGroups = [];
        $noInit = static::buildNoInitModel();
        foreach ($blames as $blame)
        {
            if (static::find()->createdBy($user)->andWhere(['like', $noInit->multiBlamesAttribute, $blame->getGUID()])->exists()) {
                continue;
            }
            $emptyGroups[] = $blame;
        }
        return $emptyGroups;
    }
}
