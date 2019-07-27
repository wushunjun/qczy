<?php

namespace app\common\logic;

use think\Model;
use think\Db;

/**
 * 分销逻辑
 * Class CatsLogic
 * @package Home\Logic
 */
class DistributLogic extends Model
{

    /**
     * 生成分成记录
     * @param $order 订单内容
     */
    public function rebateLog($order)
    {
        /*$user = Db::name('users')->where(['user_id'=>$order['user_id']])->find();
        if(!$user['first_leader']){
            return true;
        }
        if($user['second_leader'])
            $second =  Db::name('users')->where(['user_id'=>$user['second_leader']])->find();
        $data = [
            'user_id' => $user['first_leader'],
            'user_id' => $order['user_id'],
        ];*/
    }


}