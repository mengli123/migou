<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Model;
use think\Db;

class GoodsModel extends Model
{
    public function specs(){
        return $this->hasMany('GoodsSpecsModel','goods_id','goods_id');
    }
    public function price(){
        return $this->hasMany('GoodsSpecsModel','goods_id','goods_id');
    }
}