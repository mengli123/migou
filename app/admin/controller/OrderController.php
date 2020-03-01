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
        $data = Db::name('order')->select()->all();
       $v = array_keys($data[0]);
       foreach ($v as $key=>$val){
           $v[$key]=$table[$val];
       }

        $data = array_merge([$v],$data);
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
               // $res=$this->save_data($data);
                if($data){
                    //return json(['state'=>1,'data'=>$res]);
                    return json(['state'=>1,'data'=>$data]);

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