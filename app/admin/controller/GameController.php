<?php
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\AdminMenuModel;

class GameController extends AdminBaseController
{
    /**
    猫咪列表
     */
	public function cat_list(){
        $cat = Db::name('cat')->select()->all();
        $this->assign("cat",$cat);
        return $this->fetch();
	}

	/**
	管理阶段猫咪
     */
	public function cat_age(){
	    $
        return $this->fetch();
    }

}