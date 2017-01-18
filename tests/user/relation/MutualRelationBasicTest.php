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

namespace rhosocial\base\models\tests\user\relation;

/**
 * @author vistart <i@vistart.me>
 */
class MutualRelationBasicTest extends MutualRelationTestCase
{
    /**
     * @group user
     * @group relaion
     * @group relation-mutual
     */
    public function testNormal()
    {
        $this->assertTrue($this->user->register());
        $this->assertTrue($this->other1->register());
        $this->assertTrue($this->relationNormal->save());
        $this->assertTrue($this->user->deregister());
        $this->assertEquals(0, $this->relationNormal->remove());
        $this->assertTrue($this->other1->deregister());
    }
}