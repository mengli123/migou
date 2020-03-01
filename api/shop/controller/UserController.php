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

    }
    /**
    用户--购物车
     */
    public function user_car_cut(){

    }
    /**
    用户清购物车
     */
    public function user_car_clear(){

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
    生成订单
     */
    public function create_order(){
        $request = request();
        $data=[];
        $specs =$request->param('specs_id');
        if(!$specs){
            $this->error('请传入商品规格和数量字符串');
        }
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
        $user_id= input('user_id');
        if(!$user_id){
            $this->error('请传入用户id');
        }

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
            if($goods_specs['is_group_buying']==1){
                /** 团购中*/
                if($goods_specs['group_max_count']<$goods_specs['group_current_count']+1){
                    $this->error('当前数量超过库存团购余量');
                }

                $data['total_price']=$goods_specs['group_price']*$v[1];
                $data['price']=$goods_specs['group_price'];
            }else{
                /** 未在团购*/
                $data['total_price']=$goods_specs['price']*$v[1];
                $data['price']=$goods_specs['price'];
            }
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
        $order = Db::name('order')->where('order_no',$order_no)->select();
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


}
