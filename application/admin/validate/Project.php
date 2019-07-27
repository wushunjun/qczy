<?php

namespace app\admin\validate;

use think\Validate;

/**
 * Description of Article
 *
 * @author Administrator
 */
class Project extends Validate
{
    //验证规则
    protected $rule = [
        'project_id'     => 'require|integer|gt:0',
        'shop_id'     => 'require|integer|gt:0',
        'project_name'     => 'require',
        'project_price'     => 'require|number',
        'project_shop_price'     => 'require|number',
        'project_sale_num'     => 'integer',
        'project_info'     => 'require',
        'project_notes'     => 'require',
        'project_img'     => 'require',
    ];

    //错误消息
    protected $message = [
        'project_id'    => '非法操作',
        'shop_id'    => '所属店铺必须选择',
        'project_name'    => '项目名称不能为空',
        'project_price.require'    => '项目售价不能为空',
        'project_price.number'    => '数据格式错误',
        'project_shop_price.require'    => '店铺售价不能为空',
        'project_shop_price.number'    => '数据格式错误',
        'project_sale_num.integer'    => '请输入正整数',
        'project_info'    => '项目详情不能为空',
        'project_notes'    => '购买须知不能为空',
        'project_img'    => '请上传店铺主图',
    ];

    //验证场景
    protected $scene = [
        'add'  => ['shop_id','project_name','project_price','project_shop_price','project_sale_num','project_info','project_notes','project_img'],
        'edit'  => ['project_id','shop_id','project_name','project_price','project_shop_price','project_sale_num','project_info','project_notes','project_img'],
        'del'  => ['project_id']
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
