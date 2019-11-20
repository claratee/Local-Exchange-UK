<?php
include_once("includes/inc.global.php");

$cUser->MustBeLevel(2);


$member = new cMemberUtils;

try{

	if($_POST['submit']){
		processData($_POST);
	}

	$condition = "m.member_id={$_REQUEST["member_id"]}";
	$success = $member->Load($condition);




	if($member->getStatus() == 'A'){
		$p->page_title = "Inactivate member {$member->getDisplayName()} ({$member->getMemberId()})";
		$output = "<p>This member is currently active. If you make them INACTIVE, they will no longer be able to log in or trade. Other members will not be able to find them or their listings. You may do this if the member does not with to be a part of the community. It is usual to give a grace period described in the community terms, and then remove the personal information and take the balance to 0.</p>";
	}
	else{
		$p->page_title = "Re-activate member {$member->getDisplayName()} ({$member->getMemberId()})";
		$output = "<p>This member is currently inactive. If you make them ACTIVE, they will be able to log in, and start trading. Depending on how the record was kept, you may have to populate the data again, befor the member can have access. Any trading record will still be retained.</p>";

	}
	$output .= displayStatusChangeButton($member);	
	// $p->page_title .= $member->PrimaryName() ." (". $member->member_id .")";

	// include("includes/inc.forms.php");
	// include_once("classes/class.news.php");
}catch(Exception $e){
	$cStatusMessage->Error($e->getMessage());
		$redir_url="admin_menu.php";
	    include("redirect.php");
}


function displayStatusChangeButton($member) { // TODO: Should use SaveMember and should reset $this->password
       global $p;
       if($member->getStatus() == "A"){
			$options = array('0'=>'Leave record intact', '1'=>'Archive account - transfer balance to fund account and delete PII');
			$archive_dropdown = "<p>
	                <label for=\"archve\">
	                    How do you want to treat the record?<br />" . $p->PrepareFormSelector('archive', $options, null, $_POST['archive']) . "
	                </label>
	            </p>";
	         $button_text ="Inactivate account";
	         $status ="I";
       }else{
			$archive_dropdown ="";
	         $status ="A";
	         $button_text ="Make account active again";

       }
       
            //CT todo - use template.
            $output = "
            <form action=\"{$_SERVER['PHP_SELF']}?member_id={$member->getMemberId()}\" method=\"post\" name=\"form\" id=\"form\" class=\"layout2\">
	            {$archive_dropdown}
                <input name=\"member_id\" id=\"member_id\" type=\"hidden\" value=\"{$member->getMemberId()}\">
                <input name=\"status\" id=\"status\" type=\"hidden\" value=\"{$status}\">
                <p><input name=\"submit\" id=\"submit\" class=\"large\" value=\"{$button_text}\" type=\"submit\" /></p>
            </form>";
            
        
        return $output;
    }

	// $form->addElement("hidden", "member_id", $_REQUEST["member_id"]);

	// if($member->status == 'A') {
	// 	$form->addElement("static", null, "Are you sure you want to inactivate this member?  They will no longer be able to use this website, and all their listings will be inactivated as well.", null);
	// 	$form->addElement("static", null, null, null);
	// 	$form->addElement('submit', 'btnSubmit', 'Inactivate');
	// } else {
	// 	$form->addElement("static", null, "Are you sure you want to re-activate this member?  Their listings will need to be reactivated individually or new ones created.", null);
	// 	$form->addElement("static", null, null, null);
	// 	$form->addElement('submit', 'btnSubmit', 'Re-activate');
	// }

	// if ($form->validate()) { // Form is validated so processes the data
	//    $form->freeze();
	//  	$form->process("process_data", false);
	// } else {  // Display the form
	// 	$p->DisplayPage($form->toHtml());
	// }

	function processData ($field_array) {
		global $p, $member;
		$build_array = array();
		$build_array['status'] = $field_array['status'];
		$member->Build($build_array);
		print_r($member->getStatus());
		$member->Save();
		// if($field_array['status'] == 'I') {

		// 	$success = $member->DeactivateMember();
		// 	$listings = new cListingGroup(OFFER_LISTING);
		// 	$listings->LoadListingGroup(null,null,$member->member_id);
		// 	$date = new cDateTime("yesterday");
		// 	if($success)
		// 		$success = $listings->ExpireAll($date);
		// 	if($success) {
		// 		$listings = new cListingGroup(WANT_LISTING);
		// 		$listings->LoadListingGroup(null,null,$member->member_id);
		// 		$success = $listings->ExpireAll($date);
		// 	}
		// } else {
		// 	$success = $member->ReactivateMember();
		// }

		// if($success)
		// 	$output = "Changes to member status saved.";
		// else
		// 	$output = "There was an error changing the member's status.  Please try again later.";	
				
		
	}


$p->DisplayPage($output);
?>
