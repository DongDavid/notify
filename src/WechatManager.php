<?php
namespace Dongdavid\Notify;

use Dongdavid\Notify\utils\Redis;
use Dongdavid\Notify\utils\Http;
/**
 *
 */
class WechatManager
{

    /**
     * [getWorkAccessToken 获取企业微信access_token]
     * Don't look at me!
     * @Author   DongDavid
     * @DateTime 2017-07-17T15:12:41+0800
     * @param    [type]                   $appid     [description]
     * @param    [type]                   $appsecret [description]
     * @return   [type]                              [description]
     */
    public static function getWorkAccessToken($appid, $appsecret)
    {
        $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$appid}&corpsecret={$appsecret}";
        $res = Http::get($url);
        if (isset($res['access_token'])) {
            $access_token = $res['access_token'];
        }else{
            throw new \Exception("获取企业微信access_token失败:".$res['errcode'].$res['errmsg']);
        }
        $data = [
            'access_token'=>$access_token,
            'expire_time'=>time()+7000,
        ];
        return $data;
    }


    public static function getOfficalAccessToken($appid,$appsecret)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appsecret}";
        $res = Http::get($url);
        if (isset($res['access_token'])) {
            $access_token = $res['access_token'];
        }else{
            throw new \Exception("获取微信公众号access_token失败:".$res['errcode'].$res['errmsg']);
        }
        $data = [
            'access_token'=>$access_token,
            'expire_time'=>time()+7000,
        ];
        return $data;
    }

    public static function getMiniAccessToken($appid,$appsecret)
    {

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appsecret}";
        $res = Http::get($url);
        if (isset($res['access_token'])) {
            $access_token = $res['access_token'];
        }else{
            throw new \Exception("获取小程序access_token失败:".$res['errcode'].$res['errmsg']);
        }
        $data = [
            'access_token'=>$access_token,
            'expire_time'=>time()+7000,
        ];
        return $data;
    }

    // 获取access_token
    public static function getAccessToken($config)
    {
        $key = 'access_token-'.$config['signature'];
        $data = Redis::get($key);
        if ($data['expire_time'] > time()) {
            return $data['ticket'];
        }
        return self::updateAccessToken($config);
    }

    // 刷新access_token 建议定时执行
    public static function updateAccessToken($config)
    {
        switch ($config['type']) {
            case 'wechatoffical':
                $result = self::getOfficalAccessToken($config['appid'],$config['appsecret']);
                break;
            case 'wechatwork':
                $result = self::getWorkAccessToken($config['appid'], $config['appsecret']);
                break;
            case 'miniprogram':
                $result = self::getMiniAccessToken($config['appid'],$config['appsecret']);
                break;
            default:
                throw new \Exception('无效的公众号类型:'.$config['type']);
                break;
        }
        $key = 'access_token-' . $config['signature'];
        $data = [
            'ticket'=>$result['access_token'],
            'type'=>'access_token',
            'signature'=>$config['signature'],
            'expire_time'=>$result['expire_time'],
        ];

        Redis::set($key, json_encode($data));
        return $data['ticket'];
    }
}