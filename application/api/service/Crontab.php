<?php
/**
 * Created by PhpStorm.
 * User: 10562
 * Date: 2018/10/31
 * Time: 15:21
 */

namespace app\api\service;


interface Crontab
{
    public function recharge($coin);
    public function warn($coin);
}