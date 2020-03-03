<?php
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\AdminMenuModel;

class GameController extends AdminBaseController
{
	public function cat_list(){
        $cat = Db::name('cat')->select()->all();
        $this->assign("cat",$cat);
        return $this->fetch();
	}

}