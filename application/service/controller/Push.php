<?php
namespace app\service\controller;

use think\Controller;
use think\Db;
vendor('GatewayClient.Gateway');
use GatewayClient\Gateway;

class Push extends Controller
{
    public function __construct()
    {
        parent::__construct();
        Gateway::$registerAddress = '127.0.0.1:1238';
    }

    public function index(){
        return $this->fetch();
    }
}
