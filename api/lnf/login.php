<pre>
<?php
require_once('../../main.inc.php');
if(!defined('INCLUDE_DIR')) die('Fatal Error. Kwaheri!');
require_once(INCLUDE_DIR.'class.staff.php');
require_once('class.lnfutil.php');

$loginCheck = lnfutil::loginCheck();
$host = $_SERVER['HTTP_HOST'];
$ref = $_SERVER['HTTP_REFERER'];
$ref = ($ref)?$ref:'/helpdesk/scp';
$ref = str_replace("http://".$host, '', $ref);
$ref = str_replace("https://".$host, '', $ref);
$url = "https://ssel-apps.eecs.umich.edu/login";
$logout = lnfutil::request('logout', false);

if ($logout){
	$_SESSION['_staff']=array();
	session_unset();
	session_destroy();
	$returnUrl = urlencode("/helpdesk/api/lnf/login.php?dest=$ref");
	$returnServer = urlencode($host);
	$url .= "?ReturnUrl=$returnUrl&ReturnServer=$returnServer";
	@header("Location: $url");
	exit;
}else if($loginCheck->authenticated){
	$_SESSION['_staff']=array();
		
	if (lnfutil::login($loginCheck->username)){
		//Figure out where the user is headed - destination!
		$dest=lnfutil::request('dest', $ref);
		//Redirect to the original destination. (make sure it is not redirecting to login page.)
		$dest=($dest && (!strstr($dest,'login.php') && !strstr($dest,'ajax.php')))?$dest:'/helpdesk/scp';
		header("Location: $dest");
		require_once('/helpdesk/scp'); //Just incase header is messed up.
		exit;
	}else{
		@header("Location: /helpdesk");
		exit;
	}
}else{
	$returnUrl = urlencode("/helpdesk/api/lnf/login.php?dest=".urlencode($ref));
	$returnServer = urlencode($host);
	$url .= "?ReturnUrl=$returnUrl&ReturnServer=$returnServer";
	@header("Location: $url");
	exit;
}
?>
