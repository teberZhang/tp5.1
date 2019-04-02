<?php
// +----------------------------------------------------------------------
// | Desc:谷歌二次认证
// +----------------------------------------------------------------------
// | Date：2019/01/03
// +----------------------------------------------------------------------
// | Author: Teber <sy@alicms.com>
// +----------------------------------------------------------------------

namespace app\api\controller;

use think\Controller;

class Google2fa extends Controller
{
    /**
     * @OA\Post(
     *     path="/otc/google2fa/bind",
     *     summary="谷歌二次认证绑定",
     *     operationId="bindGoogle2fa",
     *     tags={"otc-Google2fa"},
     *     @OA\Parameter(
     *         name="secret",
     *         in="query",
     *         description="秘钥",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="验证码",
     *         required=false,
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
    public function bind()
    {
        $this->results([],200,'验证成功','json');
    }

    /**
     * @OA\Get(
     *     path="/otc/google2fa/bind",
     *     summary="生成秘钥和二维码链接",
     *     operationId="showBindFormGoogle2fa",
     *     tags={"otc-Google2fa"},
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function showBindForm()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/otc/google2fa/unbind",
     *     summary="解除谷歌二次验证",
     *     operationId="unbindGoogle2fa",
     *     tags={"otc-Google2fa"},
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         description="验证码",
     *         required=false,
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
    public function unbind(){

    }
}
