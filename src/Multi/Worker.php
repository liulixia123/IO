<?php
namespace Lixia18\Io\Multi;
/*1. 保存所有的socket,通过select系统调用，监听socket描述符的可读事件
2. Select会在内核空间监听一旦发现socket可读，会从内核空间传递至用户空间，在用户空间通过逻辑判断是服务端socket可读，还是客户端的socket可读
3. 如果是服务端的socket可读，说明有新的客户端建立，将socket保留到监听数组当中
4. 如果是客户端的socket可读，说明当前已经可以去读取客户端发送过来的内容了，读取内容，然后响应给客户端。*/
class Worker{
	//自定义服务事件
	public $onReceive = null;
	public $onConnect = null;
	public $onClose = null;
	//连接
	public $socket = null;

	protected $sockets = [];

	public function __construct($socket_address){
		$this->socket = stream_socket_server($socket_address);
		stream_set_blocking($this->socket,0);
		$this->sockets[(int) $this->socket] = $this->socket;
		//echo $socket_address."\n";
	}
	public function accept() { 
	// 接收连接和处理使用 
		while (true) { 
			$read = $this->sockets;
			stream_select($read, $w, $e, 1);
			foreach ($read as $socket) { 
				// $socket 可能为 
				if ($socket === $this->socket) { 
					// 创建与客户端的连接 
					$this->createSocket(); 
				 }else { 
					// 发送信息 			  
					$this->sendMessage($socket); 
				}
			}
			
		} 
	}

	public function createSocket(){
		$client = @stream_socket_accept($this->socket); 
		// is_callable判断一个参数是不是闭包 
		if (is_callable($this->onConnect)) {
			// 执行函数 
			($this->onConnect)($this, $client); 
		}
		$this->sockets[(int) $client] = $client;
	}

	public function sendMessage($client){
		$data = fread($client, 65535); 
		if ($data === '' || $data == false) { 
			// 关闭连接 
			fclose($client);
			unset($this->sockets[(int) $client]); 
			// 这里需要 
			return null; 
		}
		if (is_callable($this->onReceive)) { 
			($this->onReceive)($this, $client, $data); 
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