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

namespace rhosocial\base\models\tests\user;

use Faker\Factory;
use rhosocial\base\helpers\IP;
use rhosocial\base\models\tests\data\ar\User;
use yii\db\IntegrityException;

/**
 * @version 2.0
 * @since 1.0
 * @author vistart <i@vistart.me>
 */
class IPTest extends UserTestCase
{
    /**
     * @group user
     * @group ip
     * @group registration
     * @dataProvider severalTimes
     */
    public function testAfterRegister()
    {
        $this->user->setIPAddress('192.168.1.1');
        $this->assertTrue($this->user->register());
        $this->assertEquals('192.168.1.1', $this->user->ipAddress);
        $this->assertEquals('192.168.1.1', $this->user->getIPAddress());
        $this->assertTrue($this->user->deregister());
    }

    /**
     * @group user
     * @group ip
     * @param string $ip
     * @throws IntegrityException
     * @dataProvider IPv4Provider
     * @depends      testAfterRegister
     */
    public function testIPv4(string $ip)
    {
        $this->assertEquals(User::IP_ALL_ENABLED, $this->user->enableIP);
        $this->user->setIPAddress($ip);
        $this->assertTrue($this->user->register());
        $this->assertEquals($ip, $this->user->ipAddress);
        $this->assertEquals($ip, $this->user->getIPAddress());
        $this->assertEquals(inet_pton($ip), $this->user->{$this->user->ipAttribute});
        $this->assertEquals(IP::IPv4, $this->user->{$this->user->ipTypeAttribute});
        $this->assertTrue($this->user->deregister());

        $this->user = new User(['enableIP' => User::IP_DISABLED]);
        $this->assertEquals(User::IP_DISABLED, $this->user->enableIP);
        $this->assertEquals(0, $this->user->enableIP & 1);
        $this->assertNull($this->user->setIPAddress($ip));
        $this->assertNull($this->user->ipAddress);
        
        $this->user = new User(['enableIP' => User::IP_V4_ENABLED]);
        $this->assertEquals(User::IP_V4_ENABLED, $this->user->enableIP);
        $this->assertEquals(1, $this->user->enableIP & 1);
        $this->assertEquals(IP::IPv4, $this->user->setIPAddress($ip));
        $this->assertEquals($ip, $this->user->ipAddress);
        
        $this->user = new User(['enableIP' => User::IP_V6_ENABLED]);
        $this->assertEquals(User::IP_V6_ENABLED, $this->user->enableIP);
        $this->assertEquals(0, $this->user->enableIP & 1);
        $this->assertEquals(0, $this->user->setIPAddress($ip));
        $this->assertNull($this->user->ipAddress);
    }

    /**
     * @group user
     * @group ip
     * @param string $ip
     * @throws IntegrityException
     * @dataProvider IPv6Provider
     * @depends      testAfterRegister
     */
    public function testIPv6(string $ip)
    {
        $this->user->setIPAddress($ip);
        $this->assertTrue($this->user->register());
        $this->assertEquals($ip, $this->user->ipAddress);
        $this->assertEquals($ip, $this->user->getIPAddress());
        $this->assertEquals(inet_pton($ip), $this->user->ip);
        $this->assertEquals(IP::IPv6, $this->user->ip_type);
        $this->assertTrue($this->user->deregister());
        
        $this->user = new User(['enableIP' => 0]);
        $this->assertEquals(0, $this->user->enableIP);
        $this->assertEquals(0, $this->user->enableIP & 2);
        $this->assertNull($this->user->setIPAddress($ip));
        $this->assertNull($this->user->ipAddress);
        
        $this->user = new User(['enableIP' => 1]);
        $this->assertEquals(1, $this->user->enableIP);
        $this->assertEquals(0, $this->user->enableIP & 2);
        $this->assertEquals(0, $this->user->setIPAddress($ip));
        $this->assertNull($this->user->ipAddress);
        
        $this->user = new User(['enableIP' => 2]);
        $this->assertEquals(2, $this->user->enableIP);
        $this->assertEquals(2, $this->user->enableIP & 2);
        $this->assertEquals(IP::IPv6, $this->user->setIPAddress($ip));
        $this->assertEquals($ip, $this->user->ipAddress);
    }
    
    /**
     * @group user
     * @group ip
     * @dependes testAfterRegister
     */
    public function testWebRequest()
    {
        $ip = $this->faker->ipv4;
        $this->user = new User(['requestId' => null, 'ipAddress' => $ip]);
        $this->assertEquals($ip, $this->user->ipAddress);
        
        $this->user = new User(['requestId' => 'db', 'ipAddress' => $ip]);
        $this->assertEquals($ip, $this->user->ipAddress);
    }
    
    /**
     * @group user
     * @group ip
     * @dependes testAfterRegister
     */
    public function testEnabledFields()
    {
        $user = new User(['enableIP' => User::IP_DISABLED]);
        $this->assertNotEmpty($user->enabledFields());
        $user = new User(['enableIP' => User::IP_V4_ENABLED]);
        $this->assertNotEmpty($user->enabledFields());
        $user = new User(['enableIP' => User::IP_V6_ENABLED]);
        $this->assertNotEmpty($user->enabledFields());
        $user = new User(['enableIP' => User::IP_ALL_ENABLED]);
        $this->assertNotEmpty($user->enabledFields());
    }
    
    public static function severalTimes(): \Generator
    {
        for ($i = 0; $i < 3; $i++) {
            yield [$i];
        }
    }
    
    public static function IPv4Provider(): \Generator
    {
        $faker = Factory::create();
        $faker->seed(time() % 1000000);
        for ($i = 0; $i < 3; $i++)
        {
            yield [$faker->ipv4];
        }
    }
    
    public static function IPv6Provider(): \Generator
    {
        $faker = Factory::create();
        $faker->seed(time() % 1000000);
        for ($i = 0; $i < 3; $i++)
        {
            yield [$faker->ipv6];
        }
    }
}