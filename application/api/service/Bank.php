<?php
/**
 * Created by PhpStorm.
 * User: 10562
 * Date: 2018/11/2
 * Time: 9:19
 */

namespace app\api\service;


interface Bank
{
    /**
     * 查询所有开户行
     * @return mixed
     */
    public function banks();

    /**
     * post 银行卡新增 , get查询银行卡列表
     * @return mixed
     */
    public function bankcards();
}