<?php
namespace app\api\controller;

use think\Page;
use think\Db;

class Shake extends Apibase {
    /**
     * 今天是否已经摇一摇
     * @param int user_id 用户id
     */
    public function has_shake()
    {
        $start_time = strtotime(date('Y-m-d'));
        $end_time = $start_time + 3600 * 24 -1;
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $res = model('shake')->where(['user_id'=>$param['user_id'],'shake_add_time'=>['between',"$start_time,$end_time"]])->find();
        $data = $res ? 1 : 0;
        $this->apiReturn('1001','成功',$data);
    }
    /**
     * 摇一摇
     * @param int user_id 用户id
     */
    public function shake()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $res = model('shake')->addShake($param['user_id']);
        $this->ajaxReturn($res);
    }
    /**
     * 摇一摇记录
     * @param int user_id 用户id
     * @param int page 页码
     * @param int pageSize 单页显示数量
     */
    public function shake_list()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'page' => 'integer',
            'pageSize' => 'integer',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $res = model('shake')->getList($param);
        $this->apiReturn('1001','成功',$res);
    }
    /**
     * 摇一摇详情
     * @param int shake_id 用户id
     */
    public function shake_info()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $res = model('shake')->find($param['shake_id']);
        if($res){
            $this->apiReturn('1001','成功',$res);
        }else{
            $this->apiReturn('1002','记录不存在','');
        }
    }
}