<?php
namespace app\index\controller;

use think\Exception;
use think\Queue;
use think\facade\Log;

class Jobtask
{
    public function actionWithMultiTask(){

        $taskType = $_GET['taskType'];
        switch ($taskType) {
            case 'taskA':
                $jobHandlerClassName  = 'app\index\job\MultiTask@taskA';
                $jobDataArr = ['a'	=> '1'];
                $jobQueueName = "multiTaskJobQueue";
                break;
            case 'taskB':
                $jobHandlerClassName  = 'app\index\job\MultiTask@taskB';
                $jobDataArr = ['b'	=> '2'];
                $jobQueueName = "multiTaskJobQueue";
                break;
            default:
                break;
        }

        $isPushed = Queue::push($jobHandlerClassName, $jobDataArr, $jobQueueName);
        if ($isPushed !== false) {
            echo("$taskType 添加到了 ".$jobQueueName ."<br>");
        }else{
            throw new Exception("push a new $taskType of MultiTask Job Failed!");
        }
    }

}
