<?php

namespace app\admin\validate;

use think\Validate;

/**
 * Description of Article
 *
 * @author Administrator
 */
class LuckComment extends Validate
{
    //验证规则
    protected $rule = [
        'lc_id'     => 'integer|require',
        'lc_nickname'     => 'require',
        'lc_content'     => 'require',
    ];

    //错误消息
    protected $message = [
        'lc_nickname'    => '昵称不能为空',
        'lc_content'    => '内容不能为空',
    ];

    //验证场景
    protected $scene = [
        'add'  => ['lc_nickname','lc_content'],
        'del'  => ['lc_id']
    ];


}
