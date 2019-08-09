<?php

namespace app\admin\controller;

use think\Db;
use think\Page;

class Dynamic extends Base
{
    public function index()
    {
        $keywords = I('keywords');
        $where = ['is_del'=>0];
        if($keywords){
            $where['dynamic_content'] = ['like',"%$keywords%"];
        }
        $count = Db::name('dynamic')->where($where)->count();// 查询满足要求的总记录数
        $Page = $pager = new Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数
        $list = model('dynamic')->where($where)->with('User')->order('dynamic_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k=>$v){
            $list[$k]['dynamic_images'] = my_explode(',',$v['dynamic_images']);
        }
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
        $dynamic_id = input('dynamic_id/d');
        if ($dynamic_id) {
            $info = Db::name('dynamic')->where(['dynamic_id' => $dynamic_id,'is_del'=>0])->find();
            if (empty($info)) {
                $this->error('非法操作');
            }
            $this->assign('info', $info);
        }
        $dynamic_type_list = Db::name('dynamic_type')->select();
        $this->assign('dynamic_type_list', $dynamic_type_list);
        return $this->fetch();
    }

    /**
     * 提交操作
     */
    public function postHandle(){
        $data = I('post.');

        $result = $this->validate($data, 'dynamic.'.$data['act'], [], true);
        if ($result !== true) {
            $this->ajaxReturn(['status' => 0, 'msg' => '参数错误', 'result' => $result]);
        }
        $data['dynamic_album'] = $data['dynamic_album'] ? implode(',',$data['dynamic_album']) : '';
        switch($data['act']){
            case 'add':
                $data['dynamic_add_time'] = time();
                $res = Db::name('dynamic')->add($data);
                break;
            case 'edit':
                $res = Db::name('dynamic')->update($data);
                break;
            case 'del':
                $res = Db::name('dynamic')->where(['dynamic_id'=>$data['del_id']])->update(['is_del'=>1]);
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
        $dynamic_id = I('dynamic_id');
        $where = ['dynamic_id'=>$dynamic_id];
        $count = Db::name('dynamic_comment')->where($where)->count();// 查询满足要求的总记录数
        $Page = $pager = new Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数
        $list = Db::name('dynamic_comment')->alias('a')
            ->join('users b','a.user_id = b.user_id')
            ->where($where)
            ->order('comment_id DESC')
            ->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $show = $Page->show();
        $this->assign('list', $list);
        $this->assign('dynamic_id', $dynamic_id);
        $this->assign('page', $show);
        return $this->fetch();
    }
    /**
     * 添加评论
     * @return mixed
     */
    public function add_comment()
    {
        $dynamic_id = input('dynamic_id/d');
        if ($dynamic_id) {
            $info = Db::name('dynamic')->where(['dynamic_id' => $dynamic_id,'is_del'=>0])->find();
            if (empty($info)) {
                $this->error('非法操作');
            }
            $this->assign('info', $info);
        }
        $this->assign('dynamic_id', $dynamic_id);
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
                foreach($data['comment_nickname'] as $k=>$v){
                    $save_data[$k]['comment_nickname'] = $v;
                    $save_data[$k]['comment_content'] = $data['comment_content'][$k];
                    $save_data[$k]['dynamic_id'] = $data['dynamic_id'];
                }
                $res = Db::name('dynamic_comment')->insertAll($save_data);
                break;
            case 'del':
                $res = Db::name('dynamic_comment')->where(['comment_id'=>$data['del_id']])->delete();
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
