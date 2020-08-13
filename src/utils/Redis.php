<?php
namespace Dongdavid\Notify\utils;

/**
 *
 */
class Redis
{
    const PREFIX = 'cr-';
    private static $redis;
    private static $redisConfig = [
        'host'     => '127.0.0.1',
        'port'     => '6379',
        'password' => '',
        'select'   => 6,
        'timeout'  => 3,
    ];

    // 覆盖redis连接配置
    public static function setConfig($config)
    {
        foreach (self::$redisConfig as $k => $v) {
            if (isset($config[$k])) {
                self::$redisConfig[$k] = $config[$k];
            }
        }
    }

    public static function connect($config = [])
    {
        // if (!extension_loaded('redis')) {

        // }
        if (self::$redis) {
            return self::$redis;
        }
        if (empty($config)) {
            $config = self::$redisConfig;
        }
        self::$redis = new \Redis();
        self::$redis->connect($config['host'], $config['port'], $config['timeout']);
        if (isset($config['password']) && !empty($config['password'])) {
            self::$redis->auth($config['password']);
        }
        if (isset($config['select'])) {
            self::$redis->select($config['select']);
        }
        return self::$redis;
    }

    public static function set($key, $value)
    {
        if (!is_string($key)) {
            throw new \Exception("require key type is String");
        }
        if (!is_string($value)) {
            if (is_array($value)) {
                $value = json_encode($value);
            } else {
                throw new \Exception("require value type is String or Array");
            }
        }
        if (!self::$redis) {
            self::connect();
        }
        self::$redis->set(self::PREFIX . $key, $value);
    }

    public static function get($key)
    {
        if (!is_string($key)) {
            throw new \Exception("require key type is String");
        }
        if (!self::$redis) {
            self::connect();
        }

        $value = self::$redis->get(self::PREFIX . $key);
        $json  = json_decode($value, true);
        return $json ? $json : $value;
    }
}