<?php

namespace Dongdavid\Notify\sender;

use Dongdavid\Notify\Exceptions\InvalidArgumentException;
use Dongdavid\Notify\Sender;
use Dongdavid\Notify\WechatManager;

class WechatOffical extends Sender
{
    const MESSAGE_URL = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=';

    public function checkConfig()
    {
        if (!$this->config['access_token']) {
            throw new InvalidArgumentException('缺失access_token');
        }
    }

    public function checkMsgFormate($msg)
    {
        if (!isset($msg['touser'])) {
            throw new \Exception('接收人openid未设置');
        }

        if (!isset($msg['template_id'])) {
            throw new \Exception('未传入模版ID');
        }

        if (!isset($msg['data'])) {
            throw new \Exception('未设置消息内容');
        }
        foreach ($msg['data'] as $item) {
            if (!isset($item['value'])){
                throw new \Exception("模版消息内容未设置value");
            }
        }
        if (isset($msg['miniprogram'])){
            if (!isset($msg['miniprogram']['appid'])){
                throw new \Exception("缺失小程序appId");
            }
        }
    }

    public function send($msg)
    {
        $this->checkMsgFormate($msg);
        // 发送
        $res = $this->sendTemplate($msg);

        if (isset($res['errcode']) && 0 == $res['errcode']) {
            return true;
        }
        if (!isset($res['errcode'])) {
            throw new \Exception('发送失败:网络错误,无法请求微信服务器');
        }
        //if (0 == $res['errcode']) {
        //    return true;
        //} else {
        //    //if (in_array($res['errcode'], ['40014', '41001', '42001'])) {
        //        //$this->config['access_token'] = WechatManager::updateAccessToken($this->config);
        //    //}
        //}
        throw new \Exception('发送失败:'.$res['errcode'].$res['errmsg']);
    }

    public function sendTemplate($msg)
    {
        $url = self::MESSAGE_URL.$this->config['access_token'];

        return $this->post($url, $msg);
    }
}
