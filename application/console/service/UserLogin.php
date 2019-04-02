<?php
/**
 * Created by PhpStorm.
 * User: mxc
 * Date: 2018/10/29
 * Time: 10:48
 */

namespace app\api\service;

interface UserLogin
{
    /**
     * 用户注册
     * @param string $userName      注册用户名(手机或邮箱)
     * @param string $password      登陆密码
     * @param static $inviteCode    邀请码
     * @param int $msgCode          短信验证码
     * @param int $step             1|2(第一步:验证手机或邮箱号并发送并验证动态码，第二步:设置登录密码)
     * @return mixed
     */
    public function register($step,$userName,$msgCode,$password,$inviteCode);

    /**
     * 用户登录
     * @param string $userName  用户名(手机或邮箱)
     * @param string $password  登陆密码
     * @param string $imgCode   图形验证码
     * @return mixed
     */
    public function welcome($userName,$password,$imgCode);

    /**
     * App登录
     * @param string $userName  用户名(手机或邮箱)
     * @param string $password  登陆密码
     * @param string $imgCode   图形验证码
     * @param string $deviceId  设备码
     * @return mixed
     */
    public function app_login($userName,$password,$deviceId,$imgCode = '');

    /**
     * 客户移动端短信登陆
     * @param string $userName  用户名(手机或邮箱)
     * @param int $smsCode      动态码
     * @param string $deviceId  设备码
     * @return mixed
     */
    public function app_sms_login($userName,$smsCode,$deviceId);

    /**
     * 客户端用户登出
     * @return mixed
     */
    public function logout();

    /**
     * 忘记密码找回
     * @param string $password  密码
     * @param int $smsCode      动态码
     * @param string $userName  用户名(手机或邮箱)
     * @param int $step         1|2(第一步:验证手机或邮箱号并发送并验证动态码，第二步:设置登录密码)
     * @return mixed
     */
    public function forgot_password($step,$userName,$password,$smsCode);

    /**
     * 获取邮箱验证码
     * @param string $email     邮箱
     * @param string $imgCode   图形验证码
     * @return mixed
     */
    public function email_code($email,$imgCode,$id,$captcha);

    /**
     * 获取短信验证码
     * @param int $phone        手机号
     * @param int $imgCode      动态码
     * @param string $apiName   接口名
     * @return mixed
     */
    public function sms_code($phone,$imgCode,$apiName,$id,$captcha);

    /**
     * 获取图形验证码
     * @return mixed
     */
    public function img_code($captcha,$id);
}