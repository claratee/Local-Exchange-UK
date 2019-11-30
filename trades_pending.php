<?
include_once("includes/inc.global.php");
include_once("classes/class.datetime.php");
include_once("classes/class.trade.php");

/*
 An explanation of different member_decisions statuses in the trades_pending database...
 //CT moved to enums constants in global, so you can read it as words in the code. Humnas need words.

 1 = Member hasn't made a decision regarding this trade - either it is Open or it has been Fulfilled (see 'status' column)
 2 = Member has removed this trade trade from their records
 3 = Member has rejected this trade
 4 = Member has accepted that this trade has been rejected
 
*/
$p->site_section = EXCHANGES;
	$cUser->MustBeLoggedOn();
//CT allow admins to see this page
//$mode = $cUser->getMode();  // Administrator is editing a member's account

if((($cUser->getMemberRole() > 0 AND !($site_settings->getKey('USER_MODE'))) OR ($site_settings->getKey('USER_MODE') && $cUser->getMode() == USER_MODE_ADMIN)) && !empty($_REQUEST["member_id"])){
	//$cUser->MustBeLevel(2);
	$member_id = $_REQUEST["member_id"];
	/* CT just for user experience - check that member exists, and show display name */
	$member = new cMember();
	$condition = "m.member_id =\"{$member_id}\"";
	$member_exists = $member->Load($condition);
	if(!$member_exists){
		
		//CT hard stop  until I have time to fix
		$cStatusMessage->Error('Member does not exist or something went wrong.');
		$p->DisplayPage($output);
		exit;
	}
	if($member->getStatus() == "I") {
        $cStatusMessage->Info("This member is INACTIVE. They cannot log in and their profile and listings are hidden from view for non-admin users.");
    }
	$p->page_title = "Exchanges Pending for {$member->getDisplayName()}";
	//$form->addElement("header", null, "Edit Member " . $member->getAllNames());
}
else{
	$member_id = $cUser->getMemberId();
	$p->page_title = "Exchanges Pending";
}





//$output = $p->Wrap($output, "p", "quickmenu");
//CT this isnt ideal, but have split into view and actions so at least you get something useful on the page if there is a fail
try{
	$info = doPageAction();
	if($info) $cStatusMessage->info($info);
}catch(Exception $e){
	$cStatusMessage->Error("Action error: " . $e->getMessage());
}
try{
	$output .= doPageView();
}catch(Exception $e){
	$cStatusMessage->Error("Viewing error: " . $e->getMessage());
}










function makeActionLink($base_link, $action, $label){
	return "<a href=\"{$base_link}&action={$action}\">{$label}</a>";
}

function makeRemoveLink($base_link){
	return "<a href=\"{$base_link}&action=remove\" class=\"float-right\"><i class=\"fas fa-times-circle\" title=\"Remove this notice\"></i></a>";
}
//CT make better
function displayTrade($field_array, $filter) {
		//print_r($type);
		global $cUser, $cDB, $member_id, $site_settings;
		//CT this is rubbish
		$base_link = "trades_pending.php?filter={$filter}&member_id={$member_id}&tid={$field_array["trade_pending_id"]}";


		//CT work out what actions are available
		$action_text = "";
		if ($field_array["status"]==TRADE_PENDING_STATUS_OPEN) {

			$css_status = "open";
			//confirm payments
			if($field_array["type"] == TRADE_TYPE_TRANSFER){
				if($field_array["member_id_from"] == $member_id){
					//CT has it been rejected?
					if($field_array["member_to_decision"]  != 3){
						$action_text .= "Transfer requested. Awaiting acceptance... ";
						if($cUser->isAdminActionPermitted()) { 
							$action_text .= makeActionLink($base_link, "cancel", "Cancel");
						}

					}else{
						$action_text .= "Transfer was rejected. ";
						$action_text .= makeActionLink($base_link, "accept_rejection", "Accept rejection") . " | ";
						$action_text .= makeActionLink($base_link, "resend", "Re-send");
					}

				}else{
					$action_text .= makeActionLink($base_link, "confirm", "Accept transfer") . " | ";
					$action_text .= makeActionLink($base_link, "reject", "Reject");
				}
			}
			//confirm invoice
			elseif($field_array["type"] == TRADE_TYPE_INVOICE){
				if($field_array["member_id_to"] == $member_id){
					//CT has it been rejected?
					if($field_array["member_from_decision"] != 3){
						$action_text .= "Invoice sent.  Awaiting confirmation... ";
						if(($cUser->getMemberRole() > 0 AND !($site_settings->getKey('USER_MODE'))) OR ($site_settings->getKey('USER_MODE') && $cUser->getMode() == USER_MODE_ADMIN))  {   
 $action_text .= makeActionLink($base_link, "cancel", "Cancel");
 						}
					}else{
						$action_text .= "Invoice was rejected. ";
						$action_text .= makeActionLink($base_link, "accept_rejection", "Accept rejection") . " | ";
						$action_text .= makeActionLink($base_link, "resend", "Re-send");
					}
				}else{
					$action_text .= makeActionLink($base_link, "confirm", "Pay invoice") . " | ";
					$action_text .= makeActionLink($base_link, "reject", "Reject");
				}
			}

		}else{
			$css_status = "closed";
			
			$action_text .= ($field_array["type"] == TRADE_TYPE_TRANSFER) ? "Transfer accepted!" : "Invoice paid!";
			//$action_text .= makeActionLink($base_link, "remove", "Remove this notice");
			$action_text .= makeRemoveLink($base_link);
		}

		$output .= "<tr class=\"{$css_status}\">
			<td>{$field_array["trade_date"]}</td>
			<td>{$field_array["member_id_from"]}</td>
			<td>{$field_array["member_id_to"]}</td>
			<td>{$cDB->UnEscTxt($field_array["amount"])}</td>
			<td><div class=\"metadata\">#{$field_array['trade_pending_id']}</div>{$cDB->UnEscTxt($field_array["description"])}</td>
			<td>{$action_text}</td>
		</tr>";
		
		return $output;
}
//CT row gets passed in as field_array
function confirmTrade($field_array) {
	//print_r($field_array);
	//exit;
	//CT this is global? HATE IT
	global $member_id;
	//CT just ironing out the oddness of inverting invoices. trades are the same way round regardless of the source.
	//print_r($field_array);
	//CT change the status to "final" to indicate that its a confirmed tradea. will drop the unneeded stuff about decisions but keep all the rest
	$field_array['status'] = TRADE_STATUS_APPROVED;


	//CT little fudge so it saves in the right place...
	//$field_array['trade_id'] = $field_array['trade_pending_id']; //ct ignore id
    $field_array["description"] .= " [confirmed from trade_pending_id {$field_array['trade_pending_id']}]";
    $field_array["action"] = "confirm";



	$trade = new cTradeUtils($field_array);


	//$trade = new cTradeUtils($member, $member_to, htmlspecialchars($trade['amount']), htmlspecialchars($trade['category_id']), 		htmlspecialchars($trade['description']), "T");

	// $member_to = new cMember;
	// $member_to->LoadMember($trade["member_id_to"]);
	// // if ($trade["type"]==TRADE_TYPE_TRANSFER)
	// // 	$member_to->LoadMember($member_id);
	// // else
	// // 	$member_to->LoadMember($trade["member_id_from"]);
		
	// //$member = new cMember;
	// $member_from = new cMember;
	// $member_from->LoadMember($trade["member_id_from"]);

	
	// // if ($trade["type"]==TRADE_TYPE_TRANSFER)
	// // 	$member->LoadMember($trade["member_id_from"]);
	// // else
	// // 	$member->LoadMember($member_id);
	// //retain the info that the trade is invoice or drict transfer. it will be good to know for stats and all kinds...
	//trade_id is response of save if all is well
	return $trade->Save();
	
	// else {
		
	// 		// Has the recipient got an income tie set-up? If so, we need to transfer a percentage of this elsewhere...
		
	// 		$recipTie = cIncomeTies::getTie($member_to->member_id);
			
	// 		if ($recipTie) {
				
	// 			$theAmount = round(($trade['amount']*$recipTie->percent)/100);
				
	// 			$charity_to = new cMember;
	// 			$charity_to->LoadMember($recipTie->tie_id);
	
	// 			$trade = new cTrade($member_to, $charity_to, htmlspecialchars($theAmount), htmlspecialchars(12), htmlspecialchars("Donation from ".$member_to->member_id.""), TRADE_TYPE_TRANSFER);
		
	// 			$status = $trade->MakeTrade();
	// 		}
			
	// 	return true;
	// }
}
//CT Sorry, this is horrendous - just trying to work within what is here, but make safer :) like put in try/catch and not just print out on screen and continue executing
function doPageView(){
	global $cDB, $cUser, $site_settings;

	// CT WRNING THIS function is grabbing values off of the querystring which I know is not a great thing to do. there is also a lot of duplication. 

	//CT enforce enums for filter....
	//this is conflating actions that result in db changes with read views. TODO: fix this!!
	//at least putting in enums make safer, together with the permissions for the actions
	switch($_REQUEST["filter"]){
		//CT actions for you to do
		case "incoming": //payments sent to you
		case "outgoing": //CT invoices to pay...
		case "invoices_sent": //CT invoices to pay...
		case "payments_sent": // CT payments sent to others requiring confirmation
			$filter = $_REQUEST["filter"];
		break;
		default:
			$filter = "summary";
	}



if((($cUser->getMemberRole() > 0 AND !($site_settings->getKey('USER_MODE'))) OR ($site_settings->getKey('USER_MODE') && $cUser->getMode() == USER_MODE_ADMIN)) && !empty($_REQUEST["member_id"])){		//$cUser->MustBeLevel(2);	
		$member_id = $_REQUEST["member_id"];
		//$form->addElement("header", null, "Edit Member " . $member->getAllNames());
	}
	else{
		$member_id = $cUser->getMemberId();
	}

	$pending = new cTradesPending($member_id);



	// $output = "<em>NOTE that only transactions currently pending approval from one member or the other are displayed here. To view your complete
	// 	Exchange History please <a href=trade_history.php?mode=self>click here</a>.</em><p><a href='trades_pending.php?mode={$mode}&member_id={$member_id}'>Summary</a>

	// | <a href='trades_pending.php?action=incoming&member_id={$member_id}'>Payments to Confirm ({$pending->numToConfirm})</a>";

	 $output .= "<p>Showing trades that require approval by either the person receiving the invoice or person accepting payment. For full exchange, <a href=\"trade_history.php?member_id={$member_id}\">View trade history</a>.</p>";




	$tab = "Summary";
	$tabs .= ($filter=="summary") ? "{$tab}" : "<a href=\"trades_pending.php?filter=summary&member_id={$member_id}\">$tab</a>";

	$tab = "Payments to Confirm (" . $pending->numToConfirm .")";
	$tabs .= " | ";
	$tabs .= ($filter=="incoming") ? "{$tab}" : "<a href=\"trades_pending.php?filter=incoming&member_id={$member_id}\">$tab</a>";


	if (MEMBERS_CAN_INVOICE==true) {// No point displaying invoice stats if invoicing has been disabled
		$tab = "Invoices to pay ({$pending->numToPay})";
		$tabs .= " | ";
		$tabs .= ($filter=="outgoing") ? "{$tab}" : "<a href='trades_pending.php?filter=outgoing&member_id={$member_id}'>$tab</a>";
	}

	$tab = "Sent Payments ({$pending->numToHaveConfirmed})";
	$tabs .= " | ";
	$tabs .= ($filter=="payments_sent") ? "{$tab}" : "<a href='trades_pending.php?filter=payments_sent&member_id={$member_id}'>$tab</a>";


	if (MEMBERS_CAN_INVOICE==true) {// ditto
		$tab = "Sent Invoices ({$pending->numToBePayed})";
		$tabs .= " | ";
		$tabs .= ($filter=="invoices_sent") ? "{$tab}" : "<a href='trades_pending.php?filter=invoices_sent&member_id={$member_id}'>$tab</a>";

	}
	$output .= "<h5>{$tabs}</h5>";
	switch($filter) {
		case("incoming"):
			$output .= "<p>The following trades need to be confirmed...</p>";
			$condition = "member_id_to=".$cDB->EscTxt($member_id)." and `type`= \"" . TRADE_TYPE_TRANSFER . "\" AND member_to_decision = 1";		
		break;
		
		case("outgoing"):
			$output .= "<p>The following Invoices need paying...</p>";
			$condition = "member_id_from=".$cDB->EscTxt($member_id)." AND type=\"" . TRADE_TYPE_INVOICE . "\" AND member_from_decision = 1";
		break;
		case("payments_sent"):		
			$output .= "<p>You are awaiting acceptance of the following Payments...</p>";
			
			/*
			$cDB->Query("INSERT INTO " . DATABASE_TRADES_PENDING . " (trade_date, member_id_from, member_id_to, amount, category_id, description, typ) VALUES (now(), ". 	$cDB->EscTxt($member->member_id) .", ". $cDB->EscTxt($member_to_id) .", ". $cDB->EscTxt($values["units"]) .", ". $cDB->EscTxt($values["category_id"]) .", ". 	$cDB->EscTxt($values["description"]) .", \"T\");");
			*/
			
			$condition = "member_id_from=".$cDB->EscTxt($member_id)." AND type=\"" . TRADE_TYPE_TRANSFER . "\" AND member_from_decision = 1";
		
			
		
		break;
		
		
		case("invoices_sent"):
		
			$output .= "<p>You are awaiting payment for the following Invoices...</p>";
			
			/*
			$cDB->Query("INSERT INTO " . DATABASE_TRADES_PENDING . " (trade_date, member_id_from, member_id_to, amount, category_id, description, typ) VALUES (now(), ". 	$cDB->EscTxt($member->member_id) .", ". $cDB->EscTxt($member_to_id) .", ". $cDB->EscTxt($values["units"]) .", ". $cDB->EscTxt($values["category_id"]) .", ". 	$cDB->EscTxt($values["description"]) .", \"T\");");
			*/
			
			$condition = "member_id_to=".$cDB->EscTxt($member_id)." and type=\"" . TRADE_TYPE_INVOICE ."\" and member_to_decision = 1";
		
			
		break;
		
		default:
			//nothing
			
		break;
	}

	if($filter=="summary"){
		//$output .= 
		//put the summary here
		$temp="";
		if (MEMBERS_CAN_INVOICE==true) $temp .= "<li>I need to pay ".$pending->numToPay." Invoices</li>";
		$temp .= "<li>I need to accept ".$pending->numToConfirm." Incoming Payments<br /><br /></li>";
			
		if (MEMBERS_CAN_INVOICE==true) $temp .= "<li>I am awaiting payment for ".$pending->numToBePayed." Invoices</li>";
	
		$temp .= "<li>I am awaiting acceptance of ".$pending->numToHaveConfirmed." Outgoing Payments</li>";
		$output .= "<ul>{$temp}</ul>";
	}
	else{
		$query_string = "SELECT * FROM " . DATABASE_TRADES_PENDING . " where {$condition}";
		if($query = $cDB->Query($query_string)){
			
			
			$table_rows="";
			while($row = $cDB->FetchArray($query)) {
				$table_rows .= displayTrade($row,$filter);
			}
			if(empty($table_rows)){
				$output .= "<p><em>None found!</em></p>";
			}else{
				$output .= "
				<table class=\"tabulated\">
					<tr>
						<th>Date</th>
						<th>Payer</th>
						<th>Payee</th>
						<th class=\"amount\">Amount pending</th>
						<th>Description</th><th>Action</th>
					</tr>
					{$table_rows}
				</table>
				";

			}
		
		}else{
			throw new Exception('Cannot read from database.');
		}
		




	}
	return $output;
}

function doPageAction(){
	global $cUser, $member_id, $cDB;
	switch($_REQUEST["action"]){
		//CT ENUMS actions
		case "resend": // action
		case "accept_rejection": // action
		case "reject": // action
		case "remove": // action
		case "confirm": // action
		case "cancel": // CT action NEW...so users can cancel/withdraw, if paid by other means
			$action = $_REQUEST["action"];
			$query_string = "SELECT * FROM " . DATABASE_TRADES_PENDING . " where trade_pending_id=".$cDB->EscTxt($_GET["tid"])." limit 0,1";
			if($query = $cDB->Query($query_string)){
				//all good - continue
				$row = mysqli_fetch_array($query);
			}else{
				throw new Exception("Could not find trade.");
			}

		break;
		default:
			$action = "view";
	}
	//CT now only the set of actions
	switch($action) {
		
		case("resend"): 
		
			$success_message = "You've re-sent the trade request, and the other member has been notified";
			// Do we have permission to act on this trade?
			//its not in the right state for rejection.. works for either way
			if ($row["member_to_decision"]!=TRADE_PENDING_DECISION_REJECTED AND $row["member_from_decision"]!=TRADE_PENDING_DECISION_REJECTED) {
				
				throw new Exception('The trade has not been rejected to be re-sent.');
			}			
			// Check this is not a 'still Open' trade
			if ($row["status"]!=TRADE_PENDING_STATUS_OPEN) {
				
				throw new Exception('Only Open trades can be rejected or resent.');
			}			
			if ($row["type"] == TRADE_TYPE_TRANSFER && $row["member_id_from"]!=$member_id ) {
				throw new Exception('You cannot re-send this transfer request');
			}
			if ($row["type"] == TRADE_TYPE_INVOICE && $row["member_id_to"]!=$member_id ) {
				throw new Exception('You cannot re-send this invoice');
			}
			
			//unhides the option - so it can be acted upon
			$field_array = array("member_to_decision"=>TRADE_PENDING_DECISION_DEFAULT);
			$field_array = array("member_from_decision"=>TRADE_PENDING_DECISION_DEFAULT);

			

			//CT - send reminder again
		
			
		break;
		//this is new...so members can cancel trades that have been fulfillied in another way. as often happens.
		case("cancel"): 
		
			$success_message = "Trade was cancelled";
			// Do we have permission to act on this trade?
			if ($row["status"] !=TRADE_PENDING_STATUS_OPEN){
				throw new Exception('Only Open pending trades can be cancelled.');
			}
			if(!$cUser->isAdminActionPermitted())  {   

				throw new Exception('Only admins can cancel pending trades right now.');
			}



			if ((($row["member_id_to"]==$member_id && $row["type"]==TRADE_TYPE_INVOICE) OR ($row["member_id_from"]==$member_id && $row["type"]==TRADE_TYPE_TRANSFER)) AND ($row["member_to_decision"] == TRADE_PENDING_DECISION_DEFAULT AND $row["member_from_decision"] == TRADE_PENDING_DECISION_DEFAULT)){
				$field_array = array(
					"status"=>TRADE_PENDING_STATUS_CANCELLED,
				 	"member_to_decision"=>TRADE_PENDING_DECISION_REMOVED, 
				 	"member_from_decision"=>TRADE_PENDING_DECISION_REMOVED
				 );
				//CT hide from view
			}else{
				throw new Exception('You cannot cancel this trade.');
			}

			
		break;

		case("accept_rejection"): 
			$success_message = "You've accepted the trade rejection and it's been hidden from view";

			if ($row["member_to_decision"]!=TRADE_PENDING_DECISION_REJECTED AND $row["member_from_decision"]!=TRADE_PENDING_DECISION_REJECTED) {
				
				throw new Exception('The trade has not been rejected to be re-sent.');
			}			
			// Check this is not a 'still Open' trade
			if ($row["status"]!=TRADE_PENDING_STATUS_OPEN) {
				
				throw new Exception('You can only do this on open trades');
			}			
			if ($row["type"] == TRADE_TYPE_TRANSFER && $row["member_id_from"]!=$member_id ) {
				throw new Exception('You cannot accept rejection on this transfer request');
			}
			if ($row["type"] == TRADE_TYPE_INVOICE && $row["member_id_to"]!=$member_id ) {
				throw new Exception('You cannot accept rejection for this invoice');
			}
			//only do this if trade passed all those other tests
			//CT this is in fact withdrawing or cancelling the trade, so mark it as such
			if ($row["type"] == TRADE_TYPE_TRANSFER) {
				$field_array["member_from_decision"] = TRADE_PENDING_DECISION_ACCEPTED_REJECTED;
			
			}else{
				$field_array["member_to_decision"] = TRADE_PENDING_DECISION_ACCEPTED_REJECTED;
			}
			$field_array["status"] = TRADE_PENDING_STATUS_CANCELLED;
			
			
			
		break;

		case("reject"): 
		
			$success_message = "You've rejected the trade. Please get in touch with them to explain your reasons, if not already known by them. ";

			// Check this is not a 'still Open' trade
			if ($row["status"]!=TRADE_PENDING_STATUS_OPEN) {
				
				throw new Exception('This trade is marked as Open and the notice cannot be removed.');
			}
			
			if ($row["type"]==TRADE_TYPE_TRANSFER && $row["member_id_to"]==$member_id) { // We want to reject the payment!
				
				$field_array = array("member_to_decision"=>TRADE_PENDING_DECISION_REJECTED);

			}
			//CT surely this is the direction it should go?
	
			else if ($row["type"]==TRADE_TYPE_INVOICE && $row["member_id_from"]==$member_id) { // We don't want to pay this invoice!
				
				$field_array = array("member_from_decision"=>TRADE_PENDING_DECISION_REJECTED);

			}
			
			// if ($cDB->Query($q))
			// 	$output .= "Member ".$row["member_id_from"]." has been informed that you have rejected this transaction.";
			// else
			// 	throw new Exception('Could not reject trade - error in database');
		
		break;

		case("remove"): 
			$success_message = "You've removed the confirmation notice";
	
			// Check this is not a 'still Open' trade
			if ($row["status"]==TRADE_PENDING_STATUS_OPEN) {
				throw new Exception('This trade is marked as Open and the notice cannot be removed.');
			}
			elseif ($row["status"]==TRADE_PENDING_STATUS_FINAL) {
				if ($row["member_id_from"]==$member_id) { // Our sent payment can be removed!
					$field_array = array("member_from_decision"=>TRADE_PENDING_DECISION_REMOVED);
				} elseif($row["member_id_to"]==$member_id) { // Our received payment can be removed!
					$field_array = array("member_to_decision"=>TRADE_PENDING_DECISION_REMOVED);
				}
			}
				
			
			
		break;

		case("confirm"):
			$success_message = "You've confirmed the trade";
		

			if ($row["status"]!=TRADE_PENDING_STATUS_OPEN) {
				
				throw new Exception('This trade had already been confirmed and is now closed.');
			}
				
				/* What is the nature of the trade - Payment or Invoice? */
			
			if ($row["type"]==TRADE_TYPE_TRANSFER) { // Payment - we are confirming receipt of incoming
				
				// Check we are the intended recipient
				if ($row["member_id_to"]!=$member_id){
					
					throw new Exception('The trade can\'t be found or you do not have permission to act on it.');
				}else { // Action the trade
					//CT attempt to do trade - move to trade table
					//CT append pending trade info just in case something goes wrong
					if ($trade_id = confirmTrade($row)){	
						$field_array = array(
							"status"=>TRADE_PENDING_STATUS_FINAL,
							"description"=>"{$row['description']} [confirmed to trade_id #{$trade_id}]"
						);					
					} else {
						throw new Exception('Could not accept payment.');
					}
				}
			}
					
			elseif ($row["type"]==TRADE_TYPE_INVOICE) { // Invoice - we are sending a payment
			
					// Check we are the intended payer of the invoice
				if ($row["member_id_from"]!=$member_id)
					
					throw new Exception('You do not have permission to act on this trade.');
				else { // Action the trade
					/*
					$goingFrom = $_SESSION["user_login"];
					$goingTo = $row["member_id_from"];
					
					$row["member_id_to"] = $goingTo;
					$row["member_id_from"] = $goingFrom;
					*/
					if ($trade_id = confirmTrade($row)){
						$field_array = array(
							"status"=>TRADE_PENDING_STATUS_FINAL,
							"description"=>$row['description'] . " (committed to trade #{$trade_id})"
						);					
					} else {
						$member = new cMember;
						$member->LoadMember($member_id);
						if ($member->restriction==1) {
							throw new Exception(LEECH_NOTICE);
						}else{
							throw new Exception('Could not confirm invoice.');
						}
						
					}

				}
			}
		
		
		
		break;
		default:
			//does nothing
			return "";
	}

	//CT if no fails, do the actions. removed all the inpage db calls and out here, as they were all v similar
	$condition = "`trade_pending_id`=\"" . $cDB->EscTxt($row["trade_pending_id"]) . "\"";
	$query_string = $cDB->BuildUpdateQuery(DATABASE_TRADES_PENDING, $field_array, $condition);
	//CT all good
	
	if ($cDB->Query($query_string)){

			//CT send emails to confirm. todo - put somewhere else to stop repetition
			$member_from = new cMember();
			$member_from->Load("`m`.`member_id`=\"{$row['member_id_from']}\"");
			$member_to = new cMember();
			$member_to->Load("`m`.`member_id`=\"{$row['member_id_to']}\"");
		if($action=="reject"){

			if($row['type'] == TRADE_TYPE_TRANSFER){
			 	$message_array = array();
			 		//CT send message to member trying to do the transfer saying its been rejected
                    $message_array['subject'] = "ACTION REQUIRED: Acknowledge transfer rejection";
                    $message_array['message'] = "<p>Hi {$member_from->getDisplayName()},</p><p>Your transfer request of {$row['amount']} to {$member_to->getDisplayName()} ({$member_to->getMemberId()}) has been rejected. You can accept this rejection, or re-send the transfer request using the following URL</p><p><a href=\"".SERVER_DOMAIN.SERVER_PATH_URL."/trades_pending.php?filter=outgoing\">".SERVER_DOMAIN.SERVER_PATH_URL."/trades_pending.php?filter=outgoing</a></p>";
                    $mailer = new cMail($message_array);
                    $mailer->buildRecipientsFromMemberObject($member_to);
                    $mailer->sendMail(LOG_SEND_TRADE_PENDING_REJECTED);
			} elseif($row['type'] == TRADE_TYPE_INVOICE){
				//CT send message to member whi sent the invoice saying its been rejected

				$message_array = array();
                    $message_array['subject'] = "ACTION REQUIRED: Acknowledge invoice rejection";
                    $message_array['message'] = "<p>Hi {$member_to->getDisplayName()},</p><p> Your invoice of {$row['amount']} requesting payment from {$member_from->getDisplayName()} ({$member_from->getMemberId()}) has been rejected. You can accept this rejection, or re-send the invoice using the following URL</p><p><a href=\"".SERVER_DOMAIN.SERVER_PATH_URL."/trades_pending.php?filter=invoices_sent\">".SERVER_DOMAIN.SERVER_PATH_URL."/trades_pending.php?filter=invoices_sent</a></p>";
                    $mailer = new cMail($message_array);
                    $mailer->buildRecipientsFromMemberObject($member_to);
                    $mailer->sendMail(LOG_SEND_TRADE_PENDING_ACCEPT_REJECTION);
			}

		}elseif($action=="resend"){
			if($row["type"] == TRADE_TYPE_INVOICE){
				$message_array = array();
                    //CT todo - put in a template.
                    $message_array['subject'] = "ACTION REQUIRED: Confirm invoice";
                    $message_array['message'] = "<p>Hi {$member_from->getDisplayName()},</p><p>You have been re-sent an invoice from {$member_to->getDisplayName()} ({$member_to->getMemberId()}) for {$row["amount"]} " . UNITS . ". Please confirm or reject this payment using the following link: </p><p><a href=\"".SERVER_DOMAIN.SERVER_PATH_URL."/trades_pending.php?filter=outgoing\">".SERVER_DOMAIN.SERVER_PATH_URL."/trades_pending.php?filter=outgoing</a></p>";
                    //CT 
                    $mailer = new cMail($message_array);
                    //CT a bit awkward, but set recipients after object already instantiated
                    $mailer->buildRecipientsFromMemberObject($member_to);
                    //CT should be try catch
                    $mailer->sendMail(LOG_SEND_TRADE_PENDING_RESENT);
			}else{
					$message_array = array();
                    $message_array['subject'] = "ACTION REQUIRED: Confirm transfer";
                    $message_array['message'] = "<p>Hi {$member_to->getDisplayName()},</p><p>You have been re-sent a request from {$member_from->getDisplayName()} ({$member_from->getMemberId()}) to transfer a payment of {$row["amount"]} " . UNITS . " to your account.  Please confirm or reject this payment using the following URL</p><p><a href=\"".SERVER_DOMAIN.SERVER_PATH_URL."/trades_pending.php?filter=incoming\">".SERVER_DOMAIN.SERVER_PATH_URL."/trades_pending.php?filter=incoming</a></p>";
                    $mailer = new cMail($message_array);
                    $mailer->buildRecipientsFromMemberObject($member_to);
                    $mailer->sendMail(LOG_SEND_TRADE_PENDING_ACCEPT);
			}
		}





		$output .= "Action completed - {$success_message}.";
		// $output .= "Member has been informed of your decision.";
	}
	else {
		throw new Exception('Could not do action - error in database');
	}
	return $output;
}



$p->DisplayPage($output);

?>