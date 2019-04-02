<?php

namespace app\api\logic;
use app\common\library\Redis;
use app\api\service\TradeInterface;
use think\Db;
/*
 * 交易市场委托实现类
 * */
class RealizaTrade implements TradeInterface
{
    private $data    = [];
    public  $Trade   = [];
    private $market  = [];
    private $jsonArr = [];
    public $userid  = 0;
    private $UserCoin = [];
    private $status  = false;
    private $Cstatus = false;
    /*
     * 提交委托
     * */
    public function transaction($userid){
        $redis = new Redis();
        $this->userid = $userid;
        //        接收参数并验证
        $res = $this->parameter()->VerificationParameter(new Entrusts);
        if(!empty($res)){
            return $res;
        }
//        验证交易市场和币种相关规则
        $res = $this->TradeMarketRule($redis)->TradeCoinRule();
        if(!empty($res)){
            return $res;
        }
//        验证用户相关信息
        $res = $this->TradeUser();
        if(!empty($res)){
            return $res;
        }
//        写入数据
        return $this->TradeMysql();

    }

    /*
     * 获取参数
     * */
    private function parameter(){
        $this->data['pwd'] = request()->param('password','','string');
        $this->Trade = [
            'id'=>request()->param('id',0,'int'),
            'market'=>request()->param('market','','string'),//交易市场
            'price'=>request()->param('price',0),//单价
            'entrustNumber'=>request()->param('entrustNumber',0),//数量
            'dealType'=>request()->param('dealType',0,'int')//交易类型
        ];
        return $this;
    }
    /*
     * 验证参数的正确性
     * */
    private function VerificationParameter($Entrusts){
        if(!$this->userid){
            return jsonArr(NO_LOGIN,[]);
        }
        $market = isset($this->Trade['market'])?$this->Trade['market']:'';
//        验证交易市场是否存在
        $re = $Entrusts->VerificationMarket($market);
        if(!$re){
            return jsonArr(NO_RETURN_DATA,[]);
        }
        $this->market = db(MARKET)->where(['market'=>$market])->find();
        if($this->market['trade_type'] != 1){
            return jsonArr(NO_MARKET_TYPE,[]);
        }
        return $this->VerificationTrade();
    }
    /*
     * 验证交易数据的正确性
     * */
    private function VerificationTrade(){
        $market = $this->market;
        $number = $this->Trade['entrustNumber'];
        $unitPrice = $this->Trade['price'];
        $type = $this->Trade['dealType'];
        if($market['trade_start_time'] && $market['trade_end_time']){
            // 获取每日交易开始和结束时间戳
            $start_time = explode(' ',$market['trade_start_time']);
            $end_time = explode(' ',$market['trade_end_time']);
            $trade_start_time = strtotime(date('Y-m-d').' '.$start_time[1]);
            $trade_end_time = strtotime(date('Y-m-d').' '.$end_time[1]);
            // 获取当前时间戳
            $cur_time = time();
            if($trade_start_time < $cur_time && $trade_end_time < $cur_time){
                return jsonArr(NO_TRADETIME,[]);
            }
        }
        //交易市场配置检验
//        价格类型
        if (!check($unitPrice, 'double')) {
            return jsonArr(NO_TRADEPRICE,[]);
        }
//        价格位数
        $int = $market['price_precision']?$market['price_precision']:6;
        if(sprintf( "%.".$int."f ",$unitPrice*1) != $unitPrice*1){
            return jsonArr(NO_TRADEPOINT,[]);
        }
//        数量类型
        if (!check($number, 'double')) {
            return jsonArr(NO_TRADENUM,[]);
        }
//        数量位数
        $int = $market['number_precision']?$market['number_precision']:6;
        if(sprintf( "%.".$int."f ",$number*1) != $number*1){
            return jsonArr(NO_TRADENUMPOINT,[]);
        }
//        买卖类型
        if ($type != 1 && $type != 2) {
            return jsonArr(NO_TRADETYPE,[]);
        }
        $this->status = true;
        return [];
    }
    /*
     * 验证交易市场规则
     * */
    private function TradeMarketRule($redis){
        if(!$this->status){
            $this->jsonArr = jsonArr(EXCEPTION,[]);
            return $this;
        }
        $market = $this->market;
        if ($this->Trade['dealType'] == 1) {
//            最小买入价
            if($market['buy_min_price'] && $this->Trade['price'] < $market['buy_min_price']){
                $this->jsonArr = jsonArr(BUYMINPRICE,[],['price'=>$market['buy_min_price']]);
                return $this;
            }
//            最小买入量
            if($market['buy_min'] && $this->Trade['entrustNumber'] < $market['buy_min']){
                $this->jsonArr = jsonArr(BUY_SELL,[],['type'=>'小买入','num'=>$market['buy_min']]);
                return $this;
            }
        }else if ($this->Trade['dealType'] == 2) {
//            最小卖出量
            if($market['sell_min'] && $this->Trade['entrustNumber'] < $market['sell_min']){
                $this->jsonArr = jsonArr(BUY_SELL,[],['type'=>'小卖出','num'=>$market['sell_min']]);
                return $this;
            }
//            最大卖出量
            if($market['sell_max'] && $this->Trade['entrustNumber'] > $market['sell_max']){
                $this->jsonArr = jsonArr(BUY_SELL,[],['type'=>'大卖出','num'=>$market['sell_max']]);
                return $this;
            }
        }else{
            $this->jsonArr = jsonArr(NO_TRADETYPE,[]);
            return $this;
        }
        // 获取昨日最后一笔交易
        $hget = $redis->hGet('market.new.twentyfour',$market['market']);
        $hou_price = 0;
        if($hget != null){
            $hou_price = json_decode($hget,true)['hqzrsp'];
        }
        if ($hou_price) {
            // 获取涨幅度
            if ($market['rise']) {
                // 获取最大涨幅度:昨日交易价格的百分之一*(允许涨幅百分比+百分百)=今日委托最高价
                $rise = round(($hou_price / 100) * (100 + $market['rise']),2);
                if ($rise < $this->Trade['price']) {
                    $this->jsonArr = jsonArr(TRADERISE,[]);
                    return $this;
                }
            }
            // 获取跌幅度
            if ($market['fall']) {
                // 获取最大跌幅度:昨日交易价格的百分之一*(百分百-允许跌幅百分比)=今日委托最低价
                $fall = round(($hou_price / 100) * (100 - $market['fall']), 2);
                if ($this->Trade['price'] < $fall) {
                    $this->jsonArr = jsonArr(TRADEFALL,[]);
                    return $this;
                }
            }
        }
        return $this;
    }
    /*
     * 验证币种规则
     * */
    public function TradeCoinRule(){
        if(!empty($this->jsonArr)){
            return $this->jsonArr;
        }
        $coin_pre = db(COIN)->where(['coin_name'=>$this->market['coin_pre']])->find();//货币A(商品)
        $coin_suf = db(COIN)->where(['coin_name'=>$this->market['coin_suf']])->find();//货币B(钱)
        if(empty($coin_pre) || empty($coin_suf)){
            return jsonArr(NO_COIN,[]);
        }
        if($coin_pre['status'] != 1){
            return jsonArr(NO_COIN_STATUS,[],['CoinName'=>strtoupper($coin_pre['coin_name'])]);
        }
        if($coin_suf['status'] != 1){
            return jsonArr(NO_COIN_STATUS,[],['CoinName'=>strtoupper($coin_suf['coin_name'])]);
        }
        $this->Trade['coin_pre'] = $coin_pre['coin_name'];
        $this->Trade['coin_suf'] = $coin_suf['coin_name'];
        $this->Cstatus = true;
        return [];
    }
    /*
     * 验证用户信息
     * */
    public function TradeUser(){
        $UserId = $this->userid;
        $password = $this->data['pwd'];
        $user = Db('User')->where(array('id' => $UserId))->find();
        if(empty($user)){
            return jsonArr(NO_USER,[]);
        }
        if(!$user['is_remember']){
            if(!$password){
                return jsonArr(NO_USER_K_PWD,[]);
            }
            if (md5($password.$user['salt']) != $user['transaction_password']) {
                return jsonArr(NO_USER_PWD,[]);
            }
        }
        /*检验交易市场/币种/域名类型状态*/
        $this->Trade['mum'] = bcmul($this->Trade['entrustNumber'],$this->Trade['price'],16);    //总价
        // /*检验检验货币配置丨比对用户持有货币*/
        $coin = $this->Trade['dealType'] == 1?$this->market['coin_suf']:$this->market['coin_pre'];
        // 获取用户币种余额
        $this->UserCoin = db(USERCOIN)->where(['user_id'=>$UserId])->find();
        if($this->Trade['dealType'] == 2){
            if ($this->UserCoin[$coin] < $this->Trade['entrustNumber']) {
                return jsonArr(NO_USERCOIN,[],['CoinName'=>strtoupper($coin)]);
            }
            if($user['exchange_flag']){
                $this->Trade['feeRate'] = $this->market['fee_sell']/100;
            }
        }
        //挂买单
        if($this->Trade['dealType'] == 1){
            if ($this->UserCoin[$coin] < $this->Trade['mum']) {
                return jsonArr(NO_USERCOIN,[],['CoinName'=>strtoupper($coin)]);
            }
            if($user['exchange_flag']){
                $this->Trade['feeRate'] = $this->market['fee_buy']/100;
            }
        }

        return [];
    }
    /*
     * 写入数据
     * */
    public function TradeMysql(){
//        启动事务
        Db::execute('set autocommit=0');
        Db::startTrans();
        try {
//            修改用户余额
            $row = $this->UpUserCoin();
//            写入委托记录
            $orderSn = orderSn();
            $id = $this->insertTrade($orderSn);
            if(!$id){
                // 回滚事务
                Db::rollback();
                return jsonArr(OPERATION_ERROR,[]);
            }
//            写入财务
            $row[] = $this->insertFinance($orderSn,$id);
            if(!check_array($row)){
                // 回滚事务
                Db::rollback();
                return jsonArr(OPERATION_ERROR,[]);
            }
            $Rabbit = new \app\common\library\RabbitPublisher();
            $time = time();
            $data = [
                'ab_sort'=>$time,
                'coin_pre'=>$this->Trade['coin_pre'],
                'coin_suf'=>$this->Trade['coin_suf'],
                'createTime'=>$time,
                'dealType'=>$this->Trade['dealType'],
                'entrustNumber'=>$this->Trade['entrustNumber'],
                'feeRate'=>$this->Trade['feeRate'],
                'id'=>$id,
                'market'=>$this->Trade['market'],
                'orderNumber'=>$orderSn,
                'price'=>$this->Trade['price'],
                'userId'=>$this->userid,
                'userCoinId'=>$this->UserCoin['id']
            ];
            $Rabbit->init($data);
            // 提交事务
            Db::commit();
            Db::execute('set autocommit=1');
            return jsonArr(OPERATION_SUCCESS,[]);
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return jsonArr(OPERATION_ERROR,[]);
        }
    }
    /*
     * 修改用户余额
     * */
    private function UpUserCoin(){
        $row = [];
//            修改用户余额
        if($this->Trade['dealType'] == 1){
            $row[] = Db::table(PREFIX.USERCOIN)->where(['user_id'=>$this->userid])->setDec($this->Trade['coin_suf'],$this->Trade['mum']);
            $row[] = Db::table(PREFIX.USERCOIN)->where(['user_id'=>$this->userid])->setInc($this->Trade['coin_suf'].'_frozen',$this->Trade['mum']);
        }else{
            $row[] = Db::table(PREFIX.USERCOIN)->where(['user_id'=>$this->userid])->setDec($this->Trade['coin_pre'],$this->Trade['entrustNumber']);
            $row[] = Db::table(PREFIX.USERCOIN)->where(['user_id'=>$this->userid])->setInc($this->Trade['coin_pre'].'_frozen',$this->Trade['entrustNumber']);
        }
        return $row;
    }
    /*
     * 写入委托记录
     * */
    public function insertTrade($orderSn = ''){
        return Db::table(PREFIX.ENTRUST)->insertGetId([
            'user_id'=>$this->userid,
            'order_number'=>$orderSn,
            'market'=>$this->market['market'],
            'deal_type'=>$this->Trade['dealType'],
            'price'=>$this->Trade['price'],
            'entrust_number'=>$this->Trade['entrustNumber'],
            'total'=>$this->Trade['mum'],
            'create_time'=>date('Y-m-d H:i:s',time()),
            'status'=>1
        ]);
    }
    /*
     * 写入财务
     * */
    public function insertFinance($orderSn = '',$id = 0){
        //当前时间
        $creation_time = time();
        //唯一ID
        $Particle = new \app\common\library\Particle(31,31);
        $next_id = $Particle->nextId();
        //流水号 = 年月日(8位) + 行为操作(4位) + 唯一ID(10-20位)
        $transaction_number = date('Ymd',$creation_time).str_pad(2,4,0,STR_PAD_LEFT).$next_id;
        return Db::table(PREFIX.FINANCEDETAIL)->insertGetId([
            'user_id' => $this->userid,
            'order_id' => $id,
            'transaction_number' => $transaction_number,//交易流水号
            'order_number' => $orderSn,
            'market' => $this->market['market'],
            'operation' => 2,
            'deal_type' => 4,
            'transaction_currency' => $this->Trade['dealType'] == 1?$this->Trade['coin_suf']:$this->Trade['coin_pre'],
            'deal_total' => $this->Trade['dealType'] == 1?$this->Trade['mum']:$this->Trade['entrustNumber'],
            'create_time' => date('Y-m-d H:i:s',time()),
            'status' => 1
        ]);
    }
    /*
     * 撤销委托
     * */
    public function RevokeTrade($userid){
        $this->userid = $userid;
        //        接收参数并验证
        $res = $this->parameter()->ContrastTradeUser();
        if(!empty($res)){
            return $res;
        }
        return $this->UpTradeStatus();
    }
    /*
     * 验证委托记录
     * */
    public function ContrastTradeUser(){
        if(!$this->userid){
            return jsonArr(NO_LOGIN,[]);
        }
        if(!$this->Trade['id']){
            return jsonArr(LACK_VALUE,[]);
        }
        $Tdata = db(ENTRUST)->where(['id'=>$this->Trade['id'],'user_id'=>$this->userid])->find();
        //交易市场1
        $MarketInfo = db(MARKET)->where(['market'=>$Tdata['market']])->find();
        /*检验交易市场/币种/域名类型状态*/
        if (!$MarketInfo) {
            return jsonArr(NO_TRADE,[]);
        }elseif (!$MarketInfo['status']) {
            return jsonArr(NO_TRADE_STATUS,[]);
        }
        if(empty($Tdata)){
            return jsonArr(NO_RETURN_DATA,[]);
        }
        if($Tdata['status'] == 2 || $Tdata['status'] == 4 || $Tdata['status'] == 3){
            return jsonArr(NO_TRADE_UP_STATUS,[]);
        }
        return [];
    }
    /*
     * 修改委托状态
     * */
    public function UpTradeStatus(){
        Db::execute('set autocommit=0');
        Db::startTrans();
        try{
            $Tdata = Db::table(PREFIX.ENTRUST)->where(['id'=>$this->Trade['id']])->lock(true)->find();
            if($Tdata['status'] == 2 || $Tdata['status'] == 4 || $Tdata['status'] == 3){
                return jsonArr(NO_TRADE_UP_STATUS,[]);
            }
            $re = Db::table(PREFIX.ENTRUST)->where(['id'=>$this->Trade['id']])->update(['status'=>4]);
            if($re){
                $data = [
                    'dealType'=>$Tdata['deal_type'],
                    'id'=>$Tdata['id'],
                    'market'=>$Tdata['market'],
                    'price'=>$Tdata['price'],
                    'status'=>4,
                    'userCoinId'=>Db::table(PREFIX.USERCOIN)->where(['user_id'=>$this->userid])->value('id')
                ];
                $Rabbit = new \app\common\library\RabbitPublisher();
                $Rabbit->init($data);
                Db::commit();
                Db::execute('set autocommit=1');
                return jsonArr(OPERATION_SUCCESS,[]);
            }else{
                Db::rollback();
                return jsonArr(OPERATION_ERROR,[]);
            }
        }catch (\Exception $e) {
            Db::rollback();
            return jsonArr(OPERATION_ERROR,[]);
        }
    }
}
