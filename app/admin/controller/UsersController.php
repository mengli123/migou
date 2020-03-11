<?php
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\AdminMenuModel;

class UsersController extends AdminBaseController
{
    /** 添加代理页面*/
    public function add_agent(){
        $roles = Db::name('role')->where('status', 1)->order("id DESC")->select();
        $this->assign("roles", $roles);
        return $this->fetch('user/add_agent');
    }

    public function test(){
        $_POST=['mobile'=>1735385051];
        $find=Db::name('user')->where('mobile',$_POST['mobile'])->find();
        if($find==null){
            echo 'no';
        }else{
            echo 'yes';
        }
        dump($find);

    }

    public function addPost()
    {
        if ($this->request->isPost()) {
            if (!empty($_POST['role_id']) && is_array($_POST['role_id'])) {
                $role_ids = $_POST['role_id'];
                unset($_POST['role_id']);
                $result = $this->validate($this->request->param(), 'User');
                if ($result !== true) {
                    $this->error($result);
                } else {
                    $_POST['user_pass'] = cmf_password($_POST['user_pass']);
                    $find=Db::name('user')->where('mobile',$_POST['mobile'])->find();
                    if($find==null){
                        $result             = DB::name('user')->insertGetId($_POST);
                    }elseif($find['user_type']!=1){
                        $_POST['user_type']=1;
                        DB::name('user')->where('id',$find['id'])->update($_POST);
                        $result =$find['id'];
                    }else{
                        $this->error("该用户已在管理员列表，请直接编辑");
                    }
                    if ($result !== false) {
                        //$role_user_model=M("RoleUser");
                        foreach ($role_ids as $role_id) {
                            if (cmf_get_current_admin_id() != 1 && $role_id == 1) {
                                $this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
                            }
                            Db::name('RoleUser')->insert(["role_id" => $role_id, "user_id" => $result]);
                        }
                        $this->success("添加成功！", url("user/index"));
                    } else {
                        $this->error("添加失败！");
                    }
                }
            } else {
                $this->error("请为此用户指定角色！");
            }

        }
    }





}