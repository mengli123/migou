<?php
namespace api\shop\controller;

use cmf\controller\RestBaseController;
use think\Db;
use think\Controller;



class CatController extends RestBaseController
{
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
    /**
    猫咪列表
     */
    public function get_cat_list(){
        $cat = Db::name('cat')->select()->all();
        if($cat){
            $this->success('获取成功',$cat);
        }else{
            $this->error('获取失败',[]);
        }
    }

    /**
    领养猫咪
     * 问题，可以同时喂养几只猫
     */
    public function buy_cat(){
        $user_id =input('user_id');
        $cat_id = input('cat_id');
        if(!$user_id){
            $this->error('请传入user_id');
        }
        if(!$cat_id){
            $this->error('请传入cat_id');
        }
        $sel = Db::name('user_cat')
            ->where(['user_id'=>$user_id,])
            ->where('age_id','in',[1,2,3])
            ->select()->all();
        if(count($sel)>0){
            $this->error('已存在喂养中的猫咪');
        }
        $data= [
            'user_id'=>$user_id,
            'cat_id'=>$cat_id,
            'age_id'=>1,
            'ctime'=>time(),
            'status'=>0
        ];
        $ins = Db::name('user_cat')->insert($data);
        if($ins){
            $this->success('领养成功');
        }else{
            $this->error('领养失败');
        }
    }

    /**
    查询当前5只猫咪年龄段以及状态
     * 每一只距离上次喂养的时间
     */
    public function user_cat_status(){
        $user_id=input('user_id');
        if(!$user_id){
            $this->error('请传入user_id');
        }
        $sel = Db::name('user_cat')
            ->where(['user_id'=>$user_id])
            ->select()->all();
        $user_cat_info=Db::name('user_cat_info')->where('user_id',$user_id)->find();
        if($sel&&$user_cat_info){
            $data=[
                'info'=>$user_cat_info,
                'cat'=>$sel
            ];
            $this->success('查询成功',$data);
        }else{
            $this->error('查询失败',[]);
        }
    }

    /**
    喂猫
     * 问题：什么时候升级进入下一个阶段，需要一个事件去触发他
     * 返回一个下次喂猫时间
     */
    public function feed_cat(){
        $user_id=input('user_id');
        $cat_id =input('cat_id');
        $age_id =input('age_id');
        $last_feed_time = Db::name('user_cat_log')->where(['user_id'=>$user_id,'cat_id'=>$cat_id])->value('ctime');
        $interval =Db::name('cat_age')->where(['cat_id'=>$cat_id,'age_id'=>$age_id])->value('interval');
        $feed_num =Db::name('cat_age')->where(['cat_id'=>$cat_id,'age_id'=>$age_id])->value('feed_num');
        $duration=time()-$last_feed_time; //距上次喂猫过去了$interval秒
        $will =$interval-$duration;  //$will秒后可以喂猫
        if($duration<$interval){
            $this->error('未到喂猫时间，请在'.$will.'秒后再来');
        }
        $data = [
            'user_id'=>$user_id,
            'cat_id'=>$cat_id,
            'feed_num'=>$feed_num,
            'ctime'=>time()
        ];
        $ins=Db::name('cat_age')->insert($data);
        if($ins){
            $this->success('喂猫成功');
        }else{
            $this->error('喂猫失败');
        }

    }

    /**

     */
    public function test(){

    }

    /**

     */
    public function test1(){

    }

    /**

     */
    public function test2(){

    }



}