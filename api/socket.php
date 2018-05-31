<?php
$address = '0.0.0.0';
$port = 12345;

// Create WebSocket.
$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($server, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($server, $address, $port);
socket_listen($server);
socket_set_nonblock($server);

$clients = array();

while (true){
    $temp = $clients;
    
    if (($newc = socket_accept($server)) !== false){
        echo "Client $newc has connected\n";
        $clients[] = $newc;
    }
    
    foreach ($clients as $c){
        $response = "time: " . time();
        socket_write($c, $response);
    }
    
    sleep(1);
}

/*
$client = socket_accept($server);

// Send WebSocket handshake headers.
$request = socket_read($client, 5000);
preg_match('#Sec-WebSocket-Key: (.*)\r\n#', $request, $matches);
$key = base64_encode(pack(
    'H*',
    sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')
));

$headers = "HTTP/1.1 101 Switching Protocols\r\n";
$headers .= "Upgrade: websocket\r\n";
$headers .= "Connection: Upgrade\r\n";
$headers .= "Sec-WebSocket-Version: 13\r\n";
$headers .= "Sec-WebSocket-Accept: $key\r\n\r\n";
socket_write($client, $headers, strlen($headers));

$uid = $_GET['uid'];

$last_ticket = '';

// Send messages into WebSocket in a loop.
while (true) {
    $last = get_last_ticket($uid, 'helpdesk.support@lnf.umich.edu');
    
    if ($last["ticketID"] !== $last_ticket){
        $last_ticket = $last["ticketID"];
        $time = time();
        $content = "[$time] $uid:$last_ticket";
        $response = chr(129) . chr(strlen($content)) . $content;
        socket_write($client, $response);
    }
    
    sleep(30);
}

function get_last_ticket($uid, $queue){
    return array('ticketID' => '1');
}
*/
?>
