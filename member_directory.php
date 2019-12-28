<?php

include_once("includes/inc.global.php");
$p->site_section = SECTION_DIRECTORY;

$cUser->MustBeLoggedOn();



$members = new cMemberGroup();

//turns on extra options and controls
if(!empty($_REQUEST['option'])) {
	$members->setOption($_REQUEST['option']);
}else{
	$members->setOption("active");
}

//[chris] Search function
//[CT] built on it for readability and use. page has now become a one-stop shop for insights and management by admins, to remove duplication and help maintainability

	//CT language properties for fields...todo put somewhere else!
	$field_array=array();
	$field_array['member_id'] = 'Membership id';
	$field_array['first_name'] = 'First Name';
	$field_array['last_name'] = 'Last Name';
	$field_array['address_street2'] = 'Neighbourhood';
	$field_array['address_post_code'] = 'Postcode';
	$field_array['address_city'] = 'Town/City';
	$field_array['balance'] = 'Balance';
	$field_array['expiry_date'] = 'Expiry date';

		//select fom element
	$order_by_selector = $p->PrepareFormSelector('order', $field_array, null, $_REQUEST["order"]);


	//admin only = options for filtering members
	$option_field_array=array();
	$option_field_array['active'] = 'All active members';
	$option_field_array['all'] = 'Members: include inactive';
	$option_field_array['inactive'] = 'Members: inactive only';
	$option_field_array['restricted'] = 'Members: restricted';
	$option_field_array['not-restricted'] = 'Members: not restricted';
	$option_field_array['role-admin'] = 'Members: admin role';
	$option_field_array['role-committee'] = 'Members: committee role';

	$member_filter_options = $p->PrepareFormSelector('option', $option_field_array, null, $_REQUEST["option"]);


	$admin_extras = "";
	if(($cUser->getMemberRole() > 0 AND !($site_settings->getKey('USER_MODE'))) OR ($site_settings->getKey('USER_MODE') && $cUser->getMode() == USER_MODE_ADMIN)){
		$admin_extras = "<p class=\"l_text\">
			<label>
				<span>[Admin] Options:</span>
				{$member_filter_options}
			</label>
		</p>";
	}

	$output = "
	<form class=\"layout1 summary\" action=\"member_directory.php\" method=\"get\" name=\"form1\" id=\"form1\">
		<input type='hidden' name='mode' id='mode' value='{$cUser->getMode()}' />
		{$admin_extras}
		<p class=\"l_text\">
			<label>
				<span>Filter by:</span>
				<input type='text' name='filter' id='filter' placeholder='Name/s, neighbourhood or postcode' value='".$_REQUEST["filter"]."'>
			</label>
		</p>
		<p class=\"l_text\">
			<label>
				<span>Order by:</span>
				{$order_by_selector}
			</label>
		</p>
		<input name=\"submit\" value=\"Go\" type=\"submit\" />
	</form>


	";
	//CT these are now global vars - less than ideal. TODO: put somewhere
	list($condition, $label, $actions_keys) = $members->makeSettingFromOption();
	//$condition = 1;
	//$members->Load($condition, $order);
	if(!empty($_REQUEST['filter'])){
		//split by commas or spaces
		$filters = explode( ", ", $_REQUEST['filter']);
		if(sizeof($filters) == 1) $filters = explode( ",", $_REQUEST['filter']);
		if(sizeof($filters) == 1) $filters = explode( " ", $_REQUEST['filter']);

		$c = "";
		foreach ($filters as $key => $like) {
			//$c .= ($key==0) ? " AND " : " OR ";
			$c .= "p.first_name LIKE '%{$like}%' OR ";
			$c .= "j.first_name LIKE '%{$like}%' OR ";
			$c .= "p.last_name LIKE '%{$like}%' OR ";
			$c .= "j.last_name LIKE '%{$like}%' OR ";
			$c .= "p.address_street2 LIKE '%{$like}%' OR ";
			$c .= "p.address_city LIKE '%{$like}%'";
		}
		$condition .= " AND ({$c})";
	}
	$order_by = "m.member_id";
	if(!empty($_REQUEST['order'])){
		$order = $_REQUEST['order'];
		switch ($order) {
			case 'first_name':
				$order_by = "p.first_name, m.member_id";
				break;
			case 'last_name':
				$order_by = "p.last_name, m.member_id";
				break;
			case 'address_street2':
				$order_by = "p.address_street2, m.member_id";
				break;
			case 'address_city':
				$order_by = "p.address_city, m.member_id";
				break;
			case 'address_post_code':
				$order_by = "p.address_post_code, m.member_id";
				break;
			case 'expiry_date':
				$order_by = "m.expire_date, m.member_id";
				break;
			case 'balance':
				$order_by = "m.balance, m.member_id";
				break;
		}
	}

	$members->Load($condition, $order_by);


	$row_output = "";
	$i=0;
	$running_balance = 0; //CT shown only in admin mode - to check state of system. useful for checking inactive accounts that are not 0
	
	foreach($members->getItems() as $member) {
		$running_balance = $running_balance + $member->getBalance();



		//CT: use css styles not html colors - cleaner
		$rowclass = ($i % 2) ? "even" : "odd";

		//$postcode = $member->getPerson()->getAddressPostCode());
		
		$row_output .="<tr class='{$rowclass}'>
		   <td>{$member->makeMemberLink()}</td>
		   <td>{$member->getDisplayName()}</td>
		   <td>{$member->getDisplayPhone()}";
		//if (MEM_LIST_DISPLAY_EMAIL==true)  {   
		//	$row_output .= "<div>{$member->getDisplayEmail()}</div>";
		//}
		$row_output .="</td><td>{$member->getDisplayLocation()}</td>";
		
		/*$row_output .="</td><td>{$member->getPerson()->getAddressStreet2()}";
		if (!empty(trim($member->getPerson()->getAddressStreet2())) AND !empty(trim($member->getPerson()->getAddressCity()))){
			$row_output  .= ", ";
		}*/
		

		
		if (MEM_LIST_DISPLAY_BALANCE==true || $cUser->member_role >= 1){
			$row_output .= "<td class='units balance'>{$member->getBalance()} </td>";
		}
		if(($cUser->getMemberRole() > 0 AND !($site_settings->getKey('USER_MODE'))) OR ($site_settings->getKey('USER_MODE') && $cUser->getMode() == USER_MODE_ADMIN))  {   
			$row_output .= "<td class='action nowrap'>{$member->makeActionsButtons($actions_keys, $member->getMemberId())}</td>";
		}
		$row_output .= "</tr>";

		$i++;
	
	 } // end loop to force display of inactive members off


$output .= "<div class=\"scrollable-x\"><table class=\"tabulated\">
	<tr>
		<th class='id' colspan='2'>Member</th>
		<th>Contact</th>
		<th>Location</th>";

if (MEM_LIST_DISPLAY_BALANCE==true || $cUser->getMemberRole() > 0)  {   
	$output .= "<th class='units balance'>Balance</th>";

}
if(($cUser->getMemberRole() > 0 AND !($site_settings->getKey('USER_MODE'))) OR ($site_settings->getKey('USER_MODE') && $cUser->getMode() == USER_MODE_ADMIN))  {   
	$output .= "<th class='action'>Available actions</th>";
}
$output .= "</tr>
			{$row_output}
		</table></div>
		<div class='summary'>{$label} ({$i} found).</div>";
if(($cUser->getMemberRole() > 0 AND !($site_settings->getKey('USER_MODE'))) OR ($site_settings->getKey('USER_MODE') && $cUser->getMode() == USER_MODE_ADMIN)) {
	$output .= "
		<div class='summary'>[Admin] Total (for check balance): {$running_balance} " . UNITS . "</div>";
}
$p->page_title = $label;

//$p->DisplayPage($output); 
include_once("includes/inc.events.php");

$p->DisplayPage($output); 


?>
