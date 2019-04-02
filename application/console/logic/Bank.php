<?php
/**
 * Created by PhpStorm.
 * User: 10562
 * Date: 2018/11/2
 * Time: 9:26
 */

namespace app\api\logic;


use app\common\library\Redis;
use think\App;
use think\Exception;

class Bank extends Base implements \app\api\service\Bank
{
    /**
     * 查询所有开户行
     * @return mixed
     */
    public function banks(){
        try{
        $banks = Db(USERBANK)
            ->where(['status'=>1])
            ->select();
            if ($banks !== false) {
                return $this->warn(lang('success'),OPERATION_SUCCESS,camelize($banks));
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
     * post 银行卡新增 , get查询银行卡列表
     * @return mixed
     */
    public function bankcards(){

        try{
            if($this->request->isPost()){

                $phone = $this->request->param('phone','');
                $tpw = $this->request->param('tpw');
                $bank = $this->request->param('bank');
                $branch = $this->request->param('branch');
                $bankCard = $this->request->param('bankCard');
                $remark = $this->request->param('remark');

                //验证参数
                if (!check($phone, 'mobile')) {
                    return $this->warn(lang('Incorrect format of mobile phone number!'));
                }

                //验证密码
                $user_real_information = model(USER)
                    ->getUserInfo($this->user_info['id'],3,[],'transaction_password,salt');

                if(!$user_real_information['transaction_password']){
                    return $this->warn(lang('Please set the transaction password first!'));
                }

                if ($user_real_information['transaction_password'] != md5($tpw . $user_real_information['salt'])) {
                    return $this->warn(lang('Incorrect password!'));
                }

                if(!check($bankCard,'idcard')){
                    return $this->warn(lang('Incorrect bank card format!'));
                }

                //验证用户真实信息
                if(!$user_real_information['id_card_auth'] || !$user_real_information['id_card'] || !$user_real_information['true_name']){
                    return $this->warn(lang('User not real name or real name information incomplete!'));
                }

                //验证身份信息是否被使用
                if (Db(USERBANKCARD)->where(['user_id'=>$this->user_info['id'],'bank_card' => $bankCard])->find()) {
                    return $this->warn(lang('This bank card has been bound!'));
                } else {

                    if (Db(USERBANKCARD)->where(['user_id' => $user_real_information['id'], 'status' => 1])->count() > 10) {
                        return $this->warn(lang('Up to 10 bank CARDS can be added!'));
                    }

                    //银行34要素
                    $aliyuncardb = config('aliyuncardb');

                    $headers = $aliyuncardb['headers'];
                    array_push($headers, "Authorization:APPCODE " . $aliyuncardb['appcode']);

                    $params = array(
                        "accountNo" => $bankCard,
                        "idCardCode" => $user_real_information['id_card'],
                        "name" => $user_real_information['true_name']
                    );

                    if (!$phone) {
                        $url = $aliyuncardb['host'] . $aliyuncardb['path4'];
                        $params['bankPreMobile'] = $phone;
                    } else {
                        $url = $aliyuncardb['host'] . $aliyuncardb['path3'];
                    }

                    //发送请求
                    $res_str = $this->http_curl($url, $params, $aliyuncardb['method'], $headers, false);
                    $res_json = json_decode($res_str, true);

                    if ($res_json['error_code'] === 0 && $res_json['result']['messagetype'] === 0) {
                        $save_data['user_id'] = $user_real_information['id'];
                        $save_data['holder'] = $user_real_information['true_name'];
                        $save_data['phone'] = $phone;
                        $save_data['bank'] = $bank;
                        $save_data['branch'] = $branch;
                        $save_data['bank_card'] = $bankCard;
                        $save_data['status'] = 1;
                        $save_data['remark'] = $remark;
                        $save_data['create_time'] = date('Y-m-d H:i:s');

                        if ($res = Db(USERBANKCARD)->insert($save_data)) {
                            Db(USER)->where(['id'=>$this->user_info['id']])->update(['bank_card_flag'=>1]);
                            return $this->warn(lang('success'), OPERATION_SUCCESS);
                        } else {
                            return $this->warn(lang('failed'));
                        }

                    } else {
                        if (!isset($res_json['result']) || !$res_json['result']) {
                            return $this->warn(lang('Bank card authentication failed!'));
                        } else {
                            return $this->warn($res_json['result']['message'], NO_RETURN_DATA);
                        }
                    }
                }

            }else{
                $user_banks = Db(USERBANKCARD)
                    ->field('area,bank,bank_card as bankCard,branch,city,holder,is_deleted asisDeleted,phone,province,remark,status,user_id as userId')
                    ->where(['user_id'=>$this->user_info['id'],'status'=>1])
                    ->select();

                if ($user_banks !== false) {
                    return $this->warn(lang('success'), OPERATION_SUCCESS,$user_banks);
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
     * 发送HTTP请求方法
     * @param  string $url 请求URL
     * @param  array $params 请求参数
     * @param  string $method 请求方法GET/POST
     * @return array  $data   响应数据
     */
    function http_curl($url, $params, $method = 'GET', $header = array(), $multi = false)
    {
        if (empty($header)) {
            $header = array("Content-type: application/x-www-form-urlencoded; charset=utf-8");
        }
        $opts = array(
            CURLOPT_TIMEOUT => 30,//超时时间
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,//不验证证书
            CURLOPT_SSL_VERIFYHOST => false,//不验证HOST
            CURLOPT_HTTPHEADER => $header//默认array("Content-type: text/html; charset=utf-8")
            //CURLOPT_USERAGENT      => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1'
        );
        /* 根据请求类型设置特定参数 */
        switch (strtoupper($method)) {
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                //判断是否传输文件
                $params = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new \Exception('不支持的请求方式！');
        }
        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) throw new \Exception('请求发生错误：' . $error);
        return $data;
    }
}