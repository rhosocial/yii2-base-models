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

namespace rhosocial\base\models\tests\meta;

use rhosocial\base\models\tests\data\ar\blameable\Meta;
use rhosocial\base\models\tests\user\UserTestCase;

/**
 * @author vistart <i@vistart.me>
 */
class MetaTest extends UserTestCase
{
    /**
     * @group meta
     */
    public function testNew()
    {
        $this->assertTrue($this->user->register());
        
        $key = \Yii::$app->security->generateRandomString();
        $value = \Yii::$app->security->generateRandomString();
        
        $this->assertTrue(Meta::set($key, $value, $this->user->guid));
        $this->assertNull(Meta::get($key . \Yii::$app->security->generateRandomString(1)));
        $this->assertEquals($value, Meta::get($key));
        $this->assertEquals($value, Meta::gets()[$key]);
        $this->assertEquals($value, Meta::gets([$key])[$key]);
        $value = \Yii::$app->security->generateRandomString();
        Meta::sets(null);
        Meta::sets([$key => $value]);
        $this->assertEquals($value, Meta::gets([$key])[$key]);
        $this->assertEquals(1, Meta::set($key));
        $this->assertEquals(0, Meta::remove($key));
        $this->assertNull(Meta::get($key));
        
        $this->assertTrue($this->user->deregister());
    }
    
    /**
     * @group meta
     */
    public function testInstance()
    {
        $this->assertTrue($this->user->register());
        
        $key = \Yii::$app->security->generateRandomString();
        $value = \Yii::$app->security->generateRandomString();
        
        $meta = $this->user->create(Meta::class, ['key' => $key, 'value' => $value]);
        $this->assertTrue($meta->save());
        
        $this->assertEquals($value, Meta::get($key));
        $this->assertEquals($value, Meta::gets()[$key]);
        $this->assertEquals($value, Meta::gets([$key])[$key]);
        
        $this->assertEquals($key, $meta->getKey());
        $this->assertEquals($key, $meta->key);
        
        $this->assertEquals($value, $meta->getValue());
        $this->assertEquals($value, $meta->value);
        
        $key = \Yii::$app->security->generateRandomString();
        $this->assertNotEquals($key, $meta->key);
        $this->assertEquals($key, $meta->setKey($key));
        
        $this->assertTrue($meta->save());
        
        $value = \Yii::$app->security->generateRandomString();
        $this->assertNotEquals($value, $meta->value);
        $this->assertEquals($value, $meta->setValue($value));
        
        $this->assertTrue($meta->save());
        
        $this->assertEquals($value, Meta::get($key));
        $this->assertEquals($value, Meta::gets()[$key]);
        $this->assertEquals($value, Meta::gets([$key])[$key]);
        
        $this->assertTrue($this->user->deregister());
    }
}