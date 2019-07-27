<?php
class Health {
    public static $status;
    public function __construct()
    {
    }
    public function check($ip, $port){
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_nonblock($sock);
        socket_connect($sock,$ip, $port);
        socket_set_block($sock);
        self::$status = socket_select($r = array($sock), $w = array($sock), $f = array($sock), 5);
        return(self::$status);
    }
    public function checklist($lst){
    }
    public function status(){
        switch(self::$status)
        {
            case 2:
                echo "Closed\n";
                break;
            case 1:
                echo "Openning\n";
                break;
            case 0:
                echo "Timeout\n";
                break;
        }
    }
}
$ip='192.168.2.10';
$port=80;
$health = new Health();
$health->check($ip, $port);
$health->status();