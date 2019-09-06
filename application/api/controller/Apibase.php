<?php
namespace app\api\controller;

use think\Controller;

class Apibase extends Controller
{
    public $user;
    public function _initialize()
    {
        /*解决跨域问题*/
        header('Access-Control-Allow-Origin:*');
        header("Content-type: text/html; charset=utf-8");
        //获取请求方式，接口只能post请求
        if (!$this->request->isPost()) {
            //$this->apiReturn(1004, '请求方式错误', '');
        }
        $param = $this->request->param();
        if($param['user_id']){
            $this->user = model('users')->find($param['user_id']);
        }
        $controller_name = request()->controller();
        $action_name = request()->action();
        //$this->check_sign();//进行签名验证
        $exclude = [//不需要token验证的接口，*代表控制器下所有方法均不需验证，一个控制器下如有多个action则用数组存储action
            'Login' => '*',
            'Index' => '*',
            'Reward' => '*',
            'User' => ['card_bar_code','card_qr_code'],
        ];
        //排除不需要token验证的接口$exclude[$controller_name] != $action_name
        if(!isset($exclude[$controller_name]) || ($exclude[$controller_name] != '*' && !in_array($action_name, $exclude[$controller_name]))){
            $this->check_token();//进行token验证
        }

    }

    //TODO 签名验证
    public function check_sign()
    {
        $param = request()->param();
        $validate = $this->validate($param, [
            'sign' => 'require'
        ], [
            'sign' => '签名不能为空'
        ]);
        if ($validate !== true)
            $this->apiReturn(3, $validate, '');
        $post_sign = $param['sign'];
        unset($param['sign']);
        $arr = array();
        foreach ($param as $key => $value) {
            $arr[$key] = $key;
        }
        sort($arr);//字典序排序
        $str = '';
        foreach ($arr as $k => $v) {
            $str .= $v . $param[$v];
        }
        $sign = strtoupper(md5($str . config('secret_str')));
        if ($sign !== $post_sign) {
            $this->apiReturn(5, '签名验证未通过', '');
        }
    }

//TODO token验证
    public function check_token()
    {
        $param = request()->param();
        if(!$param['user_token']){
            $this->apiReturn(1006, 'token验证未通过', '');
        }
        $validate = $this->validate($param, [
            'user_token' => 'require',
            'user_id' => 'integer|gt:0|require',
        ]);
        if ($validate !== true)
            $this->paramError($validate);
        $model = model('users');
        if (!$this->user)
            $this->apiReturn(1006, '用户不存在', '');
        if ($this->user['user_token'] != $param['user_token'] || $this->user['expire_time'] < time()) {//验证token是否正确，以及是否过期
            $this->apiReturn(1006, 'token验证未通过', '');
        }
        $model->update(['user_id' => $param['user_id'], 'expire_time' => time() + config('token_expire_time'), 'last_login_time' => time()]);//更新token有效期
    }

    /**
     * [apiReturn 用于给app提供接口使用 带有请求结果状态表示,和结果提示，默认返回json]
     * @param  [number] $status  [请求结果的状态标识，设定后要在文档中给予说明]
     * @param  string $message [请求结果的提示语句]
     * @param  [array] $data    [请求返回的数据,app前端需要的数据]
     * @param  string $type [要返回的数据类型，支持json,xml，默认返回json]
     * return [json或xml]          [返回数据]
     * @author      Lcw
     */
    protected function apiReturn($status, $message = '', $data = [], $type = 'json')
    {
        if (!is_numeric($status) || !is_string($message)) {
            $this->apiReturn('4', '后台代码异常');
        }
        $res = array();
        $res['status'] = (string)$status;
        $res['message'] = $message;
        $res['data'] = $data;

        if (in_array($type, array('json', 'xml'))) {
            $this->ajaxReturn($res, $type);
        } else {
            $this->ajaxReturn($res);
        }
    }

    /**
     * 参数错误
     */
    public function paramError($msg = '')
    {
        $msg = isset($msg) && $msg ? $msg :  '参数错误';
        $this->apiReturn(1003, $msg, '');
    }
    public function ajaxReturn($data,$type = 'json'){
        exit(json_encode($data));
    }
}