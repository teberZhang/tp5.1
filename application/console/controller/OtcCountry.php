<?php
// +----------------------------------------------------------------------
// | Desc:OTC国家管理
// +----------------------------------------------------------------------
// | Date：2019/01/03
// +----------------------------------------------------------------------
// | Author: Teber <sy@alicms.com>
// +----------------------------------------------------------------------

namespace app\console\controller;

use think\Controller;

class OtcCountry extends Controller
{
    /**
     * @OA\Get(
     *     path="/admin/otc/country",
     *     summary="国家列表",
     *     operationId="countryOtc",
     *     tags={"otc-system"},
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
     *         name="code",
     *         in="query",
     *         description="国家代码",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="areaCode",
     *         in="query",
     *         description="国家区号",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"成功","data":{"data":{},"total":1}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function lists(){
        return '{"code":200,"msg":"成功","data":{"data":{},"total":1}}';
    }
}
