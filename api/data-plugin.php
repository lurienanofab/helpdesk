<?php
require_once("lnf/class.lnfutil.php");

abstract class DataPlugin{
    protected $format;
    protected $ticket_id;
    protected $ticketID;
    protected $resource_id;
    protected $assigned_to;
    protected $unassigned;
    protected $email;
    protected $name;
    protected $queue;
    protected $subject;
    protected $message;
    protected $pri;
    protected $priority_desc;
    protected $status;
    protected $sdate;
    protected $edate;
    protected $callback;
    protected $action;
    protected $search;
    protected $test;

    abstract protected function processAction();

    protected function processTest(){
        if ($this->test){
            $result = $this->selectTickets();
            $this->outputTickets($result);
            exit();
        }
    }

    protected function writeLog($type, $title, $log, $logger){
        db_query(
            "INSERT ost_syslog_lnf (log_type, title, log, logger, ip_address, created, updated) VALUES (".
            "'".db_real_escape($type)."'".
            ", '".db_real_escape($title)."'".
            ", '".db_real_escape($log)."'".
            ", '".db_real_escape($logger)."'".
            ", '".$this->getRealIP()."', NOW(), NOW())"
        );
    }

    //http://roshanbh.com.np/2007/12/getting-real-ip-address-in-php.html
    protected function getRealIP(){
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) //check ip from share internet
            $ip=$_SERVER["HTTP_CLIENT_IP"];
        elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) //to check ip is pass from proxy
            $ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
        else
            $ip=$_SERVER["REMOTE_ADDR"];
        return $ip;
    }

    protected function selectTickets($criteria = null){
        $sql = "SELECT t.*, IFNULL(tr.resource_id, 0) AS 'resource_id', tp.priority_desc, tp.priority_urgency, CONCAT(s.lastname, ', ', s.firstname) AS 'assigned_to'"
            ." FROM ".TICKET_TABLE." t"
            ." LEFT JOIN ".TICKET_RESOURCE_TABLE." tr ON t.ticket_id = tr.ticket_id"
            ." INNER JOIN ".TICKET_PRIORITY_TABLE." tp ON tp.priority_id = t.priority_id"
            ." LEFT JOIN ".STAFF_TABLE." s ON s.staff_id = t.staff_id";
        $sql .= $this->where($criteria);
        $result = $this->execQuery($sql);
        return $result;
    }

    protected function selectTicketDetail(){
        $ticket = TicketPlugin::getTicket($this->ticketID);
        $result = array(
            "info"          => TicketPlugin::getInfo($ticket),
            "messages"      => TicketPlugin::getMessages($ticket),
            "responses"     => TicketPlugin::getResponses($ticket)
        );
        return $result;
    }

    protected function postMessage(){
        $ticket = TicketPlugin::getTicket($this->ticketID);
        $id = $ticket->postMessage($this->message);
        return $id;
    }

    protected function createTicket($var, $errors, $source){
        $start = microtime(true);
        $result = array(
            'ticket'    => Ticket::create($var, $errors, "email"),
            'timeTaken' => 0);
        $end = microtime(true);
        $result['timeTaken'] = $end - $start;
        return $result;
    }

    protected function assignResourceTicket($ticket){
        LNF::assignResourceTicket($ticket, $this->resource_id);
    }

    protected function ticketQueueEmailId(){
        return Email::getIdByEmail(urldecode($this->queue));
    }

    protected function ticketMessage(){
        return utf8_encode(Format::stripEmptyLines(urldecode($this->message)));
    }

    protected function ticketSubject(){
        return urldecode($this->subject);
    }

    protected function ticketEmail(){
        return urldecode($this->email);
    }

    protected function ticketName(){
        return urldecode($this->name);
    }

    protected function ticketPri(){
        return urldecode($this->pri);
    }

    public function handleRequest(){
        $this->format           = $this->getval("f", "xml");
        $this->format           = $this->getval("format", $this->format);
        $this->ticket_id        = $this->getval("ticket_id", 0);
        $this->ticketID         = $this->getval("ticketID", 0);
        $this->resource_id      = $this->getval("resource_id", 0);
        $this->assigned_to      = $this->getval("assigned_to", "");
        $this->unassigned       = $this->getval("unassigned", "");
        $this->email            = $this->getval("email", "");
        $this->name             = $this->getval("name", "");
        $this->queue            = $this->getval("queue", "");
        $this->subject          = $this->getval("subject", "");
        $this->message          = $this->getval("message", "");
        $this->pri              = $this->getval("pri", "");
        $this->priority_desc    = $this->getval("priority_desc", "");
        $this->status           = $this->getval("status", "");
        $this->sdate            = $this->getval("sdate", "");
        $this->edate            = $this->getval("edate", "");
        $this->callback         = $this->getval("callback", "");
        $this->action           = $this->getval("action", "");
        $this->search           = $this->getval("search", "");
        $this->test             = $this->getval("test", false);
        $this->setResponseHeader();
        $this->processAction();
    }

    protected function getval($key, $default, $array = null){
        if ($array == null) $array = $_REQUEST;
        $result = $default;
        if (isset($array[$key]))
            $result = $array[$key];
        return $result;
    }

    protected function setResponseHeader(){
        switch($this->format){
            case "xml":
                header("Content-Type: text/xml");
                break;
            case "html":
                header("Content-Type: text/html");
                break;
            case "json":
            case "jsonp":
                header("Content-Type: application/json");
                break;
            default:
                header("Content-Type: text/plain");
                break;
        }
    }

    protected function getUrl(){
        $result = ($_SERVER["HTTPS"] == "off") ? "http://" : "https://";
        $result .= $_SERVER["SERVER_NAME"];
        $result .= dirname($_SERVER["PHP_SELF"]);
        return $result;
    }

    protected function postData(){
        return array(
            "f"                 => $this->format,
            "ticket_id"         => $this->ticket_id,
            "ticketID"          => $this->ticketID,
            "resource_id"       => $this->resource_id,
            "assigned_to"       => $this->assigned_to,
            "unassigned"        => $this->unassigned,
            "email"             => $this->email,
            "name"              => $this->name,
            "queue"             => $this->queue,
            "subject"           => $this->subject,
            "message"           => $this->message,
            "pri"               => $this->pri,
            "priority_desc"     => $this->priority_desc,
            "status"            => $this->status,
            "sdate"             => $this->sdate,
            "edate"             => $this->edate,
            "callback"          => $this->callback,
            "action"            => $this->action,
            "search"            => $this->search,
            "test"              => $this->test
        );
    }

    protected function criteria(){
        if ($this->search == "by-resource"){
            return array(
                "resource_id"   => $this->resource_id,
                "status"        => "open",
                "sdate"         => $this->sdate,
                "edate"         => $this->edate
            );
        }
        else if ($this->search == "by-email"){
            return array(
                "email"     => $this->email,
                "status"    => "open",
                "sdate"     => $this->sdate,
                "edate"     => $this->edate
            );
        }
        else{
            return array(
                "ticket_id"         => $this->ticket_id,
                "ticketID"          => $this->ticketID,
                "resource_id"       => $this->resource_id,
                "assigned_to"       => $this->assigned_to,
                "unassigned"        => $this->unassigned,
                "email"             => $this->email,
                "name"              => $this->name,
                "priority_desc"     => $this->priority_desc,
                "status"            => $this->status,
                "sdate"             => $this->sdate,
                "edate"             => $this->edate
            );
        }
    }

    protected function addTicket(){
        try{
            $var = array(
                "mid"       => "",
                "email"     => $this->ticketEmail(),
                "name"      => $this->ticketName(),
                "emailId"   => $this->ticketQueueEmailId(),
                "subject"   => $this->ticketSubject(),
                "message"   => $this->ticketMessage(),
                "header"    => "",
                "pri"       => $this->ticketPri()
            );
            $this->writeLog("Debug", '/api/data-plugin.php:addTicket:$var', print_r($var, true), "jg");
            $errors = array();
            $result = $this->createTicket($var, $errors, "email");
            $this->assignResourceTicket($result['ticket']);
            return $result;
        }
        catch (Exception $e){
            $this->writeLog("Error", "Error in /api/data-plugin.php:addTicket", $e->getMessage(), "jg");
        }
    }

    protected function outputTicketsByResourceId(){
        try{
            $tickets = $this->selectTickets();
            $this->outputTickets($tickets);
        }
        catch (Exception $e){
            $this->writeLog("Error", "Error in /api/data-plugin.php:outputTicketsByResourceId", $e->getMessage(), "jg");
        }
    }

    protected function outputTicketsByEmail(){
        try{
            $tickets = $this->selectTickets();
            $this->outputTickets($tickets);
        }
        catch (Exception $e){
            $this->writeLog("Error", "Error in /api/data-plugin.php:outputTicketsByEmail", $e->getMessage(), "jg");
        }
    }

    protected function outputTickets($tickets, $message = null){
        switch ($this->format){
            case "json":
            case "jsonp":
                $json = $this->jsonFill($tickets, $message);
                $this->wrapjsonp($json);
                echo $json;
                break;
            default:
                $xml = $this->xmlFill($tickets, $message);
                echo $xml->asXML();
                break;
        }
    }

    protected function outputTicketDetail($detail){
        switch ($this->format){
            case "json":
            case "jsonp":
                $json = json_encode($detail);
                $this->wrapjsonp($json);
                echo $json;
                break;
            default:
                $xml = $this->xmlBase();
                $xml[0] = json_encode($detail);
                echo $xml->asXML();
                break;
        }
    }

    protected function outputSummary($summary){
        switch ($this->format){
            case "json":
            case "jsonp":
                $json = json_encode($summary);
                $this->wrapjsonp($json);
                echo $json;
                break;
            default:
                $xml = $this->xmlBase();
                $xml[0] = json_encode($summary);
                echo $xml->asXML();
                break;
        }
    }

    protected function xmlFill($data, $message = null){
        try{
            $xml = $this->xmlBase();
            $this->addNode("message", $xml, $message);
            if (is_array($data)){
                foreach ($data as $item){
                    if (is_array($item)){
                        $child = $xml->addChild("row");
                        foreach ($item as $k => $v){
                            $this->addNode($k, $child, $item);
                        }
                    }
                }
            }
            return $xml;
        }
        catch (Exception $e){
            $this->writeLog("Error", "Error in /api/data-plugin.php:xmlFill", $e->getMessage(), "jg");
        }
    }

    protected function addNode($key, $child, $item){
        $node = $child->addChild($key);
        $child->$key = $item[$key];
    }

    protected function xmlBase(){
        $xml = new SimpleXMLElement("<data></data>");
        return $xml;
    }

    protected function jsonFill($data, $message = null){
        try{
            $o = new stdClass();
            $o->message = $message;
            $o->tickets = array();
            if (is_array($data)){
                foreach ($data as $item){
                    if (is_array($item)){
                        $ticket = new stdClass();
                        foreach ($item as $k => $v){
                            $ticket->$k = $v;
                        }
                        $o->tickets[] = $ticket;
                    }
                }
            }
            return json_encode($o);
        }
        catch (Exception $e){
            $this->writeLog("Error", "Error in /api/data-plugin.php:jsonFill", $e->getMessage(), "jg");
        }
    }

    protected function wrapjsonp(&$json){
        if ($this->format != "jsonp") return;
        if ($this->callback == "") return;
        $json = $this->callback."($json)";
    }

    protected function where($criteria = null){
        if ($criteria == null) $criteria = $this->criteria();
        $ticket_id = db_real_escape($this->getval("ticket_id", 0, $criteria));
        $ticketID = db_real_escape($this->getval("ticketID", 0, $criteria));
        $resourceId = db_real_escape($this->getval("resource_id", 0, $criteria));
        $assignedTo = db_real_escape($this->getval("assigned_to", "", $criteria));
        $unassigned = db_real_escape($this->getval("unassigned", "", $criteria));
        $email = db_real_escape($this->getval("email", "", $criteria));
        $name = db_real_escape($this->getval("name", "", $criteria));
        $status = db_real_escape($this->getval("status", "", $criteria));
        $sdate = db_real_escape($this->getval("sdate", "", $criteria));
        $edate = db_real_escape($this->getval("edate", "", $criteria));
        $priorityDesc = db_real_escape($this->getval("priority_desc", "", $criteria));
        $result = "";
        $and = " WHERE";
        if (is_numeric($ticket_id) && $ticket_id > 0){
            $result .= "$and t.ticket_id = ".(int)$ticket_id;
            $and = " AND";
        }
        if (is_numeric($ticketID) && $ticketID > 0){
            $result .= "$and t.ticketID = ".(int)$ticketID;
            $and = " AND";
        }
        if (is_numeric($resourceId) && $resourceId > 0){
            $result .= "$and tr.resource_id = ".(int)$resourceId;
            $and = " AND";
        }
        if (is_string($assignedTo) && $assignedTo != ""){
            $result .= "$and CONCAT(s.lastname, ', ', s.firstname) LIKE '$assignedTo'";
            $and = " AND";
        }
        if (is_string($unassigned) && $unassigned == "1"){
            $result .= "$and t.staff_id = 0";
            $and = " AND";
        }
        if (is_string($email) && $email != ""){
            $result .= "$and t.email LIKE '$email'";
            $and = " AND";
        }
        if (is_string($name) && $name != ""){
            $result .= "$and t.name LIKE '$name'";
            $and = " AND";
        }
        if (is_string($status) && $status != ""){
            $result .= "$and t.status = '$status'";
            $and = " AND";
        }
        if (is_string($priorityDesc) && $priorityDesc != ""){
            $result .= "$and tp.priority_desc LIKE '$priorityDesc'";
            $and = " AND";
        }
        $result .= $this->whereDateRange($and, "t.created", $sdate, $edate);
        return $result;
    }

    protected function whereDateRange($and, $column, $sdate, $edate){
        $and = trim($and);
        $result = "";
        $result .= (!empty($sdate)) ? " $and $column >= '".db_real_escape($sdate)."'" : "";
        if (!empty($edate)){
            $result .= (!empty($result)) ? " AND" : " $and";
            $result .= " $column < '".db_real_escape($edate)."'";
        }
        return $result;
    }

    protected function execQuery($sql){
        try{
            $result = db_assoc_array(db_query($sql));
            return $result;
        }
        catch (Exception $e){
            die(print_r($e, true));
        }
    }
}
?>
