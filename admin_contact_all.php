<?php
include_once("includes/inc.global.php");
$mail = new cMail();

$p->page_title = "Email all members from the admin";

$cUser->MustBeLevel(2);

//include("includes/inc.forms.php");

//
// First, we define the form
//
if ($_POST["submit"]){
    $errors = array();
    if(empty($_POST['subject'])) {
    	$errors['subject'] = "Please enter a subject";
    }
    if(empty($_POST['message'])) {
    	$errors['message'] = "Please enter a subject";
    }
    

    if(sizeof($errors) > 0) {

        //CT todo: highlight the form elements from keys
        foreach($errors as $key => $error) {
            $cStatusMessage->Error($error);
        }
        //return false;
    } else{
		$fieldArray = $_POST;      
		// recipients - all members
        $condition="1";
    	$fieldArray['recipients'] = $mail->loadRecipients($condition);     
		$mail->Build($fieldArray);

		$is_success = 0;
		$is_success = $mail->ProcessData();
		//echo("done? " .$is_success);
		if($is_success){
			//redirect page if saved
            $cStatusMessage->Info("The email was sent successfully.");	
			//$redir_url="admin_menu.php";
	  		//include("redirect.php");
	  	}
	}
} 
$output = "<form action=\"". HTTP_BASE ."/admin_contact_all.php\" method=\"post\" name=\"form\" id=\"form\" class=\"layout2\">
    <p>This email will go out to <em>ALL</em> members of {$site_settings->getKey('SITE_LONG_TITLE')}, and will appear to be sent from the admin. Be sure that this is what you want to do. This form will take html or plain text, and will respect linebreaks. Need help with the formatting? Use an <a href=\"https://html-online.com/editor/\" target=\"_blank\">Online html editor</a></p>
   <!-- <p>
        <label for=\"from\">
            <span>From  *</span>
            {from: self or admin}
        </label>
    </p> -->
    <p>
        <label for=\"subject\">
            <span>Subject  *</span>
            <input maxlength=\"200\" name=\"subject\" id=\"subject\" type=\"text\" value=\"{$mail->getSubject()}\">
        </label>
    </p>
    <p>
        <label for=\"message\">
            <span>Message  *</span>
            <textarea name=\"message\" id=\"message\" type=\"text\">{$mail->getMessage()}</textarea>
        </label>
    </p>
    <p class=\"summary\">
        <input name=\"submit\" id=\"submit\" class=\"button\" value=\"Send to all members\" type=\"submit\" />
        * denotes a required field
    </p>
</form>";
/*form->addElement("static", null, "This email will go out to <i>ALL</i> members of ".SITE_LONG_TITLE.".", null);
$form->addElement("static", null, null, null);
$form->addElement("text", "subject", "Subject", array("size" => 30, "maxlength" => 50));
$form->addElement("static", null, null, null);
$form->addElement("textarea", "message", "Your Message", array("cols"=>65, "rows"=>10, "wrap"=>"soft"));
$form->addElement("static", null, null, null);

$form->addElement("static", null, null, null);
$form->addElement("submit", "btnSubmit", "Send");

//
// Define form rules
//
$form->addRule("subject", "Enter a subject", "required");
$form->addRule("message", "Enter your message", "required");

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

//
// The form has been submitted with valid data, so process it   
//
function process_data ($values) {
	global $p, $heard_from;
	
	$output = "";
	$errors = "";
	$all_members = new cMemberGroup;
	$all_members->LoadMemberGroup();
	
	foreach($all_members->members as $member) {
		if($errors != "")
			$errors .= ", ";
		
		if($member->person[0]->email != "")
			$mailed = mail($member->person[0]->email, $values["subject"], wordwrap($values["message"], 64) , "From:". EMAIL_ADMIN);
		else
			$mailed = true;
		
		if(!$mailed)
			$errors .= $member->person[0]->email;
	}
	if($errors == "")
		$output .= "Your message has been sent to all members.";
	else
		$output .= "There were errors sending the email to the following email addresses:<BR>". $errors;	
	*/	
	$p->DisplayPage($output);




?>
