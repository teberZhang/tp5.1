<?php
namespace app\index\controller;

use think\App;
use think\Controller;
use think\facade\Hook;
use think\facade\Env;
use think\Db;
use think\Request;
//use think\cache\driver\Redis;
use app\common\library\Redis;

class Index extends Controller
{
    protected $_redis;
    protected $notifyRedisKey = 'appFaceVerifyToken';

    public function __construct(App $app = null,Request $request)
    {
        parent::__construct($app);
        $this->request = $request;
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
        //Db::name("student")->select();
        db("student")->insert(['name'=>'6666666']);
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
        return 'fuck';
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

}
