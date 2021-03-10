<?php

namespace Dongdavid\Notify\sender;

use Dongdavid\Notify\Exceptions\Exception;
use Dongdavid\Notify\Sender;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Email extends Sender
{
    public function checkConfig()
    {
        if (!isset($this->config['host'])) {
            throw new \Exception('请传入邮箱服务器地址');
        }

        if (!isset($this->config['username'])) {
            throw new \Exception('请传入邮箱服务器认证账号');
        }

        if (!isset($this->config['password'])) {
            throw new \Exception('请传入邮箱服务器认证密码');
        }

        if (!isset($this->config['fromEmail'])) {
            throw new \Exception('请设置发件人邮箱');
        }

        if (!isset($this->config['fromName'])) {
            $this->config['fromName'] = $this->config['fromEmail'];
        }

        if (!isset($this->config['debug'])) {
            $this->config['debug'] = 0;
        }
        if (!isset($this->config['SMTPSecure'])) {
            $this->config['SMTPSecure'] = false;
            $this->config['SMTPOptions'] = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
        }
        if (!isset($this->config['SMTPOptions'])) {
            $this->config['SMTPOptions'] = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];
        }

        if (!isset($this->config['port'])) {
            $this->config['port'] = 25;
        }
    }

    public function checkMsgFormate($msg)
    {
        if (!isset($msg['subject'])) {
            throw new \Exception('邮件主题未设置');
        }

        if (!isset($msg['body'])) {
            throw new \Exception('邮件正文未设置');
        }

        if (!isset($msg['to'])) {
            throw new \Exception('收件人未设置');
        }else{
            $msg['to'] = $this->checkEmailAddress($msg['to']);
        }
        if (isset($msg['cc'])){
            $msg['cc'] = $this->checkEmailAddress($msg['cc']);
        }
        if (isset($msg['bcc'])){
            $msg['bcc'] = $this->checkEmailAddress($msg['bcc']);
        }
        if (isset($msg['attachments'])) {
            $msg['attachments'] = $this->checkAttachments($msg['attachments']);
        }
        return $msg;
    }

    public function checkEmailAddress($emailAddresses)
    {
        if (is_string($emailAddresses)){
            if (filter_var($emailAddresses,FILTER_VALIDATE_EMAIL)){
                return [
                    ['email'=>$emailAddresses,'name'=>$emailAddresses]
                ];
            }else{
                throw new \Exception("邮箱格式不正确:".$emailAddresses);
            }
        }elseif (is_array($emailAddresses)){
            foreach ($emailAddresses as &$emailAddress) {
                if (is_string($emailAddress)){
                    $emailAddress = ['email'=>$emailAddress,'name'=>$emailAddress];

                }else{
                    if (!isset($emailAddress['email'])){
                        throw new Exception("缺失邮箱地址[email]");
                    }
                }
                if (!filter_var($emailAddress['email'],FILTER_VALIDATE_EMAIL)){
                    throw new \Exception("邮箱格式不正确:".$emailAddress);
                }
            }
            return $emailAddresses;
        }


    }
    public function checkAttachments($attachments)
    {
        if (is_string($attachments)) {
            $path = realpath($attachments);
            if (!file_exists($path)) {
                throw new \Exception('附件不存在:'.$attachments);
            }
            $attachments = [
                [
                    'filepath'=>$path,
                    'filename'=>basename($path),
                ]
            ];
        } elseif (is_array($attachments)) {
            foreach ($attachments as &$attachment) {
                if (is_string($attachment)){
                    $attachment = [
                        'filename'=>basename($attachment),
                        'filepath'=>realpath($attachment),
                    ];
                }
                if (!file_exists($attachment['filepath'])) {
                    throw new \Exception('附件不存在:'.$attachment);
                }
            }
        }else{
            throw new \Exception("attachments参数格式错误");
        }
        return $attachments;

    }

    public function send($msg)
    {
        $msg = $this->checkMsgFormate($msg);
        return $this->sendMail($msg);
    }

    private function sendMail($msg)
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = $this->config['debug'];  // Enable verbose debug output
            $mail->isSMTP();  // Set mailer to use SMTP
            $mail->Host = $this->config['host'];  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;  // Enable SMTP authentication
            $mail->Username = $this->config['username'];  // SMTP username
            $mail->Password = $this->config['password'];  // SMTP password
            $mail->SMTPSecure = $this->config['SMTPSecure'];  // Enable TLS encryption, `ssl` also accepted
            $mail->Port = $this->config['port'];  // TCP port to connect to
            $mail->CharSet = 'UTF-8';
            $mail->SMTPOptions = $this->config['SMTPOptions'];
            //Recipients
            $mail->setFrom($this->config['fromEmail'], $this->config['fromName']);
            if (isset($this->config['replyMail'])){
                $mail->setReplyTo($this->config['replyMail']);
            }
            if (isset($this->config['SMTPOptions'])){
                $mail->SMTPOptions = $this->config['SMTPOptions'];
            }
            // touser
            if (isset($msg['to'])) {
                foreach ($msg['to'] as $t) {
                    $mail->addAddress($t['email'], $t['name']);
                }

            }
            // cc
            if (isset($msg['cc']) && is_string($msg['cc'])) {
                foreach ($msg['cc'] as $c) {
                    $mail->addCC($c['email'], $c['name']);
                }
            }

            // bcc
            if (isset($msg['bcc'])){
                foreach ($msg['bcc'] as $bc) {
                    $mail->addBCC($bc['email'], $bc['name']);
                }
            }

            // Attachments
            if (isset($msg['attachments']) && !empty($msg['attachments'])) {
                foreach ($msg['attachments'] as $attachment) {
                    $mail->addAttachment($attachment['filepath'], $attachment['filename']);
                }
            }
            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $msg['subject'];
            $mail->Body = $msg['body'];

            $mail->AltBody = '请使用支持html的邮箱客户端，以取得更好的浏览体验';
            $r = $mail->send();
            if (!$r) {
                throw new \Exception('邮件发送失败:'.$mail->ErrorInfo);
            }
            return true;
        } catch (\Exception $e) {
            throw new \Exception('邮件发送失败:'.$e->getMessage().$e->getLine());
        }
    }
}
