<?php

namespace app\api\logic;
use app\api\service\CoinInterface;

/*
 * 币种管理实现类
 * */
class RealizaCoin implements CoinInterface
{
    private $data = [];
    private $userid = 0;
    /*
     *提币：资产、提币手续费查询
     * */
    public function balance($userid){
        $res = $this->parameter($userid)->VerificationParameter('id');
        if(!empty($res)){
            return $res;
        }
        return $this->fee_balance();
    }
    /*
     * 接收参数
     * */
    private function parameter($userid){
        $this->data['id'] = request()->param('id',0,'int');
        $this->data['type'] = request()->param('type',1,'int');
        $this->userid = $userid;
        return $this;
    }
    /*
     * 参数验证
     * */
    private function VerificationParameter($str = ''){
        if(!$this->userid){
            return jsonArr(NO_LOGIN,[]);
        }
        $market = db(COIN)->column($str);
        if(!in_array($this->data[$str],$market)){
            return jsonArr(NO_RETURN_DATA,[]);
        }
        return [];
    }
    /*
     * 获取手续费和可用余额
     * */
    private function fee_balance(){
        $coin = db(COIN)->where(['id'=>$this->data['id']])->find();
        $data['outflowFee'] = $coin['outflow_fee'] == null?0:$coin['outflow_fee'];
        $data['balance'] = num(db(USERCOIN)->where(['user_id'=>$this->userid])->value($coin['coin_name']));
        $data['outflowMax'] = floatval($coin['outflow_max']);
        $data['outflowMin'] = floatval($coin['outflow_min']);
        return jsonArr(OPERATION_SUCCESS,$data);
    }
    /*
     *充值提币,币种查询
     * */
    public function CoinQuery(){
        $type = request()->param('type',0,'int');
        $where = [];
        //只查询可以正常转入的币种
        if($type == 1){
            $where['inflow_type'] = 1;
        }
        //只查询可以正常转出的币种
        if($type == 2){
            $where['outflow_type'] = 1;
        }
        $data = db(COIN)
                ->field('id,coin_name as coinName,imgs,inflow_confirm_number as inflowConfirmNumber')
                ->where('coin_type','<>',3)
                ->where($where)
                ->select();
        return jsonArr(OPERATION_SUCCESS,$data);
    }
}
