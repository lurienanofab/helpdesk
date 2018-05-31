<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include_once('../include/class.pushnotification.php');

header('content-type: application/json');

$pn = new PushNotification();
$result = $pn->execute($_GET);

echo json_encode($result);
?>

