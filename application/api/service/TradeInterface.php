<?php

namespace app\api\service;

/*
 * 交易市场委托接口类
 * */
interface TradeInterface
{
    /*
     * 提交委托
     * */
    public function transaction($userid);
    /*
     * 撤销委托
     * */
    public function RevokeTrade($userid);
}