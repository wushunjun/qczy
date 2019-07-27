<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\api\controller;

use think\Db;
use app\api\logic\PaymentLogic;

/**
 * 打赏相关类
 * Class PraiseController
 * @package app\api\controller
 */
class Reward extends Apibase
{
    /**
     * 打赏
     * @param int user_id 打赏人id
     * @param int gift_id 礼物id
     * @param int reward_num 礼物数量
     * @param int reward_type 类型，0打赏动态，1打赏主页
     * @param int reward_obj_id 打赏对象id，如是打赏动态则传动态id，打赏主页则传用户id
     */
    public function reward(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'reward_obj_id' => 'integer|require',
            'reward_type' => 'in:0,1|require',
            'gift_id' => 'integer|require',
            'reward_num' => 'integer|require|gt:0',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $user = model('users')->where(['user_id'=>$param['user_id']])->find();
        if(!$user){
            $this->apiReturn('1002','非法操作','');
        }
        $gift = Db::name('gift')->find($param['gift_id']);
        if(!$gift){
            $this->apiReturn('1002','该礼物不存在','');
        }
        $param['order_amount'] = $gift['gift_price'] * $param['reward_num'];
        $param['openid'] = $this->user['openid'];
        $logic = new PaymentLogic();
        $result = $logic->getGiftPayCode($param);
        if($result['status']){
            $this->apiReturn('1001','成功',$result['msg']);
//            $this->assign('jsApiParameters',$result['msg']);
//            return $this->fetch();
        }else{
            $this->apiReturn('1002',$result['msg'],'');
        }
    }
}
