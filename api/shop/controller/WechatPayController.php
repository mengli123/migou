<?php
namespace api\shop\controller;
//use cmf\controller\RestBaseController;
use cmf\controller\RestBaseController;
use think\Controller;

class WechatPayController extends RestBaseController {

    protected $mchid='1567722331';
    protected $appid='wxf5ebee3eedc05935';
    protected $appKey='beb089e88f083d23389d1d4e822aaad0';
    protected $apiKey='qwer2020qwer2020qwer2020qwer2020';
    public $data = null;
//    public function __construct($mchid, $appid, $appKey,$key)
//    {
//        $this->mchid = $mchid; //https://pay.weixin.qq.com 产品中心-开发配置-商户号
//        $this->appid = $appid; //微信支付申请对应的公众号的APPID
//        $this->appKey = $appKey; //微信支付申请对应的公众号的APP Key
//        $this->apiKey = $key;   //  帐户设置-安全设置-API安全-API密钥-设置API密钥
//    }
    /**
     * 统一下单
     * @param string $openid 调用【网页授权获取用户信息】接口获取到用户在该公众号下的Openid
     * @param float $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param string $orderName 订单名称
     * @param string $notifyUrl 支付结果通知url 不要有问号
     * @param string $timestamp 支付时间
     * @return string
     */
    public function Pay($totalFee, $outTradeNo)
    {
        $orderName='migou';
        $timestamp=time();
        $notifyUrl='https://www.justmetu.top/api/shop/wechat_pay/notify_url';
        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->apiKey,
        );
        //dump($config);exit;
        //$orderName = iconv('GBK','UTF-8',$orderName);
        $unified = array(
            'appid' => $config['appid'],
            'attach' => 'pay',             //商家数据包，原样返回，如果填写中文，请注意转换为utf-8
            'body' => $orderName,
            'mch_id' => $config['mch_id'],
            'nonce_str' => self::createNonceStr(),
            'notify_url' => $notifyUrl,
            'out_trade_no' => $outTradeNo,
            'spbill_create_ip' =>  request()->ip(),
            'total_fee' => intval($totalFee * 100),       //单位 转为分
            'trade_type' => 'APP',
            //'sign' =>'6d46e0eb91e3f12a959cd9d9effb42aa'
        );
        ksort($unified);
        $a = array();
        foreach ($unified as $k => $v) {
            if ((string) $v === '') {
                continue;
            }
            $a[] = "{$k}={$v}";
        }
        $a = implode('&', $a);
        $sign=  strtoupper(md5($a.'&key='.$config['key']));
        $unified['sign'] =$sign;
        //$unified['sign'] = hash_hmac('sha256', $a,$config['key']);
        //dump($unified);

        //$unified['sign'] = self::getSign($unified, $config['key']);
       // dump($unified);exit;
        $responseXml = self::curlPost('https://api.mch.weixin.qq.com/pay/unifiedorder', self::arrayToXml($unified));
        //dump($responseXml);exit;
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
       // $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

        $unifiedOrder=$this->xmlToArray($responseXml);
        //dump($unifiedOrder);

       // echo $unifiedOrder->return_code;
        if ($unifiedOrder === false) {
            die('parse xml error');
        }
        if ($unifiedOrder['return_code'] != 'SUCCESS') {
            die($unifiedOrder->return_msg);
        }
        if ($unifiedOrder['result_code'] != 'SUCCESS') {
            die($unifiedOrder->err_code);
        }

        $arr = array(
            "appId" => $config['appid'],
            "timeStamp" => "$timestamp",        //这里是字符串的时间戳，不是int，所以需加引号
            "nonceStr" => self::createNonceStr(),
            //"package" => "prepay_id=" . $unifiedOrder->prepay_id,
            "package" => "Sign=WXPay",
            "prepayid"=>$unifiedOrder['prepay_id'],
            "partnerid"=>$config['mch_id'],
            "sign" => $sign,
        );
       // $arr['paySign'] = self::getSign($arr, $config['key']);
        return json($arr);
    }
    public function xmlToArray($xml){
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }
    public static function curlGet($url = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public static function curlPost($url = '', $postData = '', $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public static function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }
    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }
    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
    public function notify_url(){
        $d = $this->xmlToArray(file_get_contents('php://input'));
        if (empty($d)) {
            $this->error('缺失参数');
        }
        if ($d['return_code'] != 'SUCCESS') {
            $this->error($d['return_msg']);
        }else{
            $this->success($d['return_msg']);
        }


//  验证函数
        if (empty($d['sign'])) {
            return false;
        }
        $sign = $d['sign'];
        unset($d['sign']);
        return $sign == $this->sign($d);
    }
}
