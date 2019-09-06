<?php
namespace app\api\controller;

use think\Db;
use app\common\model\Order as OrderModel;
use app\common\logic\OrderLogic;
use app\common\logic\UsersLogic;

class Order extends Apibase
{
    /**
     * 订单列表
     * @param int user_id 用户id
     * @param int type 订单类型，0全部，1待付款，2待发货，3待收货，4待评价，5已完成
     * @param int page 页码
     * @param int pageSize 单页显示数量
     * 注：返回值中，order_status_detail为状态可直接取用，order_button为可执行的按钮
     */
    public function order_list()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'type' => 'in:0,1,2,3,4,5|require',
            'page' => 'integer',
            'pageSize' => 'integer',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $order = new OrderModel();
        $where = [
            'user_id' => $param['user_id'],
            'deleted' => 0,
        ];
        $type_arr = [
            '0',
            'WAITPAY',
            'WAITSEND',
            'WAITRECEIVE',
            'WAITCCOMMENT',
            'FINISH',
        ];
        $type = $type_arr[$param['type']];
        $list = $order->where($where)
            ->where(function ($query) use ($type) {
                if ($type) {
                    $query->where('1=1' . config($type));
                }
            })
            ->page(page($param))->order('order_id desc')->select();
        if ($list) {
            $list = collection($list)->append(['order_status_detail', 'order_button','order_goods'])->toArray();
        }
        foreach($list as $k=>$v){
            foreach($v['order_goods'] as $key=>$val){
                $list[$k]['order_goods'][$key]['goods_img'] = goods_thum_images($val['goods_id'],200,200);
            }
        }
        $this->apiReturn('1001', '成功', $list);
    }

    /**
     * 订单详情
     * @param int user_id 用户id
     * @param int order_id 订单id
     * 注：返回值中，order_status_detail为状态可直接取用，order_button为可执行的按钮
     * 评论按钮为order_goods里面的comment_btn
     */
    public function order_detail()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'order_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $Order = new OrderModel();
        $order = $Order->where(['user_id' => $param['user_id'], 'order_id' => $param['order_id']])->find();
        if (!$order) {
            $this->apiReturn('1002', '订单不存在', '');
        }
        $order = $order->append(['order_status_detail', 'order_button', 'order_goods','full_address','big_status'])->toArray();
        foreach($order['order_goods'] as $k=>$v){
            if(!$v['is_comment'] && $order['order_button']['comment_btn']){
                $order['order_goods'][$k]['comment_btn'] = 1;
            }else{
                $order['order_goods'][$k]['comment_btn'] = 0;
            }
            $order['order_goods'][$k]['goods_img'] = goods_thum_images($v['goods_id'],200,200);
        }
        unset($order['order_button']['comment_btn']);
        $order['pay_money'] = $order['total_amount'] - $order['user_money'] - $order['integral_money'];

        $this->apiReturn('1001', '成功', $order);
    }

    /**
     * 取消订单
     * @param int user_id 用户id
     * @param int order_id 订单id
     */
    public function cancel_order()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'order_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        //检查是否有积分，余额支付
        $logic = new OrderLogic();
        $data = $logic->cancel_order($param['user_id'], $param['order_id']);
        if ($data['status'] == 1) {
            $this->apiReturn('1001', '成功', '');
        } else {
            $this->apiReturn('1002', $data['msg'], '');
        }
    }

    /**
     * 确定收货
     * @param int user_id 用户id
     * @param int order_id 订单id
     */
    public function order_confirm()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'order_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $data = confirm_order($param['order_id'], $param['user_id']);
        if ($data['status'] != 1) {
            $this->apiReturn('1002', $data['msg'], '');
        } else {
            $this->apiReturn('1001', '成功', '');
        }
    }

    /**
     *添加评论
     * @param int user_id 用户id
     * @param int order_id 订单id
     * @param array img 图片
     * @param int goods_rank 分数，1-5分
     * @param int rec_id 订单商品id
     * @param int goods_id 订单商品id
     * @param string content 评论内容
     */
    public function add_comment()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'order_id' => 'integer|require',
            'img' => 'array',
            'goods_rank' => 'in:1,2,3,4,5|require',
            'rec_id' => 'integer|require',
            'goods_id' => 'integer|require',
            'content' => 'require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        if (!empty($param['img'])) {
            $param['img'] = implode(',',$param['img']);
        }
        $logic = new UsersLogic();
        $param['username'] = $this->user['nickname'];
        $param['add_time'] = time();
        $param['ip_address'] = request()->ip();

        //添加评论
        $row = $logic->add_comment($param);
        if ($row['status'] == 1) {
            $this->apiReturn('1001', '成功', '');
        } else {
            $this->apiReturn('1002', $row['msg'], '');
        }
    }
}