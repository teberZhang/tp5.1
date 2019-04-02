<?php
// +----------------------------------------------------------------------
// | Desc:OTC订单管理
// +----------------------------------------------------------------------
// | Date：2019/01/03
// +----------------------------------------------------------------------
// | Author: Teber <sy@alicms.com>
// +----------------------------------------------------------------------

namespace app\api\controller;

use think\Controller;
use think\Db;

class OtcOrder extends Controller
{
    /**
     * @OA\Post(
     *     path="/otc/order/create",
     *     summary="创建订单",
     *     operationId="createOrder",
     *     tags={"otc-order"},
     *     @OA\Parameter(
     *         name="counterId",
     *         in="query",
     *         description="广告id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="in",
     *         in="query",
     *         description="数量 OR 金额amount=金额（法币）,quantity=数量（数字币）",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum = {"amount", "quantity"},
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         description="金额（法币）:in=amount时传递",
     *         required=false,
     *         @OA\Schema(
     *             type="double",
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="quantity",
     *         in="query",
     *         description="数量（数字币）:in=quantity时传递",
     *         required=false,
     *         @OA\Schema(
     *             type="double",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"成功","data":{"info":{"id":7,"counterId":4,"buyerId":950398,"sellerId":950397,"status":3,"price":"24286.83","amount":"20000.00","quantity":"0.82349158","fee":"0.00576444","buyerFeedback":1,"sellerFeedback":1,"expiresAt":"1545974722000","createTime":"1545974722000","updateTime":"1545974722000"},"messages":{{"id":7,"orderId":401,"fromUid":950398,"toUid":950397,"type":1,"isRead":0,"content":"hello","extra":"","createTime":"1545974722000","updateTime":"1545974722000"}}}}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function create()
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/otc/order/{orderId}",
     *     summary="订单详情",
     *     operationId="showOrder",
     *     tags={"otc-order"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"msg":"成功","data":{"info":{"id":7,"counterId":4,"buyerId":950398,"sellerId":950397,"status":3,"price":"24286.83","amount":"20000.00","quantity":"0.82349158","fee":"0.00576444","buyerFeedback":1,"sellerFeedback":1,"expiresAt":"1545974722000","createTime":"1545974722000","updateTime":"1545974722000"},"messages":{{"id":7,"orderId":401,"fromUid":950398,"toUid":950397,"type":1,"isRead":0,"content":"hello","extra":"","createTime":"1545974722000","updateTime":"1545974722000"}}}}
     *             )
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="orderId",
     *         in="query",
     *         description="订单id",
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
    public function show()
    {
        return '{"code":200,"msg":"成功","data":{"info":{"id":7,"counterId":4,"buyerId":950398,"sellerId":950397,"status":"Confirmed","price":"24286.83","amount":"20000.00","quantity":"0.82349158","fee":"0.00576444","buyerFeedback":"Positive","sellerFeedback":"Positive","expiresAt":"1545974722000","createTime":"1545974722000","updateTime":"1545974722000"},"messages":[{"id":10,"orderId":7,"sourceId":950398,"targetId":950397,"type":"Text","isRead":1,"content":"aaa","extra":null,"createTime":"1545974722000","updateTime":"1545974722000"}]}}';
    }

    /**
     * @OA\Get(
     *     path="/otc/order",
     *     summary="订单列表",
     *     operationId="listOrderOTC",
     *     tags={"otc-order"},
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
     *         name="type",
     *         in="query",
     *         description="类型:Processing=进行中的交易,Completed=已完成的交易",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum = {"Processing", "Completed"},
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="coin",
     *         in="query",
     *         description="币种:BTC=比特币,LTC=莱特币",
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
    public function lists()
    {
        return '{"code":200,"msg":"成功","data":{"data":[{"id":7,"counterId":4,"buyerId":950398,"sellerId":950397,"status":"Confirmed","price":"24286.83","amount":"20000.00","quantity":"0.82349158","fee":"0.00576444","buyerFeedback":"Positive","sellerFeedback":"Positive","expiresAt":"1545974722000","createTime":"1545974722000","updateTime":"1545974722000"}],"processingMessageCount":5,"completedMessageCount":3}}';
    }

    /**
 * @OA\Post(
 *     path="/otc/order/repeal/{orderId}",
 *     summary="撤销订单",
 *     operationId="repealOrder",
 *     tags={"otc-order"},
 *     @OA\Parameter(
 *         name="orderId",
 *         in="query",
 *         description="订单id",
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
    public function repeal()
    {
        $this->results([],200,'撤销成功','json');
    }

    /**
     * @OA\Post(
     *     path="/otc/order/restart/{orderId}",
     *     summary="重启订单",
     *     operationId="restartOrder",
     *     tags={"otc-order"},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="query",
     *         description="订单id",
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
    public function restart()
    {
        $this->results([],200,'重启成功','json');
    }

    /**
     * @OA\Post(
     *     path="/otc/order/payment/{orderId}",
     *     summary="已付款",
     *     operationId="paymentOrder",
     *     tags={"otc-order"},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="query",
     *         description="订单id",
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
    public function payment()
    {
        $this->results([],200,'成功','json');
    }

    /**
     * @OA\Post(
     *     path="/otc/order/release/{orderId}",
     *     summary="释放托管",
     *     operationId="releaseOrder",
     *     tags={"otc-order"},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="query",
     *         description="订单id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="transactionPassword",
     *         in="query",
     *         description="交易密码",
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
    public function release()
    {
        $this->results([],200,'成功','json');
    }

    /**
     * @OA\Post(
     *     path="/otc/order/review/{orderId}",
     *     summary="评论订单",
     *     operationId="reviewOrder",
     *     tags={"otc-order"},
     *     @OA\Parameter(
     *         name="orderId",
     *         in="query",
     *         description="订单id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="feedback",
     *         in="query",
     *         description="评价:1=好评,2=中评,3=差评",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32",
	 *             enum = {"1", "2", "3"},
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function review()
    {
        $this->results([],200,'成功','json');
    }

	/**
     * @OA\Get(
     *     path="/otc/transaction",
     *     summary="交易记录",
     *     operationId="transactionOrderOTC",
     *     tags={"otc-order"},
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
     *         description="排序字段，默认createTime",
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
     *         name="operation",
     *         in="query",
     *         description="操作类型:1=交易中心撮合交易,2=交易挂单,5=撤单,6=差价退回,12=充值,13=提现,14=C2C驳回,15=推荐赠送糖果,16=注册赠送糖果,17=实名赠送糖果,18=抽奖奖励,19=注册实名推荐赠送糖果解冻,20=平台奖励,28=PUSH交易,30=OTC充值,31=OTC提现,32=OTC买入,33=OTC卖出,34=OTC撤单,35=OTC过期,36=OTC重启",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32"
     *         )
     *     ),
	 *     @OA\Parameter(
     *         name="coinName",
     *         in="query",
     *         description="币种:BTC=比特币,LTC=莱特币",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
	 *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 example={"code":200,"data":{"data":{{"id":926,"dealType":2,"dealTotal":"50.00000000000000000000","fee":"0.00000000000000000000","market":"usdt","operation":13,"orderId":156,"orderNumber":"RW1205440109362710","remark":null,"status":1,"transactionCurrency":"usdt","transactionNumber":"201812050013289486462670336000","url":"http:\/\/btc-alicms-com.oss-cn-hangzhou.aliyuncs.com\/uploads\/2018\/11\/14\/f048c156-c97e-4d63-b2bb-da2f1d7265d0.png","userId":1,"createTime":1544010951000}},"total":41},"msg":"成功"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *     ),
     * )
     */
    public function lists()
    {
        return '{"code":200,"data":{"data":[{"id":926,"dealType":2,"dealTotal":"50.00000000000000000000","fee":"0.00000000000000000000","market":"usdt","operation":13,"orderId":156,"orderNumber":"RW1205440109362710","remark":null,"status":1,"transactionCurrency":"usdt","transactionNumber":"201812050013289486462670336000","url":"http:\/\/btc-alicms-com.oss-cn-hangzhou.aliyuncs.com\/uploads\/2018\/11\/14\/f048c156-c97e-4d63-b2bb-da2f1d7265d0.png","userId":1,"createTime":1544010951000}],"total":41},"msg":"成功"}';
    }

}
