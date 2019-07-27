<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 采用最新Thinkphp5助手函数特性实现单字母函数M D U等简写方式
 * ============================================================================
 * Author: lhb
 * Date: 2017-05-15
 */

namespace app\api\logic;

use think\Model;
use think\Db;

/**
 * 支付逻辑类
 */
class PaymentLogic extends Model
{
    public $payment; //  具体的支付类

    /**
     * 析构流函数
     */
    public function __construct()
    {
        parent::__construct();
        include_once "plugins/payment/weixin/weixin.class.php";
        $this->payment = new \weixin();
    }
    /**
     * 获取礼物支付码
     * @return array
     */
    public function getGiftPayCode($param)
    {
        $order = [
            'order_sn' => 'rwd' . build_order_sn(),
            'order_attach' => $param['user_id'] . '|' . $param['reward_type'] . '|' . $param['reward_obj_id'] . '|' . $param['gift_id'] . '|' .$param['reward_num'],
            'order_amount' => $param['order_amount'],
            'openid' => $param['openid'],
        ];
        $result = $this->payment->getJSAPI($order);
        return $result;
    }
    /**
     * 获取商品支付码
     * @return array
     */
    public function getGoodsPayCode($order)
    {
        $order['order_attach'] = '';
        $result = $this->payment->getJSAPI($order);
        return $result;
    }
    /**
     * 获取项目支付码
     * @return array
     */
    public function getProjectPayCode($param)
    {
        $order = [
            'order_sn' => 'po' . build_order_sn(),
            'order_attach' => $param['user_id'] . '|' . $param['project_id'] . ($param['po_id'] ? '|' . $param['po_id'] : ''),
            'order_amount' => $param['order_amount'],
            'openid' => $param['openid'],
        ];
        $result = $this->payment->getJSAPI($order);
        return $result;
    }
}