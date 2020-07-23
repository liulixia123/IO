<?php
$port = 9005;
$host = "tcp://0.0.0.0:9005";
// 创建socket服务
$server = stream_socket_server($host);
echo $host."\n";
if ($server === false) {
    throw new \RuntimeException("fail to listen on port: {$port}!");
}
// 建立与客户端的连接
// 服务就处于一个挂起的状态 -》 等待连接进来并且呢创建连接
// stream_socket_accept 是阻塞
// 监听连接 -》 可以重复监听
while (true) {
	$client = @stream_socket_accept($server);
	// sleep(3);
	var_dump(fread($client, 65535));
	fwrite($client, "server hello");
	fclose($client);
	var_dump($client);
}


/*$port = 9000;
$host = "tcp://0.0.0.0:".$port;

$socket = stream_socket_server($host,$errno, $errMsg);
if ($socket === false) {
    throw new \RuntimeException("fail to listen on port: {$port}!");
}
fwrite(STDOUT, "socket server listen on port: {$port}" . PHP_EOL);
while (true) {
	$client = stream_socket_accept($socket);
	var_dump($client);
	fwrite($client, "server hellow");
	fclose($client);
	
}*/