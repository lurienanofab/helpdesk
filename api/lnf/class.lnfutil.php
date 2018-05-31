<?
require_once('class.webrequest.php');

class lnfutil{
	public static function getAuthUrl() {
		return "http://ssel-sched.eecs.umich.edu/login/authcheck";
	}
	
	public static function request($key, $defval = ''){
		return (isset($_REQUEST[$key])) ? $_REQUEST[$key] : $defval;
	}

	public static function loginCheck(){
		$timeout = self::request('timeout', 5000);
		//note: we can only get the cookie value here because the helpdesk and scheduler servers are in the same domain (umich.edu)
		$token = (isset($_COOKIE['sselAuth_cookie'])) ? $_COOKIE['sselAuth_cookie'] : '';
		//153E4EE90D506B1E1B618DFDC504A0D396038C866AA9ED8DD124F9924B80D2F43D612782F9216DE51BCF6B30EABFED30C32F992556478342B826325B6AA82BE55A3D9AC2466BE746476EE9CDE1C43FFFD212A22D38358747984B04A5254ED416A39989F394B219D5C636607198F1727E032F06A12072550D036DAAF1E621D1DB507AC552D93097F374A776FB1B764B73D01E19EE585087C9B753890078F2E5030C23A947CCB941636326BF1945D3B8EE74006BB5967B38B9F14A70CD53BC95F82EC87674F3D4C239DF648349D19B4888EDE1B369F1D94C5F74AB45B85920F63C6ACED1F26839CF818E68ED363935BE75683C8CA5AF8889B31F755A576FBA8EC00315AA52BAF258378EC21718EF87E82C8F6D081846DC0338F717D042A679A2A02066E0F4F3D2B9E6800B5A62F14BAB971DFC6CDC4B474F3F96D3B307
		//die($token);
		$req = new webrequest(self::getAuthUrl(), array('cookieValue'=>$token), $timeout);
		if ($req->send()) {
			//{"success":true,"message":"","authenticated":true,"username":"jgett","roles":["Staff","Store User","Administrator","Web Site Admin","Store Manager","Physical Access","SSEL-OnLine Access","Developer"],"lastName":"Getty","firstName":"James","email":"jgett@umich.edu","expiration":"\/Date(1432863753208)\/","expired":false}
			return json_decode($req->getContent());
		} else {
			return $req->getError();
		}
	}
	
	public static function toolSelect(){
		$result = '';
		$req = new webrequest('http://ssel-apps.eecs.umich.edu/data/feed/tool-list/xml');
		if ($req->send()){
			$xml = simplexml_load_string($req->getContent());
			$result .= '<select name="tool" class="tool-select">';
			$result .= '<option value="0">Any tool</option>';
			foreach ($xml->table[0]->row as $row)
				$result .= '<option value="'.$row->ResourceID.'">'.$row->ResourceName.'</option>';
			$result .= '</select>';
		}
		else{
            $err = $req->getError();
			if ($err->errno == 28)
				$result .= '<span style="color: #ff0000;">The request timed out.</span>';
			else
				$result .= '<span style="color: #ff0000;">An error occurred: '.$err->error.' [#'.$err->errno.']</span>';
		}
        return $result;
	}
	
	public static function login($userID){
		$dest = "";
		if(($user=new StaffSession($userID)) && $user->getId()){
			//update last login.
			db_query('UPDATE '.STAFF_TABLE.' SET lastlogin=NOW() WHERE staff_id='.db_input($user->getId()));
			//Now set session crap and lets roll baby!
			$_SESSION['_staff']=array(); //clear.
			$_SESSION['_staff']['userID']=$userID;
			$user->refreshSession(); //set the hash.
			$_SESSION['TZ_OFFSET']=$user->getTZoffset();
			$_SESSION['daylight']=$user->observeDaylight();
			Sys::log(LOG_DEBUG,'Staff login',sprintf("%s logged in [%s]",$user->getUserName(),$_SERVER['REMOTE_ADDR'])); //Debug.
			session_write_close();
			session_regenerate_id();
			return true;
		}
		return false;
	}
}
?>