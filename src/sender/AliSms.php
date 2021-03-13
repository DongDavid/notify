<?php

namespace Dongdavid\Notify\sender;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use Dongdavid\Notify\Sender;

class AliSms extends Sender
{
    public function send($data)
    {
        AlibabaCloud::accessKeyClient($this->config['accessKeyId'], $this->config['accessKeySecret'])
            ->regionId('cn-hangzhou')
            ->asDefaultClient();
        $query = [
            'RegionId' => 'cn-hangzhou',
            'SignName' => $this->config['SignName'],
            'TemplateCode' => $this->config['template_code'],
            'PhoneNumbers' => $data['phone'],
            'TemplateParam' => json_encode($data['template_param'], JSON_UNESCAPED_UNICODE),
        ];
        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => $query,
                ])
                ->request();

            return $result->toArray();
        } catch (ClientException $e) {
            throw new \Exception('短信发送失败-client:'.$e->getErrorMessage());
        } catch (ServerException $e) {
            throw new \Exception('短信发送失败-server:'.$e->getErrorMessage());
        }
    }

    public function checkConfig()
    {
        // TODO: Implement checkConfig() method.
        if (!isset($this->config['accessKeyId']) || !$this->config['accessKeyId']) {
            throw new \Exception('缺失accessKeyId');
        }
        if (!isset($this->config['accessKeySecret']) || !$this->config['accessKeySecret']) {
            throw new \Exception('缺失accessKeySecret');
        }
        if (!isset($this->config['SignName'])) {
            throw new \Exception('缺失短信签名');
        }
        if (empty($this->config['template_code'])) {
            throw new \Exception('模版ID不能为空');
        }
    }

    public function checkMsgFormate($msg)
    {
        // TODO: Implement checkMsgFormate() method.
        if (empty($msg['phone'])) {
            throw new \Exception('手机号码不能为空');
        }
        if (!(isset($msg['checkPhone']) && false === $msg['checkPhone'])) {
            $phonePreg = '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#';
            if (!preg_match($phonePreg, $msg['phone'])) {
                throw new \Exception('手机号码格式不正确');
            }
        }
    }
}
