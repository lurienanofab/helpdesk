<?php
session_start();

require_once('data-plugin.php');

class Data extends DataPlugin{
 
	private $keys = array(
		'141.213.7.23'      => '53475715FC332BA1E5320CB0DCB45089',
		'168.61.39.172'     => 'FF051DC3C5699012965578752FA34E85',
		'141.213.8.37'      => 'BFCCB07172D97BB934253D0709FEC278',
		
		'137.117.73.153'    => '3A34BDBAFBC2BB5EE72316841068A214',
		'10.241.30.65'      => '3A34BDBAFBC2BB5EE72316841068A214', //the key here is the same as 137.117.73.153 because when $_SERVER['SERVER_ADDR'] is 10.241.30.65 on data.php $_SERVER['REMOTE_ADDR'] will be 137.117.73.153 on data-exec.php
		
		'104.41.155.112'    => '123EED4B3EE31BED4F5FE629C03BD813',
		'10.241.50.164'     => '123EED4B3EE31BED4F5FE629C03BD813', //the key here is the same as 104.41.155.112 because when $_SERVER['SERVER_ADDR'] is 10.241.50.164 on data.php $_SERVER['REMOTE_ADDR'] will be 104.41.155.112 on data-exec.php
		
        '191.237.18.92'     => 'E08A50F8CF60F01C56CCD455678CBCD5',
        '10.0.0.4'          => 'E08A50F8CF60F01C56CCD455678CBCD5'  //the key here is the same as 191.237.18.92 because when $_SERVER['SERVER_ADDR'] is 10.0.0.4 on data.php $_SERVER['REMOTE_ADDR'] will be 191.237.18.92 on data-exec.php
	);
	
	private $postRequiredActions;
	
	protected function getApiKey(){
        
        if (array_key_exists('SERVER_ADDR', $_SERVER))
            $k = $_SERVER['SERVER_ADDR']; //on linux
        else
            $k = $_SERVER['LOCAL_ADDR']; //on windows
        
		return $this->keys[$k];
	}
	
	public function Data(){
		$this->postRequiredActions = array('add_ticket');
	}
	
	protected function processAction(){
		if ($this->validateRequest()){
			echo $this->sendPostRequest();
		}
		else{
			header('Content-Type: text/plain');
			echo "Error: POST required for {$this->action}";
		}
	}
	
	private function validateRequest(){
		$getAction = $this->getval('action', '', $_GET);
		foreach ($this->postRequiredActions as $a){
			if ($getAction == $this->action && $getAction == $a){
				return false;
			}
		}
		return true;
	}
	
	private function sendPostRequest(){
		$postData = array_merge($_REQUEST, $this->postData());
		$postData["staff_userID"] = $_SESSION["_staff"]["userID"];
		$postData["staff_token"] = $_SESSION["_staff"]["token"];
		$req = new webrequest($this->getUrl().'/data-exec.php', $postData, 30000);
		$req->setHeader(array('Content-Type: application/x-www-form-urlencoded'));
		$req->setUserAgent($this->getApiKey());
		$req->send();
		$result = $req->getContent();
		echo $result;
	}
}

$data = new Data();
$data->handleRequest();
?>