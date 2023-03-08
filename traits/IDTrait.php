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

use rhosocial\base\helpers\Number;
use Yii;
use yii\base\Exception;
use yii\base\ModelEvent;

/**
 * Entity features concerning ID.
 * @property-read array $idRules
 * @property mixed $ID
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait IDTrait
{
    /**
     * @var string|false OPTIONAL. The attribute that will receive the IDentifier No.
     * You can set this property to false if you don't use this feature.
     */
    public string|false $idAttribute = 'id';
    const ID_TYPE_STRING = 0;
    const ID_TYPE_INTEGER = 1;
    const ID_TYPE_AUTO_INCREMENT = 2;
    public static $idTypeString = 0;
    public static $idTypeInteger = 1;
    public static $idTypeAutoIncrement = 2;

    /**
     * @var int type of id attribute.
     */
    public int $idAttributeType = 0;

    /**
     * @var bool Determines whether its ID has been pre-assigned. It will not
     * generate or assign ID if true.
     */
    public bool $idPreassigned = false;

    /**
     * @var string The prefix of ID. When ID type is Auto Increment, this feature
     * is skipped.
     */
    public string $idAttributePrefix = '';

    /**
     * @var int OPTIONAL. The length of id attribute value, and max length
     * of this attribute in rules. If you set $idAttribute to false or ID type
     * to Auto Increment, this property will be ignored.
     */
    public int $idAttributeLength = 4;

    /**
     * @var bool Determine whether the ID is safe for validation.
     */
    protected bool $idAttributeSafe = false;

    /**
     * Get ID.
     * @return int|string|null
     */
    public function getID(): int|string|null
    {
        $idAttribute = $this->idAttribute;
        return (!empty($idAttribute)) ? $this->$idAttribute : null;
    }

    /**
     * Set id.
     * @param int|string $identity
     * @return int|string|null
     */
    public function setID(int|string $identity): int|string|null
    {
        $idAttribute = $this->idAttribute;
        return (!empty($idAttribute)) ? $this->$idAttribute = $identity : null;
    }

    /**
     * Attach `onInitGuidAttribute` event.
     * @param string $eventName
     */
    protected function attachInitIDEvent(string $eventName): void
    {
        $this->on($eventName, [$this, 'onInitIDAttribute']);
    }

    /**
     * Initialize the ID attribute with new generated ID.
     * If the model's id is pre-assigned, then it will return directly.
     * If the model's id is auto-increment, the id attribute will be marked safe.
     * This method is ONLY used for being triggered by event. DO NOT call,
     * override or modify it directly, unless you know the consequences.
     * @param ModelEvent $event
     * @throws Exception
     */
    public function onInitIDAttribute($event): void
    {
        $sender = $event->sender;
        /* @var $sender static */
        if ($sender->idPreassigned) {
            return;
        }
        if ($sender->idAttributeType === static::$idTypeAutoIncrement) {
            $sender->idAttributeSafe = true;
            return;
        }
        $idAttribute = $sender->idAttribute;
        if (!empty($idAttribute) && $sender->idAttributeLength > 0) {
            $sender->setID($sender->generateId());
        }
    }

    /**
     * Generate the ID. You can override this method to implement your own
     * generation algorithm.
     * @return false|string|null the generated ID.
     * @throws Exception
     */
    public function generateId(): false|string|null
    {
        if ($this->idAttributeType == self::ID_TYPE_INTEGER) {
            do {
                $result = Number::randomNumber($this->idAttributePrefix, $this->idAttributeLength);
            } while ($this->checkIdExists((int) $result));
            return $result;
        }
        if ($this->idAttributeType == self::ID_TYPE_STRING) {
            return $this->idAttributePrefix .
                Yii::$app->security->generateRandomString($this->idAttributeLength - strlen($this->idAttributePrefix));
        }
        if ($this->idAttributeType == static::$idTypeAutoIncrement) {
            return null;
        }
        return false;
    }
    
    /**
     * Check if $identity existed.
     * @param mixed $identity
     * @return bool
     */
    public function checkIdExists($identity): bool
    {
        if ($identity == null) {
            return false;
        }
        return static::find()->where([$this->idAttribute => $identity])->exists();
    }

    /**
     * Get the rules associated with id attribute.
     * @return array
     */
    public function getIdRules(): array
    {
        if (!$this->idAttribute) {
            return [];
        }
        if ($this->idAttributeSafe || $this->idAttributeType === self::ID_TYPE_AUTO_INCREMENT) {
            return [
                [[$this->idAttribute], 'safe'],
            ];
        }
        if (!empty($this->idAttribute) && $this->idAttributeLength > 0) {
            $rules = [
                [[$this->idAttribute], 'required'],
                [[$this->idAttribute], 'unique'],
            ];
            if ($this->idAttributeType === self::ID_TYPE_INTEGER) {
                $rules[] = [
                    [$this->idAttribute], 'number', 'integerOnly' => true
                ];
            }
            if ($this->idAttributeType === self::ID_TYPE_STRING) {
                $rules[] = [[$this->idAttribute], 'string',
                    'max' => $this->idAttributeLength,];
            }
            return $rules;
        }
        return [];
    }

    /**
     * Composite IDs from models.
     * @param $models
     * @return array|int|string
     */
    public static function compositeIDs($models): int|array|string
    {
        if (!is_array($models) && $models instanceof static) {
            return $models->getID();
        }
        $ids = [];
        foreach ($models as $model) {
            if ($model instanceof static) {
                $ids[] = $model->getID();
            }
        }
        return $ids;
    }
}

