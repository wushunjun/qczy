<?php

namespace app\admin\controller;

use think\Db;
use think\Page;

class Task extends Base
{
    public function index()
    {
        $user_id = I('user_id/d');
        $where = ['user_id'=>$user_id];
        $count = Db::name('task')->where($where)->count();// 查询满足要求的总记录数
        $Page = $pager = new Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数
        $list = Db::name('task')->where($where)->order('task_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $show = $Page->show();
        $this->assign('list', $list);
        $this->assign('user_id', $user_id);
        $this->assign('page', $show);
        return $this->fetch();
    }

    /**
     * 详情
     * @return mixed
     */
    public function info()
    {
        $user_id = I('user_id/d');
        $task_resource = Db::name('task_resource')->where(['is_del'=>0])->select();
        $this->assign('user_id', $user_id);
        $this->assign('task_resource', $task_resource);
        return $this->fetch();
    }

    /**
     * 提交操作
     */
    public function postHandle(){
        $data = I('post.');

        $result = $this->validate($data, 'task.'.$data['act'], [], true);
        if ($result !== true) {
            $this->ajaxReturn(['status' => 0, 'msg' => '参数错误', 'result' => $result]);
        }
        switch($data['act']){
            case 'add':
                $data['task_add_time'] = time();
                $data['task_start_time'] = strtotime($data['task_start_time']);
                $data['task_end_time'] = $data['task_start_time'] + $data['task_days'] * 3600 * 24 - 1;
                $res = Db::name('task')->insertGetId($data);
                $card_data = [];
                foreach($data['tr_id'] as $k=>$v){
                    $card_data[$k]['tr_id'] = $v;
                    $card_data[$k]['user_id'] = $data['user_id'];
                    $card_data[$k]['task_id'] = $res;
                    $card_data[$k]['card_days'] = $data['task_days'];
                    $card_data[$k]['card_surplus_days'] = $data['task_days'];
                }
                Db::name('card')->insertAll($card_data);
                break;
            case 'del':
                $res = Db::name('task')->where(['task_id'=>$data['del_id']])->delete();
                Db::name('card')->where(['task_id'=>$data['del_id']])->delete();
                break;
            default:
                $res = 0;
                break;
        }
        if($res){
            $this->ajaxReturn(['status' => 1, 'msg' => '操作成功', 'result' => '']);
        }else{
            $this->ajaxReturn(['status' => 0, 'msg' => '操作失败', 'result' => '']);
        }
    }
}
