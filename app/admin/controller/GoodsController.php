<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\AdminMenuModel;

class GoodsController extends AdminBaseController
{

	
	public function goods_list()
	{		
		if($_REQUEST){
            $request = request();
            /**搜索条件**/
            $keyword = trim($request->param('keyword'));
            if ($keyword) {
                 $where['goods_name|price'] =  ['like', '%' . $keyword . '%'];
            }
            $start=$request->param('start_time');
            $end=$request->param('end_time');
            if ($start&&$end) {
                if ($start>=$end) {
                    $this->error("开始时间不能大于或等于结束时间");exit();
                }
                $where['create_time'] = array(array('gt',strtotime($start)),array('lt',strtotime($end)));
            }else if($start&&!$end){
                $where['create_time'] = array('gt',strtotime($start));
            }else if(!$start&&$end){
                $where['create_time'] = array('lt',strtotime($end));
            }
            if (empty($where)) {
                $where=1;
            }
        }else{
            $where=1;
        }
        $goods = Db::name('goods')
            ->alias("g")
//            ->join("goods_and_type gt","g.goods_id=gt.goods_id")
//            ->join("goods_type t","t.goods_id=gt.goods_id")
            //->field("g.*,c.cat_name")
            ->where($where)
            ->order("goods_id DESC")
            ->paginate(10);
		//dump($goods);exit;
        $this->assign("goods",$goods);
        $goods->appends($request->param());
        $this->assign('page', $goods->render());
        return $this->fetch();
	}

    public function add()
    {
        $cat=Db::name("goods_type")->select();
        $this->assign("type",$cat);
        return $this->fetch();
    }

    public function addPost()
    {
        $request = request();
        //提取数据
        $goods_name = $request->param('goods_name');
        $goods_dsc = $request->param('goods_dsc');
        $goods_pics=$request->param('goods_pics');
        $type_id=$request->param('type_id');
        $supplier = $request->param('supplier');
        $goods_area = $request->param('goods_area');
        $status = $request->param('status');
        $recommend_id = $request->param('recommend_id');

//        if(strlen($goods_name)>50){
//            $this->error('产品超过限制长度50');
//        }
        if (empty($goods_name)) {
            $this->error('请填写产品名');exit;
        }
        if(strlen($goods_dsc)>200){
            $this->error('产品详情超过限制长度200');
        }
        if (!$goods_pics) {
            $this->error('请至少上传一张图片');exit;
        }
        if (count($type_id)<1) {
             $this->error('请至少选择一个分类');exit;
        }
        if (empty($supplier)) {
            $this->error('请填写供货商');exit;
        }
        if (empty($goods_area)) {
            $this->error('请填写发货地');exit;
        }

        //判断是否存在该用户名和手机号
        $is_name = Db::name('goods')->where('goods_name',$goods_name)->find();
        if($is_name){
            $this->error('该产品名已存在');exit;
        }
        //封装数据
        $data['goods_name'] = $goods_name;
        $data['goods_dsc'] = $goods_dsc;
        $data['goods_area'] = $goods_area;
        $data['create_time'] = time();
        $data['supplier'] = $supplier;
        $data['status'] = $status;
        $data['recommend_id'] = $recommend_id;
        $data['goods_pics'] = json_encode($goods_pics);

        $goods_id = Db::name('goods')->insertGetId($data);
        if ($goods_id) {
             //写入商品分类表
            foreach ($type_id as $k=>$v){
                Db::name('goods_and_type')->insert(['goods_id'=>$goods_id,'type_id'=>$v]);
            }
             $this->success('新增产品成功', 'goods/goods_list');
        }else{
            $this->error('新增产品失败');
        }
            
    }
/** 修改商品*/
    public function edit(){
        $request = request();
        $id = $request->param('id');
        $goods = Db::name('goods')->where('goods_id',$id)->find();
        $goods['goods_pics']=json_decode($goods['goods_pics']);
       // dump($goods['goods_pics']);
        $cat=Db::name('goods_type')->select();
        $type=Db::name("goods_and_type")->where('goods_id',$id)->column('type_id');
        $this->assign("type",$type);
        $this->assign("cat",$cat);
        $this->assign("goods_id",$id);
        $this->assign("goods",$goods);
        return $this->fetch();
    }
/** 保存修改商品*/
    public function editPost()
    {
        $request = request();
        $goods_id =$request->param('goods_id');
        //提取数据
        $goods_name = $request->param('goods_name');
        $goods_dsc = $request->param('goods_dsc');
        $goods_pics=$request->param('goods_pics');
        $type_id=$request->param('type_id');
        $supplier = $request->param('supplier');
        $goods_area = $request->param('goods_area');
        $status = $request->param('status');
        $recommend_id = $request->param('recommend_id');

        if(!$goods_id){
            $this->error('系统错误');
        }

//        if(strlen($goods_name)>50){
//            $this->error('产品超过限制长度50');
//        }
        if (empty($goods_name)) {
            $this->error('请填写产品名');exit;
        }
        if(strlen($goods_dsc)>200){
            $this->error('产品详情超过限制长度200');
        }
        if (!$goods_pics) {
            $this->error('请至少上传一张图片');exit;
        }
        if (count($type_id)<1) {
            $this->error('请至少选择一个分类');exit;
        }
        if (empty($supplier)) {
            $this->error('请填写供货商');exit;
        }
        if (empty($goods_area)) {
            $this->error('请填写发货地');exit;
        }

        //判断是否存在该用户名和手机号
//        $is_name = Db::name('goods')->where('goods_name',$goods_name)->find();
//        if($is_name){
//            $this->error('该产品名已存在');exit;
//        }
        //封装数据
        $data['goods_name'] = $goods_name;
        $data['goods_dsc'] = $goods_dsc;
        $data['goods_area'] = $goods_area;
        $data['create_time'] = time();
        $data['supplier'] = $supplier;
        $data['status'] = $status;
        $data['recommend_id'] = $recommend_id;
        if(count($goods_pics)>0){
            $data['goods_pics'] = json_encode($goods_pics);

        }
        $update = Db::name('goods')->where('goods_id',$goods_id)->update($data);
        if ($update) {
            if(count($type_id)>0){
                Db::name('goods_and_type')->where('goods_id',$goods_id)->delete();
                //写入商品分类表
                foreach ($type_id as $k=>$v){
                    Db::name('goods_and_type')->insert(['goods_id'=>$goods_id,'type_id'=>$v]);
                }
            }

            $this->success('修改产品成功', 'goods/goods_list');
        }else{
            $this->error('修改产品失败');
        }
            
    }

	//删除
	public function delete(){
		$id = $this->request->param('id', 0, 'intval');
		$res=Db::name('goods')->where('goods_id',$id)->delete();
		if($res !== false){
		    Db::name('goods_and_type')->where('goods_id',$id)->delete();
		    Db::name('goods_specs')->where('goods_id',$id)->delete();
			$this->success("删除成功！");
		}else{
			$this->error("删除失败！");
		}
	}

	public function goods_specs(){
        $goods_id      = $this->request->param('goods_id', 0, 'intval');
        $result  = Db::name('goods_specs')->where('goods_id', $goods_id)->select()->all();
        //dump($result);
        $this->assign('goods_id', $goods_id);
        $this->assign('result', $result);
        $this->assign('status', ['否','是']);
        return $this->fetch();
    }

    public function add_specs(){
	    $goods_id = input('goods_id');
	    $this->assign('goods_id',$goods_id);
        return $this->fetch();
    }
    public function save_specs(){
	    //$data=input();
        $request = request();
        //提取数据
        $goods_id = $request->param('goods_id');
        $price = $request->param('price');
        $pic = $request->param('pic');
        $size  = $request->param('size');
        $is_group_buying = $request->param('is_group_buying');
        $all_current_count = $request->param('all_current_count');
        $all_sale_count = $request->param('all_sale_count');
        $group_max_count= $request->param('group_max_count');
        $group_current_count= $request->param('group_current_count');
        $group_price = $request->param('group_price');
        $min_count = $request->param('min_count');
        $return_rate = $request->param('return_rate');
//        if(strlen($size)>30){
//            $this->error('产品规格超过限制长度30');
//        }
        if (empty($goods_id)) {
            $this->error('商品id缺失');exit;
        }
        if ($price<0) {
             $this->error('价格不能低于0');exit;
        }
        if ($all_current_count<0) {
            $this->error('库存不能低于0');exit;
        }
        if ($all_sale_count<0) {
            $this->error('销售总量低于0');exit;
        }
        if (!$pic) {
            $this->error('请上传规格图片');exit;
        }
        if($all_current_count<$group_max_count){
            $this->error('最大团购量不能大于当前库存');exit;
        }
        if($group_max_count<0){
            $this->error('最大团购量不能低于0');exit;
        }
        if ($group_price<0) {
            $this->error('团购价格不能低于0');exit;
        }
        if ($min_count<2) {
            $this->error('团购价格不能低于2人');exit;
        }
        if (!$return_rate>0.5) {
            $this->error('团长反利率不能大于50%');exit;
        }

        //判断是否存在该规格
        $is_size = Db::name('goods_specs')->where(['size'=>$size,'goods_id'=>$goods_id])->find();
        if($is_size){
            $this->error('该商品已存在此规格');exit;
        }

        //封装数据
        $data=[];
        $data['goods_id'] = $goods_id;
        $data['price'] = $price;
        $data['pic'] = $pic;
        $data['size'] = $size;
        $data['all_current_count'] = $all_current_count;
        $data['all_sale_count'] = $all_sale_count;
        $data['is_group_buying'] = $is_group_buying;
        $data['group_max_count']=$group_max_count;
        $data['group_current_count']=$group_current_count;
        $data['group_price']=$group_price;
        $data['min_count']=$min_count;
        $data['return_rate']=$return_rate;

        if (Db::name('goods_specs')->insert($data)) {
            $this->success('新增商品规格成功', 'goods/goods_specs?goods_id='.$goods_id);
        }else{
            $this->error('新增商品规格失败');
        }
    }
    public function edit_specs(){
        $specs_id = input('specs_id');
        $specs = Db::name('goods_specs')->where('specs_id',$specs_id)->find();
        $this->assign('specs',$specs);
        $this->assign('specs_id',$specs_id);

        return $this->fetch();
    }

    public function save_edit_specs(){
        $request = request();
        //提取数据
        $goods_id = $request->param('goods_id');
        $specs_id =$request->param('specs_id');
        $price = $request->param('price');
        $pic = $request->param('pic');
        $size  = $request->param('size');
        $is_group_buying = $request->param('is_group_buying');
        $all_current_count = $request->param('all_current_count');
        $all_sale_count = $request->param('all_sale_count');
        $group_max_count= $request->param('group_max_count');
        $group_current_count= $request->param('group_current_count');
        $group_price = $request->param('group_price');
        $min_count = $request->param('min_count');
        $return_rate = $request->param('return_rate');
        if(strlen($size)>30){
            $this->error('产品规格超过限制长度30');
        }
        if (empty($specs_id)) {
            $this->error('商品规格id缺失');exit;
        }
        if ($price<0) {
            $this->error('价格不能低于0');exit;
        }
        if ($all_current_count<0) {
            $this->error('库存不能低于0');exit;
        }
        if ($all_sale_count<0) {
            $this->error('销售总量低于0');exit;
        }
        if (!$pic) {
            $this->error('请上传规格图片');exit;
        }
        if($all_current_count<$group_max_count){
            $this->error('最大团购量不能大于当前库存');exit;
        }
        if($group_max_count<0){
            $this->error('最大团购量不能低于0');exit;
        }
        if ($group_price<0) {
            $this->error('团购价格不能低于0');exit;
        }
        if ($min_count<2) {
            $this->error('团购价格不能低于2人');exit;
        }
        if (!$return_rate>0.5) {
            $this->error('团长反利率不能大于50%');exit;
        }

        //判断是否存在该规格
//        $is_size = Db::name('goods_specs')->where(['size'=>$size,'goods_id'=>$goods_id])->find();
//        if($is_size){
//            $this->error('该商品已存在此规格');exit;
//        }

        //封装数据
        $data=[];
        $data['specs_id'] = $specs_id;
        $data['price'] = $price;
        $data['pic'] = $pic;
        $data['size'] = $size;
        $data['all_current_count'] = $all_current_count;
        $data['all_sale_count'] = $all_sale_count;
        $data['is_group_buying'] = $is_group_buying;
        $data['group_max_count']=$group_max_count;
        $data['group_current_count']=$group_current_count;
        $data['group_price']=$group_price;
        $data['min_count']=$min_count;
        $data['return_rate']=$return_rate;

        if (Db::name('goods_specs')->where('specs_id',$specs_id)->update($data)) {
            $this->success('修改商品规格成功', 'goods/goods_specs?goods_id='.$goods_id);
        }else{
            $this->error('修改商品规格失败');
        }
    }


    public function del_specs(){
        $id = $this->request->param('specs_id', 0, 'intval');
        $res=Db::name('goods_specs')->where('id',$id)->delete();
        if($res !== false){
            $this->success("删除成功！");
        }else{
            $this->error("删除失败！");
        }
    }
    /**
    积分规则
     */
    public function point_rule(){
        $rule =Db::name('point_rule')->find();
        $this->assign('rule',$rule);
        return $this->fetch();
    }
    /**
     保存积分修改规则
    */
    public function save_rule_change(){
        $id=input('id');
        $money= input('money');
        $update= Db::name('point_rule')->where('id',$id)->update(['money'=>$money]);
        if($update){
        $this->success("已修改！");
    }else{
            $this->error("未修改！");
        }
    }


    public function goods_category(){
        $cat=Db::name("goods_type")->select();
        $cat=json_decode($cat,true);
        $this->assign("cat",$cat);
        return $this->fetch();
    }

    public function add_cat()
    {
        return $this->fetch();
    }

    public function addcatPost()
    {
        $request = request();
        //提取数据
        $cat_name = $request->param('cat_name');
        //$sort = $request->param('sort');
        $is_show = $request->param('status');
        $img = $request->file('img');
        //$img2 = $request->file('img2');

        if ($cat_name=="") {
             $this->error('请填写分类名');exit;
        }
//
//        if ($sort<0) {
//             $this->error('请填写排序');exit;
//        }

        $list=Db::name("goods_type")->where(array("type_name"=>$cat_name))->find();
        if (!empty($list)) {
            $this->error("分类名已存在");
        }

        //如果头像不存在
        if(!$img){
             $this->error("请上传图片");
        }else{
        //头像存在
            $info=$img->move(ROOT_PATH.'public'.DS.'uploads'.DS.'img');
            $path=$info->getSavename();
            $data['url']='\uploads\img'.DS.$path;
        }

        //如果头像不存在
//        if(!$img2){
//            $this->error("请上传图片");
//        }else{
//            //头像存在
//            $info=$img2->move(ROOT_PATH.'public'.DS.'uploads'.DS.'img');
//            $path=$info->getSavename();
//            $data['img'][]='\uploads\img'.DS.$path;
//        }
        //$data['img']=json_encode($data['img']);
        //封装数据
        $data['type_name'] = $cat_name;
        //$data['sort'] = $sort;
        $data['status'] = $is_show;
       
        if (Db::name('goods_type')->insert($data)) {
            $this->success('新增分类成功', 'goods/goods_category');
        }else{
            $this->error('新增分类失败');
        }
            
    }

    public function edit_cat()
    {
        $request = request();
        $id = $request->param('id');
        $cat = Db::name('goods_type')->where('id',$id)->find();
        //$cat['url']=json_decode($cat['url']);
        $this->assign("cat",$cat);
        return $this->fetch();
    }

    public function editcatPost()
    {
        $request = request();
        //提取数据
        $id=$request->param('id');
        $cat_name = $request->param('type_name');
        //$sort = $request->param('sort');
        $is_show = $request->param('status');
        $img = $request->file('img');
        //$img2 = $request->file('img2');
        if (!$id) {
             $this->error('系统错误');
        }
        if (!$cat_name) {
            $this->error('请填写分类名');
        }


        //如果头像不存在
        if($img){
            $info=$img->move(ROOT_PATH.'public'.DS.'uploads'.DS.'img');
            $path=$info->getSavename();
            $data['url']='\uploads\img'.DS.$path;
        }

        $data['type_name'] = $cat_name;
        $data['status'] = $is_show;

        if (Db::name('goods_type')->where("id",$id)->update($data)) {
        $this->success('编辑分类成功', 'goods/goods_category');
        }else{
            $this->error('未修改');
        }
    }

    public function delete_type(){
        $id = $this->request->param('id', 0, 'intval');
        $res=Db::name('goods_type')->where('id',$id)->delete();
        if($res !== false){
            $this->success("删除成功！");
        }else{
            $this->error("删除失败！");
        }
    }


	public function export(){
        //header("Content-type: text/html; charset=utf-8"); 
        $request = request();
        /**搜索条件**/
        $keyword = trim($request->param('keyword'));
        if ($keyword) {
             $where['goods_name|price'] =  ['like', '%' . $keyword . '%'];
        }
        $start=$request->param('start_time');
        $end=$request->param('end_time');
        if ($start&&$end) {
            if ($start>=$end) {
                $this->error("开始时间不能大于或等于结束时间");exit();
            }
            $where['time'] = array(array('gt',strtotime($start)),array('lt',strtotime($end)));
        }else if($start&&!$end){
            $where['time'] = array('gt',strtotime($start));
        }else if(!$start&&$end){
            $where['time'] = array('lt',strtotime($end));
        }
        if (empty($where)) {
            $where=1;
        }
        $goods = Db::name('goods')
            ->where($where)
            ->order("goods_id DESC")
            ->select();
        
        $data=$goods->all();
        if (empty($data)) {
            $this->error("没有订单数据");
        }
        $str="ID,产品名,价格/斤,时间\n"; 

        $str = iconv('utf-8','gbk',$str); 
        foreach ($data as $key => $row) {
            #ID
            $goods_id = iconv('utf-8','gbk',"\t".$row['goods_id']);
            $str .= $goods_id.",";
           
            #产品名
            $goods_name = iconv('utf-8','gbk',"\t".$row['goods_name']);
            $str .= $goods_name.",";

             #价格/斤
            $price = iconv('utf-8','gbk',"\t".$row['price']);
            $str .= $price.",";

            #完成时间
            $time = iconv('utf-8','gbk',"\t".date("Y-m-d H:i:s",$row['time']));
            $str .= $time.",";
            $str .=" \n";
        
        }
       
        $str.="\n";
        $filename = 'goods_'.date('Y-m-d H:i:s').'.csv'; //设置文件名
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".str_replace(' ','',$filename));
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;

        //写入系统日志
         $log['log_time']=time();
         $log['user_id']=session("ADMIN_ID");//操作人ID
         $log['name']=session("name");//操作人
         $log['info']="管理员".session("name")."于".date("Y-m-d H:i:s")."导出产品数据";
         Db::name('system_log')->insert($log);
        //$this->success('导出成功', 'users/index');
        exit;
    }


    public function total(){

        if($_REQUEST){
            $request = request();
            /**搜索条件**/
            $keyword = trim($request->param('keyword'));
            if ($keyword) {
                 $where['g.goods_name|g.price'] =  ['like', '%' . $keyword . '%'];
            }
            $start=$request->param('start_time');
            $end=$request->param('end_time');
            if ($start&&$end) {
                if ($start>=$end) {
                    $this->error("开始时间不能大于或等于结束时间");exit();
                }
                $where['g.time'] = array(array('gt',strtotime($start)),array('lt',strtotime($end)));
            }else if($start&&!$end){
                $where['g.time'] = array('gt',strtotime($start));
            }else if(!$start&&$end){
                $where['g.time'] = array('lt',strtotime($end));
            }
            if (empty($where)) {
                $where=1;
            }
        }else{
            $where=1;
        }

        if ($keyword) {
            session("keyword",$keyword);
        }else{
            session("keyword",null);
        }
        $goods = Db::name('goods')
            ->alias("g")
            ->join("goods_category c","c.cat_id=g.cat_id")
            ->field("g.*,c.cat_name")
            ->where($where)
            ->order("g.goods_id DESC")
            ->paginate(10);
        
        $goods->appends($request->param());
        $this->assign('page', $goods->render());
        $goods=$goods->all();

        foreach ($goods as $k => $v) {
            //今日
            $now_start=mktime(0,0,0,date('m'),date('d'),date('Y'));//今日开始时间
            $now_end=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;//今日结束时间
            $maps1['o.add_time'] = array(array('gt',$now_start),array('lt',$now_end));
            

             //本周
            $week_start=mktime(0,0,0,date('m'),date('d')-date('w'),date('Y'));
            $week_end=mktime(23,59,59,date('m'),date('d')-date('w')+6,date('Y'));
            $maps2['add_time'] = array(array('gt',$week_start),array('lt',$week_end));
         
             //本月
            $month_start=mktime(0,0,0,date('m'),1,date('Y'));  
            $month_end=mktime(23,59,59,date('m'),date('t'),date('Y'));  
            $maps3['add_time'] = array(array('gt',$month_start),array('lt',$month_end));
           
             //今年
            $year_start=mktime(0,0,0,1,1,date('Y'));//今日开始时间
            $year_end=mktime(23,59,59,12,31,date('Y'));//今日结束时间
            $maps4['add_time'] = array(array('gt',$year_start),array('lt',$year_end));
           

            $goods[$k]['now']=Db::name("order")->alias("o")->join("order_goods og","o.order_id=og.order_id")->where($maps1)->where("o.order_status",3)->where("og.goods_id",$v['goods_id'])->sum("og.weight");

            $goods[$k]['week']=Db::name("order_goods")->alias("og")->join("order o","o.order_id=og.order_id")->where($maps2)->where("o.order_status",3)->where("og.goods_id",$v['goods_id'])->sum("og.weight");

            $goods[$k]['month']=Db::name("order_goods")->alias("og")->join("order o","o.order_id=og.order_id")->where($maps3)->where("o.order_status",3)->where("og.goods_id",$v['goods_id'])->sum("og.weight");
            

            $goods[$k]['year']=Db::name("order_goods")->alias("og")->join("order o","o.order_id=og.order_id")->where($maps4)->where("o.order_status",3)->where("og.goods_id",$v['goods_id'])->sum("og.weight");
                
            
        }
        $this->assign("keyword",session("keyword"));
        $this->assign("goods",$goods);
        return $this->fetch();
    }
    
    public function export_total(){
        if($_REQUEST){
            $request = request();
            /**搜索条件**/
            $keyword = trim($request->param('keyword'));
            if ($keyword) {
                 $where['g.goods_name|g.price'] =  ['like', '%' . $keyword . '%'];
            }
            $start=$request->param('start_time');
            $end=$request->param('end_time');
            if ($start&&$end) {
                if ($start>=$end) {
                    $this->error("开始时间不能大于或等于结束时间");exit();
                }
                $where['g.time'] = array(array('gt',strtotime($start)),array('lt',strtotime($end)));
            }else if($start&&!$end){
                $where['g.time'] = array('gt',strtotime($start));
            }else if(!$start&&$end){
                $where['g.time'] = array('lt',strtotime($end));
            }
            if (empty($where)) {
                $where=1;
            }
        }else{
            $where=1;
        }

        
        $goods = Db::name('goods')
            ->alias("g")
            ->join("goods_category c","c.cat_id=g.cat_id")
            ->field("g.*,c.cat_name")
            ->where($where)
            ->order("g.goods_id DESC")
            ->paginate(10);
        

        $goods=$goods->all();

        foreach ($goods as $k => $v) {
            //今日
            $now_start=mktime(0,0,0,date('m'),date('d'),date('Y'));//今日开始时间
            $now_end=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;//今日结束时间
            $maps1['o.add_time'] = array(array('gt',$now_start),array('lt',$now_end));
            

             //本周
            $week_start=mktime(0,0,0,date('m'),date('d')-date('w'),date('Y'));
            $week_end=mktime(23,59,59,date('m'),date('d')-date('w')+6,date('Y'));
            $maps2['add_time'] = array(array('gt',$week_start),array('lt',$week_end));
         
             //本月
            $month_start=mktime(0,0,0,date('m'),1,date('Y'));  
            $month_end=mktime(23,59,59,date('m'),date('t'),date('Y'));  
            $maps3['add_time'] = array(array('gt',$month_start),array('lt',$month_end));
           
             //今年
            $year_start=mktime(0,0,0,1,1,date('Y'));//今日开始时间
            $year_end=mktime(23,59,59,12,31,date('Y'));//今日结束时间
            $maps4['add_time'] = array(array('gt',$year_start),array('lt',$year_end));
           

            $goods[$k]['now']=Db::name("order")->alias("o")->join("order_goods og","o.order_id=og.order_id")->where($maps1)->where("o.order_status",3)->where("og.goods_id",$v['goods_id'])->sum("og.weight");

            $goods[$k]['week']=Db::name("order_goods")->alias("og")->join("order o","o.order_id=og.order_id")->where($maps2)->where("o.order_status",3)->where("og.goods_id",$v['goods_id'])->sum("og.weight");

            $goods[$k]['admin_month']=Db::name("order_goods")->alias("og")->join("order o","o.order_id=og.order_id")->where($maps3)->where("o.order_status",3)->where("og.goods_id",$v['goods_id'])->sum("og.weight");
            

            $goods[$k]['year']=Db::name("order_goods")->alias("og")->join("order o","o.order_id=og.order_id")->where($maps4)->where("o.order_status",3)->where("og.goods_id",$v['goods_id'])->sum("og.weight");
                
            
        }
        
     
        if (empty($goods)) {
            $this->error("没有订单数据");
        }
        header("charset=utf-8");
        ini_set ('memory_limit', '128M');
        error_reporting(E_ALL);
        import("phpexcel.PHPExcel",dirname(__FILE__),".php");
        vendor("Classes.PHPExcel");
        vendor("Classes.PHPExcel.Writer.IWriter");
        vendor("Classes.PHPExcel.Writer.Abstract");
        vendor("Classes.PHPExcel.Writer.Excel5");
        vendor("Classes.PHPExcel.Writer.Excel2007");
        vendor("Classes.PHPExcel.IOFactory");
        $objPHPExcel = new \PHPExcel();
        // Set document properties
        $objPHPExcel->getProperties()->setCreator("aigindustries")//创建者
        ->setLastModifiedBy("aigindustries")//最后修改者
        ->setTitle("aigindustries")//标题
        ->setSubject("aigindustries")//主题
        ->setDescription("aigindustries")//备注
        ->setKeywords("aigindustries")//关键字
        ->setCategory("aigindustries");//分类
        // $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(60);
        
        
        $objPHPExcel->setActiveSheetIndex(0)
            //设置表的名称标题
            ->setCellValue('A1',"ID")
            ->setCellValue('B1',"产品名")
            ->setCellValue('C1',"价格/公斤")
            ->setCellValue('D1',"今日")
            ->setCellValue('E1',"本周")
            ->setCellValue('F1',"本月")
            ->setCellValue('G1',"今年");
        foreach($goods as $k => $v)
        {
            #审核状态
           
            $status="成功";
            
            $num=$k+2;
            $objPHPExcel->setActiveSheetIndex(0)
                //Excel的第A列，uid是你查出数组的键值，下面以此类推
                ->setCellValue('A'.$num, $v['goods_id'])
                ->setCellValue('B'.$num, $v['goods_name'])
                ->setCellValue('C'.$num, $v['price'])
                ->setCellValue('D'.$num, "\t".$v['now']."公斤\t")
                ->setCellValue('E'.$num, "\t".$v['week']."公斤\t")
                ->setCellValue('F'.$num, "\t".$v['admin_month']."公斤\t")
                ->setCellValue('G'.$num, "\t".$v['year']."公斤\t");
        }
         // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('统计信息');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel;charset=utf-8');
        header('Content-Disposition: attachment;filename="'.date('YmdHi').'统计信息.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');exit;
    }

}
