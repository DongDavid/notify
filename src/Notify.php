<?php

namespace Dongdavid\Notify;

class Notify
{
    protected static $types = [
        'email' => 'Email',
        'wechatoffical' => 'WechatOffical',
        'wechatwork' => 'WechatWork',
        'miniprogram' => 'MiniProgram',
        'alisms' => 'AliSms',
    ];

    /**
     * 获取可发送消息类型.
     *
     * @return string[]
     */
    public static function getTypes()
    {
        return array_keys(self::$types);
    }

    private static function buildConnector($type)
    {
        $type = strtolower($type);
        if (!isset(self::$types[$type])) {
            throw new \Exception('错误的通知类型:'.$type);
        }

        $class = false !== strpos($type, '\\') ? $type : '\\Dongdavid\\Notify\\sender\\'.self::$types[$type];

        return new $class();
    }

    public static function send($data)
    {
        return self::buildConnector($data['type'])
            ->setConfig($data['config'])
            ->send($data['data']);
    }

    public static function name($type)
    {
        return self::buildConnector($type);
    }
}
