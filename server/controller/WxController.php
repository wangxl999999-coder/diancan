<?php
class WxController
{
    public function postCode2Session()
    {
        $code = $_GET['code'] ?? '';
        if (!$code) {
            Response::error('code不能为空');
        }
        $cfg = require __DIR__ . '/../config.php';
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$cfg['wx']['appid']}&secret={$cfg['wx']['secret']}&js_code={$code}&grant_type=authorization_code";
        $res = json_decode(file_get_contents($url), true);
        Response::success($res);
    }

    public function getAccessToken()
    {
        $cfg = require __DIR__ . '/../config.php';
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$cfg['wx']['appid']}&secret={$cfg['wx']['secret']}";
        $res = json_decode(file_get_contents($url), true);
        Response::success($res);
    }
}
