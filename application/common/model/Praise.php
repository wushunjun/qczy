<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\common\model;

use think\Db;
use think\Model;

class Praise extends Model
{
    /**
     * 判断用户是否已点赞/报名
     */
    public function is_praise($param){
        $result = $this->where(['dynamic_id'=>$param['dynamic_id'],'user_id'=>$param['user_id']])->find();
        $result = $result ? 1 : 0;
        return $result;
    }
}
