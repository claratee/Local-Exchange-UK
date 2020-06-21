<?php

include_once("includes/inc.global.php");

$cUser->MustBeLoggedOn();

//only allow standard types
$listing_type = strtoupper($_REQUEST["type"]);
switch($type){
	case WANT_LISTING_CODE:
		$type=WANT_LISTING_CODE;
		$type_single = WANT_LISTING_SINGLE;
		$type_plural = WANT_LISTING_PLURAL;
		$type_description = WANT_LISTING_HEADING;
	break;
	case OFFER_LISTING_CODE:
	default:
		$type=OFFER_LISTING_CODE;
		$type_single = OFFER_LISTING_SINGLE;
		$type_plural = OFFER_LISTING_PLURAL;
		$type_description = OFFER_LISTING_HEADING;
} 

//only allow editing other members in admin mode

if (!empty($_REQUEST["member_id"]) && $cUser->getMode() =="admin"){
	$member_id = $_REQUEST["member_id"];
	$page_title = "Manage {$type_description} for Member #{$member_id}";
}else{
	$member_id = $cUser->getMemberId();
	$page_title = "Manage {$type_description}";

}
$member = new cMember;
$condition = "m.member_id='{$member_id}' AND m.status='A'";
$member->load($condition);


//CT this should be in a property in db
$p->page_title = $page_title;

if ($_POST["submit"]){
	try{
		ProcessData();
	}catch(Exception $e){
		$cStatusMessage->Error($e->getMessage());
	}
	//$listings->Save($vars);
}
//$listings->Load($cUser->getMemberRole());



$listings = new cListingGroupUtils();
//Load($member_id=null, $category_id=null, $timeframe=null, $timeframe=null, $type_code=null)
//$condition = $listings->makeFilterCondition($member_id, null, null, null, $type_code);
//$condition = $listings->makeFilterCondition($member_id, null, null, null, $type);
$condition = $listings->makeFilterCondition($member_id, $type, "%", null, null, null);
$listings->Load($condition);



$i=0;
$row_output =  "";
foreach($listings->getItems() as $listing) {
	//stripy columns
	$className= ($i%2) ? "even" : "odd";
	if($listing->getStatus()=="E" OR $listing->getStatus()=="I") $className .= " expired";
	$listing_id = $listing->getListingId();
	$is_checked=false;
	//CT Todo - get state of checkboxes from select_id array so you can keep state of checked
	$checkbox = PrepareCheckbox($listing_id, $is_checked);
	//<td>{$listing->PrepareStatusDropdown($listing_id)}</td>	
	$row_output .=  "
		<tr class=\"{$className}\">
			<td>{$checkbox}</td>
			<td><div class=\"text\"><a href=\"listing_detail.php?listing_id={$listing_id}\">{$listing->getTitle()} </a></div><span class=\"metadata\">listing_id: {$listing_id}</span></td>
			<td><div class=\"text\">{$listing->getCategoryName()}</div></td>
			<td><div class=\"text\">{$listing->getListingDate()}</div></td>
			<td><a href=\"listing_edit.php?listing_id={$listing_id}\" class=\"button edit\"><i class=\"fas fa-pencil-alt\"></i> edit</a> </td>			
		</tr>";
	$i++;
}



//CT put button for contextual creation
$output = "<p><a href=\"listing_edit.php?type={$type}&member_id={$member->getMemberId()}\" class=\"button\">Add a new {$type_single}</a></p>";
$action_dropdown = PrepareActionDropdown();
if(!empty($row_output)){
	$output .= "
	<!-- START bulk form pages -->
	<form method=\"post\">
		<table class=\"tabulated\">
			<tr>
				<th></th>
				<th>Title</th>
				<th>Category</th>
				<th>Updated</th>
				<th></th>
			</tr>
			{$row_output}
		</table>
		<p><label>Bulk actions: {$action_dropdown}</label> <input id=\"submit\" name=\"submit\" type=\"submit\" value=\"Apply to selected\"><span class=\"metadata\">{$i} items found</span></p>
		
	</form>
	<!-- END bulk form pages -->
	";
} else {
	$output .= "No listings found.";
}
$p->DisplayPage($output);

function ProcessData(){
	global $cStatusMessage, $cDB;
	$status = array();
	// CT arrange permission array according to role selection - makes it easier to process mysql
	$validate=false;
	if(!empty($_POST["action"])){
		foreach($_POST["select_id"] as $select_id){
			// CT if first one...you know this as validate== false
			if (!$validate) {
				$validate=true;
				$condition="";
			}else{
				$condition .=" OR ";
			}
			$condition .= "listing_id={$select_id}";

			//var_dump($select_id);

			//$value=$_POST["permission_" . $page->page_id];
			//$status[$_POST["status_" . $listing->getListing()]][] = $listing->getListing();
		}
		if (!$validate) {
			throw new Exception("No listings selected.");
		}else{
			//CT enums
			switch($_POST["action"]){
				case "D": //delete
				case "E": //expire or hide
				case "A": // active or show
					$action = $_POST["action"];
				break;
				//no default...drop action
			}


			//$key}`=\"{$this->EscTxt($value)}
			if($action == "D") $string_query = $cDB->BuildDeleteQuery(DATABASE_LISTINGS, $condition);
			else {
				//remove all teh expiry/reactivation stuff
				$array = array("status" => $action, "expire_date" => "", "reactivate_date" => "");
				$string_query = $cDB->BuildUpdateQuery(DATABASE_LISTINGS, $array, $condition);
			}
			$cDB->Query($string_query);
		}

	}else{
		throw new Exception("No action selected");
	}
	
}
function PrepareCheckbox($listing_id, $is_checked=false){
	$checked_string = ($is_checked) ? "checked=\"checked\"" : "";
	return "<input type=\"checkbox\" name=\"select_id[]\" value=\"{$listing_id}\" {$checked_string} />";
}
function PrepareActionDropdown(){
    global $p;
    //same as listingUtils status - but with added "delete"
    // relabelled "expire" and "active" to make more robust and uderstandable - "hide" and "show"
    $vars = array("D" => "Delete", "E" => "Hide", "A" => "Show");
    $select_name = "action";
    $output = $p->PrepareFormSelector("action", $vars, "Select action", null);
    return $output;
}