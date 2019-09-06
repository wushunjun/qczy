<?php
namespace app\service\controller;

use think\Controller;
use think\Db;
vendor('GatewayClient.Gateway');
use GatewayClient\Gateway;

class Layim extends Controller
{
    public function __construct()
    {
        parent::__construct();
        /*解决跨域问题*/
        header('Access-Control-Allow-Origin:*');
        header("Content-type: text/html; charset=utf-8");
        Gateway::$registerAddress = '127.0.0.1:1238';
    }

    /**
     * 初始化
     */
    function init()
    {
        $session_list = Gateway::getAllClientSessions();
    }
    /**
     * 绑定
     */
    function bind()
    {
        $user_id = input('user_id');
        $client_id = input('client_id');
        Gateway::bindUid($client_id, $user_id);
        Gateway::setSession($client_id, array('user_id'=>$user_id));
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->set($client_id,$user_id);
        $user = Db::name('users')->find($user_id);
        if(!$user){
            exit(json_encode(['status'=>1002,'message'=>'用户不存在','data'=>'']));
        }
        $data = [
            'username' =>  $user['nickname'],
            'avatar' =>  $user['head_pic'],
            'id' =>  $user['user_id'],
            'type' =>  'friend',
            'groupid' =>  1,
            'fromid' =>  $user['user_id'],
            'sign' =>  '无',
        ];
        Gateway::sendToUid('-1', json_encode(['type'=>'online','content'=>$data]));
        exit(json_encode(['status'=>1001,'message'=>'绑定成功','data'=>'']));
    }
    public function sendMessage(){
        $user_id = input('user_id');
        $message = input('message');
        $to_user_id = input('to_user_id/d',-1);
        $user = Db::name('users')->find($user_id);
        $data = [
            'username' =>  $user['nickname'],
            'avatar' =>  $user['head_pic'],
            'id' =>  $user['user_id'],
            'type' =>  'friend',
            'content' =>  $message,
            'mine' =>  false,
            'fromid' =>  $user['user_id'],
            'timestamp' =>  time() * 1000,
        ];
        $log_data = json_encode(['id'=>$user_id, 'name'=>$user['nickname'], 'message'=>$message, 'time'=>time() * 1000]);
        $key = $user_id < $to_user_id ? $user_id . '-' . $to_user_id : $to_user_id . '-' . $user_id;
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->lPush($key,$log_data);//保存聊天记录
        Gateway::sendToUid($to_user_id, json_encode(['type'=>'msg','content'=>$data]));
        exit(json_encode(['status'=>1001,'message'=>'发送成功','data'=>'']));
    }

    /**
     * 获取历史聊天记录
     */
    public function getHistory(){
        $user_id = input('user_id');
        $to_user_id = input('to_user_id/d',-1);
        $key = $user_id < $to_user_id ? $user_id . '-' . $to_user_id : $to_user_id . '-' . $user_id;
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        $list = $redis->lRange($key,0,100);//保存聊天记录
        $list = array_reverse($list);
        foreach($list as $k=>$v){
            $list[$k] = json_decode($v);
        }
        exit(json_encode(['status'=>1001,'message'=>'发送成功','data'=>$list]));
    }
}
