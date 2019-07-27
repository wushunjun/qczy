<?php
namespace app\common\model;

use think\Db;
use think\Model;
class Shake extends Model{
    /**
     * 获取解签码
     * @return string
     */
    public function getCode(){
        $code = '';
        $a = true;
        //保证唯一性
        while($a){
            $code = random_string();
            $res = $this->where('shake_code',$code)->find();
            if(!$res){
                $a = false;
            }
        }
        return $code;
    }

    /**
     * 添加摇签
     * @param $user_id
     * @return array
     */
    public function addShake($user_id){
        $start_time = strtotime(date('Y-m-d'));
        $end_time = $start_time + 3600 * 24 -1;
        $res = Db::name('shake')->where(['user_id'=>$user_id,'shake_add_time'=>['between',"$start_time,$end_time"]])->find();
        if($res){
            return ['status'=>'1002','message'=>'童鞋，每天只能摇一次哟！','data'=>''];
        }
        $code = $this->getCode();
        $data = [
            'user_id' => $user_id,
            'shake_code' => $code,
            'shake_add_time' => time(),
        ];
        $result = $this->insert($data);
        if($result){
            return ['status'=>'1001','message'=>'成功','data'=>$code];
        }else{
            return ['status'=>'1002','message'=>'失败','data'=>''];
        }
    }
    /**
     * 获取列表
     * @param $param
     */
    public function getList($param){
        return $this->where(['user_id'=>$param['user_id']])->order('shake_id desc')->page(page($param))->select();
    }
}