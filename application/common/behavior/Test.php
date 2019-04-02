<?php
namespace app\common\behavior;


class Test
{
    public function run($params = [])
    {
        error_log(date("H:i:s",time()).json_encode($params)."\r\n",3,'appfile.txt');
    }
}
