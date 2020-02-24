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
            'address'=>$address
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
}
