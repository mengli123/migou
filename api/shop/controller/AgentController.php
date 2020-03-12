<?php
namespace api\shop\controller;

use cmf\controller\RestBaseController;
use think\Db;
use think\Request;

class AgentController extends RestBaseController
{
    /** 查询当前用户身份-若有上级代理查询上级代理，若有下级查询下级*/
    public function sel_user_staus(){
        $user_id=input('user_id');
    }


    /** 添加上级代理
     3=>省级
     4=>市级
     5=>区级
     */
    public function add_up_agent(){
        $user_id=input('user_id');
        $parent_id=input('parent_id');
        $user_parent = Db::name('user')->where('id',$user_id)->value('parent_id');
        $parent = Db::name('role_user')->where('user_id',$parent_id)->where('role_id','in',[3,4,5])->find();
        //dump($parent);
        if($parent==null){
            $this->error('要绑定的上级不是代理，请选择代理绑定');
        }
        if($user_parent!=0){
            $this->error('已绑定过上级代理，不能重复绑定');
        }
        $data=[];
        if($parent['role_id']==3){
            /** 绑定省级上级，成为市级代理*/
           $data['user_type']=1;
            Db::name('RoleUser')->insert(["role_id" => 4, "user_id" => $user_id,'parent_id'=>$parent_id]);
            $msg='成为市级代理';

        }elseif($parent['role_id']==4){
            /** 绑定市级上级，成为区级代理*/
            $data['user_type']=1;
            Db::name('RoleUser')->insert(["role_id" => 5, "user_id" => $user_id,'parent_id'=>$parent_id]);
            $msg='成为区级代理';
        }else{
            /** 绑定区级上级*/
            $msg='';
        }
        $data['parent_id']=$parent_id;
        $upd=Db::name('user')->where('id',$user_id)->update($data);
        if($upd){
            $this->success('已绑定上级代理'.$msg);
        }else{
            $this->error('绑定上级失败');
        }

    }

}