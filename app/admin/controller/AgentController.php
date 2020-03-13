<?php
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\AdminMenuModel;

class AgentController extends AdminBaseController
{

    public function agent_list(){
        $id   = cmf_get_current_admin_id();
        //dump($id);
        //$id=9;
        $list=Db::name('user')->where('parent_id',$id)
            ->select()
            ->all();
        $this->assign('list',$list);
        return $this->fetch();
    }


}