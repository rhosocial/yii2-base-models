<?php

/**
 *   _   __ __ _____ _____ ___  ____  _____
 *  | | / // // ___//_  _//   ||  __||_   _|
 *  | |/ // /(__  )  / / / /| || |     | |
 *  |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests;

use rhosocial\base\models\tests\data\ar\MongoBlameable;

/**
 * @author vistart <i@vistart.me>
 */
class MongoBlameableTest extends MongoTestCase
{
    /**
     * @group blameable
     * @group mongo
     */
    public function testNew()
    {
        $user = static::prepareUser();
        $content = (string) mt_rand(1, 65535);
        $blameable = $user->create(MongoBlameable::class, ['content' => $content]);
        if ($blameable->save()) {
            $this->assertTrue(true);
        } else {
            var_dump($blameable->errors);
            $this->fail();
        }
        $blameable = MongoBlameable::find()->id($blameable->id)->one();
        $this->assertInstanceOf(MongoBlameable::class, $blameable);
        $this->assertEquals($content, $blameable->content);
        $cbAttribute = $blameable->createdByAttribute;
        $this->assertEquals($user->getReadableGUID(), $blameable->$cbAttribute);
        $blameable = MongoBlameable::findByIdentity($user)->one();
        $this->assertInstanceOf(MongoBlameable::class, $blameable);
        $this->assertEquals($content, $blameable->content);
        $id = $blameable->id;
        $this->assertEquals(1, $blameable->delete());
        $this->assertNull(MongoBlameable::find()->id($id)->one());
        $this->assertTrue($user->deregister());
    }
}