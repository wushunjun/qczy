<?php

namespace app\admin\controller;

use think\Db;
use think\Page;

class Project extends Base
{
    public function index()
    {
        $count = Db::name('project')->count();// 查询满足要求的总记录数
        $Page = $pager = new Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数
        $list = Db::name('project')->order('project_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_list = Db::name('shop')->cache(true)->column('shop_id,shop_name');
        $show = $Page->show();
        $this->assign('list', $list);
        $this->assign('shop_list', $shop_list);
        $this->assign('page', $show);
        return $this->fetch();
    }

    /**
     * 详情
     * @return mixed
     */
    public function info()
    {
        $project_id = input('project_id/d');
        if ($project_id) {
            $info = Db::name('project')->where(['project_id' => $project_id,'is_del'=>0])->find();
            if (empty($info)) {
                $this->error('非法操作');
            }
            if($info['project_album'])
                $info['project_album'] = explode(',',$info['project_album']);
            $this->assign('info', $info);
        }
        $shop_list = Db::name('shop')->select();
        $this->assign('shop_list', $shop_list);
        return $this->fetch();
    }

    /**
     * 提交操作
     */
    public function postHandle(){
        $data = I('post.');

        $result = $this->validate($data, 'project.'.$data['act'], [], true);
        if ($result !== true) {
            $this->ajaxReturn(['status' => 0, 'msg' => '参数错误', 'result' => $result]);
        }
        $data['project_album'] = $data['project_album'] ? implode(',',$data['project_album']) : '';
        switch($data['act']){
            case 'add':
                $data['project_add_time'] = time();
                $res = Db::name('project')->add($data);
                break;
            case 'edit':
                $res = Db::name('project')->update($data);
                break;
            case 'del':
                $res = Db::name('project')->where(['project_id'=>$data['del_id']])->update(['is_del'=>1]);
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
