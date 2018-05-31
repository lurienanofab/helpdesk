<?
class TicketPlugin{
	static function getTicket($ticketID){
		$ticket = new Ticket(0);
		$id = $ticket->getIdByExtId($ticketID);
		$ticket->load($id);
		return $ticket;
	}
	
	static function getInfo($ticket){
		$dept = $ticket->getDept();
		$staff = $ticket->getStaff();
		$topic = $ticket->getTopic();
		$result = array(
			'ticketID'			=> $ticket->getExtId(),
			'subject'			=> $ticket->getSubject(),
			'status'			=> $ticket->getStatus(),
			'priority'			=> $ticket->getPriority(),
			'dept_name'			=> ($dept)?$dept->getName():'',
			'created'			=> $ticket->getCreateDate(),
			'name'				=> $ticket->getName(),
			'email'				=> $ticket->getEmail(),
			'phone'				=> $ticket->getPhone(),
			'source'			=> $ticket->getSource(),
			'assigned_name'		=> ($staff)?$staff->getName():'',
			'assigned_email'	=> ($staff)?$staff->getEmail():'',
			'help_topic'		=> ($topic)?$topic->getName():'',
			'last_response'		=> $ticket->getLastResponseDate(),
			'last_message'		=> $ticket->getLastMessageDate(),
			'ip_address'		=> $ticket->getIP(),
			'due_date'			=> $ticket->getDueDate()
		);
		return $result;
	}
	
	static function getMessages($ticket){
		$result = array();
		$sql='SELECT msg.*, count(attach_id) as attachments  FROM '.TICKET_MESSAGE_TABLE.' msg '.
            ' LEFT JOIN '.TICKET_ATTACHMENT_TABLE.' attach ON  msg.ticket_id=attach.ticket_id AND msg.msg_id=attach.ref_id AND ref_type=\'M\' '.
            ' WHERE  msg.ticket_id='.db_input($ticket->getId()).
            ' GROUP BY msg.msg_id ORDER BY created';
	    $msgres =db_query($sql);
	    while ($msg_row = db_fetch_array($msgres)){
			$result[] = array(
				'msg_id'=>$msg_row['msg_id'],
				'created'=>$msg_row['created'],
				'message'=>$msg_row['message'],
				'source'=>$msg_row['source'],
				'ip_address'=>$msg_row['ip_address'],
				'attachments'=>$msg_row['attachments']
			);
		}
		return $result;
	}
	
	static function getNotes($ticket){
		$result = array();
		$sql ='SELECT note_id,title,note,source,created FROM '.TICKET_NOTE_TABLE.' WHERE ticket_id='.db_input($ticket->getId()).' ORDER BY created DESC';
		if(($resp=db_query($sql)) && ($notes=db_num_rows($resp))){
			while($row=db_fetch_array($resp)) {
				$result[] = $row;
			}
		}
		return $result;
	}
	
	static function getResponses($ticket, $msg_id=false){
		$result = array();
		$sql='SELECT resp.*,count(attach_id) as attachments FROM '.TICKET_RESPONSE_TABLE.' resp '.
			' LEFT JOIN '.TICKET_ATTACHMENT_TABLE.' attach ON  resp.ticket_id=attach.ticket_id AND resp.response_id=attach.ref_id AND ref_type=\'R\' '.
			' WHERE msg_id='.(($msg_id)?db_input($msg_id):'msg_id').' AND resp.ticket_id='.db_input($ticket->getId()).
			' GROUP BY resp.response_id ORDER BY created';
		$resp =db_query($sql);
		while ($resp_row = db_fetch_array($resp)){
			$result[] = array(
				'response_id'=>$resp_row['response_id'],
				'msg_id'=>$resp_row['msg_id'],
				'staff_id'=>$resp_row['staff_id'],
				'staff_name'=>$resp_row['staff_name'],
				'response'=>$resp_row['response'],
				'ip_address'=>$resp_row['ip_address'],
				'created'=>$resp_row['created'],
				'attachments'=>$resp_row['attachments']
			);
		}
		return $result;
	}
}
?>