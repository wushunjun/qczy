<?php

namespace app\admin\controller;

use think\Db;
use think\Page;

class Luck extends Base
{
    public function index()
    {
        $keywords = I('keywords');
        $where = ['is_del'=>0];
        if($keywords){
            $where['luck_name'] = ['like',"%$keywords%"];
        }
        $count = Db::name('luck')->where($where)->count();// 查询满足要求的总记录数
        $Page = $pager = new Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数
        $list = Db::name('luck')->where($where)->order('luck_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $luck_type_list = Db::name('luck_type')->cache(true)->column('lt_id,lt_name');
        $show = $Page->show();
        $this->assign('list', $list);
        $this->assign('luck_type_list', $luck_type_list);
        $this->assign('page', $show);
        return $this->fetch();
    }

    /**
     * 详情
     * @return mixed
     */
    public function info()
    {
        $luck_id = input('luck_id/d');
        if ($luck_id) {
            $info = Db::name('luck')->where(['luck_id' => $luck_id,'is_del'=>0])->find();
            if (empty($info)) {
                $this->error('非法操作');
            }
            $this->assign('info', $info);
        }
        $luck_type_list = Db::name('luck_type')->select();
        $this->assign('luck_type_list', $luck_type_list);
        return $this->fetch();
    }

    /**
     * 提交操作
     */
    public function postHandle(){
        $data = I('post.');

        $result = $this->validate($data, 'luck.'.$data['act'], [], true);
        if ($result !== true) {
            $this->ajaxReturn(['status' => 0, 'msg' => '参数错误', 'result' => $result]);
        }
        $data['luck_album'] = $data['luck_album'] ? implode(',',$data['luck_album']) : '';
        switch($data['act']){
            case 'add':
                $data['luck_add_time'] = time();
                $res = Db::name('luck')->add($data);
                break;
            case 'edit':
                $res = Db::name('luck')->update($data);
                break;
            case 'del':
                $res = Db::name('luck')->where(['luck_id'=>$data['del_id']])->update(['is_del'=>1]);
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

    /**
     * 评论列表
     * @return mixed
     */
    public function commentList()
    {
        $luck_id = I('luck_id');
        $where = ['luck_id'=>$luck_id];
        $count = Db::name('luck_comment')->where($where)->count();// 查询满足要求的总记录数
        $Page = $pager = new Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数
        $list = Db::name('luck_comment')->where($where)->order('lc_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $show = $Page->show();
        $this->assign('list', $list);
        $this->assign('luck_id', $luck_id);
        $this->assign('page', $show);
        return $this->fetch();
    }
    /**
     * 添加评论
     * @return mixed
     */
    public function add_comment()
    {
        $luck_id = input('luck_id/d');
        if ($luck_id) {
            $info = Db::name('luck')->where(['luck_id' => $luck_id,'is_del'=>0])->find();
            if (empty($info)) {
                $this->error('非法操作');
            }
            $this->assign('info', $info);
        }
        $this->assign('luck_id', $luck_id);
        return $this->fetch();
    }
    /**
     * 提交操作
     */
    public function commentPost(){
        $data = I('post.');
        switch($data['act']){
            case 'add':
                $save_data = [];
                foreach($data['lc_nickname'] as $k=>$v){
                    $save_data[$k]['lc_nickname'] = $v;
                    $save_data[$k]['lc_content'] = $data['lc_content'][$k];
                    $save_data[$k]['luck_id'] = $data['luck_id'];
                }
                $res = Db::name('luck_comment')->insertAll($save_data);
                break;
            case 'del':
                $res = Db::name('luck_comment')->where(['lc_id'=>$data['del_id']])->delete();
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
