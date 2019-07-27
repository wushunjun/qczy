<?php

namespace app\admin\validate;

use think\Validate;

/**
 * Description of Article
 *
 * @author Administrator
 */
class TaskResource extends Validate
{
    //验证规则
    protected $rule = [
        'tr_id'     => 'require|integer|gt:0',
        'tr_name'     => 'require',
        'tr_img'     => 'require',
        'tr_gif'     => 'require',
    ];

    //错误消息
    protected $message = [
        'tr_id'    => '非法操作',
        'tr_name'    => '打卡名称不能为空',
        'tr_img'    => '请上传打卡图片',
        'tr_gif'    => '请上传打卡动图',
    ];

    //验证场景
    protected $scene = [
        'add'  => ['tr_name','tr_img','tr_gif'],
        'edit'  => ['tr_id','tr_name','tr_img','tr_gif'],
        'del'  => ['del_id']
    ];


}
