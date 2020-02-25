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

    public function check_user(){

    }
    /**
    获取用户所有地址
     */
    public function get_user_address(){
        //$user_id = input('user_id');
        $user_id = 1;
        $address = Db::name('user_address')->where('user_id',$user_id)->select()->all();
        if($address){
            $this->success('请求成功!', $address);
        }else{
            $this->error('请求失败');
        }
    }
    /**
    添加用户地址
     */
    public function add_user_address(){
        //$user_id = input('user_id');
        $user_id=1;
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
            'is_'
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
        $data=[[1,3],[2,3]];
        foreach ($data as $k=>$v){

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
