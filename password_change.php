<?php
//CT this page can only be viewed when there is a valid token and member_id. changed teh way that passwords work - so user must reset their own account, its not assigned or shared with anyone.
include_once("includes/inc.global.php");
$is_success = false;
$token = $_REQUEST['token'];
// if(empty($token)) {
// 	$cUser->MustBeLoggedOn();
// 	$is_success = true;
// }else{
	//print_r($cUser);
	

	if(!empty($_REQUEST['member_id'])) {
		//CT cant directly set the member ID
		$field_array = array();
		$field_array['member_id'] = $_REQUEST['member_id'];
		$cUser->Build($field_array);
		//print_r($cUser->getMemberId());
	} 
	$is_verified_token = $cUser->verifyToken($token);
//}
//$p->site_section = SITE_SECTION_OFFER_LIST;


$p->page_title = "Change my password";

//
// Define form elements
//
/*

$list = $p->Wrap('Your password must be at least 7 characters long (the longer the better) and include at least one number. <a href="https://www.wikihow.tech/Create-a-Secure-Password target="_blank">Tips on how to create a secure password</a>', 'p');
$form->addElement('html', '<TR></TR>');
$options = array('size' => 10, 'maxlength' => 15);
$form->addElement('password', 'old_passwd', 'Old Password',$options);
$form->addElement('password', 'new_passwd', 'Choose a New Password',$options);
$form->addElement('password', 'rpt_passwd', 'Repeat the New Password',$options);
$form->addElement('submit', 'btnSubmit', 'Change Password');

//
// Define form rules
//
$form->addRule('old_passwd', 'Enter your current password', 'required');
$form->addRule('new_passwd', 'Enter a new password', 'required');
$form->addRule('rpt_passwd', 'You must re-enter the new password', 'required');
$form->addRule('new_passwd', 'Password not long enough', 'minlength', 7);
$form->registerRule('verify_passwords_equal','function','verify_passwords_equal');
$form->addRule('new_passwd', 'Passwords are not the same', 'verify_passwords_equal');
$form->registerRule('verify_old_password','function','verify_old_password');
$form->addRule('old_passwd', 'Password is incorrect', 'verify_old_password');
$form->registerRule('verify_good_password','function','verify_good_password');
$form->addRule('new_passwd', 'Passwords must contain at least one number', 'verify_good_password');

$list .= $form->toHtml();
//*/
//	Display or process the form
//


if ($_POST["submit"]){
	// $vars = array();
	// $vars['old_passwd'] = $_POST["old_passwd"];
	// $vars['new_passwd'] = $_POST["new_passwd"];
	// $vars['rpt_passwd'] = $_POST["rpt_passwd"];
	if(processData()){
		$cStatusMessage->Info("Your password has been changed. Please log in with it now.");	
		$output = "<a href=\"member_dashboard.php\">Log in now</a>";
		include("redirect.php");
	}
	else{
		$cStatusMessage->Info("Something went wrong");	

	}
}

if($token && $is_verified_token){
	$output = displayPasswordForm($token);
}else{
	$cStatusMessage->Error('The link provided is not valid.');
}

$p->DisplayPage($output);  // just display the form





function displayPasswordForm($token) { // TODO: Should use SaveMember and should reset $this->password
        global $cUser, $cDB, $cStatusMessage;
        //CT todo - use template.
        $output = "
        <form action=\"/members/password_change.php\" method=\"post\" name=\"form\" id=\"form\" class=\"layout2\">
        	<input type=\"hidden\" name=\"token\" id=\"token\"  value=\"{$token}\" />
        	<input type=\"hidden\" name=\"member_id\" id=\"member_id\"  value=\"{$cUser->getMemberId()}\" />
            <p>Passwords can be a phrase at least 8 characters long, any character, even spaces - the longer it is the more secure it is.</p>
            <p>Just make sure it's one you haven't used before, and one you can remember - you are keeping other members' data safe, not just your own. <a href=\"https://www.avg.com/en/signal/how-to-create-a-strong-password-that-you-wont-forget\" target=\"_blank\">Tips on how to create a secure password</a>.</p>
            <hr />
            <p>
                <label for=\"password\">
                    <span>New password  *</span>
                    <input maxlength=\"200\" name=\"password\" id=\"password\" type=\"password\" value=\"\">
                </label>
            </p>
            <p>
                <label for=\"repeat_password\">
                    <span>Repeat new password  *</span>
                    <input maxlength=\"200\" name=\"repeat_password\" id=\"repeat_password\" type=\"password\" value=\"\">
                </label>
            </p>
            
            <p class=\"summary\">
                <input name=\"submit\" id=\"submit\" value=\"Submit\" type=\"submit\" />
                * denotes a required field
            </p>
        </form>";
        return $output;
        
    }


function processData() {
	global $p, $cUser, $cStatusMessage, $cDB;
	$field_array= $_POST;

	$errors = array();
	//if(verify_old_password($_POST['old_passwd'])) $errors[] = "your old password does not match.";
	if (strlen($field_array['password'])<8) $errors[] = "A password must be at least 8 characters long.";
	if ($field_array['password'] != $field_array['repeat_password']) $errors[] = "Make sure the repeated password matches.";
	if(sizeof($errors)==0){
		if($cUser->ChangePassword($field_array['password'])){
			$cStatusMessage->Info('Password successfully changed.');
			return true;
		}
	}else{
		//$errors[] = "Your password could not be changed.";
		foreach ($errors as $key => $string) {
			$cStatusMessage->Error($string);
		}
		return false;

	}

	//return false;
}

// function verify_email($email) {
// 	global $cUser;
// 	if($cUser->ValidatePassword($element_value))
// 		return true;
// 	else
// 		return false;
// }
// function verify_member_id($member_id) {
// 	global $cUser;
// 	if($cUser->ValidatePassword($element_value))
// 		return true;
// 	else
// 		return false;
// }

// function verify_good_password($element_name,$element_value) {
// 	$i=0;
// 	$length=strlen($element_value);
	
// 	while($i<$length) {
// 		if(ctype_digit($element_value{$i}))
// 			return true;	
// 		$i+=1;
// 	}
	
// 	return false;
// }


?>
