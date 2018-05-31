<?php
interface iPusher{
    function setConfig($value);
    function getConfig();
    function push($notification);
}

class UserConfig{
    private $type;
    private $username;
    private $apikey;
    
    function __construct($type, $username, $apikey){
        $this->type = $type;
        $this->username = $username;
        $this->apikey = $apikey;
    }
    
    function getUsername(){
        return $this->username;
    }
    
    function getType(){
        return $this->type;
    }
    
    function getApiKey(){
        return $this->apikey;
    }
}

class Notification{
    private $application;
    private $event;
    private $description;
    private $priority = 0;
    private $url = '';
    private $contentType = 'text/html';
    
    function getApplication(){
        return $this->application;
    }
    
    function setApplication($value){
        $this->application = $value;
    }
    
    function getEvent(){
        return $this->event;
    }
    
    function setEvent($value){
        $this->event = $value;
    }
    
    function getDescription(){
        return $this->description;
    }
    
    function setDescription($value){
        $this->description = $value;
    }
    
    function getPriority(){
        return $this->priority;
    }
    
    function setPriority($value){
        if (is_int($value)){
            $val = (int)$value;
            
            if ($val < -2)
                $val = -2;
            
            if ($val > 2)
                $val = 2;
            
            if ($val >= -2 && $val <= 2)
                $this->priority = $val;
        }
    }
    
    function getUrl(){
        $raw = $this->getUrlRaw();
        $scheme = $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
		if (!isset($host) || $host == null || $host == ""){
			$host = "lnf.umich.edu";
		}
        $result = str_replace('{host}', $scheme.$host, $raw);
        return $result;
    }
    
    function getUrlRaw(){
        return $this->url;
    }
    
    function setUrl($value){
        if (is_string($value) && $value != '')
            $this->url = $value;
    }
    
    function getContentType(){
        return $this->contentType;
    }
    
    function setContentType($value){
        $this->contentType = $value;
    }
    
    function hasUrl(){
        return $this->url != '';
    }
}

abstract class Pusher implements iPusher{
    private $config;
    
    function getConfig(){
        return $this->config;
    }
    
    function setConfig($value){
        $this->config = $value;
    }
    
    abstract function push($notification);
    
    protected function httpPost($url, $data){
        $post_data = http_build_query($data);
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
        
        $result = curl_exec($ch);
        
        return $result;
    }
}

class NotifyMyAndroidPusher extends Pusher{
    function push($notification){
        $config = $this->getConfig();
        
        $post_fields = array(
            'apikey'        => $config->getApiKey(),
            'application'   => $notification->getApplication(),
            'event'         => $notification->getEvent(),
            'description'   => $notification->getDescription(),
            'priority'      => $notification->getPriority(),
            'content-type'  => $notification->getContentType()
        );
        
        if ($notification->hasUrl())
            $post_fields['url'] = $notification->getUrl();
        
        $result = $this->httpPost('https://www.notifymyandroid.com/publicapi/notify', $post_fields);
        
        return result;
    }
}

class ProwlPusher extends Pusher{    
    function push($notification){
        $config = $this->getConfig();
        
        $post_fields = array(
            'apikey'        => $config->getApiKey(),
            'application'   => $notification->getApplication(),
            'event'         => $notification->getEvent(),
            'description'   => $notification->getDescription(),
            'priority'      => $notification->getPriority()
        );
        
        if ($notification->hasUrl())
            $post_fields['url'] = $notification->getUrl();
        
        $result = $this->httpPost('https://api.prowlapp.com/publicapi/add', $post_fields);
        
        $ch = curl_init();

        return $result;
    }
}

class PushNotification{
    private $appName = 'LNF Helpdesk';
    
    function getUsers(){
        return array(
            //'jgett'     => new UserConfig('nma', 'jgett', '210b2f87c1996604a11244b625fff6fdc5dbd93aee2b5d6d'),
            //'kjvowen'   => new UserConfig('nma', 'kjvowen', 'b20d5597536cf33e5cf25c59159f0806c03cd8b318691cc8'),
            'wrightsh'  => new UserConfig('prowl', 'wrightsh', '3908d1fe8cc2765b723ee7ddd98d093e9f9f88a3'),
            'sandrine'  => new UserConfig('prowl', 'sandrine', '4a96dbfb3934f2edd953bb61370ed59aa689a6f9')
        );
    }
    
    function getPushers(){
        return array(
            'nma'   => new NotifyMyAndroidPusher(),
            'prowl' => new ProwlPusher()
        );
    }
    
    function execute($args){
        
        $user = isset($args['user']) ? $args['user'] : '';
        $subj = isset($args['subj']) ? $args['subj'] : '';
        $msg = isset($args['msg']) ? $args['msg'] : '';
        $url = isset($args['url']) ? $args['url'] : '';
        $pri = isset($args['pri']) ? $args['pri'] : '';
        
        if (!$user)
            return array('error' => 'missing parameter user');

        if (!$subj)
            return array('error' => 'missing parameter subj');

        if (!$msg)
            return array('error' => 'missing parameter msg');

        $users = $this->getUsers();
        $pushers = $this->getPushers();
        
        if (!array_key_exists($user, $users))
            return array('error' => 'user not found');

        $cfg = $users[$user];

        if (!array_key_exists($cfg->getType(), $pushers))
            return array('error' => 'undefined type: '.$cfg->getType());

        // so far so good

        $n = new Notification();
        $n->setApplication($this->appName);
        $n->setEvent($subj);
        $n->setDescription($msg);
        $n->setPriority($pri);
        $n->setUrl($url);

        $pusher = $pushers[$cfg->getType()];
        $pusher->setConfig($cfg);
        $pusher->push($n);
    }
}
?>