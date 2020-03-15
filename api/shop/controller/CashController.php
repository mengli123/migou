<?php
namespace api\shop\controller;

use cmf\controller\RestBaseController;
use think\Db;
use EasyWeChat\Factory;

class CashController extends RestBaseController{
    //提现记录
    public function rebate_money_log($shop_id){
        $db=new RebateMoneyLog();
        $data=$db->where('shop_id',$shop_id)->paginate();

        $this->assign(['list'=>$data,'empty'=>'暂无数据','page'=>$data->render()]);
        return $this->fetch();
    }

    //金额管理
    public function money(){
        $db=new Money();
        $list=$db->with('shop')->paginate();
        //return json($list);
        $page = $list->render();
        $status=['无操作','<p style="color:darkorange">提现申请中~</p>','<p style="color:orangered">银行处理中~</p>'];
        $empty = '暂无';
        $this->assign('status',$status);
        $this->assign('empty', $empty);
        $this->assign("page", $page);
        $this->assign("list", $list);
        return $this->fetch();
    }
    public function bank_list(){
        $list=Db::name('bank_list')->select()->all();
        return json($list);
    }
    public function user_bank(){
        $user_id =input('user_id');
        $ub=Db::name('user_bank')->where('user_id',$user_id)->find();
        if($ub){
            $this->success('查询成功',$ub);
        }else{
            $this->error('查询失败');
        }
    }

    public function bind_bank(){
        $user_id=input('user_id');
        $bank_no=input('bank_no');
        $true_name=input('true_name');
        $bank_code=input('bank_code');
        if(!$bank_code&&!$bank_no&&!$true_name){
            $this->error('请确保信息完整');
        }
        $ins=Db::name('user_bank')->insert([
            'user_id'=>$user_id,
            'bank_no'=>$bank_no,
            'bank_code'=>$bank_code,
            'true_name'=>$true_name
        ]);
        if($ins){
            $this->success('成功');
        }else{
            $this->error('失败');
        }
    }

    // 给商户提现
    public function cash_out(){
        $id=input('user_id');
        $apply_cash=input('apply_cash');

        $result=[];
        $money=$db->where('shop_id',$id)->find();
        $out_money=$money['out_money']+$money['now_money'];
        $shop=$shop_db->where('id',$id)->find();

        $datas=[];
        $datas['trade_no']=date('YmdHis',time()).rand(1000,9999);
        // $data['trade_no']=1234567891;
        $datas['bank_no']=$shop['bank_card'];
        $datas['true_name']=$shop['true_name'];
        $datas['bank_code']=$shop['bank_code'];
        $tx_money=$money['now_money'];
        // $tx_money=0.02;
        $datas['amount']= $tx_money;

        // dump($datas);
        $q=$this->tixian($datas);
        $query=$this->query($trade_no=$datas['trade_no']);

//        $res=[$q,$query];
//        return json($res);

        if($query['err_code']=="SUCCESS"){
            //$data=['now_money'=>0,'out_money'=>$out_money,'status'=>2];
            $data=['status'=>2];
            $update=$db->where('shop_id',$id)->update($data);
            if($update==1){
                $operator=session("admin.name");
                $logs=['shop_id'=>$id,'ctime'=>time(),'money'=>$money['now_money'],'operator'=>$operator,'trade_no'=>$datas['trade_no'],'payment_no'=>$query['payment_no']];
                $ins=$log->insert($logs);
                $datas=['shop_id'=>$id,'mark'=>'提现','ctime'=>time(),'content'=>'本次提现'.$tx_money.'已提交银行处理~'];
                $insert=Db::name('news')->insert($datas);
                $result['log']=$ins;
                $result['news']=$insert;
            }
            $result['code']=$update;
            $result['msg']='提现成功';
        }
        return json($result);
    }

    public function tixian($data){
        $config = [
            // 必要配置
            'app_id'             => 'wx97e9490b4525584a',
            'mch_id'             => '1488813712',
            'key'                => 'fb0ce200f0ca32dcb9060a37636258a2',   // API 密钥
            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          => ROOT_PATH .'cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
            'key_path'           => ROOT_PATH .'cert/apiclient_key.pem',      // XXX: 绝对路径！！！！
            // 将上面得到的公钥存放路径填写在这里
            'rsa_public_key_path' =>ROOT_PATH .'cert/public-1488813712.pem', // <<<------------------------
            'notify_url'         => 'https://linyi.sdulife.cn/admin/shop/query',     // 你也可以在下单时单独设置来想覆盖它
        ];
        $app = Factory::payment($config);
        $res = $app->transfer->toBankCard([
            'partner_trade_no' => $data['trade_no'],
            'enc_bank_no' => $data['bank_no'], // 银行卡号
            'enc_true_name' => $data['true_name'],   // 银行卡对应的用户真实姓名
            'bank_code' => $data['bank_code'], // 银行编号
            'amount' => $data['amount']*100,  // 单位：分
            'desc' => '商户提现',
        ]);
        return json($res);
    }
    //查看提现状态
    public function trade_find($trade_no){
        $data=$this->query($trade_no);
        dump($data['status']);
    }

    public function trade_find_status($trade_no){
        $data=$this->query($trade_no);
        dump($data);
    }

    public function query($trade_no){
        $config = [
            // 必要配置
            'app_id'             => 'wx97e9490b4525584a',
            'mch_id'             => '1488813712',
            'key'                => 'fb0ce200f0ca32dcb9060a37636258a2',   // API 密钥
            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          => ROOT_PATH .'cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
            'key_path'           => ROOT_PATH .'cert/apiclient_key.pem',      // XXX: 绝对路径！！！！
            // 将上面得到的公钥存放路径填写在这里
            'rsa_public_key_path' =>ROOT_PATH .'cert/public-1488813712.pem', // <<<------------------------
            'notify_url'         =>  'https://linyi.sdulife.cn/admin/shop/query',     // 你也可以在下单时单独设置来想覆盖它
        ];
        $app = Factory::payment($config);
        $partnerTradeNo = $trade_no;
        $result=$app->transfer->queryBankCardOrder($partnerTradeNo);
        return $result;
    }
    public function test(){
        $a=100*(1-config('rate')[0]);
        dump($a);
    }


    //提现记录
    public function money_log(){
        $db=new MoneyLog();
        $list=$db->with('shop')->order('id desc')->paginate();
        foreach ($list as $k=>$v){
            $res=$this->query($v['trade_no']);
            $v['status']=$res['status'];
        }
        $page = $list->render();
        $empty = '暂无';
        $this->assign('empty', $empty);
        $this->assign("page", $page);
        $this->assign("list", $list);
        return $this->fetch();
    }
    //完成提现
    public function finish_money(){
        $id=input('post.id');
        $db=new Money();
        $shop_id=$db->where('id',$id)->value('shop_id');
        $log= new MoneyLog();
        $money=$db->where('shop_id',$shop_id)->find();
        $out_money=$money['out_money']+$money['now_money'];
        $data=['status'=>0,'now_money'=>0,'out_money'=>$out_money,];
        try{
            if(empty($data)){
                throw new \Exception("未检测到数据");
            }
            $op= $db->where('shop_id',$shop_id)->update($data);
            $res = $log->where('id',$id)->update(['money_status'=>0]);
            $result["error"] =false;
            $result["errmsg"] = "恭喜您保存成功";
            $result["data"]=$op;
            $result['res']=$res;
        }catch (\Exception $e){
            $result['error']=true;
            $result['errmsg']=$e->getMessage();
        }
        return json($result);

    }
    //退回
    public function back_money(){
        $id=input('post.id');
        $data=['status'=>0];
        $db=new Money();
        $shop_id=$db->where('id',$id)->value('shop_id');
        $log= new MoneyLog();
        try{
            if(empty($data)){
                throw new \Exception("未检测到数据");
            }
            $op= $db->where('shop_id',$shop_id)->update($data);
            $res = $log->where('id',$id)->update(['money_status'=>0]);
            $result["error"] =false;
            $result["errmsg"] = "恭喜您修改成功";
            $result["data"]=$op;
            $result['res']=$res;
        }catch (\Exception $e){
            $result['error']=true;
            $result['errmsg']=$e->getMessage();;
        }
        return json($result);
    }
}
