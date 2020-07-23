<?php
$client = stream_socket_client("tcp://127.0.0.1:9001");
$new = time();
// 给socket通写信息
fwrite($client, "hello world");
// 读取信息
var_dump(fread($client, 65535));
// 关闭连接
fclose($client);
echo "\n".time()- $new;