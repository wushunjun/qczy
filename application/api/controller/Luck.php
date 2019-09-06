<?php
namespace app\api\controller;

use think\Page;
use think\Db;

class Luck extends Apibase {
    /**
     * 运势类型
     */
    public function luck_type(){
        $list = Db::name('luck_type')->select();
        $this->apiReturn('1001','成功',$list);
    }
    /**
     * 今日是否已抽运势
     * 流程：今日运势每日可抽一次，进入今日运势前先调此接口确认用户是否已抽,
     * 如果返回的data有数据则直接显示到运势详情，否则用户选择类型系统随机给出运势结果
     * @param int user_id 用户id
     * @param int lt_id 运势类型id
     */
    public function has_luck()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'lt_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $start_time = strtotime(date('Y-m-d'));
        $end_time = $start_time + 3600 * 24 -1;
        $res = Db::name('luck_log')->alias('a')
            ->join('luck b','a.luck_id = b.luck_id')
            ->where(['a.user_id'=>$param['user_id'],'a.lt_id'=>$param['lt_id'],'a.ll_add_time'=>['between',"$start_time,$end_time"]])
            ->find();
        if($res){
            $comment_list = Db::name('luck_comment')->where(['luck_id'=>$res['luck_id']])->select();
            $res['comment_list'] = $comment_list;
        }
        $res = $res ? $res : (object)[];
        $this->apiReturn('1001','成功',$res);
    }
    /**
     * 随机获取运势结果
     * @param int user_id 用户id
     * @param int lt_id 类型id
     */
    public function luck()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'lt_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $start_time = strtotime(date('Y-m-d'));
        $end_time = $start_time + 3600 * 24 -1;
        $result = Db::name('luck_log')->alias('a')
            ->join('luck b','a.luck_id = b.luck_id')
            ->where(['a.user_id'=>$param['user_id'],'a.lt_id'=>$param['lt_id'],'a.ll_add_time'=>['between',"$start_time,$end_time"]])
            ->find();
        if($result){//如果抽过
            //$this->apiReturn('1002','非法操作','');
            $data = Db::name('luck')->where(['luck_id'=>$result['luck_id']])->find();
        }else{
            $list = Db::name('luck')->where(['lt_id'=>$param['lt_id']])->column('luck_id,luck_img,luck_name,lt_id');
            if(!$list){
                $this->apiReturn('1002','数据错误，请联系客服','');
            }
            $id_arr = array_keys($list);
            $i = mt_rand(0, count($id_arr)-1);
            $data = $list[$id_arr[$i]];
        }
        $comment_list = Db::name('luck_comment')->where(['luck_id'=>$id_arr[$i]])->select();
        $data['comment_list'] = $comment_list;
        $log_data = [
            'user_id' => $param['user_id'],
            'luck_id' => $data['luck_id'],
            'll_add_time' => time(),
            'lt_id' => $data['lt_id'],
        ];
        $res = Db::name('luck_log')->insertGetId($log_data);
        if(!$res){
            $this->apiReturn('1002','失败','');
        }
        $this->apiReturn('1001','成功',$data);
    }
    /**
     * 运势详情
     * @param ll_id 运势记录id
     */
    public function luck_info(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'll_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $info = Db::name('luck_log')->alias('a')->join('luck b','a.luck_id = b.luck_id')->where(['ll_id'=>$param['ll_id']])->find();
        if(!$info){
            $this->apiReturn('1002','失败','');
        }
        $comment_list = Db::name('luck_comment')->where(['luck_id'=>$info['luck_id']])->select();
        $info['comment_list'] = $comment_list;
        $this->apiReturn('1001','成功',$info);
    }
}