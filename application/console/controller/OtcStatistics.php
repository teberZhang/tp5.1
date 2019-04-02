<?php
// +----------------------------------------------------------------------
// | Desc:OTC统计管理
// +----------------------------------------------------------------------
// | Date：2019/01/03
// +----------------------------------------------------------------------
// | Author: Teber <sy@alicms.com>
// +----------------------------------------------------------------------

namespace app\console\controller;

use think\Controller;

class OtcStatistics extends Controller
{
    /**
     * @OA\Get(
     *     path="/statisticsUser",
     *     summary="用户统计",
     *     operationId="statisticsUser",
     *     tags={"otc-statistics"},
     *     @OA\Parameter(
     *         name="dateStart",
     *         in="query",
     *         description="开始时间",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="dateEnd",
     *         in="query",
     *         description="结束时间",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"成功","data":{"info":{"userCount":100,"onlineCount":23,"newUsers":{},"activeUsers":{},"date_range":"2018-12-04 ~ 2019-01-04"}}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function getUser(){
        return '{"code":200,"msg":"成功","data":{"info":{"userCount":100,"onlineCount":23,"newUsers":{},"activeUsers":{},"date_range":"2018-12-04 ~ 2019-01-04"}}}';
    }

    /**
     * @OA\Get(
     *     path="/statisticsFlow",
     *     summary="流水统计",
     *     operationId="statisticsFlow",
     *     tags={"otc-statistics"},
     *     @OA\Parameter(
     *         name="dateStart",
     *         in="query",
     *         description="开始时间",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="dateEnd",
     *         in="query",
     *         description="结束时间",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"成功","data":{"info":{"data":{},"date_range":"2018-12-04 ~ 2019-01-04","totalFeeBtc":100.23,"totalFeeLtc":23.12,"totalDepositBtc":20.00,"totalDepositLtc":1.02,"soldWithdrawBtc":1.0,"soldWithdrawLtc":28.01}}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function getFlow(){
        return '{"code":200,"msg":"成功","data":{"info":{"data":{},"date_range":"2018-12-04 ~ 2019-01-04","totalFeeBtc":100.23,"totalFeeLtc":23.12,"totalDepositBtc":20.00,"totalDepositLtc":1.02,"soldWithdrawBtc":1.0,"soldWithdrawLtc":28.01}}}';
    }
}
