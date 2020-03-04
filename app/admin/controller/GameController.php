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
        $img = $request->param('img');
        $width = $request->param('width');
        $height = $request->param('height');
        $feed_times = $request->param('feed_times');
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
        $data['interval'] = $interval;
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

}