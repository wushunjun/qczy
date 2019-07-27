<?php

namespace app\admin\validate;

use think\Validate;

/**
 * Description of Article
 *
 * @author Administrator
 */
class Luck extends Validate
{
    //验证规则
    protected $rule = [
        'luck_id'     => 'require|integer|gt:0',
        'lt_id'     => 'require|integer|gt:0',
        'luck_name'     => 'require',
        'luck_img'     => 'require',
    ];

    //错误消息
    protected $message = [
        'luck_id'    => '非法操作',
        'lt_id'    => '所属分类必须选择',
        'luck_name'    => '素材名称不能为空',
        'luck_img'    => '请上传素材图片',
    ];

    //验证场景
    protected $scene = [
        'add'  => ['lt_id','luck_name','luck_img'],
        'edit'  => ['luck_id','lt_id','luck_name','luck_img'],
        'del'  => ['del_id']
    ];


}
