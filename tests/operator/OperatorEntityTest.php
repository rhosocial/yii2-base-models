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

namespace rhosocial\base\models\tests\operator;

use rhosocial\base\models\tests\data\ar\operator\Entity;
use rhosocial\base\models\tests\data\ar\User;
use rhosocial\base\models\tests\TestCase;
use Yii;
use yii\base\Event;

/**
 * Class OperatorEntityTest
 * @package rhosocial\base\models\tests\operator
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class OperatorEntityTest extends TestCase
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var Entity
     */
    protected $entity;

    protected function setUp()
    {
        parent::setUp();;
        $this->user = new User();
        $this->assertTrue($this->user->register());
        Yii::$app->user->identityClass = get_class($this->user);
        Yii::$app->user->identity = $this->user;
        $this->entity = new Entity();
    }

    protected function tearDown()
    {
        Entity::deleteAll();
        parent::tearDown();
    }

    /**
     * @group entity
     * @group operator
     */
    public function testNormal()
    {
        $this->assertEmpty($this->entity->operator);
        unset($this->entity->operator);
        $this->assertTrue($this->entity->save());
        $this->assertEquals($this->user->getGUID(), $this->entity->operator->getGUID());
    }

    /**
     * @group entity
     * @group operator
     */
    public function testChangeOperator()
    {
        $user = new User();
        $this->assertTrue($user->register());

        $this->assertTrue($this->entity->save());
        $this->assertNotEquals($user->getGUID(), $this->entity->operator->getGUID());
        $this->assertEquals($this->user->getGUID(), $this->entity->operator->getGUID());
        unset($this->entity->operator);

        Yii::$app->user->identity = $user;
        $this->entity->content = Yii::$app->security->generateRandomString(32);
        $this->assertTrue($this->entity->save());
        $this->assertEquals($user->getGUID(), $this->entity->operator->getGUID());
        $this->assertNotEquals($this->user->getGUID(), $this->entity->operator->getGUID());
    }

    /**
     * @group entity
     * @group operator
     */
    public function testDisableOperator()
    {
        $this->entity->operatorAttribute = false;
        $this->assertTrue($this->entity->save());

        $this->assertEmpty($this->entity->getOperatorBehaviors());
        $this->assertEmpty($this->entity->getOperatorRules());
        $this->assertNull($this->entity->operator);
    }

    /**
     * @group entity
     * @group operator
     */
    public function testAssignEmptyIdentity()
    {
        Yii::$app->user->identity = null;
        $this->assertNull($this->entity->onAssignOperator(new Event(['sender' => $this])));
    }
}
