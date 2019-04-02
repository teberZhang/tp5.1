<?php

namespace app\api\logic;
use app\api\service\MarketInterface;

class RealizaMarket implements MarketInterface
{
    /*
     *获取交易市场列表
     * */
    public function marketList(){
        $data = db(MARKET)->field('change,coin_pre,coin_suf,id,market,max_price,min_price,number_precision,price_precision,recent_quotation,recommend,status,tendency,volume')->where(['status'=>1])->select();
        $coin = db(COIN)->column('coin_name,imgs');
        foreach($data as &$v){
            $v['images'] = $coin[$v['coin_pre']];
        }
        return jsonArr(OPERATION_SUCCESS,camelize($data));
    }
    /*
     *获取交易区币种名称
     * */
    public function marketUnique(){
        $data = $this->CoinSuf();
        return jsonArr(OPERATION_SUCCESS,$data);
    }
    /*
     * 交易市场去重
     * */
    private function CoinSuf(){
        $data = db(MARKET)->where(['status'=>1])->group('coin_suf')->column('coin_suf');
        $exCoin = db(EXCHANGECONFIG)->find();
        $Cdata = [];
        if(in_array($exCoin['coin_name'],$data)){
            $Cdata[] = $exCoin['coin_name'];
            $key = array_search($exCoin['coin_name'],$data);
            unset($data[$key]);
        }
        foreach($data as $v){
            $Cdata[] = $v;
        }
        return $Cdata;
    }
    /*
     *获取交易基准币
     * */
    public function marketCoinSuf(){
        $coin = $this->CoinSuf();
        $Mdata = db(MARKET)->where(['status'=>1])->select();
        $data = [];
        foreach($coin as $k=>$v){
            $data[$k]['coinSuf'] = $v;
            foreach($Mdata as $mv){
                if($mv['coin_suf'] == $v){
                    $data[$k]['listMarket'][] = $mv['market'];
                }
            }
        }
        return jsonArr(OPERATION_SUCCESS,$data);
    }
    /*
     *委托提交小数位限制位数查询
     * */
    public function MarketDigit(){
        $res = $this->parameter()->VerificationParameter('market');
        if(!empty($res)){
            return $res;
        }
        $market = db(MARKET)->where(['market'=>$this->data['market']])->find();
        $data=[
            'market'=>$market['market'],
            'numberPrecision'=>$market['number_precision'],
            'pricePrecision'=>$market['price_precision']
        ];
        return jsonArr(OPERATION_SUCCESS,$data);
    }
    /*
     * 获取参数
     * */
    private function parameter(){
        $this->data['market'] = request()->param('market','','string');
        $this->data['coin_suf'] = request()->param('suf','','string');
        return $this;
    }
    /*
     * 参数验证
     * */
    private function VerificationParameter($str = ''){
        $market = db(MARKET)->where(['status'=>1])->column($str.',id');
        if(!isset($market[$this->data[$str]])){
            return jsonArr(NO_RETURN_DATA,[]);
        }
        return [];
    }
    /*
     * 基准币对应的交易市场信息查询
     * */
    public function MarketSuf(){
        $res = $this->parameter()->VerificationParameter('coin_suf');
        if(!empty($res)){
            return $res;
        }
        $data = db(MARKET)->where(['coin_suf'=>$this->data['coin_suf'],'status'=>1])->column('market');
        return jsonArr(OPERATION_SUCCESS,$data);
    }
    /*
     *收藏、取消收藏交易市场
     * */
    public function collect($userid){
        $res = $this->parameter()->VerificationParameter('market');
        if(!empty($res)){
            return $res;
        }
//        操作方法
        return $this->operation($userid);
    }
    /*
     * 收藏/取消操作方法
     * */
    public function operation($userid){
        $CMdata = db(COLLECTMARKET)->where(['user_id'=>$userid])->find();
        $time = date('Y-m-d H:i:s',time());
        try{
            if(empty($CMdata)){
                db(COLLECTMARKET)->insert([
                    'user_id'=>$userid,
                    'market'=>$this->data['market'].',',
                    'create_time'=>$time,
                    'update_time'=>$time
                ]);
            }else{
                $marketStr = '';
                $Mdata = explode(',',$CMdata['market']);
                if(in_array($this->data['market'],$Mdata)){
                    $marketStr = str_replace($this->data['market'].',','',$CMdata['market']);
                }else{
                    $marketStr = $CMdata['market'] . $this->data['market'] . ',';
                }
                db(COLLECTMARKET)->where(['id'=>$CMdata['id']])->update(['market'=>$marketStr,'update_time'=>$time]);
            }
            return jsonArr(OPERATION_SUCCESS,[]);
        } catch (\Exception $e) {
            return jsonArr(OPERATION_ERROR,[]);
        }

    }
    /*
     *查询收藏的交易市场
     * */
    public function forCollect($userid){
        $market = db(COLLECTMARKET)->where(['user_id'=>$userid])->value('market');
        if($market){
            $data = explode(',',trim($market,','));
        }else{
            $data = [];
        }
        return jsonArr(OPERATION_SUCCESS,$data);
    }
}
