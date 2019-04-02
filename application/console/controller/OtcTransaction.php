<?php
// +----------------------------------------------------------------------
// | Desc:OTC交易管理
// +----------------------------------------------------------------------
// | Date：2019/01/03
// +----------------------------------------------------------------------
// | Author: Teber <sy@alicms.com>
// +----------------------------------------------------------------------

namespace app\console\controller;

use think\Controller;

class OtcTransaction extends Controller
{
    /**
     * @OA\Get(
     *     path="/transaction",
     *     summary="交易记录列表",
     *     operationId="transactionBackend",
     *     tags={"otc-financialBack"},
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
     *         name="userId",
     *         in="query",
     *         description="用户id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="username",
     *         in="query",
     *         description="用户名",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="手机",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="日期",
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
