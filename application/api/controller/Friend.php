<?php
namespace app\api\controller;

use think\Db;

class Friend extends Apibase {
    /**
     * 感兴趣的人
     * @param int user_id 用户id
     * @param string user_lng 经度
     * @param string user_lat 纬度
     * @param int page 页码
     * @param int pageSize 单页显示数量
     * @return array
     */
    public function interest_list(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_lat' => 'number|require',
            'user_id' => 'integer|require',
            'user_lng' => 'number|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        model('users')->where(['user_id' => $param['user_id']])->update(['user_lat' => $param['user_lat'], 'user_lng' => $param['user_lng']]);//更新自己的坐标
        $where = ['user_id' => ['neq', $param['user_id']]];
        $lists = model('users')->get_lists($param,$where);
        $this->apiReturn('1001', '成功', $lists);
    }
    /**
     * 我的好友列表
     * @param int user_id 用户id
     * @param string user_lng 经度
     * @param string user_lat 纬度
     * @param int page 页码
     * @param int pageSize 单页显示数量
     * @return array
     */
    public function friend_list(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'user_lat' => 'number|require',
            'user_lng' => 'number|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        model('users')->where(['user_id' => $param['user_id']])->update(['user_lat' => $param['user_lat'], 'user_lng' => $param['user_lng']]);//更新自己的坐标
        $friend_id = Db::name('follow')->where(['user_id'=>$param['user_id']])->column('to_user_id');
        $where = ['user_id'=>['in',$friend_id]];
        $lists = model('users')->get_lists($param,$where);
        $this->apiReturn('1001', '成功', $lists);
    }
    /**
     * 用户主页
     * @param int page_user_id 被查看用户id
     * @return array
     */
    public function user_page(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'page_user_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $result = model('users')->find($param['page_user_id']);
        Db::name('user_visit')->where(['user_id'=>$param['user_id'],'to_user_id'=>$param['page_user_id']])->delete();
        Db::name('user_visit')->insert(['user_id'=>$param['user_id'],'to_user_id'=>$param['page_user_id'],'uv_add_time'=>time()]);
        if($result){
            $this->apiReturn('1001','成功',$result);
        }else{
            $this->apiReturn('1002','失败','');
        }
    }
}