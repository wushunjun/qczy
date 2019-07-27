<?php
namespace app\api\controller;

use think\Page;
use think\Db;

class Shop extends Apibase {
    /**
     * 店铺列表
     * @param int district_id 地区id，非必传
     * @param int distance 距离范围，1km传1,2km传2，非必传
     * @param int sort 排序，0默认，1评价最高，2距离最近
     * @param string user_lng 经度
     * @param string user_lat 纬度
     * @param int page 页码
     * @param int pageSize 单页显示数量
     */
    public function index(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'district_id' => 'integer',
            'user_lat' => 'number|require',
            'user_lng' => 'number|require',
            'distance' => 'integer',
            'sort' => 'in:0,1,2|require',
            'page' => 'integer',
            'pageSize' => 'integer',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        switch($param['sort']){
            case 0:
                $sort = 'shop_id desc';
                break;
            case 1:
                $sort = 'shop_star desc';
                break;
            case 2:
                $sort = 'distance desc';
                break;
            default:
                $sort = 'shop_id desc';
                break;
        }
        $where = ['deleted'=>0,'shop_status'=>1];
        if($param['distance']){
            $res = getSquarePoint($param['user_lng'],$param['user_lat'],$param['distance']);
            $where['latitude'] = ['between',$res['lat_min'] . "," . $res['lat_max']];
            $where['longitude'] = ['between',$res['lng_min'] . "," . $res['lng_max']];
        }
        if($param['district_id']){
            $where['district_id'] = $param['district_id'];
        }
        $list = model('shop')
            ->relation('project,region')
            ->where($where)
            ->page(page($param))
            ->field('*,round(6378.138 * 2 * asin(sqrt(pow(sin((' . $param['user_lat'] . ' * pi() / 180 - latitude * pi() / 180) / 2),2) + cos(' . $param['user_lat'] . ' * pi() / 180) * cos(latitude * pi() / 180) * pow(sin((' . $param['user_lng'] . ' * pi() / 180 - longitude * pi() / 180) / 2),2))) * 1000,-1) distance')
            ->order($sort)
            ->select();
        $this->apiReturn('1001','成功',$list);
    }
    /**
     * 区县列表
     */
    public function area_list(){
        $list = model('region')->where(['level'=>3,'parent_id'=>31930])->select();
        $this->apiReturn('1001','成功',$list);
    }
    /**
     * 店铺详情
     * @param int shop_id 店铺id
     */
    public function info(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'shop_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $info = model('shop')->relation('shopImages,project,technician')->where(['deleted'=>0,'shop_status'=>1,'shop_id'=>$param['shop_id']])->find();
        if(!$info){
            $this->apiReturn('1002','店铺不存在或已歇业','');
        }
        $info['shop_desc'] = htmlspecialchars_decode($info['shop_desc']);
        $this->apiReturn('1001','成功',$info);
    }
    /**
     * 店铺评论列表
     * @param int shop_id 店铺id
     * @param int page 页码
     * @param int pageSize 单页显示数量
     */
    public function shop_comment_list(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'shop_id' => 'integer|require',
            'page' => 'integer',
            'pageSize' => 'integer',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $list = model('project_comment')->where(['is_show'=>1,'shop_id'=>$param['shop_id']])->page(page($param))->select();
        $this->apiReturn('1001','成功',$list);
    }
    /**
     * 技师列表
     * @param int shop_id 店铺id
     */
    public function technician_list(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'shop_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $list = model('technician')->where(['is_del'=>0,'technician_status'=>1,'shop_id'=>$param['shop_id']])->select();
        $this->apiReturn('1001','成功',$list);
    }
    /**
     * 技师详情
     * @param int technician_id 技师id
     */
    public function technician_info(){
        $param = request()->param();
        $validate = $this->validate($param, [
            'technician_id' => 'integer|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $info = model('technician')->where(['is_del'=>0,'technician_status'=>1,'technician_id'=>$param['technician_id']])->find();
        if(!$info){
            $this->apiReturn('1002','技师不存在或不在岗','');
        }
        $info['technician_album'] = explode(',',$info['technician_album']);
        $this->apiReturn('1001','成功',$info);
    }
}