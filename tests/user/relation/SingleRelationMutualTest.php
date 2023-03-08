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

namespace rhosocial\base\models\tests\user\relation;

use rhosocial\base\models\tests\data\ar\relation\UserSingleRelation;

/**
 * @version 1.0
 * @author vistart <i@vistart.me>
 */
class SingleRelationMutualTest extends SingleRelationTestCase
{
    /**
     * @var UserSingleRelation
     */
    protected $opposite = null;
    
    protected function setUp() : void {
        parent::setUp();
        $this->opposite = $this->prepareSingleRelation($this->other, $this->user);
    }
    
    protected function tearDown() : void {
        if ($this->opposite instanceof UserSingleRelation)
        {
            $this->opposite->remove();
        }
        $this->opposite = null;
        parent::tearDown();
    }
    
    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testInitiator()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $this->assertTrue($this->relation->getInitiator()->one()->equals($this->user));
        $this->assertFalse($this->relation->getInitiator()->one()->equals($this->other));
        $this->assertTrue($this->relation->initiator->equals($this->user));
        $this->assertFalse($this->relation->initiator->equals($this->other));
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
        $this->assertEquals(0, $this->relation->delete());
    }

    /**
     * @group user
     * @group relation
     * @group relation-single
     */
    public function testRecipient()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other->register());
        $this->assertTrue($this->relation->save());
        
        $this->assertTrue($this->relation->getRecipient()->one()->equals($this->other));
        $this->assertFalse($this->relation->getRecipient()->one()->equals($this->user));
        $this->assertTrue($this->relation->recipient->equals($this->other));
        $this->assertFalse($this->relation->recipient->equals($this->user));
        
        $this->assertTrue($this->user->deregister());
        $this->assertTrue($this->other->deregister());
        $this->assertEquals(0, $this->relation->delete());
    }
}
