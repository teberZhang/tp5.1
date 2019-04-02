<?php
/**
 * Created by PhpStorm.
 * User: 10562
 * Date: 2018/11/3
 * Time: 10:57
 */

namespace app\api\logic;


use app\common\library\Redis;
use think\App;
use think\Controller;

class Base extends Controller
{
    public $debug = true;
    public $uuid = 'SID';
    public $user_info = [];
    public $redis_init = [];
    public $warn = ['msg' => '', 'code' => NO_RETURN_DATA, 'data' => NULL];
    public $CookieId = '';

    public function __construct()
    {
        parent::__construct();
        $controller = $this->request->controller();
        if ($controller != 'Login') {
            $this->userInfo();
            if(isset($this->login)){
                $login = $this->login;
                if(!empty($login)){
                    if($login[0] != '*'){
                        $action = request()->action();
                        foreach($login as $lv){
                            if(strtolower($lv) == $action){
                                $this->cookieUuid();
                            }
                        }
                    }
                }
            }else{
                $this->cookieUuid();
            }
        }
    }
    private function userInfo(){
        $this->CookieId = $uuid = Cookie($this->uuid);

        if ($uuid) {
            //获取用户信息
            $this->redis_init = new Redis();
            $this->user_info = json_decode($this->redis_init->get('shiro:session:' . $uuid), true);

            //验证多点登录(暂不允许)
            $token = Db(USER)->where(['id'=>$this->user_info['id']])->value('token');
            if($this->user_info && $uuid && $token != 'shiro:session:' . $uuid){
                Cookie($this->uuid,NULL);
                $this->redis_init->expire('shiro:session:' . $uuid,0);
                exit(json_encode($this->warn('Your account has been logged in in another place, please confirm the security.', NO_PERMISSION)));
            }
        }
    }
    private function cookieUuid(){
        if (!$this->user_info) {
            exit(json_encode($this->warn('Please login first!', LOGOUT)));
        }
        $this->redis_init->expire('shiro:session:' . $this->CookieId, 1800);
    }
    public function warn($msg = '', $code = NO_RETURN_DATA, $data = NULL)
    {
        $this->warn = [
            'code' => $code,
            'data' => $data,
            'msg' => $msg,
        ];
        return $this->warn;
    }
}
