<?php
//CT tod - make better, just being lazy here
// if(!isset($mode)) {
// 	$mode = "member";
// 	if (!empty($_REQUEST['mode'])) {
// 		if($cUser->getMemberRole() > 0 && $_REQUEST['mode'] == "admin"){
// 			$mode = "admin";
// 		}
// 	} //admin or member (default)

// }
$menu_string ="";
if($cUser->getMemberId() == $member_id OR $cUser->getMode()=="admin"){
	$menu_string .="
		<a href=\"member_edit.php?member_id={$member_id}\" class=\"button edit\"><i class=\"fas fa-pencil-alt\"></i> edit</a>";
	 if($member->getStatus() == "A"){
	 	$menu_string .=" <a href=\"listing_manage.php?type=O&member_id={$member_id}\" class=\"button\"><i class=\"fas fa-hand-holding-heart\"></i> Manage offers</a>
		<a href=\"listing_manage.php?type=W&member_id={$member_id}\" class=\"button\"><i class=\"fas fa-hand-holding\"></i> Manage wants</a>
		";
	 }
		
}

if(($cUser->getMemberId() != $member_id  OR $cUser->getMode()=="admin") AND $member->getStatus() == "A"){
	
	$menu_string .="
	<div class=\"context-trade-menu\">
		<a href=\"trade.php?type=transfer&member_id_to={$member_id}\" class=\"button action\"><i class=\"fas fa-sign-out-alt\"></i> Pay</a>
		<a href=\"trade.php?type=invoice&member_id_from={$member_id}\" class=\"button action\"><i class=\"fas fa-receipt\"></i> Invoice</a>
	</div>";
}
$output .= "<div class=\"context-menu\">{$menu_string}</div>";

?>