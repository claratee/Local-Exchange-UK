<?php
include_once("includes/inc.global.php");
//$cUser->MustBeLevel(1);
$cUser->MustBeLoggedOn();

//$p->site_section = ADMINISTRATION;
$p->page_title = "For which member?";
$members = new cMemberGroup;

$option = (!empty($_REQUEST['option']) && ($cUser->getMemberRole() > 0 AND !($site_settings->getKey('USER_MODE'))) OR ($site_settings->getKey('USER_MODE') && $cUser->getMode() == USER_MODE_ADMIN)) ? $_REQUEST['option'] : "member";

$members->setOption($option);
list($condition, $label, $actions_keys) = $members->makeSettingFromOption();
//print($condition);
//$condition = "m.status=\"A\" ORDER BY p.first_name";
$action = $_REQUEST["action"];
//can only hold one?
$values = $_REQUEST["values"];


if ($_REQUEST["member_id"]){
	$fieldArray = $_REQUEST;
	processData($fieldArray);
}


$members->Load($condition);

	$row_output = "";
	foreach($members->getMembers() as $member) {
		//CT: use css styles not html colors - cleaner
		$rowclass = ($i % 2) ? "even" : "odd";

		//$postcode = $member->getPerson()->getAddressPostCode());
		
		$row_output .="
			<li><a href=\"{$p->makeLinkSelf(Array('member_id'=>$member->getMemberId()))}\">{$member->getDisplayName()} (#{$member->getMemberId()})</a></li>";
		$i+=1;
	
	 } // end loop to force display of inactive members off


$output .="
	<h2>{$label}</h2>
	<p>Choose the member account you want to perform the action &quot;{$action}&quot;.</p>
		<ul class=\"selector-y\">
			{$row_output}
		</ul>
		<div class='summary'>{$label} ({$i} found).</div>";


//$p->DisplayPage($output); 

// //$form->addElement("header", null, "For which member?");
// //$form->addElement("html", "<TR></TR>");
// $show_inactive = (!empty($_REQUEST["show_inactive"]))? true : false;
// $action = $_REQUEST["action"];
// $output = "";

// $get_string = "";
// if (isset($_REQUEST["get1"])) $get_string .= "&get1=" . $_REQUEST["get1"];
// if (isset($_REQUEST["get1val"])) $get_string .= "&get1val=" . $_REQUEST["get1val"];

// if(empty($show_inactive)){
// 	$output .= $p->Wrap("<strong>Show active members</strong> | <a href='member_choose.php?action={$action}&show_inactive=true{$get_string}'>Show all members</a>", "p", "small");
// }else{
// 	$output .= $p->Wrap("<a href='member_choose.php?action={$action}{$get_string}'>Show active members</a> | <strong>Show all members</strong>", "p", "small");
// }




$p->DisplayPage($output);
	
// if ($form->validate()) { // Form is validated so processes the data
//    $form->freeze();
//  	$form->process("process_data", false);
// } else {  // Display the form
// 	$output .= $form->toHtml();
// 	
// }

function processData ($fieldArray) {
	//print_r($fieldArray);
	$redir_url="{$fieldArray['action']}.php?member_id={$fieldArray['member_id']}";
	//print_r($redir_url);
	//$redir_url="{$fieldArray['action']}.php?mode=admin&member_id={$$fieldArray['member_id']}";
  	include("redirect.php");
	exit;	
}

?>
