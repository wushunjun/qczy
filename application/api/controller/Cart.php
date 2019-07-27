<?php
namespace app\api\controller;

use think\Page;
use think\Db;
use app\common\logic\CartLogic;
use app\common\model\Order as OrderModel;
use app\common\util\TpshopException;
use think\Loader;
use app\common\logic\Pay;
use app\common\logic\PlaceOrder;
use app\api\logic\PaymentLogic;

class Cart extends Apibase {
    /**
     * 购物车列表
     * @param int user_id 用户id
     */
    public function index(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $cartLogic = new CartLogic();
        $cartLogic->setUserId($param['user_id']);
        $cartList = $cartLogic->getCartList();//用户购物车
        $cartLogic->AsyncUpdateCart($cartList);
        $select_cart_list = $cartLogic->getCartList(1);//获取选中购物车
        $cart_price_info = $cartLogic->getCartPriceInfo($select_cart_list);//计算选中购物车
        foreach($cartList as $k=>$v){
            $cartList[$k]['original_img'] = goods_thum_images($v['goods_id'], 400, 400,$v['item_id']);
        }
        $return['cart_list'] = $cartList;//拼接需要的数据
        $return['cart_price_info'] = $cart_price_info;
        $this->apiReturn('1001','成功',$return);
    }
    /**
     * 加入购物车
     * @param int user_id 用户id
     * @param int goods_id 商品id
     * @param int goods_num 商品数量
     * @param int item_id 商品规格id，非必传
     */
    function add()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'goods_id' => 'integer|require',
            'goods_num' => 'integer|require',
            'item_id' => 'integer',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $cartLogic = new CartLogic();
        $cartLogic->setUserId($param['user_id']);
        $cartLogic->setGoodsModel($param['goods_id']);
        $cartLogic->setSpecGoodsPriceById($param['item_id']);
        $cartLogic->setGoodsBuyNum($param['goods_num']);
        try {
            $cartLogic->addGoodsToCart();
            $this->apiReturn('1001','成功','');
        } catch (TpshopException $t) {
            $error = $t->getErrorArr();
            $this->apiReturn('1002',$error['msg'],'');
        }
    }
    /**
     * 购物车加减
     * @param int id 购物车id
     * @param int goods_num 商品数量
     */
    public function change_num(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'id' => 'integer|require',
            'goods_num' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $cartLogic = new CartLogic();
        $result = $cartLogic->changeNum($param['id'], $param['goods_num']);
        $result['status'] = $result['status'] ? '1001' : '1002';
        $this->apiReturn($result['status'],$result['msg'],'');
    }
    /**
     * 改变选中状态
     * @param array cart 购物车数据
     * @param int user_id 用户id
     */
    public function change_selected(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'cart' => 'array|require',
            'user_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $cart = input('cart/a', []);
        $cartLogic = new CartLogic();
        $cartLogic->setUserId($this->user['user_id']);
        $cartLogic->AsyncUpdateCart($cart);
        $select_cart_list = $cartLogic->getCartList(1);//获取选中购物车
        $cart_price_info = $cartLogic->getCartPriceInfo($select_cart_list);//计算选中购物车
        $user_cart_list = $cartLogic->getCartList();//获取用户购物车
        $return['cart_list'] = $cartLogic->cartListToArray($user_cart_list);//拼接需要的数据
        $return['cart_price_info'] = $cart_price_info;
        $this->apiReturn('1001','成功',$return);
    }
    /**
     * 删除购物车商品
     * @param array cart_ids 购物车id，注意这里是传数组哟
     * @param int user_id 用户id
     */
    public function delete(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'cart_ids' => 'array|require',
            'user_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $cartLogic = new CartLogic();
        $cartLogic->setUserId($param['user_id']);
        $result = $cartLogic->delete($param['cart_ids']);
        if($result !== false){
            $this->apiReturn('1001','成功','');
        }else{
            $this->apiReturn('1002','失败','');
        }
    }
    /**
     * 购物车数量
     * @param int user_id 用户id
     */
    public function cart_num(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $cartLogic = new CartLogic();
        $cartLogic->setUserId($param['user_id']);
        $result = $cartLogic->getUserCartOrderCount();
        $this->apiReturn('1001','成功',$result);
    }
    /**
     * 确认订单
     * @param int user_id 用户id
     * @param int goods_id 商品id，立即购买时必传
     * @param int goods_num 商品数量，立即购买时必传
     * @param int item_id 商品规格id，立即购买时且有规格必传
     * @param int action 行为，0表示从购物车下单，1表立即购买
     */
    public function confirm_order(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'action' => 'in:0,1|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $cartLogic = new CartLogic();
        $cartLogic->setUserId($param['user_id']);
        if($param['action'] == 1){
            $validate = $this->validate($param, [
                'goods_id' => 'integer|require',
                'goods_num' => 'integer|require',
                'item_id' => 'integer',
            ]);
            if ($validate !== true)
                $this->paramError($validate);
            $cartLogic->setGoodsModel($param['goods_id']);
            $cartLogic->setSpecGoodsPriceById($param['item_id']);
            $cartLogic->setGoodsBuyNum($param['goods_num']);
            $buyGoods = [];
            try{
                $buyGoods = $cartLogic->buyNow();
            }catch (TpshopException $t){
                $error = $t->getErrorArr();
                $this->apiReturn('1002',$error['msg'],'');
            }
            $cartList['cartList'][0] = $buyGoods;
        }else{
            if ($cartLogic->getUserCartOrderCount() == 0){
                $this->apiReturn('1002','你的购物车没有选中商品','');
            }
            $cartList['cartList'] = $cartLogic->getCartList(1); // 获取用户选中的购物车商品
            $cartList['cartList'] = $cartLogic->getCombination($cartList['cartList']);  //找出搭配购副商品
        }
        foreach($cartList['cartList'] as $k=>$v){
            $cartList['cartList'][$k]['original_img'] = goods_thum_images($v['goods_id'],200,200);
        }
        $cartPriceInfo = $cartLogic->getCartPriceInfo($cartList['cartList']);  //初始化数据。商品总额/节约金额/商品总共数量
        $cartList = array_merge($cartList,$cartPriceInfo);
        $this->apiReturn('1001','成功',$cartList);
    }
    /**
     * 订单计算与提交订单
     * @param user_id 用户id
     * @param address_id 地址id
     * @param is_pay_points 是否使用积分，0不使用，1使用
     * @param is_user_money 是否使用余额，0不使用，1使用
     * @param user_note 用户留言
     * @param pay_pwd 支付密码，使用积分或余额时必传
     * @param goods_id 商品id，立即购买必传
     * @param item_id 商品规格id，立即购买的商品有规格必传
     * @param action 下单方式，0购物车下单，1立即购买
     * @param act 行为，0计算，1提交订单
     */
    public function order_handle(){
        $user_id = input("user_id/d", 0); //  用户id
        $address_id = input("address_id/d", 0); //  收货地址id
        $is_pay_points = input("is_pay_points/d", 0); //  是否使用积分
        $is_user_money = input("is_user_money/f", 0); //  是否使用余额
        $user_note = input("user_note/s", ''); // 用户留言
        $pay_pwd = input("pay_pwd/s", ''); // 支付密码
        $goods_id = input("goods_id/d"); // 商品id
        $goods_num = input("goods_num/d");// 商品数量
        $item_id = input("item_id/d"); // 商品规格id
        $action = input("action"); // 立即购买
        $data = input('request.');
        $cart_validate = Loader::validate('Cart');
        if (!$cart_validate->check($data)) {
            $error = $cart_validate->getError();
            $this->paramError($error);
        }
        $address = Db::name('user_address')->where("address_id", $address_id)->find();
        $cartLogic = new CartLogic();
        $pay = new Pay();
        try {
            $cartLogic->setUserId($user_id);
            if ($action == 1) {
                $cartLogic->setGoodsModel($goods_id);
                $cartLogic->setSpecGoodsPriceById($item_id);
                $cartLogic->setGoodsBuyNum($goods_num);
                $buyGoods = $cartLogic->buyNow();
                $cartList[0] = $buyGoods;
                $pay->payGoodsList($cartList);
            } else {
                $userCartList = $cartLogic->getCartList(1);
                $cartLogic->checkStockCartList($userCartList);
                $pay->payCart($userCartList);
            }
            $pay->setUserId($user_id)->delivery($address['district'])->useUserMoney($is_user_money)->usePayPoints($is_pay_points,false,'mobile');
            // 提交订单
            if ($_REQUEST['act'] == 1) {
                $placeOrder = new PlaceOrder($pay);
                $placeOrder->setUserAddress($address)->setUserNote($user_note)->setPayPsw($pay_pwd)->addNormalOrder();
                $cartLogic->clear();
                $order = $placeOrder->getOrder();
                $this->apiReturn('1001','提交订单成功',$order['order_id']);
            }
            $this->apiReturn('1001','计算成功',$pay->toArray());
        } catch (TpshopException $t) {
            $error = $t->getErrorArr();
            $this->apiReturn('1002',$error['msg'],'');
        }
    }

    /**
     * 订单支付
     * @param user_id 用户id
     * @param order_id 订单id
     */
    public function pay()
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
        $order['openid'] = $this->user['openid'];
        $logic = new PaymentLogic();
        $result = $logic->getGoodsPayCode($order);
        if($result['status']){
            $this->apiReturn('1001','成功',['order'=>$order,'pay_code'=>$result['msg']]);
        }else{
            $this->apiReturn('1002',$result['msg'],'');
        }
    }
}