# 结合thinkphp-queue实现消息队列  

## 安装thinkphp5.1和think-queue  

```sh
composer create-project topthink/think=5.1.* mq
composer require topthink/think-queue ~2.0
```  
## 测试Queue  

修改think-queue的配置文件`config/queue.php`  


```php
<?php
# 这里 select随便填,真正在跑队列的话，建议使用长连接，用work的模式去运行
return [
    'connector' => 'redis',
    'expire'     => 60,
    'default'    => 'default',
    'host'       => '127.0.0.1',
    'port'       => 6379,
    'password'   => '',
    'select'     => 8,
    'timeout'    => 0,
    'persistent' => true,
];
```

创建一个Job类`application/job/JobTest.php`  


```php
<?php
namespace app\job;

use think\queue\Job;

class JobTest {
    
    public function fire(Job $job, $data){
    
            trace("it's a test job ",'queue');
            $data['fire_time'] = date('Y-m-d H:i:s');
            trace($data,'queue');
            $job->delete();
    }
    
    public function failed($data){
        trace('任务执行失败了','queue');
        trace($data,'queue');
        // ...任务达到最大重试次数后，失败了
    }

}
```

修改文件`application/index/controller/Index.php`  

```php
<?php
namespace app\index\controller;

class Index
{   
    public function test(){
        // 向队列中新增一个任务
        \think\Queue::push('JobTest',['name'=>'testjob']);
    }
}
``` 

执行新增任务动作
```sh
php public/index.php index/index/test
```

启动消息队列消费任务  

```sh
ubuntu@VM-16-4-ubuntu:/data/mq$ php think queue:work --daemon --tries=2
Processed: JobTest
```

## 引入消息发送组件  

```sh
composer require dongdavid/notify:dev-master
```

新建消息通知任务类`application/job/Notify.php`

```php
<?php
namespace app\job;

use think\queue\Job;

class Notify {
    
    public function fire(Job $job, $data){
    
        //....这里执行具体的任务 
        try {
            $res = \dongdavid\notify\Notify::send($data);
            if ($res === true) {
                trace($data,'info');
                $job->delete();
                // trace('success'.$data['notify_id'],'info');
                $this->callback($data,1);
                return;
            }else{
                $this->callback($data,2);
            }
        } catch (\Exception $e) {
            trace('任务执行失败了','error');            
            $data['error'] = $e->getFile().'-'.$e->getLine().'-'.$e->getMessage();
            trace($data,'error');
            $body = '消息队列-任务运行失败:'.var_export($data,true);
            // 这里要直接发邮件给管理员通知出错了 小心陷入死循环，如果邮箱崩了的话
            $data = [
                'type'=>'email',
                'config'=>'email_tx',
                'msg'=>[
                    'touser'=>'xxx@live.com',
                    'subject'=>'消息队列错误通知',
                    'body'=>$body,
                ],
            ];
            \dongdavid\notify\Notify::send($data);
        }
        
        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
    }

    public function callback($data,$code)
    {
        // TODO CALLBACK
    }
    
    public function failed($data){
        trace('任务执行失败了','queue');
        trace($data,'queue');
        // ...任务达到最大重试次数后，失败了
    }

}

```

在`config/app.php`中维护`notify_config`  

```php
<?php
return [
    .
    .
    .
    'notify_config'=>[
        'email_tx'=>[
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
    ],
];
```
修改文件`application/index/controller/Index.php`  

```php
<?php
namespace app\index\controller;

class Index
{   
    public function test(){
        // 向队列中新增一个任务
        \think\Queue::push('JobTest',['name'=>'testjob']);
    }
    public function test1(){
        $data = [
            'type'   => 'email',
            'config' => 'email_tx',
            'msg'    => [
                'subject'     => '邮件主旨',
                'body'        => '邮件内容',
                'touser'      => [
                    [
                        'emailAddress' => 'xxx@live.com',
                        'name'         => '收件人名字',
                    ],
                ],
            ],
        ];
        \think\Queue::push('Notify',$data);
    }
}
``` 

启动think-queue  

```sh
# 后台单进程运行 失败最大重试次数为2
php think queue:work --daemon --tries=2
```

使用supervisor启动  

增加配置文件`/etc/supervisord.d/tp-queue.ini`

```
[program:think-queue-mq]
;command= /usr/bin/php /path/think queue:listen --tries=2
command= /usr/bin/php /path/think queue:work --tries=2 --daemon
; 被监控进程
directory=/path
;process_name=%(process_num)02d 
numprocs=1 
;启动几个进程
autostart=true 
;随着supervisord的启动而启动
autorestart=true 
;自动启动
startsecs=1 
;程序重启时候停留在runing状态的秒数
startretries=3 
;启动失败时的最多重试次数
redirect_stderr=true 
;重定向stderr到stdout
stdout_logfile=/root/supervisor-mq.log
```

启动supervisor
```sh
supervisorctl start
```