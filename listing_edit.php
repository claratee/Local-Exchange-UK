<?php

include_once("includes/inc.global.php");  

$cUser->MustBeLoggedOn();

//listing with extras
$listing = new cListingUtils();



//safely get values
$is_loaded = false;

//$form_action = "create";

function createForValidMember($member_id){
	//global $listing
	$member = new cMember();
	if($member->Load("m.member_id=\"{$member_id}\" AND m.status=\"A\"")){
		return $member;
	}else{
		throw new Exception("Member cannot be found or is inactive");
		
	}
}

if(!empty($_REQUEST["listing_id"])){
	$listing_id =  $cDB->EscTxt($_REQUEST['listing_id']);
	$listing->setFormAction("update");
	$condition = "p.primary_member = \"Y\" and m.status = \"A\" AND listing_id=\"{$listing_id}\"";
	$is_loaded = $listing->Load($condition);

	if(!$is_loaded){
		$cStatusMessage->Error("Cannot load id '{$listing_id}'");
		//$redir_url="index.php";
		//include("redirect.php");
	}
	// only allow committee and above to edit other people's ads
	if(($listing->getMemberId() != $cUser->getMemberId())){
		$cUser->MustBeLevel(1);

	}else{
		$member = $cUser;
	}

}else{
	$listing->setFormAction("create");
	if($_REQUEST["type"] == OFFER_LISTING_CODE){
		$listing->setType(OFFER_LISTING_CODE);
	}else{
		$listing->setType(WANT_LISTING_CODE);
	}
	if(!empty($_REQUEST["category_id"])) $listing->setCategoryId($_REQUEST["category_id"]);

	if($cUser->isAdminActionPermitted() && $_REQUEST['member_id'] && $_REQUEST['member_id'] != $cUser->getMemberId()){
	
		try{

			$member = new cMember;
			$condition = "m.member_id='{$_REQUEST['member_id']}' AND m.status='A'";
			$member->load($condition);
			$page_title = "for {$member->getDisplayName()} (#{$member->getMemberId()})";
		}catch(Exception $e){
			$cStatusMessage->Error("Database error: " . $e->getMessage());
			$p->DisplayPage("Something went wrong");
			exit;
		}
	}else{
		$member = $cUser;
	}
	
}


$type_description = ($listing->getType() == OFFER_LISTING_CODE) ? OFFER_LISTING : WANT_LISTING;


// //admin action... only for committee and above
// $form_mode = (!empty($_REQUEST["form_mode"]) && $cUser->getMemberRole()>0) ? $cDB->EscTxt($_REQUEST['form_mode']) : null;

// //load from id
// $is_loaded = false;
// if(!empty($listing_id) ){
// 	$condition = "p.primary_member = 'Y' and 
//         m.status = 'A' AND listing_id={$listing_id}";
// 	$is_loaded = $listing->Load($condition);
// }



//allow extra controls
//if(!empty($form_action)) $listing->setFormMode($form_action);

if($listing->getFormAction() == "update"){

	// CT user must match
	$page_title = "Edit '{$type_description}': {$listing->getTitle()} " . $page_title;
	//CT doesnt go through build function - todo - should it?
}else{
	//ct hack - just make sure only these 2 values possible
	//if($type == "W") $typeDescription = "Want";
	//else $type == "Offer";
	$page_title = "Create new '{$type_description}' listing " . $page_title;
	$listing->setMemberId($member->getMemberId());

	//CT doesnt go through build function - todo - should it?
}
	$p->page_title = $page_title;





// if form submitted
if ($_POST["submit"]){
	//build object from inputs
	$listing->Build($_POST);

	//print_r($_POST);
	// error catching without PEAR is a bit of a faff, but cant use PEAR anymore.
	$error_message = "";
	if(strlen($listing->getTitle()) < 1) $error_message .= "Title is missing. ";
	if(empty($listing->getCategoryId())) $error_message .= "Category is missing. ";

	//check if errors and save
	$listing_id = 0;
	if(empty($error_message)) {
		try{
			$listing_id = $listing->Save();
		}catch(Exception $e){
			$cStatusMessage->Info("Could not save listing:" . $e->getMessage());
		}
	} else {
		$cStatusMessage->Error($error_message);
	}

	if(!empty($listing_id)){
		//redirect page if saved	
		$cStatusMessage->Info("Changes to the Listing were saved.");
		$redir_url="listing_detail.php?listing_id={$listing_id}";
  		include("redirect.php");
	} 
}



//show form



	
$output .= "
	<form action=\"". HTTP_BASE ."/listing_edit.php\" method=\"post\" name=\"\" id=\"\" class=\"layout2\">
		<input type=\"hidden\" id=\"type\" name=\"type\" value=\"{$listing->getType()}\" />
		<input type=\"hidden\" id=\"listing_id\" name=\"listing_id\" value=\"{$listing->getListingId()}\" />
		<input type=\"hidden\" id=\"member_id\" name=\"member_id\" value=\"{$listing->getMemberId()}\" />
		<input type=\"hidden\" id=\"form_action\" name=\"form_action\" value=\"{$listing->getFormAction()}\" />
		<!-- <input type=\"hidden\" id=\"active\" name=\"active\" value=\"1\" /> -->
		<input type=\"hidden\" id=\"active\" name=\"status\" value=\"{$listing->getStatus()}\" />
		{$member_text}
		<p>
			<label for=\"category_id\">Category *<br />
				{$listing->PrepareCategoryDropdown()}
			</label>
		</p>		
		<p>
			<label for=\"title\">
				Title *<br />
				<input maxlength=\"200\" name=\"title\" id=\"title\" type=\"text\" value=\"{$listing->getTitle()}\">
			</label>
		</p>

		<p>
			<label for=\"description\">Description <br />
				<textarea cols=\"80\" rows=\"20\" wrap=\"soft\" name=\"description\" id=\"description\">{$listing->getDescription()}</textarea>
			</label>
		</p>
		<p>
			<label for=\"rate\">
				Rate (and any other variants)<br />
				<input maxlength=\"50\" name=\"rate\" id=\"rate\" type=\"text\" value=\"{$listing->getRate()}\">
			</label>
		</p>		
			

		<p>
			<input name=\"submit\" id=\"submit\" value=\"Submit\" type=\"submit\" />
			* denotes a required field
		</p>
	</form>";


$p->DisplayPage($output);

?>
