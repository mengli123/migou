<?php
namespace api\shop\controller;

use cmf\controller\RestBaseController;
use think\Db;
use think\Controller;



class CatController extends RestBaseController
{
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
    查询当前猫咪年龄段以及状态
     */
    public function cat_status(){
        $user_id=input('user_id');
        if(!$user_id){
            $this->error('请传入user_id');
        }
        $sel = Db::name('user_cat')->where(['user_id'=>$user_id])->select()->all();
        if($sel){
            $this->success('查询成功',$sel);
        }else{
            $this->error('查询失败',[]);
        }
    }

    /**
    喂猫
     */
    public function feed_cat(){

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