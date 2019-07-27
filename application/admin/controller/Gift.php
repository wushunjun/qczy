<?php

namespace app\admin\controller;

use think\Db;
use think\Page;

class Gift extends Base
{
    public function index()
    {
        $count = Db::name('gift')->count();// 查询满足要求的总记录数
        $Page = $pager = new Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数
        $list = Db::name('gift')->order('gift_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
        $gift_id = input('gift_id/d');
        if ($gift_id) {
            $info = Db::name('gift')->where(['gift_id' => $gift_id,'is_del'=>0])->find();
            if (empty($info)) {
                $this->error('非法操作');
            }
            if($info['gift_album']){
                $info['gift_album'] = explode(',',$info['gift_album']);
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

        $result = $this->validate($data, 'gift.'.$data['act'], [], true);
        if ($result !== true) {
            $this->ajaxReturn(['status' => 0, 'msg' => '参数错误', 'result' => $result]);
        }
        switch($data['act']){
            case 'add':
                $data['gift_add_time'] = time();
                $res = Db::name('gift')->add($data);
                break;
            case 'edit':
                $res = Db::name('gift')->update($data);
                break;
            case 'del':
                $res = Db::name('gift')->where(['gift_id'=>$data['del_id']])->update(['is_del'=>1]);
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
