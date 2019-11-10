<?php
include_once("includes/inc.global.php");

if($cUser->getMode()!="admin"){
	$cStatusMessage->Error("You don't have permission for this action.");
   // 		return false;
	$redir_url="index.php";
   include("redirect.php");
}
if(empty($_REQUEST['member_id'])) {
	$cStatusMessage->Error("No member_id specified.");
   // 		return false;
	$redir_url="admin_menu.php";
   include("redirect.php");
}

$member = new cMemberUtils;
$condition="m.member_id={$_REQUEST['member_id']}";
$member->Load($condition);

//$p->site_section = ADMINISTRATION;
$p->page_title = "Welcome email and password reset to {$member->getDisplayName()} ({$member->getMemberId()})";



//page titles
//CT store in object
//if user themselves or a comittee or above





/*include("includes/inc.forms.php");

$form->addElement("static", 'contact', "This form will both unlock an account (if it is locked) and reset the member's password.  Then it will email the new password to the member.  You may want to make sure the member's email is still current.", null);
$form->addElement("static", null, null);
$ids = new cMemberGroup;
$ids->LoadMemberGroup();
$form->addElement("select", "member_id", "Choose the Member Account", $ids->MakeIDArray());

$form->addElement("static", null, null, null);
$form->addElement("submit", "btnSubmit", "Unlock and Reset");
$form->addElement("radio", "emailTyp", "", "Send 'Password Reset' email","pword");
$form->addElement("radio", "emailTyp", "", "Send 'Welcome' Email","welcome");

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $p;
	
	$list = "";
	$member = new cMember;
	$member->LoadMember($values["member_id"]);

	if($consecutive_failures = $member->UnlockAccount()) {
		$list .= "This member account had been locked due to ". $consecutive_failures ." consecutive login failures.  It has been unlocked.  If the number of attempts is more than 10 or 20,you may want to contact your administrator at ". PHONE_ADMIN ."</I>, because it could indicate someone is trying to hack into the system.<P>";
	}


	$password = $member->GeneratePassword();
	$member->ChangePassword($password); // This will bomb out if the password change fails
	
	$list .= "The password has been reset";
	
	if ($_REQUEST["emailTyp"]=='welcome') {
		
		$mailed = mail($member->person[0]->email, NEW_MEMBER_SUBJECT, NEW_MEMBER_MESSAGE . "\n\nMember ID: ". $member->member_id ."\n". "Password: ". $password, EMAIL_FROM);
			
		$whEmail = "'Welcome'";
	}
	else {
		$mailed = mail($member->person[0]->email, PASSWORD_RESET_SUBJECT, PASSWORD_RESET_MESSAGE . "\n\nMember ID: ". $member->member_id ."\nNew Password: ". $password, EMAIL_FROM);
		
		$whEmail = "'Password Reset'";
	}

	if($mailed)
		$list .= " and a $whEmail email has been sent to the member's email address (". $member->person[0]->email .").";
	else
		$list .= ". <I>However, the attempt to email the new password failed.  This is most likely due to a technical problem.  Contact your administrator at ". PHONE_ADMIN ."</I>.";	
	
}*/

if ($_POST["submit"]){

	$is_saved = 0;
	$is_saved = process_data();
		//redirect to page if saved
	
    if($is_saved){
        //$cUser->Logout();
        $cStatusMessage->Info("An email has been sent to {$member->getDisplayName()}.");
        $redir_url = "admin_menu.php";
        include_once('redirect.php');
    }else{
        $output = "<p>Something went wrong.</p>";
    }
    
 
	

} else{
    
    
    $output .="<p>You are sending a welcome email with instructions how to set a password.</p><form action=\"". HTTP_BASE ."/member_unlock.php\" method=\"post\" name=\"form\" id=\"form\" class=\"layout2\">
            
            <input name=\"member_id\" id=\"member_id\" type=\"hidden\" value=\"{$member->getMemberId()}\">
            <input name=\"email\" id=\"email\" type=\"hidden\" value=\"{$member->getPerson()->getEmail()}\">
            <p><input name=\"submit\" id=\"submit\" class=\"large\" value=\"Send welcome mail to {$member->getPerson()->getFirstName()}\" type=\"submit\" /></p>
        </form>";
   
    //$cUser->MustBeLoggedOn()
	
}

function process_data() {
	global $cUser, $member, $cStatusMessage;
    $is_success = 0;
	$errors = array();

    try{
        $is_success = $member->sendWelcomeEmail();
    }catch(Exception $e){
        $cStatusMessage->Error($e->getMessage());
    }
    
    $_POST = array();

	return $is_success;
}



$p->DisplayPage($output);
?>
