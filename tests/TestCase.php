<?php
/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 vistart
 * @license https://vistart.me/license/
 */

namespace rhosocial\base\models\tests;

use Faker\Factory;
use Faker\Generator;
use yii\di\Container;
use yii\helpers\ArrayHelper;
use Yii;
use yii\db\Connection;

/**
 * Description of TestCase
 *
 * @author vistart <i@vistart.me>
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase {
    
    /**
     *
     * @var Generator 
     */
    protected $faker = null;
    
    public function sleep($seconds = 1) {
        for ($i = $seconds; $i > 0; $i--) {
            echo "$i\n";
            sleep(1);
        }
    }
    
    public static $params;
    
    /**
     * Returns a test configuration param from /data/config.php
     * @param  string $name params name
     * @param  mixed $default default value to use when param is not set.
     * @return mixed  the value of the configuration param
     */
    public static function getParam($name, $default = null) {
        if (static::$params === null) {
            static::$params = require(__DIR__ . '/data/config.php');
        }
        return isset(static::$params[$name]) ? static::$params[$name] : $default;
    }
    
    /**
     * Clean up after test.
     * By default the application created with [[mockApplication]] will be destroyed.
     */
    protected function tearDown() {
        parent::tearDown();
        $this->destroyApplication();
        $this->faker = null;
    }
    
    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = '\yii\console\Application') {
        new $appClass(ArrayHelper::merge([
                    'id' => 'testapp',
                    'basePath' => __DIR__,
                    'vendorPath' => dirname(__DIR__) . '/vendor',
                        ], $config));
    }
    
    protected function mockWebApplication($config = [], $appClass = '\yii\web\Application') {
        new $appClass(ArrayHelper::merge([
                    'id' => 'testapp',
                    'basePath' => __DIR__,
                    'vendorPath' => dirname(__DIR__) . '/vendor',
                    'components' => [
                        'request' => [
                            'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                            'scriptFile' => __DIR__ . '/index.php',
                            'scriptUrl' => '/index.php',
                        ],
                        'user' => [
                            'class' => '\yii\web\User',
                            'identityClass' => 'app\models\user\User',
                            'enableAutoLogin' => true,
                        ]
                    ]
                        ], $config));
    }
    
    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication() {
        $redis = Yii::$app->redis;
        /* @var $redis \yii\redis\Connection */
        $redis->executeCommand('flushall');
        Yii::$app = null;
        Yii::$container = new Container();
    }
    
    public function __construct($name = null, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->faker = Factory::create();
        $this->faker->seed(time() % 1000000);
    }
    
    protected function setUp() {
        $databases = self::getParam('databases');
        $params = isset($databases['mysql']) ? $databases['mysql'] : null;
        if ($params === null) {
            $this->markTestSkipped('No mysql server connection configured.');
        }
        $connection = new Connection($params);
        $md = self::getParam('multiDomainsManager');
        $redis = self::getParam('redis');
        $cacheParams = self::getParam('cache');/*
        if ($cacheParams === null) {
            $this->markTestSkipped('No cache component configured.');;
        }*/
        $this->mockWebApplication(['components' => ['redis' => $redis, 'multiDomainsManager' => $md, 'db' => $connection, 'cache' => $cacheParams]]);
        parent::setUp();
        
    }
    
    /**
     * @param  boolean    $reset whether to clean up the test database
     * @return Connection
     */
    public function getConnection($reset = true) {
        $databases = self::getParam('databases');
        $params = isset($databases['mysql']) ? $databases['mysql'] : [];
        $db = new Connection($params);
        if ($reset) {
            $db->open();
            //$db->flushdb();
        }
        return $db;
    }
}