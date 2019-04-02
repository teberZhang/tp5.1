<?php
namespace app\index\controller;

use think\App;
use think\Controller;
use think\Db;

/*APP版本更新*/
class AppUpgrade extends Controller
{
    //post提交过来的数据
    protected $param = [];
    //该客户端设备的信息详情
    protected $app;
    //数据库连接资源
    protected $connect;

    public function __construct(App $app = null)
    {
        parent::__construct($app);
    }

    //确认设备的版本信息，权限等
    protected function check()
    {
        //post提交过来的数据整合，一行太长了，分开来写\(^o^)/~
        $app_id	=	$this->request->post('app_id','');
        $this	->	param['app_id']	=	$app_id;
        $version_id	=	$this->request->post('version_id','');
        $this	->	param['version_id']	=	$version_id;
        $did	=	$this->request->post('did','');
        $this	->	param['did']	=	$did;
        $version_mini	=	$this->request->post('version_mini','');
        $this	->	param['version_mini']	=	$version_mini;
        $encrypt_did	=	$this->request->post('encrypt_did','');
        $this	->	param['encrypt_did']	=	$encrypt_did;

        //判断app_id和version_id数据类型是否正确
        if(!is_numeric($app_id) || !is_numeric($version_id)){
            return ['code'=>400,'msg'=>'数据不合法'];
        }

        //判断是否需要加密处理
        $this->app=$this->getApp();
        if(!$this->app){
            return ['code'=>400,'msg'=>'该app不存在'];
        }

        /*
			判断是否有权限，判断权限的方式，1,是否需要加密处理
			2,发送过来的encrypt_did是否和服务端生成的值一致
		*/
        if($this->app['is_encryption'] && $this->param['encrypt_did'] != md5($did . $this->app['key'])){
            return ['code'=>405,'msg'=>'你没有权限'];
        }
        return ['code'=>200,'msg'=>'校验成功'];
    }

    //获取该设备信息
    protected function getApp(){
        $app_id = $this->param['app_id'];
        return Db::name("app")
            ->where(['app_id'=>$app_id])
            ->find();
    }
    //获取新版本信息
    protected function getVersionUpgrade($app_id)
    {
        return Db::name("version_upgrade")
            ->where(['app_id'=>$app_id])
            ->order("create_time desc")
            ->find();
    }
}
