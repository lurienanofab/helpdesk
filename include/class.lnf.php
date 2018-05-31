<?php
//A class created for LNF special requirements.

//This class is included in /helpdesk/main.inc.php.

//When the url is /helpdesk/scp the include chain is:
//  /helpdesk/scp/index.php
//      /helpdesk/scp/tickets.php
//          /helpdesk/scp/staff.inc.php
//              /helpdesk/main.inc.php
//                  /helpdesk/include/class.lnf.php

define('TICKET_RESOURCE_TABLE',TABLE_PREFIX.'ticket_resource');

//$sqlsrv = sqlsrv_connect('192.168.168.200,1435', array('Database' => 'sselData', 'UID' => 'webuser', 'PWD' => 'lnf$web'));
//if ($sqlsrv === false) die(print_r(sqlsrv_errors(), false));

$resources = null;

class LNF{
    private $email;
    private $ticketID;
    private $requestedTicketID;
    private $valid;
    
    public function __get($name){
        switch ($name){
            case "email":
                return $this->email;
                break;
            case "ticketID":
                return $this->ticketID;
                break;
            case "valid":
                return $this->valid;
                break;
            default:
                die("Invalid property.");
                break;
        }
    }
    
    public static function Get($key){
        $result = LNF::getval($_GET, $key, "");
        return $result;
    }
    
    public static function Post($key){
        $result = LNF::getval($_POST, $key, "");
        return $result;
    }
    
    public static function Request($key){
        $result = LNF::getval($_REQUEST, $key, "");
        return $result;
    }
    
    public static function getval($array, $key, $default = null){
        $result = $default;
        
        if (is_array($array)){
            if (is_array($key)){
                //return first non-null value
                foreach($key as $k){
                    if (isset($array[$k])){
                        $result = $array[$k];
                        break;
                    }
                }
            }
            else{
                if (isset($array[$key]))
                    $result = $array[$key];
            }
        }
        
        return $result;
    }
    
    public static function Log($type, $title, $log, $logger){
        db_query(
            "INSERT ost_syslog_lnf (log_type, title, log, logger, ip_address, created, updated) VALUES (".
            "'".db_input($type)."'".
            ", '".db_input($title)."'".
            ", '".db_input($log)."'".
            ", '".db_input($logger)."'".
            ", '".LNF::GetIP()."', NOW(), NOW())"
        );
    }
    
    /*http://roshanbh.com.np/2007/12/getting-real-ip-address-in-php.html*/
    public static function GetIP(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
            //check ip from share internet
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            //to check ip is pass from proxy
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else{
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    public function ClientLogin(){
        $this->valid = false;
        
        $email = LNF::Post("lemail");
        $ticketID = LNF::Post("lticket");
        
        if (!Validator::is_email($email)) return false;
        
        $this->requestedTicketID = $ticketID;
        if ($ticketID == ""){
            $sql = "SELECT ticketID FROM ".TICKET_TABLE." WHERE email = '$email' LIMIT 1";
            if (($rs = db_query($sql)) && db_num_rows($rs) && ($row = db_fetch_row($rs))){
                $ticketID = $row[0];
            }
        }
        
        if ($ticketID == "") return false;
        
        $this->email = $email;
        $this->ticketID = $ticketID;
        
        $this->valid = true;
        return true;
    }
    
    public function LoginRedirect(){
        if ($this->valid){
            if ($this->requestedTicketID == ""){
                header("Location: tickets.php");
            }
            else{
                header("Location: view.php?id=".$this->requestedTicketID);
            }
        }
        else{
            header("Location: index.php");
        }
    }
    
    public static function sendClientEmailOnAssign($ticket){
        global $cfg;
        
        //notify the client
        
        if(!($email=$cfg->getAlertEmail()))
            $email=$cfg->getDefaultEmail();

        if($email){
            $staff = $ticket->getStaff();
            $eol = "\n";
            $subj = "Support Ticket Assigned [#".$ticket->getExtId()."]";
            $body = $ticket->getName().",".$eol.$eol."Your support ticket has been assigned to ".$staff->getName()." (".$staff->getEmail().")".$eol.$eol."You can view this ticket's progress online here: ".$cfg->getUrl()."view.php?e=".$ticket->getEmail()."&t=".$ticket->getExtId().".";
            $email->send($ticket->getEmail(), $subj, $body);   
        }
    }
    
    public static function parseSubject($ticket){
      $resourceID = null;
      $ticketType = "";
      $ticketSubj = "";
      //[sample subject] ResourceID : 40000 | Type : General Question | this is the subject
      
      $split = explode('|', $ticket->getSubject());
      if (count($split) == 3){
        //get the ResourceID
        $parts = explode(':', trim($split[0]));
        if (count($parts) > 0){
          if (trim($parts[0]) == "ResourceID"){
            $resourceID = (int)trim($parts[1]);
          }
        }
        
        //get the ticket type
        $parts = explode(':', trim($split[1]));
        if (count($parts)>0){
          if (trim($parts[0]) == "Type"){
            $ticketType = trim($parts[1]);
          }
        }
        
        //get the actual subject
        $ticketSubj = trim($split[2]);
      }
      
      self::assignResourceTicket($ticket, $resourceID, $ticketType, $ticketSubj);
   }
   
   public static function assignResourceTicket($ticket, $resourceId, $ticketType = null, $ticketSubj = null){
    if ($resourceId){
      $sql = "SELECT * FROM ".TICKET_RESOURCE_TABLE." WHERE ticket_id = ".$ticket->getId()." AND resource_id = $resourceId";
      if(($records=db_query($sql)) && db_num_rows($records)){
        //ticket already associated with resource
        return;
      }

      $sql = "INSERT ".TICKET_RESOURCE_TABLE." (ticket_id, resource_id) VALUES (".$ticket->getId().", ".$resourceId.")";
      if (!db_query($sql)) return; //if we can't add to ost_ticket_resource for some reason then bail

      if (isset($ticketSubj)){
        if (strlen($ticketSubj)>0){
          $subj = db_input($ticketSubj);
          $sql = "UPDATE ".TICKET_TABLE." SET subject = '$subj' WHERE ticket_id = ".$ticket->getId();
          db_query($sql);
        }
      }
      
      if (isset($ticketType)){
        switch($ticketType){
          case "General Question":
            $ticket->setPriority(1); //low
            break;
          case "Process Issue":
            $ticket->setPriority(2); //normal
            break;
          case "Hardware Issue":
            $ticket->setPriority(3); //high
            break;
        }
      }
    }
  }
   
    public static function tweet($ticket, $action){
        return;
        $status = "";
        switch ($action){
            case 'assign':
                $status = 'A ticket has been assigned to me.';
            break;
        }
        if ($status != ""){
            $data = array('status' => $status);
            $response = LNF::send_post_request('http://api.twitter.com/1/statuses/update.format ', $data);
        }
    }
   
    public static function selectTicketsByEmail($email, $status = 'open', $sdate = '', $edate = ''){
        $email = db_input($email);
        $status = (empty($status)) ? "NULL" : "'$status'";
        $sql = "SELECT t.*, IFNULL(tr.resource_id, 0) AS 'resource_id', tp.priority_desc, tp.priority_urgency, CONCAT(s.lastname, ', ', s.firstname) AS 'assigned_to' FROM ".TICKET_TABLE." t LEFT JOIN ".TICKET_RESOURCE_TABLE." tr ON t.ticket_id = tr.ticket_id INNER JOIN ".TICKET_PRIORITY_TABLE." tp ON tp.priority_id = t.priority_id LEFT JOIN ".STAFF_TABLE." s ON s.staff_id = t.staff_id WHERE t.email LIKE '$email' AND t.status = IFNULL($status, t.status)".LNF::whereDateRange('AND', 't.created', $sdate, $edate)." ORDER BY t.created ASC";
        $result = db_assoc_array(db_query($sql));
        return $result;
    }
   
    public static function selectTicketsByResourceId($resourceId, $status = 'open', $sdate = '', $edate = ''){
        $rid = (empty($resourceId)) ? "NULL" : $resourceId;
        $status = (empty($status)) ? "NULL" : "'$status'";
        $sql = "SELECT t.*, IFNULL(tr.resource_id, 0) AS 'resource_id', tp.priority_desc, tp.priority_urgency, CONCAT(s.lastname, ', ', s.firstname) AS 'assigned_to' FROM ".TICKET_TABLE." t LEFT JOIN ".TICKET_RESOURCE_TABLE." tr ON t.ticket_id = tr.ticket_id INNER JOIN ".TICKET_PRIORITY_TABLE." tp ON tp.priority_id = t.priority_id LEFT JOIN ".STAFF_TABLE." s ON s.staff_id = t.staff_id WHERE IFNULL(tr.resource_id, 0) = IFNULL($rid, IFNULL(tr.resource_id, 0)) AND t.status = IFNULL($status, t.status)".LNF::whereDateRange('AND', 't.created', $sdate, $edate)." ORDER BY t.created ASC";
        $result = db_assoc_array(db_query($sql));
        return $result;
    }
   
    public static function selectTickets($criteria){
        $sql = "SELECT t.*, IFNULL(tr.resource_id, 0) AS 'resource_id', tp.priority_desc, tp.priority_urgency, CONCAT(s.lastname, ', ', s.firstname) AS 'assigned_to' FROM ".TICKET_TABLE." t LEFT JOIN ".TICKET_RESOURCE_TABLE." tr ON t.ticket_id = tr.ticket_id INNER JOIN ".TICKET_PRIORITY_TABLE." tp ON tp.priority_id = t.priority_id LEFT JOIN ".STAFF_TABLE." s ON s.staff_id = t.staff_id";
        $sql .= LNF::where($criteria);
        return LNF::execQuery($sql);
    }
   
    public static function selectTicketDetail($criteria){
        $sql = "SELECT t.*, IFNULL(tr.resource_id, 0) AS 'resource_id', tp.priority_desc, tp.priority_urgency, CONCAT(s.lastname, ', ', s.firstname) AS 'assigned_to', tm.msg_id, tm.title, tm.message, tm.message_created, tm.message_source, tm.message_type FROM ".TICKET_TABLE." t LEFT JOIN ".TICKET_RESOURCE_TABLE." tr ON t.ticket_id = tr.ticket_id INNER JOIN ".TICKET_PRIORITY_TABLE." tp ON tp.priority_id = t.priority_id LEFT JOIN ".STAFF_TABLE." s ON s.staff_id = t.staff_id LEFT JOIN (SELECT m.ticket_id, m.msg_id, NULL AS 'title', m.message, m.created AS 'message_created', m.`source` AS 'message_source', 'message' AS 'message_type' FROM ".TICKET_MESSAGE_TABLE." m UNION ALL SELECT n.ticket_id, n.note_id, n.title, n.note, n.created, n.`source`, 'note' FROM ".TICKET_NOTE_TABLE." n) tm ON tm.ticket_id = t.ticket_id";
        $sql .= LNF::where($criteria);
        return LNF::execQuery($sql);
    }
   
    public static function where($criteria){
        $ticketId = db_input(LNF::getval($criteria, 'ticket_id', 0));
        $resourceId = db_input(LNF::getval($criteria, 'resource_id', 0));
        $assignedTo = db_input(LNF::getval($criteria, 'assigned_to', ''));
        $unassigned = db_input(LNF::getval($criteria, 'unassigned', ''));
        $email = db_input(LNF::getval($criteria, 'email', ''));
        $name = db_input(LNF::getval($criteria, 'name', ''));
        $status = db_input(LNF::getval($criteria, 'status', ''));
        $sdate = db_input(LNF::getval($criteria, 'sdate', ''));
        $edate = db_input(LNF::getval($criteria, 'edate', ''));
        $priorityDesc = db_input(LNF::getval($criteria, 'priority_desc', ''));
        $result = '';
        $and = ' WHERE';
        if (is_numeric($ticketId) && $ticketId > 0){
            $result .= "$and t.ticketID = ".(int)$ticketId;
            $and = ' AND';
        }
        if (is_numeric($resourceId) && $resourceId > 0){
            $result .= "$and tr.resource_id = ".(int)$resourceId;
            $and = ' AND';
        }
        if (is_string($assignedTo) && $assignedTo != ''){
            $result .= "$and CONCAT(s.lastname, ', ', s.firstname) LIKE '$assignedTo'";
            $and = ' AND';
        }
        if (is_string($unassigned) && $unassigned == '1'){
            $result .= "$and t.staff_id = 0";
            $and = ' AND';
        }
        if (is_string($email) && $email != ''){
            $result .= "$and t.email LIKE '$email'";
            $and = ' AND';
        }
        if (is_string($name) && $name != ''){
            $result .= "$and t.name LIKE '$name'";
            $and = ' AND';
        }
        if (is_string($status) && $status != ''){
            $result .= "$and t.status = '$status'";
            $and = ' AND';
        }
        if (is_string($priorityDesc) && $priorityDesc != ''){
            $result .= "$and tp.priority_desc LIKE '$priorityDesc'";
            $and = ' AND';
        }
        $result .= LNF::whereDateRange($and, 't.created', $sdate, $edate);
        return $result;
    }
   
    public static function execQuery($sql){
        $result = db_assoc_array(db_query($sql));
        return $result;
    }
   
    public static function tickets_by_ticket_number($ticket_number){
        $tnum = ($ticket_number) ? $ticket_number : "-1";
        $sql = "SELECT t.*, IFNULL(tr.resource_id, 0) AS 'resource_id', tp.priority_desc, tp.priority_urgency, CONCAT(s.lastname, ', ', s.firstname) AS 'assigned_to' FROM ".TICKET_TABLE." t LEFT JOIN ".TICKET_RESOURCE_TABLE." tr ON t.ticket_id = tr.ticket_id INNER JOIN ".TICKET_PRIORITY_TABLE." tp ON tp.priority_id = t.priority_id LEFT JOIN ".STAFF_TABLE." s ON s.staff_id = t.staff_id WHERE t.ticketID = $tnum AND t.status = 'open'".LNF::whereDateRange('AND', 't.created', $sdate, $edate)." ORDER BY t.created ASC";
        $result = db_assoc_array(db_query($sql));
        return $result;
    }
   
   private static function whereDateRange($and, $column, $sdate, $edate){
        $and = trim($and);
        $result = '';
        $result .= (!empty($sdate)) ? " $and $column >= '".db_input($sdate)."'" : '';
        if (!empty($edate)){
            $result .= (!empty($result)) ? ' AND' : " $and";
            $result .= " $column < '".db_input($edate)."'";
        }
        return $result;
   }
   
    public static function jslog($msg){
        echo '<script type="text/javascript">';
        echo 'if (console) console.log("'.str_replace('"', '\"', $msg).'");';
        echo '</script>';
    }
   
    public static function send_post_request($url, $data, $optional_headers = null){
        $params = array('http' => array(
            'method'    => 'post',
            'content'   => $data
        ));

        if ($optional_headers !== null) 
            $params['http']['header'] = $optional_headers;

        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);

        if (!$fp)
            throw new Exception("Problem with $url, $php_errormsg");

        $response = @stream_get_contents($fp);

        if ($response === false)
            throw new Exception("Problem reading data from $url, $php_errormsg");

        return $response;
    }
  
    public static function find_resource($id){
        $resources = self::select_resources();
        return '&nbsp;';
    }
  
    public static function select_resources(){
        //return simplexml_load_file('http://192.168.168.200/data/feed/?g=7d0e0ffc-6096-4107-a57c-b768cb92fa00&f=xml');
        return simplexml_load_file('http://ssel-apps.eecs.umich.edu/data/feed/tool-list/xml');
    }
  
    public static function exec($sql, $params = null){
        global $sqlsrv;
        return sqlsrv_query($sqlsrv, $sql, $params);
    }
  
    public static function selfAssign($user, $ticket, $post){
        if (isset($post['self_assign']) && $post['self_assign'] == 'on')
            $ticket->assignStaff($user->udata['staff_id'], 'Assigned to self.');
    }
  
    public static function getDeptMembership($row){
        $staff_id = ($row) ? $row['staff_id'] : 0;
        $sql = 'SELECT s.group_id, s.dept_id, g.dept_access FROM '.STAFF_TABLE.' s INNER JOIN '.GROUP_TABLE.' g ON g.group_id = s.group_id WHERE s.staff_id = '.$staff_id;
        $rows = self::execQuery($sql);
        $membership = array();
        if (count($rows) > 0){
            $r = $rows[0]; //there should only be one
            $deptNames = self::getDeptNames($r['dept_id']);
            foreach ($deptNames as $staffDept){
                $membership[] = array(
                    'dept_id' => $staffDept['dept_id'],
                    'dept_name' => $staffDept['dept_name'],
                    'from' => 'staff'
                );
            }
            if ($r['dept_access'] != ''){
                $deptNames = self::getDeptNames($r['dept_access']);
                foreach ($deptNames as $groupDept){
                    $membership[] = array(
                        'dept_id' => $groupDept['dept_id'],
                        'dept_name' => $groupDept['dept_name'],
                        'from' => 'group'
                    );
                }
            }
        }
        $result = '';
        if ($row != null){
            $br = '';
            foreach ($membership as $dept){
                if ($dept['from'] == 'staff')
                    $result .= '<span class="dept-name-staff" style="font-weight: normal;">'.$dept['dept_name'].'</span><div class="group-depts" style="display: none;">';
                else{
                    $result .= $br.'<span class="dept-name-group" style="font-style: italic;">'.$dept['dept_name'].'</span>';
                    $br = '<br />';
                }
            }
            $result .= '</div>';
        }
        else
            $result .= '<div style="font-style: italic; color: #808080;">No departments found.</div>';
        return $result;
    }
    
    public static function getDepts($dept_id = null){
        if ($dept_id == null){
            //original query: 'SELECT dept_id,dept_name FROM '.DEPT_TABLE
            return db_query('SELECT dept_id, dept_name FROM '.DEPT_TABLE.' ORDER BY dept_name');
        }
        else{
            //original query: 'SELECT dept_id,dept_name FROM '.DEPT_TABLE.' WHERE dept_id!='.db_input($ticket->getDeptId())
            return db_query('SELECT dept_id, dept_name FROM '.DEPT_TABLE.' WHERE dept_id != '.db_input($dept_id).' ORDER BY dept_name');
        }
    }
    
    public static function getDeptNames($dept_id = null){
        if ($dept_id == null){
            //original query: ''SELECT dept_id,dept_name FROM '.DEPT_TABLE
            return db_query('SELECT dept_id, dept_name FROM '.DEPT_TABLE.' ORDER BY dept_name');
        }
        else
            return self::execQuery('SELECT dept_id, dept_name FROM '.DEPT_TABLE.' WHERE dept_id IN ('.$dept_id.') ORDER BY dept_name');
    }
    
    public static function getLnfUser(&$refresh){
        $result = null;
        $refresh = false;
        if (($userCheck = json_decode(self::userCheck())) !== false){
            if ($userCheck->authenticated){
                $result = $userCheck->username;
                $refresh = true;
            }
        }
        return $result;
    }
    
    public static function userCheck(){
        global $_COOKIE;
        $token = (isset($_COOKIE['sselAuth_cookie'])) ? $_COOKIE['sselAuth_cookie'] : '';
        //$token='83A21D46B32E178114F7B4E2BC3E65EEB3B20E6302C43F42CEF94BF3AAC6861FCD77F38CECC02A41114EF2AFB165AA97B5BB6525A55393A36291DE45BAE18635BFB72E3CB6A848BA3F9DD071D0535C0BD00AC5B152287998CFF065AAB46D296DC9252D622D3229D3BA10061F2C7CD86B3FE81A3D19C600E6F6D95F601BAC1A425070B12FBE816C12321E032862A8AD3691BC2097655BA18CC8DA359A3B191BA05A7D026F8F05D6D8719974D0BE2A8E9CE75457A51EAEFC3648F897804F8B2A64B21EEAB02638BAB316B3A0BBF4271F845002CE9FE48CE38DA4823F23364039974FB15D412CA623F7AF9BB3B16103B109374A9F066BB64F4ED7A94422AADA02901CE86D19153EA895B7D035C943432D84100F02BA4574A693D334061D80756D3F9468A2B7CEA49D3C912E26064FB1684A1BA536E0B978048D751CF13D';
        $content = self::curlPost('http://ssel-sched.eecs.umich.edu/login/authcheck', array('cookieValue'=>$token));
        //self::dumpvar($content);
        return $content;
    }
    
    public static function curlPost($url, $data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        return $result;
    }
    
    public static function dumpvar($var){
        echo '---<pre>';
        print_r($var);
        echo '<pre>---';
        die();
    }
    
    public static function overwriteStaffTimezonePreference($var){
        //2011-02-03 [jg]: Added a checkbox to Settings -> Preferences -> Date & Time. If checked, changes all user tz preferences to system tz
        if (isset($var['set_staff_tz_pref'])){
            $sql = 'UPDATE ost_staff SET timezone_offset = (SELECT timezone_offset FROM ost_config WHERE id = '. $this->getId() .');';
            db_query($sql);
        }
    }
    
    public static function addGroupMemberRecipients(&$recipients, $dept){
        //added so staff with dept access through groups get an alert
        $sql = "SELECT s.staff_id FROM ".GROUP_TABLE." g INNER JOIN ".STAFF_TABLE." s ON s.group_id = g.group_id WHERE CONCAT(',', g.dept_access, ',') LIKE '%,".db_input($dept->getId()).",%' AND g.group_new_ticket_alerts = 1 and g.group_enabled = 1";
        if (($users=db_query($sql)) && db_num_rows($users)){
            while(list($id)=db_fetch_row($users))
                $recipients[]= new Staff($id);
        }
    }
    
    public static function getTicketResponse($resp, $ticket){
        $respID = $resp['response_id'];
        $result = Format::display($resp['response']);
        list($attachments, $hasimg, $imglist) = self::getTicketAttachments($ticket, $respID, 'R');
        $result .= ($hasimg) ? '<div class="inline-attachment"><div class="label">Attachments</div>'.$imglist.'</div>' : '';
        return $result;
    }
    
    public static function getTicketAttachments($ticket, $respID, $type){
        global $cfg;
        $uploadDir = $cfg->getUploadDir(); 
        $root = substr(ROOT_DIR, 0, -1);
        if (strpos($uploadDir, $root) === false)
            return array(null, false, null);
        $dir = str_replace($root, "", $uploadDir);
        $my = date('my', strtotime($ticket->getCreateDate()));
        $baseurl = $cfg->getUrl().substr($dir, 1)."/$my";
        
        $sql ='SELECT attach_id,file_size,file_name,file_key FROM '.TICKET_ATTACHMENT_TABLE.
            ' WHERE deleted=0 AND ticket_id='.db_input($ticket->getId()).' AND ref_id='.db_input($respID).' AND ref_type='.db_input($type);
        $res=db_query($sql);
        $hasimg=false;
        $imglist="";
        $result=null;
        if($res && db_num_rows($res)){
            $result=array();
            while(list($id,$size,$name,$key)=db_fetch_row($res)){
                $temp=array();
                $temp["attachstr"]="";
                $temp["fileName"] = "$uploadDir\\$my\\{$key}_{$name}";
                $temp["hash"]=MD5($ticket->getId()*$respID.session_id());
                $temp["size"]=Format::file_size($size);
                $temp["name"]=Format::htmlchars($name);
                $temp["key"]=Format::htmlchars($key);
                $temp["attachstr"].= "<a class='Icon file' href='attachment.php?id=$id&ref=$hash' target='_blank'><b>$name</b></a>&nbsp;(<i>$size</i>)&nbsp;&nbsp;";
                $temp["url"]=$baseurl."/$key".'_'.$name;
                $temp["ext"]=substr($name,-3);
                if (in_array($temp["ext"], array("jpg","gif","png"))){
                    $hasimg = true;
                    $style = '';
                    if (function_exists('getimagesize')){
                        $size = getimagesize($temp['fileName']);
                        if ($size){
                            $width = $size[0];
                            $style = ($width > 200) ? ' style="width: 200px;"' : '';
                        }
                    }
                    $imglist.='<div class="image"><a href="'.$temp["url"].'"><img src="'.$temp["url"].'" border="0"'.$style.' /></a></div>';
                }

                $result[] = $temp;
            }
        }
        return array($result, $hasimg, $imglist);
    }
    
    public static function getDeptEmails($info = null){
        if ($info == null){
            //original query: 'SELECT email_id,email,name,smtp_active FROM '.EMAIL_TABLE
            return db_query('SELECT email_id, email, name, smtp_active FROM '.EMAIL_TABLE.' ORDER BY name');
        }
        else{
            //original query: 'SELECT email_id,email,name,smtp_active FROM '.EMAIL_TABLE.' WHERE email_id!='.db_input($info['email_id'])
            return db_query('SELECT email_id, email, name, smtp_active FROM '.EMAIL_TABLE.' WHERE email_id != '.db_input($info['email_id']).' ORDER BY name');
        }
    }
    
    public static function getDeptUsers(){
        global $info;
        //original query: 'SELECT staff_id,CONCAT_WS(" ",firstname,lastname) as name FROM '.STAFF_TABLE.' WHERE dept_id='.db_input($info['dept_id'])
        return db_query('SELECT staff_id, CONCAT_WS(" ", firstname, lastname) AS `name` FROM '.STAFF_TABLE.' WHERE dept_id = '.db_input($info['dept_id']).' OR group_id IN (SELECT group_id FROM '.GROUP_TABLE.' WHERE CONCAT(",", dept_access, ",") LIKE "%,'.db_input($info['dept_id']).',%") ORDER BY firstname, lastname');
    }
    
    public static function getDeptTemplates(){
        global $cfg;
        //original query: 'SELECT tpl_id,name FROM '.EMAIL_TEMPLATE_TABLE.' WHERE tpl_id!='.db_input($cfg->getDefaultTemplateId())
        return db_query('SELECT tpl_id, name FROM '.EMAIL_TEMPLATE_TABLE.' WHERE tpl_id != '.db_input($cfg->getDefaultTemplateId()).' ORDER BY name');
    }
    
    public static function getGroups(){
        //original query: 'SELECT group_id,group_name FROM '.GROUP_TABLE
        return db_query('SELECT group_id, group_name FROM '.GROUP_TABLE.' ORDER BY group_name');
    }
    
    public static function getPriorities(){
        //original query: 'SELECT priority_id,priority_desc FROM '.TICKET_PRIORITY_TABLE
        return db_query('SELECT priority_id,priority_desc FROM '.TICKET_PRIORITY_TABLE.' ORDER BY priority_urgency DESC');
    }
    
    public static function getDeptStaff($ticket){
        $sql = "SELECT staff.staff_id, CONCAT_WS(', ', staff.lastname, staff.firstname) AS 'name' FROM ".STAFF_TABLE." staff ".
            "LEFT JOIN ".GROUP_TABLE." groups ON groups.group_id = staff.group_id ".
            "WHERE staff.isactive = 1 AND staff.onvacation = 0 AND (staff.dept_id = ".$ticket->getDeptId().
            " OR IFNULL(groups.dept_access, '') LIKE '".$ticket->getDeptId().",%'".
            " OR IFNULL(groups.dept_access, '') LIKE '%,".$ticket->getDeptId().",%'".
            " OR IFNULL(groups.dept_access, '') LIKE '%,".$ticket->getDeptId()."'".
            " OR IFNULL(groups.dept_access, '') = '".$ticket->getDeptId()."')";
        if($ticket->isAssigned()) 
            $sql .= ' AND staff.staff_id != '.db_input($ticket->getStaffId());
        return db_query($sql.' ORDER BY staff.lastname, staff.firstname');
    }
    
    public static function printToolSelectForTicketList(){
        global $_REQUEST;
        echo '<div style="margin-bottom: 4px;">';
        echo 'Tool:';
        echo '<input type="hidden" class="selected-tool" value="'.$_REQUEST['tool'].'" />';
        echo '<span class="tool-select-container"><i style="font-weight: bold; color: #808080;">Loading..</i><select style="visibility: hidden;" name="tool" class="tool-select"></select></span>';
        echo '</div>';
    }
    
    public static function getSortOptions($status){
        //added staff to sort by assigned staff name, and resource_id [jg]
        return array(
            'date'=>(strtolower($status) == 'closed') ? 'ticket.closed' : 'ticket.created',
            'ID'=>'ticketID',
            'pri'=>'priority_urgency',
            'dept'=>'dept_name',
            'staff'=>'staff_name',
            'resource'=>'resource_id'
        );
    }
    
    public static function customizeQueryForTicketListBeforeTotal(&$qselect, &$qfrom, &$qwhere, &$qstr, $search, $startTime, $endTime, $status){
        global $_REQUEST;
        
        //pre total customizations
        if ($search){
            //tool [added by jg on 3/29/2013]
            if ($_REQUEST['tool']){
                $qwhere .= ' AND res.resource_id='.db_input($_REQUEST['tool']);
                $qstr .= '&tool='.urlencode($_REQUEST['tool']);
            }
            //date [use closed date when status is closed]
            $orig = $temp = '';
            if ($startTime){
                $orig .= ' AND ticket.created>=FROM_UNIXTIME('.$startTime.')';
                if (strtolower($status) != 'closed')
                    $temp .= ' AND ticket.created>=FROM_UNIXTIME('.$startTime.')';
                else
                    $temp .= ' AND ticket.closed>=FROM_UNIXTIME('.$startTime.')';
            }
            if ($endTime){
                $orig .= ' AND ticket.created<=FROM_UNIXTIME('.$endTime.')';
                if (strtolower($status) != 'closed')
                    $temp .= ' AND ticket.created<=FROM_UNIXTIME('.$endTime.')';
                else
                    $temp .= ' AND ticket.closed<=FROM_UNIXTIME('.$endTime.')';
            }
            $qwhere = str_replace($orig, $temp, $qwhere);
        }
        //add table prefix to email because two different email columns will be used
        $qselect = str_replace(',email,dept_name', ',ticket.email,dept_name', $qselect);
        $qselect .= ',CASE WHEN status = \'closed\' THEN (SELECT sub.staff_id FROM ost_ticket_note sub WHERE sub.ticket_id = ticket.ticket_id AND sub.source=\'system\' AND sub.title = \'Ticket status changed to Closed\' LIMIT 1) ELSE 0 END AS closed_staff_id,ticket.closed';
        //added to get assigned staff name [jg]
        $qfrom .= ' LEFT JOIN '.STAFF_TABLE.' staff ON ticket.staff_id=staff.staff_id ';
        $qfrom .= ' LEFT JOIN '.STAFF_TABLE.' closed_staff ON (CASE WHEN status = \'closed\' THEN (SELECT sub.staff_id FROM ost_ticket_note sub WHERE sub.ticket_id = ticket.ticket_id AND sub.source=\'system\' AND sub.title = \'Ticket status changed to Closed\' LIMIT 1) ELSE 0 END)=closed_staff.staff_id';
        //added to get assigned resource name [jg]
        $qfrom .= ' LEFT JOIN '.TICKET_RESOURCE_TABLE.' res ON ticket.ticket_id=res.ticket_id';
    }
    
    public static function customizeQueryForTicketListAfterTotal(&$qselect, &$qfrom, &$qwhere, &$qstr, $search, $startTime, $endTime, $status){
        //post total customizations
        //add assigned staff name [jg]
        $qselect .= " ,IFNULL(CONCAT(staff.lastname, ', ', staff.firstname), '&nbsp;') AS staff_name";
        $qselect .= " ,IFNULL(CONCAT(closed_staff.lastname, ', ', closed_staff.firstname), '&nbsp;') AS closed_staff_name";
        //add assigned resource [jg]
        $qselect .= " ,res.resource_id";
    }
    
    public static function getResourceIdForTicket($row){
        $result = '&nbsp;';
        if ($row['resource_id'])
            if ($row['resouce_id'] != '0')
                $result = '<span style="font-style: italic; color: #a0a0a0;">'.$row['resource_id'].'</span>';
        return $result;
    }
    
    public static function getResourceNameForTicket($row){
        global $resources;
        if ($resources == null)
            $resources = new lnf_resources();
        if ($row['resource_id'])
            return $resources->find($row['resource_id'])->ResourceName;
        else
            return '&nbsp;';
    }
    
    public static function getStaffNameForTicket($row, $status){
        if ($status == 'closed')
            return $row['closed_staff_name'];
        else
            return $row['staff_name'];
    }
    
    public static function getDateForTicket($row, $status){
        if (strtolower($status)=='closed')
            return Format::db_date($row['closed']);
        else
            return Format::db_date($row['created']);
    }
    
    public static function printSelfAssignCheckbox($ticket){
        if(!$ticket->isAssigned())
            echo '<label><input type="checkbox" name="self_assign" /> Assign to Self</label>';
    }
    
    public static function defaultStartDate(){
        //first day of previous month
        $today = getdate();
        $fom = $today['year'].'-'.$today['mon'].'-1';
        return date('Y-m-d', strtotime("$fom -1 month"));
    }
    
    public static function getNavigationSubMenu(){
        $sdate = self::defaultStartDate();
        return array(
            'desc'=>'Closed Tickets',
            'title'=>'Closed Tickets',
            'href'=>'tickets.php?a=search&status=closed&startDate='.urlencode($sdate).'&advance_search=Search',
            'iconclass'=>'closedTickets'
        );
    }
    
    public static function summary($resources){
        $sql = "SELECT tr.resource_id, tp.priority_urgency, tp.priority_desc, COUNT(*) AS 'ticket_count' FROM ".TICKET_TABLE." t"
            ." INNER JOIN ".TICKET_PRIORITY_TABLE." tp ON tp.priority_id = t.priority_id"
            ." INNER JOIN ".TICKET_RESOURCE_TABLE." tr ON tr.ticket_id = t.ticket_id"
            ." WHERE tr.resource_id IN ($resources) AND t.status IN ('open') GROUP BY tr.resource_id, tp.priority_urgency, tp.priority_desc";
        $result = db_assoc_array(db_query($sql));
        return $result;
    }
    
    public static function getStaleTicketCount($user, $numdays = 5){
        $sql = "SELECT COUNT(*) AS 'stale_ticket_count' FROM ".TICKET_TABLE." WHERE staff_id = ".$user->getId()." AND `status` = 'open' AND DATEDIFF(NOW(), lastresponse) > ".$numdays;
        $result = db_assoc_array(db_query($sql));
        return $result[0]["stale_ticket_count"];
    }
    
    public static function getOverdueTicketCount($user){
        $sql = "SELECT COUNT(*) AS 'overdue_ticket_count' FROM ".TICKET_TABLE." WHERE staff_id = ".$user->getId()." AND `status` = 'open' AND isoverdue = 1";
        $result = db_assoc_array(db_query($sql));
        return $result[0]["overdue_ticket_count"];
    }
}
/*===== end LNF =====*/

class LnfStaff extends Staff{

  var $lnf_passwd;
  
  function lookup($var){
  
    //do the normal staff database load
    parent::lookup($var);
    
    //try to get a password from the LNF OnLine Services database
    //$sql = sprintf("SELECT PasswordHash FROM dbo.Client c WHERE c.UserName = '%s'", $var);
    //$res = LNF::exec($sql);
    //if (($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) !== false){
    //  $this->lnf_passwd = $row['PasswordHash'];
    //}
    
    return($this->id);
  }
  
  /*compares user password - both the osticket and lnf online services passwords are checked*/
  function check_passwd($password){
    $salt = 'NiNnPaSs';
    $valid = parent::check_passwd($password);
    $valid = $valid || (strlen($this->lnf_passwd) && strcmp($this->lnf_passwd, MD5($salt.$password))==0)?(TRUE):(FALSE);
    return $valid;
  }
  
}

class lnf_resources
{
    private $xml;

    function __construct(){
        $this->xml = null;
    }

    function find($id){
        $nodes = $this->all()->xpath("/data/row/ResourceID[.='$id']/..");
        return (count($nodes) > 0) ? $nodes[0] : null;
    }
    
    function all(){
        if ($this->xml == null)
            $this->xml = LNF::select_resources();
        return $this->xml;
    }
}

class LnfStaffSession extends LnfStaff{
    var $session;

    function LnfStaffSession($var){
        parent::Staff($var);
        $this->session = new UserSession($var);
    }

    function isValid(){
        global $_SESSION,$cfg;

        if(!$this->getId() || $this->session->getSessionId()!=session_id())
            return false;

        return $this->session->isvalidSession($_SESSION['_staff']['token'],$cfg->getStaffTimeout(),$cfg->enableStaffIPBinding())?true:false;
    }

    function refreshSession(){
        global $_SESSION;
        $_SESSION['_staff']['token']=$this->getSessionToken();
    }

    function getSession() {
        return $this->session;
    }

    function getSessionToken() {
        return $this->session->sessionToken();
    }

    function getIP(){
        return $this->session->getIP();
    }
}
?>
