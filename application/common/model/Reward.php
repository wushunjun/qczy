<?php

namespace app\common\model;

use think\Db;
use think\image\Exception;
use think\Model;

class Reward extends Model
{
    /**
     * 添加打赏
     * @param $data ['user_id'] 打赏者id
     * @param $data ['reward_type'] 打赏类型，0打赏动态，1打赏主页，2平台发放（抽奖获取等）
     * @param $data ['reward_obj_id'] 打赏对象id，用户id或者动态id
     * @param $data ['gift_id'] 礼物id
     * @param $data ['reward_sn'] 订单号
     */
    public function addReward($data)
    {
        try {
            $code = '';
            $a = true;
            //保证唯一性
            while ($a) {
                $code = random_string();
                $res = $this->where('reward_code', $code)->find();
                if (!$res) {
                    $a = false;
                }
            }
            $gift = Db::name('gift')->find($data['gift_id']);
            if ($data['reward_type'] == 0) {
                $data['to_user_id'] = Db::name('dynamic')->where(['dynamic_id' => $data['reward_obj_id']])->getField('user_id');
                Db::name('dynamic')->where(['dynamic_id' => $data['reward_obj_id']])->setInc('dynamic_reward');
            } else {
                $data['to_user_id'] = $data['reward_obj_id'];
            }
            $data['reward_code'] = $code;
            $data['gift_name'] = $gift['gift_name'];
            $data['gift_img'] = $gift['gift_img'];
            $data['reward_add_time'] = time();
            if (!$data['reward_sn']) {
                $data['reward_sn'] = 'rwd' . build_order_sn();
            }
            $reward_id = $this->insertGetId($data);
            if ($data['user_id']) {//如果有打赏人，则添加消费记录
                $to_user = Db::name('users')->find($data['to_user_id']);
                $pay_log_data = [
                    'user_id' => $data['user_id'],
                    'log_money' => -$data['reward_price'],
                    'log_add_time' => time(),
                    'log_info' => '赠送' . $data['reward_num'] . '个【' . $gift['gift_name'] . '】给' . $to_user['nickname'],
                ];
                model('pay_log')->insertGetId($pay_log_data);
                if($data['reward_price'] >= 0.01){//大于某金额则添加好友
                    $res = Db::name('follow')->where(['user_id'=>$data['user_id'],'to_user_id'=>$data['to_user_id']])->find();
                    if(!$res){
                        $friend_data = [
                            ['user_id'=>$data['user_id'],'to_user_id'=>$data['to_user_id'],'follow_add_time'=>time()],
                            ['user_id'=>$data['to_user_id'],'to_user_id'=>$data['user_id'],'follow_add_time'=>time()],
                        ];
                        Db::name('follow')->insertAll($friend_data);
                    }
                }
            }
            return $reward_id;
        } catch (Exception $e) {
            return false;
        }
    }
}
