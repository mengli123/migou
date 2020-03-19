<?php
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\AdminMenuModel;
use think\Request;

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

    public function version_list(){
        $list=Db::name('app_version')->order('id desc')->select()->all();
        $this->assign('list',$list);
        $status= [
            '<p style="color: gray">测试版本</p>',
            '<b style="color:green">正式版本</b>'
        ];
        $this->assign('status',$status);
        return $this->fetch();
    }
    public function pub(){
        $id= input('id');
        $pub= Db::name('app_version')->where('id',$id)->update(['status'=>1]);
        Db::name('app_version')->where('id','neq',$id)->update(['status'=>0]);
        if($pub){
            $this->success("发布成功！");
        }else{
            $this->error("发布失败！");
        }
    }
    public function up_app(){
        return $this->fetch();
    }
    public function up(){
        $file = request()->file('files');
        if (empty($file)) {
            $this->error('请选择上传文件');
        }
        //移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        if ($info) {
            $save_url=str_replace('\\','/',$info->getSaveName());
            Db::name('app_version')->insert(['version'=>'1.0.','url'=> $this->request->domain().'/uploads/'.$save_url]);
            $this->success('文件上传成功','agent/version_list');

        } else {
            //上传失败获取错误信息
            $this->error($file->getError());
        }
    }
    //删除
    public function delete(){
        $id = $this->request->param('id', 0, 'intval');
        $res=Db::name('app_version')->where('id',$id)->delete();
        if($res !== false){
            $this->success("删除成功！");
        }else{
            $this->error("删除失败！");
        }
    }


}