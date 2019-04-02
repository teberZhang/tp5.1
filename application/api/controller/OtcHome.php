<?php
// +----------------------------------------------------------------------
// | Desc:OTC公告页面
// +----------------------------------------------------------------------
// | Date：2019/01/03
// +----------------------------------------------------------------------
// | Author: Teber <sy@alicms.com>
// +----------------------------------------------------------------------

namespace app\api\controller;

use think\Controller;
/**
 * @OA\Info(
 *   title="OTC Forentend API",
 *    version="2.0.0"
 * )
 */
/**
 * @OA\Server(
 *      url="{schema}://192.168.10.195:8080",
 *      description="OpenApi parameters",
 *      @OA\ServerVariable(
 *          serverVariable="schema",
 *          enum={"https", "http"},
 *          default="http"
 *      )
 * )
 */

class OtcHome extends Controller
{
    /**
     * @OA\Get(
     *     path="/otc/buy",
     *     summary="购买列表",
     *     operationId="buyOtc",
     *     tags={"otc-buySell"},
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
	 *     @OA\Parameter(
     *         name="coin",
     *         in="query",
     *         description="币种如:BTC",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="countryCode",
     *         in="query",
     *         description="国家如:CN",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="currencyCode",
     *         in="query",
     *         description="货币如:CNY",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="paymentProvider",
     *         in="query",
     *         description="收款方式:1=现金存款,2=银行转账,3=支付宝,4=微信支付,5=iTunes礼品卡,6=Paytm,7=其他",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="nickname",
     *         in="query",
     *         description="昵称如:小白",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"操作成功","data":{"data":{{"id":14,"userId":11,"coin":"BTC","status":1,"type":1,"countryCode":"CN","currencyCode":"CNY","paymentProvider":2,"price":"24874.34","margin":"7.54","minPrice":"0.00","maxPrice":"0.00","minAmount":50,"maxAmount":11111,"paymentWindowMinutes":30,"message":"购买测试2","onlyTradeCardAuth":0,"onlyTradeSelfTrust":0,"createTime":1547019098000,"updateTime":1547028660000,"counterFee":"0.7"}},"total":1},"success":true}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function buy()
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/otc/sell",
     *     summary="出售列表",
     *     operationId="sellOtc",
     *     tags={"otc-buySell"},
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
	 *     @OA\Parameter(
     *         name="coin",
     *         in="query",
     *         description="币种如:BTC",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="countryCode",
     *         in="query",
     *         description="国家如:CN",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="currencyCode",
     *         in="query",
     *         description="货币如:CNY",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="paymentProvider",
     *         in="query",
     *         description="收款方式:1=现金存款,2=银行转账,3=支付宝,4=微信支付,5=iTunes礼品卡,6=Paytm,7=其他",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="nickname",
     *         in="query",
     *         description="昵称如:小白",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"操作成功","data":{"data":{{"id":6,"userId":11,"coin":"BTC","status":1,"type":2,"countryCode":"CN","currencyCode":"CNY","paymentProvider":2,"price":"0.00","margin":"8.10","minPrice":"0.00","maxPrice":"0.00","minAmount":50,"maxAmount":100000,"paymentWindowMinutes":0,"message":"留言内容1","onlyTradeCardAuth":0,"onlyTradeSelfTrust":0,"createTime":1547015347000,"updateTime":1547099941000,"counterFee":"0.7"}},"total":1},"success":true}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function sell()
    {
        //
    }

	/**
     * @OA\Get(
     *     path="/otc/buy/{id}",
     *     summary="买单详情",
     *     operationId="buybuySell",
     *     tags={"otc-buySell"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="广告id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"data":{"info":{"id":5,"userId":11,"coin":"BTC","status":1,"type":2,"countryCode":"CN","currencyCode":"CNY","paymentProvider":2,"price":"0.00","margin":"8.10","minPrice":"0.00","maxPrice":"0.00","minAmount":50,"maxAmount":100000,"paymentWindowMinutes":0,"message":"留言内容1","onlyTradeCardAuth":0,"onlyTradeSelfTrust":0,"createTime":1547014446000,"updateTime":1547014446000,"counterFee":"0.7"},"user":{"id":11,"nickname":"B53C5A76","image":"\/static\/images\/userhead\/u004.jpg","buyTradeCount":0,"sellTradeCount":0,"positiveFeedbackCount":0,"neutralFeedbackCount":0,"negativeFeedbackCount":0,"tradeCount":12,"positiveFeedbackRates":81}},"msg":"请求成功"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */

	 /**
     * @OA\Get(
     *     path="/otc/sell/{id}",
     *     summary="卖单详情",
     *     operationId="sellbuySell",
     *     tags={"otc-buySell"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="广告id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"data":{"info":{"id":5,"userId":11,"coin":"BTC","status":1,"type":2,"countryCode":"CN","currencyCode":"CNY","paymentProvider":2,"price":"0.00","margin":"8.10","minPrice":"0.00","maxPrice":"0.00","minAmount":50,"maxAmount":100000,"paymentWindowMinutes":0,"message":"留言内容1","onlyTradeCardAuth":0,"onlyTradeSelfTrust":0,"createTime":1547014446000,"updateTime":1547014446000,"counterFee":"0.7"},"user":{"id":11,"nickname":"B53C5A76","image":"\/static\/images\/userhead\/u004.jpg","buyTradeCount":0,"sellTradeCount":0,"positiveFeedbackCount":0,"neutralFeedbackCount":0,"negativeFeedbackCount":0,"tradeCount":12,"positiveFeedbackRates":81}},"msg":"请求成功"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */

    public function showDetail()
    {
        return '{"code":200,"data":{"info":{"id":5,"userId":11,"coin":"BTC","status":1,"type":2,"countryCode":"CN","currencyCode":"CNY","paymentProvider":2,"price":"0.00","margin":"8.10","minPrice":"0.00","maxPrice":"0.00","minAmount":50,"maxAmount":100000,"paymentWindowMinutes":0,"message":"留言内容1","onlyTradeCardAuth":0,"onlyTradeSelfTrust":0,"createTime":1547014446000,"updateTime":1547014446000,"counterFee":"0.7"},"user":{"id":11,"nickname":"B53C5A76","image":"\/static\/images\/userhead\/u004.jpg","buyTradeCount":0,"sellTradeCount":0,"positiveFeedbackCount":0,"neutralFeedbackCount":0,"negativeFeedbackCount":0,"tradeCount":12,"positiveFeedbackRates":81}},"msg":"请求成功"}';
    }

	/**
     * @OA\Get(
     *     path="/otc/user/{userId}",
     *     summary="OTC用户详情",
     *     operationId="userDetailcommon",
     *     tags={"otc-common"},
     *     @OA\Parameter(
     *         name="userId",
     *         in="query",
     *         description="用户id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"data":{"info":{"id":11,"nickname":"B53C5A76","createTime":1543816060000,"image":"\/static\/images\/userhead\/u004.jpg","idCardAuth":1,"email":"","phone":"176****5466","trustCount":0,"buyTradeCount":0,"sellTradeCount":0,"positiveFeedbackCount":0,"neutralFeedbackCount":0,"negativeFeedbackCount":0,"tradeCount":12,"positiveFeedbackRates":81,"isEmailVerified":0,"isPhoneVerified":1}},"msg":"请求成功"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */

    public function userDetail()
    {
        return '{"code":200,"data":{"info":{"id":11,"nickname":"B53C5A76","createTime":1543816060000,"image":"\/static\/images\/userhead\/u004.jpg","idCardAuth":1,"email":"","phone":"176****5466","trustCount":0,"buyTradeCount":0,"sellTradeCount":0,"positiveFeedbackCount":0,"neutralFeedbackCount":0,"negativeFeedbackCount":0,"tradeCount":12,"positiveFeedbackRates":81,"isEmailVerified":0,"isPhoneVerified":1}},"msg":"请求成功"}';
    }

	/**
     * @OA\Get(
     *     path="/otc/trustRelation/{userId}",
     *     summary="我与某人的信任关系",
     *     operationId="trustRelationcommon",
     *     tags={"otc-common"},
     *     @OA\Parameter(
     *         name="userId",
     *         in="query",
     *         description="用户id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"data":{"info":{"isTrust":1,"isBlack":0}},"msg":"请求成功"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */

    public function trustRelation()
    {
        return '{"code":200,"data":{"info":{"isTrust":1,"isBlack":0}},"msg":"请求成功"}';
    }


}
