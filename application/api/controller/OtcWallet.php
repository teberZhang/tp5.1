<?php
// +----------------------------------------------------------------------
// | Desc:OTC钱包管理
// +----------------------------------------------------------------------
// | Date：2019/01/03
// +----------------------------------------------------------------------
// | Author: Teber <sy@alicms.com>
// +----------------------------------------------------------------------

namespace app\api\controller;

use think\Controller;
use think\Db;

class OtcWallet extends Controller
{
    /**
     * @OA\Get(
     *     path="/otc/wallet",
     *     summary="钱包余额",
     *     operationId="Wallet",
     *     tags={"otc-wallet"},
     *     @OA\Parameter(
     *         name="coin",
     *         in="query",
     *         description="币种名称如:BTC",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum = {"BTC", "LTC"},
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function wallet()
    {
        return '{"code":200,"msg":"成功","data":{"info":{"id":1,"userId":950397,"coin":"BTC","balanceAvailable":"9998.34148796","balanceLocked":"0.00000000","createTime":"1545974722000","updateTime":"1545974722000"}}}';
    }

    /**
     * @OA\Get(
     *     path="/otc/wallet/address",
     *     summary="钱包地址",
     *     operationId="addressWallet",
     *     tags={"otc-wallet"},
     *     @OA\Parameter(
     *         name="coin",
     *         in="query",
     *         description="币种名称如:BTC",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum = {"BTC", "LTC"},
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function address()
    {
        return '{"code":200,"msg":"成功","data":{"info":{"id":1,"coin":"BTC","address":"12wkcykmxeF5rbq67zXzZQM4MMRG4Zn5ag","userId":950398,"createTime":"1545974722000","updateTime":"1545974722000"}}}';
    }

    /**
     * @OA\Get(
     *     path="/otc/wallet/transactions",
     *     summary="交易记录",
     *     operationId="transactionsWallet",
     *     tags={"otc-wallet"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="当前页，默认1",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="pageSize",
     *         in="query",
     *         description="当前页大小，默认10",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="排序字段，默认id",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="排列顺序，默认desc",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="类型:NetworkIn=网络转入,NetworkOut=网络转出,TradeIn=交易买入,TradeOut=交易卖出,PlatformIn=平台转入,PlatformOut=平台转出",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum = {"NetworkIn","NetworkOut","TradeIn","TradeOut","PlatformIn","PlatformOut"},
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function transactions()
    {
        return '{"code":200,"msg":"成功","data":{"data":[{"id":4,"userId":950397,"walletId":1,"coin":"BTC","type":"TradeOut","amount":"0.82349158","fee":"0.00576444","txid":"","address":"","relatedTransactionId":5,"targetId":950398,"price":"24286.83","currencyCode":"CNY","remark":"","createTime":"1545974722000","updateTime":"1545974722000"},{"id":5,"userId":950398,"walletId":3,"coin":"BTC","type":"TradeIn","amount":"0.82349158","fee":"0.00000000","txid":"","address":"","relatedTransactionId":4,"targetId":950397,"price":"24286.83","currencyCode":"CNY","remark":"","createTime":"1545974722000","updateTime":"1545974722000"},{"id":6,"userId":950397,"walletId":1,"coin":"BTC","type":"TradeOut","amount":"0.82349158","fee":"0.00576444","txid":"","address":"","relatedTransactionId":7,"targetId":950398,"price":"24286.83","currencyCode":"CNY","remark":"","createTime":"1545974722000","updateTime":"1545974722000"}],"total":3}}';
    }

    /**
     * @OA\Get(
     *     path="/otc/wallet/deposits",
     *     summary="取得所有未到账的存款列表",
     *     operationId="depositsWallet",
     *     tags={"otc-wallet"},
     *     @OA\Parameter(
     *         name="coin",
     *         in="query",
     *         description="币种名称如:BTC",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum = {"BTC", "LTC"},
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function deposits()
    {
        return '{"code":200,"msg":"成功","data":{"data":[{"id":1,"coin":"BTC","txid":"aaaa","userId":950398,"amount":"0.00000000","confirmations":10,"createTime":"1545974722000","updateTime":"1545974722000"}],"total":1}}';
    }

    /**
     * @OA\Get(
     *     path="/otc/wallet/qrcode/{address}/{amount}",
     *     summary="钱包收款二维码图片",
     *     operationId="qrcodeWallet",
     *     tags={"otc-wallet"},
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="钱包地址",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         description="数量",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function qrcode()
    {
        return '{"code":200,"msg":"成功","data":{"info":{"url":"http://aa.cc.com"}}}';
    }

    /**
     * @OA\Post(
     *     path="/otc/wallet/send",
     *     summary="发送数字货币",
     *     operationId="sendWallet",
     *     tags={"otc-wallet"},
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="钱包地址",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         description="数量",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="secondaryPassword",
     *         in="query",
     *         description="资金密码",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="remark",
     *         in="query",
     *         description="备注",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function send()
    {
        $this->results([],200,'发送成功','json');
    }
}
