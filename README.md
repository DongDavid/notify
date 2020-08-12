<h1 align="center"> notify </h1>

<p align="center"> 消息通知发送组件.</p>


## Installing

```shell
$ composer require dongdavid/notify -vvv
```

## Usage

TODO  

### 邮件 

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

$notify = new Notify();
$notify->send($mail);
```

### 企业微信  

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

$notify = new Notify();
$notify->send($mail);
```

### 微信公众号  

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
$notify = new Notify();
$notify->send($mail);
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