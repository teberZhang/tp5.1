<?php
namespace app\http;

use think\facade\Env;
use think\worker\Server;

class Worker extends Server
{
    protected $host = '127.0.0.1';
    protected $port = 2345;
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
        $connection->send('receive success555555');
    }
}