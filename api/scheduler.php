<?php
require('api.inc.php');
require_once('ticket-plugin.php');
require_once('data-plugin.php');
require_once(INCLUDE_DIR.'class.ticket.php');
require_once(INCLUDE_DIR.'class.lnf.php');

class Scheduler extends DataPlugin{
	protected function processAction(){
		$this->action = $this->getval('Command', '');
		$this->ticketId = $this->getval('TicketID', 0);
		$this->resourceId = $this->getval('ResourceID', 0);
		$this->email = $this->getval('Email', '');
		$this->name = $this->getval('Name', '');
		$this->queue = $this->getval('Queue', '');
		$this->subject = $this->getval('Subject', '');
		$this->message = $this->getval('Message', '');
		$this->pri = $this->getval('Priority', '');
		$this->test = $this->getval('Test', false);
		$this->status = ($this->status == '') ? 'open' : $this->status;
		switch ($this->action){
			case "select-tickets":
				$this->outputTickets($this->selectTickets());
				break;
			case "select-ticket-detail":
				if ($this->ticketId != 0){
					$detail = $this->selectTicketDetail();
					$this->outputTicketDetail(array('error'=>false,'message'=>"ok: $this->ticketId",'detail'=>$detail));
				}
				else
					$this->outputTicketDetail((array('error'=>true,'message'=>'Invalid parameter: ticket_id')));
				break;
			case "create-ticket":
				$this->addTicket();
				$this->outputTickets($this->selectTickets());
				break;
			case "post-message":
				if ($this->ticketId != 0){
					$msgid = $this->postMessage();
					
					if ($msgid > 0){
						$err = false;
						$msg = "ok: $msgid";
					}
					else{
						$err = true;
						$msg = "Unable to post message.";
					}
					
					$detail = $this->selectTicketDetail();
					
					$this->outputTicketDetail(array('error'=>$err,'message'=>$msg,'detail'=>$detail));
				}
				else
					$this->outputTicketDetail((array('error'=>true,'message'=>'Invalid parameter: ticket_id')));
				break;
			default:
				header('Content-Type: text/plain');
				echo 'Invalid command: '.$this->action;
				break;
		}
	}
}

$sched = new Scheduler();
$sched->handleRequest();
?>