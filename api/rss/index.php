<?php
header("Content-Type: text/plain");
//header("Content-Type: text/xml");

require('client.inc.php');

define('RSS_DIR', ROOT_DIR.'api/rss/');
define('RSS_INCLUDE_DIR', RSS_DIR.'include/');

include_once(RSS_INCLUDE_DIR.'class.rssutility.php');
include_once(RSS_INCLUDE_DIR.'class.rssfeed.php');
require_once(INCLUDE_DIR.'class.lnf.php');

//$ticket= new Ticket(Ticket::getIdByExtId(127668));
//LNF::parseSubject($ticket);
//die('ok');

$staff = LNF::Get("staff");
$status = LNF::Get("status");

$rss = new RssFeed();

echo $rss->GetTickets($staff, $status);

//$test = 'ResourceID : 40000 | Type : Emergency (Tool Down) | this is another test';
//echo strlen($test);
?>