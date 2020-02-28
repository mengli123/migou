<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace api\shop\controller;


use cmf\controller\HomeBaseController;
use cmf\controller\RestBaseController;
use think\Db;
use think\Session;
use think\Validate;
class LoginController extends RestBaseController
{
    public function test(){
        cache(16606332135,1234);
        echo cache(16606332135);
    }
    /**
    获取手机验证码
     */
    public function get_phone_code(){
        $this->origin();
        $mobile = input('mobile');
        //手机号正则
        if(!preg_match("/^(((13[0-9])|(14[579])|(15([0-3]|[5-9]))|(16[6])|(17[0135678])|(18[0-9])|(19[0-9]))\\d{8})$/",$mobile)){
            $this->error('请填写正确手机号');
        }
        $code=send_code($mobile);
        if($code){
            cache($mobile,$code);
            $this->success('验证码发送成功',$code);
        }else{
            $this->error('验证码发送失败');
        }

    }
    /**
    手机号登录验证
     */
    public function do_mobile_login(){
        $this->origin();
        $mobile = input('mobile');
        $openid= input('openid');
        $nick_name = input('nick_name');
        $avatar = input('avatar');
        $code = input('code');
        if(!preg_match("/^(((13[0-9])|(14[579])|(15([0-3]|[5-9]))|(16[6])|(17[0135678])|(18[0-9])|(19[0-9]))\\d{8})$/",$mobile)){
            $this->error('请填写正确手机号');
        }
        if(!$nick_name){
            $nick_name='咪购用户'.$mobile;
        }
        if(!$avatar){
            $avatar='1';
        }

        if($code!=cache($mobile)){
            $this->error('验证码不正确，请重新输入','-1');
        }
        $user =Db::name('user')->where(['mobile'=>$mobile])->find();

        if(!$user){
            /** 用户不存在，去注册*/
            $insert = Db::name('user')->insertGetId(['mobile'=>$mobile,'openid'=>$openid,'user_nickname'=>$nick_name,'avatar'=>$avatar]);
            if($insert){
                $this->success('已注册并登录成功',$insert);
            }
           // echo '已注册并登录';
        }else{
            if($openid){
                $update=Db::name('user')->where('mobile',$mobile)->update(['openid'=>$openid,'user_nick_name'=>$nick_name,'avatar'=>$avatar]);
                if($update){
                    $this->success('更新用户信息并且登录成功',$user['id']);
                }
            }else{
                $this->success('登录成功',$user['id']);
            }
        }
       // unset($user['user_pass']);

        //dump($user);
    }
    /**
    微信登录查询
     */
    public function wx_check_login(){
        $openid = input('openid');
        if(!$openid){
            $this->error('请传入openid');
        }
        $user= Db::name('user')->where('openid',$openid)->find();
        if(!$user){
            /** 用户不存在*/
                $this->error('用户不存在','');
            }else{
                $this->success('用户存在',$user['id']);
        }
    }

    /**
    微信登录之后绑定手机号
     */
    public function bind_mobile(){

    }

    /**
     * 登录验证提交
     */
    public function dologin()
    {
        if ($_POST) {
            $this->origin();
            $request = request();
            $phone = $request->param("phone");
            $password = $request->param("password");
            if (empty($phone)) {
                exit(json_encode(array("status"=>500,"msg"=>"请输入账号")));    
            } 
            if (empty($password)) {
                exit(json_encode(array("status"=>500,"msg"=>"请输入密码")));    
            }
            //判断账号
            $check_phone=Db::name("driver")->where(array("phone"=>$phone))->find();
            if (!$check_phone) {
                exit(json_encode(array("status"=>500,"msg"=>"账号不存在")));  
            }
            //判断密码
            if (md5(md5($password))==$check_phone['password']) {
                Db::name("driver")->where("driver_id",$check_phone['driver_id'])->update(array("login_time"=>time()+3600));
                exit(json_encode(array("status"=>200,"msg"=>"登录成功","driver_id"=>$check_phone['driver_id'])));  
            }else{
                exit(json_encode(array("status"=>500,"msg"=>"密码错误")));  
            }
        } 
    }

    /**
     * 微信登录验证提交
     */
    public function wx_logins()
    {
        if ($_POST) {
            $this->origin();
            $request = request();
            $unionid = $request->param("unionid");
            file_put_contents('unionid.txt', $unionid);
            if (empty($unionid)) {
                exit(json_encode(array("status"=>500,"msg"=>"缺少unionid")));    
            } 
            
            //判断账号
            $check_unionid=Db::name("driver")->where(array("unionid"=>$unionid))->find();
            if (!$check_unionid) {
                exit(json_encode(array("status"=>500,"msg"=>"账号不存在")));  
            }

            Db::name("driver")->where("driver_id",$check_unionid['driver_id'])->update(array("login_time"=>time()+3600));
            exit(json_encode(array("status"=>200,"msg"=>"登录成功","driver_id"=>$check_unionid['driver_id'])));  
            
        } 
    }

    public function check_session($str){
        if (!$str) {
            exit(json_encode(array("status"=>666,"msg"=>"非法操作")));
        }
        
        $data=explode('=', $str);
        if (empty($data[0])) {
            exit(json_encode(array("status"=>666,"msg"=>"非法操作")));
        }
        if (empty($data[1])) {
            exit(json_encode(array("status"=>666,"msg"=>"非法操作")));
        }
        $token="ee34413661ef38d73b9";
        $str=$data[0]."&".$data[2]."&".$data[4]."&".$data[7]."&sign=".$token;
        $sign=md5(md5($str));
        if ($sign!=$data[1]) {
            exit(json_encode(array("status"=>666,"msg"=>"非法操作")));
        }
        
        return $data[0];
 
    }

    //检查登录有效期
    public function login_time(){
        $this->origin();
        $request= request();
        $user_id = $request->param('driver_id');
        $login_time=Db::name("driver")->where("driver_id",$user_id)->value("login_time");
        $now=time();
        if ($now>$login_time) {
            exit(json_encode(array("status"=>500,"msg"=>"登录过期")));
        }else{
            exit(json_encode(array("status"=>200,"msg"=>"登录有效")));
        }
    }

    //跨域方法
    public function origin(){
        // 指定允许其他域名访问
        header('Access-Control-Allow-Origin:*');
        // 响应类型
        // header('Access-Control-Allow-Methods:POST');
        header('Access-Control-Allow-Methods: OPTIONS,POST,GET');
        // 响应头设置
        header('Access-Control-Allow-Headers:origin,x-requested-with,content-type');

    }

    //退出
   public function loginout(){
        $this->origin();
        $request= request();
        $driver_id = $request->param('driver_id');
        if (Db::name("driver")->where("driver_id",$driver_id)->update(array("login_time"=>0))) {
            exit(json_encode(array("status"=>200)));
        }else{
            exit(json_encode(array("status"=>500)));
        }
    }

    // 检查更新
    public function version(){
        $this->origin();
        $request = request();
        $version = $request->param("version");
        if($version == "1.0"){
            exit(json_encode(array("status"=>200,"msg"=>"当前无新版本")));
        }else{
            exit(json_encode(array("status"=>300,"msg"=>"有新版本")));
        }
    }

}