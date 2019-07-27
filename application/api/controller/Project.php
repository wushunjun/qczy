<?php
namespace app\api\controller;

use think\image\Exception;
use think\Page;
use think\Db;
use app\api\logic\PaymentLogic;

class Project extends Apibase {
    /**
     * 项目详情
     * @param int project_id 项目id
     */
    public function info(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'project_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $info = model('project')->where(['is_del'=>0,'project_status'=>1,'project_id'=>$param['project_id']])->find();
        if(!$info){
            $this->apiReturn('1002','项目不存在或已下架','');
        }
        $info->append(['shop']);
        $info['project_album'] = my_explode(',',$info['project_album']);
        $info['project_info'] = htmlspecialchars_decode($info['project_info']);
        $info['project_notes'] = htmlspecialchars_decode($info['project_notes']);
        $this->apiReturn('1001','成功',$info);
    }
    /**
     * 项目评论列表
     * @param int project_id 项目id
     * @param int page 页码
     * @param int pageSize 单页显示数量
     */
    public function project_comment_list(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'project_id' => 'integer|require',
            'page' => 'integer',
            'pageSize' => 'integer',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $list = model('project_comment')->where(['is_show'=>1,'project_id'=>$param['project_id']])->page(page($param))->select();
        $this->apiReturn('1001','成功',$list);
    }
    /**
     * 免费预约项目
     * @param int project_id 项目id
     * @param int po_id 项目订单id，从我的项目里面预约必传
     * @param int user_id 用户id
     * @param str pre_time 预约时间
     * @param int technician_id 技师id，非必传
     * @param str pre_person_num 人数
     * @param str pre_mobile 手机号
     * @param str pre_remark 备注
     */
    public function pre_project(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'project_id' => 'integer|require',
            'user_id' => 'integer|require',
            'pre_time' => 'require',
            'technician_id' => 'integer',
            'po_id' => 'integer',
            'pre_person_num' => 'integer|require',
            'pre_mobile' => 'mobile|require',
        ],[
            'pre_mobile.mobile' => '手机号格式错误'
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        Db::startTrans();
        try{
            if(!$param['po_id']){
                $res = model('project_order')->addOrder($param['user_id'],0,$param['project_id']);
                if($res['status'] == 1002){
                    throw new Exception($res['message']);
                }
                $project_order = $res['data'];
            }else{
                $project_order = Db::name('project_order')->where(['po_id'=>$param['po_id'],'po_status'=>1])->find();
                if(!$project_order){
                    throw new Exception('项目不存在或还未购买');
                }
                if($project_order['po_num']){
                    throw new Exception('剩余次数不足');
                }
                $update['po_num'] = $project_order['po_num'] - 1;
                if($update['po_num'] == 0){
                    $update['po_status'] = 2;
                }
                $res = Db::name('project_order')->where(['po_id'=>$param['po_id']])->update($update);
                if(!$res){
                    throw new Exception('网络连接失败，请联系平台客服');
                }
            }
            $pre_data = [
                'project_id' => $param['project_id'],
                'user_id' => $param['user_id'],
                'shop_id' => $project_order['shop_id'],
                'pre_time' => $param['pre_time'],
                'technician_id' => $param['technician_id'],
                'pre_person_num' => $param['pre_person_num'],
                'pre_mobile' => $param['pre_mobile'],
                'pre_remark' => $param['pre_remark'],
                'po_id' => $project_order['po_id'],
                'pre_add_time' => time(),
            ];
            $result = model('project_pre')->insertGetId($pre_data);
            if(!$result){
                throw new Exception('预约失败');
            }
            Db::commit();
            $this->apiReturn('1001','成功','');
        }catch (Exception $e){
            Db::rollback();
            $this->apiReturn('1002',$e->getMessage(),'');
        }
    }
    /**
     * 项目购买
     * @param int project_id 项目id
     * @param int user_id 用户id
     * @param int po_id 项目订单id，从我的项目里面购买时必传
     */
    public function buy_project(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'project_id' => 'integer|require',
            'user_id' => 'integer|require',
            'po_id' => 'integer',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $project = model('project')->find($param['project_id']);
        if(!$project){
            $this->apiReturn('1002','项目不存在或已下架','');
        }
        if($param['po_id']){
            $project_order = model('project_order')->where(['po_id'=>$param['po_id'],'po_status'=>0])->find();
            if(!$project_order){
                $this->apiReturn('1002','订单不存在','');
            }
        }
        $param['order_amount'] = $project['project_price'];
        $param['openid'] = $this->user['openid'];
        $logic = new PaymentLogic();
        $result = $logic->getProjectPayCode($param);
        if($result['status']){
            $this->apiReturn('1001','成功',$result['msg']);
        }else{
            $this->apiReturn('1002',$result['msg'],'');
        }
    }
    /**
     * 我的项目列表
     * @param int user_id 用户id
     * @param int page 页码
     * @param int pageSize 单页显示数量
     */
    public function my_project(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'user_id' => 'integer|require',
            'page' => 'integer',
            'pageSize' => 'integer',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $list = model('project_order')->where(['user_id'=>$param['user_id']])->page(page($param))->order('po_id desc')->select();
        $this->apiReturn('1001','成功',$list);
    }
    /**
     * 我的项目详情
     * @param int project_id 项目id
     * @param int po_id 项目订单id
     */
    public function my_project_info(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'po_id' => 'integer',
            'project_id' => 'integer',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $info = model('project_order')->where(['po_id'=>$param['po_id']])->find();
        if(!$info){
            $this->apiReturn('1002','项目不存在','');
        }
        $info->append(['project_pre','shop']);
        $this->apiReturn('1001','成功',$info);
    }
}