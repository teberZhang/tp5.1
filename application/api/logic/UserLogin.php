<?php
/**
 * Created by PhpStorm.
 * User: 10562
 * Date: 2018/10/29
 * Time: 11:15
 */

namespace app\api\logic;


use app\common\controller\Notice;
use app\common\library\AliyunSms;
use app\common\library\Captcha;
use app\common\library\DynamicCode;
use app\common\library\Email;
use app\common\library\LxSms;
use app\common\library\Particle;
use app\common\library\Redis;
use think\Exception;

class UserLogin extends Base implements \app\api\service\UserLogin
{
    public function addUser($data)
    {
        try {
            $UserModel = Db(USER);
            $str = "345678ABCDEFGHJKLMNPQRTUVWX";    //字符集
            $num = 8;       //字符集取多少位

//            $count = $KeyModel->count();    //已知
//            $limit = 1/10000;  //概率大于限定值时则字符集获取的总数+1
//            for(;;$num++){
//                $total = pow(strlen($str),$num);    //总数
//                $probability = $count/$total;       //概率
//                if($probability < $limit){
//                    break;
//                }
//            }

            $invite_str = '';
            $subscript = (string)mt_rand(pow(10, $num - 1) - 1, pow(10, $num) - 1);  //随机取得下标
            for ($subscript_num = 0; $subscript_num < $num; $subscript_num++) {
                $invite_str .= $str[$subscript[$subscript_num]];
            }

            $data['nickname'] = $invite_str;
            $data['invite_code'] = $invite_str;
            if ($UserModel->insert($data) !== false) {
                $id = $UserModel->getLastInsID();
                return (int)$id;
            }
        } catch (\Exception $e) {
            $_error = $e->getMessage();
            if (stripos(strtolower($_error), 'sqlstate') !== false) {
                $id = $this->addUser($data);
                return $id;
            } else {
                return $_error;
            }
        }
    }

    /**
     * 用户注册
     * @param string $userName 注册用户名(手机或邮箱)
     * @param string $password 登陆密码
     * @param static $inviteCode 邀请码
     * @param int $msgCode 短信验证码
     * @param int $step 1|2(第一步:验证手机或邮箱号并发送并验证动态码，第二步:设置登录密码)
     * @return mixed
     */
    public function register($step, $userName, $msgCode, $password, $inviteCode)
    {
        try {
            // 验证手机号或邮箱是否存在
            $result = model(USER)->verifyAccount($userName);
            if ($result['account']) {
                return $this->warn(lang('Please enter the correct user name or password'));
            }

            //Redis
            $redis_init = new Redis();

            //执行步骤
            switch ($step) {
                case 1:
                    //动态码验证
                    $dynamic_code = new DynamicCode();
                    if (!$dynamic_code->DynamicCheck($msgCode, $userName)) {
                        return $this->warn(lang('Verification code error or expired!'));
                    }

                    //设置Redis
                    $redis_init->set('register:' . $userName, 1);
                    $redis_init->expire('register:' . $userName, 300);

                    break;
                case 2:
                    //验证Redis是否有进行第一步的记录
                    if (!$redis_init->get('register:' . $userName)) {
                        return $this->warn(lang('Please submit the account to be verified!'));
                    }

                    //验证密码格式
                    if (!check($password, 'pwd')) {
                        return $this->warn(lang('The cipher format is 6~20 bit, and is any combination of  more than 2 combinations of letters, numbers, symbols and so on'));
                    }

                    //准备数据
                    $salt = md5(substr(str_shuffle('1234567890ABCDEFGHIJKLMNPQRSTUVWXYZ'), 0, 6));
                    $inviter = model(USER)->field('id,inviter_1')->where(['invite_code'=>$inviteCode])->find();
                    $data = [
                        $result['account_type'] => $userName,
                        'password' => md5($password . $salt),
                        'salt' => $salt,
                        'create_time' => date('Y-m-d H:i:s'),
                        'inviter_1' => $inviter?$inviter['id']:0,
                        'inviter_2' => $inviter?$inviter['inviter_1']:0,
                    ];

                    //防止推荐码重复的用户插入
                    $result = $this->addUser($data);
                    if (is_int($result)) {
                        $user_id = $result;
                    } else {
                        throw new Exception($result);
                    }

                    //添加用户财产
                    model(USERCOIN)->insert(['user_id' => $user_id]);

                    //清除redis记录
                    $redis_init->expire('register:' . $userName, 0);
                    break;
                default:
                    return $this->warn(lang('failed'));
                    break;
            }

            return $this->warn(lang('success'), OPERATION_SUCCESS);
        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * 用户登录
     * @param string $userName 用户名(手机或邮箱)
     * @param string $password 登陆密码
     * @param string $imgCode 图形验证码
     * @return mixed
     */
    public function welcome($userName, $password, $imgCode)
    {
        try {
            $model = model(USER);
            // 验证手机号或邮箱是否存在
            $result = $model->verifyAccount($userName, 4);
            if (!isset($result['account']) || !$result['account']) {
                return $this->warn(lang('Incorrect account or password!'));
            }

            //获取用户IP并密码错误次数
            $ip = $this->request->ip();
            $redis_init = new Redis();
            $count = $redis_init->get('login:' . $ip);

            //UUID
            $Particle = new Particle(31, 31);
            $next_id = $Particle->nextId();

            //验证图形验证码
            if ($count >= 2) {
                $captcha = new Captcha();
                if (!$imgCode || !$captcha->check($imgCode)) {
                    return $this->warn(lang('Error in graphic verification code!'),NO_RETURN_DATA,['limits'=>$redis_init->get('login:' . $ip)]);
                } else {
                    $redis_init->expire('login:' . $ip, 0);
                }
            }

            if ($count >= 5) {
                return $this->warn(lang('Please visit again after one hour if you try too many times'));
            }

            //验证密码并记录用户信息至Redis
            if (md5($password . $result['account']['salt']) != $result['account']['password']) {
                //密码输入错误次数记录
                $redis_init->set('login:' . $ip, ($count ?: 0) + 1);
                $redis_init->expire('login:' . $ip, 600);
                return $this->warn(lang('Incorrect account or password!'),NO_RETURN_DATA,['limits'=>(int)$count]);
            }

            //得到不包含敏感信息的用户数据
            $user_info = $model->getUserInfo($result['account']['id'], 1);

            //记录TOKEN
            $model->save(['token'=>'shiro:session:' . $next_id],['id'=>$user_info['id']]);

            //Redis存入
            $redis_init->set('shiro:session:' . $next_id, json_encode($user_info));
            $redis_init->expire('shiro:session:' . $next_id, 1800);
            Cookie($this->uuid, $next_id);
            $user_info['sockets'] = $next_id;

            return $this->warn(lang('success'), OPERATION_SUCCESS, camelize($user_info));
        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * App登录
     * @param string $userName 用户名(手机或邮箱)
     * @param string $password 登陆密码
     * @param string $imgCode 图形验证码
     * @param string $deviceId 设备码
     * @return mixed
     */
    public function app_login($userName, $password, $deviceId, $imgCode = '')
    {
        try {
            $model = model(USER);
            // 验证手机号或邮箱是否存在
            $result = $model->verifyAccount($userName, 4);
            if (!isset($result['account']) || !$result['account']) {
                return $this->warn(lang('Incorrect account or password!'));
            }

            //获取用户IP并密码错误次数
            $redis_init = new Redis();
            $count = $redis_init->get('login:' . $deviceId);

            //UUID
            $Particle = new Particle(31, 31);
            $next_id = $Particle->nextId();

            //验证图形验证码
            if ($count >= 2) {
                $captcha = new Captcha();
                if (!$imgCode || !$captcha->check($imgCode)) {
                    return $this->warn(lang('Error in graphic verification code!'),LACK_VALUE,['limits'=>(int)$redis_init->get('login:' . $deviceId)]);
                } else {
                    $redis_init->expire('login:' . $deviceId, 0);
                }
            }

            if ($count >= 5) {
                return $this->warn(lang('Please visit again after one hour if you try too many times'));
            }

            //验证密码并记录用户信息至Redis
            if (md5($password . $result['account']['salt']) != $result['account']['password']) {
                //密码输入错误次数记录
                $redis_init->set('login:' . $deviceId, ($count ?: 0) + 1);
                $redis_init->expire('login:' . $deviceId, 600);
                return $this->warn(lang('Incorrect account or password!'),NO_RETURN_DATA,['limits'=>(int)$count+1]);
            }

            //得到不包含敏感信息的用户数据
            $user_info = $model->getUserInfo($result['account']['id'], 1);

            //记录TOKEN
            $model->save(['token'=>'shiro:session:' . $next_id],['id'=>$user_info['id']]);

            //Redis存入
            $redis_init->set('shiro:session:' . $next_id, json_encode($user_info));
            $redis_init->expire('shiro:session:' . $next_id, 1800);
            Cookie($this->uuid,$next_id);

            return $this->warn(lang('success'), OPERATION_SUCCESS, camelize($user_info));
        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * 客户移动端短信登陆
     * @param string $userName 用户名(手机或邮箱)
     * @param int $smsCode 动态码
     * @param string $deviceId 设备码
     * @return mixed
     */
    public function app_sms_login($userName, $smsCode, $deviceId = '')
    {
        try {
            $model = model(USER);
            // 验证手机号或邮箱是否存在
            $result = $model->verifyAccount($userName, 4);

            if (!isset($result['account']) || !$result['account']) {
                return $this->warn(lang('Error in graphic verification code!'));
            }

            //获取用户IP并密码错误次数
            $redis_init = new Redis();
            $count = $redis_init->get('login:' . $deviceId);

            if ($count >= 5) {
                return $this->warn(lang('Please visit again after one hour if you try too many times'));
            }

            //UUID
            $Particle = new Particle(31, 31);
            $next_id = $Particle->nextId();

            //动态码验证
            $dynamic_code = new DynamicCode();
            if (!$dynamic_code->DynamicCheck($smsCode, $userName)) {
                throw new Exception(lang('Verification code error or expired!'));
            }

            //得到不包含敏感信息的用户数据
            $user_info = $model->getUserInfo($result['account']['id'], 1);

            //Redis存入
            $redis_init->set('shiro:session:' . $next_id, json_encode($user_info));
            $redis_init->expire('shiro:session:' . $next_id, 1800);
            Cookie($this->uuid, 'shiro:session:' . $next_id);

            return $this->warn(lang('success'), OPERATION_SUCCESS, camelize($user_info));
        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * 客户端用户登出
     * @return mixed
     */
    public function logout()
    {
        $uuid = Cookie($this->uuid, NULL);
        if ($uuid) {
            $redis_init = new Redis();
            $redis_init->expire($uuid, 0);
        }

        return $this->warn(lang('success'), OPERATION_SUCCESS);
    }

    /**
     * 忘记密码找回
     * @param string $password 密码
     * @param int $smsCode 动态码
     * @param string $userName 用户名(手机或邮箱)
     * @param int $step 1|2(第一步:验证手机或邮箱号并发送并验证动态码，第二步:设置登录密码)
     * @return mixed
     */
    public function forgot_password($step, $userName, $password, $smsCode)
    {
        try {
            // 验证手机号或邮箱是否存在
            $model = model(USER);
            $result = $model->verifyAccount($userName,1,'salt');
            if (!$result['account']) {
                return $this->warn(lang('The user does not exist!'));
            }

            //Redis
            $redis_init = new Redis();

            //执行步骤
            switch ($step) {
                case 1:

                    //动态码验证
                    $dynamic_code = new DynamicCode();
                    if (!$dynamic_code->DynamicCheck($smsCode, $userName)) {
                        return $this->warn(lang('Verification code error or expired!'));
                    }

                    //设置Redis
                    $redis_init->set('forgot-password:' . $userName, 1);
                    $redis_init->expire('forgot-password:' . $userName, 300);

                    break;
                case 2:
                    //验证Redis是否有进行第一步的记录
                    if (!$redis_init->get('forgot-password:' . $userName)) {
                        return $this->warn(lang('Please submit the account to be verified!'));
                    }

                    //验证密码格式
                    if (!check($password, 'pwd')) {
                        return $this->warn(lang('The cipher format is 6~20 bit, and is any combination of  more than 2 combinations of letters, numbers, symbols and so on'));
                    }

                    //重置密码
                    $model->where([$result['account_type'] => $userName])->update(['password' => md5($password.$result['account']['salt'])]);

                    //清除redis记录
                    $redis_init->expire('forgot-password:' . $userName, 0);
                    break;
                default:
                    return ['code' => OPERATION_ERROR, 'msg' => lang('failed'), 'data' => []];
                    break;
            }

            return $this->warn(lang('success'), OPERATION_SUCCESS);
        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * 获取邮箱验证码
     * @param string $email 邮箱
     * @param string $imgCode 图形验证码
     * @return mixed
     */
    public function email_code($email, $imgCode, $id, $captcha)
    {
        try {
            if (!check($email, 'email')) {
                return $this->warn(lang('Incorrect format of mobile phone number!'));
            }

            //验证验证码
            if (!$captcha->check($imgCode, $id)) {
                return $this->warn(lang('Error in graphic verification code!'));
            }


            //生成动态码
            $dynamic_code = new DynamicCode();
            $code = $dynamic_code->createCode($email);

            //发送短信
            $notice = new Notice(Email::instance());
            $error = $notice->send($email, $code);
            if ($error) {
                throw new Exception($error);
            }

            return $this->warn(lang('success'), OPERATION_SUCCESS);
        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * 获取短信验证码
     * @param int $phone 手机号
     * @param int $imgCode 动态码
     * @param string $apiName 接口名
     * @return mixed
     */
    public function sms_code($phone, $imgCode, $apiName, $id, $captcha)
    {
        try {
            if (!check($phone, 'mobile')) {
                return $this->warn(lang('Incorrect format of mobile phone number!'));
            }

            //验证验证码
            if (!$captcha->check($imgCode, $id)) {
                return $this->warn(lang('Error in graphic verification code!'));
            }

            //生成动态码
            $dynamic_code = new DynamicCode();
            $code = $dynamic_code->createCode($phone);

            //发送短信
            $notice = new Notice(AliyunSms::instance());
            $result = $notice->send($phone, $code);
            if (!$result['code']) {
                throw new Exception('*' . $result['msg'] . '*');
            }

            return $this->warn(lang('success'), OPERATION_SUCCESS);
        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * 获取图形验证码
     * @return mixed
     */
    public function img_code($captcha, $id = '')
    {
        return $captcha->entry($id);
    }
}
