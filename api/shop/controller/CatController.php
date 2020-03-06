<?php
namespace api\shop\controller;

use cmf\controller\RestBaseController;
use think\Db;
use think\Controller;



class CatController extends RestBaseController
{
    //public $play_time=100;
    /**
    设定玩耍时间为喂食间隔的1/2
     */
    //跨域方法
    public function origin(){
        // 指定允许其他域名访问
        header('Access-Control-Allow-Origin:*');
        // 响应类型
        // header('Access-Control-Allow-Methods:POST');
        header('Access-Control-Allow-Methods: OPTIONS,POST,GET');
        // 响应头设置
        header('Access-Control-Allow-Headers:origin,x-requested-with,content-type');

    }
    function get_time() {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
    获取商品兑换列表
     */
    public function get_cat_prize(){
        $list=Db::name('cat_prize')->all();
        dump($list);
    }

    /**
    猫咪列表
     */
    public function get_cat_list(){
        $cat = Db::name('cat')->select()->all();
        if($cat){
            $this->success('获取成功',$cat);
        }else{
            $this->error('获取失败',[]);
        }
    }

    /**
    获取饲料余量
     */
    public function get_user_feed(){
        $user_id= input('user_id');
        $all_feed =Db::name('user_cat_info')->where('user_id',$user_id)->value('feed');
        if($all_feed){
            $this->success('请求成功',$all_feed);
        }else{
            $this->error('查询失败','');
        }
    }
    /**
    增加饲料
     */
    public function add_user_feed(){
        $first_send=100;
        $user_id=input('user_id');
        $feed_add_num = input('feed_add_num');
        $sel=Db::name('user_cat_info')->where('user_id',$user_id)->select()->all();
        if(count($sel)<1){
            $create = Db::name('user_cat_info')->insert(['user_id'=>$user_id,'feed'=>$first_send]);
        }
        $res=Db::name('user_cat_info')->where('user_id',$user_id)->setInc('feed',$feed_add_num);
        if($res){
            $this->success('增加饲料成功',$res);
        }else{
            $this->error('增加饲料失败','');
        }
    }

    public function tests(){

//        dump($prize_array);
//        dump($prize_id);

    }

    /**
    领养猫咪
     * 可以同时喂养5只猫
     */
    public function buy_cat(){
        $user_id =input('user_id');
        $cat_id = input('cat_id');

        $sel= Db::name('user_cat_info')->where('user_id',$user_id)->select();
        if(count($sel)<1){
            $user_cat_info = Db::name('user_cat_info')->insert(['user_id'=>$user_id]);
        }

        if(!$user_id){
            $this->error('请传入user_id');
        }
        if(!$cat_id){
            $this->error('请传入cat_id');
        }
        $sel = Db::name('user_cat')
            ->where(['user_id'=>$user_id,'status'=>0])
            ->select()->all();
        if(count($sel)>4){
            $this->error('最多领养5只猫咪，请卖出后再来');
        }
        $data= [
            'user_id'=>$user_id,
            'cat_id'=>$cat_id,
            'age_id'=>1,
            'ctime'=>time(),
            'status'=>0
        ];
        $ins = Db::name('user_cat')->insertGetId($data);
        if($ins){
            $new = Db::name('user_cat')->where('user_cat_id',$ins)->find();
            $cat_age=Db::name('cat_age')->where(['cat_id'=>$new['cat_id'],'age_id'=>$new['age_id']])->find();
            $new['feed_num']=$cat_age['feed_num'];
            $new['feed_times']=$cat_age['feed_times'];
            $new['width']=$cat_age['width'];
            $new['interval']=$cat_age['interval'];
            $new['height']=$cat_age['height'];
            $new['img']=$cat_age['img'];
            $this->success('领养成功',$new);
        }else{
            $this->error('领养失败',[]);
        }
    }

    /**
    查询当前5只猫咪年龄段以及状态
     * 每一只距离上次喂养的时间
     */
    public function user_cat_status(){
        $user_id=input('user_id');
        if(!$user_id){
            $this->error('请传入user_id');
        }
        $sel = Db::name('user_cat')
            ->where(['user_id'=>$user_id])
            ->select()->all();
        foreach ($sel as $k=>$v){
            $cat_age=Db::name('cat_age')->where(['cat_id'=>$v['cat_id'],'age_id'=>$v['age_id']])->find();
            $interval_array=json_decode($cat_age['interval']);
            $level=$v['level'];
            $interval=$interval_array[$level];
            $last_feed_time=$v['last_feed_time'];
            if($v['is_play']==1&&time()-$last_feed_time>$interval/2){
                $is_play=0;
                Db::name('user_cat')->where('user_cat_id',$v['user_cat_id'])->update(['is_play'=>0]);
                $sel[$k]['is_play']=$is_play;
                $prize_array=Db::name('cat_prize')->column('id');
                $rand_id=array_rand($prize_array,1);
                $prize_id=$prize_array[$rand_id];
                Db::name('user_cat_prize')->where(['user_id'=>$user_id,'prize_id'=>$prize_id])->setInc(1);
                Db::name('user_cat_prize_log')
                    ->insert([
                        'user_id'=>$user_id,
                       //
                        // 'cat_id'=>$v['cat_id'],
                        'user_cat_id'=>$v['user_cat_id'],
                        'ctime'=>time(),
                        'prize_id'=>$prize_id
                    ]);
            }

            $sel[$k]['interval']=$interval;
            $sel[$k]['feed_num']=$cat_age['feed_num'];
            $sel[$k]['feed_times']=$cat_age['feed_times'];
            $sel[$k]['width']=$cat_age['width'];
            $sel[$k]['height']=$cat_age['height'];
            $sel[$k]['img']=$cat_age['img'];
        }
//        dump($sel);
//        exit;
        $user_cat_info=Db::name('user_cat_info')->where('user_id',$user_id)->find();
        if($sel&&$user_cat_info){
            $data=[
                'info'=>$user_cat_info,
                'game'=>$sel
            ];
           // dump($data);
            $this->success('查询成功',$data);
        }else{
            $this->error('查询失败',[]);
        }
    }

    /**
    喂猫
     * 问题：什么时候升级进入下一个阶段，需要一个事件去触发他
     * 返回一个下次喂猫时间
     */
    public function feed_cat(){
        $user_id=input('user_id');
        $user_cat_id =input('user_cat_id');
        $cat_age=Db::name('user_cat')->where('user_cat_id',$user_cat_id)->find();
        $user_cat=Db::name('user_cat')->where('user_cat_id',$user_cat_id)->find();
        $cat_id= $user_cat['cat_id'];
        $age_id =$user_cat['age_id'];
        $last_feed_time = $user_cat['last_feed_time'];
        $interval =json_decode($cat_age['interval']);
        $feed_num =$cat_age['feed_num'];
        $feed_times =$cat_age['feed_times'];
        $feed=Db::name('user_cat_info')->where('user_id',$user_id)->value('feed');
        $duration=time()-$last_feed_time; //距上次喂猫过去了$interval秒
        $will =$interval-$duration;  //$will秒后可以喂猫
        if($age_id>2){
            $this->error('已经养到成年,不需要再喂啦',['code'=>0]);
        }
        if($feed<$feed_num){
            $this->error('饲料不足',['code'=>2]);
        }
        if($duration<$interval){
            $this->error('未到喂猫时间，请在'.$will.'秒后再来',['code'=>3]);
        }
        $data = [
            'user_id'=>$user_id,
            'cat_id'=>$cat_id,
            'age_id'=>$age_id,
            'ctime'=>time(),
            'user_cat_id'=>$user_cat_id
        ];
        $ins=Db::name('user_cat_log')->insert($data);
        $age_feed_num =Db::name('user_cat_log')->where(['user_cat_id'=>$user_cat_id,'age_id'=>$age_id])->count();
        $up_data=['last_feed_time'=>time()];
        if($age_feed_num==$feed_times){
            $up_data['age_id']=$age_id+1;
            $is_full=true;
        }else{
            $is_full=false;
        }
        //dump($up_data);
        $upd= Db::name('user_cat')->where('user_cat_id',$user_cat_id)->update($up_data);
        if($ins&&$upd){
            $new = Db::name('user_cat')->where('user_cat_id',$user_cat_id)->find();
            $cat_age=Db::name('cat_age')->where(['cat_id'=>$new['cat_id'],'age_id'=>$new['age_id']])->find();
            Db::name('user_cat_info')->where('user_id',$user_id)->setDec('feed',$cat_age['feed_num']);
            $new['feed_num']=$cat_age['feed_num'];
            $new['feed_times']=$cat_age['feed_times'];
            $new['width']=$cat_age['width'];
            $new['interval']=$cat_age['interval'];
            $new['height']=$cat_age['height'];
            $new['img']=$cat_age['img'];
            $new['is_full']=$is_full;
            $new['code']=1;
            $new['feed']=$feed-$feed_num;
            $this->success('喂猫成功',$new);
        }else{
            $this->error('喂猫失败',[]);
        }

    }

    /**
    获取奖励别聊
     */
    public function cat_prize(){
        $list= Db::name('cat_prize')->select()->all();
        if($list){
            $this->success('获取成功',$list);
        }else{
            $this->error('获取失败',$list);
        }
    }

    /**

     */
    public function test1(){

    }

    /**

     */
    public function test2(){

    }



}