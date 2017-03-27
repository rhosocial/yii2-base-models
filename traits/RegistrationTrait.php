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

use Yii;
use yii\base\ModelEvent;
use yii\db\IntegrityException;
use yii\rbac\ManagerInterface;
use yii\rbac\Item;

/**
 * User features concerning registration.
 *
 * @property-read mixed $authManager
 * @property mixed $source
 * @property array $sourceRules rules associated with source attribute.
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
trait RegistrationTrait
{

    /**
     * @event Event an event that is triggered after user is registered successfully.
     */
    public static $eventAfterRegister = "afterRegister";

    /**
     * @event Event an event that is triggered before registration.
     */
    public static $eventBeforeRegister = "beforeRegister";

    /**
     * @event Event an event that is triggered when registration failed.
     */
    public static $eventRegisterFailed = "registerFailed";

    /**
     * @event Event an event that is triggered after user is deregistered successfully.
     */
    public static $eventAfterDeregister = "afterDeregister";

    /**
     * @event Event an event that is triggered before deregistration.
     */
    public static $eventBeforeDeregister = "beforeDeregister";

    /**
     * @event Event an event that is triggered when deregistration failed.
     */
    public static $eventDeregisterFailed = "deregisterFailed";

    /**
     * @var string name of attribute which store the source. if you don't want to
     * record source, please assign false.
     */
    public $sourceAttribute = 'source';
    private $_sourceRules = [];
    public static $sourceSelf = '0';

    /**
     * @var string auth manager component id.
     */
    public $authManagerId = 'authManager';

    /**
     * Get auth manager. If auth manager not configured, Yii::$app->authManager
     * will be given.
     * @return ManagerInterface
     */
    public function getAuthManager()
    {
        $authManagerId = $this->authManagerId;
        return empty($authManagerId) ? Yii::$app->authManager : Yii::$app->$authManagerId;
    }

    /**
     * Register new user.
     * It is equivalent to store the current user and its associated models into
     * database synchronously. The registration will be terminated immediately
     * if any errors occur in the process, and all the earlier steps succeeded
     * are rolled back.
     * If auth manager configured, and auth role(s) provided, it(they) will be
     * assigned to user after registration.
     * If current user is not a new one(isNewRecord = false), the registration
     * will be skipped and return false.
     * The $eventBeforeRegister will be triggered before registration starts.
     * If registration finished, the $eventAfterRegister will be triggered. or
     * $eventRegisterFailed will be triggered when any errors occured.
     * @param array $associatedModels The models associated with user to be stored synchronously.
     * @param string|Item[] $authRoles auth name, auth instance, auth name array or auth instance array.
     * @return boolean Whether the registration succeeds or not.
     * @throws IntegrityException when inserting user and associated models failed.
     */
    public function register($associatedModels = [], $authRoles = [])
    {
        if (!$this->getIsNewRecord()) {
            return false;
        }
        $this->trigger(static::$eventBeforeRegister);
        $transaction = $this->getDb()->beginTransaction();
        try {
            if (!$this->save()) {
                throw new IntegrityException('Registration Error(s) Occured: User Save Failed.', $this->getErrors());
            }
            if (($authManager = $this->getAuthManager()) && !empty($authRoles)) {
                if (is_string($authRoles) || $authRoles instanceof Item || !is_array($authRoles)) {
                    $authRoles = [$authRoles];
                }
                foreach ($authRoles as $role) {
                    if (is_string($role)) {
                        $role = $authManager->getRole($role);
                    }
                    if ($role instanceof Item) {
                        $authManager->assign($role, $this->getGUID());
                    }
                }
            }
            if (!empty($associatedModels) && is_array($associatedModels)) {
                foreach ($associatedModels as $model) {
                    if (!$model->save()) {
                        throw new IntegrityException
                        ('Registration Error(s) Occured: Associated Models Save Failed.', $model->getErrors());
                    }
                }
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            $this->trigger(static::$eventRegisterFailed);
            if (YII_DEBUG || YII_ENV !== YII_ENV_PROD) {
                Yii::error($ex->getMessage(), __METHOD__);
                return $ex;
            }
            Yii::warning($ex->getMessage(), __METHOD__);
            return false;
        }
        $this->trigger(static::$eventAfterRegister);
        return true;
    }

    /**
     * Deregister current user itself.
     * It is equivalent to delete current user and its associated models. BUT it
     * deletes current user ONLY, the associated models will not be deleted
     * forwardly. So you should set the foreign key of associated models' table
     * referenced from primary key of user table, and their association mode is
     * 'on update cascade' and 'on delete cascade'.
     * the $eventBeforeDeregister will be triggered before deregistration starts.
     * if deregistration finished, the $eventAfterDeregister will be triggered. or
     * $eventDeregisterFailed will be triggered when any errors occured.
     * @return boolean Whether deregistration succeeds or not.
     * @throws IntegrityException when deleting user failed.
     */
    public function deregister()
    {
        if ($this->getIsNewRecord()) {
            return false;
        }
        $this->trigger(static::$eventBeforeDeregister);
        $transaction = $this->getDb()->beginTransaction();
        try {
            $result = $this->delete();
            if ($result == 0) {
                throw new IntegrityException('User has not existed.');
            }
            if ($result != 1) {
                throw new IntegrityException('Deregistration Error(s) Occured.', $this->getErrors());
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            $this->trigger(static::$eventDeregisterFailed);
            if (YII_DEBUG || YII_ENV !== YII_ENV_PROD) {
                Yii::error($ex->getMessage(), __METHOD__);
                return $ex;
            }
            Yii::warning($ex->getMessage(), __METHOD__);
            return false;
        }
        $this->trigger(static::$eventAfterDeregister);
        return $result == 1;
    }

    /**
     * Get source.
     * @return string
     */
    public function getSource()
    {
        $sourceAttribute = $this->sourceAttribute;
        return is_string($sourceAttribute) ? $this->$sourceAttribute : null;
    }

    /**
     * Set source.
     * @param string $source
     */
    public function setSource($source)
    {
        $sourceAttribute = $this->sourceAttribute;
        return is_string($sourceAttribute) ? $this->$sourceAttribute = $source : null;
    }

    /**
     * Get the rules associated with source attribute.
     * @return array rules.
     */
    public function getSourceRules()
    {
        if (!is_string($this->sourceAttribute) || empty($this->sourceAttribute)) {
            return [];
        }
        if (empty($this->_sourceRules)) {
            $this->_sourceRules = [
                [[$this->sourceAttribute], 'required'],
                [[$this->sourceAttribute], 'string'],
            ];
        }
        return $this->_sourceRules;
    }

    /**
     * Set the rules associated with source attribute.
     * @param array $rules
     */
    public function setSourceRules($rules)
    {
        if (!empty($rules) && is_array($rules)) {
            $this->_sourceRules = $rules;
        }
    }

    /**
     * Initialize the source attribute with $sourceSelf.
     * This method is ONLY used for being triggered by event. DO NOT call,
     * override or modify it directly, unless you know the consequences.
     * @param ModelEvent $event
     */
    public function onInitSourceAttribute($event)
    {
        $sender = $event->sender;
        $sourceAttribute = $sender->sourceAttribute;
        $sender->$sourceAttribute = static::$sourceSelf;
    }
}
