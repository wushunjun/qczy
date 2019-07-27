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

use think\Model;

class Task extends Model
{
    public function card(){
        return $this->hasMany('card','task_id','task_id');
    }
    public function getTaskStatusAttr($value,$data){
        $time = time();
        $status = 0;
        if($data['task_start_time'] > $time){
            $status = 0;//待打卡
        }elseif($data['task_start_time'] <= $time && $data['task_end_time'] > $time){
            $status = 1;//打卡中
        }elseif($data['task_end_time'] < $time){
            if($data['gift_id'] == 0){
                $status = 2;//待抽奖
            }else{
                $status = 3;//已完成
            }
        }
        return $status;
    }
    public function getDaysAttr($value,$data){
        $res = ceil((time() - $data['task_start_time']) / (3600 * 24));
        if($res <= 0){
            $res = '任务未开始';
        }elseif($res <= $data['task_days']){
            $res = '第'.$this->numberToChinese($res).'天';
        }else{
            $res = '任务已完成';
        }
        return $res;
    }
    public function numberToChinese($number){
        $number = intval($number);
        $bit = array("零", "一", "二", "三", "四", "五", "六", "七", "八", "九", "十");
        //各位数
        if ($number <= 10) {
            return $bit[$number];
        }
        //十位数
        if($number < 100){
            $array = str_split($number);
            if($array[0] < 2){
                return $bit[10].$bit[$array[1]];
            }else{
                if($array[1] == 0){
                    return $bit[$array[0]].$bit[10];
                }else{
                    return $bit[$array[0]].$bit[10].$bit[$array[1]];
                }
            }
        }
        //百位数
        if($number < 1000){
            $array = str_split($number);
            if($array[1] == 0 && $array[2] == 0){
                return $bit[$array[0]]."百";
            }elseif($array[1] == 0 && $array[2] != 0){
                return $bit[$array[0]]."百".$bit[$array[1]].$bit[$array[2]];
            }elseif($array[1] != 0 && $array[2] == 0 ){
                return $bit[$array[0]]."百".$bit[$array[1]].$bit[10];
            }else{
                return $bit[$array[0]]."百".$bit[$array[1]].$bit[10].$bit[$array[2]];
            }
        }
        //千位数
        if($number < 10000){
            $array = str_split($number);
            if($array[1] == 0 && $array[2] == 0 && $array[3] == 0){
                return $bit[$array[0]]."千";
            }elseif($array[1] == 0 && $array[2] != 0 && $array[3] != 0){
                return $bit[$array[0]]."千".$bit[$array[1]].$bit[$array[2]].$bit[10].$bit[$array[3]];
            }elseif($array[1] == 0 && $array[2] == 0 && $array[3] != 0){
                return $bit[$array[0]]."千".$bit[$array[1]].$bit[$array[3]];
            }elseif($array[1] == 0 && $array[2] != 0 && $array[3] == 0){
                return $bit[$array[0]]."千".$bit[$array[1]].$bit[$array[2]].$bit[10];
            }elseif($array[1] != 0 && $array[2] == 0 && $array[3] == 0){
                return $bit[$array[0]]."千".$bit[$array[1]]."百";
            }elseif($array[1] != 0 && $array[2] != 0 && $array[3] == 0){
                return $bit[$array[0]]."千".$bit[$array[1]]."百".$bit[$array[2]].$bit[10];
            }elseif($array[1] != 0 && $array[2] == 0 && $array[3] != 0){
                return $bit[$array[0]]."千".$bit[$array[1]]."百".$bit[$array[2]].$bit[$array[3]];
            }else{
                return $bit[$array[0]]."千".$bit[$array[1]]."百".$bit[$array[2]].$bit[10].$bit[$array[3]];
            }
        }
        return $number;
    }
}
