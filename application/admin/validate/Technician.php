<?php

namespace app\admin\validate;

use think\Validate;

/**
 * Description of Article
 *
 * @author Administrator
 */
class Technician extends Validate
{
    //验证规则
    protected $rule = [
        'technician_id'     => 'require|integer|gt:0',
        'shop_id'     => 'require|integer|gt:0',
        'technician_name'     => 'require',
        'technician_icon'     => 'require',
        'technician_img'     => 'require',
        'technician_position'     => 'require',
        'technician_years'     => 'require|integer|gt:0',
        'technician_major'     => 'require',
    ];

    //错误消息
    protected $message = [
        'technician_id'    => '非法操作',
        'shop_id'    => '所属店铺必须选择',
        'technician_name'    => '技师名称不能为空',
        'technician_icon'    => '请上传技师头像',
        'technician_img'    => '请上传技师背景图',
        'technician_position'    => '技师职位不能为空',
        'technician_years.require'    => '从业年限不能为空',
        'technician_years.integer'    => '数据格式有误',
        'technician_major'    => '擅长工作不能为空',
    ];

    //验证场景
    protected $scene = [
        'add'  => ['shop_id','technician_name','technician_icon','technician_img','technician_position','technician_years','technician_major'],
        'edit'  => ['technician_id','shop_id','technician_name','technician_icon','technician_img','technician_position','technician_years','technician_major'],
        'del'  => ['del_id']
    ];


}
