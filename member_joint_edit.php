<?php

include_once("includes/inc.global.php");

//
// First, we define the form
//

try{
	if($cUser->getMode()=="admin" && !empty($_GET['member_id'])){
		//CT can manage someone else's income shares - inactive users too, just in case
		//CT TODO should be able to search active users to make sure that their inclme shares are not benefitting inactive account - sweep
		$member = new cMemberUtils();
		$condition="m.member_id=\"{$_GET['member_id']}\" AND status=\"A\"";
		$member->Load($condition);
		$page_title = "Joint member for {$member->getPerson()->getFirstName()} {$member->getPerson()->getLastName()} ({$member->getMemberId()})";

		
		//if($member->getDisplayName())
	} else {
	//CT just use the stripped down logged-in user
		$member = new cMemberUtils();
		$condition="m.member_id=\"{$cUser->getMemberId()}\" AND status=\"A\"";
		$member->Load($condition);
		$page_title = "Joint member";
	}

if($_POST["delete"]){
	$field_array = array();
	$field_array["action"]=$_POST["action"];
	$field_array["member_id"]=$_POST["member_id"];
	$field_array["j_person_id"]=$_POST["j_person_id"];
	$field_array["account_type"]="S";
	$member->Build($field_array);
	//print_r($field_array);
	$is_saved = $member->Save();
	if($is_saved){
		//redirect page if saved	
	    $cStatusMessage->Info("Joint member has been deleted.");
		$redir_url="member_detail.php?member_id={$member->getMemberId()}";
			include("redirect.php");
	} 
}

	if ($_POST["submit"]){
		$field_array = $_POST;
		$member->Build($field_array);
		//CT TODO - validation. this is hokey and manual, will replace with proper validator sometime...
		$error_message = "";
		//CT validation - manual. sorry...
	    if(strlen($member->getJointPerson()->getFirstName()) < 1) $error_message .= "First name is missing. ";
	    if(strlen($member->getJointPerson()->getFirstName()) > 100) $error_message .= "First name is too long. ";
	    if(strlen($member->getJointPerson()->getLastName()) < 1) $error_message .= "Last name is missing. ";
	    if(strlen($member->getJointPerson()->getLastName()) > 100) $error_message .= "Last name is too long. ";
	    if(strlen($member->getJointPerson()->getEmail()) > 0 AND (!$p->isValidEmail($member->getPerson()->getEmail(), true) OR strlen($member->getJointPerson()->getEmail()) > 100) ) $error_message .= "Email is not formed correctly.";
	    if(strlen($member->getJointPerson()->getPhone1Number()) > 0 && strlen($member->getJointPerson()->getPhone1Number()) < 11) $error_message .= "Include full telephone number including dialling code. ";
	    if(strlen($member->getJointPerson()->getEmail()) < 1 AND strlen($member->getJointPerson()->getPhone1Number()) < 1) $error_message .= "You must include at least a phone number or an email address so other members may contact you.";


		$person_id = 0;
		if(empty($error_message)) {
			try{
				$person_id = $member->Save();
				if($person_id){
					//redirect page if saved	
			        $cStatusMessage->Info("Your changes have been saved.");
					$redir_url="member_detail.php?member_id={$member->getMemberId()}";
			  		include("redirect.php");
				} else{
					$cStatusMessage->Error("Something went wrong - changes not saved.");

				}
			}catch(Exception $e){
				$cStatusMessage->Info("Could not save person:" . $e->getMessage());
			}
		} else {
			$cStatusMessage->Error($error_message);
		}
	    
		

	    
	}else{
		if(empty($member->getJointPerson()->getPersonId())){
			//can I do this
			$member->setAction("joint_create");

		}else{
			$member->setAction("joint_update");
		}
	}



} catch (Exception $e) {
	$cStatusMessage->Error("Joint member: " . $e->getMessage());
		$p->DisplayPage("Something went wrong");
	exit;
}


$p->page_title = $page_title;

$joint_person_text = "{$member->getJointPerson()->getFirstName()} {$member->getJointPerson()->getLastName()}";
if($member->getJointPerson()->getDirectoryList()=="N") $joint_person_text .= " (hidden from directory and listings)";


if($member->getAction() == "joint_create") {
		$output .= "<div class=\"summary\">There is no joint member on this account.</div>
			<h3>Create joint member</h3>"; 
	}else{
		 $output .= "<form method=\"POST\" class=\"layout3\"  action=\"" . $_SERVER['PHP_SELF'] . "\"><input name=\"delete\" id=\"delete\" value=\"Remove joint member\"  type=\"submit\"  />
        <input type=\"hidden\" id=\"action\" name=\"action\" value=\"joint_delete\" />

		    <input type=\"hidden\" id=\"member_id\" name=\"member_id\" value=\"{$member->getMemberId()}\" />

		 	<input name=\"j_person_id\" id=\"j_person_id\" value=\"{$member->getJointPerson()->getPersonId()}\"  type=\"hidden\"  />ACTIVE joint member: {$joint_person_text}</form>
			<h3>Update joint member</h3>";  
	}

$directory_array = array("Y"=>"Show in directory and listings", "N"=>"Hidden from directory and listings");
$output .= "
    <form action=\"". HTTP_BASE ."/member_joint_edit.php\" method=\"post\" name=\"form\" id=\"form\" class=\"layout2\">
        <input type=\"hidden\" id=\"member_id\" name=\"member_id\" value=\"{$member->getMemberId()}\" />
        <input type=\"hidden\" id=\"account_type\" name=\"account_type\" value=\"J\" />
        <input type=\"hidden\" id=\"j_person_id\" name=\"j_person_id\" value=\"{$member->getJointPerson()->getPersonId()}\" />        
        <input type=\"hidden\" id=\"action\" name=\"action\" value=\"{$member->getAction()}\" />
        <p>The joint member can be shown or hidden from directory and listings. In either case, they will receive emails from the system just like you do. </p>
        
		<p>
		    <label for=\"j_directory_list\">  
		        <span>Display of joint member *</span>
		        {$p->PrepareFormSelector('j_directory_list', $directory_array, null, $member->getJointPerson()->getDirectoryList())}
		    </label>
		</p>
        <p>
            <label for=\"j_first_name\">
                <span>First name  *</span>
                <input maxlength=\"100\" name=\"j_first_name\" id=\"j_first_name\" type=\"text\" value=\"{$member->getJointPerson()->getFirstName()}\">
            </label>
        </p>
        <p>
            <label for=\"j_last_name\">
                <span>Last name  *</span>
                <input maxlength=\"100\" name=\"j_last_name\" id=\"j_last_name\" type=\"text\" value=\"{$member->getJointPerson()->getLastName()}\">
            </label>
        </p>
       
        <h3>Contact details</h3>

        <p>
            <label for=\"j_email\">
                <span>Email address *</span>
                <input maxlength=\"100\" name=\"j_email\" id=\"j_email\" type=\"text\" value=\"{$member->getJointPerson()->getEmail()}\">
            </label>
        </p>            
        <p>
            <label for=\"j_phone1_number\">
                <span>Phone number</span>
                <input maxlength=\"200\" name=\"j_phone1_number\" id=\"j_phone1_number\" type=\"text\" value=\"{$member->getJointPerson()->getPhone1Number()}\">
            </label>
        </p>

        

        <div>* Required field</div>
        <p class=\"summary\">
            <input name=\"submit\" id=\"submit\" value=\"Submit\" type=\"submit\" />
            <a href=\"#\" onclick=\"goBack()\" class=\"cancel\">Cancel</a>
        </p>
    </form>";

//
// The form has been submitted with valid data, so process it   
// //
// function process_data ($values) {
// 	global $p, $cUser,$cStatusMessage, $today;
// 	$list = "";

// 	$values['primary_member'] = "N"; 

// 	// // [chris] fixed problem with passing an Array to htmlspecialchars()
// 	// $date = $values['dob'];
	
// 	// $values['dob'] = htmlspecialchars($date['Y'] . '/' . $date['F'] . '/' . $date['d']);
	
// 	// if($values['dob'] == $today['year']."/".$today['mon']."/".$today['mday'])
// 	// 	$values['dob'] = ""; // if birthdate was left as default, set to null
	
// 	$phone = new cPhone_uk($values['phone1']);
// 	$values['phone1_area'] = $phone->area;
// 	$values['phone1_number'] = $phone->SevenDigits();
// 	$values['phone1_ext'] = $phone->ext;
// 	$phone = new cPhone_uk($values['phone2']);
// 	$values['phone2_area'] = $phone->area;
// 	$values['phone2_number'] = $phone->SevenDigits();
// 	$values['phone2_ext'] = $phone->ext;	
// 	$phone = new cPhone_uk($values['fax']);
// 	$values['fax_area'] = $phone->area;
// 	$values['fax_number'] = $phone->SevenDigits();
// 	$values['fax_ext'] = $phone->ext;	

//     // XSS guard
//     foreach($values as $key => $value) {
//         $values[$key] = htmlspecialchars($value);
//     }

// 	$new_person = new cPerson($values);
// 	$created = $new_person->SaveNewPerson();
	
// 	$member = new cMember();
// 	$member->LoadMember($_REQUEST["member_id"]);
	
// 	if($created and $member->account_type == "S") {
// 		$member->account_type = "J";  // Now it's a Joint account
// 		$member->SaveMember();
// 	}	

// 	if($created) {
// 		$list .= "<p>Joint member created. Would you like to <a href='member_contact_create.php?mode=". $_REQUEST["mode"] ."&member_id=". $values["member_id"] ."''>add another</a>?</p>";
// 	} else {
// 		$cStatusMessage->Error("<p>There was an error saving the joint member. Please try again later.</p>");
// 	}
//    $p->DisplayPage($list);
// }
// //
// // The following functions verify form data
// //

// // TODO: All my validation functions should go into a new cFormValidation class
		
// function verify_reasonable_dob($element_name,$element_value) {
// 	global $today;
// 	$date = $element_value;
// 	$date_str = $date['Y'] . '/' . $date['F'] . '/' . $date['d'];
// //	echo $date_str ."=".$today['year']."/".$today['mon']."/".$today['mday'];

// 	if ($date_str == $today['year']."/".$today['mon']."/".$today['mday']) 
// 		// date wasn't changed by user, so no need to verify it
// 		return true;
// 	elseif ($today['year'] - $date['Y'] < 3)  // A little young to be trading, presumably a mistake
// 		return false;
// 	else
// 		return true;
// }

// function verify_not_future_date ($element_name,$element_value) {
// 	$date = $element_value;
// 	$date_str = $date['Y'] . '/' . $date['F'] . '/' . $date['d'];

// 	if (strtotime($date_str) > strtotime("now"))
// 		return false;
// 	else
// 		return true;
// }

// // TODO: This simplistic function should ultimately be replaced by this class method on Pear:
// // 		http://pear.php.net/manual/en/package.mail.mail-rfc822.intro.php
// function verify_valid_email ($element_name,$element_value) {
// 	if ($element_value=="")
// 		return true;		// Currently not planning to require this field
// 	if (strstr($element_value,"@") and strstr($element_value,"."))
// 		return true;	
// 	else
// 		return false;
	
// }

// function verify_phone_format ($element_name,$element_value) {
// 	$phone = new cPhone_uk($element_value);
	
// 	if($phone->prefix)
// 		return true;
// 	else
// 		return false;
// }
//if($mode == "admin") $page_title .= " [ADMIN MODE]";
$p->page_title = $page_title;

$p->DisplayPage($output);
?>
