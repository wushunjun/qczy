<?php

namespace app\admin\validate;

use think\Validate;

/**
 * Description of Article
 *
 * @author Administrator
 */
class Shop extends Validate
{
    //验证规则
    protected $rule = [
        'shop_id'     => 'require|integer|gt:0',
        'shop_name'     => 'require|checkEmpty',
        'shop_phone'     => 'require|mobile',
        'work_time'     => 'require',
        'shop_project'     => 'require',
        'province_id'     => 'require|integer|gt:0',
        'city_id'     => 'require|integer|gt:0',
        'district_id'     => 'require|integer|gt:0',
        'shop_address'     => 'require',
        'longitude'     => 'require',
        'shop_img'     => 'require',
    ];

    //错误消息
    protected $message = [
        'shop_id'    => '非法操作',
        'shop_name'    => '店铺名称不能为空',
        'shop_phone.require'    => '联系电话不能为空',
        'shop_phone.mobile'    => '手机号格式错误',
        'work_time'    => '营业时间不能为空',
        'shop_project'    => '主营项目不能为空',
        'province_id'    => '省必须选择',
        'city_id'    => '市必须选择',
        'district_id'    => '区必须选择',
        'shop_address'    => '详细地址不能为空',
        'longitude'    => '请在地图上点击获取店铺精确定位',
        'shop_img'    => '请上传店铺主图',
    ];

    //验证场景
    protected $scene = [
        'add'  => ['shop_name','shop_phone','work_time','shop_project','province_id','city_id','district_id','shop_address','longitude','shop_img'],
        'del'  => ['shop_id']
    ];

    protected function checkEmpty($value)
    {
        if (is_string($value)) {
            $value = trim($value);
        }
        if (empty($value)) {
            return false;
        }
        return true;
    }

}
