<?php
// +----------------------------------------------------------------------
// | Desc:OTC柜台管理
// +----------------------------------------------------------------------
// | Date：2019/01/03
// +----------------------------------------------------------------------
// | Author: Teber <sy@alicms.com>
// +----------------------------------------------------------------------

namespace app\api\controller;

use think\Controller;
use think\Db;

class OtcCounter extends Controller
{
    /**
     * @OA\Get(
     *     path="/otc/counter",
     *     summary="OTC广告列表",
     *     operationId="Counter",
     *     tags={"otc-counter"},
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
     *         name="type",
     *         in="query",
     *         description="类型:1=在线购买,2=在线出售",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32",
     *             enum = {"1", "2"},
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="userId",
     *         in="query",
     *         description="用户id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"成功","data":{"data":{{"id":3,"userId":950397,"coin":"BTC","status":1,"type":1,"countryCode":"CN","currencyCode":"CNY","paymentProvider":3,"price":"24286.83","margin":"5.00","minPrice":0,"minAmount":1000,"maxAmount":50000,"paymentWindowMinutes":30,"message":"13868057574","onlyTradeCardAuth":0,"onlyTradeSelfTrust":0,"createTime":"1545974722000","updateTime":"1545974722000"}},"total":1}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function counters()
    {
        return '{"code":200,"msg":"成功","data":{"data":{{"id":3,"userId":950397,"coin":"BTC","status":1,"type":1,"countryCode":"CN","currencyCode":"CNY","paymentProvider":3,"price":"24286.83","margin":"5.00","minPrice":0,"minAmount":1000,"maxAmount":50000,"paymentWindowMinutes":30,"message":"13868057574","onlyTradeCardAuth":0,"onlyTradeSelfTrust":0,"createTime":"1545974722000","updateTime":"1545974722000"}},"total":1}}';
    }

    /**
     * @OA\Post(
     *     path="/otc/counter/create",
     *     summary="发布OTC广告",
     *     operationId="createCounters",
     *     tags={"otc-counter"},
     *     @OA\Parameter(
     *         name="coin",
     *         in="query",
     *         description="币种:BTC=比特币,LTC=莱特币",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="类型:1=在线购买,2=在线出售",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum = {"1", "2"},
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="countryCode",
     *         in="query",
     *         description="所在地-请选择你要发布广告的国家:如CN",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="currencyCode",
     *         in="query",
     *         description="货币:如CNY",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="margin",
     *         in="query",
     *         description="溢价:如1.0",
     *         required=true,
     *         @OA\Schema(
     *             type="double"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="价格-基于溢价比例得出的报价，10分钟更新一次",
     *         required=true,
     *         @OA\Schema(
     *             type="double"
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="minPrice",
     *         in="query",
     *         description="最低价",
     *         required=false,
     *         @OA\Schema(
     *             type="double",
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="maxPrice",
     *         in="query",
     *         description="最高价",
     *         required=false,
     *         @OA\Schema(
     *             type="double",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="minAmount",
     *         in="query",
     *         description="最小限额-一次交易的最低的交易限制",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="maxAmount",
     *         in="query",
     *         description="最大限额-一次交易中的最大交易限制，您的钱包余额也会影响最大量的设置",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="paymentWindowMinutes",
     *         in="query",
     *         description="过期时间（分钟）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="paymentProvider",
     *         in="query",
     *         description="收款方式:1=现金存款,2=银行转账,3=支付宝,4=微信支付,5=iTunes礼品卡,6=Paytm,7=其他",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32",
     *             enum = {"1","2","3","4","5","6","7"},
     *         )
     *     ),
	       @OA\Parameter(
     *         name="onlyTradeCardAuth",
     *         in="query",
     *         description="仅限实名认证的交易者:0=否,1=是",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32",
     *             enum = {"0","1"},
     *         )
     *     ),
	       @OA\Parameter(
     *         name="onlyTradeSelfTrust",
     *         in="query",
     *         description="仅限受信任的交易者:0=否,1=是",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32",
     *             enum = {"0","1"},
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="message",
     *         in="query",
     *         description="广告留言",
     *         required=false,
     *         @OA\Schema(
     *             type="text"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"提交成功","data":{}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function create(){
        $this->results([],200,'提交成功','json');
    }

    /**
     * @OA\Post(
     *     path="/otc/counter/edit",
     *     summary="编辑OTC广告",
     *     operationId="editCounters",
     *     tags={"otc-counter"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="柜台id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="countryCode",
     *         in="query",
     *         description="所在地-请选择你要发布广告的国家:如CN",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="currencyCode",
     *         in="query",
     *         description="货币:如CNY",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="margin",
     *         in="query",
     *         description="溢价:如1.0",
     *         required=true,
     *         @OA\Schema(
     *             type="double"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="价格-基于溢价比例得出的报价，10分钟更新一次",
     *         required=true,
     *         @OA\Schema(
     *             type="double"
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="minPrice",
     *         in="query",
     *         description="最低价",
     *         required=false,
     *         @OA\Schema(
     *             type="double",
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="maxPrice",
     *         in="query",
     *         description="最高价",
     *         required=false,
     *         @OA\Schema(
     *             type="double",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="minAmount",
     *         in="query",
     *         description="最小限额-一次交易的最低的交易限制",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="maxAmount",
     *         in="query",
     *         description="最大限额-一次交易中的最大交易限制，您的钱包余额也会影响最大量的设置",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="paymentWindowMinutes",
     *         in="query",
     *         description="过期时间（分钟）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="paymentProvider",
     *         in="query",
     *         description="收款方式:1=现金存款,2=银行转账,3=支付宝,4=微信支付,5=iTunes礼品卡,6=Paytm,7=其他",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32",
     *             enum = {"1","2","3","4","5","6","7"},
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="onlyTradeCardAuth",
     *         in="query",
     *         description="仅限实名认证的交易者:0=否,1=是",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32",
     *             enum = {"0","1"},
     *         )
     *     ),
	       @OA\Parameter(
     *         name="onlyTradeSelfTrust",
     *         in="query",
     *         description="仅限受信任的交易者:0=否,1=是",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32",
     *             enum = {"0","1"},
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="message",
     *         in="query",
     *         description="广告留言",
     *         required=false,
     *         @OA\Schema(
     *             type="text"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"保存成功","data":{}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function edit()
    {
        $this->results([],200,'保存成功','json');
    }

    /**
     * @OA\Get(
     *     path="/otc/counter/list",
     *     summary="我的广告",
     *     operationId="listCounter",
     *     tags={"otc-counter"},
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
     *         description="类型:1=在线购买,2=在线出售",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum = {"1", "2"},
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="状态:1=进行中的广告,2=已下架的广告",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32",
     *             enum = {"1", "2"},
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"成功","data":{"data":{{"id":3,"userId":950397,"coin":1,"status":1,"type":1,"countryCode":"CN","currencyCode":"CNY","paymentProvider":3,"price":"24286.83","margin":"5.00","minPrice":0,"minAmount":1000,"maxAmount":50000,"paymentWindowMinutes":30,"message":"13868057574","createTime":"1545974722000","updateTime":"1545974722000"}},"total":1}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function lists()
    {
        return '{"code":200,"msg":"成功","data":{"data":[{"id":3,"userId":950397,"coin":"BTC","status":"Open","type":"OnlineBuy","countryCode":"CN","currencyCode":"CNY","paymentProvider":"Alipay","price":"24286.83","margin":"5.00","minPrice":0,"minAmount":1000,"maxAmount":50000,"paymentWindowMinutes":30,"message":"13868057574","createTime":"1545974722000","updateTime":"1545974722000"}],"total":1}}';
    }

    /**
     * @OA\Post(
     *     path="/otc/counter/closed/{id}",
     *     summary="关闭广告",
     *     operationId="closedCounters",
     *     tags={"otc-counter"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="柜台id",
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
     *                 example={"code":200,"msg":"操作成功","data":{}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function closed()
    {
        $this->results([],200,'操作成功','json');
    }

    /**
     * @OA\Post(
     *     path="/otc/counter/open/{id}",
     *     summary="开放广告",
     *     operationId="openCounters",
     *     tags={"otc-counter"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="柜台id",
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
     *                 example={"code":200,"msg":"操作成功","data":{}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function open()
    {
        $this->results([],200,'操作成功','json');
    }

    /**
     * @OA\Get(
     *     path="/otc/counter/{id}",
     *     summary="广告详情",
     *     operationId="showCounters",
     *     tags={"otc-counter"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="柜台id",
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
     *                 example={"code":200,"msg":"操作成功","data":{"info":{"id":3,"userId":950397,"coin":1,"status":1,"type":1,"countryCode":"CN","currencyCode":"CNY","paymentProvider":3,"price":"24286.83","margin":"5.00","minPrice":0,"minAmount":1000,"maxAmount":50000,"paymentWindowMinutes":30,"message":"13868057574","createTime":"1545974722000","updateTime":"1545974722000"},"country":{{"code":"AO","areaCode":244,"nameEn":"Angola","nameZh-CN":"安哥拉","nameZh-HK":"安哥拉","language":""},{"code":"AF","areaCode":93,"nameEn":"Afghanistan","nameZh-CN":"阿富汗","nameZh-HK":"阿富汗","language":""},{"code":"AL","areaCode":355,"nameEn":"Albania","nameZh-CN":"阿尔巴尼亚","nameZh-HK":"阿爾巴尼亞","language":""}},"currency":{{"code":"AED","nameEn":"United Arab Emirates Dirham","nameZh-CN":"阿联酋迪拉姆","nameZh-HK":"阿聯酋迪拉姆"},{"code":"AFN","nameEn":"Afghan Afghani","nameZh-CN":"阿富汗尼","nameZh-HK":"阿富汗尼"},{"code":"ALL","nameEn":"Albania Lek","nameZh-CN":"阿尔巴尼列克","nameZh-HK":"阿爾巴尼列克"}}}}
     *     )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function show()
    {
        return '{"code":200,"msg":"操作成功","data":{"info":{"id":3,"userId":950397,"coin":1,"status":1,"type":1,"countryCode":"CN","currencyCode":"CNY","paymentProvider":3,"price":"24286.83","margin":"5.00","minPrice":0,"minAmount":1000,"maxAmount":50000,"paymentWindowMinutes":30,"message":"13868057574","createTime":"1545974722000","updateTime":"1545974722000"},"country":{{"code":"AO","areaCode":244,"nameEn":"Angola","nameZh-CN":"安哥拉","nameZh-HK":"安哥拉","language":""},{"code":"AF","areaCode":93,"nameEn":"Afghanistan","nameZh-CN":"阿富汗","nameZh-HK":"阿富汗","language":""},{"code":"AL","areaCode":355,"nameEn":"Albania","nameZh-CN":"阿尔巴尼亚","nameZh-HK":"阿爾巴尼亞","language":""}},"currency":{{"code":"AED","nameEn":"United Arab Emirates Dirham","nameZh-CN":"阿联酋迪拉姆","nameZh-HK":"阿聯酋迪拉姆"},{"code":"AFN","nameEn":"Afghan Afghani","nameZh-CN":"阿富汗尼","nameZh-HK":"阿富汗尼"},{"code":"ALL","nameEn":"Albania Lek","nameZh-CN":"阿尔巴尼列克","nameZh-HK":"阿爾巴尼列克"}}}}';
    }

	/**
     * @OA\Get(
     *     path="/otc/counter/paymentProvider",
     *     summary="广告付款方式",
     *     operationId="paymentProviderCounters",
     *     tags={"otc-counter"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"操作成功","data":{"info":{"1":"现金存款","2":"银行转账","3":"支付宝","4":"微信支付","5":"iTunes礼品卡","6":"Paytm","7":"其他"}},"success":true}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function paymentProvider()
    {
        return '{"code":200,"msg":"操作成功","data":{"info":{"1":"现金存款","2":"银行转账","3":"支付宝","4":"微信支付","5":"iTunes礼品卡","6":"Paytm","7":"其他"}},"success":true}';
    }

	/**
     * @OA\Get(
     *     path="/otc/marketPrices/{coin}",
     *     summary="某币种市场价最新价USD",
     *     operationId="marketPricesCounters",
     *     tags={"otc-counter"},
	 *     @OA\Parameter(
     *         name="coin",
     *         in="query",
     *         description="币种:BTC=比特币,LTC=莱特币",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"操作成功","data":{"info":{"coinName":"BTC","currencyCode":"USD","price":"3558.51"}},"success":true}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
	 public function marketPrices(){
		 return '{"code":200,"msg":"操作成功","data":{"info":{"coinName":"BTC","currencyCode":"USD","price":"3558.51"}},"success":true}';
	 }

	 /**
     * @OA\Get(
     *     path="/otc/exchangeRates/{currencyCode}",
     *     summary="获取某种货币兑美元汇率",
     *     operationId="exchangeRatesCounters",
     *     tags={"otc-counter"},
	 *     @OA\Parameter(
     *         name="currencyCode",
     *         in="query",
     *         description="货币:如CNY",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"操作成功","data":{"info":{"coinExchange":"USD2CNY","currencyCode":"CNY","rates":"6.500000"}},"success":true}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
	 public function exchangeRates(){
		 return '{"code":200,"msg":"操作成功","data":{"info":{"coinExchange":"USD2CNY","currencyCode":"CNY","rates":"6.500000"}},"success":true}';
	 }

	 /**
     * @OA\Get(
     *     path="/otc/counter/coinLists",
     *     summary="广告发布币种选择列表",
     *     operationId="coinListsCounters",
     *     tags={"otc-counter"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"data":{"data":{{"id":1,"coinName":"BTC"},{"id":2,"coinName":"LTC"}},"total":2},"msg":"请求成功"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function coinLists()
    {
        return '{"code":200,"data":{"data":[{"id":1,"coinName":"BTC"},{"id":2,"coinName":"LTC"}],"total":2},"msg":"请求成功"}';
    }


}
