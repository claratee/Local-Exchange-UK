<?php
include_once("includes/inc.global.php");

$cUser->MustBeLevel(2);


$member = new cMemberUtils; //CT load the extended

try{


	$condition = "m.member_id=\"{$_REQUEST["member_id"]}\"";
	$member->Load($condition);

	if($_POST['submit']){
		if(ProcessData($_POST)){
			$cStatusMessage->Info("The changes were saved.");
			$redir_url="admin_menu.php";
    	  	include("redirect.php");
		}
	}




	if($member->getStatus() == 'A'){
		$p->page_title = "Inactivate member {$member->getDisplayName()} ({$member->getMemberId()})";
		$output = "<p>By them INACTIVE, they will no longer be able to log in or trade. All listings will be expired. You can choose what to do with the record, depending on the reason whiy the account is being set to inactive.</p>";
	}
	else{
		$p->page_title = "Re-activate member {$member->getDisplayName()} ({$member->getMemberId()})";
		$output = "<p>This member is currently inactive. If you make them ACTIVE, they will be able to log in, and start trading.</p>";

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
			$options = array('0'=>'Temporary. Keep personal information and balance.', '1'=>'Permanent. Delete personal information and balance.');
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
                <input name=\"action\" id=\"action\" type=\"hidden\" value=\"change_status\">
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

	function ProcessData () {
		global $member, $cStatusMessage;
		$field_array = array();
		$field_array['status'] = $_POST['status'];
		$field_array['action'] = $_POST['action'];
		$member->Build($field_array);
		//print_r($member->getStatus());
		$success = $member->Save();
		if(!$success) return false;
		if($member->getStatus()=="I"){
			
			

			if($_POST['archive'] == 1){
				//archive the record - delete personal data for GDPR.
				$member->setAction("archive");
				$success = $member->Save();
			} else{
				$listings = new cListingGroupUtils();
				$condition = "member_id = \"{$member->getMemberId()}\"";
				$count = $listings->ExpireAll($condition);
				//$date = new cDateTime("yesterday");
				$cStatusMessage->Info($count . " listings expired.");
			}
		} 	
		return $success;
	}


$p->DisplayPage($output);
?>
