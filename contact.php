<?php
include_once("includes/inc.global.php");

//if(RECAPTCHA_VALIDATION) include_once(VENDOR_PATH. "/securimage/secureimage.php");

//$p->site_section = SECTION_EMAIL;
$p->page_title = "Contact Us";

$mail = new cMail();

// if form submitted
if ($_POST["submit"]){
	//build object from inputs
$cc_me_string ="";
if(!empty($_POST['contact_form_from_cc'])) $cc_me_string = " checked=\"checked\"";
	// error catching without PEAR is a bit of a faff, but cant use PEAR anymore.
	$error_message = "";
	//if(!strlen($_POST['contact_form_message'] > 10)) $error_message .= "Message is missing or not long enough.";
    if(strlen($_POST['contact_form_from_name']) < 1) $error_message .= "Your name is missing. ";
    if(strlen($_POST['contact_form_from_name']) > 100) $error_message .= "Name cannot be more than 100 letters. ";
    if(strlen($_POST['contact_form_from_email']) < 0) $error_message .= "You must include your email address. ";
    if(!$p->isEmailValid($_POST['contact_form_from_email']) OR strlen($_POST['contact_form_from_email']) > 100) $error_message .= "Email is not formed correctly.";

	if(empty($_POST['contact_form_subject'])) $error_message .= "Subject is missing. This helps us to know who to direct your enquiry to.";
	if(empty($_POST['contact_form_message'])) $error_message .= "Message is missing.";

	//check if errors and save
	$is_sent = 0;
	if(empty($error_message)) {
		
	    $field_array = array();
	    $field_array['reply_to_name'] = $_POST['contact_form_from_name'];
	    $field_array['reply_to_email'] = $_POST['contact_form_from_email'];

	    //$field_array['recipients'] = array();
	    $field_array['recipients'][0] = array('display_name'=>"Admin", 'email'=>$site_settings->getKey('EMAIL_ADMIN'));
	    if(!empty($_POST['contact_form_from_cc'])){
		    $field_array['recipients'][1] = array('display_name'=>$_POST['contact_form_from_name'], 'email'=>$_POST['contact_form_from_email']);
	    }
	    $field_array['message'] = "From: {$_POST['contact_form_from_name']}<br />
			Email: {$_POST['contact_form_from_email']}<br />
			<br />
			{$_POST['contact_form_message']}
			";
		$field_array['subject'] = "Contact form - {$_POST['contact_form_subject']}";
		$mail->Build($field_array);
		//TODO allow user to be mailed a copy

		$is_sent = $mail->sendMail(LOG_SEND_CONTACT);
	} else {
		$cStatusMessage->Error($error_message);
	}
	

	if($is_sent){
		$cStatusMessage->Info("Message was sent. Someone will get back to you shortly.");
		//redirect page if saved	
		$redir_url="index.php";
  		include("redirect.php");
	} 
}


 //    private $recipients;  // array of members to be sent. optimise for multiple recipient

 //    //email stuff
	// private $email_from;
	// private $email_from_name;
	// //private $php_version; 
 //    //private $to_name; 
 //    private $subject; 
 //    private $message;
 //    //the final step before sending mail
 //    private $headers;
 //    private $formatted_subject; 
 //    private $formatted_message;
//include("includes/inc.forms.php");

//CT:simplify the form! and protect with captcha against bots
//TODO: move over to google's recaptcha
//
// First, we define the form
//
//$form->addElement("header", null, "Contact us");
$subject_dropdown = $p->PrepareFormSelector("contact_form_subject", ARRAY_CONTACT_SUBJECT, "Choose a subject", $_POST['contact_form_subject']);

if($cUser->isLoggedOn()){
	//load full member - for email address
	$member = new cMember();
	$member->Load("m.member_id=\"{$cUser->getMemberId()}\"");
	$member_id = $member->getMemberId();
	$contact_string = "
		<p>You are logged in as {$member->getDisplayName()} ({$member->getPerson()->getMemberId()}).</p>
		<input type=\"hidden\" id=\"contact_form_from_name\" name=\"contact_form_from_name\" value=\"{$member->getDisplayName()}\" />";

	if(!empty($member->getPerson()->getEmail())){
		$contact_string .= "<input type=\"hidden\" id=\"contact_form_from_email\" name=\"contact_form_from_email\" value=\"{$member->getPerson()->getEmail()}\" />";
	}else{
		$contact_string .= "<p>
			<label for=\"contact_form_from_email\">
				Your email (you do not have one associated with your member account) *<br />
				<input maxlength=\"200\" name=\"contact_form_from_email\" id=\"contact_form_from_email\" type=\"text\" value=\"{$_POST['contact_form_from_email']}\">
			</label>
		</p>";
	}
	

}else{
	$member_id = "";
	$contact_string = "
		<p>
			<label for=\"contact_form_from_name\">
				Your name *<br />
				<input maxlength=\"200\" name=\"contact_form_from_name\" id=\"contact_form_from_name\" type=\"text\" value=\"{$_POST['contact_form_from_name']}\">
			</label>
		</p>
		<p>
			<label for=\"contact_form_from_email\">
				Your email *<br />
				<input maxlength=\"200\" name=\"contact_form_from_email\" id=\"contact_form_from_email\" type=\"text\" value=\"{$_POST['contact_form_from_email']}\">
			</label>
		</p>
	";
}

$recaptcha = "";
// if(RECAPTCHA_VALIDATION && !$cUser->isLoggedOn()) {
// 	$recaptcha ="<img id=\"captcha\" src=\"".HTTP_BASE."/vendor/securimage/securimage_show.php\" alt=\"CAPTCHA Image\" /> <button style=\"font-size:1.2em\" onclick=\"document.getElementById('captcha').src = '".HTTP_BASE."/vendor/securimage/securimage_show.php?' + Math.random(); return false\" title=\"Load another captcha image\">&#x21bb;</button><br />Enter the text you see *: <input type=\"text\" name=\"captcha_code\" id=\"captcha_code\" size=\"10\" maxlength=\"6\"  autocomplete=\"off\" />";
// }
$output .= "
	<form action=\"contact.php\" method=\"post\"  class=\"layout2\">
		<input type=\"hidden\" id=\"contact_form_from_member_id\" name=\"contact_form_from_member_id\" value=\"{$member_id}\" />
		{$contact_string}
		<p>
			<label for=\"contact_form_subject\">
				Subject *<br />
				{$subject_dropdown}
			</label>
		</p>
				
		<p>
			<label for=\"contact_form_message\">Your message *<br />
				<textarea cols=\"80\" rows=\"20\" wrap=\"soft\" name=\"contact_form_message\" id=\"contact_form_message\">{$_POST['contact_form_message']}</textarea>
			</label>
		</p>	
			
		<p>
			<label for=\"contact_form_from_cc\">
				<input name=\"contact_form_from_cc\" id=\"contact_form_from_cc\" type=\"checkbox\" value=\"contact_form_from_cc\" {$cc_me_string}\"> Send a copy also to my email address
			</label>
		</p>
		{$recaptcha}
		<p>
			<input name=\"submit\" id=\"submit\" value=\"Submit\" type=\"submit\" />
			* denotes a required field
		</p>
	</form>";


// $form->addElement("static", null, "<p>Ask for help directly or find out more about " . SITE_SHORT_TITLE . " and how to join in our <a href='pages.php?id=7'>information pages</a>.</p>");

// $form->addElement("text", "name", "Your name");
// $form->addElement("text", "email", "Your email");
// //$form->addElement("text", "phone", "Phone");
// //$form->addElement("static", null, null, null);
// $form->addElement("textarea", "message", "Your message", array("cols"=>65, "rows"=>10, "wrap"=>"soft"));
// $form->addElement("static", null, null, null);
// //$heard_from = array ("0"=>"(Select One)", "1"=>"Newspaper", "2"=>"Radio", "3"=>"Search Engine", "4"=>"Friend", "5"=>"Local Business", "6"=>"Article", "7"=>"Other");
// //$form->addElement("select", "how_heard", "How did you hear about us?", $heard_from);
// //if($recaptchaenabled){
// //CT: include recaptcha if set
// /*
// if(RECAPTCHA_VALIDATION) $form->addElement("static", null, "<img id=\"captcha\" src=\"".RECAPTCHA_SRC."securimage_show.php\" alt=\"CAPTCHA Image\" /> <button style=\"font-size:1.2em\" onclick=\"document.getElementById('captcha').src = '".RECAPTCHA_SRC."securimage_show.php?' + Math.random(); return false\" title=\"Load another captcha image\">&#x21bb;</button><br />Enter the text you see *: <input type=\"text\" name=\"captcha_code\" id=\"captcha_code\" size=\"10\" maxlength=\"6\" />", null);
// //}
// */

// //$form->addElement("static", null, null, null);
// $form->addElement("submit", "btnSubmit", "Send");

// //
// // Define form rules
// //
// $form->addRule("name", "Enter your name", "required");
// $form->addRule("email", "Enter your email", "required");
// $form->addRule("message", "Enter a message", "required");
// //$form->addRule("phone", "Enter your phone number", "required");
// $form->registerRule('checkmail', 'function', 'checkEmail');
// $form->addRule('email', 'Enter a valid email', 'checkmail', true);

// //$output = "<p>Ask for help directly or find out more about " . SITE_SHORT_TITLE . " and how to join in our <a href='pages.php?id=7'>information pages</a>.</p>";

// //$output .= $form->toHtml();
// if ($form->validate()) { // Form is validated so processes the data
// 	if (RECAPTCHA_VALIDATION) $securimage = new Securimage();
// 	//$p->page_msg .= $_POST['captcha_code'];
// 	//echo "secureimage" . $securimage->check($_POST['captcha_code'];
// 	if ((RECAPTCHA_VALIDATION) && $securimage->check($_POST['captcha_code']) == false) {
// 		//$form->addElement("static", null, "<div class='error'>Captcha is missing or incorrect.</div>", null);
// 		$p->DisplayPage($form->toHtml());
// 		//$p->DisplayPage("something went wrong");
// 	}else{	
// 		$form->freeze();
// 		$form->process("process_data", $POST);
// 	}
// } else{
// 	$p->page_content = $form->toHtml();
// }
$p->DisplayPage($output);

// //CT: check email - with DNS
// function checkEmail($element_name,$element_value, $domainCheck = false)
// {
// 	$email=$element_value;
//     if (preg_match('/^[a-zA-Z0-9\._-]+\@(\[?)[a-zA-Z0-9\-\.]+'.
//                    '\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/', $email)) {
//         if ($domainCheck && function_exists('checkdnsrr')) {
//             list (, $domain)  = explode('@', $email);
//             if (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A')) {
//                 return true;
//             }
//             return false;
//         }
//         return true;
//     }
//     return false;
// }

// //
// // The form has been submitted with valid data, so process it   
// //

// function process_data ($values) {
// 	global $p;
// 	// send mail and check for errors
// 	$mailed = mail(EMAIL_ADMIN, "[" . SITE_SHORT_TITLE ."] Contact form", "From: ". $values["name"]."\n\n". wordwrap($values["message"], 64) , "From:". $values["email"]);
// 	if(isset($mailed) && $mailed==true){
// 		$output = "Thank you, your message has been sent. We'll get back to you soon.";
// 	}else{
// 		$output .= "There was a problem sending the email.";	
// 	}
// 	$output .= " <a href=\"javascript:location.reload()\">Go back</a>";
// 	$p->page_content = $output;
// 	//$p->DisplayPage();
// }



?>
