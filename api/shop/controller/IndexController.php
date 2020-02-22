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
use think\Controller;
use app\admin\model\GoodsAndTypeModel;
use app\admin\model\GoodsModel;


class IndexController extends RestBaseController
{
    /** 获取主轮播图*/
	public function get_slide(){
//	    $res=[];
//        try {
//            $list=Db::name('slide_item')->where(['slide_id'=>1,'status'=>1])->field('title,image,url')->select();
//            // throw new \Exception("自定义错误");
//            $res['code']=1;
//            $res['msg']='success';
//            $res['data']=$list;
//
//        } catch (\Exception $e) {
//            $res['code']=0;
//            $res['msg']=$e->getMessage();
//        }
//        return json($res);
        $list=Db::name('slide_item')->where(['slide_id'=>1,'status'=>1])->field('title,image,url')->select();
        if($list){
            $this->success('请求成功!', $list);
        }else{
            $this->error('请求失败');
        }
	}
    /** 获取所有商品分类*/     
    public function get_type_list(){
//        $res=[];
//        try {
//            $list=Db::name('goods_type')->where(['status'=>1])->select();
//            // throw new \Exception("自定义错误");
//            $res['code']=1;
//            $res['msg']='success';
//            $res['data']=$list;
//
//        } catch (\Exception $e) {
//            $res['code']=0;
//            $res['msg']=$e->getMessage();
//        }
//        return json($res);

        $list=Db::name('goods_type')->where(['status'=>1])->select();
        if($list){
            $this->success('请求成功!', $list);
        }else{
            $this->error('请求失败');
        }
    }
    public function get_goods_list(){
        $goods_and_type =new GoodsAndTypeModel();
        $type_id=input('type_id');
        if(!$type_id){
            $this->error('分类id参数缺失');
        }
        $list=$goods_and_type->where(['type_id'=>$type_id])
            ->with(['goods','type_name'])
            ->select();
        if($list){
            $this->success('success',$list);
        }else{
            $this->error('请求失败');
        }
    }
    /**
     * 根据关键词或者分类名称返回列表
     */
    public function goods_list(){
        $key = input('keyword');
        $list = Db::name('goods_and_type')
            ->alias('gt')
            ->join('goods g','g.goods_id=gt.goods_id')
            ->join('goods_type t','t.id=gt.type_id')
            ->where('g.goods_name|t.type_name','like','%' . $key. '%')
            ->order("gt.id DESC")
            ->select()->all();
        foreach ($list as $k=>$v){
           // echo $v['goods_id'];
            $list[$k]['price']=Db::name('goods_specs')->where('goods_id',$v['goods_id'])->min('price');
        }
       // dump($list);
        if($list){
            $this->success('请求成功!', $list);
        }else{
            $this->error('请求失败');
        }
    }
    /**
     商品详情查询
     */
    public function goods_detail(){
        $goods_model = new GoodsModel();
        $goods_id = input('goods_id');
        $detail = $goods_model->where('goods_id',$goods_id)->with('specs')->find();
        //dump($detail);
        if($detail){
            $this->success('请求成功!', $detail);
        }else{
            $this->error('请求失败');
        }
    }
    public function index()
    {
        //$this->success('请求成功!', 1);
        $list=1;
        if(!$list){
            $this->success('请求成功!', $list);
        }else{
            $this->error('请求失败');
        }
    }
}
