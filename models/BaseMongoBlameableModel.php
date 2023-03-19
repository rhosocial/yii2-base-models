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

namespace rhosocial\base\models\models;

use MongoDB\BSON\Binary;
use rhosocial\base\helpers\Number;
use rhosocial\base\models\queries\BaseUserQuery;
use rhosocial\base\models\queries\BaseMongoBlameableQuery;
use rhosocial\base\models\traits\BaseUserModel;
use rhosocial\base\models\traits\BlameableTrait;
use yii\web\IdentityInterface;

/**
 * Description of BaseMongoBlameableModel
 *
 * @version 2.0
 * @since 1.0
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
     * @var string|false determines whether enable the GUID features.
     */
    public string|false $guidAttribute = false;
    public string|false $idAttribute = '_id';
    public int $idAttributeType = 2;

    /**
     * @inheritdoc
     * You can override this method if enabled fields cannot meet your requirements.
     * @return array
     */
    public function attributes(): array
    {
        return $this->enabledFields();
    }
    
    /**
     * Get created_by attribute.
     * @return string|null
     */
    public function getCreatedBy(): ?string
    {
        $createdByAttribute = $this->createdByAttribute;
        return (!is_string($createdByAttribute) || empty($createdByAttribute)) ? null : $this->$createdByAttribute;
    }

    /**
     * Get updated_by attribute.
     * @return string|null
     */
    public function getUpdatedBy(): ?string
    {
        $updatedByAttribute = $this->updatedByAttribute;
        return (!is_string($updatedByAttribute) || empty($updatedByAttribute)) ? null : $this->$updatedByAttribute;
    }
}
