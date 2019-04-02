<?php
/**
 * Created by PhpStorm.
 * User: 17901
 * Date: 2018/10/31
 * Time: 18:48
 */

namespace app\api\service;

/*
 * K线接口类
 * */
interface ObtainkInterface
{
    /*
     * 获取K线数据
     * */
    public function Obtain();
}