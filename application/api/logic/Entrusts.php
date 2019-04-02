<?php
namespace app\api\logic;
use app\api\service\TradeEntrusts;
use think\Db;
use app\common\model\Entrust;
class Entrusts  implements TradeEntrusts
{
    public $data;
    private $userid;
    /*
     * 查询委托
     * integer $page     当前页数,默认1
     * integer $pageSize 当前页大小,默认10
     * string  $sort     排序字段,默认id
     * string  $order    排列顺序，默认desc
     * integer $type     交易类型
     * string  $market   交易市场
     * integer $status   交易状态:1=交易中,2=已成交,3=已撤销,4=撤销申请中
     * */
    public function UserTrade(){
//        获取参数
        $data = $this->data;
        $where = [];
        if($data['market']!= ''){
            $re = $this->VerificationMarket($data['market']);
            if(!$re){
                return jsonArr(LACK_VALUE,[]);
            }
            $where['market'] = $data['market'];
        }
        if($data['type']){
            if($data['type'] == 1){
                $where['deal_type'] = 1;
            }else{
                $where['deal_type'] = 2;
            }
        }
        $where['user_id'] = $this->userid;
        $arr = [];
        if($data['status'] == -1){
            $count = db(ENTRUST)->where($where)->where('status','<>','1')->count();
            $arr = ucfirstArr(db(ENTRUST)->field('*,unix_timestamp(create_time)*1000 as create_time')->where($where)->where('status','<>','1')->page($data['offset'],$data['pageSize'])->order('id desc')->select());
        }else{
            if($data['status']){
                $where['status'] = $data['status'];
            }
            $count = db(ENTRUST)->where($where)->count();
            $arr = ucfirstArr(db(ENTRUST)->field('*,unix_timestamp(create_time)*1000 as create_time')->where($where)->page($data['offset'],$data['pageSize'])->order('id desc')->select());
        }
        return jsonArr(OPERATION_SUCCESS,['data'=>$arr,'total'=>$count]);
    }
    public function showTradeParam($userid = 0){
        $this->data['page'] = request()->param('page',1,'int');
        $this->data['pageSize'] = request()->param('pageSize',10,'int');
        $this->data['sort'] = request()->param('sort','id','string');
        $this->data['order'] = request()->param('order','desc','string');
        $this->data['type'] = request()->param('type',0,'int');
        $this->data['market'] = request()->param('market','','string');
        $this->data['coin'] = request()->param('coin','','string');
        $this->data['offset'] = $this->data['page'];
        $this->data['status'] = request()->param('status',0,'int');
        $this->userid = $userid;
        return $this;
    }
    /*
     * 查询委托
     * string  $market   交易市场
     * */
    public function showTrade($time = 0){
        $re = $this->VerificationMarket($this->data['market']);
        if(!$re){
            return jsonArr(LACK_VALUE,[]);
        }
        if(!$time){
            $arr = (new Socketinterface())->candlestick_user(['market'=>$this->data['market']],false,(new Entrust()),$this->userid);
            return jsonArr(OPERATION_SUCCESS,$arr);
        }
        $where['market'] = $this->data['market'];
        $where['user_id'] = $this->userid;
        if($time){
            $StrTime = strtotime(date("Y-m-d"),$time);
            $whereTime['create_time'] = ['egt',$StrTime];
            $arr = ucfirstArr(db(ENTRUST)->field('*,unix_timestamp(create_time)*1000 as create_time')->where($where)->whereTime('create_time','>=',$StrTime)->select());
        }else{
            $arr = ucfirstArr(db(ENTRUST)->field('*,unix_timestamp(create_time)*1000 as create_time')->where($where)->order('id desc')->select());
        }
        return jsonArr(OPERATION_SUCCESS,$arr);
    }
    /*
     * 验证交易市场
     * */
    public function VerificationMarket($strMarket = ''){
        $market = db(MARKET)->where(['status'=>1])->column('market,id');
        if(isset($market[$strMarket])){
            return true;
        }
        return false;
    }
    /*
     * 验证币种
     * */
    public function VerificationCoin($strCoin = ''){
        $coin = db(COIN)->column('coin_name,id');
        if(isset($coin[$strCoin])){
            return true;
        }
        return false;
    }
    /*
     * 交易中心价格排行查询
     * string  $market   交易市场
     * */
    public function priceTrade($redis = ''){
        $market = $this->data['market'];
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
        return jsonArr(OPERATION_SUCCESS,$list);
    }
    /*
     * 交易比例
     * string   $coin   币种名称
     * */
    public function coinTrade($redis,$type = 1){
        $Coin = [];
        if($this->data['coin'] != ''){
            $re = $this->VerificationCoin($this->data['coin']);
            if(!$re){
                return jsonArr(LACK_VALUE,[]);
            }
            $Coin[] = $this->data['coin'];
        }else{
            $Coin = db(COIN)->column('coin_name');
        }
//        获取兑换比例和平台币
        $change = db(EXCHANGECONFIG)->find();
        if(empty($change)){
            return jsonArr(NO_EXCHANGE,[]);
        }
        $arr = $this->coinTradeAppPc($redis,$Coin,$change,$type);
        return jsonArr(OPERATION_SUCCESS,$arr);
    }
    /*
     * 交易比例-APP/PC
     * */
    private function coinTradeAppPc($redis,$Coin,$change,$type = 1){
        if($type == 1){
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
        }else{
            $arr = [
                'cny'=>[],
                'usd'=>[]
            ];
        }

        foreach($Coin as $v){
            if($v == $change['coin_name']){
                if($type == 1){
                    $arr[0]['rates'][] = [
                        'coin' => $v,
                        'rate' => $change['proportion']
                    ];
                    $arr[1]['rates'][] = [
                        'coin' => $v,
                        'rate' => 1
                    ];
                }else{
                    $arr['cny'][$v]=$change['proportion'];
                    $arr['usd'][$v]=1;
                }

            }else{
                $price = $redis->hGet('market:new:price',$v.'_'.$change['coin_name'].':new:price');
                if(!$price){
                    $price = 1;
                }
                $price = trim($price,'"');
                if($type == 1){
                    $arr[0]['rates'][]=[
                        'coin' => $v,
                        'rate' => $price*$change['proportion']
                    ];
                    $arr[1]['rates'][]=[
                        'coin' => $v,
                        'rate' => $price
                    ];
                }else{
                    $arr['cny'][$v]=$price*$change['proportion'];
                    $arr['usd'][$v]=$price;
                }

            }
        }
        return $arr;
    }
    /*
     * 获取24小时最新成交：最高价、最低价、成交总量
     * string  $market  交易市场
     * */
    public function highLowTrade($redis){
        $re = $this->VerificationMarket($this->data['market']);
        if(!$re){
            return jsonArr(LACK_VALUE,[]);
        }
//        获取数据
        $json_data = $redis->hGet('market:new:twentyfour',$this->data['market']);
        if($json_data){
            $data = json_decode($json_data,true);
            $arr = [
                'market'=>$this->data['market'],
                'hqzrsp'=>$data['hqzrsp'],
                'minPrice'=>$data['minPrice'],
                'sumNumber'=>$data['sumNumber'],
                'maxPrice'=>$data['maxPrice'],
            ];
        }else{
            $arr = [
                'market'=>$this->data['market'],
                'hqzrsp'=>0,
                'minPrice'=>0,
                'sumNumber'=>0,
                'maxPrice'=>0,
            ];
        }
        return jsonArr(OPERATION_SUCCESS,$arr);
    }
    /*
     * 计算涨跌幅
     * */
    public function RiseAndFall($market,$redis){
        $newPrice = $redis->hGet('market:new:price',$market.':new:price');
        if(!$newPrice){
            return ['newPrice'=>0,'change'=>0];
        }
        $risefall = 0;
//        获取24小时收盘价
            $json_change = $redis->hGet('market:new:twentyfour',$market);
            if(!$json_change){
                $risefall = 0;
            }else{
                $newPrice = trim($newPrice,'"');
                $change = json_decode($json_change,true);
                $pioneer = $change['hqzrsp'];
                if($pioneer){
                    $risefall = round((($newPrice - $pioneer) / $pioneer) * 100, 2);
                }
            }
        return ['newPrice'=>$newPrice,'change'=>$risefall];
    }
    /*
     * 成交记录查询
     * */
    public function LogTradeS($redis,$length = 100){
        $re = $this->VerificationMarket($this->data['market']);
        if(!$re){
            return jsonArr(LACK_VALUE,[]);
        }
        $length = $length - ($length*2);
        $datas = $redis->ZRANGE($this->data['market'].':new:deal:price',$length,-1);
        $TradeLog =[];
        if($datas){
            foreach($datas as $k=>$v){
                $vD=json_decode($v,true);
                $TradeLog[] = $vD;
            }
        }
        $TradeLog = array_reverse($TradeLog);
        return jsonArr(OPERATION_SUCCESS,$TradeLog);
    }
}
