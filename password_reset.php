<?php
include_once("includes/inc.global.php");

$p->page_title = "Set a new password";

//if logged in, log out. pass on any vars
if(!empty($_GET['logout']) && $_GET['logout']==true && $cUser->IsLoggedOn()){
    $cUser->logout();
    $redir_url = "password_reset.php";
    if (!empty($_GET['member_id'])) $redir_url .= "?member_id={$_GET['member_id']}";
    include_once("redirect.php");
}


if ($_POST["submit"]){

	$is_saved = 0;
	$is_saved = process_data();
		//redirect to page if saved
	if(!$cUser->IsLoggedon()){
		$cStatusMessage->Info("If the combination of member id and email address was correct, you should find an email in your inbox containing instructions to set a new password (please check your spam / junk mail folders). If you don't receive it, <a href=\"contact.php\">contact support</a> - we'd be happy to help.");

		//return $output;
		//display success message if saved	
		//$redir_url="member_detail.php?member_id={$member->getMemberId()}&form_action=saved";
  		//include("redirect.php");
	} else{
        if($is_saved){
            $cUser->Logout();
            $cStatusMessage->Info("An email has been sent containing instructions to reset your password (please check your spam / junk mail folders). If you don't receive it, <a href=\"contact.php\">contact support</a> - we'd be happy to help.");
            $redir_url="member_login.php";
            include_once("redirect.php");
        }else{
            $output = "<p>Something went wrong.</p>";
        }
    }   
    $output = "<p>Check your email.</p>";
 
	

} else{
    if(!$cUser->IsLoggedOn()){
       $output = displayPasswordResetForm();
    } else{
        try{
            $output = displayPasswordResetButton();
        } catch(Exception $e){
            $cStatusMessage->Info("Password reset error: " . $e->getMessage());
        }
       
    //  $output = "Please contact the administrator for help.";
    }
    //$cUser->MustBeLoggedOn()
	
}

$p->DisplayPage($output);  // just display the form



function displayPasswordResetForm() { // TODO: Should use SaveMember and should reset $this->password
        global $cDB, $cStatusMessage;
        //CT todo - use template.
        //$date2=date_create("2013-12-12");
        //date_diff($date1, $date2);
        $output = "
        <form action=\"/members/password_reset.php\" method=\"post\" name=\"form\" id=\"form\" class=\"layout2\">
            <p>Complete the details below to reset your password for your account. If you cannot remember your member ID or email address, please <a href=\"contact.php\">contact our support team for help</a>.</p>
            <p>
                <label for=\"member_id\">
                    <span>Member ID  *</span>
                    <input maxlength=\"20\" name=\"member_id\" id=\"member_id\" type=\"text\" value=\"{$_REQUEST['member_id']}\">
                </label>
            </p>
            <p>
                <label for=\"email\">
                    <span>Email  *</span>
                    <input maxlength=\"50\" name=\"email\" id=\"email\" type=\"text\" value=\"{$_REQUEST['email']}\">
                </label>
            </p>
            
            <p class=\"summary\">
                <input name=\"submit\" id=\"submit\" value=\"Submit\" type=\"submit\" />
                * denotes a required field
            </p>
        </form>";
        return $output;
        
    }
function displayPasswordResetButton() { // TODO: Should use SaveMember and should reset $this->password
        global $cUser, $cDB, $cStatusMessage;
        $member = new cMember();
        $condition="m.member_id=\"{$cUser->getMemberId()}\"";
        //print_r($condition);
        if($member->Load($condition)){
            //CT todo - use template.
            $output = "
            <form action=\"/members/password_reset.php\" method=\"post\" name=\"form\" id=\"form\" class=\"layout2\">
                <p>You are setting a new password. When you click the button below, a use-once link will be sent to your email address, and you will be logged out of your account.</p>
                <input name=\"member_id\" id=\"member_id\" type=\"hidden\" value=\"{$member->getMemberId()}\">
                <input name=\"email\" id=\"email\" type=\"hidden\" value=\"{$member->getPerson()->getEmail()}\">
                <p><input name=\"submit\" id=\"submit\" class=\"large\" value=\"Send me a password reset link\" type=\"submit\" /></p>
            </form>";
            
        }else{
            throw new Exception("Can't load member.");
        }
        return $output;
    }

function process_data() {
	global $cUser, $cStatusMessage;
    $is_success = 0;
	$errors = array();

	$member_id = $_POST['member_id'];
	$email = $_POST['email'];

	//check for obvious errors in form
	if(strlen($member_id) < 4 || strlen($member_id) > 10) {
		$errors['member_id'] = "Enter your member id.";
	}
	//valid email
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errors['email'] = "Enter a valid email address.";
	}
     if(sizeof($errors) > 0) {

        //CT todo: highlight the form elements from keys
        foreach($errors as $key => $error) {
            $cStatusMessage->Error($error);
        }
        return $is_success;
        //fail
    }
	//$member = new cMemberUtils;
	$cUser->setMemberId($member_id);
	$cUser->getPerson()->setEmail($email);
    try{
        $is_success = $cUser->recoverPassword();
    }catch(Exception $e){
        $cStatusMessage->Error($e->getMessage());
    }
    
    $_POST = array();
	return $is_success;
}





?>
