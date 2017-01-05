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

use rhosocial\base\models\queries\BaseMongoBlameableQuery;
use rhosocial\base\models\traits\BlameableTrait;

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
        if (!is_string($this->queryClass)) {
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
     * Because every document has a `MongoId" class, this class is no longer needed GUID feature.
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
}
