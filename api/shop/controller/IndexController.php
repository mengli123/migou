<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------

namespace api\shop\controller;

//use cmf\controller\RestBaseController;
use think\Db;
use think\Controller;

class IndexController extends Controller
{
    /** 获取主轮播图*/
	public function get_slide(){
	    $res=[];
        try {
            $list=Db::name('slide_item')->where(['slide_id'=>1,'status'=>1])->field('title,image,url')->select();
            // throw new \Exception("自定义错误");
            $res['code']=1;
            $res['msg']='success';
            $res['data']=$list;

        } catch (\Exception $e) {
            $res['code']=0;
            $res['msg']=$e->getMessage();
        }
        return json($res);
	}
    /** 获取所有商品分类*/     
    public function get_type_list(){
        $res=[];
        try {
            $list=Db::name('goods_type')->where(['status'=>1])->select();
            // throw new \Exception("自定义错误");
            $res['code']=1;
            $res['msg']='success';
            $res['data']=$list;

        } catch (\Exception $e) {
            $res['code']=0;
            $res['msg']=$e->getMessage();
        }
        return json($res);


//        if($list){
//            $this->success('请求成功!', $list);
//        }else{
//            $this->error('请求失败');
//        }
    }
    public function index()
    {

        $this->success('请求成功!', ['test'=>'test']);
    }
}
