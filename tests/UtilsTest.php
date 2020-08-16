<?php

namespace Dongdavid\Notify\Tests;

function config($config_name)
{
    $config = [
        'notify_config' => [
            'email_tx' => [
                'type' => 'email',
                'signature' => 'email_tx',
                'host' => 'smtp.exmail.qq.com',
                'port' => '465',
                'username' => 'noreply@xxx.com',
                'password' => '', //腾讯的要用专用密码登陆
                'SMTPSecure' => 'ssl',
                'fromEmail' => 'noreply@xxx.com',
                'fromName' => 'name',
                'debug' => 3,
            ],
            'work_xxx_1000002' => [
                'type' => 'wechatwork',
                'signature' => 'work_xxx_1000002',
                'appid' => 'xxx',
                'appsecret' => 'xxxxx',
                'agentid' => '1000002',
            ],
            'offical_xxx' => [
                'type' => 'wechatoffical',
                'signature' => 'offical_xxx',
                'appid' => 'xxx',
                'appsecret' => 'xxxxx',
            ],
            'mini_xxx' => [
                'type' => 'miniprogram',
                'signature' => 'mini_xxx',
                'appid' => 'xxx',
                'appsecret' => 'xxxx',
            ],
        ],
    ];
    if (!isset($config[$config_name])) {
        throw new \Exception('配置不存在');
    }

    return $config[$config_name];
}

class UtilsTest
{
    public static function getData($type)
    {
        $config = [
            'wechatwork' => [
                'type' => 'wechatwork',
                'config' => config('notify_config')['work_xxx_1000002'],
                'msg' => [
                    'touser' => 'userid',
                    'msgtype' => 'textcard',
                    'textcard' => [
                        'title' => 'title',
                        'description' => 'description',
                        'url' => 'url',
                    ],
                ],
            ],

            'wechatoffical' => [
                'type' => 'wechatoffical',
                'config' => config('notify_config')['offical_xxx'],
                'msg' => [
                    'touser' => 'openid',
                    'template_id' => 'template_id',
                    'url' => 'https://www.dongdavid.com',
                    'miniprogram' => [
                        'appid' => '',
                        'pagepath' => '',
                    ],
                    'data' => [
                        'first' => [
                            'value' => '哈喽，我是first one',
                        ],
                        'keyword1' => [
                            'value' => '我是关键词一号',
                            'color' => '#17317',
                        ],
                        'keyword2' => [
                            'value' => '我是关键词二号',
                        ],
                        'remark' => [
                            'value' => '我是备注了啊',
                        ],
                    ],
                ],
            ],
            'miniprogram' => [
                'type' => 'miniprogram',
                'config' => config('notify_config')['mini_xxx'],
                'msg' => [
                    'touser' => 'oBEIa0cr36R6FTCppAvgKLoKG9FY',
                    'template_id' => '0OHAj375XtCEVwJaASmRv79c4KhlqzN_mtsmNn6qHGg',
                    'page' => '',
                    'miniprogram_state' => 'developer', //跳转小程序类型：developer为开发版；trial为体验版；formal为正式版；默认为正式版
                    'data' => [
                        'thing1' => [
                            'value' => '哈喽，我是first one',
                        ],
                        'time2' => [
                            'value' => date('Y-m-d H:i'),
                        ],
                    ],
                ],
            ],
            'email' => [
                'type' => 'email',
                'config' => config('notify_config')['email_tx'],
                'msg' => [
                    'subject' => '邮件主旨',
                    'body' => '邮件内容',
                    'touser' => [
                        [
                            'emailAddress' => 'receiver@outlook.com',
                            'name' => '收件人名字',
                        ],
                    ],
                    'cc' => [],
                    'attachments' => [
                        // './public/robots.txt',
                    ],
                ],
            ],
        ];

        return $config[$type];
    }

    public static function setUtilsMock()
    {
        // 模拟获取access_token的http接口
        $http = \Mockery::mock('alias:\Dongdavid\Notify\utils\Http');
        $http->shouldReceive('get')
        // ->once()
            ->withAnyArgs()
            ->andReturn(['errcode' => 0, 'errmsg' => 'success', 'access_token' => 'mock_access_token']);

        $http->shouldReceive('post')
            ->once()
            ->withAnyArgs()
            ->andReturn(['errcode' => 0, 'errmsg' => 'success']);

        // 模拟redis接口
        $redis = \Mockery::mock('alias:\Dongdavid\Notify\utils\Redis');
        $redis->shouldReceive('set')
            ->withAnyArgs()
            ->andReturn(true);
        $redis->shouldReceive('get')
            ->withAnyArgs()
            ->andReturn([
                'expire_time' => time() + 7000,
                'ticket' => 'mock_access_token',
                'signature' => '',
                'type' => '',
            ]);
    }
}
