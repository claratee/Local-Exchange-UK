<?php

//CT this is a bit dodgy. don't like it. todo - fix it. 
//simply relays the form submit information to the 

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
// Load Composer's autoloader
require 'vendor/autoload.php';
//if(RECAPTCHA_VALIDATION) include_once(VENDOR_PATH. "/securimage/secureimage.php");

$mailer = new PHPMailer(true);

//CT todo: make it so it doesn't have to use smtp, but fall back to server mail(). Currently thats the only option.
if(EMAIL_SMTP_AUTH == true){
    $mailer->isSMTP();                                  // Send using SMTP
    //if(DEBUG == true) $mailer->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
    $mailer->Host       = EMAIL_SMTP_HOST;                    // Set the SMTP server to send through
    $mailer->CharSet = "UTF-8";
    $mailer->SMTPAuth   = EMAIL_SMTP_AUTH;                    // Enable SMTP authentication
    $mailer->Username   = EMAIL_SMTP_USERNAME;                // SMTP username
    $mailer->Password   = EMAIL_SMTP_PASSWORD;                // SMTP password
    //CT lets do this for now - // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
    if (EMAIL_SMTP_SMTPSECURE == "ENCRYPTION_STARTTLS") PHPMailer::ENCRYPTION_STARTTLS;
    elseif (EMAIL_SMTP_SMTPSECURE == "ENCRYPTION_SMTPS") PHPMailer::ENCRYPTION_SMTPS;
    $mailer->Port = EMAIL_PORT;                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
}

$mailer->setFrom(EMAIL_FROM[0], EMAIL_FROM[1]);

//Recipients
//$email->addAddress('didthiswork@zigzagged.co.uk', 'Joe User');     // Add a recipient


function logMessage($mailer){
    //exclude some updates from saving contact
    $field_array = array();
    $field_array['recipients'] = $mailer->To();
    $field_array['reply_to_email'] = $mailer->From();
    $field_array['subject'] = $mailer->Subject();
    $field_array['message'] = $mailer->Body();
    // $field_array['headers'] = $mailer->getHeaders();
    return $this->insert(DATABASE_CONTACT, $field_array);
}
function logSendEvent($action, $contact_id=null, $note=null){
    //print_r($action);
    //exclude some updates from saving contact
    $log_event = new cLoggingSystemEvent();
    //CreateSystemEvent($action, $ref_id=null, $note=null)
    //CreateSystemEvent($category, $action, $ref_id=null, $note=null)
    $log_event->CreateSystemEvent(LOG_SEND, $action, $contact_id, $note);
    $log_event->Save();
}
?>
