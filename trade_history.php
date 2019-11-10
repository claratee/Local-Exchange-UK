<?php
	include_once("includes/inc.global.php");

	$cUser->MustBeLoggedOn();

	//if ($_REQUEST["mode"] == "admin" || $_REQUEST["mode"] == "other") {
	$member_id = (!empty($_REQUEST["member_id"])) ? $cDB->EscTxt($_REQUEST["member_id"]) : $cUser->getMemberId();

	$member = new cMember();


	if($cUser->getMode() == "admin"){
	    $condition = "m.member_id=\"{$member_id}\"";
	} else{
		//only show active users
	    $condition = "m.member_id=\"{$member_id}\" AND m.status=\"A\"";
	}

	$is_success = $member->Load($condition);	
	 
	if(!$is_success){
		$page_title = "Trade history for member - not found";
		$cStatusMessage->Error("Trade history cannot be shown. The member does not exist or no longer active.");
		$output = "Nothing to show.";
	} else{
		$status_label ="";
		if($member->getStatus()=="I") {
    		$cStatusMessage->Info("This member is INACTIVE. They cannot log in and their profile, trades and listings are hidden from view for non-admin users.");
		}
		$page_title = "Trade History for {$member->getDisplayName()} (#{$member_id})";
	


		
		
		
		$cssClass = ($member->getBalance() > 0) ? "positive" : "negative";
			
		$output .= $p->Wrap($p->Wrap("Current balance: ", "span", "label") . $p->Wrap($member->getBalance() . " ". UNITS . ".", "span", "value ". $cssClass), "p", " summary");	
		
		$condition = "t.status = '" .TRADE_STATUS_APPROVED . "' AND (member_id_to='{$member_id}' OR member_id_from=\"{$member_id}\") AND NOT t.type = '" . TRADE_TYPE_REVERSAL . "'";
	//trades relating to this member
		$trades = new cTradeGroup();
		$trades->Load($condition, $member_id);
		//$output .= $trade_group->DisplayTradeGroupUser($member->getBalance());
		//$output .= $trades->Display($member->getBalance());
		//CT without running balance
		$output .= $trades->Display();
		
		$output .= "CT: show record of reversals";
	}

	$p->page_title = $page_title;
	$p->DisplayPage($output);
	

	
?>
	
