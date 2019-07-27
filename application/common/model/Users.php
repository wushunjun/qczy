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

use think\Db;
use think\Model;

class Users extends Model
{
    //自定义初始化
    protected static function init()
    {
        //TODO:自定义的初始化
    }

    public function oauthUsers()
    {
        return $this->hasMany('OauthUsers', 'user_id', 'user_id');
    }

    public function userLevel()
    {
        return $this->hasOne('UserLevel', 'level_id', 'level');
    }

    /**
     * 用户下线分销金额
     * @param $value
     * @param $data
     * @return float|int
     */
    public function getRebateMoneyAttr($value, $data){
        $sum_money = DB::name('rebate_log')->where(['status' => 3,'user_id'=>$data['user_id']])->sum('money');
        $rebate_money = empty($sum_money) ? (float)0 : $sum_money;
        return  $rebate_money;
    }

    /**
     * 用户一级下线数
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getFisrtLeaderNumAttr($value, $data){
        $fisrt_leader = Users::where(['first_leader'=>$data['user_id']])->count();
        return  $fisrt_leader;
    }

    /**
     * 用户二级下线数
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getSecondLeaderNumAttr($value, $data){
        $second_leader = Users::where(['second_leader'=>$data['user_id']])->count();
        return  $second_leader;
    }

    /**
     * 用户二级下线数
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getThirdLeaderNumAttr($value, $data){
        $third_leader = Users::where(['third_leader'=>$data['user_id']])->count();
        return  $third_leader;
    }
    /**
     * 获取用户列表，按照直线距离排序
     */
    public function get_lists($param,$where)
    {
        $lists = $this->
        field('round(6378.138 * 2 * asin(sqrt(pow(sin((' . $param['user_lat'] . ' * pi() / 180 - user_lat * pi() / 180) / 2),2) + cos(' . $param['user_lat'] . ' * pi() / 180) * cos(user_lat * pi() / 180) * pow(sin((' . $param['user_lng'] . ' * pi() / 180 - user_lng * pi() / 180) / 2),2))) * 1000,-1) distance,head_pic,nickname,age,introduce,sex,user_id,last_login_time')->
        where($where)->
        page(page($param))->
        order('distance asc')->select();
        foreach ($lists as $k => $v) {
            if($v['distance'] >= 1000){
                $lists[$k]['distance'] = round($v['distance']/1000,0) . 'km';
            }else{
                $v['distance'] = $v['distance'] < 100 ? 100 : $v['distance'];
                $lists[$k]['distance'] = round($v['distance'],-2) . 'm';
            }
            if(time() - $v['last_login_time'] > 3600){//大于一个小时的
                $lists[$k]['last_login_time'] = round((time() - $v['last_login_time'])/3600,0) . '小时前';
            }else{
                $minute = round((time() - $v['last_login_time'])/60,0);
                if($minute)
                    $lists[$k]['last_login_time'] = $minute . '分钟前';
                else
                    $lists[$k]['last_login_time'] = '1分钟前';
            }
        }
        return $lists;
    }
}
