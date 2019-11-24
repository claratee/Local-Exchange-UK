<?php

include_once("includes/inc.global.php");

$cUser->MustBeLoggedOn();
$p->site_section = LISTINGS;


//init
try{
	$isLoaded = false;
	$listing = new cListing();
	$listing_id = $cDB->UnEscTxt($_REQUEST['listing_id']);
	if(!empty($listing_id)){
		$condition = "listing_id=\"{$listing_id}\" AND m.status=\"A\"";
		//exit;	
		$listing->Load($condition);
		if(!empty($listing->getListingId())) $isLoaded = true; 
	}
	// halt if loaded
	if(!$isLoaded){
		$cStatusMessage->Error("Sorry, listing not found with that ID ({$listing_id}). It may no longer be active.");
		include("redirect.php");
	}
	$member_id=$listing->getMemberId();
	//bloody messy
	$member = new cMember;
	$condition = " p.primary_member = \"Y\" and m.member_id=\"{$member_id}\"";

	$member->Load($condition); 

	// //$mLocation = $member->getDisplayEmail() . ", " .$member->getDisplayPhone();
	// $stats = new cTradeSummary();
	// $condition="member_id_from 
	//             LIKE \"{$member_id}\" 
	//             OR member_id_to 
	//             LIKE \"{$member_id}\" 
	//             AND NOT type=\"R\" 
	//             AND NOT status=\"R\"";
	// $stats->Load($condition);

	$output = "";

	$form_action = $cDB->UnEscTxt($_REQUEST['form_action']);
	if($form_action == "update") {
	    $output .= "
	    <div class=\"response success\">
	    	Your changes have been saved.
	    </div>";
	} elseif($form_action == "create"){
	    $output .= "
	    <div class=\"response success\">
	    	New listing created.
	    </div>";
	}
	$adminElements ="";
	//allow edit by the logged in user on self, or committee.
	if(($cUser->getMemberRole() > 0 AND !($site_settings->getKey('USER_MODE'))) OR ($site_settings->getKey('USER_MODE') && $cUser->getMode() == USER_MODE_ADMIN) || $cUser->getMemberId() == $member_id){
	    $output .= "
	    <div>
	    	<a href=\"listing_edit.php?listing_id={$listing->getListingId()}\" class=\"button edit\">
	    		<i class=\"fas fa-pencil-alt\"></i> edit
	    	</a>
	    </div>";
	}
		$output .= "<p class='large'>{$listing->getDescription()}</p>";

	//$array[]=$this->makeLabelArray($title, $value))
	$metadata = "<div class=\"columns2\">".
			$p->WrapLabelValue("Type", $listing->makeTypeDescription()) . 
			$p->WrapLabelValue("Category", $listing->getCategoryName()) . 
			$p->WrapLabelValue("Rate", $listing->getRate()) . 
			$p->WrapLabelValue("Listing ID", $listing->getListingId()) . 
			$p->WrapLabelValue("Last update", $listing->getListingDate()) . 
		"</div>"; 
	$hidden = "<!-- ".
			$p->WrapLabelValue("Status", $listing->getStatus()) . 
			$p->WrapLabelValue("Expires", $listing->getExpireDate()) . 
			$p->WrapLabelValue("Reactivation Date", $listing->getReactivateDate()) . 

			"-->"; 

	$output .= $metadata . $hidden;
	//$output .=$listing->TableFromArray($array);
	//TODO - make into little summary object
	$output .="<br /><!--START include member_summary -->
	<div class=\"profile-wrap\">
		<div class=\"profile-inner\">
			<div class=\"profile-text\">
				<h4><a href=\"member_detail.php?member_id={$member->getMemberId()}\">{$member->getDisplayName()}</a></h4>
				<p>{$member->getDisplayLocation()}</p>
				<p>Phone: {$member->getDisplayPhone()} / Email: {$member->getDisplayEmail()}</p>
			</div>
			<div class=\"profile-avatar\">
				<a href=\"member_detail.php?member_id={$member->getMemberId()}\">avatar </a>
			</div>
		</div>
	</div>	            	
	<!--END include member_summary -->

	";

	$p->page_title = "{$listing->makeTypeDescription()}: {$listing->getTitle()}";
}catch(Exception $e){
	$cStatusMessage->Error($e->getMessage());
}

$p->DisplayPage($output);

include("includes/inc.events.php");

?>
