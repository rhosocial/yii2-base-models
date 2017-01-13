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

namespace rhosocial\base\models\tests\user;

use rhosocial\base\helpers\IP;
use rhosocial\base\models\tests\data\ar\User;

/**
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
     * @dataProvider IPv4Provider
     * @depends testAfterRegister
     */
    public function testIPv4($ip)
    {
        $this->assertEquals(3, $this->user->enableIP);
        $this->user->setIPAddress($ip);
        $this->assertTrue($this->user->register());
        $this->assertEquals($ip, $this->user->ipAddress);
        $this->assertEquals($ip, $this->user->getIPAddress());
        $this->assertEquals(inet_pton($ip), $this->user->ip);
        $this->assertEquals(IP::IPv4, $this->user->ip_type);
        $this->assertTrue($this->user->deregister());
        
        $this->user = new User(['enableIP' => 0]);
        $this->assertEquals(0, $this->user->enableIP);
        $this->assertEquals(0, $this->user->enableIP & 1);
        $this->assertNull($this->user->setIPAddress($ip));
        $this->assertNull($this->user->ipAddress);
        
        $this->user = new User(['enableIP' => 1]);
        $this->assertEquals(1, $this->user->enableIP);
        $this->assertEquals(1, $this->user->enableIP & 1);
        $this->assertEquals(IP::IPv4, $this->user->setIPAddress($ip));
        $this->assertEquals($ip, $this->user->ipAddress);
        
        $this->user = new User(['enableIP' => 2]);
        $this->assertEquals(2, $this->user->enableIP);
        $this->assertEquals(0, $this->user->enableIP & 1);
        $this->assertEquals(0, $this->user->setIPAddress($ip));
        $this->assertNull($this->user->ipAddress);
    }
    
    /**
     * @group user
     * @group ip
     * @param string $ip
     * @dataProvider IPv6Provider
     * @depends testAfterRegister
     */
    public function testIPv6($ip)
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
        $user = new User(['enableIP' => 0]);
        $this->assertNotEmpty($user->enabledFields());
        $user = new User(['enableIP' => 1]);
        $this->assertNotEmpty($user->enabledFields());
        $user = new User(['enableIP' => 2]);
        $this->assertNotEmpty($user->enabledFields());
        $user = new User(['enableIP' => 3]);
        $this->assertNotEmpty($user->enabledFields());
    }
    
    public function severalTimes()
    {
        for ($i = 0; $i < 3; $i++) {
            yield [$i];
        }
    }
    
    public function IPv4Provider()
    {
        for ($i = 0; $i < 3; $i++)
        {
            yield [$this->faker->ipv4];
        }
    }
    
    public function IPv6Provider()
    {
        for ($i = 0; $i < 3; $i++)
        {
            yield [$this->faker->ipv6];
        }
    }
}