<?php
namespace app\index\controller;

use think\App;
use think\Request;

/*版本升级接口逻辑*/
class Appupinit extends AppUpgrade
{

    public function __construct(App $app = null,Request $request)
    {
        parent::__construct($app);
        $this->request = $request;
    }

    public function index()
    {
        //本方法在基类中，确实数据的正确性
        $checkResult = $this->check();
        //return json($this->request->param());
        if(200 <> $checkResult['code']) {
            return json($checkResult);
        }

        /*
			获取新版本信息，和APP发送过来的版本信息进行对比
			如果是最新版本，不升级
			如果是老版本，升级
		*/
        $version_grade=$this->getVersionUpgrade($this->param['app_id']);
        //检测是否拿到版本信息
        if($version_grade){
            /*
                判断type类型看能否升级，并且判断客户端是不是最新版本
                如果可以更新，添加键值is_upload = 1
                不能更新，is_upload	= 0
                APP工程师根据返回的is_upload的值进行相应的操作
            */
            if ($version_grade['type'] && $this->param['version_id'] < $version_grade['version_id']) {
                $version_grade['is_upload']=$version_grade['type'];
            }else{
                $version_grade['is_upload']=0;
            }
            return json(['code'=>200,'msg'=>'获取版本成功','data'=>$version_grade]);
        }else{
            return json(['code'=>400,'msg'=>'获取新版本失败']);
        }
    }
}
