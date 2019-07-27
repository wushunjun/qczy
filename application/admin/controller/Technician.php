<?php

namespace app\admin\controller;

use think\Db;
use think\Page;

class Technician extends Base
{
    public function index()
    {
        $keywords = I('keywords');
        $where = ['is_del'=>0];
        if($keywords){
            $where['technician_name'] = ['like',"%$keywords%"];
        }
        $count = Db::name('technician')->where($where)->count();// 查询满足要求的总记录数
        $Page = $pager = new Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数
        $list = Db::name('technician')->where($where)->order('technician_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
        $technician_id = input('technician_id/d');
        if ($technician_id) {
            $info = Db::name('technician')->where(['technician_id' => $technician_id,'is_del'=>0])->find();
            if (empty($info)) {
                $this->error('非法操作');
            }
            if($info['technician_album']){
                $info['technician_album'] = explode(',',$info['technician_album']);
            }
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

        $result = $this->validate($data, 'technician.'.$data['act'], [], true);
        if ($result !== true) {
            $this->ajaxReturn(['status' => 0, 'msg' => '参数错误', 'result' => $result]);
        }
        $data['technician_album'] = $data['technician_album'] ? implode(',',$data['technician_album']) : '';
        switch($data['act']){
            case 'add':
                $data['technician_add_time'] = time();
                $res = Db::name('technician')->add($data);
                break;
            case 'edit':
                $res = Db::name('technician')->update($data);
                break;
            case 'del':
                $res = Db::name('technician')->where(['technician_id'=>$data['del_id']])->update(['is_del'=>1]);
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
