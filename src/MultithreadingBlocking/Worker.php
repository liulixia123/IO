<?php
namespace Lixia18\Io\MultithreadingBlocking;

class Worker{
	//自定义服务事件
	public $onReceive = null;
	public $onConnect = null;
	public $onClose = null;
	//连接
	public $socket = null;
	//配置进程数
	protected $config = [
		'workerNum' => 4,
	];

	public function __construct($socket_address){
		$this->socket = stream_socket_server($socket_address);
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
		$this->fork();
	}
	// 创建进程完成事情
	public function fork() {
		for ($i=0; $i < $this->config['workerNum']; $i++) {
			// 创建子进程
			$son_pid = pcntl_fork();
			if ($son_pid > 0) {
				// 父进程空间
			} else if ($son_pid < 0){
				$this->send($this->socket, "服务器异常");
			} else {
				echo $son_pid."\n";
				// 由子进程完成事情
				$this->accept();
			}
		}
		// 父进程监听子进程情况并回收进程
		if ($son_pid) {
			$status = 0;
			$sop = \pcntl_wait($status);
			echo $sop."\n";
		}
	}
	public function accept()
	{
	    while (true){ //循环监听
			$client = stream_socket_accept($this->socket);//在服务端阻塞监听
			if(!empty($client) && is_callable($this->onConnect)){//socket连接成功并且是我们的回调
				//触发事件的连接的回调
				call_user_func($this->onConnect, $client);
			}
			//从连接中读取客户端内容
			$buffer=fread($client,65535);//参数2：在缓冲区当中读取的最大字节数
			//正常读取到数据。触发消息接收事件，进行响应
			if(!empty($buffer) && is_callable($this->onMessage)){
				//触发时间的消息接收事件
				//传递到接收消息事件》当前连接、接收到的消息
				call_user_func($this->onMessage,$this,$client,$buffer);
			}
			\fclose($client);
		}
	}
	//响应http请求
	public function send($conn,$content){
		$http_resonse = "HTTP/1.1 200 OK\r\n";
		$http_resonse .= "Content-Type: text/html;charset=UTF-8\r\n";
		$http_resonse .= "Connection: keep-alive\r\n";
		$http_resonse .= "Server: php socket server\r\n";
		$http_resonse .= "Content-length: ".strlen($content)."\r\n\r\n";
		$http_resonse .= $content;
		fwrite($conn, $http_resonse);
	}
}
?>