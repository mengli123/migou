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

    public function read(){
        header("Content-Type: text/html;charset=utf-8");
        if(request()->file("file")){
            $file = request()->file('file');
            $info = $file->move( './uploads/excel');
            if($info){
                $path = "./uploads/excel/".$info->getSaveName();
                $data=$this->import_excel($path);
                $res=$this->save_data($data);
                if($data){
                    return json(['state'=>1,'data'=>$res]);

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