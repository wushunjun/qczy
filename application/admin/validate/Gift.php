<?php

namespace app\admin\validate;

use think\Validate;

/**
 * Description of Article
 *
 * @author Administrator
 */
class Gift extends Validate
{
    //验证规则
    protected $rule = [
        'gift_id'     => 'require|integer|gt:0',
        'gift_name'     => 'require',
        'gift_img'     => 'require',
        'gift_price'     => 'require|number',
    ];

    //错误消息
    protected $message = [
        'gift_id'    => '非法操作',
        'gift_name'    => '礼物名称不能为空',
        'gift_img'    => '请上传礼物图片',
        'gift_position'    => '礼物职位不能为空',
        'gift_price.require'    => '礼物价格不能为空',
        'gift_price.number'    => '价格数据格式有误',
    ];

    //验证场景
    protected $scene = [
        'add'  => ['gift_name','gift_img','gift_price'],
        'edit'  => ['gift_id','gift_name','gift_img','gift_price'],
        'del'  => ['del_id']
    ];


}
