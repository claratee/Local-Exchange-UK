<?php
include_once("includes/inc.global.php");

$cUser->MustBeLevel(2);
if($cUser->getMode() !="admin"){
	$cStatusMessage->Error("You don't have permission to view this page.");
	$redir_url="member_profile_menu.php";
    include("redirect.php");
}

$member = new cMember;

try{
	$condition = "m.member_id={$_REQUEST["member_id"]}";
	$success = $member->Load($condition);




	if($member->getStatus() == 'A'){
		$p->page_title = "Inactivate ";
	}
	else{
		$p->page_title = "Re-activate ";
	}
		
	// $p->page_title .= $member->PrimaryName() ." (". $member->member_id .")";

	// include("includes/inc.forms.php");
	// include_once("classes/class.news.php");







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

	function process_data ($values) {
		global $p, $member;
		
		if($member->status == 'A') {
			$success = $member->DeactivateMember();
			$listings = new cListingGroup(OFFER_LISTING);
			$listings->LoadListingGroup(null,null,$member->member_id);
			$date = new cDateTime("yesterday");
			if($success)
				$success = $listings->ExpireAll($date);
			if($success) {
				$listings = new cListingGroup(WANT_LISTING);
				$listings->LoadListingGroup(null,null,$member->member_id);
				$success = $listings->ExpireAll($date);
			}
		} else {
			$success = $member->ReactivateMember();
		}

		if($success)
			$output = "Changes to member status saved.";
		else
			$output = "There was an error changing the member's status.  Please try again later.";	
				
		
	}
}catch(Exception $e){
	$cStatusMessage->Error($e->getMessage());
			$action="member_status_change";
		$redir_url="member_choose.php?action={$action}&option=all";
	    include("redirect.php");
}

$p->DisplayPage($output);
?>
