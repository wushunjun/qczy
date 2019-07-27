<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * 采用最新Thinkphp5助手函数特性实现单字母函数M D U等简写方式
 * ============================================================================
 * @author lhb
 */

namespace app\common\logic\wechat;

/**
 * 微信平台的错误吗
 */
class WxCode 
{
    static public $map = [
        -1 => '系统繁忙',

        40001 => '不合法的调用凭证',
        40002 => '不合法的grant_type',
        40003 => '不合法的OpenID',
        40004 => '不合法的媒体文件类型',
        40007 => '不合法的media_id',
        40008 => '不合法的message_type',
        40009 => '不合法的图片大小',
        40010 => '不合法的语音大小',
        40011 => '不合法的视频大小',
        40012 => '不合法的缩略图大小',
        40013 => '不合法的AppID',
        40014 => '不合法的access_token',
        40015 => '不合法的菜单类型',
        40016 => '不合法的菜单按钮个数',
        40017 => '不合法的按钮类型',
        40018 => '不合法的按钮名称长度',
        40019 => '不合法的按钮KEY长度',
        40020 => '不合法的url长度',
        40023 => '不合法的子菜单按钮个数',
        40024 => '不合法的子菜单类型',
        40025 => '不合法的子菜单按钮名称长度',
        40026 => '不合法的子菜单按钮KEY长度',
        40027 => '不合法的子菜单按钮url长度',
        40029 => '不合法或已过期的code',
        40030 => '不合法的refresh_token',
        40036 => '不合法的template_id长度',
        40037 => '不合法的template_id',
        40039 => '不合法的url长度',
        40048 => '不合法的url域名',
        40054 => '不合法的子菜单按钮url域名',
        40055 => '不合法的菜单按钮url域名',
        40066 => '不合法的url',
        40164 => '服务器IP没有在白名单里',
        41001 => '缺失access_token参数',
        41002 => '缺失appid参数',
        41003 => '缺失refresh_token参数',
        41004 => '缺失secret参数',
        41005 => '缺失二进制媒体文件',
        41006 => '缺失media_id参数',
        41007 => '缺失子菜单数据',
        41008 => '缺失code参数',
        41009 => '缺失openid参数',
        41010 => '缺失url参数',
        42001 => 'access_token超时',
        42002 => 'refresh_token超时',
        42003 => 'code超时',
        43001 => '需要使用GET方法请求',
        43002 => '需要使用POST方法请求',
        43003 => '需要使用HTTPS',
        43004 => '需要订阅关系',
        44001 => '空白的二进制数据',
        44002 => '空白的POST数据',
        44003 => '空白的news数据',
        44004 => '空白的内容',
        44005 => '空白的列表',
        45001 => '二进制文件超过限制',
        45002 => 'content参数超过限制',
        45003 => 'title参数超过限制',
        45004 => 'description参数超过限制',
        45005 => 'url参数长度超过限制',
        45006 => 'picurl参数超过限制',
        45007 => '播放时间超过限制（语音为60s最大）',
        45008 => 'article参数超过限制',
        45009 => '接口调动频率超过限制',
        45010 => '建立菜单被限制',
        45011 => '频率限制',
        45012 => '模板大小超过限制',
        45015 => '响应超时或者已取消关注',
        45016 => '不能修改默认组',
        45017 => '修改组名过长',
        45018 => '组数量过多',
        45027 => '模板与所在行业冲突',
        47001 => '数据格式错误',
        50001 => '接口未授权',

        61004 => '服务器地址尚未登记',
        61005 => '组件ticket已过期',

        85001 => '微信号不存在或微信号设置为不可搜索',
        85002 => '小程序绑定的体验者数量达到上限',
        85003 => '微信号绑定的小程序体验者达到上限',
        85004 => '微信号已经绑定',
        85006 => '标签格式错误',
        85007 => '页面路径错误',
        85008 => '类目填写错误',
        85009 => '已经有正在审核的版本',
        85010 => 'item_list有项目为空',
        85011 => '标题填写错误',
        85012 => '无效的审核id',
        85013 => '无效的自定义配置',
        85014 => '无效的模版编号',
        85019 => '没有审核版本',
        85020 => '审核状态未满足发布',
        85021 => '状态不可变',
        85022 => 'action非法',
        85023 => '审核列表填写的项目数不在1-5以内',
        86000 => '不是由第三方代小程序进行调用',
        86001 => '不存在第三方的已经提交的代码',
        86002 => '小程序还未设置昵称、头像、简介。请先设置完后再重新提交',

        89000 => '该公众号/小程序已经绑定了开放平台帐号',
        89001 => '授权者与开放平台帐号主体不相同',
        89002 => '该公众号/小程序未绑定微信开放平台帐号',
        89003 => '该开放平台帐号并非通过api创建，不允许操作',
        89004 => '该开放平台帐号所绑定的公众号/小程序已达上限（100个）',
    ];

    /**
     * 获取错误码对应说明，没有返回false
     * @param $code
     * @return bool|mixed
     */
    static public function getItem($code)
    {
        if (key_exists($code, self::$map)) {
            return self::$map[$code];
        }
        return false;
    }
}

