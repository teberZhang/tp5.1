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

class OtcCommon extends Controller
{
    /**
     * @OA\Get(
     *     path="/otc/country",
     *     summary="国家列表",
     *     operationId="Country",
     *     tags={"otc-common"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"成功","data":{"data":{{"code":"AO","areaCode":244,"nameEn":"Angola","nameZh-CN":"安哥拉","nameZh-HK":"安哥拉","language":""}},"total":1}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function country()
    {
        return '{"code":200,"msg":"成功","data":{"data":[{"code":"AO","areaCode":244,"nameEn":"Angola","nameZh-CN":"安哥拉","nameZh-HK":"安哥拉","language":""}],"total":1}}';
    }

    /**
     * @OA\Get(
     *     path="/otc/currency",
     *     summary="货币列表",
     *     operationId="Currency",
     *     tags={"otc-common"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"成功","data":{"data":{{"code":"AED","nameEn":"United Arab Emirates Dirham","nameZh-CN":"阿联酋迪拉姆","nameZh-HK":"阿聯酋迪拉姆"},{"code":"AFN","nameEn":"Afghan Afghani","nameZh-CN":"阿富汗尼","nameZh-HK":"阿富汗尼"},{"code":"ALL","nameEn":"Albania Lek","nameZh-CN":"阿尔巴尼列克","nameZh-HK":"阿爾巴尼列克"}},"total":3}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function currency()
    {
        return '{"code":200,"msg":"成功","data":{"data":[{"code":"AED","nameEn":"United Arab Emirates Dirham","nameZh-CN":"阿联酋迪拉姆","nameZh-HK":"阿聯酋迪拉姆"},{"code":"AFN","nameEn":"Afghan Afghani","nameZh-CN":"阿富汗尼","nameZh-HK":"阿富汗尼"},{"code":"ALL","nameEn":"Albania Lek","nameZh-CN":"阿尔巴尼列克","nameZh-HK":"阿爾巴尼列克"}],"total":3}}';
    }

	/**
     * @OA\Get(
     *     path="otc/config/{configKey}",
     *     summary="系统配置获取",
     *     operationId="otcconfig",
     *     tags={"otc-common"},
	 *     @OA\Parameter(
     *         name="configKey",
     *         in="query",
     *         description="OTC配置key:all=全部,counterFee=柜台手续费",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"data":{"info":{"key":"counter_fee","type":1,"value":"0.7","name":"柜台手续费","description":"比如手续费 0.7% ，购买 1 BTC 后，实际到账 0.993 BTC 。若是出售，则需要扣除账户 1.007 BTC 的可用余额。","rules":"required|numeric|between:0,100","weight":9,"unit":"%","options":null}},"msg":"请求成功"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
/
}
