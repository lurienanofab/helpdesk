<?php
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(E_ALL);

require_once('../include/pear/Mail.php');
//require_once('../include/pear/Mail/mime.php');

$msg = '';

if (isset($_POST['send'])){
    $subj = isset($_POST['subj']) ? $_POST['subj'] : '';
    $body = isset($_POST['body']) ? $_POST['body'] : '';
    
    if ($subj && $body){
        $from = 'helpdesk.alerts@lnf.umich.edu';
        $to = 'jgett@umich.edu';

        $headers = array(
            'From' => $from,
            'To' => $to,
            'Subject' => $subj
        );
        
        $smtp = Mail::factory('smtp', array(
            'host' => 'smtp.gmail.com',
            'port' => '465',
            'auth' => true,
            'username' => 'helpdesk.alerts@lnf.umich.edu',
            'password' => 'lnfhelpdesk'
        ));

        try{
            $mail = $smtp->send($to, $headers, $body);
            
            if (PEAR::isError($mail))
                $msg = '<p>' . $mail->getMessage() . '</p>';
            else
                $msg = '<p>Message successfully sent!</p>';
        }catch(Exception $ex){
            $msg = '<p>'.$ex->getMessage().'</p>';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Send Email</title>
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Send an email message</h1>
        </div>
        
        <form action="send.php" method="POST" class="form-horizontal">
            <div class="form-group">
                <label for="subj" class="col-sm-2 control-label">Subject</label>
                <div class="col-sm-4">
                    <input type="text" name="subj" id="subj" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label for="body" class="col-sm-2 control-label">Body</label>
                <div class="col-sm-4">
                    <textarea name="body" id="body" class="form-control" cols="5" rows="5"></textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-4">
                    <button type="submit" name="send" class="btn btn-primary">Send Email</button>
                </div>
            </div>
        </form>
        <hr>
        <?php echo $msg; ?>
    </div>
    
    <script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</body>
</html>
