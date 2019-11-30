<?php

include_once("includes/inc.global.php");
$cUser->MustBeLoggedOn();
try{
	if(($cUser->getMemberRole() > 0 AND !($site_settings->getKey('USER_MODE'))) OR ($site_settings->getKey('USER_MODE') && $cUser->getMode() == USER_MODE_ADMIN) && !empty($_REQUEST["member_id"])) {  // Administrator is creating listing for another member
		$member = new cMember();
		
			$member->Load("m.member_id =\"{$_REQUEST["member_id"]}\"");
			$page_title = "Leave feedback for a recent trade by {$member->getDisplayName()}";
		

	}else{
		//CT user the current (session-saved user as member - efficiency)
		$member=$cUser;
		$page_title = "Leave feedback for a recent trade";
	}

	//CT bit messy
	$field_array = array('member_id_author'=>$member->getMemberId());
	$feedback= new cFeedback($field_array);
	
	$trades = $feedback->returnAllValidTrades(); 
	
	if(!$trades || sizeof($trades->getItems()) < 1){
		$output = "You do not have any more trades to leave feedback on. You can leave feedback for trades that have completed in the last three months with an active member, when you haven't written feedback already.";

		
	} else{

		$row_output = "";
		$i=0;
		foreach($trades->getItems() as $trade) {
			//CT: use css styles not html colors - cleaner
			$rowclass = ($i % 2) ? "even" : "odd";


			if($trade->getMemberIdTo() == $member->getMemberId()) {
				//$role = "As Seller";
				$statusclass = "credit";
				$amount = $trade->getAmount();

			}else{
				$role = "As Buyer";
				//$statusclass = "debit";
				$amount = "-" . $trade->getAmount();
			}
			// $row_output .="
			// 	<tr class=\"{$rowclass} {$statusclass}\">
			// 		<td>
			// 			<a href=\"feedback.php?member_id={$member->getMemberId()}&trade_id={$trade->getTradeId()}\">{$trade->getTradeDate()}</a>
			// 		</td>
			// 		<td class=\"units\">
			// 			{$amount}
			// 		</td>
			// 		<td>
			// 			{$role}
			// 		</td>				
			// 		<td>
			// 			{$trade->getCategoryName()}
			// 		</td>
			// 		<td>
			// 			From: {$trade->getMemberIdFrom()}, to: {$trade->getMemberIdTo()}. Description: {$trade->getDescription()} <span class=\"metadata\">trade id: {$trade->getTradeId()}</span>
			// 		</td>

			// 	</tr>";

			$row_output .="
			 	<tr class=\"{$rowclass} {$statusclass}\"><td>
						<a href=\"feedback.php?member_id={$member->getMemberId()}&trade_id={$trade->getTradeId()}\">{$trade->getTradeDate()}</a>
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
			$i++;

		} // end loop to force display of inactive members off


		$output .="
			<p>Choose the trade you want to leave feedback for.</p>
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
			<div class='summary'>Trades ({$i} found).</div>";






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

	}

} catch(Exception $e){
		$cStatusMessage->Error($e->getMessage());

}
	$p->page_title = $page_title;
	$p->DisplayPage($output);

?>
