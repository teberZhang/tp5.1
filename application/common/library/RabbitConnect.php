<?php
namespace app\common\library;

use think\cache\driver\Redis;

final class RabbitConnect
{
    /**
     * @var RabbitConnect
     */
    private static $instance;
    private $redis;

    /***
     * RabbitConnect
     * @param string $host 主机host
     * @param string $port 端口
     * @param string $username 用户
     * @param string $password 密码
     * @return RabbitConnect
     */
    public static function getInstance($host = '', $port = '', $username = '', $password = ''): RabbitConnect
    {
        if (null === static::$instance) {
            static::$instance = new static($host, $port, $username, $password);
        }

        return static::$instance;
    }

    /**
     * 不允许从外部调用以防止创建多个实例
     * 要使用单例，必须通过 RabbitConnect::getInstance() 方法获取实例
     */
    private function __construct($host = '', $port = '', $username = '', $password = '')
    {
        $config = [
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => 'xiaoyong666',
            'select' => 0,
            'timeout' => 0,
            'expire' => 0,
            'persistent' => false,
            'prefix' => '',
        ];
        $this->redis = new Redis($config);
        return $this->redis;
    }

    public function getHandle()
    {
        return $this->redis;
    }

    /**
     * 防止实例被克隆（这会创建实例的副本）
     */
    private function __clone()
    {
        //
    }

    /**
     * 防止反序列化（这将创建它的副本）
     */
    private function __wakeup()
    {
    }
}
