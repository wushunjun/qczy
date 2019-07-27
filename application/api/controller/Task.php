<?php
namespace app\api\controller;

use think\image\Exception;
use think\Page;
use think\Db;

class Task extends Apibase {
    /**
     * 打卡任务列表
     * @param int user_id 用户id
     * @param int page 页码
     * @param int pageSize 单页显示数量
     * 说明：task_status  0待打卡，1打卡中，2待抽奖，3已完成
     */
    public function task_list(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'page' => 'integer',
            'pageSize' => 'integer',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $list = model('task')->where(['user_id'=>$param['user_id']])
            ->order('task_id desc')->page(page($param))->select();
        if($list){
            collection($list)->append(['task_status'])->toArray();
        }
        $this->apiReturn('1001','成功',$list);
    }
    /**
     * 获取进行中最新的任务
     * @param int user_id 用户id
     */
    public function newest_task(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $time = time();
        $info = model('task')->where(['user_id'=>$param['user_id'],'task_start_time'=>['lt',$time],'task_end_time'=>['gt',$time]])
            ->order('task_id desc')->find();
        $this->apiReturn('1001','成功',$info);
    }
    /**
     * 任务详情
     * @param int task_id 任务id
     * 说明：card_selected控制打卡列表前的选中按钮，card_button控制打卡按钮
     */
    public function task_info(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'task_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $info = model('task')->where(['task_id'=>$param['task_id']])->find();
        if(!$info){
            $this->apiReturn('1002','任务不存在','');
        }
        $info = $info->append(['card','task_status','days'])->toArray();
        $time = strtotime(date('Y-m-d'));
        foreach($info['card'] as $k=>$v){
            if($info['task_status'] == 1){
                if($v['card_last_time'] > $time){
                    $info['card'][$k]['card_button'] = 0;
                    $info['card'][$k]['card_selected'] = 1;
                }else{
                    $info['card'][$k]['card_button'] = 1;
                    $info['card'][$k]['card_selected'] = 0;
                }
            }else{
                $info['card'][$k]['card_button'] = 0;
                if($v['card_last_time'] > $time){
                    $info['card'][$k]['card_selected'] = 1;
                }else{
                    $info['card'][$k]['card_selected'] = 0;
                }
            }
            $info['card'][$k]['card_hit_days'] = $v['card_days'] - $v['card_surplus_days'];
        }
        $this->apiReturn('1001','成功',$info);
    }
    /**
     * 打卡页面
     * @param int card_id 打卡任务id
     * @return array
     */
    public function card()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'card_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $result = model('card')->getInfo($param['card_id']);
        if(!$result['status']){
            $this->apiReturn('1002',$result['msg'],'');
        }
        $this->apiReturn('1001','成功',$result['data']);

    }
    /**
     * 打卡
     * @param int card_id 打卡任务id
     * @return array
     */
    public function hit_card()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'card_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $result = model('card')->getInfo($param['card_id']);
        if(!$result['status']){
            $this->apiReturn('1002',$result['msg'],'');
        }
        $res = model('card')->where(['card_id'=>$param['card_id']])->update(['card_surplus_days'=>['exp','card_surplus_days-1'],'card_last_time'=>time()]);
        if($res){
            $this->apiReturn('1001','成功','');
        }else{
            $this->apiReturn('1002','打卡失败','');
        }
    }
}