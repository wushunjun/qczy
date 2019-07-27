<?php

namespace app\admin\controller;

use think\Db;
use think\Page;

class Shake extends Base
{
    public function index()
    {
        $count = Db::name('shake')->alias('a')->join('users b','a.user_id = b.user_id')->count();// 查询满足要求的总记录数
        $Page = $pager = new Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数
        $list = Db::name('shake')->alias('a')->join('users b','a.user_id = b.user_id')->order('shake_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
        $shake_id = input('shake_id/d');
        if ($shake_id) {
            $info = Db::name('shake')->where(['shake_id' => $shake_id,'is_del'=>0])->find();
            if (empty($info)) {
                $this->error('非法操作');
            }
            if($info['shake_album']){
                $info['shake_album'] = explode(',',$info['shake_album']);
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

        $result = $this->validate($data, 'shake.'.$data['act'], [], true);
        if ($result !== true) {
            $this->ajaxReturn(['status' => 0, 'msg' => '参数错误', 'result' => $result]);
        }
        $data['shake_album'] = $data['shake_album'] ? implode(',',$data['shake_album']) : '';
        switch($data['act']){
            case 'add':
                $data['shake_add_time'] = time();
                $res = Db::name('shake')->add($data);
                break;
            case 'edit':
                $res = Db::name('shake')->update($data);
                break;
            case 'del':
                $res = Db::name('shake')->where(['shake_id'=>$data['del_id']])->delete();
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
