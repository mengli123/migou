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

class IndexController extends RestBaseController
{
    /** 获取主轮播图*/
	public function get_slide(){
		$list=Db::name('slide_item')->where(['slide_id'=>1,'status'=>1])->field('title,image,url')->select();
		if($list){
            $this->success('请求成功!', $list);
        }else{
		    $this->error('请求失败');
        }
	}
    public function index()
    {
        
        $this->success('请求成功!', ['test'=>'test']);
    }
}
