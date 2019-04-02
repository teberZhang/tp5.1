<?php
/**
 * Created by PhpStorm.
 * User: 10562
 * Date: 2018/10/31
 * Time: 15:20
 */

namespace app\api\logic;


use app\common\library\BlockChain;
use app\common\library\CoinClient;
use app\common\library\CoinToken;
use phpseclib\Math\BigInteger;
use think\Db;
use think\Exception;

class Crontab implements \app\api\service\Crontab
{
    protected $coin = [];

    //用户充值
    public function recharge($coin = '')
    {
        $where = [];
        if ($coin != null) {
            $where['a.coin_name'] = $coin;
        }
        $coinList = Db(COIN)
            ->alias('a')
            ->field('a.*,b.address as summary_account_address')
            ->join(tableConvert(ADMINADDRESS) . ' b', 'b.coin_name = a.coin_name AND b.status = 1', 'LEFT')
            ->where($where)
            ->select();
        foreach ($coinList as $coin) {
            $this->scanTrade($coin);
        }
    }

    public function scanTrade($coin)
    {
        echo 'START : ' . strtoupper($coin['coin_name']) . "<br>\n";
        //钱包联接信息验证
        if (!$coin['wallet_service_ip'] || !$coin['wallet_service_port'] || !$coin['wallet_service_name'] || !$coin['wallet_service_password']) {
            echo lang('Wallet configuration is incomplete!') . "<br>";
        } else {

            //START
            $row = [];
            Db::startTrans();
            Db::execute('set autocommit=0');

            try {
                $user_model = model(USER);
                $user_coin_model = model(USERCOIN);
                $admin_address_model = model(ADMINADDRESS);
                $user_coin_order_model = model(USERCOINORDER);
                $finance_detail_model = model(FINANCEDETAIL);

                switch ($coin['coin_type']) {
                    case 3:
                        break;
                    case 1:

                        $CoinClient = new CoinClient($coin['wallet_service_name'], $coin['wallet_service_password'], $coin['wallet_service_ip'], $coin['wallet_service_port'], 5, array(), 1);
                        $json = $CoinClient->getnetworkinfo();

                        if (!isset($json['version']) || !$json['version']) {
                            echo lang('ERROR') . ' : ' . strtoupper($coin['coin_name']) . lang('Communication connection failed!') . "<br>";
                            continue;
                        }

                        $listtransactions = $CoinClient->listtransactions('*', 100, 0);
                        if (!is_array($listtransactions)) {
                            echo lang('No transaction data!') . '<br>';
                            continue;
                        } else {
                            echo lang('NODES') . ' : ' . count($listtransactions) . "<br>";
                        }

                        krsort($listtransactions);

                        foreach ($listtransactions as $trans) {
                            //匿名转账
                            if (!isset($trans['account']) || !isset($trans['address']) || !$trans['account'] || !$trans['address']) {
                                continue;
                            }

                            //验证是否是平台用户
                            if (!($user_coin = $user_coin_model->where([$coin['coin_name'] . '_site' => $trans['address']])->find())) {
                                continue;
                            }

                            //已确认过的记录
                            if ($user_coin_order_model->where(array('txid' => $trans['txid'], 'inflow_confirm_number' => ['egt', 1]))->find()) {
                                continue;
                            }

                            //接收记录
                            if ($trans['category'] == 'receive') {

                                //验证网络确认
                                if ($trans['confirmations'] < $coin['inflow_confirm_number']) {
                                    $confirmations = intval($trans['confirmations'] - $coin['inflow_confirm_number']);
                                } else {
                                    $confirmations = 1;
                                    $row[] = $user_coin_model->where(array('user_id' => $user_coin['user_id']))->setInc($coin['coin_name'], $trans['amount']);
                                }

                                if ($res = $user_coin_order_model->where(array('txid' => $trans['txid']))->find()) {
                                    if ($res['confirmations'] != $confirmations) {
                                        $row[] = $user_coin_order_model->update(array('id' => $res['id'], 'update_time' => date("Y-m-d H:i:s"), 'confirmations' => intval($trans['confirmations'] - $coin['inflow_confirm_number'])));
                                    }
                                } else {
                                    $order = orderSn();
                                    $create_time = date("Y-m-d H:i:s", time());
                                    $row[] = $user_coin_order_model
                                        ->insertGetId(array(
                                            'order_number' => $order,
                                            'user_id' => $user_coin['user_id'],
                                            'coin_id' => $coin['id'],
                                            'coin_name' => $coin['coin_name'],
                                            'to_address' => $trans['address'],
                                            'txid' => $trans['txid'],
                                            'amount' => $trans['amount'],
                                            'total' => $trans['amount'],
                                            'fee' => 0,
                                            'type' => 1,
                                            'create_time' => $create_time,
                                            'inflow_confirm_number' => $confirmations,
                                            'status' => 2
                                        ));

                                    $row[] = $finance_detail_model->insertGetId([
                                        'user_id' => $user_coin['user_id'],
                                        'transaction_number' => $finance_detail_model->setTransactionNumberAttr(12),
                                        'order_number' => $order,
                                        'market' => $coin['coin_name'],
                                        'operation' => 12,
                                        'deal_type' => 1,
                                        'transaction_currency' => $coin['coin_name'],
                                        'deal_total' => $trans['amount'],
                                        'encryption' => '',
                                        'remark' => '',
                                        'create_time' => $create_time,
                                        'status' => 1,
                                    ]);
                                }
                                continue;
                            }
                        }
                        break;
                    case 2:
                        $ethereum = new \app\common\library\Ethereum($coin['wallet_service_ip'], $coin['wallet_service_port']);//联接

                        $version = $ethereum->web3_clientVersion();
                        if (!$version || !$version->result) {
                            echo lang('ERROR') . ' : ' . strtoupper($coin['coin_name']) . lang('Communication connection failed!') . "<br>";
                            continue;
                        }

                        $blockNum = $ethereum->eth_blockNumber(TRUE);//查询最后的区块号

                        switch ($coin['protocol']) {
                            case 'ETH':
                                for ($bn = $blockNum; $bn >= $blockNum - 100; $bn--) {
                                    $block = $ethereum->eth_getBlockByNumber('0x' . dechex($bn));
                                    if (empty($block->result->transactions)) {
                                        continue;
                                    } else {
                                        foreach ($block->result->transactions as $tx) {

                                            //是否存在转账金额/验证是否是以太坊交易
                                            if (!$tx->value || $tx->value == '0x0') {
                                                continue;
                                            }

                                            //验证是否是平台用户
                                            if (!$user_id = $user_coin_model->field('user_id')->where([$coin['coin_name'] . '_site' => $tx->to])->value('user_id')) {
                                                continue;
                                            }

                                            //已确认过的记录
                                            if ($log = $user_coin_order_model->where(array('txid' => $tx->hash, 'inflow_confirm_number' => ['egt', 1]))->find()) {
                                                continue;
                                            }

                                            //取得并验证交易金额
                                            $money = base_convert($tx->value, 16, 10) / bcpow(10, 18);
                                            if (!$money || $money <= 0) {
                                                continue;
                                            }

                                            //验证用户信息
                                            if (!$user = $user_model->where(['id' => $user_id])->find()) {
                                                continue;
                                            }

                                            //数据记录
                                            $order = orderSn();
                                            $create_time = date("Y-m-d H:i:s", time());
                                            $row[] = $user_coin_model->where(array('user_id' => $user['id']))->setInc($coin['coin_name'], $money);
                                            $row[] = $user_coin_order_model
                                                ->insertGetId(array(
                                                    'order_number' => $order,
                                                    'user_id' => $user_id,
                                                    'coin_id' => $coin['id'],
                                                    'coin_name' => $coin['coin_name'],
                                                    'from_address' => $tx->from,
                                                    'to_address' => $tx->to,
                                                    'txid' => $tx->hash,
                                                    'amount' => $money,
                                                    'total' => $money,
                                                    'fee' => 0,
                                                    'type' => 1,
                                                    'create_time' => $create_time,
                                                    'inflow_confirm_number' => 1,
                                                    'status' => 2
                                                ));

                                            $row[] = $finance_detail_model->insertGetId([
                                                'user_id' => $user_id,
                                                'transaction_number' => $finance_detail_model->setTransactionNumberAttr(12),
                                                'order_number' => $order,
                                                'market' => $coin['coin_name'],
                                                'operation' => 12,
                                                'deal_type' => 1,
                                                'transaction_currency' => $coin['coin_name'],
                                                'deal_total' => $money,
                                                'encryption' => '',
                                                'remark' => '',
                                                'create_time' => $create_time,
                                                'status' => 1,
                                            ]);

                                            //汇总
                                            if (check_array($row)) {
//                                            Db::commit();

                                                //验证主账户
                                                if (!$coin['summary_account_address']) {
                                                    throw new Exception(lang('Main account not set!'));
                                                }

                                                //转出账号解锁
                                                $unlock = $ethereum->personal_unlockAccount($tx->to, $coin['wallet_service_password']);
                                                if ($unlock && isset($unlock->result) && $unlock->result) {
                                                    //主账户
                                                    if (!$coin['summary_account_address']) {
                                                        echo lang('Main account not set!');
                                                        continue;
                                                    }

                                                    //准备数据
                                                    $gas = '21000';
                                                    $gasPrice = '0.000000015';
                                                    $decimals = bcpow(10, 18);//小数位换算
                                                    $value = bcmul($money - $gasPrice, $decimals);

                                                    //准备数据
                                                    $transaction[0] = [
                                                        'from' => $tx->to,
                                                        'to' => $coin['summary_account_address'],
                                                        'gas' => '0x' . base_convert($gas, 10, 16),
                                                        'value' => '0x' . base_convert($value, 10, 16),
                                                    ];

                                                    //发起交易
                                                    $result = $ethereum->eth_sendTransaction($transaction);
                                                    if ($result && isset($result->result) && $result->result) {
                                                        p($result);
                                                    }
                                                }
                                            } else {
                                                Db::rollback();
                                            }
                                            continue;
                                        }
                                    }
                                }
                                break;
                            case 'ERC20':

                                //代币合约
                                $CoinToken = new CoinToken($coin['smart_contract_address']);

                                //TOKEN小数
                                $decimals = $CoinToken->call('decimals');

                                for ($bn = $blockNum; $bn >= $blockNum - 500; $bn--) {
                                    $block = $ethereum->eth_getBlockByNumber('0x' . dechex($bn));
                                    if (empty($block->result->transactions)) {
                                        continue;
                                    } else {
                                        foreach ($block->result->transactions as $tx) {

                                            //验证合约
                                            if (strcasecmp($tx->to, $coin['smart_contract_address']) !== 0) {
                                                continue;
                                            }

                                            //验证input内容
                                            if (strlen($tx->input) < 104) {
                                                continue;
                                            }

                                            //验证是否是平台用户
                                            $recipients = '0x' . substr($tx->input, -104, 40);
                                            if (!$recipients || !$user_id = $user_coin_model->field('user_id')->where([$coin['coin_name'] . '_site' => $recipients])->value('user_id')) {
                                                continue;
                                            }

                                            //验证金额
                                            $money = base_convert('0x' . substr($tx->input, -64, 64), 16, 10) / bcpow(10, $decimals);
                                            if (!$money || $money <= 0) {
                                                continue;
                                            }

                                            //已确认过的记录
                                            if ($log = $user_coin_order_model->where(array('txid' => $tx->hash, 'inflow_confirm_number' => ['egt', 1]))->find()) {
                                                continue;
                                            }

                                            //验证用户信息
                                            if (!$user = $user_model->where(['id' => $user_id])->find()) {
                                                continue;
                                            }

                                            //数据记录
                                            $order = orderSn();
                                            $create_time = date("Y-m-d H:i:s", time());
                                            $row[] = $user_coin_model->where(array('user_id' => $user['id']))->setInc($coin['coin_name'], $money);
                                            $row[] = $user_coin_order_model
                                                ->insertGetId(array(
                                                    'order_number' => $order,
                                                    'user_id' => $user_id,
                                                    'coin_id' => $coin['id'],
                                                    'coin_name' => $coin['coin_name'],
                                                    'from_address' => $tx->from,
                                                    'to_address' => $recipients,
                                                    'txid' => $tx->hash,
                                                    'amount' => $money,
                                                    'total' => $money,
                                                    'fee' => 0,
                                                    'type' => 1,
                                                    'create_time' => $create_time,
                                                    'inflow_confirm_number' => 1,
                                                    'status' => 2
                                                ));

                                            $row[] = $finance_detail_model->insertGetId([
                                                'user_id' => $user_id,
                                                'transaction_number' => $finance_detail_model->setTransactionNumberAttr(12),
                                                'order_number' => $order,
                                                'market' => $coin['coin_name'],
                                                'operation' => 12,
                                                'deal_type' => 1,
                                                'transaction_currency' => $coin['coin_name'],
                                                'deal_total' => $money,
                                                'encryption' => '',
                                                'remark' => '',
                                                'create_time' => $create_time,
                                                'status' => 1,
                                            ]);

                                            //交易记录完成
                                            if (check_array($row)) {
                                                Db::commit();

                                                //验证主账户
                                                if (!$coin['summary_account_address']) {
                                                    throw new Exception(lang('Main account not set!'));
                                                }

                                                //转出账户解锁
                                                $unlock = $ethereum->personal_unlockAccount($coin['summary_account_address'], $coin['wallet_service_password']);

                                                if ($unlock && $unlock->result) {

                                                    //准备数据
                                                    $gas = '21000';
                                                    $gasPrice = '0.000000015';
                                                    $decimals = bcpow(10, 18);//小数位换算
                                                    $value = bcmul($money - $gasPrice, $decimals);

                                                    //准备数据
                                                    $transaction[0] = [
                                                        'from' => $tx->to,
                                                        'to' => $coin['summary_account_address'],
                                                        'gas' => '0x' . base_convert($gas, 10, 16),
                                                        'value' => '0x' . base_convert($value, 10, 16),
                                                    ];

                                                    //发起交易
                                                    $result = $ethereum->eth_sendTransaction($transaction);
                                                    if ($result && isset($result->result) && $result->result) {
                                                        p($result);
                                                    }
                                                    //汇总手续费
                                                    $gas = '60000';
                                                    $gasPrice = '0.000000015';
                                                    $decimals = bcpow(10, 18);//小数位换算

                                                    //转给子账户作为手续费的ETH
                                                    $transaction = [
                                                        'from' => $coin['summary_account_address'],
                                                        'to' => $recipients,
                                                        'gas' => '0x' . base_convert($gas, 10, 16),
                                                        'value' => '0x' . base_convert(bcmul($gasPrice, $decimals), 10, 16),
                                                    ];

                                                    $result = $ethereum->eth_sendTransaction($transaction);

                                                    //代币汇总
                                                    if ($result && isset($result->result) && $result->result) {
                                                        $token_result = $CoinToken->transfer($recipients, $coin['summary_account_address'], $money);
                                                    }
                                                }
                                            } else {
                                                Db::rollback();
                                            }
                                            continue;
                                        }
                                    }
                                }
                                break;
                        }
                        break;
                }

                Db::commit();
            } catch (Exception $exception) {
                echo $exception->getMessage();
                Db::rollback();
            }
        }

        echo 'END : ' . strtoupper($coin['coin_name']) . "<br><br>\n";
    }

    public function scanTrade1($coin)
    {

        //START
        $row = [];
        Db::startTrans();
        Db::execute('set autocommit=0');
        $user_model = model(USER);
        $user_coin_model = model(USERCOIN);
        $admin_address_model = model(ADMINADDRESS);
        $user_coin_order_model = model(USERCOINORDER);
        echo 'START : ' . strtoupper($coin['coin_name']) . "<br>";

        switch ($coin['coin_type']) {
            case 3:
                echo lang('over') . ' : ' . strtoupper($coin['coin_name']) . "<br><br>";
                break;
            case 1:
                $CoinClient = new CoinClient($coin['wallet_service_name'], $coin['wallet_service_password'], $coin['wallet_service_ip'], $coin['wallet_service_port'], 5, array(), 1);
                $json = $CoinClient->getnetworkinfo();

                if (!isset($json['version']) || !$json['version']) {
                    echo lang('ERROR') . ' : ' . strtoupper($coin['coin_name']) . lang('Communication connection failed!') . "<br>";
                    continue;
                }

                $listtransactions = $CoinClient->listtransactions('*', 100, 0);
                if (!is_array($listtransactions)) {
                    echo lang('No transaction data!') . '<br>';
                    continue;
                } else {
                    echo lang('NODES') . ' : ' . count($listtransactions) . "<br>";
                }

                krsort($listtransactions);

                foreach ($listtransactions as $trans) {
                    //匿名转账
                    if (!isset($trans['account']) || !isset($trans['address']) || !$trans['account'] || !$trans['address']) {
                        continue;
                    }

                    //验证是否是平台用户
                    if (!($user_coin = $user_coin_model->where([$coin['coin_name'] . '_site' => $trans['address']])->find())) {
                        continue;
                    }

                    //已确认过的记录
                    if ($user_coin_order_model->where(array('txid' => $trans['txid'], 'inflow_confirm_number' => ['egt', 1]))->find()) {
                        continue;
                    }

                    //接收记录
                    if ($trans['category'] == 'receive') {

                        //验证网络确认
                        if ($trans['confirmations'] < $coin['inflow_confirm_number']) {
                            $confirmations = intval($trans['confirmations'] - $coin['inflow_confirm_number']);
                        } else {
                            $confirmations = 1;
                            $row[] = $user_coin_model->where(array('user_id' => $user_coin['user_id']))->setInc($coin['coin_name'], $trans['amount']);
                        }

                        if ($res = $user_coin_order_model->where(array('txid' => $trans['txid']))->find()) {
                            if ($res['confirmations'] != $confirmations) {
                                $row[] = $user_coin_order_model->update(array('id' => $res['id'], 'update_time' => date("Y-m-d H:i:s"), 'confirmations' => intval($trans['confirmations'] - $coin['inflow_confirm_number'])));
                            }
                        } else {
                            $order = orderSn();
                            $create_time = date("Y-m-d H:i:s", time());
                            $row[] = $user_coin_order_model
                                ->insertGetId(array(
                                    'order_number' => $order,
                                    'user_id' => $user_coin['user_id'],
                                    'coin_id' => $coin['id'],
                                    'coin_name' => $coin['coin_name'],
                                    'to_address' => $trans['address'],
                                    'txid' => $trans['txid'],
                                    'amount' => $trans['amount'],
                                    'total' => $trans['amount'],
                                    'fee' => 0,
                                    'type' => 1,
                                    'create_time' => $create_time,
                                    'inflow_confirm_number' => $confirmations,
                                    'status' => 2
                                ));

                            $row[] = model(FINANCEDETAIL)->save([
                                'user_id' => $user_coin['user_id'],
                                'transaction_number' => 12,
                                'order_number' => $order,
                                'market' => $coin['coin_name'],
                                'operation' => 12,
                                'deal_type' => 1,
                                'transaction_currency' => $coin['coin_name'],
                                'deal_total' => $trans['amount'],
                                'encryption' => '',
                                'remark' => '',
                                'create_time' => $create_time,
                                'status' => 1,
                            ]);
                        }
                        continue;
                    }
                }
                break;
            case 2:
                //主账户地址验证
                if (!isset($coin['summary_account_address']) || !$coin['summary_account_address']) {
                    throw new Exception(lang('Main account not set!'));
                }

                $ethereum = new \app\common\library\Ethereum($coin['wallet_service_ip'], $coin['wallet_service_port']);//联接

                $version = $ethereum->web3_clientVersion();
                if (!$version || !$version->result) {
                    echo lang('ERROR') . ' : ' . strtoupper($coin['coin_name']) . lang('Communication connection failed!') . "<br>";
                    continue;
                }

                $blockNum = $ethereum->eth_blockNumber(TRUE);//查询最后的区块号

                switch ($coin['protocol']) {
                    case 'ETH':

                        //汇总手续费
                        $gas = 21000;
                        $result = $ethereum->eth_gasPrice();
                        $eth_fee = $result->result * ($gas + 1);

                        for ($bn = $blockNum; $bn >= $blockNum - 100; $bn--) {
                            $block = $ethereum->eth_getBlockByNumber('0x' . dechex($bn));
                            if (empty($block->result->transactions)) {
                                continue;
                            } else {
                                foreach ($block->result->transactions as $tx) {

                                    //是否存在转账金额/验证是否是以太坊交易
                                    if (!$tx->value || $tx->value == '0x0') {
                                        continue;
                                    }

                                    //验证是否是平台用户
                                    if (!$user_id = $user_coin_model->field('user_id')->where([$coin['coin_name'] . '_site' => $tx->to])->value('user_id')) {
                                        continue;
                                    }

                                    //已确认过的记录
                                    if ($log = $user_coin_order_model->where(array('txid' => $tx->hash, 'inflow_confirm_number' => ['egt', 1]))->find()) {
                                        continue;
                                    }

                                    //取得并验证交易金额
                                    $money = base_convert($tx->value, 16, 10) / bcpow(10, 18);
                                    if (!$money || $money <= 0) {
                                        continue;
                                    }

                                    //验证用户信息
                                    if (!$user = $user_model->where(['id' => $user_id])->find()) {
                                        continue;
                                    }

                                    //数据记录
                                    $row[] = $user_coin_model->where(array('user_id' => $user['id']))->setInc($coin['coin_name'], $money);
                                    $row[] = $user_coin_order_model
                                        ->insertGetId(array(
                                            'order_number' => orderSn(),
                                            'user_id' => $user_id,
                                            'coin_id' => $coin['id'],
                                            'coin_name' => $coin['coin_name'],
                                            'from_address' => $tx->from,
                                            'to_address' => $tx->to,
                                            'txid' => $tx->hash,
                                            'amount' => $money,
                                            'total' => $money,
                                            'fee' => 0,
                                            'type' => 1,
                                            'create_time' => date("Y-m-d H:i:s"),
                                            'inflow_confirm_number' => 1,
                                            'status' => 2
                                        ));

                                    //汇总
                                    if (check_array($row)) {
                                        Db::commit();

                                        //验证主账户
                                        if (!$coin['summary_account_address']) {
                                            throw new Exception(lang('Main account not set!'));
                                        }

                                        //转出账号解锁
                                        $unlock = $ethereum->personal_unlockAccount($tx->to, $coin['wallet_service_password']);
                                        if ($unlock && isset($unlock->result) && $unlock->result) {
                                            //主账户
                                            $address = $admin_address_model->where(['coin_name' => $coin['coin_name'], 'status' => 1])->value('address');
                                            if (!$address) {
                                                echo lang('Main account not set!');
                                                continue;
                                            }

                                            //转给子账户作为手续费的ETH
                                            $transaction = [
                                                'from' => $tx->to,
                                                'to' => $address,
                                                'gas' => $gas,
                                                'value' => $money - $eth_fee,
                                            ];
                                            $result = $ethereum->eth_sendTransaction($transaction);
                                        }
                                    } else {
                                        Db::rollback();
                                    }
                                    continue;
                                }
                            }
                        }
                        break;
                    case 'ERC20':
//                        //余额
//                        $ethM = $ethereum->eth_getBalance($coin['summary_account_address'], 'latest', TRUE) / 1000000000000000000;
//
//                        //验证汇总交易手续费
//                        if ($ethM < $eth_fee) {
//                            echo strtoupper($coin['coin_name']). ' ' .lang('Insufficient account balance!');
//                            //ETH钱包余额不足
//                            continue;
//                        }

                        //代币合约
                        $CoinToken = new CoinToken($coin['smart_contract_address']);

                        //TOKEN小数
                        $decimals = $CoinToken->call('decimals');

                        //汇总手续费
                        $gas = 60000;
                        $gas_price = $CoinToken->gasPrice();
                        $eth_fee = bcmul($gas_price, ($gas + 1));

                        for ($bn = $blockNum; $bn >= $blockNum - 500; $bn--) {
                            $block = $ethereum->eth_getBlockByNumber('0x' . dechex($bn));
                            if (empty($block->result->transactions)) {
                                continue;
                            } else {
                                foreach ($block->result->transactions as $tx) {

                                    //验证合约
                                    if (strcasecmp($tx->to, $coin['smart_contract_address']) !== 0) {
                                        continue;
                                    }

                                    //验证input内容
                                    if (strlen($tx->input) < 104) {
                                        continue;
                                    }

                                    //验证是否是平台用户
                                    $recipients = '0x' . substr($tx->input, -104, 40);
                                    if (!$recipients || !$user_id = $user_coin_model->field('user_id')->where([$coin['coin_name'] . '_site' => $recipients])->value('user_id')) {
                                        continue;
                                    }

                                    //验证金额
                                    $money = base_convert('0x' . substr($tx->input, -64, 64), 16, 10) / bcpow(10, $decimals);
                                    if (!$money || $money <= 0) {
                                        continue;
                                    }

                                    //已确认过的记录
                                    if ($log = $user_coin_order_model->where(array('txid' => $tx->hash, 'inflow_confirm_number' => ['egt', 1]))->find()) {
                                        continue;
                                    }

                                    //验证用户信息
                                    if (!$user = $user_model->where(['id' => $user_id])->find()) {
                                        continue;
                                    }

                                    //数据记录
                                    $row[] = $user_coin_model->where(array('user_id' => $user['id']))->setInc($coin['coin_name'], $money);
                                    $row[] = $user_coin_order_model
                                        ->insertGetId(array(
                                            'order_number' => orderSn(),
                                            'user_id' => $user_id,
                                            'coin_id' => $coin['id'],
                                            'coin_name' => $coin['coin_name'],
                                            'from_address' => $tx->from,
                                            'to_address' => $recipients,
                                            'txid' => $tx->hash,
                                            'amount' => $money,
                                            'total' => $money,
                                            'fee' => 0,
                                            'type' => 1,
                                            'create_time' => date("Y-m-d H:i:s"),
                                            'inflow_confirm_number' => 1,
                                            'status' => 2
                                        ));

                                    //交易记录完成
                                    if (check_array($row)) {
                                        Db::commit();

                                        //验证主账户
                                        if (!$coin['summary_account_address']) {
                                            throw new Exception(lang('Main account not set!'));
                                        }

                                        //转出账户解锁
//                                        $unlock = $ethereum->personal_unlockAccount($coin['summary_account_address'], $coin['wallet_service_password']);
                                        $unlock = $ethereum->personal_unlockAccount($coin['summary_account_address'], 'mo1996522');

                                        if ($unlock && $unlock->result) {

                                            //转给子账户作为手续费的ETH
                                            $transaction = [
                                                'from' => $coin['summary_account_address'],
                                                'to' => $recipients,
                                                'gas' => $gas,
                                                'value' => $money - $eth_fee,
                                                'gasPrice' => $gas_price,
                                            ];

                                            $result = $ethereum->eth_sendTransaction($transaction);

                                            //代币汇总
                                            if (isset($result->result) && substr($result->result, 0, 2) == '0x') {
                                                $token_result = $CoinToken->transfer($recipients, $coin['summary_account_address'], $money);
                                            }
                                        }
                                    } else {
                                        Db::rollback();
                                    }
                                    continue;
                                }
                            }
                        }
                        break;
                }
                break;
        }

        if (check_array($row)) {
            Db::commit();
        } else {
            Db::rollback();
        }

        echo lang('OVER') . ' : ' . strtoupper($coin['coin_name']) . "<br><br>";
    }

    /**
     * 余额预警
     * @param $coin
     */
    public function warn($coin = NULL)
    {
        try {
            $where = [];
            if ($coin != null) {
                $where['a.coin_name'] = $coin;
            }

            $admin_notice_model = model(ADMINNOTICE);
            $coin_info = model(ADMINADDRESS)
                ->alias('a')
                ->field('a.address,a.caution_number,b.*')
                ->join(PREFIX . COIN . ' b', 'a.coin_name = b.coin_name')
                ->where($where)
                ->where('a.coin_name', 'not in', implode(',', $this->coin))
                ->where(['a.status' => 1])
                ->select()
                ->toArray();

            foreach ($coin_info as $value) {

                if ($value['caution_number']) {
                    $this->coin[] = $value['coin_name'];

                    $info = [
                        'name' => $value['coin_name'],
                        'type' => $value['coin_type'],
                        'protocol' => $value['protocol'],
                        'contract' => $value['smart_contract_address'],
                    ];

                    //连接钱包并获取余额信息
                    $balance = BlockChain::instance()
                        ->setCoin($info)
                        ->setLink($value['wallet_service_ip'], $value['wallet_service_port'], $value['wallet_service_name'], $value['wallet_service_password'])
                        ->getAccountsBalance($value['address']);

                    //达到预设值记录预警信息
                    if ($value['caution_number'] < $balance) {

                        $notice = $admin_notice_model->insertGetId([
                            'content' => lang('{:coin_name} has reached the preset value of transfer, please deal with it timely', ['coin_name' => $value['coin_name']]),
                            'create_time' => date('Y-m-d H:i:s'),
                            'status' => 0
                        ]);

                        if ($notice) {
                            echo $value['coin_name'] . ' ' . $balance . "<br>";
                        }
                    }
                }

            }
        } catch (Exception $exception) {
            //错误抛出
            echo $exception->getMessage() . "<br>";

            //遇到错误继续
            $this->warn($coin);
        }
    }
}