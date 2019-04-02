<?php

namespace app\api\service;

/*
 * 币种接口类
 * */
interface CoinInterface
{
    /*
     *提币：资产、提币手续费查询
     * */
    public function balance($userid);
    /*
     *充值提币,币种查询
     * */
    public function CoinQuery();
}