<?php

namespace app\admin\controller;

use app\common\model\Shopper;
use think\Loader;
use think\Db;
use think\Page;

class Shop extends Base
{
    public function index()
    {
        $count = Db::name('shop')->count();// 查询满足要求的总记录数
        $Page = $pager = new Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数
        $list = Db::name('shop')->order('shop_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $v){
            $id_arr[] = $v['province_id'];
            $id_arr[] = $v['city_id'];
            $id_arr[] = $v['district_id'];
        }
        $region_list = Db::name('region')->where(['id'=>['in',$id_arr]])->column('id,name');
        $show = $Page->show();
        $this->assign('list', $list);
        $this->assign('region_list', $region_list);
        $this->assign('page', $show);
        return $this->fetch();
    }

    /**
     * 门店自提点
     * @return mixed
     */
    public function info()
    {
        $shop_id = input('shop_id/d');
        if ($shop_id) {
            $Shop = new \app\common\model\Shop();
            $shop = $Shop->where(['shop_id' => $shop_id,'deleted' => 0])->find();
            if (empty($shop)) {
                $this->error('非法操作');
            }
            $city_list = Db::name('region')->where(['parent_id'=>$shop['province_id'],'level'=> 2])->select();
            $district_list = Db::name('region')->where(['parent_id'=>$shop['city_id']])->select();
            $shop_image_list = Db::name('shop_images')->where(['shop_id'=>$shop['shop_id']])->select();
            $this->assign('city_list', $city_list);
            $this->assign('district_list', $district_list);
            $this->assign('shop_image_list', $shop_image_list);
            $this->assign('shop', $shop);
        }
        $province_list = Db::name('region')->where(['parent_id'=>0,'level'=> 1])->cache(true)->select();
        $suppliers_list = Db::name("suppliers")->where(['is_check'=>1])->select();
        $this->assign('suppliers_list', $suppliers_list);
        $this->assign('province_list', $province_list);
        return $this->fetch();
    }

    /**
     * 添加店铺
     */
    public function add(){
        $data = I('post.');

        $result = $this->validate($data, 'Shop.add', [], true);
        if ($result !== true) {
            $this->ajaxReturn(['status' => 0, 'msg' => '参数错误', 'result' => $result]);
        }
        $data['add_time'] = time();
        $shop_images = $data['shop_images'];
        unset($data['shop_images']);
        $shop_id = model('Shop')->insertGetId($data);
        if($shop_id){
            if($shop_images){
                $shop_images_data = [];
                foreach($shop_images as $k=>$v){
                    $shop_images_data[$k]['shop_id'] = $shop_id;
                    $shop_images_data[$k]['image_url'] = $v;
                }
                model('shop_images')->insertAll($shop_images_data);
            }
            $this->ajaxReturn(['status' => 1, 'msg' => '添加成功', 'result' => '']);
        }else{
            $this->ajaxReturn(['status' => 0, 'msg' => '添加失败', 'result' => '']);
        }
    }
    /**
     * 编辑店铺
     */
    public function save(){
        $data = I('post.');

        $result = $this->validate($data, 'Shop', [], true);
        if ($result !== true) {
            $this->ajaxReturn(['status' => 0, 'msg' => '参数错误', 'result' => $result]);
        }
        $data['add_time'] = time();
        $shop_images = $data['shop_images'];
        $shop_id = $data['shop_id'];
        unset($data['shop_images']);
        $res = model('Shop')->update($data);
        if($res){
            if($shop_images){
                $shop_images_data = [];
                foreach($shop_images as $k=>$v){
                    model('shop_images')->where(['shop_id'=>$shop_id,'image_url'=>$v])->delete();
                    $shop_images_data[$k]['shop_id'] = $shop_id;
                    $shop_images_data[$k]['image_url'] = $v;
                }
                model('shop_images')->insertAll($shop_images_data);
            }
            $this->ajaxReturn(['status' => 1, 'msg' => '更新成功', 'result' => '']);
        }else{
            $this->ajaxReturn(['status' => 0, 'msg' => '更新失败', 'result' => '']);
        }
    }
    /**
     * 删除
     */
    public function delete()
    {
        $shop_id = input('shop_id/d');
        if(empty($shop_id)){
            $this->ajaxReturn(['status' => 0, 'msg' => '参数错误']);
        }
        $Shop = new \app\common\model\Shop();
        $shop = $Shop->where(['shop_id'=>$shop_id])->find();
        if(empty($shop)){
            $this->ajaxReturn(['status' => 0, 'msg' => '非法操作', 'result' => '']);
        }
        $row = $shop->save(['deleted'=>1]);
        if($row !== false){
            $this->ajaxReturn(['status' => 1, 'msg' => '删除成功', 'result' => '']);
        }else{
            $this->ajaxReturn(['status' => 0, 'msg' => '删除失败', 'result' => '']);
        }
    }

    public function shopImageDel()
    {
        $path = input('filename','');
        Db::name('goods_images')->where("image_url",$path)->delete();
    }
}
