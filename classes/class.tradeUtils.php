<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

// include_once("class.category.php");
//include_once("class.feedback.php");


//CT should extend cBasic so it can use builder
class cTradeUtils extends cTrade{

    //CT new - used for saving. 
    //private $mode;
    private $action; //CT what you are doing - affects saves

	public function Save() {  // This function should never be called directly
		global $cDB, $cStatusMessage, $cUser, $site_settings;
		
        //CT seems odd to be checking right here, when user is strying to do something - wouldnt it be better to test before?
        $balanceUtils = new cBalanceTotal;
        $balanceUtils->checkBalance();
        //print("is balanced " . $balance->getIsSystemBalanced());


        $field_array = array();
        //trad
        //$field_array["trade_date"]=""; //on update - so no need to set. 
        //trades are records, and persistent forever - you cannot edit

        // //$field_array["trade_date"]=$this->getTradeDate(); // CT automatic with the update in the db
        // $field_array["status"]=$this->getStatus();
        // if($this->getAction() == "invoice"){
        //     $field_array["status"]='';

        // }

        
        switch($this->getAction()){
            // //these are just status changes
            // case "confirm":
            // case "reject":
            // case "cancel":
            // case "accept":
            // case "resend":
            //     $field_array["status"]=$this->getStatus();
            //     $condition = "$trade_id={$this->getTradeId()}";
            //     $string_query = $cDB->BuildUpdateQuery(DATABASE_MEMBERS, $field_array, $condition);
            // break;
            case "reverse":
                $field_array["status"]= TRADE_STATUS_REVERSED;
                $field_array["description"]=$this->getDescription();
                $condition = "`trade_id`={$this->getTradeId()}";
                $database = DATABASE_TRADES;
                $string_query = $cDB->BuildUpdateQuery($database, $field_array, $condition);
                // if(null == $this->getMemberIdAuthor()) {
                //     $field_array["member_id_author"]=strval($cUser->getMemberId()); // make sure its a string
                // }else{
                //     $field_array["member_id_author"]=$this->getMemberIdAuthor();
                // }
                //CT TODO create new reverse trade
                //$string_query = $cDB->BuildInsertQuery(DATABASE_TRADES, $field_array);

            break;
            case "create":
            case "confirm":
                //also used for reversing trades - new record. balances must be adjusted, but the status of the trade is set as TRADE_STATUS_REVERSAL
        
                $field_array["member_id_to"]=$this->getMemberIdTo(); 
                $field_array["member_id_from"]=$this->getMemberIdFrom(); 
                if(null == $this->getMemberIdAuthor()) {
                    $field_array["member_id_author"]=strval($cUser->getMemberId()); // make sure its a string
                }else{
                    $field_array["member_id_author"]=$this->getMemberIdAuthor();
                }
                $field_array["status"]=$this->getStatus(); 
                $field_array["amount"]=$this->getAmount(); 
                $field_array["category_id"]=$this->getCategoryId(); 
                $field_array["description"]=$this->getDescription(); 
                $field_array["type"]=$this->getType(); 

                //$field_array["trade_type"]=$this->getType(); 
                // CT if pending, use pending db
                if($this->getStatus() == TRADE_PENDING_STATUS_OPEN){
                    $database = DATABASE_TRADES_PENDING;
                }else{
                    $database = DATABASE_TRADES;
                }

                $string_query = $cDB->BuildInsertQuery($database, $field_array);
            break;
            default:
                throw new Exception("Unknown action.");
        }

        // if($this->getAction() == "confirm"){

        // }elseif
        
        //print_r($string_query);

        //print_r($cDB->Query($string_query));
        $trade_id = $cDB->QueryReturnId($string_query);

        //$trade_id = "";
        // if($is_success && $field_array["status"]==TRADE_STATUS_APPROVED){
        //     //update to account

        //     //update from account

        // }
        //return false if failed, if success, carry on
        if(!is_numeric($trade_id) or $trade_id==0){
            throw new Exception('Could not save trade.');
        } 
        $this->setTradeId($trade_id);

        //log events done by admins
        if($cUser->isAdminActionPermitted() AND LOG_LEVEL > 0){
            //      $keys_array = array('admin_id', 'category', 'action', 'ref_id', 'note');
            $field_array=array();
            $field_array['admin_id'] = $cUser->getMemberId();
            $field_array['category'] = $this->getType();
            $field_array['action'] = $this->getStatus();
            $field_array['ref_id'] = $this->getTradeId();
            $field_array['note'] = "";
            $log_entry = new cLogging ($field_array);
            $log_entry->Save();
        }		
        //$this->setTradeId(mysqli_insert_id());
        //$this->setTradeId($cDB->getInsertId);
        if($this->getStatus() == TRADE_STATUS_APPROVED){
            // CT build
            $string_query ="
                UPDATE " . DATABASE_MEMBERS . " SET `balance`=(balance-{$this->getAmount()}) WHERE `member_id`=\"{$this->getMemberIdFrom()}\";";
            
            $is_success = $cDB->Query($string_query);
            if(!$is_success){
                throw new Exception('Could not update FROM account balance.');
                exit;
            }
            //echo "trade " . $is_success;

            if(!$is_success) return false;  

            //CT put somewhere nicer

            $string_query ="
                UPDATE " . DATABASE_MEMBERS . " SET `balance`=(balance+{$this->getAmount()}) WHERE `member_id`=\"{$this->getMemberIdTo()}\";";
            $is_success = $cDB->Query($string_query);
            if(!$is_success){
                throw new Exception('Could not update TO account balance.');
                exit;
            }
                                // Has the recipient got an income tie set-up? If so, we need to transfer a percentage of this elsewhere...
        /*  
                $recipTie = cIncomeTies::getTie($member_to_id);
                
                if ($recipTie && ALLOW_INCOME_SHARES==true) {
                    
                    $member_to = new cMember;
                    $member_to->LoadMember($member_to_id);
        
                    $theAmount = round(($values['units']*$recipTie->percent)/100);
                    
                    $charity_to = new cMember;
                    $charity_to->LoadMember($recipTie->tie_id);
        
                    $trade2 = new cTrade($member_to, $charity_to, htmlspecialchars($theAmount), htmlspecialchars(12), htmlspecialchars("Donation from ".$member_to_id.""), 'T');
            
                    $status = $trade2->MakeTrade();
                }
        */            
            //CT this replaces the code above  
                try {
                    if($this->getAction() !="reverse"){
                        $income_tie = new cIncomeTies();
                        $has_income_tie=$income_tie->Load($this->getMemberIdTo());
                        if($has_income_tie){
                            //make sure that the user being donated to is valid/active
                            $member_donate_to = new cMember();
                            $condition = "m.member_id=\"{$income_tie->getMemberIdTo()}\" AND m.status=\"A\"";
                            //CT if member is valid
                            
                            //loads
                            if($member_donate_to->Load($condition)){
                               
                                $donation_amount = round(($field_array['amount'] * $income_tie->getPercent())/100);
                                //CT only do donation if more than 1 unit - safety for recursion error
                                if($donation_amount>=1){
                                    $field_array_donation = array();
                                    $field_array_donation["action"]="create"; 
                                    $field_array_donation["member_id_to"]=$income_tie->getMemberIdTo(); 
                                    $field_array_donation["member_id_from"]=$income_tie->getMemberId(); 
                                    $field_array_donation["member_id_author"]="system"; //automatic 
                                    //$field_array["status"]=$this->getStatus(); 
                                    $field_array_donation["amount"]=$donation_amount; 
                                    $field_array_donation["category_id"]="43"; //system business 
                                    $field_array_donation["description"]="Donation of " . UNITS . " from income share by {$income_tie->getMemberId()}"; 
                                    $field_array_donation["type"]=TRADE_TYPE_TRANSFER; 
                                    
                                    //CT commit trade
                                    //$trade_donation = new cTradeUtils($field_array_donation);
                                    $trade_donation = new cTradeUtils($field_array_donation);
                                    $trade_donation->ProcessData($field_array_donation);
                                }
                            }
                        }
                    } else{
                           
                            
                            $field_array_reversal = array();
                            $field_array_reversal["action"]="create"; 
                            $field_array_reversal["member_id_to"]=$this->getMemberIdFrom(); 
                            $field_array_reversal["member_id_from"]=$this->getMemberIdTo(); 
                            $field_array_reversal["member_id_author"]=$cUser->getMemberId(); //automatic 
                            //$field_array["status"]=$this->getStatus(); 
                            $field_array_reversal["amount"]=$this->getAmount(); 
                            $field_array_reversal["category_id"]="43"; //system business 
                            $field_array_reversal["type"]=TRADE_TYPE_REVERSAL; //reversal
                            $field_array_reversal["status"]="V"; //confirmed 
                            $field_array_reversal["description"]="[Reversal of exchange #{$trade_id} from " . substr($this->getTradeDate(), 0, 10) . "by admin " . $cUser->getMemberId() . ". Reason: '" . $field_array["reason"] . "']"; 
                            $field_array_reversal["type"]=TRADE_TYPE_TRANSFER; 
                            
                            //CT commit trade
                            //$trade_donation = new cTradeUtils($field_array_donation);
                            $trade_donation = new cTradeUtils($field_array_reversal);
                            $trade_donation->ProcessData($field_array_reversal);
                            
                        }
                        

                }catch(Exception $e){
                    $cStatusMessage->Error("Trade has been completed, but the Income share has failed: " . $e->getMessage());
                }
                    
                
            
           

  		// 	//$query = $cDB->Query("SELECT trade_id, trade_date from ". DATABASE_TRADES ." WHERE trade_id=\"{$this->getTradeId()}\";");
        }
        
        return $trade_id;
	}
	

//CT redoing - is this needed?

	// // It is very important that this function prevent the database from going out balance.
	// function MakeTrade($reversed_trade_id=null) { 
	// 	global $cDB, $cStatusMessage;
		
	// 	if ($this->getAmount() <= 0 and $this->getType() != TRADE_TYPE_REVERSAL) // Amount should be positive unless
	// 		return false;									 // this is a reversal of a previous trade.
			
	// 	if ($this->getAmount() >= 0 and $this->getType() == TRADE_TYPE_REVERSAL)	 // And likewise.
	// 		return false;
			
		
	// 	if ($this->getTradeMemberIdFrom() == $this->getTradeMemberIdTo())
	// 		return false;		// don't allow trade to self
		
	// 	if ($this->getTradeMemberFrom()->getRestriction()==1) { // This member's account has been restricted - they are not allowed to make outgoing trades
			
	// 		return false;
	// 	}
	
	// 	$balances = new cBalancesTotal;
	
	// 	// TODO: At some point, we should handle out-of-balance problems without shutting 
	// 	// down all trades.  But for now, seems like a wonderfully simple solution.	
	// 	//
	// 	// [chris] Have added a few more methods for dealing with out-of-balance scenarios (admin can set his/her preferred method in inc.config.php)	
	// 	// CT - TODO put this in the db and elsewhere
	// 	if(!$balances->Balanced()) {
			
	// 		if (OOB_EMAIL_ADMIN==true) // Admin wishes to receive an email notifying him/her when db is found to be out-of-balance
 //                //CT TODO - mail
	// 			$cStatusMessage->Error("The trade database is out of balance. TODO: mail admins");
 //                //$mailed = mail();
			
	// 		switch(OOB_ACTION) { // How should we handle the out-of-balance event?
				
	// 			case("FATAL"): // FATAL: The original method for dealing which is to abort the transaction
					
	// 				$cStatusMessage->Error("The trade database is out of balance!  Please contact your administrator at ". EMAIL_ADMIN .".", ERROR_SEVERITY_HIGH);  

	// 				//include("redirect.php");
	// 				exit;  // Probably unnecessary...
					
	// 			break;
				
	// 			default: // SILENT: Just ignore the situation and don't burden the user with warnings/error messages
					
	// 					// doing nothing...
						
	// 			break;
	// 		}
	// 	}	

	// 	// NOTE: Need table type InnoDB to do the following transaction-style statements.		
	// 	$cDB->Query("SET AUTOCOMMIT=0");
		
	// 	$cDB->Query("BEGIN");
		
	// 	if($this->Save()) {
			
	// 		$success1 = $this->gettradeMemberFrom()->UpdateBalance(-($this->amount));
	// 		$success2 = $this->getTradeMemberTo()->UpdateBalance($this->amount);
			
	// 		if(LOG_LEVEL > 0 and $this->getType() != TRADE_BY_MEMBER) {//Log if enabled & not an ordinary trade
	// 			$log_entry = new cLogging (TRADE, $this->getType(), $this->getTradeId());
	// 			$success3 = $log_entry->SaveLogEntry();
	// 		} else {
	// 			$success3 = true;
	// 		}
			
	// 		if($reversed_trade_id) {  // If this is a trade reversal, need to mark old trade reversed
	// 			$success4 = $cDB->Query("UPDATE ".DATABASE_TRADES." SET status='R', trade_date=trade_date WHERE trade_id=". $cDB->EscTxt($reversed_trade_id) .";");
	// 		} else {
	// 			$success4 = true;
	// 		}

	// 		if($success1 and $success2 and $success3 and $success4) {
	// 			$cDB->Query('COMMIT');
	// 			$cDB->Query("SET AUTOCOMMIT=1"); // Probably isn't necessary...
	// 			return true;
	// 		} else {
	// 			$cDB->Query('ROLLBACK');
	// 			$cDB->Query("SET AUTOCOMMIT=1"); // Probably isn't necessary...
	// 			return false;
	// 		}
	// 	} else {
	// 		$cDB->Query("SET AUTOCOMMIT=1"); // Probably isn't necessary...
	// 		return false;
	// 	}			
	// }
	
	// function ReverseTrade() { 	// This method allows administrators to reverse
	// 	global $cUser;								// trades that were made in error.
	// 	$cUser->MustBeLevel(1);
	// 	if($this->getStatus() != TRADE_STATUS_APPROVED) return false;		// CT only reverse active trades
	// 	$this->setStatus(TRADE_STATUS_REVERSED);
 //        $string = $this->setDescription() .  "[Trade from {$this->getTradeDate()} Reversed by admin #{$cUser->getMemberId()}]";
 //        $this->setDescription($string);
 //        $this->Save();

	// 	$new_trade = new cTrade;				
 //        //CT - set and use 
 //        //
 //        $new_trade->setType(TRADE_TYPE_REVERSAL);
 //        $new_trade->setStatus(TRADE_STATUS_REVERSAL);
	// 	$new_trade->setMemberIdFrom($this->getMemberIdTo());
 //        $new_trade->setMemberIdTo($this->getMemberIdFrom());
 //        $new_trade->setMemberIdAuthor($CUSER->getMemberId());
	// 	$new_trade->setAmount($this->getAmount());
	// 	$new_trade->setCategory($this->getCategory());
	// 	$string = "[Reversal of trade #{$this->getTradeId()} from {$this->getTradeDate()} by admin {$cUser->getMemberId()}] {$description}";
	// 	$new_trade->setDescription($string);

 //        //CT change status of the current
	// 	return $new_trade->Save();
	// }

    ///CT moved from inpage trade.php. 
    function ProcessData ($field_array) {
        global $p, $member, $cStatusMessage, $cUser, $cDB;
        $list = "";
        //CT this appears to be time bank capabilities - which is great - but needs more work
        // if(UNITS == "Hours") {
        //     if($field_array['minutes'] > 0)
        //         $field_array['units'] = $field_array['units'] + ($field_array['minutes'] / 60);
        // }
        
        // if(!($field_array['units'] > 0)) {
        //     $cStatusMessage->Error("No units were entered to exchange!");
        //     //include("redirect.php");
        // }   
        //CT validate
        $errors=array();     
        if(empty($field_array['member_id_to'])) {
            $errors[] = "TO Member is missing.";
        }
        if(empty($field_array['member_id_from'])) {
            $errors[] = "FROM Member is missing.";
        }
        if($field_array['member_id_to'] == $field_array['member_id_from']) {
            $errors[] = "TO and FROM members to be different.";
        }
        if(empty($field_array['category_id'])) {
            $errors[] = "Category is missing.";
        }        
        if(empty($field_array['amount']) OR !(is_numeric($field_array['amount']))) {
            $errors[] = "Amount is missing or not a number.";
        }
        // CT safety for members - admin can complete large trades
        if(!empty($field_array['amount']) AND is_numeric($field_array['amount']) AND $field_array['amount'] > 1000 AND (!$cUser->isAdminActionPermitted()))  {   

            $errors[] = "Amount is too large. You've hit the maximum number allowed for members. Contact an admin for trades bigger than 1000.";
        }        
        if(!empty($field_array['description']) AND strlen($field_array['description']) > 300) {
            $errors[] = "Description must be under 300 characters long.";
        }
        //CT todo - refuse amounts bigger than 10000?
        // if($field_array['amount']) {
        //     $errors[] = "This seems like a big amount....";
        // }
        if(sizeof($errors) > 0) {
            //CT todo: highlight the form elements from keys
            foreach($errors as $key => $error) {
                $errorstring .= $error . " ";
            }
            throw new Exception($errorstring);
        }
        //CT continue - all is well
        //$member_id_to = substr($field_array['member_id_to'],0, strpos($values['member_to'],"?")); // TODO:
        $member_from = new cMember;
        $condition = "m.member_id = \"{$this->getMemberIdFrom()}\"";
        $member_from->Load($condition);

        $member_to = new cMember;
        $condition = "m.member_id = \"{$this->getMemberIdTo()}\"";
        $member_to->Load($condition);
        

        //CT user cant spend.
        //CT restriction is not working - not sure why.
        if($member_from->getRestriction()) {
            throw new Exception("Member #{$this->getMemberIdFrom()} cannot spend as their account is restricted. Please contact the member.");
        }
        // set correct status
        //echo $this->getType();
        if(($this->getType()== TRADE_TYPE_TRANSFER && $member_to->getConfirmPayments()) OR ($this->getType() == TRADE_TYPE_INVOICE)) {
            $this->setStatus(TRADE_PENDING_STATUS_OPEN); //pending status
        }
        else {
            $this->setStatus(TRADE_STATUS_APPROVED);
        }



        //CT passes on trade_id from processData

        $trade_id = $this->Save();
        //
        if(is_numeric($trade_id)) {
            //CT continue if valid trade id.
        //CT pending trades you the same table as normal trades. just a different status. Saves faff.

            if($this->getStatus() == TRADE_PENDING_STATUS_OPEN && $this->getType() == TRADE_TYPE_INVOICE) {
                     //CT send to joint members too. 
                    $message_array = array();
                    //CT todo - put in a template.
                    $message_array['subject'] = "ACTION REQUIRED: Confirm invoice";
                    $message_array['message'] = "<p>Hi {$member_from->getDisplayName()},</p><p>You have received an invoice from {$member_to->getDisplayName()} ({$member_to->getMemberId()}) for {$this->getAmount()} " . UNITS . ". Please confirm or reject this payment using the following link: </p><p><a href=\"".SERVER_DOMAIN.SERVER_PATH_URL."/trades_pending.php?filter=outgoing\">".SERVER_DOMAIN.SERVER_PATH_URL."/trades_pending.php?filter=outgoing</a></p>";
                    //CT 
                    $mailer = new cMail($message_array);
                    //CT a bit awkward, but set recipients after object already instantiated
                    $mailer->buildRecipientsFromMemberObject($member_to);
                    //CT should be try catch
                    $mailer->sendMail($this->getType());

                    //CT 
                    $confirmation_message = "Invoice for {$this->getAmount()} " . UNITS . " has been 
                        sent to {$this->getMemberIdFrom()} 
                        from {$this->getMemberIdTo()}. The invoice can be either approved or rejected. Either way, we will contact you when this happens.
                        Leave <a href=\"feedback.php?author={$this->getMemberIdFrom()}&about={$this->getMemberIdTo()}&trade_id={$this->getTradeId()}\">feedback</a> for this trade?";
            
            }else{
                if($this->getStatus() == TRADE_PENDING_STATUS_OPEN && $member_to->getConfirmPayments()) {
                    //CT make better - this is rubbish. put in a function
                   
                    $message_array = array();
                    $message_array['subject'] = "ACTION REQUIRED: Confirm transfer";
                    $message_array['message'] = "<p>Hi {$member_to->getDisplayName()},</p><p>You have received a request from {$member_from->getDisplayName()} ({$member_from->getMemberId()}) to transfer a payment of {$this->getAmount()} " . UNITS . " to your account.  Please confirm or reject this payment using the following URL</p><p><a href=\"".SERVER_DOMAIN.SERVER_PATH_URL."/trades_pending.php?filter=incoming\">".SERVER_DOMAIN.SERVER_PATH_URL."/trades_pending.php?filter=incoming</a></p>";
                    $mailer = new cMail($message_array);
                    $mailer->buildRecipientsFromMemberObject($member_to);
                    $mailer->sendMail($this->getType());

                    //print_r($mailer->sendMail($this->getType()));

                    //CT todo - make notification thing instead of error thing
                    $confirmation_message = "Requested to transfer {$this->getAmount()} " . UNITS . " 
                        from {$this->getMemberIdFrom()} 
                        to {$this->getMemberIdTo()}. As the receiver has opted to be approve for incoming payments, this trade requires action from them before completion. You cannot leave feedback for trade until the trade has completed.
                        ";
                } else{
                    $confirmation_message = "Transferred {$this->getAmount()} " . UNITS . " 
                        from {$this->getMemberIdFrom()} 
                        to {$this->getMemberIdTo()}.";
                    /* 
                    //CT confirmation of sending - not needed for now as no action required, and user has just seen the confirmation message in the UI.
                    $message_array = array();
                    $message_array['subject'] = "Payment sent";
                    $message_array['message'] = "<p>Hi {$member_from->getDisplayName()},</p><p>You've sent a payment of {$this->getAmount()} " . UNITS . " to {$member_to->getDisplayName()} ({$member_to->getMemberId()}). No further action required.</p>";
                   
                    $mailer = new cMail($message_array);
                    //CT a bit awkward, but set recipients after object already instantiated
                    $mailer->buildRecipientsFromMemberObject($member_from);
                    //CT should be try catch

                    //print_r($mailer->sendMail($this->getType()));
                    $mailer->sendMail($this->getType());
                    */

                    //get joint members too. 
                    $message_array = array();
                    $message_array['subject'] = "Payment received";
                    $message_array['message'] = "<p>Hi {$member_to->getDisplayName()},</p><p>You've received a payment of {$this->getAmount()} " . UNITS . " from {$member_to->getDisplayName()} ({$member_to->getMemberId()}). No further action required.</p><p></p>";
                    $mailer = new cMail($message_array);
                        //CT a bit awkward, but set recipients after object already instantiated
                    $mailer->buildRecipientsFromMemberObject($member_to);

                }      //CT make better - this is rubbish. put in a function


               
            }
            $cStatusMessage->Info($confirmation_message);
            return $trade_id;
        }
            else {
                return false;
        }
        
            

    }

    


    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param mixed $mode
     *
     * @return self
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }




    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     *
     * @return self
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }
}

?>
