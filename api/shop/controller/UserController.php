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
    public function delete_user_address(){
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
        $data=[];
       // $specs =input('specs_id');
        $address_id=input('address_id');
        $user_id= input('user_id');
        $address_data = Db::name('user_address')->where('id',$address_id)->find();

        $data['order_no']=time().mt_rand(111111,999999);
        $data['ctime']=time();
        $data['user_id']=$user_id;

        $specs=[[4,3],[5,2]];
        foreach ($specs as $k=>$v){
            $goods_specs=Db::name('goods_specs')->where('specs_id',$v[0])->find();
            $goods=Db::name('goods')->where('goods_id',$goods_specs['goods_id'])->find();
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

                $data['total']=$goods_specs['group_price']*$v[1];
            }else{
                /** 未在团购*/
                $data['total']=$goods_specs['price']*$v[1];
            }
            $data['goods_area']=$goods['goods_area'];
            $data['supplier']=$goods['supplier'];
            $data['num'] = $v[1];
            $data['name']=$address_data['name'];
            $data['mobile']=$address_data['mobile'];
            $data['address']=$address_data['address'];
            $insert = Db::name('order')->insert($data);
        }
        if($insert){
            $this->success('下单成功');
        }else{
            $this->error('下单失败');
        }
    }
    /**
    订单列表
     */
    public function user_order_list(){

    }
    /**
    查看订单详情
     */
    public function user_order_detail(){

    }


}
