<?php
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\AdminMenuModel;

class GameController extends AdminBaseController
{
    /**
    猫咪列表
     */
	public function cat_list(){
        $cat = Db::name('cat')->select()->all();
        $this->assign("cat",$cat);
        return $this->fetch();
	}


	/**
	添加/修改猫咪列表
     */
	public function cat_add(){
	    $cat_id=input('cat_id');
	    if($cat_id!=''){
	        /**修改猫咪*/
            $cat =Db::name('cat')->where('cat_id',$cat_id)->find();
            $this->assign('cat_id',$cat_id);
            $this->assign('cat',$cat);
        }
	    return $this->fetch();
    }
    /**
    保存猫咪数据
     *
     */
    public function save_cat(){
        $cat_id=input('cat_id');
        $cat=input('cat');
        $logo=input('logo');
        $desc=input('desc');
        if(!$cat){
            $this->error('请输入猫名');
        }
        if(!$desc){
            $this->error('请输入简介');
        }
        if(!$logo){
            $this->error('请上传猫咪头像');
        }
        $data=[
            'cat'=>$cat,
            'logo'=>$logo,
            'desc'=>$desc
        ];
        if($cat_id==''){
            /**添加*/
            $res=Db::name('cat')->insert($data);
        }else{
            /**修改*/
            $res=Db::name('cat')->where('cat_id',$cat_id)->update($data);
        }
        if($res){
            $this->success('保存成功','game/cat_list');
        }else{
            $this->error('保存失败','game/cat_list');
        }
    }
    /**
    删除猫咪
     */
    public function del_cat(){
        $id = $this->request->param('cat_id', 0, 'intval');
        $res=Db::name('cat')->where('cat_id',$id)->delete();
        if($res !== false){
            $this->success("删除成功！");
        }else{
            $this->error("删除失败！");
        }
    }

	/**
	管理阶段猫咪
     */
	public function cat_age(){
	    $cat_id=input('cat_id');
	    $age_id=input('age_id');
	    $cat_age=Db::name('cat_age')->where(['cat_id'=>$cat_id,'age_id'=>$age_id])->find();
	    $cat_age['interval']=json_decode($cat_age['interval']);
	    $this->assign('cat_id',$cat_id);
	    $this->assign('age_id',$age_id);
	    $this->assign('cat_age',$cat_age);
        return $this->fetch();
    }
    /**
    保存阶段猫咪
     */
    public function save_cat_age(){
        $request = request();
        //提取数据
        $cat_id = $request->param('cat_id');
        $age_id = $request->param('age_id');
        $feed_num = $request->param('feed_num');
        $interval = $request->param('interval');
        //dump($interval);exit;
        $img = $request->param('img');
        $width = $request->param('width');
        $height = $request->param('height');
        //$feed_times = $request->param('feed_times');
        $feed_times = count($interval);
        if(!$cat_id){
            $this->error('请传入cat_id');
        }
        if(!$age_id){
            $this->error('请传入age_id');
        }
        if(!$feed_num){
            $this->error('请输入喂食量');
        }
        if( !$interval){
            $this->error('请输入喂食间隔');
        }
        if(!$img){
            $this->error('请上传图片');
        }
        if(!$feed_times){
            $this->error('请输入喂食次数');
        }
        if(!$width){
            $this->error('请输入宽');
        }
        if(!$height){
            $this->error('请输入高');
        }
        $data['cat_id'] = $cat_id;
        $data['age_id'] = $age_id;
        $data['feed_num'] = $feed_num;
        $data['interval'] = json_encode($interval);
        $data['img'] = $img;
        $data['feed_times'] = $feed_times;
        $data['width'] = $width;
        $data['height'] = $height;

        //判断是否存在阶段猫咪
        $is_cat_age = Db::name('cat_age')->where(['cat_id'=>$cat_id,'age_id'=>$age_id])->find();
        if(!$is_cat_age){
           /**
           insert
            */
           $res =Db::name('cat_age')->insert($data);
        }else{
            /**
            update
             */
            $res=Db::name('cat_age')->where(['cat_id'=>$cat_id,'age_id'=>$age_id])->update($data);
        }
        if ($res) {
            $this->success('保存成功', 'game/cat_list');
        }else{
            $this->error('保存失败','game/cat_list');
        }
    }

    /**
    猫咪用户信息列表
     */
    public function info_list(){
        $cat = Db::name('user_cat_info')->select()->all();
        foreach($cat as $k=>$v){
            $cat[$k]['user_name'] =Db::name('user')->where('id',$v['user_id'])->value('user_nickname');
        }
        $this->assign("info",$cat);
        return $this->fetch();
    }

    /**
    修改保存的用户饲料
     */
    public function save_feed_change(){

    }
    /**
    猫咪奖品列表
     */
    public function prize_list(){
        $cat = Db::name('cat_prize')
            ->alias('cp')
            ->join('goods g','g.goods_id=cp.goods_id')
            ->join('goods_specs gs','gs.specs_id=cp.specs_id')
            ->select()->all();

        $this->assign("info",$cat);
        return $this->fetch();
    }

    /**
    增加/修改猫咪奖品页面
     */
    public function prize_add(){
        $id=input('id');
        if($id!=''){
            /**修改猫咪*/
            $prize =Db::name('cat_prize')->where('id',$id)->find();
            $this->assign('id',$id);
            $this->assign('prize',$prize);
        }
        return $this->fetch();
    }
    /**
    保存猫咪奖品
     */
    public function save_prize(){
        $id=input('id');
        $prize=input('prize');
        $img=input('img');
        $num=input('num');
        $specs_id=input('specs_id');
        $goods_id=input('goods_id');
//        if(!$id){
//            $this->error('系统错误');
//        }
        if(!$prize){
            $this->error('请输入奖励名称');
        }
        if(!$img){
            $this->error('请上传奖励图片');
        }
        if(!$num){
            $this->error('请填写碎片数目');
        }
        if(!$goods_id){
            $this->error('请填写商品id');
        }
        $sel=Db::name('goods')->where('goods_id',$goods_id)->select();
        if(count($sel)<1){
            $this->error('请填写存在的商品id');
        }
        if(!$specs_id){
            $this->error('请填写规格ID');
        }
        $data=[
            'prize'=>$prize,
            'img'=>$img,
            'num'=>$num,
            'goods_id'=>$goods_id,
            'specs_id'=>$specs_id,
        ];
        if($id==''){
            /**添加*/
            $res=Db::name('cat_prize')->insert($data);
        }else{
            /**修改*/
            $res=Db::name('cat_prize')->where('id',$id)->update($data);
        }
        if($res){
            $this->success('保存成功','game/prize_list');
        }else{
            $this->error('保存失败','game/prize_list');
        }
    }
    /**
    删除猫咪奖品
     */
    public function del_prize(){
        $id = $this->request->param('id', 0, 'intval');
        $res=Db::name('cat_prize')->where('id',$id)->delete();
        if($res){
            $this->success("删除成功！");
        }else{
            $this->error("删除失败！");
        }
    }

}