<?php
namespace app\api\controller;

use think\image\Exception;
use think\Page;
use think\Db;

class Account extends Apibase {
    /**
     * 余额或积分收支明细
     * @param int user_id 用户id
     * @param int type 类型，0余额，1积分，2分销收益
     * @param int page 页码
     * @param int pageSize 单页显示数量
     */
    public function account_list(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'type' => 'in:0,1,2|require',
            'page' => 'integer',
            'pageSize' => 'integer',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        if($param['type'] == 0){
            $where = "user_money !=0 and user_id=" . $param['user_id'];
        }elseif($param['type'] == 1){
            $where = "pay_points !=0 and user_id=" . $param['user_id'];
        }elseif($param['type'] == 2){
            $where = "user_money > 0 and user_id=" . $param['user_id'];
        }
        $account_log = model('account_log')->field("*,from_unixtime(change_time,'%Y-%m-%d %H:%i:%s') AS change_data")->where($where)
            ->order('log_id desc')->page(page($param))->select();
        $this->apiReturn('1001','成功',$account_log);
    }
    /**
     * 提现申请
     * @param int user_id 用户id
     * @param int money 金额
     */
    public function withdrawals(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'money' => 'number|gt:0|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        if ($param['money'] > $this->user['user_money']) {
            $this->apiReturn('1002','本次提现余额不足','');
        }
        $param['create_time'] = time();
        try{
            Db::startTrans();
            $res = accountLog($param['user_id'], -$param['money'],0, '申请提现',0,0 ,'');
            if(!$res){
                new Exception('提现申请失败');
            }
            $res = Db::name('withdrawals')->insertGetId($param);
            if(!$res){
                new Exception('提现申请失败');
            }
            Db::commit();
            $this->apiReturn('1001','成功','');
        }catch (Exception $e){
            Db::rollback();
            $this->apiReturn('1002',$e->getMessage(),'');
        }
    }
    /**
     * 提现记录
     * @param int user_id 用户id
     * @param int page 页码
     * @param int pageSize 单页显示数量
     */
    public function withdrawals_log(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'page' => 'integer',
            'pageSize' => 'integer',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $withdrawals = Db::name('withdrawals')->where(['user_id'=>$param['user_id']])
            ->order('id desc')->page(page($param))->select();
        $this->apiReturn('1001','成功',$withdrawals);
    }
    /**
     * 消费记录
     * @param int user_id 用户id
     * @param int page 页码
     * @param int pageSize 单页显示数量
     */
    public function pay_log(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'page' => 'integer',
            'pageSize' => 'integer',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $list = Db::name('pay_log')->where(['user_id'=>$param['user_id']])
            ->order('log_id desc')->page(page($param))->select();
        $this->apiReturn('1001','成功',$list);
    }
}