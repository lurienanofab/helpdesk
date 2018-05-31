<?php
require('api.inc.php');

class wp_plugin{
	function post($key){
		return isset($_POST[$key]) ? $_POST[$key] : '';
	}
	
	function handleRequest(){
		$id = $this->post('id');
		$command = $this->post('command');
		header('Content-type: application/json');
		if ($id == 'wp_osticket_plugin'){
			switch ($command){
				case 'test':
					echo json_encode(array('result'=>'success', 'message'=>'The test passed.'));
					break;
				default:
					echo json_encode(array('result'=>'fail', 'message'=>'Invalid command.'));
					break;
			}
		}
		else{
			echo json_encode(array('result'=>'fail', 'message'=>'Invalid id'));
		}
	}
}

$wpp = new wp_plugin();
$wpp->handleRequest();
?>