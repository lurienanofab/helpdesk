<?php
class RssFeed {
  function GetTickets($staff, $status) {
    $where = "1 = 1";
    
    switch ($staff) {
      case "":
      case "all":
        break;
      default:
        $where .= " AND username IN ($staff)";
        break;
    }
    
    switch ($status) {
      case "":
      case "all":
        break;
      default:
        $where .= " AND `status` IN ($status)";
        break;
    }
    
    $sql = "SELECT *, t.email AS 'sender_email' FROM ost_ticket t LEFT JOIN ost_staff s ON t.staff_id = s.staff_id WHERE $where";
    
    $rs = RssUtility::ExecuteQuery($sql);

    $result = '<?xml version="1.0" encoding="utf-8"?' . '>' . "\r\n";
    $result .= '<rss version="2.0">' . "\r\n";
    $result .= "  <channel>\r\n";
    $result .= "    <title>LNF Helpdesk Tickets</title>\r\n";
    $result .= "    <link>http://lnf.umich.edu/helpdesk/</link>\r\n";
    $result .= "    <description>LNF helpdesk tickets.</description>\r\n";
    $result .= "    <lastBuildDate>Mon, 06 Sep 2010 00:01:00 +0000</lastBuildDate>\r\n";
    $result .= "    <pubDate>Mon, 06 Sep 2009 16:45:00 +0000</pubDate>\r\n";
    $result .= "    <language>en-us</language>\r\n";
    
    if ($rs !== false) {
      while (($row = mysqli_fetch_assoc($rs)) !== false) {
        $result .= "    <item>\r\n";
        $result .= "      <title>Ticket #{$row['ticketID']} (sent by {$row['sender_email']})</" . "title>\r\n";
        $result .= "      <link>http://lnf.umich.edu/helpdesk/view.php?id={$row['ticketID']}</link>\r\n";
        $result .= "      <guid>{$row['ticketID']}</guid>\r\n";
        $result .= "      <pubDate>Mon, 12 Sep 2005 18:37:00 GMT</pubDate>\r\n";
        $result .= "      <description>{$row['subject']}</description>\r\n";
        $result .= "    </item>\r\n";
      }
    }
    
    $result .= "  </channel>\r\n";
    $result .= "</rss>";
    
    return $result;
  }
}
?>
