<?php
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\AdminMenuModel;

class OrderController extends AdminBaseController
{
	public function order_list(){
        if($_REQUEST){
            $request = request();
            /**搜索条件**/
            $keyword = trim($request->param('keyword'));
            if ($keyword) {
                $where['goods_name|price|name'] =  ['like', '%' . $keyword . '%'];
            }
            $start=$request->param('start_time');
            $end=$request->param('end_time');
            if ($start&&$end) {
                if ($start>=$end) {
                    $this->error("开始时间不能大于或等于结束时间");exit();
                }
                $where['ctime'] = array(array('gt',strtotime($start)),array('lt',strtotime($end)));
            }else if($start&&!$end){
                $where['ctime'] = array('gt',strtotime($start));
            }else if(!$start&&$end){
                $where['ctime'] = array('lt',strtotime($end));
            }
            if (empty($where)) {
                $where=1;
            }
        }else{
            $where=1;
        }
		$list = Db::name('order')
            ->where($where)
            ->paginate(10);
        //dump($goods);exit;
        $this->assign("list",$list);
        $list->appends($request->param());
        $this->assign('page', $list->render());
        return $this->fetch();

	}

}