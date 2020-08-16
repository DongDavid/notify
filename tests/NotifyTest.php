<?php
namespace Dongdavid\Notify\Tests;

use Dongdavid\Notify\Notify;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class NotifyTest extends TestCase
{

    public function testSetRedisConfigWithInvalidConfig()
    {

        // 断言会抛出此异常类
        $this->expectException(\Exception::class);

        // 断言异常消息为 'Invalid type value(base/all): foo'
        $this->expectExceptionMessage('非法的配置');

        Notify::setRedisConfig([]);

        $this->fail('Failed to assert setRedisConfig throw exception with invalid argument.');
    }

    public function testNameWithInvalidType()
    {
        // 断言会抛出此异常类
        $this->expectException(\Exception::class);

        // 断言异常消息为 'Invalid type value(base/all): foo'
        $this->expectExceptionMessage('错误的通知类型:what');

        Notify::name('what');

        $this->fail('Failed to assert setRedisConfig throw exception with invalid argument.');

    }
    public function testNameWithValidType()
    {
        $sender = Notify::name('miniprogram');
        $this->assertIsObject($sender, '返回的不是sender对象');
    }

    public function testSendWechatWork()
    {
        // 设置Http和Redis的mock
        UtilsTest::setUtilsMock();
        $data = UtilsTest::getData('wechatwork');
        $res = Notify::send($data);
        $this->assertSame(true, $res);
    }
    public function testSendWechatOffical()
    {
        // 设置Http和Redis的mock
        UtilsTest::setUtilsMock();
        $data = UtilsTest::getData('wechatoffical');
        $res = Notify::send($data);
        $this->assertSame(true, $res);
    }
    public function testSendMiniProgram()
    {
        // 设置Http和Redis的mock
        UtilsTest::setUtilsMock();
        $data = UtilsTest::getData('minprogram');
        $res = Notify::send($data);
        $this->assertSame(true, $res);
    }
    public function testSendEmail()
    {
        // 设置Http和Redis的mock
        // UtilsTest::setUtilsMock();
        // $mail = \Mockery::mock('overload:\PHPMailer\PHPMailer\PHPMailer');
        // $mail->shouldReceive('send')
        //      ->once()
        //      ->withAnyArgs()
        //      ->andReturn(true);
        // $phpmailer = \Mockery::mock(\PHPMailer\PHPMailer\PHPMailer::class);
        // $phpmailer->allows()->send()->andReturn(true);


        // $data = UtilsTest::getData('email');
        // $res = Notify::send($data);
        // $this->assertSame(true, $res);
    }
}