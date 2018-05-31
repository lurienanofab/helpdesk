<?php
require('api.inc.php');

class wpPlugin{
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
				case 'list':
					echo json_encode($this->createList());
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
	
	function createList(){
		$type = $this->post('type');
		$result = array('result'=>'success');
		foreach (explode('|', $type) as $t){
			switch($type){
				case 'users':
					$result['users'] = $this->dbselect("SELECT staff_id, username FROM ".STAFF_TABLE);
					break;
				case 'tickets':
					$status = $this->post('status');
					$dept = $this->post('dept');
					$result['tickets'] = $this->dbselect("SELECT t.ticketID, t.dept_id, t.email as 'email', t.name, t.subject, t.status, t.created, d.dept_name, s.username, s.firstname, s.lastname, s.email as 'staff_email' FROM ".TICKET_TABLE." t INNER JOIN ".DEPT_TABLE." d ON d.dept_id = t.dept_id LEFT JOIN ".STAFF_TABLE." s ON t.staff_id = s.staff_id WHERE t.status = '$status'".(($dept == '' || $dept == 'all')?'':" AND d.dept_name='$dept'")." ORDER BY t.created DESC");
					break;
				case 'detail':
					$ticketID = $this->post('ticketID');
					$result['detail'] = $this->dbselect("SELECT msg_id, 0 as 'response_id', message, created, NULL AS 'lastname', NULL AS 'firstname', NULL AS 'staff_email' FROM ".TICKET_MESSAGE_TABLE." WHERE ticket_id = (SELECT ticket_id FROM ".TICKET_TABLE." WHERE ticketID = '$ticketID') UNION SELECT r.msg_id, r.response_id, r.response, r.created, s.lastname, s.firstname, s.email FROM ".TICKET_RESPONSE_TABLE." r LEFT JOIN ".STAFF_TABLE." s ON s.staff_id = r.staff_id WHERE ticket_id = (SELECT ticket_id FROM ".TICKET_TABLE." WHERE ticketID = '$ticketID') ORDER BY 1, 2");
					break;
			}
		}
		return $result;
	}
	
	function dbselect($sql){
		$result = array();
		if(($query = db_query($sql)) && db_num_rows($query)){
			while(($row = db_fetch_array($query)) !== false){
				$result[] = $row;
			}
		}
		return $result;
	}
}

$wpp = new wpPlugin();
$wpp->handleRequest();
?>