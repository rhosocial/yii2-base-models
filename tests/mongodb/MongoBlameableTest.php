<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2023 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests\mongodb;

use MongoDB\BSON\Binary;
use rhosocial\base\models\tests\data\ar\MongoBlameable;
use Throwable;
use yii\db\IntegrityException;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class MongoBlameableTest extends MongoBlameableTestCase
{
    /**
     * @group mongo
     * @group blameable
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException|Throwable
     */
    public function testNew(int $severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group mongo
     * @group blameable
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException|Throwable
     */
    public function testCreator(int $severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $user = $this->blameable->getUser()->one();
        $this->assertEquals($this->user->getGUID(), $user->getGUID());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group mongo
     * @group blameable
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException|Throwable
     */
    public function testUpdater(int $severalTimes)
    {
        $this->assertTrue($this->user->register([$this->blameable]));
        $updater = $this->blameable->getUpdater()->one();
        $this->assertEquals($this->user->getGUID(), $updater->getGUID());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group mongo
     * @group blameable
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException|Throwable
     */
    public function testFindByIdentity(int $severalTimes)
    {
        $this->assertNull(MongoBlameable::findByIdentity($this->user)->one());
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertInstanceOf(MongoBlameable::class, MongoBlameable::findByIdentity($this->user)->one());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group mongo
     * @group blameable
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException|Throwable
     */
    public function testFindByCreator(int $severalTimes)
    {
        $this->assertNull(MongoBlameable::find()->createdBy($this->user)->one());
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertInstanceOf(MongoBlameable::class, MongoBlameable::find()->createdBy($this->user)->one());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group mongo
     * @group blameable
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException|Throwable
     */
    public function testFindByUpdater(int $severalTimes)
    {
        $this->assertNull(MongoBlameable::find()->updatedBy($this->user)->one());
        $this->assertTrue($this->user->register([$this->blameable]));
        $this->assertInstanceOf(MongoBlameable::class, MongoBlameable::find()->updatedBy($this->user)->one());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group mongo
     * @group blameable
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException|Throwable
     */
    public function testSetHostBinary(int $severalTimes)
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
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException|Throwable
     */
    public function testSetHostGUIDREGEX(int $severalTimes)
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
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException|Throwable
     */
    public function testSetHostBinaryString(int $severalTimes)
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
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException|Throwable
     */
    public function testSetHostInvalid(int $severalTimes)
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
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException
     */
    public function testSetUpdaterBinary(int $severalTimes)
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
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException|Throwable
     */
    public function testSetUpdaterIdentity(int $severalTimes)
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
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException
     */
    public function testSetUpdaterGUIDREGEX(int $severalTimes)
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
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException
     */
    public function testSetUpdaterBinaryString(int $severalTimes)
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
     * @param int $severalTimes
     * @dataProvider severalTimes
     * @throws IntegrityException
     */
    public function testSetUpdaterInvalid(int $severalTimes)
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

    public function severalTimes(): \Generator
    {
        for ($i = 0; $i < 3; $i++)
        {
            yield [$i];
        }
    }
}
