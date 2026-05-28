<?php
class WxPay
{
    private $cfg;

    public function __construct()
    {
        $config = require __DIR__ . '/config.php';
        $this->cfg = $config['wx'];
    }

    public function createOrder($orderId, $totalFee, $body, $openid)
    {
        $params = [
            'appid' => $this->cfg['appid'],
            'mch_id' => $this->cfg['mch_id'],
            'nonce_str' => $this->getNonceStr(),
            'body' => $body,
            'out_trade_no' => $orderId,
            'total_fee' => (int)($totalFee * 100),
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
            'notify_url' => $this->cfg['notify_url'],
            'trade_type' => 'JSAPI',
            'openid' => $openid
        ];
        $params['sign'] = $this->makeSign($params);
        $xml = $this->arrayToXml($params);
        $response = $this->postXmlCurl('https://api.mch.weixin.qq.com/pay/unifiedorder', $xml);
        $result = $this->xmlToArray($response);
        if ($result['return_code'] !== 'SUCCESS' || $result['result_code'] !== 'SUCCESS') {
            return ['code' => 1, 'msg' => $result['return_msg'] ?? '支付创建失败'];
        }
        return $this->getPayParams($result['prepay_id']);
    }

    public function getPayParams($prepayId)
    {
        $params = [
            'appId' => $this->cfg['appid'],
            'timeStamp' => (string)time(),
            'nonceStr' => $this->getNonceStr(),
            'package' => 'prepay_id=' . $prepayId,
            'signType' => 'MD5'
        ];
        $params['paySign'] = $this->makeSign($params);
        return ['code' => 0, 'data' => $params];
    }

    public function verifyNotify($xml)
    {
        $data = $this->xmlToArray($xml);
        $sign = $data['sign'];
        unset($data['sign']);
        if ($this->makeSign($data) !== $sign) {
            return false;
        }
        return $data;
    }

    private function makeSign($params)
    {
        ksort($params);
        $str = '';
        foreach ($params as $k => $v) {
            if ($v !== '' && $k !== 'sign') {
                $str .= "{$k}={$v}&";
            }
        }
        $str .= 'key=' . $this->cfg['mch_key'];
        return strtoupper(md5($str));
    }

    private function getNonceStr($length = 32)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }

    private function arrayToXml($arr)
    {
        $xml = '<xml>';
        foreach ($arr as $k => $v) {
            $xml .= "<{$k}><![CDATA[{$v}]]></{$k}>";
        }
        $xml .= '</xml>';
        return $xml;
    }

    private function xmlToArray($xml)
    {
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    private function postXmlCurl($url, $xml)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
