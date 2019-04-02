<?php
/**
 * Created by PhpStorm.
 * User: 10562
 * Date: 2018/10/31
 * Time: 10:27
 */

namespace app\api\logic;


use think\App;
use think\Db;
use think\Exception;
use app\common\library\Redis;
use app\common\library\DynamicCode;

class User extends Base implements \app\api\service\User
{
    protected $model = NULL;
    public function __construct()
    {
        parent::__construct();
        $this->model = model(USER);
    }

    /*--------------------------------------------------------用户信息更新-2018-30-------------------------------------------------------*/

    /**
     * 展示用户信息
     * @return array
     */
    public function index()
    {
        //得到不包含敏感信息的用户数据
        $user_info = $this->model->getUserInfo($this->user_info['id'], 1);
        return $this->warn(lang('success'), OPERATION_SUCCESS, camelize($user_info));
    }

    /**
     * 修改辅助货币
     * @param int $type 辅助货币类型1=usd,2=cny
     * @return array
     */
    public function assistant_coin($type)
    {
        try {
            if (!in_array($type, [1, 2])) {
                return $this->warn(lang('Incorrect parameter!'));
            }

            //修改默认辅助货币
            if ($user_info = model(USER)->updateUserInfo($this->user_info['id'],['assistant_currency' => $type])) {
                return $this->warn(lang('success'), OPERATION_SUCCESS, $user_info);
            } else {
                return $this->warn(lang('failed'));
            }

        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * 认证手机号
     * @param int $phone 手机号
     * @param int $smsCode 动态码
     * @return array
     */
    public function phone($phone, $smsCode)
    {
        try {
            //验证手机号码格式
            if (!check($phone, 'mobile')) {
                return $this->warn(lang('Incorrect format of mobile phone number!'));
            }

            //动态码验证
            $dynamic_code = new DynamicCode();
            if (!$dynamic_code->DynamicCheck($smsCode, $phone)) {
                return $this->warn(lang('{:name} verification code error or expired', ['name' => lang('mobile')]));
            }

            //手机号唯一性验证
            if(model(USER)->where(['phone'=>$phone])->value('phone')){
                return $this->warn(lang('This mobile phone number has been used!'));
            }

            if ($this->model->updateUserInfo($this->user_info['id'],['phone' => $phone])) {
                return $this->warn(lang('success'), OPERATION_SUCCESS, $this->user_info);
            } else {
                return $this->warn(lang('failed'));
            }

        } catch (Exception $exception) {
            if ($this->debug) {
                return $this->warn($exception->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * 更换手机号
     * @param string $phone 手机号码
     * @param int $newCode 新手机号
     * @param string $password 密码
     * @param string $oldCode 动态码
     * @return array
     */
    public function change_phone($phone, $newCode, $password, $oldCode)
    {
        return $this->warn(lang('This interface is not enabled!'));
        try {
            //验证手机号码格式
            if (check($phone, 'mobile')) {
                return $this->warn(lang('Incorrect format of mobile phone number!'));
            }

            //动态码验证
            $dynamic_code = new DynamicCode();
            if (!$dynamic_code->DynamicCheck($newCode, $phone)) {
                return $this->warn(lang('{:name} verification code error or expired', ['name' => lang('New mobile')]));
            }

            //验证旧手机号
            if ($this->user_info['phone']) {
                if (!$oldCode) {
                    return $this->warn(lang('Parameter {:arguments} cannot be empty!', ['arguments' => lang('oldCode')]));
                }

                if (!$dynamic_code->DynamicCheck($oldCode, $this->user_info['phone'])) {
                    return $this->warn(lang('{:name} verification code error or expired', ['name' => lang('old phone')]));
                }
            }

            //验证密码
            $salt = $this->model->where(['id' => $this->user_info['id']])->value('salt');
            if (md5($password . $salt) != $this->user_info['password']) {
                return $this->warn(lang('Incorrect password!'));
            }

            //修改用户信息
            if ($user_info = $this->model->updateUserInfo($this->user_info['id'],['phone' => $phone])) {
                return $this->warn(lang('success'), OPERATION_SUCCESS, $user_info);
            } else {
                return $this->warn(lang('failed'));
            }

        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * 邮箱认证
     * @param string $email 邮箱
     * @param string $emailCode 邮箱验证码
     * @return array
     */
    public function email($email, $emailCode)
    {
        try {
            //验证邮箱格式
            if (!check($email, 'email')) {
                return $this->warn(lang('Incorrect email format!'));
            }

            //动态码验证
            $dynamic_code = new DynamicCode();
            if (!$dynamic_code->DynamicCheck($emailCode, $email)) {
                return $this->warn(lang('{:name} verification code error or expired', ['name' => lang('Email')]));
            }

            //手机号唯一性验证
            if(model(USER)->where(['email'=>$email])->value('email')){
                return $this->warn(lang('This mobile phone number has been used!'));
            }

            //修改用户信息
            if ($user_info = $this->model->updateUserInfo($this->user_info['id'],['email' => $email])) {
                return $this->warn(lang('success'), OPERATION_SUCCESS, $user_info);
            } else {
                return $this->warn(lang('failed'));
            }

        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * 更换手机号
     * @param string $phone 手机号码
     * @param int $newCode 新手机号
     * @param string $password 密码
     * @param string $oldCode 动态码
     * @return array
     */
    public function change_email($step, $email, $oldCode, $newCode)
    {
        try {
            if (!in_array($step, [1, 2])) {
                return $this->warn(lang('Incorrect parameter!'));
            }

            //Redis
            $redis_init = new Redis();
            $dynamic_code = new DynamicCode();

            //执行步骤
            switch ($step) {
                case 1:
                    //用户邮箱未设置
                    if (!$this->user_info['email']) {
                        return $this->warn(lang('The user does not exist!'));
                    }

                    //动态码验证
                    if (!$dynamic_code->DynamicCheck($oldCode, $this->user_info['email'])) {
                        return $this->warn(lang('Verification code error or expired!'));
                    }

                    //设置Redis
                    $redis_init->set('change-email:' . $this->user_info['email'], 1);
                    $redis_init->expire('change-email:' . $this->user_info['email'], 300);

                    return $this->warn(lang('success'), OPERATION_SUCCESS);
                    break;
                case 2:
                    //验证Redis是否有进行第一步的记录
                    if (!$redis_init->get('change-email:' . $this->user_info['email'])) {
                        return $this->warn(lang('Please submit the account to be verified!'));
                    }

                    //验证邮箱格式
                    if (!check($email, 'email')) {
                        return $this->warn(lang('Incorrect email format!'));
                    }

                    //动态码验证
                    if (!$dynamic_code->DynamicCheck($newCode, $email)) {
                        return $this->warn(lang('Verification code error or expired!'));
                    }

                    //新邮箱地址不能和旧邮箱一样
                    if ($this->user_info['email'] == $email) {
                        return $this->warn(lang('The new email address cannot be the same as the old one!'));
                    }

                    // 验证手机号或邮箱是否存在
                    if ($this->model->where(['email' => $email])->where('id', '<>', $this->user_info['id'])->select()) {
                        return $this->warn(lang('User already exists!'));
                    }

                    //清除redis记录
                    $redis_init->expire('change-email:' . $this->user_info['email'], 0);

                    //修改用户信息
                    if ($user_info = $this->model->updateUserInfo($this->user_info['id'],['email' => $email])) {
                        return $this->warn(lang('success'), OPERATION_SUCCESS, $user_info);
                    } else {
                        return $this->warn(lang('failed'));
                    }
                    break;
                default:
                    return ['code' => OPERATION_ERROR, 'msg' => lang('failed'), 'data' => []];
                    break;
            }

        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * 修改昵称和头像
     * @param string $nickname 昵称
     * @param string $image 头像
     * @return array
     */
    public function nickname_image($nickname, $image)
    {
        try {
            //验证参数
            if (!$nickname) {
                return $this->warn(lang('Parameter {:arguments} cannot be empty!', ['arguments' => lang('nickname')]));
            }
            if (!$image) {
                return $this->warn(lang('Parameter {:arguments} cannot be empty!', ['arguments' => lang('image')]));
            }

            if(!check($nickname,'nickname')){
                return $this->warn(lang('Please fill in as required!'));
            }

            //修改用户信息
            if ($this->model->updateUserInfo($this->user_info['id'],['nickname' => $nickname, 'image' => $image])) {
                return $this->warn(lang('success'), OPERATION_SUCCESS, $this->user_info);
            } else {
                return $this->warn(lang('failed'));
            }

        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * 修改登录密码
     * @param string $oldPw 旧密码
     * @param string $newPw 新密码
     * @param int $smsCode 动态码
     * @return array
     */
    public function password($oldPw, $newPw, $smsCode)
    {
        try {
            if (!check($newPw, 'pwd')) {
                return $this->warn(lang('Incorrect password format!'));
            }

            //动态码验证
            $dynamic_code = new DynamicCode();
            if (!$dynamic_code->DynamicCheck($smsCode, $this->user_info['phone'] ?: $this->user_info['email'])) {
                return $this->warn(lang('{:name} verification code error or expired', ['name' => lang('mobile')]));
            }

            //验证密码
            $user_privacy = Db(USER)->field('password,salt')->where(['id' => $this->user_info['id']])->find();
            if (md5($oldPw . $user_privacy['salt']) != $user_privacy['password']) {
                return $this->warn(lang('Incorrect password!'));
            }

            //修改用户信息
            if ($this->model->updateUserInfo($this->user_info['id'],['password' => md5($newPw . $user_privacy['salt'])])) {
                return $this->warn(lang('success'), OPERATION_SUCCESS, $this->user_info);
            } else {
                return $this->warn(lang('failed'));
            }

        } catch (Exception $exception) {
            if ($this->debug) {
                return $this->warn($exception->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * 记住交易密码
     * @param string $tpw 记住或忘记密码
     * @return array
     */
    public function remember_tpw($tpw)
    {
        try {
            //验证交易密码
            $privacy = $this->model->field('transaction_password,salt')->where(['id'=>$this->user_info['id']])->find();

            if(!$privacy['transaction_password']){
                return $this->warn(lang('Please set the transaction password first!'));
            }

            if (md5($tpw . $privacy['salt']) != $privacy['transaction_password']) {
                return $this->warn(lang('Incorrect password!'));
            }

            //修改用户信息
            if (Db::execute('UPDATE '. tableConvert(USER,true) .' SET is_remember = !is_remember where id = '.$this->user_info['id'])) {
                return $this->warn(lang('success'), OPERATION_SUCCESS,$this->user_info);
            } else {
                return $this->warn(lang('failed'));
            }

        } catch (Exception $exception) {
            if ($this->debug) {
                return $this->warn($exception->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * 设置交易密码
     * @param string $tpw 交易密码
     * @param int $smsCode 动态码
     * @return array
     */
    public function transaction_password($tpw, $smsCode)
    {
        try {
            if (!check($tpw, 'pwd')) {
                return $this->warn(lang('Incorrect password format!'));
            }

            //动态码验证
            $this->verify($smsCode);

            //验证密码
            $user_privacy = Db(USER)->field('salt')->where(['id' => $this->user_info['id']])->find();

            //修改用户信息
            if ($this->model->updateUserInfo($this->user_info['id'],['transaction_password' => md5($tpw . $user_privacy['salt'])])) {
                return $this->warn(lang('success'), OPERATION_SUCCESS, $this->user_info);
            } else {
                return $this->warn(lang('failed'));
            }

        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }


    /**
     * 修改交易密码
     * @param string $oldPw 旧密码
     * @param string $newPw 新密码
     * @param int $smsCode 动态码
     * @return array
     */
    public function update_deal_password($oldPw, $newPw, $smsCode)
    {
    }

    public function verify($code, $account = NULL)
    {
        //设置读取方式
        if (!$account) {
            $account = $this->user_info['phone'] ?: $this->user_info['email'];
        }

        //验证参数
        if (!$account) {
            throw new Exception(lang('Incorrect parameter!'));
        }

        //动态码验证
        $dynamic_code = new DynamicCode();
        if (!$dynamic_code->DynamicCheck($code, $account)) {
            throw new Exception(lang('Dynamic verification code error or expired!'));
        }
    }


    /*--------------------------------------------------------用户邀请-2018-30-------------------------------------------------------*/
    public function invitations($page, $pageSize, $sort, $order)
    {
        try {
            $total = $this->model
                ->where(['inviter_1' => $this->user_info['id']])
                ->whereOr(['inviter_2' => $this->user_info['id']])
                ->count();

            $invite_list = $this->model
                ->field('id,phone,email,IF(password = "",0,1) as password,IF(transaction_password = "",0,1) as transaction_password,is_remember,nickname,image,assistant_currency,bank_card_flag,exchange_flag,invite_code,inviter_1,inviter_2,status,login_ip,is_deleted,create_time')
                ->where(['inviter_1' => $this->user_info['id']])
                ->whereOr(['inviter_2' => $this->user_info['id']])
                ->limit(($page - 1) * $pageSize, $pageSize)
                ->order($sort . ' ' . $order)
                ->select();

            if ($invite_list) {
                return $this->warn(lang('success'), OPERATION_SUCCESS, ['data' => camelize($invite_list), 'total' => $total]);
            } else {
                return $this->warn(lang('failed'));
            }

        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * 我推荐的人-列表
     * @param int $page 分页数
     * @param int $pageSize 每页显示条数
     * @param string $sort 排序字段
     * @param string $order 排序方式
     * @return array
     */
    public function rewards($page, $pageSize, $sort, $order)
    {
        try {
            $model = model(GIFTINVITE);
            $total = $model
                ->where(['gainer_id' => $this->user_info['id']])
                ->count();

            $rewards_list = $model
                ->where(['gainer_id' => $this->user_info['id']])
                ->limit(($page - 1) * $pageSize, $pageSize)
                ->order($sort . ' ' . $order)
                ->select()
                ->toArray();

            if ($rewards_list !== false) {
                return $this->warn(lang('success'), OPERATION_SUCCESS, ['data' => camelize($rewards_list), 'total' => $total]);
            } else {
                return $this->warn(lang('failed'));
            }

        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /*--------------------------------------------------------用户资产-2018-30-------------------------------------------------------*/

    /**
     * 资产
     * @return array
     */
    public function assets($coinId)
    {
        try {
            if ($this->request->isPost()) {
                $sort = Db(USERCOIN)->where(['user_id' => $this->user_info['id']])->value('sort');
                if (Db(USERCOIN)->where(['user_id' => $this->user_info['id']])->update(['sort' => ',' . $coinId.str_replace(',' . $coinId,'',$sort)]) !== false) {
                    return $this->warn(lang('success'), OPERATION_SUCCESS);
                } else {
                    return $this->warn(lang('failed'));
                }
            } else {
                //用户资产
                $user_assets = Db(USERCOIN)
                    ->where(['user_id' => $this->user_info['id']])
                    ->find();

                //币种信息
                $sort = (trim($user_assets['sort'],',')?:0);
                $coin = Db(COIN)
                    ->field('id,coin_name,concat(coin_type) as coin_type,imgs as image,inflow_confirm_number,inflow_type as recharge_status,outflow_type as withdraw_status')
                    ->orderRaw('CASE WHEN id in ('.$sort.') THEN 0 ELSE 1 END')
                    ->orderRaw('coin_name')
                    ->select();

                //平台币汇率
                $exchange_config = Db(EXCHANGECONFIG)
                    ->field('coin_name,proportion')
                    ->find();

                //各个交易市场最新比例
                $market = Db(MARKET)
                    ->field('market,recent_quotation')
                    ->column('recent_quotation', 'market');

                //总合资产
                $total_assets_cny = 0;
                $total_assets_usd = 0;
                $btc = 0;
                $redis = new Redis();
                //拼装输出信息
                foreach ($coin as $keys => &$value) {
                    //用户资产
                    $value['usable'] = (float)($user_assets[$value['coin_name']] + 0);
                    $value['frozen'] = (float)($user_assets[$value['coin_name'] . '_frozen'] + 0);
                    $value['total'] = (float)($value['frozen'] + $value['usable']);

                    //资产折合
                    $market_cny = $value['coin_name'] . '_' . $exchange_config['coin_name'];    //cny
                    //$market_usd = $value['coin_name'] . '_' . 'usd';    //cny
                    $coinEx = $redis->hGet('market:new:price',$market_cny.':new:price');
                    $total_assets_cny += (isset($market[$market_cny])&&$coinEx ? trim($coinEx,'"') : 1) * $value['total'] * $exchange_config['proportion'];
                    $total_assets_usd += (isset($market[$market_cny])&&$coinEx ? trim($coinEx,'"') : 1) * $value['total'];
                }
//                p($coin);die;

                if ($coin !== false) {
                    return $this->warn(lang('success'), OPERATION_SUCCESS, ['coinList' => camelize($coin), 'totalAssetsCny' => $total_assets_cny, 'totalAssetsUsd' => $total_assets_usd]);
                } else {
                    return $this->warn(lang('failed'));
                }
            }

        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * 我的资产
     * @param string $market 交易市场
     * @return array
     */
    public function user_assets($market)
    {
        try {
            //判断交易市场是否存在
            $market = addslashes($market);
            $field = explode('_',$market);
            if(is_array($field) && count($field) == 2){
                $user_assets = model(USERCOIN)->field($field)->where(['user_id' => $this->user_info['id']])->find();

                if ($user_assets !== false) {
                    return $this->warn(lang('success'), OPERATION_SUCCESS, camelize($user_assets));
                } else {
                    return $this->warn(lang('failed'));
                }
            }
            return $this->warn(lang('failed'));
        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

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
    public function transaction_records($page, $pageSize, $sort, $order, $operation, $coinName)
    {
        try {
//            $where = [];
            $where = ['a.user_id'=>$this->user_info['id']];

            //交易类型
            if ($operation) {
                $where['operation'] = $operation;
            }

            //币种简称
            if ($coinName) {
                $where['transaction_currency'] = $coinName;
            }

            //查询数据
            $model = model(FINANCEDETAIL);

            $total = $model
                ->alias('a')
                ->join(tableConvert(COIN) . ' b', 'a.transaction_currency = b.coin_name')
                ->where($where)
                ->count();

            $detail = $model
                ->alias('a')
                ->field('a.id,a.deal_type,a.deal_total,a.fee,a.market,a.operation,a.order_id,a.order_number,a.remark,a.status,a.transaction_currency,a.transaction_number,b.imgs as url,a.user_id,unix_timestamp(a.create_time)*1000 as create_time')
                ->join(tableConvert(COIN) . ' b', 'a.transaction_currency = b.coin_name')
                ->where($where)
                ->limit(($page - 1) * $pageSize, $pageSize)
                ->order($sort . ' ' . $order)
                ->select()
                ->toArray();

            if ($detail !== false) {
                return $this->warn(lang('success'), OPERATION_SUCCESS, ['data' => camelize($detail),'total'=>$total]);
            } else {
                return $this->warn(lang('failed'));
            }

        } catch (Exception $e) {
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

}
