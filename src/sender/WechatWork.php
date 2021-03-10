<?php

namespace Dongdavid\Notify\sender;

use Dongdavid\Notify\Exceptions\InvalidArgumentException;
use Dongdavid\Notify\Sender;

class WechatWork extends Sender
{
    const MESSAGE_URL = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=';

    public function checkConfig()
    {
        if (!$this->config['access_token']) {
            throw new InvalidArgumentException('notify config require access_token ');
        }
    }

    public function checkMsgFormate($msg)
    {
        if (!isset($msg['agentid'])) {
            throw new \Exception('请传入部门ID');
        }

        if (!isset($msg['msgtype'])) {
            throw new \Exception('消息类型未传入');
        }

        if ('textcard' == $msg['msgtype']) {
            if (!isset($msg['textcard'])) {
                throw new \Exception('未传入消息主体');
            }

            if (!isset($msg['textcard']['title'])) {
                throw new \Exception('未设置标题');
            }

            if (!isset($msg['textcard']['description'])) {
                throw new \Exception('未设置描述');
            }

            if (!isset($msg['textcard']['url'])) {
                throw new \Exception('未设置跳转链接');
            }
        } elseif ('text' == $msg['msgtype']) {
            if (!isset($msg['text'])) {
                throw new \Exception('未传入消息主体');
            }
            if (!isset($msg['text']['content'])) {
                throw new \Exception('未设置消息内容');
            }
        }

        if (!isset($msg['touser']) && !isset($msg['toparty']) && !isset($msg['totag'])) {
            throw new \Exception('未设置接收人');
        }

        if (empty($msg['touser']) && empty($msg['toparty']) && empty($msg['totag'])) {
            throw new \Exception('接收人不能为空');
        }
    }

    public function send($msg)
    {
        $msg['agentid'] = $this->config['agentid'];
        $this->checkMsgFormate($msg);
        // 发送
        $res = $this->sendCard($msg);
        if (!isset($res['errcode'])) {
            throw new \Exception('发送失败:网络错误,无法请求微信服务器');
        }
        if (0 == $res['errcode']) {
            return true;
        } else {
            if (in_array($res['errcode'], ['40014', '41001', '42001'])) {
                $this->config['access_token'] = WechatManager::updateAccessToken($this->config);
            }

            throw new \Exception('发送失败:'.$res['errcode'].$res['errmsg']);
        }
        // return false;
    }

    /**
     * [sendTextToUser 发送卡片消息]
     * look me baby.
     *
     * @Author   DongDavid
     * @DateTime 2017-07-12T14:31:25+0800
     *
     * @param [type] $access_token [description]
     * @param int    $agentid      [应用id]
     * @param string $title        [标题128字符]
     * @param string $description  [描述 512字符]
     * @param string $url          [消息内容 不超过2048字节 换行用\n]
     * @param string $touser       [成员ID列表@all 为全部 多人用|分隔最多1000个 @all会忽略部门标签]
     * @param string $topart       [部门 多个用|分隔最多100个]
     * @param string $totag        [标签 多个用|分隔最多100个]
     * @param int    $safe         [是否保密 0不保密 1保密]
     *
     * @return [type] [description]
     *                支持div标签 目前内置了3种文字颜色：灰色(gray)、高亮(highlight)、默认黑色(normal)
     *                以class方式引用即可 换行使用<br>
     */
    public function sendCard($msg)
    {
        $url = self::MESSAGE_URL.$this->config['access_token'];
        // halt($msg);
        return $this->post($url, $msg);
    }
}
