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

use MongoDB\BSON\Binary;
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
    public function testFindByCreator($severalTimes)
    {
        $this->assertNull(MongoBlameable::find()->createdBy($this->user)->one());
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertInstanceOf(MongoBlameable::class, MongoBlameable::find()->createdBy($this->user)->one());
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
    
    /**
     * @group mongo
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testSetHostBinary($severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertTrue($this->other->register());
        
        $this->assertTrue($this->blameable->host->equals($this->user));
        $this->assertFalse($this->blameable->host->equals($this->other));
        
        $this->assertInstanceOf(Binary::class, $this->blameable->host = new Binary($this->other->getGUID(), Binary::TYPE_UUID));
        $this->assertTrue($this->blameable->save());
        unset($this->blameable->host);
        $this->assertTrue($this->blameable->host->equals($this->other));
        $this->assertFalse($this->blameable->host->equals($this->user));
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group mongo
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testSetHostGUIDREGEX($severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertTrue($this->other->register());
        
        $this->assertTrue($this->blameable->host->equals($this->user));
        $this->assertFalse($this->blameable->host->equals($this->other));
        
        $this->blameable->host = $this->other->getReadableGUID();
        $this->assertEquals($this->other->getGUID(), $this->blameable->getCreatedBy());
        $this->assertTrue($this->blameable->save());
        unset($this->blameable->host);
        $this->assertTrue($this->blameable->host->equals($this->other));
        $this->assertFalse($this->blameable->host->equals($this->user));
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group mongo
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testSetHostBinaryString($severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertTrue($this->other->register());
        
        $this->assertTrue($this->blameable->host->equals($this->user));
        $this->assertFalse($this->blameable->host->equals($this->other));
        
        $this->blameable->host = $this->other->getGUID();
        $this->assertEquals($this->other->getGUID(), $this->blameable->getCreatedBy());
        $this->assertTrue($this->blameable->save());
        unset($this->blameable->host);
        $this->assertTrue($this->blameable->host->equals($this->other));
        $this->assertFalse($this->blameable->host->equals($this->user));
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group mongo
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testSetHostInvalid($severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertTrue($this->other->register());
        
        $this->assertTrue($this->blameable->host->equals($this->user));
        $this->assertFalse($this->blameable->host->equals($this->other));
        
        $this->blameable->host = false;
        $this->assertEquals($this->user->getGUID(), $this->blameable->getCreatedBy());
        $this->assertTrue($this->blameable->save());
        unset($this->blameable->host);
        $this->assertTrue($this->blameable->host->equals($this->user));
        $this->assertFalse($this->blameable->host->equals($this->other));
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group mongo
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testSetUpdaterBinary($severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertTrue($this->other->register());
        
        $this->assertTrue($this->blameable->updater->equals($this->user));
        $this->assertFalse($this->blameable->updater->equals($this->other));
        
        $this->blameable->updater = new Binary($this->other->getGUID(), Binary::TYPE_UUID);
        $this->assertEquals($this->other->getGUID(), $this->blameable->getUpdatedBy());
        $this->assertTrue($this->blameable->save());
        
        unset($this->blameable->updater);
        
        $this->assertTrue($this->blameable->updater->equals($this->other));
        $this->assertFalse($this->blameable->updater->equals($this->user));
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group mongo
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testSetUpdaterIdentity($severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertTrue($this->other->register());
        
        $this->assertTrue($this->blameable->updater->equals($this->user));
        $this->assertFalse($this->blameable->updater->equals($this->other));
        
        $this->blameable->updater = $this->other;
        $this->assertEquals($this->other->getGUID(), $this->blameable->getUpdatedBy());
        $this->assertTrue($this->blameable->save());
        
        unset($this->blameable->updater);
        
        $this->assertTrue($this->blameable->updater->equals($this->other));
        $this->assertFalse($this->blameable->updater->equals($this->user));
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group mongo
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testSetUpdaterGUIDREGEX($severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertTrue($this->other->register());
        
        $this->assertTrue($this->blameable->updater->equals($this->user));
        $this->assertFalse($this->blameable->updater->equals($this->other));
        
        $this->blameable->updater = $this->other->getReadableGUID();
        $this->assertEquals($this->other->getGUID(), $this->blameable->getUpdatedBy());
        $this->assertTrue($this->blameable->save());
        
        unset($this->blameable->updater);
        
        $this->assertTrue($this->blameable->updater->equals($this->other));
        $this->assertFalse($this->blameable->updater->equals($this->user));
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group mongo
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testSetUpdaterBinaryString($severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertTrue($this->other->register());
        
        $this->assertTrue($this->blameable->updater->equals($this->user));
        $this->assertFalse($this->blameable->updater->equals($this->other));
        
        $this->blameable->updater = $this->other->getGUID();
        $this->assertEquals($this->other->getGUID(), $this->blameable->getUpdatedBy());
        $this->assertTrue($this->blameable->save());
        
        unset($this->blameable->updater);
        
        $this->assertTrue($this->blameable->updater->equals($this->other));
        $this->assertFalse($this->blameable->updater->equals($this->user));
        
        $this->assertTrue($this->other->deregister());
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group mongo
     * @group blameable
     * @param integer $severalTimes
     * @dataProvider severalTimes
     */
    public function testSetUpdaterInvalid($severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertTrue($this->other->register());
        
        $this->assertTrue($this->blameable->updater->equals($this->user));
        $this->assertFalse($this->blameable->updater->equals($this->other));
        
        $this->blameable->updater = false;
        $this->assertEquals($this->user->getGUID(), $this->blameable->getUpdatedBy());
        $this->assertTrue($this->blameable->save());
        
        unset($this->blameable->updater);
        
        $this->assertTrue($this->blameable->updater->equals($this->user));
        $this->assertFalse($this->blameable->updater->equals($this->other));
        
        $this->assertTrue($this->other->deregister());
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