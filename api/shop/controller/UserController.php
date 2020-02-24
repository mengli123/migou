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
        $user_id = input('user_id');
        $address = Db::name('user_address')->where('user_id',$user_id)->select()->all();
        if($address){
            $this->success('请求成功!', $address);
        }else{
            $this->error('请求失败');
        }
    }
    /**
    修改用户地址
     */
    public function add_user_address(){
        $user_id = input('user_id');
    }
}
