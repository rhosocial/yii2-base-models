<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2022 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\models;

use rhosocial\base\models\queries\BaseBlameableQuery;
use rhosocial\base\models\traits\AdditionalAccountTrait;

/**
 * This abstract class helps you build additional account class.
 *
 * Default settings:
 * - enable GUID.
 * - enable ID, random string, with 8-digit number.
 * - enable IP, accept all IP address.
 * - enable createdAtAttribute.
 * - enable content, and its rule is integer.
 * - enable confirmation, but confirm code.
 * - enable description.
 * the content attribute is used for recording the login-type of account, e.g. ID
 * , email or any other formats.
 * the content type attribute is used for recording the account source, e.g. register
 * from self, or any other account providers.
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
abstract class BaseAdditionalAccountModel extends BaseBlameableModel
{
    use AdditionalAccountTrait;

    public $idAttributeLength = 8;
    public $updatedByAttribute = false;
    public $contentAttribute = 'content'; // Account type, types defined by yourself.
    public $contentAttributeRule = ['integer', 'min' => 0];
    public $contentTypeAttribute = 'source';  // Where did this account origin from, defined by yourself.
    public $contentTypes = [
        0 => 'self', // Self created or bound.
        1 => 'third-party', // bound with third-party account.
    ];
    public $confirmationAttribute = 'confirmed';
    public $confirmCodeAttribute = false;
    public $descriptionAttribute = 'description';

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
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge($this->getAdditionalAccountRules(), parent::rules());
    }
}
