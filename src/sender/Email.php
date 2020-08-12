<?php
namespace Dongdavid\Notify\sender;

use Dongdavid\Notify\Sender;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
/**
 *
 */
class Email extends Sender
{

	public function checkConfig(){

		if (!isset($this->config['host'])) {
			throw new \Exception("mail config require host");
		}

		if (!isset($this->config['username'])) {
			throw new \Exception("mail config require username");
		}

		if (!isset($this->config['password'])) {
			throw new \Exception("mail config require password");
		}

		if (!isset($this->config['fromEmail'])) {
			throw new \Exception("mail config require fromEmail");
		}

		if (!isset($this->config['fromName'])) {
			throw new \Exception("mail config require fromName");
		}

		if (!isset($this->config['debug'])) {
			$this->config['debug'] = 0;
		}
		if (!isset($this->config['SMTPSecure'])) {
			$this->config['SMTPSecure'] = false;
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

	public function checkMsgFormate($msg){
		if (!isset($msg['subject'])) {
			throw new \Exception("邮件主题未设置");
		}

		if (!isset($msg['body'])) {
			throw new \Exception("邮件正文未设置");
		}

		if (!isset($msg['touser'])) {
			throw new \Exception("收件人未设置");
		}

		if (isset($msg['attachments'])) {
			$this->checkAttachments($msg['attachments']);
		}
	}

	public function checkAttachments($attachments)
	{
		if (is_string($attachments)) {
			$path = realpath($attachments);
			if (!file_exists($path)) {
				throw new \Exception("文件不存在:".$attachments);
			}
		}elseif (is_array($attachments)) {
			foreach ($attachments as $attachment) {
				if (!file_exists(realpath($attachment))) {
					throw new \Exception("文件不存在:".$attachment);
				}
			}
		}
	}

	public function send($msg)
	{
		$this->checkMsgFormate($msg);
		return $this->sendMail($msg);
	}

	private function sendMail($msg)
	{
		$mail = new PHPMailer(true);

		try {
		    //Server settings
		    $mail->SMTPDebug = $this->config['debug'];  // Enable verbose debug output
		    $mail->isSMTP();  // Set mailer to use SMTP
		    $mail->Host       = $this->config['host'];  // Specify main and backup SMTP servers
		    $mail->SMTPAuth   = true;  // Enable SMTP authentication
		    $mail->Username   = $this->config['username'];  // SMTP username
		    $mail->Password   = $this->config['password'];  // SMTP password
		    $mail->SMTPSecure = $this->config['SMTPSecure'];  // Enable TLS encryption, `ssl` also accepted
		    $mail->Port       = $this->config['port'];  // TCP port to connect to
		    $mail->CharSet    = 'UTF-8';
		    $mail->SMTPOptions = $this->config['SMTPOptions'];
		    //Recipients
		    $mail->setFrom( $this->config['fromEmail'], $this->config['fromName']);
		    // touser
		    if (is_string($msg['touser'])) {
		    	$mail->addAddress($msg['touser']);
		    }else{
		    	foreach ($msg['touser'] as $k => $v) {
		    		$mail->addAddress($v['emailAddress'],isset($v['name'])?$v['name']:'');
		    	}
		    }
		    // cc
		    if (isset($msg['cc']) && is_string($msg['cc'])) {
		    	$mail->addCC($msg['cc']);
		    }elseif (isset($msg['cc']) && !empty($msg['cc'])) {
	    		foreach ($msg['cc'] as $k => $c) {
	    			$mail->addCC($c['emailAddress'],isset($c['name'])?$c['name']:'');
	    		}
	    	}

		    // bcc
		    if (isset($msg['bcc']) && is_string($msg['bcc'])) {
		    	$mail->addBCC($msg['bcc']);
		    }elseif (isset($msg['bcc']) && !empty($msg['bcc'])) {
	    		foreach ($msg['bcc'] as $k => $bc) {
	    			$mail->addBCC($bc['emailAddress'],isset($bc['name'])?$bc['name']:'');
	    		}
	    	}

		    // Attachments
		   	if (isset($msg['attachments']) && !empty($msg['attachments'])) {
		   		foreach ($msg['attachments'] as $attachment) {
		   			if (empty($attachment)) {
		   				continue;
		   			}
		   			$attachment = realpath($attachment);
		   			$mail->addAttachment($attachment,basename($attachment));
		   		}
		   	}
		    // Content
		    $mail->isHTML(true);                                  // Set email format to HTML
		    $mail->Subject = $msg['subject'];
		    $mail->Body    = $msg['body'];

		    $mail->AltBody = '请使用支持html的邮箱客户端，以取得更好的浏览体验';
		    $r = $mail->send();
		    if (!$r) {
		    	throw new \Exception("邮件发送失败");
		    }
		    return true;
		} catch (\Exception $e) {
			trace("邮件发送失败".$mail->ErrorInfo,'error');
			throw new \Exception('邮件发送失败'.$e->getMessage());

		}
	}
}