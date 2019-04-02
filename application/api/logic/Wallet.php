<?php
/**
 * Created by PhpStorm.
 * User: 10562
 * Date: 2018/11/2
 * Time: 10:29
 */

namespace app\api\logic;


use app\common\library\CoinClient;
use app\common\library\DynamicCode;
use app\common\library\Ethereum;
use app\common\library\BlockChain;
use think\App;
use think\Db;
use think\Exception;
use Web3\Utils;

class Wallet extends Base implements \app\api\service\Wallet
{
    /**
     * get 获取平台收款地址, post 提交提币订单
     * @param int $coinId 币种ID
     * @param float $num 交易数量
     * @param string $tpw 交易密码
     * @param int $smsCode 动态码
     * @param string $toAddress 提币地址
     * @return mixed
     */
    public function address($coinId, $num, $tpw, $smsCode, $toAddress)
    {
        try {
            //获取币种配置信息
            $coin = Db(COIN)
                ->alias('a')
                ->field('a.*,b.address as summary_account_address')
                ->join(PREFIX.ADMINADDRESS . ' b', 'b.coin_name = a.coin_name AND b.status = 1', 'LEFT')
                ->where(['a.id' => $coinId])
                ->find();

            if ($this->request->isPost()) {

                //是否允许转出
                if(!$coin['outflow_type']){
                    return $this->warn(lang('The current currency is not transferable!'));
                }

                //动态码验证
                $dynamic_code = new DynamicCode();
                if (!$dynamic_code->DynamicCheck($smsCode, $this->user_info['phone'] ?: $this->user_info['email'])) {
                    return $this->warn(lang('Verification code error or expired!'));
                }

                //验证交易密码
                $user_model = Db(USER);
                $privacy = $user_model->field('transaction_password,salt')->where(['id' => $this->user_info['id']])->find();

                if (!$privacy['transaction_password']) {
                    return $this->warn(lang('Please set the transaction password first!'));
                }

                if (md5($tpw . $privacy['salt']) != $privacy['transaction_password']) {
                    return $this->warn(lang('Incorrect password!'));
                }

                //验证余额
                if ($num > Db(USERCOIN)->where(['user_id' => $this->user_info['id']])->value($coin['coin_name'])) {
                    return $this->warn(lang('Insufficient wallet balance!'));
                }

                //最小转出数量验证
                if($coin['outflow_min'] > $num){
                    return $this->warn(lang('最小转出量必须大于 {:num} {:coin_name}',['num'=>$coin['outflow_min'],'coin_name'=>$coin['coin_name']]));
                }

                //最小转出数量验证
                if($coin['outflow_max'] < $num){
                    return $this->warn(lang('最大转出量必须小于 {:num} {:coin_name}',['num'=>$coin['outflow_max'],'coin_name'=>$coin['coin_name']]));
                }

                //算取手续费&实际转出金额
                $fee = ($coin['outflow_fee'] ?$coin['outflow_fee']/100: 0) * $num;
                $real_amount = $num - $fee;

                //开启事务
                Db::startTrans();
                Db::execute('set autocommit=0');

                $finance_detail_model = model(FINANCEDETAIL);
                $user_coin_model = Db::table(PREFIX.USERCOIN);
                $user_coin_order_model = Db::table(PREFIX.USERCOINORDER);

                //准备数据
                $order_number = orderSn();
                $create_time = date('Y-m-d H:i:s');

                //执行插入
                $row[] = $user_coin_model->where(array('user_id' => $this->user_info['id']))->dec($coin['coin_name'], $num)->inc($coin['coin_name'] . '_frozen', $num)->update();
                $row[] = $order_id = $user_coin_order_model
                    ->insertGetId([
                        'order_number' => $order_number,
                        'user_id' => $this->user_info['id'],
                        'coin_id' => $coinId,
                        'coin_name' => $coin['coin_name'],
                        'from_address' => '',
                        'to_address' => $toAddress,
                        'txid' => '',
                        'amount' => $real_amount,
                        'fee' => $fee,
                        'total' => $num,
                        'inflow_confirm_number' => 0,
                        'type' => 2,
                        'create_time' => $create_time,
                        'status' => 1,
                        'gas' => 0,
                    ]);

                //小额自动转出
                $automatic = 0;
                if(isset($coin['outflow_automatic']) && $coin['outflow_automatic'] && $num < $coin['outflow_automatic']){
                    $automatic = 1;
                    //发起转出交易
                    $hash = BlockChain::instance()
                        ->setCoin($coin['coin_name'])
                        ->setLimit(FALSE,$coin['outflow_automatic'])
                        ->setLink($coin['wallet_service_ip'],$coin['wallet_service_port'],$coin['wallet_service_name'],$coin['wallet_service_password'])
                        ->sendTransaction($toAddress,$real_amount,$coin['summary_account_address'],$coin['wallet_service_password']);

                    if($hash){
                        //转出成功记录交易号&扣除冻结币种数量
                        $row[] = $user_coin_order_model->where(['id'=>$order_id])->update(['txid'=>$hash,'status'=>2]);
                        $row[] = $user_coin_model->where(array('user_id' => $this->user_info['id']))->setDec($coin['coin_name'] . '_frozen', $num);
                    }else{
                        //转出失败
                        $row[] = $user_coin_order_model->where(['id'=>$order_id])->update(['txid'=>$hash,'status'=>6]);
                    }
                }

                //记录财务流水
                $row[] = $finance_detail_model->insertGetId([
                    'user_id' => $this->user_info['id'],
                    'transaction_number' => $finance_detail_model->setTransactionNumberAttr(13),
                    'order_number' => $order_number,
                    'market' => $coin['coin_name'],
                    'operation' => 13,
                    'deal_type' => $automatic?2:4,
                    'transaction_currency' => $coin['coin_name'],
                    'deal_total' => $num,
                    'fee' => $automatic?$fee:0,
                    'encryption' => '',
                    'remark' => '',
                    'create_time' => $create_time,
                    'status' => $automatic?($hash?1:0):1,
                ]);

                if (check_array($row)) {
                    Db::commit();
                    return $this->warn(lang('success'), OPERATION_SUCCESS);
                } else {
                    Db::rollback();
                    return $this->warn(lang('failed'));
                }
            } else {
                $user_coin_model = Db(USERCOIN);
                if (!$address = $user_coin_model->where(['user_id' => $this->user_info['id']])->value($coin['coin_name'] . '_site')) {

                    //钱包联接信息验证
                    if (!$coin['wallet_service_ip'] || !$coin['wallet_service_port'] || !$coin['wallet_service_name'] || !$coin['wallet_service_password']) {
                        if ($this->debug) return $this->warn('Wallet configuration is incomplete!');
                    } else {
                        switch ($coin['coin_type']) {
                            case 3:
                                echo lang('over') . ' : ' . strtoupper($coin['coin_name']) . "<br><br>";
                                break;
                            case 1:
                                $CoinClient = new CoinClient($coin['wallet_service_name'], $coin['wallet_service_password'], $coin['wallet_service_ip'], $coin['wallet_service_port'], 5, array(), 1);
                                $json = $CoinClient->getnetworkinfo();
                                if (!isset($json['version']) || !$json['version']) {
                                    if ($this->debug) return $this->warn(lang('Communication connection failed!'));
                                }

                                //通过用户预留信息查找对应转账地址
                                $get_address = $CoinClient->getaddressesbyaccount($this->user_info['id'] . '-blockchain_1.0');
                                if (!is_array($get_address)) {
                                    //不存在则新创建
                                    $address = $CoinClient->getnewaddress($this->user_info['id'] . '-blockchain_1.0');
                                } else {
                                    $address = isset($get_address[0]) ? $get_address[0] : '';
                                }
                                break;
                            case 2:
                                //主账户地址验证
                                if (!isset($coin['summary_account_address']) || !$coin['summary_account_address']) {
                                    if ($this->debug) return $this->warn(lang('Main account not set!'));
                                } else {
                                    //连接ETH
                                    $ethereum = new Ethereum($coin['wallet_service_ip'], $coin['wallet_service_port']);//联接

                                    $version = $ethereum->web3_clientVersion();
                                    if (!$version || !$version->result) {
                                        if ($this->debug) return $this->warn(lang('Communication connection failed!'));
                                    } else {
                                        switch ($coin['protocol']) {
                                            case 'ETH':
                                            case 'ERC20':
                                                //获取新的收款地址
                                                $new_address = $ethereum->personal_newAccount($coin['wallet_service_password']);
                                                if ($new_address && isset($new_address->result) && is_string($new_address->result)) {
                                                    $address = $new_address->result;
                                                }
                                                break;
                                        }
                                    }
                                }
                                break;
                        }

                        if ($address) {
                            $user_coin_model->where(['user_id' => $this->user_info['id']])->update([$coin['coin_name'] . '_site' => $address]);
                        }
                    }
                }
                return $this->warn(lang('success'), OPERATION_SUCCESS, $address);
            }

        } catch (Exception $e) {
            Db::rollback();
            if ($this->debug) {
                return $this->warn($e->getMessage(), OPERATION_ERROR);
            } else {
                return $this->warn(lang('failed'), OPERATION_ERROR);
            }
        }
    }

    /**
     * get 查询提现钱包地址, post 新增提币地址
     * @param int $coinId 币种ID
     * @param int $page 分页页数
     * @param int $pageSize 每页条数
     * @param string $sort 排序字段
     * @param string $order 排序方式
     * @return mixed
     */
    public function addresses()
    {
        try {
            if ($this->request->isPost()) {
                //准备数据
                $coinId = $this->request->param('coinId');
                $walletAddress = $this->request->param('walletAddress');
                $walletLabel = $this->request->param('walletLabel');
                $tpw = $this->request->param('tpw');

                //验证参数
                $coin_model = Db(COIN);
                if (!$coin = $coin_model->where(['id' => $coinId])->find() || !$walletAddress || $tpw) {
                    return $this->warn(lang('Incorrect parameter!'));
                }

                //验证交易密码
                $user_model = Db(USER);
                $privacy = $user_model->field('transaction_password,salt')->where(['id' => $this->user_info['id']])->find();

                if (!$privacy['transaction_password']) {
                    return $this->warn(lang('Please set the transaction password first!'));
                }

                if (md5($tpw . $privacy['salt']) != $privacy['transaction_password']) {
                    return $this->warn(lang('Incorrect password!'));
                }

                //验证提币地址
//                switch ($coin['coin_type']){
//                    case 1:
//                        //RPC连接
//                        $CoinClient = new CoinClient($coin['wallet_service_name'], $coin['wallet_service_password'], $coin['wallet_service_ip'], $coin['wallet_service_port'], 5, array(), 1);
//
//                        //验证RPC请求是否正常
//                        $json = $CoinClient->getnetworkinfo();
//                        if (!isset($json['version']) || !$json['version']) {
//                            if ($this->debug)return $this->warn(lang('Communication connection failed!'));
//                        }
//
//                        //验证钱包地址正确性
//                        $valid_res = $CoinClient->validateaddress($walletAddress);
//                        if (!$valid_res['isvalid']) {
//                            return $this->warn(lang('Wallet is not effective!'));
//                        }
//                        break;
//                    case 2:
//                        if(!Utils::isAddress($walletAddress)){
//                            return $this->warn(lang('Wallet is not effective!'));
//                        }
//                        break;
//                }

                //验证地址是否已添加
                $user_wallet_model = Db(USERWALLET);
                if ($user_wallet_model->where(['wallet_address' => $walletAddress])->find()) {
                    return $this->warn(lang('The address has been used!'));
                }

                //记录用户提币地址
                $row = $user_wallet_model->insert([
                    'user_id' => $this->user_info['id'],
                    'coin_id' => $coinId,
                    'wallet_label' => $walletLabel,
                    'wallet_address' => $walletAddress,
                    'create_time' => date('Y-m-d H:i:s'),
                    'sort' => 0,
                    'status' => 1,
                ]);

                if ($row) {
                    return $this->warn(lang('success'), OPERATION_SUCCESS);
                } else {
                    return $this->warn(lang('failed'));
                }

            } else {
                //准备数据
                $coinId = $this->request->param('coinId');
                $page = $this->request->param('page', 1);
                $pageSize = $this->request->param('pageSize', 10);
                $sort = $this->request->param('sort', 'id');
                $order = $this->request->param('order', 'desc');

                $where = [];
                if ($coinId) {
                    $where['coin_id'] = $coinId;
                }

                $total = Db(USERWALLET)
                    ->where(['user_id' => $this->user_info['id']])
                    ->where($where)
                    ->count();

                $wallet = Db(USERWALLET)
                    ->field('id,user_id,coin_id,wallet_label,wallet_address,sort,status')
                    ->where(['user_id' => $this->user_info['id']])
                    ->where($where)
                    ->limit(($page - 1) * $pageSize, $pageSize)
                    ->order($sort . ' ' . $order)
                    ->select();

                if ($wallet !== false) {
                    return $this->warn(lang('success'), OPERATION_SUCCESS, ['data' => camelize($wallet), 'total' => $total]);
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
     * 查询充值提现订单
     * @param int $coinId 币种ID
     * @param int $page 分页页数
     * @param int $pageSize 每页条数
     * @param string $sort 排序字段
     * @param string $order 排序方式
     * @param int $type 订单类型:1=充值.2=提现
     * @return mixed
     */
    public function orders($coinId, $page, $pageSize, $sort, $order, $type,$status)
    {
        try {
            $where = [];
            if ($coinId) {
                $where['coin_id'] = $coinId;
            }
            if ($type) {
                $where['type'] = $type;
            }
            if($status){
                $where['status'] = $status;
            }
            $user_coin_order_model = Db(USERCOINORDER);

            $total = $user_coin_order_model
                ->where(['user_id' => $this->user_info['id']])
                ->where($where)
                ->count();

            $order_form = $user_coin_order_model
                ->where(['user_id' => $this->user_info['id']])
                ->where($where)
                ->limit(($page - 1) * $pageSize, $pageSize)
                ->order($sort . ' ' . $order)
                ->select();

            if ($order_form !== false) {
                if($order_form){
                    foreach ($order_form as $key => $value) {
                        $order_form[$key]['create_time'] = strtotime($value['create_time'])*1000;
                        $order_form[$key]['update_time'] = $value['update_time']?strtotime($value['update_time'])*1000:strtotime($value['create_time'])*1000;
                    }
                }
                return $this->warn(lang('success'), OPERATION_SUCCESS, ['data' => camelize($order_form), 'total' => $total]);
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
     * 删除提现钱包地址
     * @param int $addressId 钱包地址ID
     * @return mixed
     */
    public function remove($addressId)
    {
        try {
            if (!$addressId) {
                throw new Exception(lang('Incorrect parameter!'));
            }

            if (Db(USERWALLET)->where(['id' => $addressId, 'user_id' => $this->user_info['id']])->delete() !== false) {
                return $this->warn(lang('success'), OPERATION_SUCCESS);
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
