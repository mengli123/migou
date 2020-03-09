<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------

namespace api\shop\controller;

use cmf\controller\RestBaseController;
use think\Db;
use think\Request;

class UserController extends RestBaseController
{
    /**
    微信登录
     */
    public function wx_login(){

    }
    /**
    获取access_token
     */
    public function get_access_token(){
        $weixin_config=Db::name("weixin_config")->field('access_token,expire_in,appid,appsecret')->find(1);
        $access_token = $weixin_config['access_token'];
        $expires_in =$weixin_config['expire_in'];
        $appid = $weixin_config['appid'];
        $appsecret = $weixin_config['appsecret'];

        if(time() - $expires_in >= 5000){
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
            $result = $this->https_request($url);
            $jsoninfo = json_decode($result,true);
            if($jsoninfo["access_token"]){
                $access_token = $jsoninfo["access_token"];
                //更新时间和access_token
                $data['access_token']=$access_token;
                $data['expire_in']=time();
                Db::name("weixin_config")->where("id",1)->update($data);
                return $access_token;
            }else{
                return false;
            }
        }else{
            return $access_token;
        }
    }
    /**
    手机号登录
     */
    public function mobile_login(){

    }
    /**
    获取用户信息
     */
    public function get_user_info(){
        $user_id = input('user_id');
        $user=Db::name('user')->where('id',$user_id)->find();
        if($user){
            unset($user['user_pass']);
            $this->success('请求成功',$user);
        }else{
            $this->error('请求失败');
        }
    }
    /**
    用户购物车列表
     */
    public function user_car_list(){
        $user_id =input('user_id');
        if(!$user_id){
            $this->error('请传入user_id');
        }
        $list = Db::name('goods_car')
            ->alias('c')
            ->join('goods g','g.goods_id=c.goods_id')
            ->join('goods_specs s','c.specs_id=s.specs_id')
            ->where(['user_id'=>$user_id])
            ->order("c.id DESC")
            ->select()->all();

        if($list){
            foreach ($list as $k=>$v){
                // echo $v['goods_id'];
                $list[$k]['goods_pics']=json_decode($v['goods_pics']);
            }
            //dump($list);
            $this->success('请求成功!', $list);
        }else{
            $this->error('请求失败',[]);
        }
    }
    /**
    用户++购物车
     */
    public function user_car_add(){
        $user_id=input('user_id');
        $specs_id =input('specs_id');
        $goods_id = input('goods_id');
        $num=input('num');
        if(!$user_id){
            $this->error('请传入用户id');
        }
        if(!$specs_id){
            $this->error('请传入规格id');
        }
        if(!$goods_id){
            $this->error('请传入商品id');
        }
        if(!$user_id){
            $this->error('请传入商品数量');
        }
        $sel = Db::name('goods_car')->where(['user_id'=>$user_id,'specs_id'=>$specs_id,'goods_id'=>$goods_id])->select()->all();
        if(count($sel)<1){
            //购物车没有，新建
            //echo 'no';
            $add=Db::name('goods_car')->insert(['user_id'=>$user_id,'specs_id'=>$specs_id,'goods_id'=>$goods_id,'num'=>1]);
        }else{
            $add=Db::name('goods_car')->where(['user_id'=>$user_id,'specs_id'=>$specs_id,'goods_id'=>$goods_id])->setInc('num',$num);
        }
        if($add){
            $this->success('加入购物车成功');
        }else{
            $this->error('添加购物车失败');
        }

    }
    /**
    用户--购物车
     */
    public function user_car_cut(){
        $user_id=input('user_id');
        $specs_id =input('specs_id');
        $goods_id = input('goods_id');
        $num=input('num');
        if(!$user_id){
            $this->error('请传入用户id');
        }
        if(!$specs_id){
            $this->error('请传入规格id');
        }
        if(!$goods_id){
            $this->error('请传入商品id');
        }
        if(!$user_id){
            $this->error('请传入商品数量');
        }
        $now_num = Db::name('goods_car')->where(['user_id'=>$user_id,'specs_id'=>$specs_id,'goods_id'=>$goods_id])->value('num');
        if($now_num<=$num){
            //echo 'no';
            //$this->error('最少保留1件');
            $cut=Db::name('goods_car')->where(['user_id'=>$user_id,'specs_id'=>$specs_id,'goods_id'=>$goods_id])->delete();
        }else{
            $cut=Db::name('goods_car')->where(['user_id'=>$user_id,'specs_id'=>$specs_id,'goods_id'=>$goods_id])->setDec('num',$num);
        }
        if($cut){
            $this->success('减掉商品成功');
        }else{
            $this->error('减掉商品失败');
        }
    }
    /**
    用户清购物车
     */
    public function user_car_clear(){
        $user_id=input('user_id');
        if(!$user_id){
            $this->error('请传入用户id');
        }
        $clear = Db::name('goods_car')->where('user_id',$user_id)->delete();
        if($clear){
            $this->success('清购物车成功');
        }else{
            $this->error('清购物车失败');
        }
    }

    /**
    获取用户所有地址
     */
    public function get_user_address(){
        $user_id = input('user_id');
        //$user_id = 1;
        $address = Db::name('user_address')->where('user_id',$user_id)->order('is_default desc')->order('c_time desc')->select()->all();
        if($address){
            $this->success('请求成功!', $address);
        }else{
            $this->error('请求失败',[]);
        }
    }
    /**
    添加用户地址
     */
    public function add_user_address(){
        $user_id = input('user_id');
        //$user_id=1;
        $name=input('name');
        $mobile=input('mobile');
        $address=input('address');
        $is_default=input('is_default');
        if (empty($name)) {
            $this->error('请填写收件人');exit;
        }
        if (empty($mobile)) {
            $this->error('请填写手机号');exit;
        }
        if (empty($address)) {
            $this->error('请填写收件地址');exit;
        }
        $insert = Db::name('user_address')->insert([
            'user_id'=>$user_id,
            'name'=>$name,
            'mobile'=>$mobile,
            'address'=>$address,
            'is_default'=>$is_default,
            'c_time'=>time()
        ]);
        if($insert){
            $this->success('添加成功',$insert);
        }else{
            $this->error('添加失败');
        }
    }
    /**
    修改用户地址
     */
    public function edit_user_address(){
        $address_id =input('address_id');
        $name=input('name');
        $mobile=input('mobile');
        $address=input('address');
        if(!$address_id){
            $this->error('缺少地址id');
        }
        if (empty($name)) {
            $this->error('请填写收件人');exit;
        }
        if (empty($mobile)) {
            $this->error('请填写手机号');exit;
        }
        if (empty($address)) {
            $this->error('请填写收件地址');exit;
        }
        $insert = Db::name('user_address')->where(['id'=>$address_id])->update([
            'name'=>$name,
            'mobile'=>$mobile,
            'address'=>$address
        ]);
        if($insert){
            $this->success('修改成功',$insert);
        }else{
            $this->error('修改失败');
        }
    }
    /**
    删除地址
     */
    public function del_user_address(){
        $address_id =input('address_id');
        if(!$address_id){
            $this->error('缺少地址id');
        }
        $del= Db::name('user_address')->where('id',$address_id)->delete();
        if($del){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    /**
    充值
     */
    public function recharge(){
        $num=input('num');
        $user_id=input('user_id');
        $data=[];
        $data['num']=$num;
        $data['price']=1;
        $data['goods_id']=-1;
        $data['total_price']=$num*1;
        $data['goods_name']='余额充值';
        $data['ctime']=time();
        $data['order_no']=date('YmdHis').mt_rand(111111,999999);
        $data['user_id']=$user_id;
        $insert = Db::name('order')->insert($data);
        if($insert){
            $this->success('生成充值订单成功',$data['order_no']);
        }else{
            $this->error('生成充值订单失败');
        }
    }

    /**
    生成订单
     */
    public function create_order(){
        $request = request();
        $data=[];
        $specs =$request->param('specs_id');
        //dump($specs);
        if(!$specs){
            $this->error('请传入商品规格和数量字符串');
        }
        $user_id= input('user_id');
        if(!$user_id){
            $this->error('请传入用户id');
        }
//        if($specs==-100){
//
//        }
        $array = explode(';',$specs);
        $specs=[];
        foreach ($array as $k=>$v){
            $specs[]=explode(',',$v);
        }

        // $this->success('下单成功',$specs);
        //$specs=json_decode($specs);
        $address_id=input('address_id');
        if(!$address_id){
            $this->error('请选择地址');
        }

        $is_up_group=input('is_up_group');
        //$is_join_group=input('is_join_group');
        $group_id=input('group_id');

        $address_data = Db::name('user_address')->where('id',$address_id)->find();

        $data['order_no']=date('YmdHis').mt_rand(111111,999999);
        $data['ctime']=time();
        $data['user_id']=$user_id;

       // $specs=[[6,3],[7,2]];
        foreach ($specs as $k=>$v){
            $goods_specs=Db::name('goods_specs')->where('specs_id',$v[0])->find();
            $goods=Db::name('goods')->where('goods_id',$goods_specs['goods_id'])->find();
            //dump($goods);
            if($goods_specs['all_current_count']<0){
                $this->error('当前规格没有库存了');
            }
            $data['specs_id']=$v[0];
            $data['goods_id']=$goods_specs['goods_id'];
            $data['goods_specs']=$goods_specs['size'];

            if($is_up_group==1){
                /** 开团*/
                if($goods_specs['group_max_count']<$goods_specs['group_current_count']+1){
                    $this->error('当前数量超过库存团购余量');
                }
                $data['total_price']=$goods_specs['group_price']*$v[1];
                $data['price']=$goods_specs['group_price'];

                $group_data=[
                    'user_id'=>$user_id,
                    'goods_id'=>$goods_specs['goods_id'],
                    'specs_id'=>$v[0],
                    'ctime'=>time(),
                    'status'=>0,
                    'group_price'=>$goods_specs['group_price'],
                    'min_count'=>$goods_specs['min_count'],
                    'return_money'=>$goods_specs['group_price']*$goods_specs['return_rate']
                ];
                $group_id=Db::name('group_open_log')->insertGetId($group_data);
                $data['group_id']=$group_id;

            }elseif($group_id){
                $group_status=Db::name('group_open_log')->where('group_id',$group_id)->value('status');
                if($group_status==1){
                    $this->error('此团人数已满，请参加其他或重新开团');
                }
                if($group_status==-1){
                    $this->error('此团已过期或解散');
                }
                $partner_str=Db::name('group_open_log')->where('group_id',$group_id)->value('partner');
                if($partner_str==''){
                    $partner=[$user_id];
                }else{
                    $partner=json_decode($partner_str);
                    if(count($partner)==$goods_specs['min_count']-1){
                        $group_status=1;
                        Db::name('group_open_log')->where('group_id',$group_id)->update(['status'=>$group_status]);
                    }
                    $partner[]=$user_id;
                }
                $data['group_id']=$group_id;

                /** 参团*/
                $data['total_price']=$goods_specs['group_price']*$v[1];
                $data['price']=$goods_specs['group_price'];
                $partner_str=json_decode($partner);
                Db::name('group_open_log')->where('group_id',$group_id)->update(['partner'=>$partner_str]);
            }else{
                /** 普通购买*/
                $data['total_price']=$goods_specs['price']*$v[1];
                $data['price']=$goods_specs['price'];
            }



            $data['total_price']=$goods_specs['price']*$v[1];
            $data['price']=$goods_specs['price'];
            $data['goods_name']=$goods['goods_name'];
            $data['goods_area']=$goods['goods_area'];
            $data['supplier']=$goods['supplier'];
            $data['num'] = $v[1];
            $data['name']=$address_data['name'];
            $data['mobile']=$address_data['mobile'];
            $data['address']=$address_data['address'];
            //dump($data);exit;
            $insert = Db::name('order')->insert($data);
        }
        if($insert){
            $this->success('下单成功',$data['order_no']);
        }else{
            $this->error('下单失败');
        }
    }
    /**
    订单列表
     */
    public function user_order_list(){
        $user_id= input('user_id');
        $status = input('status');
        $order = Db::name('order')->where(['user_id'=>$user_id,'status'=>$status])->select();
        if($order){
            $this->success('查询成功',$order);
        }else{
            $this->error('查询失败',[]);
        }
    }
    /**
    查看订单详情
     */
    public function user_order_detail(){
       // $order_id= input('order_id');
        $order_no=input('order_no');
        $order = Db::name('order')->where('order_no',$order_no)->select()->all();
        if($order){
            $this->success('查询成功',$order);
        }else{
            $this->error('查询失败',[]);
        }
    }
    /**
    取消订单
     */
    public function cancel_user_order(){
        $order_no=input('order_no');
        if($order_no!=0){
            $this->error('只有未支付状态订单才能取消');
        }
        $cancel = Db::name('order')->where('order_no',$order_no)->update(['status'=>-1]);
        if($cancel){
            $this->success('取消订单成功');
        }else{
            $this->error('取消订单失败');
        }
    }

    /**
    赠送积分接口
     */
    public function send_score(){
        $point_rule=Db::name('point_rule')->where('id',2)->find();
        $rate=$point_rule['point']/$point_rule['money'];
        $sender=input('sender');
        $score=input('score');
        $receiver=input('receiver');
        $status=0;
        $money=$score*$rate;
        $insert = Db::name('send_point_log')->insert([
           'sender'=>$sender,
            'score'=>$score,
            'money'=>$money,
            'receiver'=>$receiver,
            'ctime'=>time(),
            'status'=>$status
        ]);
        if($insert){
            Db::name('user')->where('id',$sender)->setDec('score',$score);
            $this->success('赠送已发出');
        }else{
            $this->error('赠送失败');
        }
    }

    /**
    接受积分
     */
    public function receive_score(){
        $log_id =input('log_id');
        $receiver = input('receiver');
        $log = Db::name('send_point_log')->where('id',$log_id)->find();
        if($receiver!=$log['receiver']){
            $this->error('仅限本人领取赠送积分');
        }
        $balance=Db::name('user')->where('id',$log['receiver'])->value('balance');
        if($balance<$log['money']){
            $this->error('余额不足，请先充值');
        }
        $score=Db::name('user')->where('id',$log['receiver'])->setInc('score',$log['score']);
        $update=Db::name('send_point_log')->where('id',$log_id)->update(['status'=>1]);
        if($score&&$update){
            Db::name('user')->where('id',$log['receiver'])->setDec('balance',$log['money']);
            $this->success('赠送已接受');
        }else{
            $this->error('接受赠送失败');
        }

    }
    /**
    余额兑换积分
     */
    public function balance_to_score(){
        $rule=Db::name('point_rule')->where('id',3)->find();
        $rate = $rule['point']/$rule['money'];
        $user_id=input('user_id');
        $score =input('score');
        $money=$score/$rate;
        $balance=Db::name('user')->where('id',$user_id)->value('balance');
        if($balance<$money){
            $this->error('余额不足，请先充值');
        }
        $balance =Db::name('user')->where('id',$user_id)->setDec('balance',$money);
        if($balance){
            Db::name('user')->where('id',$user_id)->setInc('score',$score);
            $this->success('余额充值积分成功');
        }else{
            $this->error('充值积分失败');
        }

    }
    /**
    查询增粉记录
     */
    public function get_receive_score(){
        $receiver =input('receiver');
        $log=Db::name('send_point_log')->where('receiver',$receiver)->select()->all();
        if($log){
            $this->success('查询成功',$log);
        }else{
            $this->error('查询失败',[]);
        }
    }

}
