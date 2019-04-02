<?php
// +----------------------------------------------------------------------
// | Desc:OTC消息管理
// +----------------------------------------------------------------------
// | Date：2019/01/03
// +----------------------------------------------------------------------
// | Author: Teber <sy@alicms.com>
// +----------------------------------------------------------------------

namespace app\api\controller;

use think\Controller;

class OtcMessage extends Controller
{
    /**
     * @OA\Post(
     *     path="/otc/message/send",
     *     summary="发送消息",
     *     operationId="sendMessage",
     *     tags={"otc-message"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="消息id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="messageText",
     *         in="query",
     *         description="文本消息",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *    @OA\Parameter(
     *         name="messageImage",
     *         in="query",
     *         description="图片消息",
     *         required=true,
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
        $this->results([],200,'成功','json');
    }

    /**
     * @OA\Post(
     *     path="/otc/message/read/{messageId}",
     *     summary="标记消息为已读",
     *     operationId="readMessage",
     *     tags={"otc-message"},
     *     @OA\Parameter(
     *         name="messageId",
     *         in="query",
     *         description="消息id",
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
    public function read()
    {
        $this->results([],200,'成功','json');
    }

    /**
     * @OA\Post(
     *     path="/otc/message/image/{messageId}",
     *     summary="显示消息内的图片",
     *     operationId="imageMessage",
     *     tags={"otc-message"},
     *     @OA\Parameter(
     *         name="messageId",
     *         in="query",
     *         description="消息id",
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
    public function showImage()
    {
        $this->results([],200,'成功','json');
    }
}
