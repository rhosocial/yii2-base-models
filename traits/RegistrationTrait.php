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

use Throwable;
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
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
trait RegistrationTrait
{
    const EVENT_AFTER_REGISTER = 'afterRegister';
    const EVENT_BEFORE_REGISTER = 'beforeRegister';
    const EVENT_REGISTER_FAILED = 'registerFailed';
    const EVENT_AFTER_UNREGISTER = 'afterUnregister';
    const EVENT_BEFORE_UNREGISTER = 'beforeUnregister';
    const EVENT_UNREGISTER_FAILED = 'unregisterFailed';

    /**
     * @var string|false name of attribute which store the source. if you don't want to
     * record source, please assign false.
     */
    public string|false $sourceAttribute = 'source';
    private array $_sourceRules = [];
    public static string $sourceSelf = '0';

    /**
     * @var string auth manager component id.
     */
    public string $authManagerId = 'authManager';

    /**
     * Get auth manager. If auth manager not configured, Yii::$app->authManager
     * will be given.
     * @return ?ManagerInterface
     */
    public function getAuthManager(): ?ManagerInterface
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
     * $eventRegisterFailed will be triggered when any errors occurred.
     * @param array|null $associatedModels The models associated with user to be stored synchronously.
     * @param array|string|null $authRoles auth name, auth instance, auth name array or auth instance array.
     * @return bool Whether the registration succeeds or not.
     */
    public function register(?array $associatedModels = [], array|string|null $authRoles = []): bool
    {
        if (!$this->getIsNewRecord()) {
            return false;
        }
        $this->trigger(self::EVENT_BEFORE_REGISTER);
        $transaction = $this->getDb()->beginTransaction();
        try {
            if (!$this->save()) {
                throw new IntegrityException('Registration Error(s) Occurred: User Save Failed.', $this->getErrors());
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
                        ('Registration Error(s) Occurred: Associated Models Save Failed.', $model->getErrors());
                    }
                }
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            $this->trigger(self::EVENT_REGISTER_FAILED);
            if (YII_DEBUG || YII_ENV != YII_ENV_PROD) {
                Yii::error($ex->getMessage(), __METHOD__);
            }
            Yii::warning($ex->getMessage(), __METHOD__);
            return false;
        }
        $this->trigger(self::EVENT_AFTER_REGISTER);
        return true;
    }

    /**
     * @throws Throwable
     * @throws IntegrityException
     */
    public function deregister(): bool
    {
        return $this->unregister();
    }

    /**
     * Deregister current user itself.
     * It is equivalent to delete current user and its associated models. BUT it
     * deletes current user ONLY, the associated models will not be deleted
     * forwardly. So you should set the foreign key of associated models' table
     * referenced from primary key of user table, and their association mode is
     * 'on update cascade' and 'on delete cascade'.
     * the $eventBeforeDeregister will be triggered before un-registration starts.
     * if un-registration finished, the $eventAfterDeregister will be triggered. or
     * $eventDeregisterFailed will be triggered when any errors occurred.
     * @return bool Whether un-registration succeeds or not.
     * @throws IntegrityException|Throwable when deleting user failed.
     */
    public function unregister(): bool
    {
        if ($this->getIsNewRecord()) {
            return false;
        }
        $this->trigger(self::EVENT_BEFORE_UNREGISTER);
        $transaction = $this->getDb()->beginTransaction();
        try {
            $result = $this->delete();
            if ($result == 0) {
                throw new IntegrityException('User has not existed.');
            }
            if ($result != 1) {
                throw new IntegrityException('Unregistration Error(s) Occurred.', $this->getErrors());
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            $this->trigger(self::EVENT_UNREGISTER_FAILED);
            if (YII_DEBUG || YII_ENV != YII_ENV_PROD) {
                Yii::error($ex->getMessage(), __METHOD__);
            }
            Yii::warning($ex->getMessage(), __METHOD__);
            return false;
        }
        $this->trigger(self::EVENT_AFTER_UNREGISTER);
        return true;
    }

    /**
     * Get source.
     * @return string|null
     */
    public function getSource(): ?string
    {
        $sourceAttribute = $this->sourceAttribute;
        return is_string($sourceAttribute) ? $this->$sourceAttribute : null;
    }

    /**
     * Set source.
     * @param string $source
     * @return string|null
     */
    public function setSource(string $source): ?string
    {
        $sourceAttribute = $this->sourceAttribute;
        return (is_string($sourceAttribute) && !empty($sourceAttribute)) ? $this->$sourceAttribute = $source : null;
    }

    /**
     * Get the rules associated with source attribute.
     * @return array rules.
     */
    public function getSourceRules(): array
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
    public function setSourceRules($rules): void
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
    public function onInitSourceAttribute($event): ?string
    {
        $sender = $event->sender;
        /* @var $sender static */
        return $sender->setSource(static::$sourceSelf);
    }
}
