<?php
namespace app\api\controller;

use think\Db;

/**
 * 礼物相关类
 * Class PraiseController
 * @package app\api\controller
 */
class Gift extends Apibase
{
    /**
     * 礼物列表
     */
    public function gift_list(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $list = Db::name('gift')->where(['gift_type'=>0,'gift_status'=>1,'is_del'=>0])->select();
        $this->apiReturn('1001','成功',$list);
    }
}
