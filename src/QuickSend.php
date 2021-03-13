<?php

namespace Dongdavid\Notify;

class QuickSend
{
    /**
     * 微信公众号模版消息.
     *
     * @param $access_token
     * @param $openid
     * @param $template_id
     * @param $data
     * @param string $url
     * @param false  $miniProgram
     *
     * @return bool|string
     */
    public static function offical($access_token, $openid, $template_id, $data, $url = '', $miniProgram = false)
    {
        $msg = [
            'type' => 'wechatoffical',
            'config' => [
                'access_token' => $access_token,
            ],
            'data' => [
                'touser' => $openid,
                'template_id' => $template_id,
                'data' => $data,
                'url' => $url,
            ],
        ];
        if ($miniProgram) {
            $msg['data']['miniprogram'] = $miniProgram;
        }
        try {
            return Notify::send($msg);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 微信小程序.
     *
     * @param $access_token
     * @param $openid
     * @param $template_id
     * @param $data
     * @param string $page
     * @param string $miniprogram_state
     * @param string $lang
     *
     * @return bool|string
     */
    public static function miniProgram(
        $access_token,
        $openid,
        $template_id,
        $data,
        $page = '',
        $miniprogram_state = 'formal',
        $lang = 'zh_CN'
    ) {
        $msg = [
            'type' => 'miniprogram',
            'config' => [
                'access_token' => $access_token,
            ],
            'data' => [
                'touser' => $openid,
                'template_id' => $template_id,
                'data' => $data,
                'page' => $page,
                'miniprogram_state' => $miniprogram_state,
                'lang' => $lang,
            ],
        ];
        try {
            return Notify::send($msg);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 发送邮件.
     *
     * @param $config
     * @param $subject
     * @param $body
     * @param $to
     * @param array $attachments
     * @param array $cc
     * @param array $bcc
     *
     * @return bool|string
     */
    public static function mail($config, $subject, $body, $to, $attachments = [], $cc = [], $bcc = [])
    {
        $msg = [
            'type' => 'email',
            'config' => $config,
            'data' => [
                'subject' => $subject,
                'body' => $body,
                'to' => $to,
            ],
        ];
        if (!empty($attachments)) {
            $msg['data']['attachments'] = $attachments;
        }
        if (!empty($cc)) {
            $msg['data']['cc'] = $cc;
        }
        if (!empty($bcc)) {
            $msg['data']['bcc'] = $bcc;
        }

        try {
            return Notify::send($msg);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $config
     * @param $phone
     * @param $template_code
     * @param array $template_param
     *
     * @return array|string
     */
    public static function alisms($config, $phone, $template_param = [])
    {
        $msg = [
            'type' => 'alisms',
            'config' => $config,
            'data' => [
                'phone' => $phone,
                'template_param' => $template_param,
            ],
        ];
        try {
            return Notify::send($msg);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
