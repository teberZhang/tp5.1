<?php
/**
 * 文件路径： \application\index\job\Hello.php
 * 这是一个消费者类，用于处理 helloJobQueue 队列中的任务
 */
namespace app\index\job;

use think\Queue\job;
use think\facade\Log;

class MultiTask
{

    public function taskA(Job $job, $data)
    {
        $isJobDone = $this->_doTaskA($data);

        if ($isJobDone) {
            $job->delete();
            Log::info("任务A处理成功" . "\n");
            print("任务A处理成功" . "\n");
        } else {
            if ($job->attempts() > 3) {
                $job->delete();
            }
        }
    }

    public function taskB(Job $job, $data)
    {

        $isJobDone = $this->_doTaskB($data);

        if ($isJobDone) {
            $job->delete();
            Log::info("任务B处理成功" . "\n");
            print("任务B处理成功" . "\n");
        } else {
            if ($job->attempts() > 2) {
                $job->release();
            }
        }
    }

    private function _doTaskA($data)
    {
        return true;
    }

    private function _doTaskB($data)
    {
        return true;
    }
}
