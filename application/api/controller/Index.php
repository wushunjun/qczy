<?php
namespace app\api\controller;

use think\Page;
use think\Db;

class Index extends Apibase {
    /**
     * 轮播列表
     */
    public function banner_list(){
        $list = Db::name('ad')->where(['pid'=>537])->limit(5)->select();
        $this->apiReturn('1001','成功',$list);
    }
    /**
     * 好货推荐
     */
    public function recommend_goods(){
        $list = model('goods')->where(['is_recommend'=>1])->limit(5)->select();
        $this->apiReturn('1001','成功',$list);
    }
    /**
     * 项目推荐
     */
    public function recommend_project(){
        $list = Db::name('project')->where(['is_recommend'=>1,'is_del'=>0])->limit(5)->select();
        $this->apiReturn('1001','成功',$list);
    }
    /**
     * 推荐店铺
     */
    public function recommend_shop(){
        $list = model('shop')->relation('shopImages')->where(['is_recommend'=>1,'deleted'=>0])->limit(3)->select();
        $this->apiReturn('1001','成功',$list);
    }
    /**
     * 上传图片接口
     * @param file file 以file为参数名称
     */
    function upload_image(){
        $base64_image_content = $this->request->param('file');
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
            $type = $result[2];
            $name = "./public/upload/user_upload/";
            $name .= date('Y-m-d');
            if(!is_dir($name))
                mkdir($name, 0777);//如果不存在tmp目录，则建立
            $name .= '/';
            $name_md5 = md5(time());
            for($i=0;$i<10;$i++){
                $name .= $name_md5[rand(0,30)];
            }
            $new_file = $name.'.'.$type;
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
                $this->apiReturn('1001', '成功', substr($new_file,1));
            }else{
                $this->apiReturn('1002', '失败', '');
            }

        }else{
            $this->apiReturn('1002', '失败', '');
        }
    }
    /**
     * 获取jssdk鉴权信息
     */
    function get_jssdk_msg(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'url' => 'require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        vendor("Jssdk.jssdk");
        $jssdk = new \JSSDK(config('weixin_conf.appid'), config('weixin_conf.appsecret'),$param['url']);
        $signPackage = $jssdk->GetSignPackage();
        $this->apiReturn('1001', '成功', $signPackage);
    }

    /**
     * 获取省市区
     */
    public function get_region(){
        $list = Db::name('region')->where(['level'=>['lt',4]])->cache(true)->field('id as v,name as n,parent_id')->select();
        $res = cateMerge($list,'v','parent_id',0);
        /*$param = request()->param();
        $validate = $this->validate($param, [
            'parent_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $list = Db::name('region')->where(['parent_id'=>$param['parent_id']])->select();*/
        $this->apiReturn('1001', '成功', $res);
    }
}