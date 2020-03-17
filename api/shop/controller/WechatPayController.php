<?php
namespace api\shop\controller;
//use cmf\controller\RestBaseController;
use cmf\controller\RestBaseController;
use think\Controller;
use think\Db;

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
        $notifyUrl='https://migou.justmetu.top/api/shop/wechat_pay/notify_url';
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
            'sign_type' =>'MD5'
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

        //$unified['sign_type'] ='MD5';
        //$unified['sign'] = hash_hmac('sha256', $a,$config['key']);
        $unified['sign'] =$sign;
        //dump($unified);

        //$unified['sign'] = self::getSign($unified, $config['key']);
       // dump($unified);exit;
        $xml=self::arrayToXml($unified);
        //echo $xml;
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
            "appid" => $config['appid'],
            "partnerid"=>$config['mch_id'],
            "prepayid"=>$unifiedOrder['prepay_id'],
            "package" => "Sign=WXPay",
            "noncestr" => self::createNonceStr(),
            "timestamp" => "$timestamp",        //这里是字符串的时间戳，不是int，所以需加引号

            //"package" => "prepay_id=" . $unifiedOrder->prepay_id,



           // "sign" => $sign,
        );
        ksort( $arr);
        $a = array();
        foreach ( $arr as $k => $v) {
            if ((string) $v === '') {
                continue;
            }
            $a[] = "{$k}={$v}";
        }
        $a = implode('&', $a);
        $sign=  strtoupper(md5($a.'&key='.$config['key']));
        $arr['sign'] = $sign;
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
//        $d= [
//            'return_code'=>'SUCCESS',
//            'out_trade_no'=>'20200301173225653173'
//        ];
        //return json($d);
       $this->notify_methed($d);

//  验证函数
//        if (empty($d['sign'])) {
//            return false;
//        }
//        $sign = $d['sign'];
//        unset($d['sign']);
//        return $sign == $this->sign($d);
    }
    /** 余额支付*/
    public function balance_pay(){
        $total_fee=input('total_fee');
        $order_no=input('order_no');
        $order=Db::name('order')->where('order_no',$order_no)->field('goods_id,user_id,total_price,share_id,status')->find();
        if(!$order){
            $this->error('查不到该订单号');
        }
        $user_balance=Db::name('user')->where('id',$order['user_id'])->value('balance');
        if($user_balance<$total_fee){
            $this->error('余额不足本次支付');
        }
        $a=Db::name('user')->where('id',$order['user_id'])->setDec('balance',$total_fee);
        $d=['out_trade_no'=>$order_no];
        if($a){
            $d['return_code']='SUCCESS';
        }else{
            $d['return_code']='FAILED';
        }
        $this->notify_methed($d);
    }

    public function notify_methed($d){
        if ($d['return_code'] == 'SUCCESS') {
            $order_no=$d['out_trade_no'];
            $order=Db::name('order')->where('order_no',$order_no)->field('goods_id,user_id,total_price,share_id,status')->select();
            foreach ($order as $k=>$v){
                // dump($v);
                Db::name('order')->where('order_no',$order_no)->update(['status'=>1]);
                if($v['goods_id']==-1){
                    if($v['status']==0){
                        $balance = $v['total_price'];
                        Db::name('user')->where('id', $v['user_id'])->setInc('balance', $balance);
                        Db::name('user_balance_log')->insert([
                            'user_id'=>$v['user_id'],
                            'create_time'=>time(),
                            'change'=>'+'.$balance,
                            'description'=>'充值'
                        ]);
                    }else{
                        $this->error('请不要重复支付');
                    }

                }else{
                    $point_rule=Db::name('point_rule')->find();
                    $rate=$point_rule['point']/$point_rule['money'];
                    $score=$v['total_price']*$rate;
                    Db::name('user')->where('id',$v['user_id'])->setInc('score',$score);
                    /**
                    判断有无分享者，若有则返利5%积分给分享者
                     */
                    if($v['share_id']!=null){
                        $share_score = $score*0.05;
                        Db::name('user')->where('id',$v['share_id'])->setInc('score',$share_score);
                    }
                    /**
                    在这里查找代理关系，返积分给上级代理
                     */
                    $parent_a =Db::name('user')->where('id',$v['user_id'])->value('parent_id');
                    if($parent_a!=0){
                        $rule_a=Db::name('point_rule')->where('id',4)->find();
                        $a=$rule_a['money']/$rule_a['point'];
                        $score_a =$score*$a;
                        $score_aa=$score-$score_a;
                        Db::name('user')->where('id',$v['user_id'])->setInc('score',$score_aa);
                        Db::name('user')->where('id',$v['user_id'])->setInc('return_score',$score_a);
                        Db::name('user')->where('id',$parent_a)->setInc('score',$score_a);
                        $parent_b =Db::name('role_user')->where('user_id',$parent_a)->value('parent_id');
                        if($parent_b){
                            $rule_b=Db::name('point_rule')->where('id',5)->find();
                            $b=$rule_b['money']/$rule_b['point'];
                            $score_b =$score_a*$b;
                            Db::name('user')->where('id',$parent_a)->setInc('return_score',$score_b);
                            Db::name('user')->where('id',$parent_b)->setInc('score',$score_b);
                            $parent_c =Db::name('role_user')->where('user_id',$parent_b)->value('parent_id');
                            if($parent_c){
                                $rule_c=Db::name('point_rule')->where('id',6)->find();
                                $c=$rule_c['money']/$rule_c['point'];
                                $score_c=$score_b*$c;
                                Db::name('user')->where('id',$parent_b)->setInc('return_score',$score_c);
                                Db::name('user')->where('id',$parent_c)->setInc('score',$score_c);
                            }
                        }
                    }

                }
            }
            $this->success('支付成功');
        }else{
            $this->error('支付失败');
        }
    }

    public function test_notify()
    {
        $d = [
            'return_code' => 'SUCCESS',
            'out_trade_no' => input('order_no')
        ];
        //return json($d);
        if ($d['return_code'] == 'SUCCESS') {
            $order_no = $d['out_trade_no'];
            $order = Db::name('order')->where('order_no', $order_no)->field('goods_id,user_id,total_price,share_id,status')->select();
            foreach ($order as $k => $v) {
                 dump($v);
                Db::name('order')->where('order_no', $order_no)->update(['status' => 1]);
                if ($v['goods_id'] == -1) {
                    if($v['status']==0){
                        $balance = $v['total_price'];
                        Db::name('user')->where('id', $v['user_id'])->setInc('balance', $balance);
                    }else{
                        $this->error('请不要重复支付');
                    }


                } else {
                    $point_rule = Db::name('point_rule')->find();
                    $rate = $point_rule['point'] / $point_rule['money'];
                    $score = $v['total_price'] * $rate;
                    Db::name('user')->where('id', $v['user_id'])->setInc('score', $score);
                    /**
                     * 判断有无分享者，若有则返利5%积分给分享者
                     */
                    if ($v['share_id'] != null) {
                        $share_score = $score * 0.05;
                        Db::name('user')->where('id', $v['share_id'])->setInc('score', $share_score);
                    }
                    /**
                     * 在这里查找代理关系，返积分给上级代理
                     */
                    $parent_a = Db::name('user')->where('id', $v['user_id'])->value('parent_id');
                    if ($parent_a != 0) {
                        $rule_a = Db::name('point_rule')->where('id', 4)->find();
                        $a = $rule_a['money'] / $rule_a['point'];
                        $score_a = $score * $a;
                        $score_aa = $score - $score_a;
                        Db::name('user')->where('id', $v['user_id'])->setInc('score', $score_aa);
                        Db::name('user')->where('id', $v['user_id'])->setInc('return_score', $score_a);
                        Db::name('user')->where('id', $parent_a)->setInc('score', $score_a);
                        $parent_b = Db::name('role_user')->where('user_id', $parent_a)->value('parent_id');
                        if ($parent_b) {
                            $rule_b = Db::name('point_rule')->where('id', 5)->find();
                            $b = $rule_b['money'] / $rule_b['point'];
                            $score_b = $score_a * $b;
                            Db::name('user')->where('id', $parent_a)->setInc('return_score', $score_b);
                            Db::name('user')->where('id', $parent_b)->setInc('score', $score_b);
                            $parent_c = Db::name('role_user')->where('user_id', $parent_b)->value('parent_id');
                            if ($parent_c) {
                                $rule_c = Db::name('point_rule')->where('id', 6)->find();
                                $c = $rule_c['money'] / $rule_c['point'];
                                $score_c = $score_b * $c;
                                Db::name('user')->where('id', $parent_b)->setInc('return_score', $score_c);
                                Db::name('user')->where('id', $parent_c)->setInc('score', $score_c);
                            }
                        }
                    }

                }
            }
            $this->success('支付成功');
        } else {
            $this->error('支付失败');
        }
    }
}
