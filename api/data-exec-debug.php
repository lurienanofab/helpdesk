<?php
require("api.inc.php");
require_once("ticket-plugin.php");
require_once("data-plugin.php");
require_once("lnf/class.lnfutil.php");
require_once(INCLUDE_DIR."class.lnf.php");
require_once(INCLUDE_DIR."class.ticket.php");

class DataExec extends DataPlugin{
	protected function processAction(){
		$this->processTest();
		//the default action (no resourceId supplied) returns all open tickets
		switch ($this->action){
			case "add-ticket":
				$this->addTicket();
				$this->outputTickets($this->selectTickets());
				break;
			case "ticket-detail":
				if ($this->ticketID != 0){
					$detail = $this->selectTicketDetail();
					$this->outputTicketDetail(array("error"=>false, "message"=>"ok: $this->ticketID", "detail"=>$detail));
				}
				else
					$this->outputTicketDetail(array("error"=>true, "message"=>"Invalid parameter: ticketID"));
				break;
			case "dept-membership":
				$staff_id = $this->getval("staff_id", 0);
				$html = LNF::getDeptMembership(array("staff_id"=>$staff_id));
				echo $html;
				break;
			case "select-tickets-by-email":
				$email = $this->getval("email", "");
				$this->outputTickets(LNF::selectTicketsByEmail($email));
				break;
			case "select-tickets-by-resource":
				$this->outputTickets($this->selectTickets());
				break;
			case "post-message":
				if ($this->ticketID != 0){
					$message = $this->getval("message", "");
					$ticket = TicketPlugin::getTicket($this->ticketID);
					$ticket->postMessage($message);
					$detail = $this->selectTicketDetail();
					$this->outputTicketDetail(array("error"=>false, "message"=>"ok: $this->ticketID", "detail"=>$detail));
				}
				else
					$this->outputTicketDetail(array("error"=>true, "message"=>"Invalid parameter: ticketID"));
				break;
			case "summary":
				$resources = $this->getval("resources", "");
				if ($resources)
					echo $this->outputSummary(array("error"=>false, "message"=>"ok: $resources", "summary"=>LNF::summary($resources)));//$this->outputSummary(array("error"=>false, "message"=>"ok: $resources", "summary"=>LNF:summary($resources)));
				else
					$this->outputSummary(array("error"=>true, "message"=>"Invalid parameter: resources"));
				break;
			case "get-ticket-counts":
				$user = new Staff($this->getval("staff_userID", ""));
				$staleCount = LNF::getStaleTicketCount($user);
				$overdueCount = LNF::getOverdueTicketCount($user);
				echo json_encode(array("staleTicketCount"=>$staleCount, "overdueTicketCount"=>$overdueCount));
				break;
			default:
				if ($this->sdate == "")
					$this->sdate = LNF::defaultStartDate();
				$this->outputTickets($this->selectTickets());
				break;
		}
	}
}

$exec = new DataExec();
$exec->handleRequest();
?>