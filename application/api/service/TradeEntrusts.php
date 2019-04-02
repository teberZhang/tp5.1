<?php
namespace app\api\service;
interface TradeEntrusts
{
    /*
     * 个人中心查询委托记录
     * */
    public function UserTrade();
    /*
     * 交易市场查询委托记录
     * */
    public function showTrade();
    /*
     * 交易中心价格排行查询
     * */
    public function priceTrade($redis);
    /*
     * 交易比例
     * */
    public function coinTrade($redis,$type);
    /*
     * 交易比例
     * */
    public function highLowTrade($redis);
}
