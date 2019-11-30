<?
require_once("includes/inc.global.php");
// require_once("includes/inc.forms.php");

// $p->site_section = EVENTS;
$cUser->MustBeLoggedOn();

if (ALLOW_INCOME_SHARES!=true){
	$cStatusMessage->Error("Income shares is not enabled for this site.");
	include("redirect.php"); // Provision for allowing income ties has been turned off, return to homepage
} 


$ties = new cIncomeTies();
try{
	if((($cUser->getMemberRole() > 0 AND !($site_settings->getKey('USER_MODE'))) OR ($site_settings->getKey('USER_MODE') && $cUser->getMode() == USER_MODE_ADMIN)) && !empty($_REQUEST['member_id']) && !empty($_GET['member_id'])){
		//CT can manage someone else's income shares - inactive users too, just in case
		//CT TODO should be able to search active users to make sure that their inclme shares are not benefitting inactive account - sweep
		$member = new cMember();
		$condition="m.member_id=\"{$_GET['member_id']}\" AND status=\"A\"";
		$member->Load($condition);
		$page_title = "Income sharing for {$member->getDisplayName()} ({$member->getMemberId()})";

		
		//if($member->getDisplayName())
	} else {
	//CT just use the stripped down logged-in user
		$member = $cUser;
		$page_title = "Income sharing";

	}



	$ties->Load($member->getMemberId());
	if(empty($ties->getMemberId())){
		//can I do this
		$field_array = array();
		$field_array["action"] = "create";
		$field_array["member_id"] = $member->getMemberId();
		$field_array["percent"] = "10";
		$ties->Build($field_array);
	}else{
		$ties->setAction("update");
	}


} catch (Exception $e) {
	$cStatusMessage->Error("Income ties: " . $e->getMessage());
		$p->DisplayPage("Something went wrong");
	exit;
}

$p->page_title = $page_title;

$output = "<p>You have the option of contributing a percentage of any income (".UNITS.") you receive to another account.</p>
	<p> If you specify an Income Share, every time you receive ".UNITS." a specified percentage (of your choosing) will automatically be paid to the account of your choice. You can change this arrangement at any time, but you are only allowed to share your income with one other account at a time.</p>";


if ($_POST["submit"]){
	//print_r($_POST);
	$ties->Build($_POST);
	
	$errors = "";
	//print_r($ties->getPercent());
	if(!is_numeric($ties->getPercent()) OR $ties->getPercent() > 100 OR $ties->getPercent() <= 0){
		$errors .= 'Percent share must be a number between 0 and 100.';
	}
	if(empty($ties->getMemberIdTo())){
		$errors .= 'Select a member to share with. ';
	}		
	if($ties->getMemberId() == $ties->getMemberIdTo()){
		$errors .= 'You cannot share a tie with yourself. ';
	}	

	if(empty($errors)){
		$is_success=$ties->Save();
		if($is_success) {
			$cStatusMessage->Info("You are now sharing {$ties->getPercent()}% of your earnings in " . UNITS . " to member {$ties->getMemberIdTo()}.");
		}


	}else{
		$cStatusMessage->Error("Errors found in form: " . $errors);
	}


	// if (!$amount || !$tie_id || !is_numeric($amount) || $amount>99) {
		
	// 	if (!$amount || !$tie_id)
	// 		$output = "Not enough data to proceed.";
	// 	else if (!is_numeric($amount))
	// 		$output = "The percentage must be numeric and must not contain any other characters (e.g. '10' = good input, '10%' = bad input)";
	// 	else if ($amount>99)
	// 		$output = "Sorry, you can't contribute more than 99% of your income to another account - but it's the thought that counts :o).";
		
	// 	$p->DisplayPage($output);
	
	// 	exit;
	// }
	
	//$output = cIncomeTies::saveTie(array("member_id"=>$cUser->member_id, "amount"=>$amount, "tie_id"=>$tie_id))."<p>";
	//$p->DisplayPage($output);
	
	//exit;
}

if ($_POST["remove"]) {
	try{
		$ties->Delete();
		//hooray!
		$cStatusMessage->Info("Tie removed.");
		$ties->Load($member->getMemberId());

	}catch(Exception $e){
		$cStatusMessage->Error("Cannot delete: " . $e->getMessage());
	}
}	
//	$p->DisplayPage($output);
	
	//exit;
	if($ties->getAction() == "create") {
		$output .= "<div class=\"summary\">Income sharing not active.</div>
			<h3>Create income share</h3>"; 
	}else{
		 $output .= "<form method=\"POST\" class=\"layout3\"  action=\"" . $_SERVER['PHP_SELF'] . "\"><input name=\"remove\" id=\"remove\" value=\"Remove income share\"  type=\"submit\"  />ACTIVE income sharing: {$ties->getPercent()}% on ".UNITS." received to member #{$ties->getMemberIdTo()}. </form>
			<h3>Update income share</h3>";  
	}
	
	$members = new cMemberGroup;
	list($condition, $label, $actions_keys) = $members->makeSettingFromOption('active');
	$order_by="p.first_name ASC";
	$members->Load($condition, $order_by);
	
	$output .= "<form method=\"POST\" action=\"" . $_SERVER['PHP_SELF'] . "?member_id={$ties->getMemberId()}\">
		<input type=\"hidden\" name=\"action\" id=\"action\" value=\"{$ties->getAction()}\">
		<p><label for=\"percent\">I would like to share * 
				<input type=\"text\" size=\"3\" maxlength=\"3\" name=\"percent\" id=\"percent\" value=\"{$ties->getPercent()}\">% of any ".UNITS." I receive</label></p>";
	$output .= "
	<p>
		        	<label for=\"member_id_to\">
			            <span>with this member: *</span>" .
			            $members->PrepareMemberDropdown("member_id_to", $ties->getMemberIdTo(), $member->getMemberId()) . " ({$members->countItems()} members)
			        </label>
		    	</p>";
	
	$output .= "<p>
				<input name=\"submit\" id=\"submit\" value=\"Set income share\" type=\"submit\" />
			* required field
			</p>

	</form>";
// } else {
	
// 	$output .= "<font color=green><b>Income Share Active:</b></font><p>";
	
// 	$output .= "You are currently sharing <b>".$myTie->percent."%</b> of your income with account <b>'".$myTie->tie_id."'</b>.<p><a href=income_ties.php?remove=1>Remove Income Share</a><p>If you wish to amend this Income Share you will first need to remove it and then create a new one.";
// }

$p->DisplayPage($output);

?>