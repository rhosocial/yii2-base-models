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

namespace rhosocial\base\models\tests\mongodb;

use rhosocial\base\helpers\Number;
use rhosocial\base\models\tests\data\ar\MongoBlameable;

/**
 * @author vistart <i@vistart.me>
 */
class MongoBlameableTest extends MongoBlameableTestCase
{
    /**
     * @group mongo
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
     * @group mongo
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testCreator($severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $user = $this->blameable->getUser()->one();
        $this->assertEquals($this->user->getGUID(), $user->getGUID());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group mongo
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testUpdater($severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $updater = $this->blameable->getUpdater()->one();
        $this->assertEquals($this->user->getGUID(), $updater->getGUID());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group mongo
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testFindByIdentity($severalTimes)
    {
        $this->assertNull(MongoBlameable::findByIdentity($this->user)->one());
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertInstanceOf(MongoBlameable::class, MongoBlameable::findByIdentity($this->user)->one());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group mongo
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testFindByUpdater($severalTimes)
    {
        $this->assertNull(MongoBlameable::find()->updatedBy($this->user)->one());
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertInstanceOf(MongoBlameable::class, MongoBlameable::find()->updatedBy($this->user)->one());
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