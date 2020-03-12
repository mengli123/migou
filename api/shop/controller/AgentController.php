<?php
namespace api\shop\controller;

use cmf\controller\RestBaseController;
use think\Db;
use think\Request;

class AgentController extends RestBaseController
{
    /** 查询当前用户身份-若有上级代理查询上级代理，若有下级查询下级*/
    /** 几种状态
     *0：啥都未绑定
     *1：已绑定，不是代理
     *3:已绑定，是省级代理
     *4:已绑定，是市级代理
     *5：已绑定，是区级代理
     */
    public function sel_user_status(){
        $user_id=input('user_id');
        $result=[];
        $user = Db::name('user')->where('id',$user_id)->field('user_type,parent_id')->find();
        $parent=Db::name('role_user')
            ->where('user_id',$user['parent_id'])
            ->find();
        if($user['user_type']==2){
            if($user['parent_id']==0){
                $result['msg']='未绑定上级代理';
                $result['code']=0;
                $parent['parent']='';
            }else{
                $user_login=Db::name('user')->where('id',$user['parent_id'])->value('user_login');
                $result['msg']='已绑定上级，不是代理';
                $result['code']=1;
                $parent['parent']=$user_login;
            }
        }else{
            $user_login=Db::name('user')->where('id',$user['parent_id'])->value('user_login');
            if($parent['role_id']==3){
                $result['msg']='已绑定上级是省级代理';
                $result['code']=3;
            }elseif($parent['role_id']==4){
                $result['msg']='已绑定上级是市级代理';
                $result['code']=4;
            }elseif($parent['role_id']==5){
                $result['msg']='已绑定上级是区级代理';
                $result['code']=5;
            }
            $result['parent']=$user_login;
        }
        $result['parent_id']=$user['parent_id'];
        return json($result);
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