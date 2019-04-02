<?php
// +----------------------------------------------------------------------
// | Desc:OTC信任关系
// +----------------------------------------------------------------------
// | Date：2019/01/03
// +----------------------------------------------------------------------
// | Author: Teber <sy@alicms.com>
// +----------------------------------------------------------------------

namespace app\api\controller;

use think\Controller;

class OtcTrust extends Controller
{
    /**
     * @OA\Post(
     *     path="/otc/trust/add/{userId}",
     *     summary="添加信任",
     *     operationId="addTrust",
     *     tags={"otc-trust"},
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
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function add()
    {
        $this->results([],200,'成功','json');
    }

    /**
     * @OA\Post(
     *     path="/otc/trust/remove/{userId}",
     *     summary="移除信任我的人",
     *     operationId="removeTrust",
     *     tags={"otc-trust"},
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
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function remove()
    {
        $this->results([],200,'成功','json');
    }

    /**
     * @OA\Get(
     *     path="/otc/trust/by",
     *     summary="信任我的人",
     *     operationId="byTrust",
     *     tags={"otc-trust"},
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
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function by()
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/otc/trust/my",
     *     summary="我信任的人",
     *     operationId="myTrust",
     *     tags={"otc-trust"},
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
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function my()
    {
        //
    }
}
