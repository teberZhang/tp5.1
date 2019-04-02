<?php
/**
 * Created by PhpStorm.
 * User: 10562
 * Date: 2018/10/31
 * Time: 14:50
 */

namespace app\api\logic;


class Auth extends Base implements \app\api\service\Auth
{
    public function callback(){

    }

    public function nonoitfy($name,$idCard,$token){

    }

    public function url($name,$idCardNumber){
        return $this->warn('',200,'https://openapi.faceid.com/lite/v1/do/1541316270,9c6e7b64-5e0b-4317-b0e7-85bcbcf48d9c');
    }
}