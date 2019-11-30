<?php

include_once("includes/inc.global.php");
$p->site_section = LISTINGS;

//include_once("classes/class.listing.php");

// CT - saves a lot of fuss for GDPR for users revealing too much info in the ads if we just dont allow non-members to see ads!
$cUser->MustBeLoggedOn();

//form - done via GET so results can be linked to
//CT this is messy, can we fix? very english-centric
$search_title = "";
/* make slightly safer by at least having a catch - too much trust on query strings! */


try{
	//CT filter conditions all initiated and overwritted as relevant
	$member_id = null;
	$category_id = null;
	$keyword = null;
	$timeframe = null;


	if(!empty($_REQUEST["type"]) && (strtolower($_REQUEST["type"]) == "want" || strtolower($_REQUEST["type"]) == "wanted" || strtoupper($_REQUEST["type"])==WANT_LISTING_CODE)){
			$type_code = WANT_LISTING_CODE;
			$search_title = WANT_LISTING_HEADING;
		
	} else{ //CT assume offer...
		$type_code = OFFER_LISTING_CODE;
		$search_title = OFFER_LISTING_HEADING;
	}
	if(!empty($_REQUEST["member_id"]) && $_REQUEST["member_id"] !="%") {
		$member_id = $_REQUEST["member_id"];
		$search_title .= " for member \"" . $member_id . "\"";
	} 	
	if(!empty($_REQUEST["category_id"]) && $_REQUEST["category_id"] !="%") {
		$category_id = $_REQUEST["category_id"];
		// todo change to english
		$search_title .= ", in category";
	} 

	if(!empty($_REQUEST["keyword"])) {
		$keyword = $_REQUEST["keyword"];
		// todo change to english
		$search_title .= ", keywords " . $keyword;
	} 



	if(!empty($_REQUEST["timeframe"]) AND $_REQUEST["timeframe"] != "0"){
		$timeframe = $_REQUEST["timeframe"];
		if(!is_numeric($timeframe)) $timeframe = WEEKLY;
	    switch ($timeframe) {
	        case DAILY:
	            $period = "day";
	        break;
	        case WEEKLY:
	            $period = "week";
	        break;           
	        case MONTHLY:
	        	$period = "month";
	        break;
	        case "14":
	        	$period = "two weeks";
	        break;
	        case "90":
	        	$period = "three months";
	        break;
	        default:
	            $period = $timeframe . " days";
	        break;
	    }

		$search_title .= " in last {$period}";

	} 


	$listings = new cListingGroup();

	
	$condition = $listings->makeFilterCondition($member_id, $type, $status, $category_id, $timeframe, $keywords);
	// instantiate new cOffer objects and load them
	//print_r($condition);
	$listings->Load($condition);

	//$listing_id = 0;
	/*
	if ($listings->listing && KEYWORD_SEARCH_DIR==true && strlen($_GET["keyword"])>0) { // Keyword specified
		
			foreach($listings->listing as $l) { // Check ->title and ->description etc against Keyword
				
				$mem = $l->member;
				$pers = $l->member->getPerson();
				
				$match = false;
		
				if (strpos(strtolower($l->title), strtolower($_GET["keyword"]))>-1) { // Offer title
					
					$match = true;
				}
				
				if (strpos(strtolower($l->description), strtolower($_GET["keyword"]))>-1) { // Offer description
					
					$match = true;
				}
				
				if ($cUser->IsLoggedOn()) { // Search is only performed on these params if the user is logged in
					
					if (strpos(strtolower($pers->getFirstName()), strtolower($_GET["keyword"]))>-1) { // Member First Name
						
						$match = true;
					}
					
					if (strpos(strtolower($pers->getLastName()), strtolower($_GET["keyword"]))>-1) { // Member Last Name
						
						$match = true;
					}
					
					if (strpos(strtolower($mem->getMemberId()), strtolower($_GET["keyword"]))>-1) { // Member ID
						
						$match = true;
					}
				
					if (strpos(strtolower($pers->getAddressPostCode()), strtolower($_GET["keyword"]))>-1) { // Postcode
						
						$match = true;
					}
				}
				
				if ($match!=true) {
					
					unset($listings->listing[$lID]);
				}
				
				$lID += 1;
		}
	}
	*/

	$p->page_title =$search_title;

	//$output .= "<p>Directories: <a href=\"directory.php\">Printable directory (opt-in)</a></p>";

			$category_id = (!empty($category_id)) ? $category_id : "";
			$member_id = (!empty($member_id)) ? $member_id : "";
			$timeframe = (!empty($timeframe)) ? $timeframe : "";
			$keywords = (!empty($keywords)) ? $keywords : "";

			//print($type_code .  $category_id .  $member_id .  $timeframe .$keywords);

			
			$output = "
				<div class=\"menu\"><a href=\"listing_edit.php?type=". $type . "&category_id={$category_id}\" class=\"button\">Add new in category</a></div>
				<form class=\"layout1 summary\" action=\"listings.php\" method=\"get\" name=\"form1\" id=\"form1\">
					<input type=\"hidden\" name=\"type\" id=\"type\" value=\"{$type}\" />
					<input type=\"hidden\" name=\"member_id\" id=\"member_id\" value=\"{$member_id}\" />
					<p class=\"l_text\">
						<label>
							<span>Category:</span>
							{$listings->PrepareSelectorCategory($category_id)}
						</label>
					</p>
					<p class=\"l_text\">
						<label>
							<span>Timeframe:</span>
							{$listings->PrepareSelectorTimeframe($timeframe)}
						</label>
					</p>
					<!-- 
					<p class=\"l_text\">
						{$listings->PrepareInputKeywords($keywords)}
					</p> -->
					<input name=\"button\" value=\"Search\" type=\"submit\" />
				</form>


				";
	$output .= $listings->Display($show_ids=true);
}catch(Exception $e){
	$cStatusMessage->Error($e->getMessage());
}

$p->DisplayPage($output); 

include("includes/inc.events.php");

?>
