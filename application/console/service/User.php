<?php
/**
 * Created by PhpStorm.
 * User: 10562
 * Date: 2018/10/31
 * Time: 10:19
 */

namespace app\api\service;


interface User
{
    /*--------------------------------------------------------用户信息更新-2018-30----------------------------------------------------*/

    /**
     * 展示用户信息
     * @return array
     */
    public function index();

    /**
     * 修改辅助货币
     * @param int $type 辅助货币类型1=usd,2=cny
     * @return array
     */
    public function assistant_coin($type);

    /**
     * 更换手机号
     * @param string $phone 手机号码
     * @param int $newCode 新手机号
     * @param string $password 密码
     * @param string $oldCode 动态码
     * @return array
     */
    public function change_phone($phone, $newCode, $password, $oldCode);

    /**
     * 邮箱认证
     * @param string $email 邮箱
     * @param string $emailCode 邮箱验证码
     * @return array
     */
    public function email($email, $emailCode);

    /**
     * 邮箱认证
     * @param string $email 邮箱
     * @param string $emailCode 邮箱验证码
     * @return array
     */
    public function change_email($step, $email, $oldCode, $newCode);

    /**
     * 修改昵称和头像
     * @param string $nickname 昵称
     * @param string $image 头像
     * @return array
     */
    public function nickname_image($nickname, $image);

    /**
     * 修改登录密码
     * @param string $oldPw 旧密码
     * @param string $newPw 新密码
     * @param int $smsCode 动态码
     * @return array
     */
    public function password($oldPw, $newPw, $smsCode);

    /**
     * 认证手机号
     * @param int $phone 手机号
     * @param int $smsCode 动态码
     * @return array
     */
    public function phone($phone, $smsCode);

    /**
     * 记住交易密码
     * @param string $tpw 记住或忘记密码
     * @return array
     */
    public function remember_tpw($tpw);

    /**
     * 设置交易密码
     * @param string $tpw 交易密码
     * @param int $smsCode 动态码
     * @return array
     */
    public function transaction_password($tpw, $smsCode);

    /**
     * 修改交易密码
     * @param string $oldPw 旧密码
     * @param string $newPw 新密码
     * @param int $smsCode 动态码
     * @return array
     */
    public function update_deal_password($oldPw, $newPw, $smsCode);

    /*--------------------------------------------------------用户邀请-2018-30-------------------------------------------------------*/
    /**
     * 我推荐的人-列表
     * @param int $page 分页数
     * @param int $pageSize 每页显示条数
     * @param string $sort 排序字段
     * @param string $order 排序方式
     * @return array
     */
    public function invitations($page, $pageSize, $sort, $order);

    /**
     * 推荐奖励、分红列表
     * @param int $page 分页数
     * @param int $pageSize 每页显示条数
     * @param string $sort 排序字段
     * @param string $order 排序方式
     * @return array
     */
    public function rewards($page, $pageSize, $sort, $order);

    /*--------------------------------------------------------用户资产-2018-30-------------------------------------------------------*/

    /**
     * 资产
     * @return array
     */
    public function assets($coinId);

    /**
     * 我的资产
     * @param string $market 交易市场
     * @return array
     */
    public function user_assets($market);

    /**
     * 交易记录
     * @param int $page 分页数
     * @param int $pageSize 每页显示条数
     * @param string $sort 排序字段
     * @param string $order 排序方式
     * @param int $operation 行为操作
     * @param string $coinName 币种简称
     * @return array
     */
    public function transaction_records($page, $pageSize, $sort, $order, $operation, $coinName);
}