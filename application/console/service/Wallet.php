<?php
/**
 * Created by PhpStorm.
 * User: 10562
 * Date: 2018/11/2
 * Time: 10:29
 */

namespace app\api\service;


interface Wallet
{
    /**
     * get 获取平台收款地址, post 提交提币订单
     * @param int $coinId       币种ID
     * @param decimal $num      交易数量
     * @param string $tpw       交易密码
     * @param int $smsCode      动态码
     * @param string $toAddress 提币地址
     * @return mixed
     */
    public function address($coinId,$num,$tpw,$smsCode,$toAddress);

    /**
     * get 查询提现钱包地址, post 新增提币地址
     * @param int $coinId       币种ID
     * @param int $page         分页页数
     * @param int $pageSize     每页条数
     * @param string $sort      排序字段
     * @param string $order     排序方式
     * @return mixed
     */
    public function addresses();

    /**
     * 查询充值提现订单
     * @param int $coinId       币种ID
     * @param int $page         分页页数
     * @param int $pageSize     每页条数
     * @param string $sort      排序字段
     * @param string $order     排序方式
     * @param int $type         订单类型:1=充值.2=提现
     * @return mixed
     */
    public function orders($coinId,$page,$pageSize,$sort,$order,$type,$status);

    /**
     * 删除提现钱包地址
     * @param int $addressId    钱包地址ID
     * @return mixed
     */
    public function remove($addressId);
}
