<?php
namespace app\admin\controller;

use app\admin\model\OrderModel;
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
            $status= $request->param('status');
            if($status){
             $where_word['status']=$status;
             $this->assign('status',$status);
            }
            if ($keyword) {
                $where_word['goods_name|price|name'] =  ['like', '%' . $keyword . '%'];
                $this->assign('keyword',$keyword);
            }
            $start=$request->param('start_time');
            $end=$request->param('end_time');
            if ($start&&$end) {
                $this->assign('start',$start);
                $this->assign('end',$end);
                if ($start>=$end) {
                    $this->error("开始时间不能大于或等于结束时间");exit();
                }
                $str =strtotime($start).','.strtotime($end);
                $where[] =  ['ctime','between',strtotime($start).','.strtotime($end)];
                dump($where);
            }else if($start&&!$end){
                $this->assign('start',$start);
                $where[] = array('ctime','gt',strtotime($start));
            }else if(!$start&&$end){
                $this->assign('end',$end);
                $where[] = array('ctime','lt',strtotime($end));
            }
            if (empty($where)) {
                $where=1;
            }
            if(empty($where_word)){
                $where_word=1;
            }
        }else{
            $where=1;
            $where_word=1;
        }
		$list = Db::name('order')
            ->where($where_word)
            ->where($where)
            ->order('order_id desc')
            ->paginate(10);
        //dump($goods);exit;
        $status= [
            '-1'=>'<b style="color: gray">已取消</b>',
            '0'=>'<b style="color: red">待支付</b>',
            '1'=>'<b style="color: green">待发货</b>',
            '2'=>'<b style="color: blue">待收货</b>',
            '3'=>'<b style="color:pink">已收货</b>',
            '4'=>'<b style="color: black">已完成</b>'
        ];
        $this->assign('status',$status);
        $this->assign("list",$list);
        $list->appends($request->param());
        $this->assign('page', $list->render());
        return $this->fetch();

	}
	/**
	编辑订单发货
     */
	public function edit_order(){
	    $order_id=input('order_id');
	    $order =Db::name('order')->where('order_id',$order_id)->find();
	    $this->assign('order',$order);
	    $this->assign('order_id',$order_id);
        return $this->fetch();
    }
    /**
    保存编辑物流信息
     */
    public function save_edit_order(){
        $request = request();
        $data=[];
        //提取数据
        $id = $request->param('order_id');
        if (empty($id)) {
            $this->error('请传入订单错误');
        }
        $express_no = $request->param('express_no');
        $express_type = $request->param('express_type');
        $status = $request->param('status');

        if (empty($express_no)) {
            $this->error('请填写物流单号');exit;
        }
        if (empty($express_type)) {
            $this->error('请填写物流快递名称');exit;
        }
        $data['express_no']=$express_no;
        $data['express_type']=$express_type;
        $data['status']=$status;
        $save= Db::name('order')->where("order_id",$id)->update($data);
        if ($save) {
            $this->success('编辑物流信息成功', 'order/order_list');
        }else{
            $this->error('暂无修改', 'order/order_list');
        }

    }
    /**
    删除订单
     */
    public function del_order(){
        $order_id = input('order_id');
        $del= Db::name('order')->where('order_id',$order_id)->delete();
        if($del){
            $this->success('删除成功', 'order/order_list');
        }else{
            $this->error('删除失败', 'order/order_list');
        }
    }

	public function test(){
	    $a=['a','b','c'];
	    $b=[[1,1,1],[2,2,2],[3,3,3]];
	    $c=[];
	    foreach ($b as $k=>$v){
            $c[]=array_combine($a,$v);
        }
	    dump($c);


    }
/**
导出excel表格
 */
	public function out_excel(){
        $fieldinfo = Db::query('SHOW FULL COLUMNS FROM cmf_order');
        $table= [];
        foreach ($fieldinfo as $ka=>$va){

            $table[$va['Field']]=$va['Comment'];
        }
        //dump($table);
        //dump($fieldinfo);
//        $data = array(
//            array(NULL, 2010, 2011, 2012),
//            array('Q1',   12,   15,   21),
//            array('Q2',   56,   73,   86),
//            array('Q3',   52,   61,   69),
//            array('Q4',   30,   32,    10),
//        );
//        $cn_name = [
//            ''
//        ]
        if($_REQUEST){
            $request = request();
            /**搜索条件**/
            $keyword = trim($request->param('keyword'));
            if ($keyword) {
                $where_word['goods_name|price|name'] =  ['like', '%' . $keyword . '%'];
                $this->assign('keyword',$keyword);
            }
            $start=$request->param('start_time');
            $end=$request->param('end_time');
            if ($start&&$end) {
                $this->assign('start',$start);
                $this->assign('end',$end);
                if ($start>=$end) {
                    $this->error("开始时间不能大于或等于结束时间");exit();
                }
                $str =strtotime($start).','.strtotime($end);
                $where[] =  ['ctime','between',strtotime($start).','.strtotime($end)];
                dump($where);
            }else if($start&&!$end){
                $this->assign('start',$start);
                $where[] = array('ctime','gt',strtotime($start));
            }else if(!$start&&$end){
                $this->assign('end',$end);
                $where[] = array('ctime','lt',strtotime($end));
            }
            if (empty($where)) {
                $where=1;
            }
            if(empty($where_word)){
                $where_word=1;
            }
        }else{
            $where=1;
            $where_word=1;
        }
        //dump($where);
        $data = Db::name('order')->where($where)->select()->all();
        foreach ($data as $k=>$v){
            $data[$k]['ctime'] = date('Y-m-d H:i:s',$v['ctime']);
        }
       // dump($data);exit;
       $v = array_keys($data[0]);
        $q=$v;
       foreach ($v as $key=>$val){
           $v[$key]=$table[$val];
       }

        $data = array_merge([$q],[$v],$data);
        //dump($data);exit;
        $filename =date('YmdHis');
        $this->create_xls($data,$filename);
    }



    /**
     * 数组存入xls格式的excel文件

     */
    function create_xls($data,$filename='simple.xls'){
        ini_set('max_execution_time', '0');
        //Vendor('PHPExcel.PHPExcel');
        $filename=str_replace('.xls', '', $filename).'.xls';
        $phpexcel = new \PHPExcel();
        $phpexcel->getProperties()
            ->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        $phpexcel->getActiveSheet()->fromArray($data);
        $phpexcel->getActiveSheet()->setTitle('Sheet1');
        $phpexcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$filename");
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        $objwriter = \PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
        $objwriter->save('php://output');
        exit;
    }

	/**
	读取excel文件
     */
    public function read(){
        header("Content-Type: text/html;charset=utf-8");
        if(request()->file("file")){
            $file = request()->file('file');
            $info = $file->move( './uploads/excel');
            if($info){
                $path = "./uploads/excel/".$info->getSaveName();
                $data=$this->import_excel($path);
                //dump($data);exit;
               // $res=$this->save_data($data);
                if($data){
                    //return json(['state'=>1,'data'=>$res]);
                    //return json(['state'=>1,'data'=>$data]);
                   // dump($data);
                    //$order = new OrderModel();
                    $a=$data[1];
                    array_pop($a);
                    unset($data[1]);
                    unset($data[2]);
                    $c=[];
                    $num=0;
                    foreach ($data as $k=>$v){
                        array_pop($v);
                        $c=array_combine($a,$v);
                        //dump($c);
                        $save = Db::name('order')->update($c);
                        if($save==1){
                            $num++;
                        }
                    }

                    return json(['state'=>1,'msg'=>'更新了'.$num.'条订单数据']);

                }else{
                    return json(['state'=>0,'msg'=>'处理错误']);
                }
            }else{
                return json(['state'=>0,'msg'=>'文件不存在']);
            }
        }
        return view();
    }

	/**
	导出excel
     */
	public function derived_excel(){

    }


	/**
	导入excel
     */
    function import_excel($file){
        // 判断文件是什么格式
        $type = pathinfo($file);
        $type = strtolower($type["extension"]);
        if ($type=='xlsx') {
            $type='Excel2007';
        }elseif($type=='xls') {
            $type = 'Excel5';
        }
        ini_set('max_execution_time', '0');
        //Vendor('PHPExcel.PHPExcel');
        // 判断使用哪种格式
        $objReader = \PHPExcel_IOFactory::createReader($type);
        $objPHPExcel = $objReader->load($file);
        $sheet = $objPHPExcel->getSheet(0);
        // 取得总行数
        $highestRow = $sheet->getHighestRow();
        // 取得总列数
        $highestColumn = $sheet->getHighestColumn();
        //总列数转换成数字
        $numHighestColum = \PHPExcel_Cell::columnIndexFromString("$highestColumn");
        //循环读取excel文件,读取一条,插入一条
        $data=array();
        //从第一行开始读取数据
        for($j=1;$j<=$highestRow;$j++){
            //从A列读取数据
            for($k=0;$k<=$numHighestColum;$k++){
                //数字列转换成字母
                $columnIndex = \PHPExcel_Cell::stringFromColumnIndex($k);
                // 读取单元格
                $data[$j][]=$objPHPExcel->getActiveSheet()->getCell("$columnIndex$j")->getValue();
            }
        }
        return $data;
    }

}