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

class Dynamic extends Apibase
{
    protected $model = '';
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->model = model('dynamic');
    }
    /**
     * 发布动态
     * @param int user_id 用户id
     * @param string dynamic_content 内容
     * @param array dynamic_images 图片地址，注意是数组哟
     */
    function release_dynamic(){
        $param = request()->post();
        $rule = [
            'user_id' => 'integer|require',
            'dynamic_content' => 'require',
            'dynamic_images' => 'array|require',
        ];
        $validate = $this->validate($param,$rule);
        if($validate !== true)
            $this->paramError($validate);
        $user = model('users')->with('userLevel')->find($param['user_id']);
        if($user['userLevel']['count'] > 0){
            $start_time = strtotime(date('y-m-d'));
            $end_time = $start_time + 3600 * 24 - 1;
            $count = $this->model->where(['dynamic_add_time'=>['between',"$start_time,$end_time"],'user_id'=>$param['user_id']])->count();
            if($count >= $user['userLevel']['count']){
                $msg = '抱歉，' . $user['userLevel']['level_name'] . '会员每日发布动态上线为' . $user['userLevel']['count'] . '条';
                $this->apiReturn('1002',$msg,'');
            }
        }
        $param['dynamic_add_time'] = time();
        $param['dynamic_images'] = implode(',',$param['dynamic_images']);
        $result = $this->model->allowField(true)->save($param);
        if($result){
            $this->apiReturn('1001','成功',$this->model->dynamic_id);
        }else{
            $this->apiReturn('1002','失败','');
        }
    }
    /**
     * 动态列表
     * @param int user_id 用户id
     * @param int page_user_id 被访问的主页主人的id，非必传
     * @param int page 页码
     * @param int pageSize 单页显示数量
     */
    function dynamic_lists(){
        $param = request()->param();
        $validate = $this->validate($param,[
            'user_id' => 'integer|require',
            'page_user_id' => 'integer',
            'page' => 'integer',
            'pageSize' => 'integer',
        ]);
        if($validate !== true)
            $this->paramError($validate);
        $where = ['dynamic_status'=>1];
        if($param['page_user_id'])
            $where['a.user_id'] = $param['page_user_id'];
        $lists = $this->model->alias('a')->join('users b','a.user_id = b.user_id')->page(page($param))
            ->where($where)->order('dynamic_id desc')->field('a.*,b.user_id,b.nickname,b.head_pic,b.sex')->select();
        foreach($lists as $k=>$v){
            $lists[$k]['dynamic_images'] = my_explode(',',$v['dynamic_images']);
            $param['dynamic_id'] = $v['dynamic_id'];
            $lists[$k]['is_praise'] = model('praise')->is_praise($param);
        }
        $this->apiReturn('1001','成功',$lists);
    }
    /**
     * 动态详情
     * @param int user_id 用户id
     * @param int dynamic_id 动态id
     */
    function dynamic_info(){
        $param = request()->param();
        $validate = $this->validate($param,[
            'user_id' => 'integer|require',
            'dynamic_id' => 'integer|require',
        ]);
        if($validate !== true)
            $this->paramError($validate);
        $info = $this->model->alias('a')->join('users b','a.user_id = b.user_id')
            ->where(['dynamic_id'=>$param['dynamic_id'],'dynamic_status'=>1])
            ->field('a.*,b.user_id,b.nickname,b.head_pic,b.sex')
            ->find();
        if($info){
            $info['is_praise'] = model('praise')->is_praise($param);//是否点赞或报名过
            $info['dynamic_images'] = my_explode(',',$info['dynamic_images']);
            $this->apiReturn('1001','成功',$info);
        }else{
            $this->apiReturn('1002','失败','');
        }
    }
    /**
     * 删除动态
     * @param int dynamic_id 动态id
     * @param int user_id 用户id
     */
    function del_dynamic(){
        $param = request()->param();
        $validate = $this->validate($param,[
            'dynamic_id' => 'integer|require',
            'user_id' => 'integer|require',
        ]);
        if($validate !== true)
            $this->paramError($validate);
        $res = $this->model->where(['dynamic_id'=>$param['dynamic_id'],'dynamic_status'=>1,'user_id'=>$param['user_id']])->delete();
        if($res){
            $this->apiReturn('1001','成功','');
        }else{
            $this->apiReturn('1002','非法操作','');
        }
    }
}
