<?php

namespace Dongdavid\Notify\utils;

use Dongdavid\Notify\Exceptions\HttpException;
use GuzzleHttp\Client;

class Http
{
    // protected static $guzzleOptions = [];

    public static function getHttpClient()
    {
        return new Client();
        // return new Client(self::$guzzleOptions);
    }

    // public static function setGuzzleOptions(array $options)
    // {
    //     self::$guzzleOptions = $options;
    // }

    /**
     * [httpget get请求]
     * look me baby.
     *
     * @Author   DongDavid
     * @DateTime 2017-07-05T09:03:03+0800
     *
     * @param string $url    [请求地址]
     * @param array  $query  [请求参数 json字符串]
     * @param bool   $decode [是否json_decode 默认返回array false返回json字符串]
     *
     * @return array|string [description]
     */
    public static function get($url, $query = [], $decode = true)
    {
        try {
            if (!empty($query)) {
                $param = ['query' => $query];
            } else {
                $param = [];
            }
            $response = self::getHttpClient()
                ->get($url, $param)
                ->getBody()
                ->getContents();

            return true === $decode ? \json_decode($response, true) : $response;
        } catch (HttpException $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * [httpPost post请求]
     * look me baby.
     *
     * @Author   DongDavid
     * @DateTime 2017-07-05T09:03:03+0800
     *
     * @param string $url    [请求地址]
     * @param array  $query  [请求参数 json字符串]
     * @param bool   $decode [是否json_decode 默认返回array false返回json字符串]
     *
     * @return array|string [description]
     */
    public static function post($url, $query, $decode = true)
    {
        try {
            $response = self::getHttpClient()
                ->post($url, ['json' => $query])
                ->getBody()
                ->getContents();

            return true === $decode ? \json_decode($response, true) : $response;
        } catch (HttpException $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public static function test($decode = true)
    {
        $url = 'https://api.dongdavid.com/index/index/test?aa=bb';

        try {
            $response = self::getHttpClient()
                ->post($url, ['json' => ['cc' => 'dd', 'gadw2' => 4332]])
                ->getBody()
                ->getContents();

            return true === $decode ? \json_decode($response, true) : $response;
        } catch (HttpException $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
