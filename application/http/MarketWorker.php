<?php
namespace app\http;

use think\facade\Env;
use think\worker\Server;

class MarketWorker extends Server
{
    protected $host = '127.0.0.1';
    protected $port = 2348;
    protected $option = [
        'count'		=> 4,
        'name'		=> 'thinkphp',
    ];

    public function onConnect($connection)
    {
        echo "new connection from ip " . $connection->getRemoteIp() . "\n";
    }

    public function onMessage($connection, $data)
    {
        $connection->send('receive success66666');
    }
}