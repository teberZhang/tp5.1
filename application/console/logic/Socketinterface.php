<?php
/**
 * Created by PhpStorm.
 * User: 17901
 * Date: 2018/11/6
 * Time: 15:38
 */

namespace app\api\logic;
use app\api\logic\Entrusts;

class Socketinterface
{
    /*
     * 成交记录查询
     * */
    public function LogTrade($json_data,$length,$redis,$coinM,$ExchangeConfig){
        $length = $length - ($length*2);
        $datas = $redis->ZRANGE($json_data['market'].':new:deal:price',$length,-1);
        $TradeLog =[];
        if($datas){
            foreach($datas as $k=>$v){
                $vD=json_decode($v,true);
                $TradeLog[] = $vD;
            }
        }
        $TradeLog = array_reverse($TradeLog);
        if($json_data['type'] == 'pc'){

            return $TradeLog;
        }else{
            $priceSort = $this->priceTrade($redis,$json_data['market']);
            //            兑换比例
            $coin = explode('_',$json_data['market']);
            $res = $this->coinTrade($redis,1,$coinM,$ExchangeConfig,$coin[0],$coin[1]);
            if(empty($res)){
                $priceSort['dealRatioCny'] = 0;
                $priceSort['dealRatioUsd'] = 0;
            }else{
                $priceSort['dealRatioCny'] = $res[0]['rates'][0]['rate'];
                $priceSort['dealRatioUsd'] = $res[1]['rates'][0]['rate'];
            }
        }
        return ['dealRecord'=>$TradeLog,'priceSort'=>$priceSort];
    }
    /*
     * 交易中心价格排行查询
     * string  $market   交易市场
     * */
    public function priceTrade($redis,$market = ''){
        $buysorc = $redis->ZREVRANGE($market.':buy:price:sort',0,100);
        $bids = [];
        if($buysorc){
            foreach($buysorc as $v){
                $str= json_decode($v,true);
                $bids[] = json_decode($str,true);
            }
        }
//        $bids = array_reverse($bids);
        //委托卖
        $sellsorc = $redis->zRange($market.':sell:price:sort',0,100);
        $asks = [];
        if($sellsorc){
            foreach($sellsorc as $v){
                $str= json_decode($v,true);
                $asks[] = json_decode($str,true);
            }
        }
        $asks = array_reverse($asks);
        // 最新成交价
        $newPrice = $redis->hGet('market:new:price',$market.':new:price');
        $risefall = 0;
        if($newPrice){
            $newPrice = trim($newPrice,'"');
//        获取24小时收盘价
            $json_change = $redis->hGet('market:new:twentyfour',$market);
            if(!$json_change){
                $risefall = 0;
            }else{
                $change = json_decode($json_change,true);
                $pioneer = $change['hqzrsp'];
                if($pioneer){
                    $risefall = round((($newPrice - $pioneer) / $pioneer) * 100, 2);
                }
            }
        }
        //声明
        $list = [
            'sellSort'=> [],
            'buySort'=> [],
            'newPrice'=>is_null($newPrice)?0:$newPrice,
            'change'=>$risefall
        ];
        //委托排列转换
        foreach ($asks as $a_price=>$a_num){
            if($a_num !=0){
                $list['sellSort'][] = [$a_num['price'],$a_num['entrustNumber']];
            }
        }
        foreach ($bids as $b_price=>$b_num){
            if($b_num !=0) {
                $list['buySort'][] = [$b_num['price'],$b_num['entrustNumber']];
            }
        }
        return $list;
    }
    /*
     * 交易比例
     * string   $coin   币种名称
     * */
    public function coinTrade($redis,$type = 1,$coinM,$ExchangeConfig,$coin,$coin_suf = ''){
        $Coin = [];
        if($coin != ''){
            $Coin[] = $coin;
        }else{
            $Coin =$coinM->where(['status'=>1])->column('coin_name');
        }
//        获取兑换比例和平台币
        $change = $ExchangeConfig->find();
        if(empty($change)){
            return [];
        }
        $arr = [];
        if($type == 1){
            $arr = $this->coinTradeApp($redis,$Coin,$change,$coin_suf);
        }else{
            $arr = $this->coinTradePc($redis,$Coin,$change);
        }

        return $arr;
    }
    /*
     * 交易比例-APP
     * */
    private function coinTradeApp($redis,$Coin,$change,$coin_suf){
        $arr = [
            0=>[
                'legalTender'=>'cny',
                'rates'=>[]
            ],
            1=>[
                'legalTender'=>'usd',
                'rates'=>[]
            ]
        ];
        foreach($Coin as $v){
            if($v == $change['coin_name'] || $coin_suf == $change['coin_name']){
                $arr[0]['rates'][] = [
                    'coin' => $v,
                    'rate' => $change['proportion']
                ];
                $arr[1]['rates'][] = [
                    'coin' => $v,
                    'rate' => 1
                ];
            }else{
                $price = $redis->hGet('market:new:price',$v.'_'.$change['coin_name'].':new:price');
                if(!$price){
                    $price = 1;
                }else{
                    $price = trim($price,'"');
                }
                $arr[0]['rates'][]=[
                    'coin' => $v,
                    'rate' => $price*$change['proportion']
                ];
                $arr[1]['rates'][]=[
                    'coin' => $v,
                    'rate' => $price
                ];
            }
        }
        return $arr;
    }
    /*
     * 交易比例-PC
     * */
    private function coinTradePc($redis,$Coin,$change){
            $arr = [
                'cny'=>[],
                'usd'=>[]
            ];
        foreach($Coin as $v){
            if($v == $change['coin_name']){
                $arr['cny'][]=[$v=>$change['proportion']];
                $arr['usd'][]=[$v=>1];
            }else{
                $price = $redis->hGet('market:new:price',$v.'_'.$change['coin_name'].':new:price');
                if(!$price){
                    $price = 1;
                }else{
                    $price = trim($price,'"');
                }
                $arr['cny'][]=[$v=>$price*$change['proportion']];
                $arr['usd'][]=[$v=>$price];
            }
        }
        return $arr;
    }
    //    APP交易市场
    public function appMarket($datas,$Market,$Coin,$CollectMarket,$redis,$ExchangeConfig){
        $uuid = $this->LoginUrlDb($redis,$datas['sockets']);
        $collect = [];
        $market = '';
        if($uuid){
            $market = $CollectMarket->where(['user_id'=>$uuid])->value('market');
            $collect = explode(',',trim($market,','));
        }elseif ($datas['market'] == 'optional' && !$market){
            return [];
        }
        if($datas['market'] == 'optional'){
            $Mdata = $Market->where('market','in',$collect)->where('status','=',1)->select();
        }else{
            $Mdata = $Market->where(['coin_suf'=>$datas['market'],'status'=>1])->select();
        }
        $Entrusts = new Entrusts();
        $data = [];
        foreach($Mdata as $k=>$v){
//            趋势图
            $data[$k]['tendency'] = $v['tendency'];
//            涨跌幅
            $RiseAndFall = $Entrusts->RiseAndFall($v['market'],$redis);
            $data[$k]['change'] = $RiseAndFall['change'];
//            是否收藏
            if(in_array($v['market'],$collect)){
                $data[$k]['collect'] = 1;
            }else{
                $data[$k]['collect'] = 0;
            }
//            兑换比例
            $res = $this->coinTrade($redis,1,$Coin,$ExchangeConfig,$v['coin_pre'],$v['coin_suf']);
            if(empty($res)){
                $data[$k]['dealRatioCny'] = 0;
                $data[$k]['dealRatioUsd'] = 0;
            }else{
                $data[$k]['dealRatioCny'] = $res[0]['rates'][0]['rate'];
                $data[$k]['dealRatioUsd'] = $res[1]['rates'][0]['rate'];
            }
//            交易市场
            $data[$k]['market'] = $v['market'];
//            最新成交价
            $data[$k]['recentQuotation'] = $RiseAndFall['newPrice'];
//            24小时成交总量
            $hGet = $redis->hGet('market:new:twentyfour',$v['market']);
            if(!$hGet){
                $data[$k]['volume'] = 0;
            }else{
                $data[$k]['volume'] = json_decode($hGet,true)['sumNumber'];
            }
        }
            return $data;

    }
    /*
     * 用户委托
     * */
    public function candlestick_user($data,$redis,$Entrust,$uuid = 0){
        if(!$uuid){
            $uuid = $this->LoginUrlDb($redis,$data['sockets']);
        }
        if(!$uuid){
            return [];
        }
        if($data['market']){
            $where['market'] = $data['market'];
        }
        if(!isset($data['length']) || $data['length'] <= 0){
            $data['length'] = 50;
        }
        $where['user_id'] = $uuid;
        $trade = $Entrust->where($where)->where(['status'=>1])->limit($data['length'])->order('create_time desc')->select()->toArray();
        $data['length'] = 50;
//        历史成交记录
        $tradelog = $Entrust->where('market',$data['market'])->where('user_id = '.$uuid.' AND status > 1')->limit($data['length'])->order('create_time desc')->select()->toArray();
        $datas = array_merge($trade,$tradelog);
        $arr = camelize($datas);
        foreach($arr as &$v){
            if($v['tradeTotal'] > 0){
                $mum_log = floor($v['tradeTotal'] / $v['tradeNumber']*100)/100;
                $v['averagePrice'] = num($mum_log);
            }else{
                $v['averagePrice'] = 0;
            }
            $v['price'] = num($v['price']);
            $v['total'] = num($v['total']);
            $v['tradeNumber'] = num($v['tradeNumber']);
            $v['tradeTotal'] = num($v['tradeTotal']);
            $v['entrustNumber'] = num($v['entrustNumber']);
            $v['createTime'] = strtotime($v['createTime'])*1000;
        }
        return $arr;
    }
    /*
     * 用户ID获取
     * */
    public function LoginUrlDb($redis,$sid){
        $user = $redis->get('shiro:session:'.$sid);
        if($user){
            return json_decode($user,true)['id'];
        }else{
            return 0;
        }
    }
}
