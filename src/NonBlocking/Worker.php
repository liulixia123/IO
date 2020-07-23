<?php
namespace Lixia18\Io\NonBlocking;

class Worker{
	//自定义服务事件
	public $onReceive = null;
	public $onConnect = null;
	public $onClose = null;
	//连接
	public $socket = null;

	public function __construct($socket_address){
		$this->socket = stream_socket_server($socket_address);
		stream_set_blocking($this->socket, 0);
		echo $socket_address."\n";
	}
	public function accept() { 
	// 接收连接和处理使用 
		while (true) { 
			$client = @stream_socket_accept($this->socket); 
			// is_callable判断一个参数是不是闭包 
			if (is_callable($this->onConnect)) {
	 			// 执行函数 
	 			($this->onConnect)($this, $client); 
	 		}
			// tcp 处理 大数据 重复多发几次 
			/*$buffer = ""; //
			while (!feof($client)) { 
			  $buffer = $buffer.fread($client, 65535); 
			}*/
			$data = fread($client, 65535); 
			if (is_callable($this->onReceive)) {
			    ($this->onReceive)($this, $client, $data); 
			}
			// 处理完成之后关闭连接 
			fclose($client); 
		} 
	}
	// 发送信息 
	public function send($conn, $data) {
	  fwrite($conn, $data); 
	}
	// 启动服务的 
	public function start() {
		$this->accept();
	}
}