# 消息发送组件  


<p align="center"> 消息通知发送组件.</p>


## Requirements  

* PHP >= 7.0.0
* Redis PHP Extension
* guzzlehttp/guzzle ^6.3
* phpmailer/phpmailer ^ 6.1


## Installing

```shell
$ composer require dongdavid/notify -vvv
```


## Usage


有两种使用方式，一种是直接将配置参数一并传入，另一种是定义`config`方法来获取配置，传入配置名称

### 第一种方式  

#### 邮件 

```php
$mail = [
    'type'   => 'email',
    'config' => [
        'type'       => 'email',
        'signature'  => 'email_tx',
        'host'       => 'smtp.exmail.qq.com',
        'port'       => '465',
        'username'   => 'noreply@xxx.com',
        'password'   => '', //腾讯的要用专用密码登陆
        'SMTPSecure' => 'ssl',
        'fromEmail'  => 'noreply@xxx.com',
        'fromName'   => 'name',
    ],
    'msg'    => [
        'subject'     => '邮件主旨',
        'body'        => '邮件内容',
        'touser'      => [
            [
                'emailAddress' => 'receiver@outlook.com',
                'name'         => '收件人名字',
            ],
        ],
        'cc'          => [],
        'attachments' => [
            './public/robots.txt',
        ],
    ],
];

Notify::send($mail);
```

#### 企业微信  

```php
$qy = [
    'type'   => 'wechatwork',
    'config' => [
        'type'      => 'wechatwork',
        'signature' => 'work_xxx_1000002',
        'appid'     => 'xxx',
        'appsecret' => 'xxxxx',
        'agentid'   => '1000002',
    ],
    'msg'    => [
        'touser'   => '001',
        'msgtype'  => 'textcard',
        'textcard' => [
            'title'       => '测试通知',
            'description' => "我是通知内容\n第二行开始了\n加上一个链接<div class=\"gray\">2016年9月26日</div> <div class=\"normal\">恭喜你抽中iPhone 7一台，领奖码：xxxx</div><div class=\"highlight\">请于2016年10月10日前联系行政同事领取</div>",
            'url'         => 'https://www.dongdavid.com',
        ],
    ],
];

Notify::send($qy);
```

#### 微信公众号  

```php
$wechat = [
    'type'   => 'wechatoffical',
    'config' => [
        'type'      => 'wechatoffical',
        'signature' => 'offical_xxx',
        'appid'     => 'xxx',
        'appsecret' => 'xxxxx',
    ],
    'msg'    => [
        'touser'      => 'openid',
        'template_id' => 'template_id',
        'url'         => 'https://www.dongdavid.com',
        'miniprogram' => [
            'appid'    => '',
            'pagepath' => '',
        ],
        'data'        => [
            'first'    => [
                'value' => '哈喽，我是first one',
            ],
            'keyword1' => [
                'value' => '我是关键词一号',
                'color' => '#17317',
            ],
            'keyword2' => [
                'value' => '我是关键词二号',
            ],
            'remark'   => [
                'value' => '我是备注了啊',
            ],
        ],
    ],
];
Notify::send($notify);
```


#### 微信小程序  

```php
$wechat = [
    'type'   => 'miniprogram',
    'config' => [
        'type'      => 'miniprogram',
        'signature' => 'mini_xxx',
        'appid'     => 'xxx',
        'appsecret' => 'xxxx',
    ],
    'msg'    => [
        'touser'            => 'oBEIa0cr36R6FTCppAvgKLoKG9FY',
        'template_id'       => '0OHAj375XtCEVwJaASmRv79c4KhlqzN_mtsmNn6qHGg',
        'page'              => '',
        'miniprogram_state' => 'developer', //跳转小程序类型：developer为开发版；trial为体验版；formal为正式版；默认为正式版
        'data'              => [
            'thing1' => [
                'value' => '哈喽，我是first one',
            ],
            'time2'  => [
                'value' => date('Y-m-d H:i'),
            ],
        ],
    ],
];
Notify::send($notify);
```
### 第二种方式  

在框架中使用,或者自定义一个`config`方法来获取配置  

在`Sender.php`中会使用`config('notify_config')`来获取所有的消息发送配置，
所以需要确保你能够通过`config('notify_config')['config_name']`来获取到对应的配置

```php
public function setConfig($config)
{
    if (is_string($config)) {
        if (!isset(config('notify_config')[$config])) {
            throw new InvalidArgumentException("无效的配置:".$config);
        }
        $this->config = config('notify_config')[$config];
    }else{
        $this->config = $config;
    }


    // 检查配置是否完整
    $this->checkConfig();

    return $this;
}
```

```php
function config($config_name)
{
    $config = [
        'notify_config'=>[
            'work_xxx_1000002' => [
                'type'      => 'wechatwork',
                'signature' => 'work_xxx_1000002',
                'appid'     => 'xxx',
                'appsecret' => 'xxxxx',
                'agentid'   => '1000002',
            ],
            'offical_xxx'      => [
                'type'      => 'wechatoffical',
                'signature' => 'offical_xxx',
                'appid'     => 'xxx',
                'appsecret' => 'xxx',
            ],
            'email_tx'                        => [
                'type'       => 'email',
                'signature'  => 'email_tx',
                'host'       => 'smtp.exmail.qq.com',
                'port'       => '465',
                'username'   => 'noreply@xxx.com',
                'password'   => '', //腾讯的要用专用密码登陆
                'SMTPSecure' => 'ssl',
                'fromEmail'  => 'noreply@xxx.com',
                'fromName'   => 'name',

            ],
            'mini_xxx'         => [
                'type'      => 'miniprogram',
                'signature' => 'mini_xxx',
                'appid'     => 'xxx',
                'appsecret' => 'xxxxx',
            ],
        ]
    ];
    return $config[$config_name];
}

$data = [
    'type'   => 'email',
    'config' => 'email_tx',
    'msg'    => [
        .
        .
        .
    ],
];

$data = [
    'type'   => 'wechatwork',
    'config' => 'work_xxx_1000002',
    'msg'    => [
        .
        .
        .
    ],
];
$data = [
    'type'   => 'wechatoffical',
    'config' => 'offical_xxx',
    'msg'    => [
        .
        .
        .
    ],
];
$data = [
    'type'   => 'miniprogram',
    'config' => 'mini_xxx',
    'msg'    => [
        .
        .
        .
    ],
];

Notify::send($data);
```


## Test  

```sh
#本地调试
mkdir notify-test
cd notify-test
composer init  
composer config repositories.notify path ../notify  
composer require dongdaivd/notify:dev-master
touch index.php
```

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/dongdavid/notify/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/dongdavid/notify/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT