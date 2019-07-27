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
    }
    public function sendMessage(){
        $user_id = input('user_id');
        $message = input('message');
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
        Gateway::sendToUid('-1', json_encode(['type'=>'msg','content'=>$data]));
    }
}
