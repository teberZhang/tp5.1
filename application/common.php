<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
if (!function_exists('camelize')) {
    /**
     * 转为驼峰格式
     * @param $set
     * @param int $tier
     * @return array|string
     */
    function camelize($set, $tier = 2, $separator = '_')
    {
        if (!$set) {
            return $set;
        }

        if (is_array($set)) {//数组key转驼峰
            $array = [];

            foreach ($set as $keys => $values) {
                $separate = explode($separator, trim($keys, $separator));
                if (count($separate) > 1) {
                    foreach ($separate as $key => &$value) {
                        if ($key >= 1) {
                            $value = ucwords($value);
                        }
                    }
                }

                $str = implode('', $separate);
                if ($values && is_array($values) && $tier > 1) {
                    $array[$str] = camelize($values, $tier - 1);
                } else {
                    $array[$str] = $values;
                }
            }

            return $array;
        } elseif (is_string($set)) {//字符串转驼峰

            $separate = explode($separator, trim($set, $separator));
            if (count($separate) > 1) {
                foreach ($separate as $key => &$value) {
                    if ($key >= 1) {
                        $value = ucwords($value);
                    }
                }
            }
            $str = implode('', $separate);

            return $str;
        } else {
            return $set;
        }
    }
}
