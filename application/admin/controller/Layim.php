<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
vendor('GatewayClient.Gateway');
use GatewayClient\Gateway;

class Layim extends Base
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
        $session_list = array_values($session_list);
        $user_ids = [];
        foreach($session_list as $k=>$v){
            if(isset($v['user_id']) && $v['user_id'] != -1){
                $user_ids[] = $v['user_id'];
            }
        }
        $user_list = Db::name('users')
            ->field('user_id as id,nickname as username,head_pic as avatar')
            ->where(['user_id'=>['in',$user_ids]])
            ->select();
        $data['mine'] = [
            'username' => '客服',
            'id' => '-1',
            'status' => 'online',
            'sign' => '为客户服务',
            'avatar' => 'https://qczyapi.siyuan666.com/public/images/im_logo.jpg',
        ];
        $data['friend'][0] = [
            'groupname' => '一群上帝',
            'id' => '1',
            'list' => $user_list
        ];
        $this->ajaxReturn(['code'=>0,'msg'=>'','data'=>$data]);
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
    }
    public function sendMessage(){
        $user_id = input('user_id');
        $message = input('message');
        $data = [
            'username' =>  '客服小姐姐',
            'avatar' =>  'https://qczyapi.siyuan666.com/public/images/im_logo.jpg',
            'id' =>  '-1',
            'type' =>  'friend',
            'content' =>  $message,
            'mine' =>  false,
            'fromid' =>  -1,
            'timestamp' =>  date('Y-m-d H:i'),
        ];
        $log_data = json_encode(['id'=>-1, 'name'=>'客服小姐姐', 'message'=>$message, 'time'=>time() * 1000]);
        $key = '-1-' . $user_id;
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->lPush($key,$log_data);//保存聊天记录
        Gateway::sendToUid($user_id, json_encode(['type'=>'msg','content'=>$data]));
    }
}
