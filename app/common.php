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