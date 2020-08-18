<?php
namespace Lixia18\Io\Reactor\Swoole\Multithreading;
use Swoole\Event;
class WorkerEC
{
	//监听socket
	protected $sockets = NULL;
	//连接事件回调
	public $onConnect = NULL;
	//接收消息事件回调
	public $onMessage = NULL;
	protected $config = [
		'workerNum' => 4,
	];
	protected $socket_address;
	public function __construct($socket_address) {
		//监听地址+端口
		// $this->socket = stream_socket_server($socket_address);
		$this->socket_address = $socket_address;
	}
	public function set($data){
		// 简单点
		$this->config = $data;
	}
	public function start(){
		$this->fork();
	}
	/**
	* 创建进程完成事情
	* 
	* @return [type] [description]
	*/
	public function fork() {
		$son_pid = pcntl_fork();
		for ($i=0; $i < $this->config['workerNum']; $i++) {
			if ($son_pid > 0) {
			$son_pid = pcntl_fork();
			} else if($son_pid < 0){
			// 异常
			} else {
			$this->accept();
			// exit;
			}
		}
		// 父进程监听子进程情况并回收进程
		for ($i=0; $i < $this->config['workerNum']; $i++) {
			$status = 0;
			$sop = pcntl_wait($status);
		}
	}
	// 接收连接，并处理连接
	public function accept(){
		// $this->sockets[(int) $socket] = $socket;
		// 第一个需要监听的事件(服务端socket的事件),一旦监听到可读事件之后会触发
		Event::add($this->initServer(), $this->createSocket());
	}
	/**
	* 初始化话server
	* 
	* @return [type] [description]
	*/
	public function initServer()
	{
		$opts = [
			'socket' => [
				// 连接成功之后的等待个数
				'backlog' => '102400',
			]
		];
		$context = stream_context_create($opts);//上下文资源流
		// 设置端口可以被多个进程重复的监听
		stream_context_set_option($context, 'socket', 'so_reuseport', 1);//对资源流、数据包或者上下文设置参数
		return stream_socket_server($this->socket_address, $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);
	}
	/**
	* 建立与客户端的连接
	* 
	* @return [type] [description]
	*/
	public function createSocket(){
		return function($socket){
			// 测试端口监听的效果
			// $this->debug(posix_getpid());
			$client=stream_socket_accept($socket);
			//触发事件的连接的回调
			if(!empty($client) && is_callable($this->onConnect)){
				call_user_func($this->onConnect, $client);
			}
			Event::add($client, $this->sendMessage());
		};
	}
	/**
	* 发送信息
	* 
	* @return [type] [description]
	*/
	public function sendMessage(){
		return function($socket){
			//从连接当中读取客户端的内容
			$buffer=fread($socket,1024);
			//如果数据为空，或者为false,不是资源类型
			if(empty($buffer)){
				if(feof($socket) || !is_resource($socket)){
				// 触发关闭事件
				// swoole_event_del($socket);
				// fclose($socket);
				}
			}
			//正常读取到数据,触发消息接收事件,响应内容
			if(!empty($buffer) && is_callable($this->onMessage)){
				call_user_func($this->onMessage,$this,$socket,$buffer);
			}
		};
	}
	public function debug($data, $flag = false)
	{
		if ($flag) {
		\var_dump($data);
		} else {
		echo "==== >>>> : ".$data." \n";
		}
	}
	/**
	* 响应http请求
	* 
	* @param [type] $conn [description]
	* @param [type] $content [description]
	* @return [type] [description]
	*/
	public function send($conn, $content){
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