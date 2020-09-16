<?php
$socket = stream_socket_server("tcp://0.0.0.0:9000", $errno, $errstr);
stream_set_blocking($socket, 0);
$eventBase = new EventBase;
$event = new Event($eventBase, $socket, Event::READ | Event::PERSIST, function($socket) use ($eventBase) {
	echo "连接 start \n";
	$conn = stream_socket_accept($socket);
	stream_set_blocking($conn, false);
	$event = new Event($eventBase, $conn, Event::WRITE | Event::PERSIST, function($conn) use ($eventBase) {
		echo "WRITE start \n";
		var_dump(fread($conn, 65535));
		fwrite($conn, "hello event");
		echo "WRITE end \n";
	});
	$event->add();
	echo "连接 end \n";
});
$event->add();
$eventBase->loop();