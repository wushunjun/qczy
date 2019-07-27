<?php

namespace app\admin\controller;

use think\Db;
use think\Page;

class TaskResource extends Base
{
    public function index()
    {
        $keywords = I('keywords');
        $where = ['is_del'=>0];
        if($keywords){
            $where['tr_name'] = ['like',"%$keywords%"];
        }
        $count = Db::name('task_resource')->where($where)->count();// 查询满足要求的总记录数
        $Page = $pager = new Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数
        $list = Db::name('task_resource')->where($where)->order('tr_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $show = $Page->show();
        $this->assign('list', $list);
        $this->assign('page', $show);
        return $this->fetch();
    }

    /**
     * 详情
     * @return mixed
     */
    public function info()
    {
        $tr_id = input('tr_id/d');
        if ($tr_id) {
            $info = Db::name('task_resource')->where(['tr_id' => $tr_id,'is_del'=>0])->find();
            if (empty($info)) {
                $this->error('非法操作');
            }
            $this->assign('info', $info);
        }
        return $this->fetch();
    }

    /**
     * 提交操作
     */
    public function postHandle(){
        $data = I('post.');

        $result = $this->validate($data, 'task_resource.'.$data['act'], [], true);
        if ($result !== true) {
            $this->ajaxReturn(['status' => 0, 'msg' => '参数错误', 'result' => $result]);
        }
        switch($data['act']){
            case 'add':
                $data['task_resource_add_time'] = time();
                $res = Db::name('task_resource')->add($data);
                break;
            case 'edit':
                $res = Db::name('task_resource')->update($data);
                break;
            case 'del':
                $res = Db::name('task_resource')->where(['tr_id'=>$data['del_id']])->update(['is_del'=>1]);
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
