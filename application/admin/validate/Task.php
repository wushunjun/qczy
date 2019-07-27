<?php

namespace app\admin\validate;

use think\Validate;

/**
 * Description of Article
 *
 * @author Administrator
 */
class Task extends Validate
{
    //验证规则
    protected $rule = [
        'task_id'     => 'require|integer|gt:0',
        'user_id'     => 'require|integer|gt:0',
        'task_name'     => 'require',
        'task_desc'     => 'require',
        'task_icon'     => 'require',
        'task_img'     => 'require',
        'task_bg_img'     => 'require',
        'task_days'     => 'integer|gt:0|require',
        'task_start_time'     => 'require',
        'tr_id'     => 'array|require',
    ];

    //错误消息
    protected $message = [
        'task_id'    => '非法操作',
        'user_id'    => '非法操作',
        'task_name'    => '任务名称不能为空',
        'task_desc'    => '任务描述不能为空',
        'task_icon'    => '请上传任务图标',
        'task_img'    => '请上传任务列表图',
        'task_bg_img'    => '请上传任务背景图',
        'task_days'    => '天数格式错误',
        'task_start_time'    => '开始时间不能为空',
        'tr_id'    => '请选择打卡项目',
    ];

    //验证场景
    protected $scene = [
        'add'  => ['user_id','task_name','task_desc','task_icon','task_bg_img','task_img','task_days','task_bg_img','task_start_time'],
        'edit'  => ['task_id','user_id','task_name','task_desc','task_icon','task_bg_img','task_img','task_days','task_bg_img','task_start_time'],
        'del'  => ['del_id']
    ];

}
