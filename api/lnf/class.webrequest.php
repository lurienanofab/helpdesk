<?
class webrequest{
	private $url;
	private $data;
	private $timeout;
    private $error;
	private $errno;
	private $content;
    private $header;
	private $userAgent;
	
	public function __construct($url = '', $data = null, $timeout = 5000){
		$this->url = $url;
		$this->data = $data;
		$this->timeout = $timeout;
		$this->error = '';
		$this->content = '';
		$this->header = null;
		$this->userAgent = '';
	}
    
    public function setHeader($value){
        $this->header = $value;
    }

    public function setUserAgent($value){
        $this->userAgent = $value;
    }
    
    public function getContent(){
        return $this->content;
    }
    
    public function getError(){
        return json_encode(array(
            'url'   => $this->url,
            'errno' => $this->errno,
            'error' => $this->error));
    }
	
	public function send(){
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->timeout);
		
		if ($this->data != null){
			curl_setopt($ch, CURLOPT_POST, count($this->data));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);
		}
		
		if ($this->headers != null)
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
			
		if ($this->userAgent != '')
			curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
		
		$this->content = curl_exec($ch);
		$this->errno = curl_errno($ch);
		if ($this->errno){
			$this->error = curl_error($ch);
			return false;
		}
        
		curl_close($ch);
        
		return true;
	}
}
?>