# 结合thinkphp-queue实现消息队列  

```sh
composer create-project topthink/think=5.0.24 notify-tp5  --prefer-dist
composer require dongdavid/notify ^2.0
# tp5.0 要用1.1.6的queue
composer require topthink/think-queue 1.1.6
mkdir application/job
touch application/job/TestJob.php
```

`application/job/TestJob.php`

```php
<?php
namespace app\job;

use think\queue\Job;
use Dongdavid\Notify\QuickSend;

class TestJob{
    
    public function offical(Job $job, $data)
    {
      $res = QuickSend::offical($data['access_token'],$data['openid'],$data['template_id'],$data['data']);
      if ($res === true) {
        // 发送成功
        trace('公众号模板消息发送成功','notify-success');
        $job->delete();
      }else{
        // 发送失败
        trace('公众号模板消息发送失败:'.$res,'notify-err');
        trace($data,'notify-err');
    }

      if ($job->attempts() > 3) {
            //通过这个方法可以检查这个任务已经重试了几次了
        $job->delete();
       }
    }
    public function mini(Job $job, $data)
    {
            $res = QuickSend::miniProgram($data['access_token'],$data['openid'],$data['template_id'],$data['data']);
      if ($res === true) {
        // 发送成功
        trace('小程序订阅消息发送成功','notify-success');
        $job->delete();
      }else{
        // 发送失败
        trace('小程序订阅消息发送失败:'.$res,'notify-err');
        trace($data,'notify-err');
      }

      if ($job->attempts() > 3) {
            //通过这个方法可以检查这个任务已经重试了几次了
        $job->delete();
       }
    }
    public function email(Job $job, $data)
    {
            $res = QuickSend::mail($data['config'],$data['subject'],$data['body'],$data['to'],$data['attachments'],$data['cc'],$data['bcc']);
      if ($res === true) {
        // 发送成功
        trace('邮件发送成功','notify-success');
        $job->delete();
      }else{
        // 发送失败
        trace('邮件发送失败:'.$res,'notify-err');
        trace($data,'notify-err');
      }

      if ($job->attempts() > 3) {
            //通过这个方法可以检查这个任务已经重试了几次了
        $job->delete();
       }
    }
    public function alisms(Job $job, $data)
    {
      $res = QuickSend::alisms($data['config'],$data['phone'],$data['template_param']);
      if ($res['Code'] == 'OK') {
        // 发送成功
        trace('短信发送成功','notify-success');
        $job->delete();
      }else{
        // 发送失败
        trace('短信发送失败:'.var_export($res,true),'notify-err');
        trace($data,'notify-err');
      }

      if ($job->attempts() > 3) {
            //通过这个方法可以检查这个任务已经重试了几次了
        $job->delete();
       }
    }
    public function fire(Job $job, $data){
    
            //....这里执行具体的任务 
          trace('我只是个普通的任务啊','notify-test');
          trace($data,'notify-test');
             
            //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
            $job->delete();
            
            // 也可以重新发布这个任务
            // $job->release($delay); //$delay为延迟时间
          
    }
    
    public function failed($data){
    
        // ...任务达到最大重试次数后，失败了
    }

}
```

修改控制器增加测试方法`app\index\controller\Index.php`


```php
<?php
namespace app\index\controller;

class Index
{
    public function test()
    {
    	\think\Queue::push('TestJob', ['none'=>'only a test']);
    	// return ;
    	$data = [
    		'access_token'=>'',
    		'openid'=>'',
    		'template_id'=>'',
    		'data'=>[
    			'first'=>['value'=>'first is me'],
    			'keyword1'=>['value'=>'哈哈哈'],
    			'keyword2'=>['value'=>number_format(23.32,2)],
    			'remark'=>['value'=>'备注信息'],
    		],
    	];
    	\think\Queue::push('TestJob@offical', $data);

    	$data = [
    		'access_token'=>'',
    		'openid'=>'',
    		'template_id'=>'',
    		'data'=>[
    			'thing1'=>['value'=>'first is me'],
	            'thing3'=>['value'=>'哈哈哈'],
	            'time2'=>['value'=>23.32],
    		],
    	];
    	// \think\Queue::push('TestJob@mini', $data);

    	$data = [
    		'config'=>[
    			'host'       => 'smtp.exmail.qq.com',
    			'port'       => '465',
    			'username'   => 'noreply@xx.com',
    			'password'   => '', //腾讯的要用专用密码登陆
    			'SMTPSecure' => 'ssl',
    			'fromEmail'  => 'noreply@xx.com',
    			'fromName'   => 'xx',
    		],
    		'subject'=>'测试邮件',
	        'body'=>'邮件正文',
	        'to'=>[
	            'xxx@outlook.com',
	        ],
	        'cc'=>[
	            ['email'=>'xxx@live.com','name'=>'收件人昵称'],
	        ],
	        'bcc'=>'xxx@qq.com',
	        'attachments'=>[
	            './aa.md',
	            ['filepath'=>'./bb.md','filename'=>'说明文件.txt']
	        ],
    	];
    	// \think\Queue::push('TestJob@email', $data);

    	$data = [
    	    'config'=>[
    	        'accessKeyId'=>'',
    	        'accessKeySecret'=>'',
    	        'SignName'=>'',
    	        'template_code'=>'',
    	    ],
    	    'phone'=>'',
    	    'template_param'=>[
    	        'name'=>'皮皮虾',
    	        'course_name'=>'如何上天',
    	    ],
    	];
    	\think\Queue::push('TestJob@alisms', $data);

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
