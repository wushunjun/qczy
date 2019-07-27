<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * Author: IT宇宙人
 * Date: 2015-09-09
 */
namespace app\common\model;

use think\Db;
use think\Model;

class ProjectOrder extends Model
{
    public function shop(){
        return $this->hasOne('Shop', 'shop_id', 'shop_id');
    }
    public function projectPre(){
        return $this->hasMany('ProjectPre', 'po_id', 'po_id');
    }
    /**
     * 添加项目订单
     */
    public function addOrder($user_id,$status,$project_id){
        $project = Db::name('project')->find($project_id);
        if(!$project){
            return ['status'=>1002,'message'=>'项目不存在','data'=>''];
        }
        if(!in_array($status,[0,1])){
            return ['status'=>1002,'message'=>'参数错误','data'=>''];
        }
        $order_data = [
            'user_id' => $user_id,
            'shop_id' => $project['shop_id'],
            'project_id' => $project['project_id'],
            'po_sn' => build_order_sn(),
            'project_name' => $project['project_name'],
            'po_num' => $project['project_num'],
            'po_buy_num' => $project['project_num'],
            'po_img' => $project['project_img'],
            'po_price' => $project['project_price'],
            'po_add_time' => time(),
            'po_status' => $status,
        ];
        $res = $this->insertGetId($order_data);
        if(!$res){
            return ['status'=>1002,'message'=>$res,'data'=>''];
        }
        return ['status'=>1001,'message'=>'成功','data'=>['po_id'=>$res,'shop_id'=>$project['shop_id']]];
    }

}
