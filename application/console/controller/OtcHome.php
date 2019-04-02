<?php
// +----------------------------------------------------------------------
// | Desc:OTC后台home
// +----------------------------------------------------------------------
// | Date：2019/01/03
// +----------------------------------------------------------------------
// | Author: Teber <sy@alicms.com>
// +----------------------------------------------------------------------

namespace app\console\controller;

use think\Controller;

/**
 * @OA\Info(
 *   title="OTC Backend API",
 *    version="2.0.0"
 * )
 */
/**
 * @OA\Server(
 *      url="{schema}://local.tp51.com",
 *      description="OpenApi parameters",
 *      @OA\ServerVariable(
 *          serverVariable="schema",
 *          enum={"https", "http"},
 *          default="http"
 *      )
 * )
 */
class OtcHome extends Controller
{
}
