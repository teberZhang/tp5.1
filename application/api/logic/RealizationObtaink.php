<?php

namespace app\api\logic;
use app\api\service\ObtainkInterface;
use app\common\library\Redis;
/*
 * K线实现类
 * */
class RealizationObtaink implements ObtainkInterface
{
    private $Redis;
    private $data;
    private $type = [
        60=>1,
        300=>1,
        900=>1,
        1800=>1,
        3600=>1,
        86400=>1,
        604800=>1
    ];
    public function __construct(Redis $Redis)
    {
        $this->Redis = $Redis;
    }
    /*
     * 获取K线数据
     * */
    public function Obtain(){
//        获取参数
        $res = $this->parameter()->VerificationParameter();
        if(!empty($res)){
            return $res;
        }
//        获取K线数据
        $res = $this->ObtainK();
        return $res;
    }
    /*
     * 获取K线最新数据
     * */
    public function newObtain(){
//        获取参数
        $res = $this->parameter()->VerificationParameter();
        if(!empty($res)){
            return $res;
        }
//        获取K线数据
        $res = $this->newObtainK();
        return $res;
    }
    /*
     * 获取数据
     * */
    public function newObtainK(){

        $time = time();
        $timeK = TimeK($time)[$this->data['type']];
        $minutes = $time-$timeK;
        $typeTwo = '';
        if($minutes > 5){
            $typeTwo = $this->Redis->hGet($this->data['market'].':trader:k:top',$this->data['type']);
        }else{
            $typeTwo = $this->Redis->hGet($this->data['market'].':trader:k:two',$this->data['type']);
        }
        if($typeTwo){
            $proportion = $this->proportion(explode('_',$this->data['market'])[0],$this->data['assist']);
            $strTwo = trim($typeTwo,'"');
            $arr = explode(',',$strTwo);
            if(($time-($arr[0]/1000)) > ($this->data['type']+5)){
                $price = $arr[4]*$proportion;
                $data[] = [
                    $timeK*1000,
                    $price,
                    $price,
                    $price,
                    $price,
                    0
                ];
            }else{
                $data[] = [
                    $arr[0],
                    $arr[1]*$proportion,
                    $arr[2]*$proportion,
                    $arr[3]*$proportion,
                    $arr[4]*$proportion,
                    $arr[5],
                ];
            }

            return jsonArr(OPERATION_SUCCESS,$data);
        }

        return jsonArr(OPERATION_SUCCESS,[]);
    }
    /*
     * 获取参数
     * */
    private function parameter(){
        $this->data['market'] = request()->param('market','','string');
        $this->data['type'] = request()->param('type',60,'int');
        $this->data['assist'] = request()->param('assist','cny','string');
        return $this;
    }
    /*
     * 验证参数
     * */
    public function VerificationParameter(){
        $market = db(MARKET)->where(['status'=>1])->column('market,id');
        if(!isset($market[$this->data['market']])){
            return jsonArr(NO_TRADE,[]);
        }
        if(!isset($this->type[$this->data['type']])){
            return jsonArr(NO_RETURN_DATA,[]);
        }
        if($this->data['assist'] != 'cny'){
            $this->data['assist'] = 'usd';
        }
        return [];
    }
    /*
     * 获取数据
     * */
    public function ObtainK(){
        $Kdata = db(TRADEK.$this->data['market'])->where('chart_type',$this->data['type'])->order('id desc')->limit(500)->column('data');
        $Kdata = array_reverse($Kdata);
        $data = [];
//        $proportion = $this->proportion(explode('_',$this->data['market'])[0],$this->data['assist']);

        $assist = input('assist');  //换算的货币种类
        $ratio = Db(EXCHANGECONFIG)->find();    //平台币比例信息
        $coin = explode('_',$this->data['market']); //交易市场交易对

        //当前交易市场最新交易比例
        if($coin[1] == $ratio['coin_name']){
            $recent_quotation = 1;
        }else{
            $recent_quotation = Db(MARKET)->where(['market'=>$coin[1] . '_' . $ratio['coin_name']])->value('recent_quotation')?:1;
        }

        //交易市场最新交易比例与平台币的交易比例
        if($assist == 'cny'){
            $proportion = $recent_quotation * $ratio['proportion'];
        }else{
            $proportion = $recent_quotation;
        }

        $assist = input('assist');
        foreach($Kdata as $v){
            $arr = explode(',',$v);
            if($assist == 'cny'){
                $data[] = [
                    $arr[0],
                    bcmul($arr[1],$proportion,3),
                    bcmul($arr[2],$proportion,3),
                    bcmul($arr[3],$proportion,3),
                    bcmul($arr[4],$proportion,3),
                    $arr[5],
                ];
            }else{

                $data[] = [
                    $arr[0],
                    $arr[1],
                    $arr[2],
                    $arr[3],
                    $arr[4],
                    $arr[5],
                ];
            }
        }
        $rData = $this->Redis->hGet($this->data['market'].':trader:k:top',$this->data['type']);
        if($rData){
            $time = time();
            $strTwo = trim($rData,'"');
            $arr = explode(',',$strTwo);
            $Karr = end($data);
            if(isset($Karr[0]) && ($Karr[0]/1000) >= ($arr[0]/1000)){
                if($assist == 'cny'){
                    $price = $arr[4]*$proportion;
                    $timeK = TimeK($time)[$this->data['type']];
                    $data[] = [
                        $timeK*1000,
                        $price,
                        $price,
                        $price,
                        $price,
                        0
                    ];
                }else{
                    $price = $arr[4];
                    $timeK = TimeK($time)[$this->data['type']];
                    $data[] = [
                        $timeK*1000,
                        $price,
                        $price,
                        $price,
                        $price,
                        0
                    ];
                }
            }else{
                $data[] = [
                    $arr[0],
                    bcmul($arr[1],$proportion,3),
                    bcmul($arr[2],$proportion,3),
                    bcmul($arr[3],$proportion,3),
                    bcmul($arr[4],$proportion,3),
                    $arr[5],
                ];
            }

        }
        return jsonArr(OPERATION_SUCCESS,$data);
    }
    private function proportion($coin = '',$key = 'cny'){
        $Entrusts = new Entrusts();
        $Entrusts->data['coin'] = $coin;
        $Coin = $Entrusts->coinTrade($this->Redis,2);
        return $Coin['data'][$key][$coin];
    }
}
