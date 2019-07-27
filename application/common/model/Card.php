<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * Author: IT宇宙人
 * Date: 2015-09-09
 */
namespace app\common\model;

use think\Model;

class Card extends Model
{
    public function taskResource(){
        return $this->hasOne('task_resource','tr_id','tr_id');
    }
    public function getInfo($card_id){
        $card = $this->relation('task_resource')->where(['card_id'=>$card_id])->find();
        if(!$card){
            return ['status'=>'0','msg'=>'打卡任务不存在','data'=>''];
        }
        $task = model('task')->where(['task_id'=>$card['task_id']])->find();
        if($task['task_status'] != 1){
            return ['status'=>'0','msg'=>'非法操作','data'=>''];
        }
        if(!$card['card_surplus_days']){
            return ['status'=>'0','msg'=>'该打卡任务已完成','data'=>''];
        }
        $today = strtotime(date('Y-m-d'));
        if($card['card_last_time'] > $today){
            return ['status'=>'0','msg'=>'您今天已经打过卡了','data'=>''];
        }
        return ['status'=>'1','msg'=>'成功','data'=>$card];
    }
}
