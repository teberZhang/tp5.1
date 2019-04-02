<?php

namespace app\api\service;

/*
 * 交易市场管理接口类
 * */
interface MarketInterface
{
    /*
     *获取交易市场列表
     * */
    public function marketList();
    /*
     *获取交易市场列表
     * */
    public function marketUnique();
    /*
     *获取交易基准币
     * */
    public function marketCoinSuf();
    /*
     *委托提交小数位限制位数查询
     * */
    public function MarketDigit();
    /*
     * 基准币对应的交易市场信息查询
     * */
    public function MarketSuf();
    /*
     *收藏、取消收藏交易市场
     * */
    public function collect($userid);
    /*
     *查询收藏的交易市场
     * */
    public function forCollect($userid);

}