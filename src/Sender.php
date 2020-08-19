<?php

namespace Dongdavid\Notify;

use Dongdavid\Notify\Exceptions\InvalidArgumentException;
use Dongdavid\Notify\utils\Http;

/**
 * 消息发送类.
 */
abstract class Sender
{
    protected $config = [];

    abstract public function send($data);

    abstract public function checkConfig();

    abstract public function checkMsgFormate($msg);

    public function setConfig($config)
    {
        if (is_string($config)) {
            if (!isset(\Dongdavid\Notify\config('notify_config')[$config])) {
                throw new InvalidArgumentException('无效的配置:'.$config);
            }
            $this->config = \Dongdavid\Notify\config('notify_config')[$config];
        } else {
            $this->config = $config;
        }

        // 检查配置是否完整
        $this->checkConfig();

        return $this;
    }

    /**
     * 从数据库获取配置.
     *
     * @param $tag 配置标识
     */
    // protected function getConfigByDatabase($signature)
    // {
    //     if (!is_string($signature)) {
    //         throw new \Exception("请传入config tag");
    //     }
    //     if (!$config) {
    //         throw new \Exception("无效的config signature");
    //     }

    //     $this->config = $config;
    // }
    public function post($url, $data)
    {
        return Http::post($url, $data);
    }
}
