<?php

namespace Dongdavid\Notify;

use Dongdavid\Notify\utils\Http;

// 如果没有cache方法就自定义一个方法用redis做缓存
if (!function_exists('getAccessToken')) {
    function getAccessToken($type, $appId, $appSecret, $agentid = 0)
    {
        switch ($type) {
            case 'wechatoffical':
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appId}&secret={$appSecret}";
                break;
            case 'miniprogram':
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appId}&secret={$appSecret}";
                break;
            default:
                return '类型错误';
        }
        echo $url;
        $res = Http::get($url);
        if (isset($res['access_token'])) {
            return $res;
        } else {
            return $res;
        }
    }
}
