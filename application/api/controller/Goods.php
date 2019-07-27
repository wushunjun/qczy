<?php
namespace app\api\controller;

use think\Page;
use think\Db;

class Goods extends Apibase {
    /**
     * 商品分类列表
     */
    public function category_list(){
        $all = ['id'=>0,'name'=>'全部'];
        $list = M('goods_category')->cache(true)->where(['is_show' => 1])->order('sort_order')->select();//所有分类
        array_unshift($list,$all);
        $this->apiReturn('1001','成功',$list);
    }
    /**
     * 商品列表
     * @param int id 分类id，非必传
     * @param int page 页码
     * @param int pageSize 单页显示数量
     */
    public function goods_list(){
        $param = request()->param();
        $where = ['is_on_sale'=>1];
        if($param['id']){
            $where['cat_id'] = $param['id'];
        }
        $list = M('goods')->where($where)->order('goods_id desc')->page(page($param))->select();
        $this->apiReturn('1001','成功',$list);
    }
    /**
     * 商品详情
     * @param int goods_id 商品id
     */
    public function goods_info(){
        $goods_id = I("goods_id/d");
        $goodsModel = new \app\common\model\Goods();
        $goods = $goodsModel::get($goods_id);
        if (empty($goods) || ($goods['is_on_sale'] == 0)) {
            $this->apiReturn('1002','此商品不存在或者已下架','');
        }
        $goods = $goods->append(['spec','goods_images','spec_goods_price'])->toArray();
        if($goods['spec_goods_price']){
            foreach($goods['spec_goods_price'] as $k=>$v){
                if($k == 0){
                    $goods['spec_goods_price'][0]['check'] = 1;
                }else{
                    $goods['spec_goods_price'][$k]['check'] = 0;
                }
            }
            foreach($goods['spec'] as $k=>$v){
                foreach($v['spec_item'] as $key=>$val){
                    if($key == 0){
                        $goods['spec'][$k]['spec_item'][$key]['check'] = 1;
                    }else{
                        $goods['spec'][$k]['spec_item'][$key]['check'] = 0;
                    }
                }
            }
        }
        $goods['goods_content'] = htmlspecialchars_decode($goods['goods_content']);
        $this->apiReturn('1001','成功',$goods);
    }
    /**
     * 评论列表
     * @param int goods_id 商品id
     * @param int page 页码
     * @param int pageSize 单页显示数量
     */
    public function comment_list(){
        $param = request()->param();
        $goods_id = I("goods_id/d", 0);
        $where = array(
            'goods_id' => $goods_id, 'parent_id' => 0, 'is_show' => 1
        );
        $list = M('Comment')
            ->alias('c')
            ->join('__USERS__ u', 'u.user_id = c.user_id', 'LEFT')
            ->where($where)->field('c.*,ceil((deliver_rank + goods_rank + service_rank) / 3) as goods_rank ,u.head_pic')
            ->order("add_time desc")
            ->page(page($param))
            ->select();
        $this->apiReturn('1001','成功',$list);
    }
}