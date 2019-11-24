<?php

include_once("includes/inc.global.php");

$p->site_section = EXCHANGES;


//include_once("classes/class.trade.php");
//include("includes/inc.forms.validation.php");

//
// Define form elements
//
$field_array = array();
$field_array['mode'] =$cUser->getMode();
//$field_array['member_id_author'] = $cUser->getMemberId();

//CT - todo - tidy
if($_REQUEST['type'] == 'invoice'){
	$field_array['type'] =  TRADE_TYPE_INVOICE;
}else{
	$field_array['type'] =  TRADE_TYPE_TRANSFER;
}

$intro = "";

if(($cUser->getMemberRole() > 0 AND !($site_settings->getKey('USER_MODE'))) OR ($site_settings->getKey('USER_MODE') && $cUser->getMode() == USER_MODE_ADMIN) && $_REQUEST['member_id'] && $_REQUEST['member_id'] != $cUser->getMemberId()){
	
	try{
		$member = new cMember;
		$condition = "m.member_id='{$_REQUEST['member_id']}' AND m.status='A'";
		$member->load($condition);
		$intro .= "<h3>[Admin action] Trading for {$member->getDisplayName()} (#{$member->getMemberId()})</h3>";
	}catch(Exception $e){
		$cStatusMessage->Error("Database error: " . $e->getMessage());
		$p->DisplayPage("Something went wrong");
		exit;
	}
}else{
	$member = $cUser;
}

//CT - todo - tidy
//Ct so we can pass in the member id of the person we want to transact with in a link
//CT one or other of these 
//look for $_REQUEST['member_id_to'];
//look for $_REQUEST['member_id_from'];
//print_r($field_array);



$trade = new cTradeUtils($field_array);
//print_r($trade->getType());

// TODO why is trestricted not being set?
if($trade->getType() == TRADE_TYPE_TRANSFER && $member->getRestriction()==true){
		$cStatusMessage->Error("This account is restricted. This means the member cannot spend, only earn. Contact the administrator if you believe this is in error.");
		include("redirect.php");
}
//$trade->getAction()=($_REQUEST['action'] == 'invoice') ? "invoice" : "transfer";

// if($cUser->member_id == "ADMIN") {
// 	$p->DisplayPage("I'm sorry, you cannot record exchanges while logged in as the ADMIN account.  This is a special account for administration purposes only.<p>To create member accounts go to the <a href=admin_menu.php>Administration menu</a>.");	
// 	exit;
// }

if($trade->getType() == TRADE_TYPE_INVOICE) {
	$page_title = "Invoice another member";
	//CT set default value from the member_id if it exists. can always set it again!
	if(!empty($_REQUEST['member_id_from'])) $trade->setMemberIdFrom($_REQUEST['member_id_from']);
	
	$trade->setMemberIdTo($member->getMemberId());

	
	$intro .= "<p>You are sending an invoice to someone else. They will receive an email notification. Not what you expect? <a href=\"trade.php?type=transfer&member_id={$member_id}\">Transfer instead</a>.</p>";
} else {
	$page_title = "Pay another member";
	
	$trade->setMemberIdFrom($member->getMemberId());
	if(!empty($_REQUEST['member_id_to'])) $trade->setMemberIdTo($_REQUEST['member_id_to']);
	$intro .= "<p>You are setting up a transfer of payment to someone else. They will receive an email notification. Not what you expect? <a href=\"trade.php?type=invoice&member_id={$member_id}\">Invoice instead</a>.</p>";
}		
	
if ($_POST["submit"]){
	$field_array = $_POST;
	$trade->Build($field_array);
	try {
		$trade_id = $trade->ProcessData($field_array);
		$cStatusMessage->Info("Successfully traded. <!-- #{$trade_id}. -->");

		$_POST=array();
		$redir_url="member_trade_menu.php";
		include("redirect.php");	
	}catch(Exception $e){
		$cStatusMessage->Error("Trade failed: " . $e->getMessage());
	
	}
}
//

	$members = new cMemberGroup;
	$members->setOption('active');
	
	list($condition, $label, $actions) = $members->makeSettingFromOption();
	$order_by="p.first_name ASC";
	$members->Load($condition, $order_by);
	//if (JS_MEMBER_SELECT==true)

	//$dropdown_member_from = $members->PrepareMemberDropdown("member_id_from", $trade->getMemberIdFrom());
	//$dropdown_member_to = $members->PrepareMemberDropdown("member_id_to", $trade->getMemberIdTo(), null);

	$categories = new cCategoryGroup();
	//no condition
	$condition=1;
	$categories->Load($condition);
	$dropdown_category = $categories->PrepareCategoryDropdown("category_id", $trade->getCategoryId());
	$form_amount = $p->WrapFormElement("text", "amount", "Amount (in ". UNITS . ") *", $trade->getAmount(), "unit");
	$form_description = $p->WrapFormElement("textarea", "description", "Description", $trade->getDescription(), "");

	if($trade->getType() == TRADE_TYPE_TRANSFER){

			//CT if addmin - otherwise set as member
			// if($trade->getMode() == 'admin'){
			// 	$member_options .= "<p>
		 //        	<label for=\"member_id_from\">
			//             <span>Transfer from member: *</span>
			//             {$members->PrepareMemberDropdown("member_id_from", $trade->getMemberIdFrom())} ({$members->getCount()} members)
			//         </label>
		 //    	</p>";
			// }else{
		$member_options .= "<p class=\"summary\">Current balance for member {$member->getMemberId()} is {$member->getBalance()} " . UNITS . ".</p>";
		
		$member_options .= "<input type=\"hidden\" name=\"member_id_from\" id=\"member_id_from\" value=\"{$trade->getMemberIdFrom()}\" />";
			// }

		$member_options .= "
		<p>
	    	<label for=\"member_id_to\">
	            <span>Pay member: *</span>
	            {$members->PrepareMemberDropdown("member_id_to", $trade->getMemberIdTo(), $member->getMemberId())} ({$members->getCount()} members) 
	        </label>
	    </p>";
	}elseif($trade->getType() == TRADE_TYPE_INVOICE){
		if (null == $trade->getMemberIdTo()) {
			$trade->setMemberIdTo($cUser->getMemberId());
		}

			$member_options = "<p>
	        	<label for=\"member_id_from\">
		            <span>Invoice member: *</span>
		            {$members->PrepareMemberDropdown("member_id_from", $trade->getMemberIdFrom(), $member->getMemberId())} ({$members->getCount()} members) 
		        </label>
	    	</p>";
		
			$member_options .= "
	    	<p>
	        	<label for=\"member_id_to\">
		            <!-- <span>To member *</span>
		            {$cUser->getDisplayName()} ({$cUser->getMemberId()}) -->
		            <input type=\"hidden\" name=\"member_id_to\" id=\"member_id_to\" value=\"{$trade->getMemberIdTo()}\"  />
		        </label>
		    </p>";
		

			
	}else{
		////unknown
	}

	$units = UNITS;


	//<div class=\"summary\"><span class=\"label\">Current balance: </span> <span class=\"value {$pos_neg}\">{$cUser->getbalance()}</span> {$units}. <!-- &nbsp;<a href=\"trade_history.php?member_id={$member_id}\" class=\"\">Your exchange history</a> --></div> 
	$output .= "
		<p>{$intro}</p>
		<form action=\"\" method=\"post\" name=\"\" id=\"\" class=\"layout1\">
			<input type=\"hidden\" id=\"type\" name=\"type\" value=\"{$trade->getType()}\" />
			<input type=\"hidden\" id=\"action\" name=\"action\" value=\"create\" />
			{$member_options}
			

		    <p>
	        	<label for=\"category_id\">
		            <span>Category *</span>
		            {$dropdown_category}
		        </label>
		    </p>	
		    </p>	
		    	   	<p>
	        	<label for=\"description\">
		            {$form_description}
		        </label>
		    </p>	   	
		    <p>
	        	<label for=\"amount\">
		            {$form_amount}
		        </label>
		
		    * denotes a required field
			<p class=\"summary\">
				<input name=\"submit\" id=\"submit\" value=\"Submit\" type=\"submit\" />
				
			</p>
		</form>";



// $form->addElement('hidden', 'member_id', $member->getMemberId());
// $form->addElement('hidden', 'mode', $_REQUEST["mode"]);
// $form->addElement("html", "<TR></TR>");  // TODO: Move this to the header

// if (MEMBERS_CAN_INVOICE==true) // Invoicing turned on in config, so let member choose

// 	$form->addElement("select", "typ", "Transaction Type", array('null'=>"-- Select transaction type --",'transfer'=>"Transfer",'invoice'=>"Invoice"));
// else // Invoicing turned off, this form now only functions to transfer money
// 	$form->addElement('hidden', 'typ', 0);	
	




$p->page_title = $page_title;
$p->DisplayPage($output);  // just display the form
// }
	//$form->addElement("html","<tr><td>To Member ".$name_list->DoNamePicker()."</td></tr>");
//else
	//$form->addElement("select", "member_to", "Transfer to Member", $name_list->MakeNameArray());

// $category_list = new cCategoryList();
// $form->addElement('select', 'category', 'Category', $category_list->MakeCategoryArray());
// $form->addElement("text", "units", "# of ". UNITS ."", array('size' => 5, 'maxlength' => 10));
// if(UNITS == "Hours") {
// 	$form->addElement("text","minutes","# of Minutes",array('size'=>2,'maxlength'=>2));
// }
// $form->addElement('static', null, 'Enter a Brief Description of the Exchange', null);
// $form->addElement('textarea', 'description', null, array('cols'=>50, 'rows'=>4, 'wrap'=>'soft'));
// $form->addElement('submit', 'btnSubmit', 'Submit');

// //
// // Define form rules
// //
// //$form->addRule('description', 'Enter a description', 'required');
// $form->registerRule('verify_not_self','function','verify_not_self');
// $form->addRule('member_to', 'You cannot transfer to yourself', 'verify_not_self');
// $form->registerRule('verify_selection','function','verify_selection');
// $form->addRule('category', 'Choose a category', 'verify_selection');
// $form->addRule('member_to', 'Choose a member', 'verify_selection');
// $form->addRule('description', 'Description too long - maximum length is 255 characters', 'verify_max255');

// if(UNITS == "Hours") {
// 	$form->registerRule('verify_whole_hours','function','verify_whole_hours');
// 	$form->addRule('units', 'Hours entered must be a positive, whole number', 'verify_whole_hours');
// 	$form->registerRule('verify_even_minutes','function','verify_even_minutes');
// 	$form->addRule('minutes', 'Enter 15, 30, or 45 (or other numbers in 3 minute increments)', 'verify_even_minutes');
// } else {
// 	$form->registerRule('verify_valid_units','function','verify_valid_units');
// 	$form->addRule('units', 'Enter a positive number with no more than two decimal points', 'verify_valid_units');
// }


// //
// // Then check if we are processing a submission or just displaying the form
// //
// if ($form->validate()) { // Form is validated so processes the data
//    $form->freeze();
//  	$form->process('process_data', false);
// } else {
//    $p->DisplayPage($form->toHtml());  // just display the form
// }







	

/* 
function verify_not_self($element_name,$element_value) {
	global $member;
	$member_id = substr($element_value,0, strpos($element_value,"?"));
	if ($member_id == $member->getMemberId())
		return false;
	else
		return true;
}

function verify_valid_units($element_name,$element_value) { 
	if ($element_value < 0)
		return false; 
	elseif ($element_value * 100 != floor($element_value * 100)) 
		return false;	// allow no more than two decimal points
	else
		return true;
}

function verify_even_minutes ($z, $minutes) { // verifies # of minutes entered represents an evenly
	if($minutes/60*1000 == floor($minutes/60*1000)) 	// divisible fraction w/ no more than 3
		return true;												//	decimal points
	else
		return false;
}

function verify_whole_hours ($z, $hours) {
	if(abs(floor($hours)) != $hours)
		return false;
	else
		return true;
}
*/

?>
