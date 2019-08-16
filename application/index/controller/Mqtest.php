<?php
namespace app\index\controller;

use think\App;
use think\Controller;
use think\Exception;
use think\Db;
use think\Request;
use app\common\library\Redis;
use think\facade\Log;
use app\common\library\RabbitConnect;

class Mqtest extends Controller
{
    protected $_redis;

    public function __construct(App $app = null,Request $request)
    {
        parent::__construct($app);
        $this->request = $request;
    }

    public function index()
    {
        $redis = RabbitConnect::getInstance()->getHandle();
        $redis->set('today',date("Y-m-d H:i:s"));
        return $redis->get('today');
    }

}
