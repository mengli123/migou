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

        $list=Db::name('goods_type')->where(['status'=>1])->select();
        if($list){
            $this->success('请求成功!', $list);
        }else{
            $this->error('请求失败');
        }
    }
    /**
    获取首页推荐商品栏目名称
     */
    public function get_recommend_title(){
        $slide_id =2;
        $list = Db::name('slide_item')->where('slide_id',$slide_id)->field('id,title,image')->select();
        if($list){
            $this->success('success',$list);
        }else{
            $this->error('请求失败');
        }
    }

    /**
    获取首页推荐商品栏目名称对应商品
     */
    public function get_recommend_list(){
        $goods_model = new GoodsModel();
        $slide_id =2;
        $list = Db::name('slide_item')->where('slide_id',$slide_id)->field('id,title,image')->select()->all();
        foreach ($list as $k=>$v){
           $goods =$goods_model
                //->with('specs')
                ->where('recommend_id',$v['id'])
                ->limit(9)
                ->field('goods_id,goods_pics')
                ->select()
                ->toArray();
           foreach ($goods as $ke=>$va){
               $goods[$ke]['goods_pics']=json_decode($va['goods_pics'])[0];
           }
            $list[$k]['goods']=$goods;
        }
        //dump($list);
        if($list){
            $this->success('success',$list);
        }else{
            $this->error('请求失败');
        }
    }

    /**
    获取首页推荐商品列表
     */
//    public function get_recommend_list(){
//        $list = Db::name('goods_and_type')
//            ->alias('gt')
//            ->join('goods g','g.goods_id=gt.goods_id')
//            //->join('goods_type t','t.id=gt.type_id')
//            ->where('g.recommend',1)
//            ->order("gt.id DESC")
//            ->select()->all();
//        if($list){
//            foreach ($list as $k=>$v){
//            // echo $v['goods_id'];
//            $list[$k]['price']=Db::name('goods_specs')->where('goods_id',$v['goods_id'])->min('price');
//            }
//            $this->success('请求成功!', $list);
//        }else{
//            $this->error('请求失败');
//        }
//    }
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
       // dump($list);
        if($list){
            foreach ($list as $k=>$v){
                // echo $v['goods_id'];
                $list[$k]['price']=Db::name('goods_specs')->where('goods_id',$v['goods_id'])->min('price');
            }
            $this->success('请求成功!', $list);
        }else{
            $this->error('请求失败',[]);
        }
    }
    /**
    获取团购商品
     */
    public function get_group_list(){
        $list = Db::name('goods_and_type')
            ->alias('gt')
            ->join('goods g','g.goods_id=gt.goods_id')
            ->join('goods_specs s','s.goods_id=gt.goods_id')
            ->where('s.is_group_buying',1)
            ->order("gt.id DESC")
            ->select()->all();
         //dump($list);
        if($list){
            foreach ($list as $k=>$v){
                $list[$k]['price']=Db::name('goods_specs')->where(['goods_id'=>$v['goods_id'],'is_group_buying'=>1])->min('price');
            }
            $this->success('请求成功!', $list);
        }else{
            $this->error('请求失败');
        }
    }

    public function test(){
        $res=Db::name('group_open_log')
            ->where('goods_id',1)
            ->select()
            ->all();
        if(count($res)>1){
            echo 1;
            foreach ($res as $ke=>$va){
                $partner=json_decode($va['partner']);
                $group_data=[];
                $group_data['opener']=Db::name('user')->where('id',$va['user_id'])->field('id,user_nickname,avatar')->find();
                foreach ($partner as $k=>$v){
                    $group_data['partner'][]=Db::name('user')->where('id',$v)->field('id,user_nickname,avatar')->find();
                }
            }
        }else{
            $group_data=[];
        }
        dump($group_data);

    }
    public function make_data(){
        $array=[1,2,3];
        $str=json_encode($array);
        Db::name('group_open_log')->where('group_id',1)->update(['partner'=>$str]);
    }

    /**
     商品详情查询
     */
    public function goods_detail(){
        $goods_model = new GoodsModel();
        $goods_id = input('goods_id');
        $detail = $goods_model->where('goods_id',$goods_id)->with('specs')->find();
        $detail['goods_pics']=json_decode($detail['goods_pics']);
//        $res=Db::name('group_open_log')
//            ->where('good_id',$goods_id)
//            ->select()
//            ->all();
        //dump($detail);

        $res=Db::name('group_open_log')
            ->where('goods_id',$goods_id)
            ->select()
            ->all();
        if(count($res)>1){
            //echo 1;
            $group_data=[];
            foreach ($res as $ke=>$va){
                $partner=json_decode($va['partner']);
                $order_status=Db::name('order')->where(['group_id'=>$va['group_id'],'user_id'=>$va['user_id']])->value('status');
                $group_data[$ke]['group_id']=$va['group_id'];
                $group_data[$ke]['opener']=Db::name('user')->where('id',$va['user_id'])->field('id,user_nickname,avatar')->find();
                if($order_status==1){
                    //dump($va);
                    $group_data[$ke]['opener_is_pay']=1;
                    if($partner){
                        foreach ($partner as $k=>$v){
                            $order_status=Db::name('order')->where(['group_id'=>$va['group_id'],'user_id'=>$v])->value('status');
                            $group_data[$ke]['partner'][] = Db::name('user')->where('id', $v)->field('id,user_nickname,avatar')->find();
//                            if($order_status==1) {
//                                $group_data[$ke]['partner']['partner_is_pay']=1;
//                            }else{
//                                $group_data[$ke]['partner']['partner_is_pay']=0;
//                            }
                        }
                    }else{
                        $group_data[$ke]['partner']=[];
                    }
                }else{
                    $group_data[$ke]['opener_is_pay']=0;
                }
            }

//            foreach ($res as $ke=>$va){
//                $partner=json_decode($va['partner']);
//                $order_status=Db::name('order')->where(['group_id'=>$va['group_id'],'user_id'=>$va['user_id']])->value('status');
//                if($order_status==1){
//                    //dump($va);
//                    $group_data[$ke]['group_id']=$va['group_id'];
//                    $group_data[$ke]['opener']=Db::name('user')->where('id',$va['user_id'])->field('id,user_nickname,avatar')->find();
//                    if($partner){
//                        foreach ($partner as $k=>$v){
//                            $order_status=Db::name('order')->where(['group_id'=>$va['group_id'],'user_id'=>$v])->value('status');
//                            if($order_status==1) {
//                                $group_data[$ke]['partner'][] = Db::name('user')->where('id', $v)->field('id,user_nickname,avatar')->find();
//                            }
//                        }
//                    }else{
//                        $group_data[$ke]['partner']=[];
//                    }
//                }
//            }

//            foreach ($res as $ke=>$va){
//                $partner=json_decode($va['partner']);
//                $order_status=Db::name('order')->where(['group_id'=>$va['group_id'],'user_id'=>$va['user_id']])->value('status');
//                $a=[];
//                if($order_status==1){
//                    //dump($va);
//
//                    $a['group_id']=$va['group_id'];
//                    $a['opener']=Db::name('user')->where('id',$va['user_id'])->field('id,user_nickname,avatar')->find();
//                    if($partner){
//                        foreach ($partner as $k=>$v){
//                            $order_status=Db::name('order')->where(['group_id'=>$va['group_id'],'user_id'=>$v])->value('status');
//                            if($order_status==1) {
//                                $a['partner'][] = Db::name('user')->where('id', $v)->field('id,user_nickname,avatar')->find();
//                            }
//                        }
//                    }else{
//                        $a['partner']=[];
//                    }
//                }else{
//                    $a[]=[];
//                }
//                $group_data[]=$a;
         //   }
        }else{
            $group_data=[];
        }
        //dump($group_data);
        if($detail){
            $detail['group_data']=$group_data;
            $this->success('请求成功!', $detail);
        }else{
            $this->error('请求失败');
        }
    }

    /**
    测试
     */
    public function index()
    {
       $typs_names= get_type(30);
       dump($typs_names);
        //$this->success('请求成功!', 1);
//        $data=['admin/20200221/b0203fae9bb10e3a4176753abe05e0d4.jpg','admin/20200221/b0203fae9bb10e3a4176753abe05e0d4.jpg'];
//        $ins=Db::name('goods')->where('goods_id',1)->update(['goods_pics'=>json_encode($data)]);
//        dump($ins);
//        $list=1;
//        if(!$list){
//            $this->success('请求成功!', $list);
//        }else{
//            $this->error('请求失败');
//        }
    }
    //获取当前版本
    public function get_now_version(){
        $data=Db::name('app_version')->where('status',1)->find();
        $data['version']=$data['version'].$data['id'];
        $this->success('当前版本',$data);
    }
}
