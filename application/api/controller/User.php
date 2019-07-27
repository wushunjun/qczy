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
use app\common\logic\UsersLogic;

class User extends Apibase
{

    /**
     * 用户详情
     * @param int user_id 用户id
     */
    public function user_info(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $result = model('users')->find($param['user_id']);
        if($result){
            $this->apiReturn('1001','成功',$result);
        }else{
            $this->apiReturn('1002','失败','');
        }
    }

    /**
     * 个人主页基础信息
     * @param int page_user_id 被访问的主页主人的id
     */
    public function user_page(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'page_user_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $user = model('user')->where(['user_id'=>$param['page_user_id']])->field('user_pay_pass',true)->find();
        if(!$user)
            $this->apiReturn('1002','用户不存在','');
        $user = $user->toArray();
        $user['user_images'] = my_explode(',',$user['user_images']);//相册
        $user['fans_count'] = Db::name('follow')->where(['to_user_id'=>$param['page_user_id']])->count();//粉丝数量
        $user['flower_count'] = Db::name('reward')->where(['to_user_id'=>$param['page_user_id'],'reward_type'=>1])->count();//被打赏次数
        $is_follow = Db::name('follow')->where(['to_user_id'=>$param['page_user_id'],'user_id'=>$param['user_id']])->find();
        $user['is_follow'] = $is_follow ? 1 : 0;
        if($user){
            $this->apiReturn('1001','成功',$user);
        }else{
            $this->apiReturn('1002','失败','');
        }
    }
    /**
     * 个人信息编辑
     * @param int user_id 用户id
     * @param string head_pic 头像地址
     * @param string nickname 昵称
     * @param int sex 性别，0未知，1男，2女
     * @param int age 年龄
     * @param string wechat_number 微信号
     * @param string hobby 兴趣爱好
     * @param string introduce 个性签名
     */
    function user_edit(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'sex' => 'integer',
            'age' => 'integer',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $result = model('users')->where(['user_id'=>$param['user_id']])->update($param);
        if($result){
            $user = model('users')->where(['user_id'=>$param['user_id']])->find();
            $this->apiReturn('1001','成功',$user);
        }else{
            $this->apiReturn('1002', '没有信息被更改', '');
        }
    }

    /**
     * 获取用户地址列表
     * @param int user_id 用户id
     */
    public function get_address_list()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $address_list = model('UserAddress')->where('user_id', $param['user_id'])->order('is_default desc')->select();
        if($address_list){
            $address_list = collection($address_list)->append(['address_area'])->toArray();
        }else{
            $address_list = [];
        }
        $this->apiReturn('1001','成功',$address_list);
    }
    /**
     * 地址详情
     * @param int address_id 地址id
     */
    public function address_info()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'address_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $address = model('UserAddress')->where('address_id', $param['address_id'])->find();
        if(!$address){
            $this->apiReturn('1002','地址不存在','');
        }
        $address->append(['province_name','city_name','district_name'])->toArray();
        $this->apiReturn('1001','成功',$address);
    }
    /**
     * 添加/编辑地址
     * @param int user_id 用户id
     * @param string consignee 收货人
     * @param string mobile 手机号
     * @param int province 省id
     * @param int city 市id
     * @param int district 区id
     * @param string address 详细地址
     * @param int is_default 是否默认，0否，1是
     * @param int address_id 地址id，编辑时必传
     */
    public function add_edit_address()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'consignee' => 'require',
            'mobile' => 'mobile|require',
            'province' => 'integer|require',
            'city' => 'integer|require',
            'district' => 'integer|require',
            'address' => 'require',
            'is_default' => 'integer|require',
            'address_id' => 'integer',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $logic = new UsersLogic();
        $address_id = $param['address_id'] ? $param['address_id'] : 0;
        unset($param['address_id']);
        $data = $logic->add_address($param['user_id'], $address_id, $param);
        if ($data['status'] != 1){
            $this->apiReturn('1002',$data['msg'],'');
        } else {
            $this->apiReturn('1001','成功','');
        }
    }
    /**
     * 地址删除
     * @param address_id 地址id
     * @param user_id 用户id
     */
    public function del_address()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'address_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $address = M('user_address')->where("address_id", $param['address_id'])->find();
        $row = M('user_address')->where(array('user_id' => $param['user_id'], 'address_id' => $param['address_id']))->delete();
        // 如果删除的是默认收货地址 则要把第一个地址设置为默认收货地址
        if ($address['is_default'] == 1) {
            $address2 = M('user_address')->where("user_id", $param['user_id'])->find();
            $address2 && M('user_address')->where("address_id", $address2['address_id'])->save(array('is_default' => 1));
        }
        if (!$row)
            $this->apiReturn('1002','失败','');
        else
            $this->apiReturn('1001','成功','');
    }
    /**
     * 我的礼品
     * @param user_id 用户id
     * @param int page 页码
     * @param int pageSize 单页显示数量
     */
    public function gift_list()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'page' => 'integer',
            'pageSize' => 'integer',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $list = model('reward')->where("to_user_id", $param['user_id'])->order('reward_id desc')->page(page($param))->select();
        $this->apiReturn('1001','成功',$list);
    }
    /**
     * 最近来访
     * @param int user_id 用户id
     * @param string user_lng 经度
     * @param string user_lat 纬度
     * @param int page 页码
     * @param int pageSize 单页显示数量
     */
    public function visit_log()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'user_lat' => 'number|require',
            'user_lng' => 'number|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        model('users')->where(['user_id' => $param['user_id']])->update(['user_lat' => $param['user_lat'], 'user_lng' => $param['user_lng']]);//更新自己的坐标
        $visit_list = Db::name('user_visit')->where(['to_user_id'=>$param['user_id']])->column('user_id,uv_add_time');
        $visit_id = array_keys($visit_list);
        $where = ['user_id'=>['in',$visit_id]];
        $lists = model('users')->get_lists($param,$where);
        foreach($lists as $k=>$v){
            $time = $visit_list[$v['user_id']];
            if(time() - $time > 3600){//大于一个小时的
                $lists[$k]['last_login_time'] = round((time() - $time)/3600,0) . '小时前';
            }else{
                $minute = round((time() - $time)/60,0);
                if($minute)
                    $lists[$k]['last_login_time'] = $minute . '分钟前';
                else
                    $lists[$k]['last_login_time'] = '1分钟前';
            }
        }
        $this->apiReturn('1001', '成功', $lists);
    }
    /**
     * 邀请列表
     * @param user_id 用户id
     */
    public function invite_list()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $list = model('users')
            ->where(['first_leader'=>$param['user_id']])
            ->whereOr(['second_leader'=>$param['user_id']])
            //->field('user_id,first_leader')
            ->order('user_id desc')
            ->select();
        if($list){
            $list = cateMerge(collection($list)->toArray(),'user_id','first_leader',$param['user_id']);
        }
        $this->apiReturn('1001','成功',$list);
    }
    /**
     * 二维码
     * @param user_id 用户id
     */
    public function qr_code()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $url = 'http://beautiful.tanwenchao.com/#/home/index?share_id='.$param['user_id'];
        $image = '/public/QR_code/spread'.$param['user_id'].'.png';
        if(!file_exists('.'.$image)){
            build_qr_code($url,'spread'.$param['user_id']);
        }
        $this->apiReturn('1001','成功',$image);
    }
}
