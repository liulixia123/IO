<?php
require __DIR__.'/../../vendor/autoload.php';
use Lixia18\Io\Blocking\Worker;
$host = "tcp://0.0.0.0:9001";
$server = new Worker($host);
$server->onConnect = function($socket, $client){
echo "on connect\n";
var_dump($client);
};
// 接收和处理信息
$server->onReceive = function($socket, $client, $data){
echo "send message\n";
$socket->send($client, "hello world client \n");
// fwrite($client, "server hellow");
};
$server->start();
?>