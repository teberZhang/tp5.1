<?php
// +----------------------------------------------------------------------
// | Desc:OTC用户管理
// +----------------------------------------------------------------------
// | Date：2019/01/03
// +----------------------------------------------------------------------
// | Author: Teber <sy@alicms.com>
// +----------------------------------------------------------------------

namespace app\api\controller;

use think\Controller;

class OtcIgnore extends Controller
{

    /**
     * @OA\Post(
     *     path="/otc/blacklist/add/{userId}",
     *     summary="添加黑名单",
     *     operationId="addIgnore",
     *     tags={"otc-ignore"},
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
    public function add()
    {
        $this->results([],200,'成功','json');
    }

    /**
     * @OA\Post(
     *     path="/otc/blacklist/remove/{userId}",
     *     summary="移除黑名单",
     *     operationId="removeIgnore",
     *     tags={"otc-ignore"},
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
    public function remove()
    {
        $this->results([],200,'成功','json');
    }

    /**
     * @OA\Get(
     *     path="/otc/blacklist/my",
     *     summary="我屏蔽的人",
     *     operationId="myIgnore",
     *     tags={"otc-ignore"},
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
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"成功","data":{"data":{{"id":3,"fromUid":950397,"toUid":950398,"tradeCount":3,"createTime":"1545974722000","updateTime":"1545974722000"}},"total":1}}
     *             )
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
