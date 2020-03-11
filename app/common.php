<?php
use think\Db;
/** 获取第一张图片*/
function get_first_pic($str){
    $pic = json_decode($str)[0];
    return $pic;
}
/** 获取分类名*/
function get_type($goods_id){
    $type_name = Db::name('goods_and_type')
        ->where('goods_id',$goods_id)
        ->alias('gt')
        ->join('goods_type t','gt.type_id=t.id')
        ->column('type_name');
    $str='';
    foreach ($type_name as $k=>$v){
        $str.=$v.'<br>';
    }
    return $str;
}
/** 获取规格名称*/
function get_specs($goods_id){
    $specs = Db::name('goods_specs')->where('goods_id',$goods_id)
        ->column('size');
    $str='';
    foreach ($specs as $k=>$v){
        $str.=$v.'<br>';
    }
    return $str;
}
/** 获取用户角色 */
function get_user_role($user_id){

    $user_role=Db::name('role_user')->where('user_id',$user_id)->select()->all();
    $roles='';
    foreach($user_role as $k=>$v){
        $role=Db::name('role')->where('id',$v['role_id'])->value('name');
        $roles.='['.$role.']';
    }
    return $roles;
}
/**
获取上级代理ID
 */
function get_parent_agent($user_id){
    $parent =Db::name('role_user')
        ->alias('ru')
        ->join('user u','ru.parent_id=u.id')
        ->field('u.id,user_login')
        ->where('user_id',$user_id)
        ->find();
    $str= '【'.$parent['id'].'】'.$parent['user_login'];
    return $str;
}
