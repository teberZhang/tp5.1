<?php
// +----------------------------------------------------------------------
// | Desc:OTC系统配置
// +----------------------------------------------------------------------
// | Date：2019/01/03
// +----------------------------------------------------------------------
// | Author: Teber <sy@alicms.com>
// +----------------------------------------------------------------------

namespace app\console\controller;

use think\Controller;

class OtcConfig extends Controller
{
    /**
     * @OA\Get(
     *     path="/admin/otc/config",
     *     summary="系统配置获取",
     *     operationId="configOtc",
     *     tags={"otc-system"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"成功","data":{"data":{"info":{}}}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function show(){
        return '{"code":200,"msg":"成功","data":{"data":{"info":{}}}}';
    }

    /**
     * @OA\Post(
     *     path="/admin/otc/config/save",
     *     summary="系统配置-修改",
     *     operationId="saveconfigOtc",
     *     tags={"otc-system"},
     *     @OA\Parameter(
     *         name="coins",
     *         in="query",
     *         description="币种",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="confirmationsBtc",
     *         in="query",
     *         description="入账确认数（BTC）",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="confirmationsLtc",
     *         in="query",
     *         description="入账确认数（LTC）",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="networkOutFeeBtc",
     *         in="query",
     *         description="网络转出手续费（BTC）",
     *         required=false,
     *         @OA\Schema(
     *             type="double",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="networkOutFeeLtc",
     *         in="query",
     *         description="网络转出手续费（LTC）",
     *         required=false,
     *         @OA\Schema(
     *             type="double",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="blockchainViewBtc",
     *         in="query",
     *         description="区块链查看（BTC）",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="blockchainViewLtc",
     *         in="query",
     *         description="区块链查看（LTC）",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="minShowAmountBtc",
     *         in="query",
     *         description="柜台展示余额限制（BTC）",
     *         required=false,
     *         @OA\Schema(
     *             type="double",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="minShowAmountLtc",
     *         in="query",
     *         description="柜台展示余额限制（LTC）",
     *         required=false,
     *         @OA\Schema(
     *             type="double",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="counterFee",
     *         in="query",
     *         description="柜台手续费",
     *         required=false,
     *         @OA\Schema(
     *             type="double",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="minAmount",
     *         in="query",
     *         description="最小限额",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"保存成功","data":null}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function save()
    {
        return '{"code":200,"msg":"保存成功","data":null}';
    }
}
