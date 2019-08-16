<?php
namespace app\index\controller;

use think\App;
use think\Controller;
use think\Exception;
use think\facade\Hook;
use think\facade\Env;
use think\Db;
use think\Request;
//use think\cache\driver\Redis;
use app\common\library\Redis;
use think\facade\Log;

class Index extends Controller
{
    protected $_redis;
    protected $notifyRedisKey = 'appFaceVerifyToken';
    protected $Trade = [
        'mum'=>0,
        'dealType' =>1,
        'coin_suf' => 'usdt',
        'coin_pre' => 'ltc',
        'entrustNumber' => 0,
    ];
    protected $total;
    protected $userid = 1;

    public function __construct(App $app = null,Request $request)
    {
        parent::__construct($app);
        $this->request = $request;
        $entrustNumber = 35.87155800;
        $price = 67.06999900;
        $total = bcmul($entrustNumber,$price,16);    //总价
        $this->Trade['mum'] = $total;
        $this->Trade['entrustNumber'] = $entrustNumber;
        $this->Trade['price'] = $price;
//        $config = [
//            'host' => '127.0.0.1',
//            'port' => 6379,
//            'password' => 'xiaoyong666',
//            'select' => 0,
//            'timeout' => 0,
//            'expire' => 0,
//            'persistent' => false,
//            'prefix' => '',
//        ];
//        $redis = new Redis($config);
//        $this->_redis = $redis;
    }

    public function index()
    {
        define('PREFIX','alicms_');
        define('ENTRUST','entrust');
        define('FINANCEDETAIL','finance_detail');
        define('USERCOIN','user_coin');
        $orderSn = date("YmdHis");
        //$result = $this->UpUserCoin();
        //var_dump($result);
        //var_dump($this->Trade);

        Db::startTrans();
        try{
            $this->UpUserCoin();
            $id = $this->insertTrade($orderSn);
            if(!$id){
                throw new Exception("create entrust error");
            }
            var_dump($id);
            $fid = $this->insertFinance($orderSn,$id);
            if(!$fid){
                throw new Exception("create finance error");
            }
            var_dump($fid);
            Db::commit();
        }catch (\Exception $e) {
            echo $e->getMessage();
            Db::rollback();
        }
    }

    public function hello($name = 'ThinkPHP5')
    {
        return $name;
    }

    public function test()
    {
        return $this->fetch();
    }

    public function market()
    {
        return $this->fetch();
    }

    public function think()
    {
        //file_put_contents($_SERVER['DOCUMENT_ROOT'].'/crontablog.txt','bbbb'."\r\n",FILE_APPEND);
        $destination = $_SERVER['DOCUMENT_ROOT'].'/crontablog.txt';
        error_log('dddd'."\r\n",3,$destination);
        //Log::info(date("Y-m-d H:i:s").json_encode($this->request->param()));
        return 'think';
    }

    public function notity()
    {
        return $this->request->param('ware');
        $user_id = 1;
        $name = '张胜永';
        $idCardNumber = '371202199206171278';
        //同一个姓名+身份证只能实名认证1次
        $whereCardIsExist = [
            ['true_name','=',$name],
            ['id_card','=',$idCardNumber],
            ['id','<>',$user_id],
        ];
        $cardIsExist = Db::name("user")
            ->field('id,phone,true_name,id_card')
            ->where($whereCardIsExist)
            ->find();
        var_dump($cardIsExist);
        echo Db::name("user")->getLastSql();
    }

    public function redisdemo1()
    {
        $redis = new Redis([
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => 'xiaoyong666'
        ]);
        $user_id = 10;
        $token = 'aabbcc';
        $redis->set("test","hello1");
        $redis->hset($this->notifyRedisKey, $user_id, $token);
        return $redis->hget($this->notifyRedisKey, (String)$user_id);
    }

    public function parseHost($domain)
    {
        $scheme = 'http://';
        $rootDomain = '';
        if(strpos($domain,'https://') !== false) {
            $scheme = 'https://';
            $rootDomain = substr($domain,8);
        } else {
            $rootDomain = substr($domain,7);
        }
        $domainArr = explode('.',$rootDomain);
        $topDomain = 'https://btc.alicms.com';
        if(count($domainArr) == 3){
            $topDomain = str_replace($domainArr[0],'www',$domain);
        }
        return $topDomain;
    }

    /*
     * 修改用户余额
     * */
    private function UpUserCoin(){
        $row = [];
        $user_id = $this->userid;
//            修改用户余额
        if($this->Trade['dealType'] == 1){
            //交易货币B
            $coin_suf = $this->Trade['coin_suf'];
            $coin_frozen = $this->Trade['coin_suf'].'_frozen';
            //委托数量*单价
            $mum = $this->Trade['mum'];
            $sql = "UPDATE ".PREFIX.USERCOIN
                ." SET {$coin_suf} = {$coin_suf} - {$mum}"
                ." , {$coin_frozen} = {$coin_frozen} + {$mum}"
                ." WHERE user_id = {$user_id}";
            $row[] = Db::execute($sql);

        }else{
            //交易货币A
            $coin_pre = $this->Trade['coin_pre'];
            //委托数量
            $entrustNumber = $this->Trade['entrustNumber'];
            $coin_frozen = $this->Trade['coin_pre'].'_frozen';
            $sql = "UPDATE ".PREFIX.USERCOIN
                ." SET {$coin_pre} = {$coin_pre} - {$entrustNumber}"
                ." , {$coin_frozen} = {$coin_frozen} + {$entrustNumber}"
                ." WHERE user_id = {$user_id}";
            $row[] = Db::execute($sql);
        }
        return $row;
    }

    /*
     * 写入委托记录
     * */
    public function insertTrade($orderSn = ''){
        $insertTableId = 0;
        $sql = "INSERT INTO ".PREFIX.ENTRUST.
            " (user_id,order_number,market,deal_type,price,
            entrust_number,total,create_time,status) 
            VALUES (:user_id,:order_number,:market,:deal_type,:price,
            :entrust_number,:total,:create_time,:status);";
        $affected = Db::execute(
            $sql,
            [
                'user_id'        => 1,
                'order_number'   => $orderSn,
                'market'         => 'ltc_usdt',
                'deal_type'      => $this->Trade['dealType'],
                'price'          => $this->Trade['price'],
                'entrust_number' => $this->Trade['entrustNumber'],
                'total'          => $this->Trade['mum'],
                'create_time'    => date('Y-m-d H:i:s',time()),
                'status'         => 1
            ]
        );
        if($affected){
            $insertTableId = Db::getLastInsID();
        }
        return $insertTableId;
    }

    /*
     * 写入财务
     * */
    public function insertFinance($orderSn = '',$id = 0){
        $entrustNumber = 35.87155800;
        $price = 67.06999900;
        $total = bcmul($entrustNumber,$price,16);    //总价
        //唯一ID
        $Particle = date("YmdHis");
        $next_id = $Particle.rand(1,9);

        $insertTableId = 0;
        $sql = "INSERT INTO ".PREFIX.FINANCEDETAIL.
            " (user_id,order_id,transaction_number,order_number,market,operation,
            deal_type,transaction_currency,deal_total,create_time,status) 
            VALUES (:user_id,:order_id,:transaction_number,:order_number,:market,:operation
            ,:deal_type,:transaction_currency,:deal_total,:create_time,:status);";
        $affected = Db::execute(
            $sql,
            [
                'user_id'=>1,
                'order_id' => $id,
                'transaction_number' => $next_id,//交易流水号
                'order_number'=>$orderSn,
                'market'=>'ltc_usdt',
                'operation' => 2,
                'deal_type' => 4,
                'transaction_currency' => $this->Trade['dealType'] == 1?$this->Trade['coin_suf']:$this->Trade['coin_pre'],
                'deal_total'           => $this->Trade['dealType'] == 1?$this->Trade['mum']:$this->Trade['entrustNumber'],
                'create_time'          => date('Y-m-d H:i:s',time()),
                'status' => 1
            ]
        );
        if($affected){
            $insertTableId = Db::getLastInsID();
        }
        return $insertTableId;
    }

}
