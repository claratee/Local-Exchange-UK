<?php

include_once("includes/inc.global.php");
	
include_once("classes/class.feedback.php");
	
$cUser->MustBeLoggedOn();
$member_id = (!empty($_REQUEST["member_id"])) ? $_REQUEST["member_id"] : $cUser->getMemberId();

//CT temp - make nicer. this is just making sure that ony admins can see feedback of members that are inactive.

$member = new cMember();
$condition = "m.member_id =\"{$member_id}\"";
if($cUser->getMode() != "admin") $condition .= " AND m.status='A'";


$member_exists = $member->Load($condition);
if(!$member_exists){
	
	//CT hard stop  until I have time to fix
	$cStatusMessage->Error('Member does not exist or is no longer active.');
	$p->DisplayPage($output);
	exit;
}
if($member->getStatus() == "I") {
    $cStatusMessage->Info("This member is INACTIVE. They cannot log in and their profile and listings are hidden from view for non-admin users.");
}
$p->page_title = "Feedback for {$member->getDisplayName()} ({$member->getMemberId()})";


$condition = "`member_id_about`=\"{$member_id}\"";
$feedback_group_about = new cFeedbackGroup();
$feedback_group_about->Load($condition);
//$feedback_group_as_seller->LoadFeedbackGroup($member_id, SELLER);

if(sizeof($feedback_group_about->getItems()) > 0 ){
	//$output .= $feedback_group_as_seller->TableFromArray($field_array);
	$output .=  "<p class=\"summary\">Feedback: " . $feedback_group_about->DisplaySummary() . "</p>";
	$output .=  $feedback_group_about->Display("about");
} 
else{
	$output .= "<p>No feedback found.</p>";
}

$output .= "<h2>Feedback left for others</h2>";

$condition = "`f`.`member_id_author`=\"{$member_id}\"";
$feedback_group_by = new cFeedbackGroup();
$feedback_group_by->Load($condition);
//$feedback_group_as_buyer->LoadFeedbackGroup();
if(sizeof($feedback_group_by->getItems()) > 0 ){
	$output .= $feedback_group_by->Display("author");
} 
else{
	$output .= "<p>No feedback found.</p>";
}


$p->DisplayPage($output);
	
?>	
