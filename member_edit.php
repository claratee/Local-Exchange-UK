<?php

include_once("includes/inc.global.php");
$cUser->MustBeLoggedOn();


//if no member_id and is not in create mode, set member_id to the current user
/*
rules
* members can edit their own profile.
* core and up can edit other people's profile but most be in admin mode. 
* admin mode must be in the query string, else will forward to profile read page.

Actions done in admin mode will be logged.
* trade
* edit profile
* edit listing
* feedback


* creates can be done, but only in admin mode.

must always pass in a member id unless admin in createmode
*/
// CT three vars to drive the actions on this page
$action = $_REQUEST['action']; //create or update (default)
//$mode = $_REQUEST['mode']; //admin or member (default)
$member_id = $_REQUEST['member_id']; //if missing, is user.

// twisty logic for safety. not readability!
// only core group and up can be in admin mode.
if($cUser->getMode() == "admin"){
    if($action == "create"){
        //member_id shown in form
        $member_id = "";
    } else{
        $action = "update";
        if (empty($_REQUEST['member_id'])) $member_id = $cUser->getMemberId();
    }
} else{
    $action = "update";
    if(!empty($_REQUEST['member_id']) & $_REQUEST['member_id'] != $cUser->getMemberId()) {
        $redir_url="member_edit.php";
        include("redirect.php");
    }
    $member_id = $cUser->getMemberId(); //if missing, is user.
}

//page titles
//CT store in object
//if user themselves or a comittee or above
$member = new cMemberUtils;
$member->setAction($action);
$member->setMemberId($member_id);
if($action == "create") $member->getPerson()->setPrimaryMember("Y");




if ($_POST["submit"]){
	$fieldArray = $_POST;
	// set into object
	//print_r($fieldArray);
	$member->Build($fieldArray);

	// test out all the fields - make sure filled
	//$is_saved = $member->ProcessData();
		//redirect to page if saved

//CT TODO - validation. this is hokey and manual, will replace with proper validator sometime...
    $error_message = "";

    if($cUser->getMode()=="admin"){
        

        if(strlen($member->getJoinDate()) < 1) {
            $error_message .= "Join date is missing";
        }else{
            //CT can't join in the future
            if(!$p->isDateValid($member->getJoinDate(), LONG_LONG_AGO, date('Y-m-d', strtotime('+1 day')))) $error_message .= "Join date must be today or in the past";
        }
        //CT can't expire before they join
        if(strlen($member->getExpireDate()) < 1) {
            $error_message .= "Expire date is missing";
        }else{
            if(!$p->isDateValid($member->getExpireDate(), $member->getJoinDate(), FAR_FAR_AWAY)) $error_message .= "Expire date cannot be before the join date";
        }
         
    }
    //if(strlen($member->getMemberId()) < 4) $error_message .= "Member ID is missing or must be unique.";
    //if(strlen($member->getJoinDate()) < 1) $error_message .= "First name is missing. ";
    //CT validation - manual. sorry...
    if(strlen($member->getPerson()->getFirstName()) < 1) $error_message .= "First name is missing. ";
    if(strlen($member->getPerson()->getFirstName()) > 100) $error_message .= "First name is too long. ";
    if(strlen($member->getPerson()->getLastName()) < 1) $error_message .= "Last name is missing. ";
    if(strlen($member->getPerson()->getLastName()) > 100) $error_message .= "Last name is too long. ";
    if(strlen($member->getPerson()->getEmail()) > 0 AND (!$p->isEmailValid($member->getPerson()->getEmail(), true) OR strlen($member->getPerson()->getEmail()) > 100) ) $error_message .= "Email is not formed correctly.";
    if(strlen($member->getPerson()->getAddressStreet2()) < 1) $error_message .= ADDRESS_LINE_2 . " is missing. ";
    if(strlen($member->getPerson()->getAddressCity()) < 1) $error_message .= ADDRESS_LINE_3 . " is missing. ";
    if(strlen($member->getPerson()->getAddressPostCode()) < 1) $error_message .= ZIP_TEXT . " is missing. ";
    if(strlen($member->getPerson()->getPhone1Number()) > 0 && strlen($member->getPerson()->getPhone1Number()) < 11) $error_message .= "Include full telephone number including dialling code. ";
    if(strlen($member->getPerson()->getEmail()) < 1 AND strlen($member->getPerson()->getPhone1Number()) < 1) $error_message .= "You must include at least a phone number or an email address so other members may contact you.";

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
	if($is_saved){
		//redirect page if saved	
        $cStatusMessage->Info("Your changes have been saved.");
		$redir_url="member_detail.php?member_id={$member->getMemberId()}";
  		include("redirect.php");
	} 
}else{
    //build from fields, or build from loaded record
    if ($member->getAction() == "create"){
        $field_array= array(
            'mode'=>'create', 
            'status'=>"A",
            'join_date'=>date('Y-m-d'),
            'expire_date'=>date('Y-m-d', strtotime('+1 years')), 
            'email_updates'=>'7'
        );
        $member->Build($field_array);
    }else{
        $condition = "m.member_id = \"{$member_id}\"";
        $member->Load($condition);

        $is_loaded = false;
        if($member_id == $member->getMemberId()) $is_loaded = true;
        //$page_title = "Edit profile for {$member->getDisplayName()} ({$member->getMemberId()})";
    }
} 
if($member->getStatus() == "I") {

    $cStatusMessage->Info("This member is INACTIVE. They cannot log in and their profile and listings are hidden from view for non-admin users.");
}
if($member->getRestriction() == "1") {
    $cStatusMessage->Info("This member is RESTRICTED. They can only earn, and not spend.");
}
$adminElements="";

        if($member->getAction() != "create"){    
            $member_role_elements = "
                <p>
                    <label for=\"member_role\">
                        <span>Member role *</span>
                        {$p->PrepareFormSelector('member_role', ARRAY_ACCOUNT_ROLE, null, $member->getMemberRole())}
                    </label>
                </p>";
                $member_id_elements = "<input type=\"hidden\" id=\"member_id\" name=\"member_id\" value=\"{$member->getMemberId()}\" />";

        }else{

            $member_role_elements = "<input type=\"hidden\" id=\"member_role\" name=\"member_role\" value=\"0\" />";
            $member_id_elements = "<p>
                <label for=\"member_id\">
                    <span>Member Id  *</span>
                    <input maxlength=\"20\" name=\"member_id\" id=\"member_id\" type=\"text\" value=\"{$member->getMemberId()}\" autofocus />
                    Check on member list for last used
                </label>
            </p>";
        }


        if($cUser->getMemberRole() > 0){
            if($cUser->getMode()=="admin"){
                $adminElements .= "
                 <div class=\"summary\">
                    <h3>Account [Admin controls]</h3>
                   
                    <p>
                        <label for=\"restriction\">  
                            <span>Restriction - restrict if member is over-using the services of others and not offering enough back in return. They will only be able to earn, not spend. *</span>
                            {$p->PrepareFormSelector('restriction', ARRAY_RESTRICTION, null, $member->getRestriction())}
                        </label>
                    </p>
                    <hr />
                                    
                    {$member_id_elements}
                    {$member_role_elements}

                    <p>
                        <label for=\"account_type\">
                            <span>Account type *</span>
                            {$p->PrepareFormSelector('account_type', ARRAY_ACCOUNT_TYPE, null, $member->getAccountType())}
                        </label>
                    </p>

                    <p>
                        <label for=\"join_date\">
                             <span>Join date -  Format as YYYY-MM-DD *</span>
                             <input type=\"text\" id=\"join_date\" name=\"join_date\" value=\"{$member->getJoinDate()}\" maxlength=\"10\" />
                        </label>
                    </p>
                    <p>
                        <label for=\"expire_date\">
                             <span>Renewal date - Format as YYYY-MM-DD *</span>
                             <input type=\"text\" id=\"expire_date\" name=\"expire_date\" value=\"{$member->getExpireDate()}\" maxlength=\"10\" />
                        </label>
                    </p>
                    <p>
                        <label for=\"admin_note\">
                            <span>Admin note</span>
                            <textarea name=\"admin_note\" id=\"admin_note\">{$member->getAdminNote()}</textarea>
                        </label>
                    </p>
                </div> 
                 ";
            }
        } else{
             $adminElements .= $member_id_elements;
        }

        //CT todo - use template.

        $output = "
        <form action=\"". HTTP_BASE ."/member_edit.php\" method=\"post\" name=\"form\" id=\"form\" class=\"layout2\">
            <input type=\"hidden\" id=\"person_id\" name=\"person_id\" value=\"{$member->getPerson()->getPersonId()}\" />
            <input type=\"hidden\" id=\"primary_member\" name=\"primary_member\" value=\"{$member->getPerson()->getPrimaryMember()}\" />
            <input type=\"hidden\" id=\"action\" name=\"action\" value=\"{$member->getAction()}\" />
            <input type=\"hidden\" id=\"status\" name=\"status\" value=\"{$member->getStatus()}\" />
            
            {$adminElements}
            <h3>Profile</h3>
            <p>Tell us a bit about yourself. You can also say what you generally offer and what you are interested in.</p>
            <p>
                <label for=\"first_name\">
                    <span>First name  *</span>
                    <input maxlength=\"200\" name=\"first_name\" id=\"first_name\" type=\"text\" value=\"{$member->getPerson()->getFirstName()}\">
                </label>
            </p>
            <p>
                <label for=\"last_name\">
                    <span>Family name  *</span>
                    <input maxlength=\"200\" name=\"last_name\" id=\"last_name\" type=\"text\" value=\"{$member->getPerson()->getLastName()}\">
                </label>
            </p>
            <p>
                <label for=\"member_note\">
                    <span>About you</span>
                    <textarea name=\"about_me\" id=\"about_me\">{$member->getPerson()->getAboutMe()}</textarea>
                </label>
            </p>
            <p>
                <label for=\"age\">
                    <span>Age range *</span>
                    {$p->PrepareFormSelector('age', ARRAY_AGE, '-- Select age range --', $member->getPerson()->getAge())} 
                </label>
            </p>
            <p>
                <label for=\"gender\">
                    Gender<br />
                    {$p->PrepareFormSelector('sex', ARRAY_SEX, "-- Select gender --", $member->getPerson()->getAge())}
                </label>
            </p>
            <h3>Contact details</h3>
   
            <p>
                <label for=\"email\">
                    <span>Email address</span>
                    <input maxlength=\"200\" name=\"email\" id=\"email\" type=\"text\" value=\"{$member->getPerson()->getEmail()}\">
                </label>
            </p>            
            <p>
                <label for=\"phone1_number\">
                    <span>Phone number</span>
                    <input maxlength=\"200\" name=\"phone1_number\" id=\"phone1_number\" type=\"text\" value=\"{$member->getPerson()->getPhone1Number()}\">
                </label>
            </p>

            <h3>Where you live</h3>
            <p>Only you and the administrators of the site can see your full address. Everyone else will see just your neighbourhood and first part of the post code. We won't force you to set your full address here, it's up to you.</p>
            <p>
                <label for=\"address_street1\">
                    <span>" . ADDRESS_LINE_1 . "</span>
                    <input maxlength=\"200\" name=\"address_street1\" id=\"address_street1\" type=\"text\" value=\"{$member->getPerson()->getAddressStreet1()}\">
                </label>
            </p>
            <p>
                <label for=\"address_street2\">
                    <span>" . ADDRESS_LINE_2 . " *</span>
                    <input maxlength=\"200\" name=\"address_street2\" id=\"address_street2\" type=\"text\" value=\"{$member->getPerson()->getAddressStreet2()}\">
                </label>
            </p>
            <p>
                <label for=\"address_city\">
                    <span>" . ADDRESS_LINE_3 . "  *</span>
                    <input maxlength=\"200\" name=\"address_city\" id=\"address_city\" type=\"text\" value=\"{$member->getPerson()->getAddressCity()}\">
                </label>
            </p>
            <p>
                <label for=\"address_state_code\">
                    <span>" . STATE_TEXT . "</span>
                    <input maxlength=\"200\" name=\"address_state_code\" id=\"address_state_code\" type=\"text\" value=\"{$member->getPerson()->getAddressStateCode()}\">
                </label>
            </p>            
            <p>
                <label for=\"address_post_code\">
                    <span>" . ZIP_TEXT . " *</span>
                    <input maxlength=\"200\" name=\"address_post_code\" id=\"address_post_code\" type=\"text\" value=\"{$member->getPerson()->getAddressPostCode()}\">
                </label>
            </p>
            <input type=\"hidden\" id=\"address_country\" name=\"address_country\" value=\"". DEFAULT_COUNTRY ."\" />
            <p>
                <label for=\"email_updates\">

                    <span>How often would you like to receive email updates for offers and wants? </span>
                    {$p->PrepareFormSelector('email_updates', ARRAY_EMAIL_UPDATES, null, $member->getEmailUpdates())}
                </label>
            </p>
            <p>
                <label for=\"email_updates\">
                    Do you wish to confirm payments that are made to you? 
                    {$p->PrepareFormSelector('confirm_payments', array("No - receive the transfer automatically", "Yes - I want to approve before the transfer is made"), null, $member->getConfirmPayments())}
                </label>
            </p>

            <div>* denotes a required field</div>
            <p class=\"summary\">
                <input name=\"submit\" id=\"submit\" value=\"Submit\" type=\"submit\" />
                <a href=\"#\" onclick=\"goBack()\" class=\"cancel\">Cancel</a>
            </p>
        </form>";


//$page_title = ($action == "create") ? "Create new member" : "Update {$member->getDisplayName()} ({$member_id})";
if($member->getMemberId() == $cUser->getMemberId()) {
    $page_title = "Update My Profile";
} else{
    $page_title = ($action == "create") ? "Create new member" : "Update Member #{$member_id}";
}

//if($mode == "admin") $page_title .= " [ADMIN MODE]";
$p->page_title = $page_title;

$p->DisplayPage($output);


?>
