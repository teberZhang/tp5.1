<?php

namespace app\api\logic;
/*
 * 计划任务执行类
 * */
class Queue
{
    public $market;
    public $marketdb;
    public $type = [
        60,
        300,
        900,
        1800,
        3600,
        86400,
        604800
    ];
    public $redis;
    public function __construct($market)
    {
        $this->marketdb = $market;
        $this->market = $this->marketdb->where(['status'=>1])->column('market');
    }

    /*
     * 获取24小时最新成交：最高价、最低价、成交总量
     * */
    public function StatisticalTwentyFour($redis,$db){
        $market = $this->market;
        $time = time()-86400;
        foreach($market as $v){
            $arr = [
                'market'=>$v,
                'hqzrsp'=>$db->where(['market'=>$v])->whereTime('create_time','>=',$time)->order('id asc')->value('price'),
                'minPrice'=>$db->where(['market'=>$v])->whereTime('create_time','>=',$time)->min('price'),
                'sumNumber'=>$db->where(['market'=>$v])->whereTime('create_time','>=',$time)->sum('deal_number'),
                'maxPrice'=>$db->where(['market'=>$v])->whereTime('create_time','>=',$time)->max('price'),
            ];
            $redis->hSet('market:new:twentyfour',$v,json_encode($arr));
            $this->marketdb->where(['market'=>$v])->update(['min_price'=>$arr['minPrice'],'max_price'=>$arr['maxPrice'],'finally_deal'=>$arr['hqzrsp'],'volume'=>$arr['sumNumber']]);
        }
        return;
    }
    /*
     * 交易市场趋势图统计
     * */
    public function TrendChart($db){
        $market = $this->market;
        if(empty($market)){
           return;
        }
//        6小时前时间戳
        $time = strtotime(date('Y-m-d H:i',time()))-21600;
        foreach($market as $v){
            $timeL = $time;
            $data = $db->table(PREFIX.TRADEK.$v)->where(['chart_type'=>60])->whereTime('create_time','>=',$time)->column('data');
            $tendency = [];
            for($i = 1;$i<37;$i++){
                $timeI = $i * 600 + $time;
                $int = 0;
                foreach ($data as $dk=>$dv){
                    $arr = explode(',',$dv);
                    $timeA = $arr[0]/1000;
                    if($timeA >= $timeL && $timeA <= $timeI){
                        $int = $int < $arr[5]?$arr[5]:$int;
                        unset($data[$dk]);
                    }
                }
                $timeL = $timeI;
                $tendency[]=[($timeI*1000),$int];
            }
            $this->marketdb->where(['market'=>$v])->update(['tendency'=>json_encode($tendency)]);
        }
        return;
    }
    /*
     * 写入K线数据总方法
     * */
    public function line_k_total($redis,$db){
        $this->redis = $redis;
        $time = time();
        $timeArr = TimeK($time);
        foreach($this->type as $v){
            $this->line_k_time($v,$time,$timeArr,$db);
        }
    }
    /*
     * 写入K线数据时间限制
     * */
    private function line_k_time($type,$time,$timeArr,$db){
        $timeS = $time-$timeArr[$type];
        if($timeS > 0 && $timeS < 6){
            $this->line_k($type,$timeArr,$db);
        }

    }
    /*
     * 写入K线数据
     * */
    private function line_k($type,$timeArr,$db){
        $redis = $this->redis;
        foreach($this->market as $v) {
            $tradekDb = $db->table(PREFIX.TRADEK . $v);
//            当前最新数据
            $typeTop = $redis->hGet($v . ':trader:k:top',$type);
//            上一个时间段数据
            $typeTwo = $redis->hGet($v . ':trader:k:two',$type);
            if($typeTwo){
                $strTwo = trim($typeTwo,'"');
                $dataTwo = explode(',',$strTwo);
//                    上一时间段大于缓存上一时间段
                if(($timeArr[$type]-$type) > ($dataTwo[0]/1000)){
                    $strTop = trim($typeTop,'"');
                    $dataTop = explode(',',$strTop);
//                    上一时间段大于缓存最新时间段画平线
                    if(($timeArr[$type]-$type) > ($dataTop[0]/1000)){
                        $close = $dataTop[4];
                        $tradekDb->insert([
                            'data'=>(($timeArr[$type]-$type)*1000).','.$close.','.$close.','.$close.','.$close.',0',
                            'chart_type'=>$type,
                            'create_time'=>date('Y-m-d H:i:s',$timeArr[$type]-$type)
                        ]);
                    }else{
//                        上一时间段等于缓存最新时间段画最新数据
                        $tradekDb->insert([
                            'data'=>$strTop,
                            'chart_type'=>$type,
                            'create_time'=>date('Y-m-d H:i:s',$dataTop[0]/1000)
                        ]);
                    }
                }else{
//                    时间戳相等画上一时间段数据
                    $tradekDb->insert([
                        'data'=>$strTwo,
                        'chart_type'=>$type,
                        'create_time'=>date('Y-m-d H:i:s',$dataTwo[0]/1000)
                    ]);
                }
            }elseif ($typeTop){
                $strTop = trim($typeTop,'"');
                $dataTop = explode(',',$strTop);
//                    上一时间段大于缓存最新时间段画平线
                if(($timeArr[$type]-$type) >= ($dataTop[0]/1000)){
                    $close = $dataTop[4];
                    $tradekDb->insert([
                        'data'=>(($timeArr[$type]-$type)*1000).','.$close.','.$close.','.$close.','.$close.',0',
                        'chart_type'=>$type,
                        'create_time'=>date('Y-m-d H:i:s',$timeArr[$type]-$type)
                    ]);
                }else{
//                  上一时间段等于缓存最新时间段画最新数据
                    $tradekDb->insert([
                        'data'=>$strTop,
                        'chart_type'=>$type,
                        'create_time'=>date('Y-m-d H:i:s',$dataTop[0]/1000)
                    ]);
                }
            }
// 最新成交价
            $newPrice = $redis->hGet('market:new:price',$v.':new:price');
            if($newPrice){
                $newPrice = trim($newPrice,'"');
                //        获取24小时收盘价
                $json_change = $redis->hGet('market:new:twentyfour',$v);
                $risefall = 0;
                if($json_change){
                    $change = json_decode($json_change,true);
                    $pioneer = $change['hqzrsp'];
                    if($pioneer){
                        $risefall = round((($newPrice - $pioneer) / $pioneer) * 100, 2);
                    }
                }
                $this->marketdb->where(['market'=>$v])->update(['recent_quotation'=>$newPrice,'change'=>$risefall]);
            }
        }
    }

}
