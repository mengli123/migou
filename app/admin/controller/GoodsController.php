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
    public function index(){
        echo 123;
    }
    public function goods_list(){
        echo 123;
    }
    
    public function type_list(){
        echo 123;
    }
    
	
	public function goods_lists()
	{		
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
        $this->assign("goods",$goods);
        $goods->appends($request->param());
        $this->assign('page', $goods->render());
        return $this->fetch();
	}

    public function add()
    {
        $cat=Db::name("goods_category")->order("cat_id desc")->select();
        $this->assign("cat",$cat);
        return $this->fetch();
    }

    public function addPost()
    {
        $request = request();
        //提取数据
        $goods_name = $request->param('goods_name');
        $price = $request->param('price');
        $commission = $request->param('commission');
        $cat_id = $request->param('cat_id');
        $ulen = strlen($goods_name);
        if($ulen>100){
            $this->error('产品超过限制长度100');
        }
        if (empty($goods_name)) {
            $this->error('请填写产品名');exit;
        }
//        if ($price<0) {
//             $this->error('价格不能低于0');exit;
//        }
        if ($commission<0) {
             $this->error('车夫提成不能低于0');exit;
        }
//        if (empty($price)) {
//            $this->error('请填写价格');exit;
//        }

        
        //判断是否存在该用户名和手机号
        $is_name = Db::name('goods')->where('goods_name',$goods_name)->find();
        if($is_name){
            $this->error('该产品名已存在');exit;
        }
       
        //封装数据
        $data['goods_name'] = $goods_name;
        //$data['price'] = $price;
        $data['price'] = 0;
        $data['cat_id'] = $cat_id;
        $data['time'] = time();
        $data['commission'] = $commission;
    
        if (Db::name('goods')->insert($data)) {

             //写入系统日志
             $log['log_time']=time();
             $log['user_id']=session("ADMIN_ID");//操作人ID
             $log['name']=session("name");//操作人
             $log['info']="管理员".session("name")."于".date("Y-m-d H:i:s")."新增了产品".$goods_name;
             Db::name('system_log')->insert($log);


             $this->success('新增产品成功', 'goods/goods_list');
        }else{
            $this->error('新增产品失败');
        }
            
    }

    public function edit(){
        $request = request();
        $id = $request->param('id');
        $goods = Db::name('goods')->where('goods_id',$id)->find();
        $cat=Db::name("goods_category")->order("cat_id desc")->select();
        $this->assign("cat",$cat);
        $this->assign("goods",$goods);
        return $this->fetch();
    }

    public function editPost()
    {
        $request = request();
        //提取数据
        $id = $request->param('id');
        if (empty($id)) {
            $this->error('系统错误');
        }
        $goods_name = $request->param('goods_name');
        $price = $request->param('price');
        $cat_id = $request->param('cat_id');
        $commission = $request->param('commission');
        $ulen = strlen($goods_name);
        $data=array();
        if($ulen>100){
            $this->error('产品超过限制长度100');
        }

        if (empty($goods_name)) {
            $this->error('请填写产品名');exit;
        }
//        if ($price<0) {
//             $this->error('价格不能低于0');exit;
//        }
        if ($commission<0) {
             $this->error('车夫提成不能低于0');exit;
        }
//        if (empty($price)) {
//            $this->error('请填写价格');exit;
//        }
        $goods= Db::name('goods')->where('goods_id',$id)->find();
        if ($goods['goods_name']!=$goods_name) {
            //判断是否存在该用户名和手机号
            $is_name = Db::name('goods')->where('goods_name',$goods_name)->find();
            if($is_name){
                $this->error('该产品名已存在');exit;
            }
        }
        

        if ($goods_name) {
            $data['goods_name'] = $goods_name;
        }

        if ($price) {
            $data['price'] = 0;
            //$data['price'] = $price;
        }
        if ($commission) {
            $data['commission'] = $commission;
        }

        $data['cat_id']=$cat_id;
       
        //封装数据
        if (!empty($data)) {
            Db::name('goods')->where("goods_id",$id)->update($data);

             //写入系统日志
             $log['log_time']=time();
             $log['user_id']=session("ADMIN_ID");//操作人ID
             $log['name']=session("name");//操作人
             $log['info']="管理员".session("name")."于".date("Y-m-d H:i:s")."编辑了产品".$goods_name;
             Db::name('system_log')->insert($log);
            $this->success('编辑产品成功', 'goods/goods_list');
        }else{
             $this->error('暂无修改', 'goods/goods_list');
        }
            
    }

	//删除
	public function delete(){
		$id = $this->request->param('id', 0, 'intval');
		$res=Db::name('goods')->where('goods_id',$id)->delete();
		if($res !== false){
			$this->success("删除成功！");
		}else{
			$this->error("删除失败！");
		}
	}

    public function goods_attr(){
        $goods_attr=Db::name("goods_attr")->paginate(10);
        $this->assign("goods_attr",$goods_attr);
        $this->assign('page', $goods_attr->render());
        return $this->fetch();
    }

    public function add_attr()
    {
        return $this->fetch();
    }

    public function addattrPost()
    {
        $request = request();
        //提取数据
        $start = $request->param('start');
        $end = $request->param('end');
        
        if ($start<0) {
             $this->error('规格属性区间的开始值不能小于0');exit;
        }
        if (empty($start)) {
            $this->error('请填写规格属性区间的开始值');exit;
        }
        if ($end<0) {
             $this->error('规格属性区间的结束值不能小于0');exit;
        }
        if (empty($end)) {
            
        }else{
            if ($start>$end) {
                $this->error('开始值不能小于结束值');exit;
            }
        }

        

        $list=Db::name("goods_attr")->order("id desc")->find();
        if (!empty($list)) {
            if ($list['end']=='') {
                $this->error('上一个区间的结束值是无穷，所以不能添加');
            }else{
                if ($start<$list['end']) {
                    $this->error('开始值不能小于上一个区间的结束值');exit;
                }
            }
            
        }
        
        
        //封装数据
        $data['start'] = $start;
        $data['end'] = $end;
       
        if (Db::name('goods_attr')->insert($data)) {
            $this->success('新增规格属性成功', 'goods/goods_attr');
        }else{
            $this->error('新增规格属性失败');
        }
            
    }

    public function edit_attr(){
        $request = request();
        $id = $request->param('id');
        $goods_attr = Db::name('goods_attr')->where('id',$id)->find();
        $this->assign("goods_attr",$goods_attr);
        return $this->fetch();
    }

    public function editattrPost()
    {
        $request = request();
        //提取数据
        $id = $request->param('id');
        $start = $request->param('start');
        $end = $request->param('end');
        $data=array();
        if ($id<0) {
             $this->error('系统错误');exit;
        }
        if ($start<0) {
             $this->error('规格属性区间的开始值不能小于0');exit;
        }
        if (empty($start)) {
            $this->error('请填写规格属性区间的开始值');exit;
        }
        if ($end<0) {
             $this->error('规格属性区间的结束值不能小于0');exit;
        }
        if (empty($end)) {
            $check=Db::name("goods_attr")->where("end","")->find();
            if ($check&&$check['id']!=$id) {
              $this->error('已存在无穷的结束值，所以不能修改');exit; 
            }
        }else{
            if ($start>$end) {
                $this->error('开始值不能小于结束值');exit;
            }
        }

        
        //上一个
        $list=Db::name("goods_attr")->where("id","lt",$id)->order("id desc")->find();

        if (!empty($list)) {
            if ($list['end']=='') {
                $this->error('上一个区间的结束值是无穷，所以不能修改');
            }else{
                if ($start<$list['end']) {
                    $this->error('开始值不能小于上一个区间的结束值');exit;
                }
            }
        }
        //下一个
        $list=Db::name("goods_attr")->where("id","gt",$id)->order("id asc")->find();
       
        if (!empty($list)) {
        
            if ($end>$list['start']) {
                $this->error('结束值不能大于下一个区间的开始值');exit;
            }
            
        }
        
        if ($start) {
            $data['start'] = $start;
        }
        if ($end) {
            $data['end'] = $end;
        }

        if ($data) {
            // dump($data);die;
           Db::name('goods_attr')->where("id",$id)->update($data);
           $this->success('修改规格属性成功', 'goods/goods_attr');
        }
        
        
            
    }

    public function attr_delete(){
        $id = $this->request->param('id', 0, 'intval');
        $res=Db::name('goods_attr')->where('id',$id)->delete();
        if($res !== false){
            $this->success("删除成功！");
        }else{
            $this->error("删除失败！");
        }
    }


    public function goods_category(){
        $city=session('city');
        $district=session('district');

        $cat=Db::name("goods_category")->order('sort asc')->select();
        $cat=json_decode($cat,true);
        foreach ($cat as $k=>$v){
            $cat[$k]['img']=json_decode($v['img']);
        }
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
        $sort = $request->param('sort');
        $is_show = $request->param('is_show');
        $img = $request->file('img');
        $img2 = $request->file('img2');

        if ($cat_name=="") {
             $this->error('请填写分类名');exit;
        }
       
        if ($sort<0) {
             $this->error('请填写排序');exit;
        }

        $list=Db::name("goods_category")->where(array("cat_name"=>$cat_name))->find();
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
            $data['img'][]='\uploads\img'.DS.$path;
        }

        //如果头像不存在
        if(!$img2){
            $this->error("请上传图片");
        }else{
            //头像存在
            $info=$img2->move(ROOT_PATH.'public'.DS.'uploads'.DS.'img');
            $path=$info->getSavename();
            $data['img'][]='\uploads\img'.DS.$path;
        }
        $data['img']=json_encode($data['img']);
        //封装数据
        $data['cat_name'] = $cat_name;
        $data['sort'] = $sort;
        $data['is_show'] = $is_show;
       
        if (Db::name('goods_category')->insert($data)) {
            $this->success('新增分类成功', 'goods/goods_category');
        }else{
            $this->error('新增分类失败');
        }
            
    }

    public function edit_cat()
    {
        $request = request();
        $id = $request->param('id');
        $cat = Db::name('goods_category')->where('cat_id',$id)->find();
        $cat['img']=json_decode($cat['img']);
        $this->assign("cat",$cat);
        return $this->fetch();
    }

    public function editcatPost()
    {
        $request = request();
        //提取数据
        $cat_name = $request->param('cat_name');
        $sort = $request->param('sort');
        $is_show = $request->param('is_show');
        $img = $request->file('img');
        $img2 = $request->file('img2');
        $id = $request->param('id');
        if ($id=="") {
             $this->error('无法操作');exit;
        }
        if ($cat_name=="") {
             $this->error('请填写分类名');exit;
        }
       
        if ($sort<0) {
             $this->error('请填写排序');exit;
        }

        $cat=Db::name("goods_category")->where("cat_id",$id)->find();
        if ($cat['cat_name']!=$cat_name) {
            $list=Db::name("goods_category")->where(array("cat_name"=>$cat_name))->find();
            if (!empty($list)) {
                $this->error("分类名已存在");
            }
        }
        $data=[];
        $data['img']=json_decode($cat['img']);
        //如果头像存在
        if($img){
            $info=$img->move(ROOT_PATH.'public'.DS.'uploads'.DS.'img');
            $path=$info->getSavename();
            $data['img'][0]='\uploads\img'.DS.$path;
        }
        //如果头像存在
        if($img2){
            $info=$img2->move(ROOT_PATH.'public'.DS.'uploads'.DS.'img');
            $path=$info->getSavename();
            $data['img'][1]='\uploads\img'.DS.$path;
        }
        $data['img']=json_encode($data['img']);
        if ($cat_name) {
           $data['cat_name'] = $cat_name;
        }
        if ($sort) {
           $data['sort'] = $sort;
        }

        $data['is_show'] = $is_show;
        
        //封装数据
        Db::name('goods_category')->where("cat_id",$id)->update($data);
        $this->success('编辑分类成功', 'goods/goods_category');
        
            
    }

    public function delete_cat(){
        $id = $this->request->param('id', 0, 'intval');
        $res=Db::name('goods_category')->where('cat_id',$id)->delete();
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
