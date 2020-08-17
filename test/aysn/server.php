<?php
require __DIR__.'/../../vendor/autoload.php';
use Lixia18\Io\Aysn\Worker;
$host = "tcp://0.0.0.0:9001";
$server = new Worker($host);

// 接收和处理信息
$server->onReceive = function($socket, $client, $data){
// echo "给连接发送信息\n";
$socket->send($client, "hello world client \n");
// fwrite($client, "server hellow");
};
echo $host."\n";
$server->start();
?>