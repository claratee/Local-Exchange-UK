<?php

include_once("includes/inc.global.php");

$cUser->MustBeLevel(2);
$p->site_section = EXCHANGES;
$p->page_title = "Reverse an Exchange";


//include_once("classes/class.trade.php");
//include("includes/inc.forms.php");

//
// Define form elements
//
$cUser->MustBeLoggedOn();

//$p->site_section = ADMINISTRATION;
$p->page_title = "For which trade?";
$trades = new cTradeGroup;

// $option = (!empty($_REQUEST['option']) && ($cUser->getMemberRole() > 0 AND !($site_settings->getKey('USER_MODE'))) OR ($site_settings->getKey('USER_MODE') && $cUser->getMode() == USER_MODE_ADMIN)) ? $_REQUEST['option'] : "member";

$members->setOption($option);
list($condition, $label, $actions_keys) = $members->makeSettingFromOption();
//print($condition);
//$condition = "m.status=\"A\" ORDER BY p.first_name";
$action = $_REQUEST["action"];
//can only hold one?
$values = $_REQUEST["values"];
if()

if ($_REQUEST["trade_id"]){
	$fieldArray = $_REQUEST;
	processData($fieldArray);
}

try{
	$trades = new cTradeGroup;

	$condition = "trade_date > (CURDATE() - INTERVAL 3 MONTH) AND `t`.`status`=\"V\"";
	$trades->Load($condition);

$row_output = "";
	foreach($trades->getItems() as $trade) {
		//CT: use css styles not html colors - cleaner
		$rowclass = ($i % 2) ? "even" : "odd";


		$row_output .="
			<tr class=\"{$rowclass} {$statusclass}\">
				<td>
					<a href=\"trade_reverse.php?trade_id={$trade->getTradeId()}\">{$trade->getTradeDate()}</a>
				</td>
				<td class=\"units\">
					{$trade->getAmount()}
				</td>	
								<td class=\"\">
					{$trade->getMemberIdFrom()}
				</td>	
								<td class=\"\">
					{$trade->getMemberIdTo()}
				</td>			
				<td>
					{$trade->getCategoryName()}
				</td>
				<td>
					{$trade->getDescription()} <span class=\"metadata\">trade id: {$trade->getTradeId()}</span>
				</td>

			</tr>";
		$i+=1;

	} // end loop to force display of inactive members off


	$output .="
		<p>Choose the trade you want to leave feedback for. Showing trades completed in last three months.</p>
		<table class=\"tabulated\">
			<tr>
				<th>Date</th>
                <th class='units'>Amount</th>
				<th>From</th>
				<th>To</th>
				<th>Category</th>
				<th>Description</th>
			</tr>
			{$row_output}
		</table>
		<div class='summary'>Trades ({$i} found in last 3 months).</div>";






	// $i=0;
	// if(sizeof($trades->getItems()>0)) {
	// 	foreach($trades->getItems() as $trade) {	
	// 		if($trades->getType() == TRADE_TYPE_REVERSAL or $trades->getStatus() == TRADE_TYPE_REVERSAL)
	// 			continue;	// No reason to list reversed trades, so let's skip 'em
		
	// 		if($i % 2)
	// 			$bgcolor = "#EEEEEE";
	// 		else
	// 			$bgcolor = "#FFFFFF";
				
	// 		/
			
	// 		if(!$feedback_member) {	// Member hasn't left feedback yet, show link
	// 			$date = new cDateTime($trade->trade_date);	
	// 			$trade_date = $date->ShortDate();
						
	// 			$output .= "<TR VALIGN=TOP BGCOLOR=". $bgcolor ."><TD><FONT SIZE=2><A HREF=feedback.php?author=". $member->member_id ."&about=". $member_id_other ."&trade_id=". $trade->trade_id ."&mode=".$_REQUEST["mode"] .">". $trade_date ."</A></FONT></TD><TD><FONT SIZE=2>". $member_id_other ."</FONT></TD><TD><FONT SIZE=2>". $trade->category->description ."</FONT></TD><TD ALIGN=RIGHT><FONT SIZE=2>". $trade->amount ."&nbsp;</FONT></TD><TD><FONT SIZE=2>". $cDB->UnEscTxt($trade->description) ."</FONT></TD></TR>";	
	// 			$i+=1;
	// 		} 	
	// 	}
	// }

	// $output .= "</TABLE>";

	if($i == 0) {
		$output .= "There are no exchanges to reverse in the last 3 months.";
	}




}catch(Exception $e){
	$cStatusMessage->Error($e->getMessage());
}

$p->DisplayPage($output);




?>
