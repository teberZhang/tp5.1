<?php
/**
 * Created by PhpStorm.
 * User: 10562
 * Date: 2018/10/31
 * Time: 14:48
 */

namespace app\api\service;


interface Auth
{
    public function callback();
    public function nonoitfy($name,$idCard,$token);
    public function url($name,$idCardNumber);
}