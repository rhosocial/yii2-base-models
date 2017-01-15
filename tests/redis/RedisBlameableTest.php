<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2017 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests\redis;

use rhosocial\base\models\tests\data\ar\RedisBlameable;

/**
 * @author vistart <i@vistart.me>
 */
class RedisBlameableTest extends RedisBlameableTestCase
{
    /**
     * @group redis
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testNew($severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group redis
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testFindByIdentity($severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $blameable = RedisBlameable::findByIdentity($this->user)->one();
        $this->assertInstanceOf(RedisBlameable::class, $blameable);
        $this->assertEquals(1, $blameable->delete());
        $nonExists = RedisBlameable::findByIdentity($this->user)->one();
        $this->assertNull($nonExists);
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group redis
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testHasEverEdited($severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertFalse($this->blameable->hasEverEdited());
        sleep(1);
        $this->blameable->setContent(\Yii::$app->security->generateRandomString());
        $this->assertTrue($this->blameable->save());
        $this->assertTrue($this->blameable->hasEverEdited()); // RedisBlameable 没有实现
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group redis
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testCreatedBy($severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertTrue($this->user->equals($this->blameable->user));
        $this->assertTrue($this->user->equals($this->blameable->getUser()->one()));
        $this->assertTrue($this->blameable->user->equals($this->user));
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group redis
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testQueryConfirmed($severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $query = RedisBlameable::find();
        $confirmedQuery = RedisBlameable::find()->confirmed();
        $this->assertEquals($query, $confirmedQuery);
        $this->assertTrue($this->user->deregister());
    }
    
    public function severalTimes()
    {
        for ($i = 0; $i < 3; $i++)
        {
            yield [$i];
        }
    }
}