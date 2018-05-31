<?php
require_once('class.lnfutil.php');

session_start();

function contentType($type){
	switch ($type){
		case 'json':
			header('Content-Type: application/json');
			break;
		case 'xml':
			header('Content-Type: text/xml');
			break;
		case 'text':
			header('Content-Type: text/plain');
			break;
		default:
			header('Content-Type: text/html');
			break;
	}
}

function handleCommand($command){
	switch($command){
		case 'get-tools':
			contentType('html');
			echo lnfutil::toolSelect();
			break;
		case 'user-check':
			contentType('json');
			echo json_encode(lnfutil::loginCheck());
			break;
		case 'logout':
			contentType('json');
			$_SESSION['_staff']=array();
			session_unset();
			session_destroy();
			echo json_encode(array('success'=>true));
			break;
		case 'session':
			contentType('json');
			echo json_encode($_SESSION);
			break;
		case 'server':
			contentType('json');
			echo json_encode($_SERVER);
			break;
		default:
			contentType('json');
			echo json_encode(array('error'=>'Invalid command.'));
			break;
	}
}

$command = lnfutil::request('command');
handleCommand($command);
?>