<?php
/*CT TODO needs cleanup. doesnt work right now. */
class cTradeGroupPending extends cTradeGroup {
	
	//private $member_id; // member id

	// private $count_pending; // Total num trades pending
		
	// private $count_invoice_in; // Num Invoices we need to pay
	// private $count_trade_in; // Num trade payments we need to confirm
	// private $count_invoice_out; // Num invoices awaiting payment on
	// private $count_trade_out; // Num trade payments awaiting confirmation on
	
	private $trades_invoice_in; // Trades - Invoices we need to pay
	private $trades_trade_in; // Trades - trade payments we need to confirm
	private $trades_invoice_out; // Trades - invoices awaiting payment on
	private $trades_trade_out; // Trades - trade payments awaiting confirmation on


	function MakeSummary(){
	    global $cDB;

		//split trades array into 4 different arrays... just pass into the build at the end
		$field_array = array();
		$field_array['trades_invoice_in'] = array(); // Trades - Invoices we need to pay
		$field_array['trades_trade_in'] = array(); // Trades - trade payments we need to confirm
		$field_array['trades_invoice_out'] = array(); // Trades - invoices awaiting payment on
		$field_array['trades_trade_out'] = array(); // Trades - trade payments awaiting confirmation on



	 //    $field_array = array();
		// $field_array['count_pending'] = 0; // Number of trades directed TO us that we must act on

		// $field_array['$count_invoice_in'] =0; // Num Invoices we need to pay
		// $field_array['$count_trade_in'] =0; // Num payments we need to confirm
		// $field_array['$count_invoice_out'] =0; // Num invoices awaiting payment on
		// $field_array['$count_trade_out'] =0; // Num payments awaiting confirmation on

		if(empty($this->getItems())) throw new Exception("No pending trades found");   // No trades yet, presumably

		//print_r($tradeGroup->getItems());
		$i=0;

		foreach($this->getItems() as $trade) {
			//echo($trade->getType()); 
			
			switch($trade->getTradeType()){
				case "I":
					if($this->getMemberId() == $trade->getMemberIdTo()) {
						$field_array['trades_invoice_out'][] = $trade;	
						// $field_array['count_invoice_out']++;

					}else{
						$field_array['trades_invoice_in'][] = $trade;	
						// $field_array['count_invoice_in']++;
					}
				break;
				case "T":
					if($this->getMemberId() == $trade->getMemberIdTo()) {
						$field_array['trades_trade_in'][] = $trade;	
						// $field_array['count_trade_in']++;
					}else{
						$field_array['trades_trade_out'][] = $trade;	
						// $field_array['count_trade_out']++;
					}
				break;

			}
			$i++;
		}
		$this->Build($field_array);
    }


// SELECT COUNT(trade_id) from lets_trades t where (`member_id_to`="0685" OR `member_id_from`="0685") AND status="P"
// ORDER BY `t`.`status` ASC
// 	function  Load($condition, $member_id=null) {
		
// 		parent::Load($condition, DATABASE);
// 		foreach($this->trades as $trade){


// 		}
// 		// for ($i=0;$i<$num_results;$i++) {
			
// 		// 	$row = $cDB->FetchArray($query);
		
// 		// 	// Is this - An Invoice TO memberID that hasn't yet been acted on?
// 		// 	if ($row["typ"]=="I" && $row["member_id_to"]==$memberID && $row["member_to_decision"]==1) {
		
// 		// 		$this->numToPay += 1;
// 		// 	}
			
// 		// 	// Is this - A Payment TO memberID that hasn't yet been acted on?
// 		// 	if ($row["typ"]=="T" && $row["member_id_to"]==$memberID && $row["member_to_decision"]==1) {
	
// 		// 		$this->numToConfirm += 1;
// 		// 	}
			
// 		// 	// Is this - An Invoice FROM memberID that hasn't yet been acted on?
// 		// 	if ($row["typ"]=="I" && $row["member_id_from"]==$memberID && $row["member_from_decision"]==1) {
			
// 		// 		$this->numToBePayed += 1;
			
// 		// 	}
			
// 		// 	// Is this - An Payment FROM memberID that hasn't yet been acted on?
// 		// 	if ($row["typ"]=="T" && $row["member_id_from"]==$memberID && $row["member_from_decision"]==1) {
				
// 		// 		$this->numToHaveConfirmed += 1;
// 		// 	}
			
// 		// }
		
// 		// $this->numIn = $this->numToPay + $this->numToConfirm;
// 		// $this->numOut = $this->numToBePayed + $this->numToHaveConfirmed;
// 		// $this->numPending = $this->numIn + $this->numOut;
// 	}

// }
//just like Display, but with actions appropriate to directiion and source of trade payment
function DisplayWithTriggers($filter) {
		global $cDB, $cUser, $p;
		
		switch($filter){
			case "invoice_out":
				$trades = $this->getTradesInvoiceOut();
			break;
			case "invoice_in":
				$trades = $this->getTradesInvoiceIn();
			break;
			case "trade_out":
				$trades = $this->getTradesTradeOut();
			break;
			case "trade_in":
				$trades = $this->getTradesTradeIn();
			break;
			default:
				$trades = $this->getItems();
		}




		//CT restructured so its got a running total and structure like a bank statement
		$output = "
			<tr>
				<th>Date</th>
                <th class='units'>Amount</th>
				<th>From</th>
				<th>To</th>
				<th>Category</th>
				<th>Description</th>
				<th>Actions</th>
			</tr>";
		
		if(empty($this->trades)) 
		
		$i=0;
		foreach($trades as $trade) {

			$hname = "t{$trade->getTradeId()}";			
            // $currentbalance = number_format((float)$runningbalance, 2, '.', '');
            $note = (!empty($trade->getFeedback())) ? "From #{$trade->getMemberIdFrom()}: {$trade->getFeedback()->showRatingAsStars()} &quot;{$trade->getFeedback()->getComment()}&quot; " : "";
            if(($cUser->getMemberRole() > 0 AND !($site_settings->getKey('USER_MODE'))) OR ($site_settings->getKey('USER_MODE') && $cUser->getMode() == USER_MODE_ADMIN))  {   
            	$note .= "(Requested by {$trade->getMemberIdAuthor()})";
            }
            //TODO - show who did it
            if(!empty($note)) $note = "<div class=\"trade-note\">{$note}</div>";
            $amount = "{$trade->getAmount()}";
            // $traded_from = "<a href='member_detail.php?member_id={$trade->getMemberIdFrom()}#{$hname}'>#{$trade->getMemberIdFrom()}</a>";
            // $traded_to = "<a href='member_detail.php?member_id={$trade->getMemberIdTo()}#{$hname}'>#{$trade->getMemberIdTo()}</a>";
            $traded_from = "#{$trade->getMemberIdFrom()}";
            $traded_to = "#{$trade->getMemberIdTo()}";
//print("memberid" . $this->getMemberId());
			
			$triggers="";
			switch($trade->getTradeType()){
				case "I":
					if($this->getMemberId() == $trade->getMemberIdTo()) {
						//invoice_in
						$triggers = "<a href=\"trades_pending.php?action=cancel&filter=invoice_out&member_id={$member_id}&trade_id={$trade->getTradeId()}\">Cancel invoice</a>";	
						// $field_array['count_invoice_out']++;

					}else{
						//trade_in
						$triggers = "<a href=\"trades_pending.php?action=confirm&filter=invoice_out&member_id={$member_id}&trade_id={$trade->getTradeId()}\">Pay invoice</a> | 
// 					<a href=\"trades_pending.php?action=reject&filter=invoice_out&member_id={$member_id}&trade_id={$trade->getTradeId()}\">Reject</a>";	
						// $field_array['count_invoice_in']++;
					}
				break;
				case "T":
					if($this->getMemberId() == $trade->getMemberIdTo()) {
						//trade_in
						$triggers = "<a href=\"trades_pending.php?action=confirm&filter=trade_in&member_id={$member_id}&trade_id={$trade->getTradeId()}\">Accept payment</a> | <a href=\"trades_pending.php?action=reject&filter=trade_in&member_id={$member_id}&trade_id={$trade->getTradeId()}\">Reject</a>";	
						// $field_array['count_trade_in']++;
					}else{

						$triggers = "<a href=\"trades_pending.php?action=cancel&filter=trade_out&member_id={$member_id}&trade_id={$trade->getTradeId()}\">Cancel payment</a>";	
						// $field_array['count_trade_out']++;
					}
				break;

			}
				
			
			//CT: use css styles not html colors - cleaner
			$rowclass = ($i % 2) ? "even" : "odd";	

			

			$output .= "<tr class='{$rowclass} {$statusclass}' id='{$hname}'>
				<td>{$trade->getTradeDate()}</td>
                <td class='units'>{$amount}</td>
				<td>{$traded_from}</td>
				<td>{$traded_to}</td>
				<td>{$trade->getCategoryName()}</td>
				<td><span class=\"metadata\">trade id: {$trade->getTradeId()} </span>{$trade->getDescription()}{$note}</td>
				<td>{$triggers}</td>
				</tr>
				";
			$i++;
		}
        $output = $p->Wrap($output, "table", "tabulated");
        $output = $p->Wrap($output, "div", "scrollable-x");
        $output .= "<br /><p>{$i} items found.</p>";
		return $output;
	}

public function doAction($trade_id, $action){
	global $cDB;
	$trade = $this->getTradeById($trade_id);

	if(empty($trade)) throw new Exception("Trade could not be found.");
	if($trade->getStatus() !="P") throw new Exception("Trade is not pending, you cannot act on it.");
	
	//CT I actually need the functions on tradeUtils - that's weher allthe actions are! 
	$tradeUtils = new cTradeUtils();
	$tradeUtils->setStatus("P");
	$tradeUtils->setAction($action);

	//CT get trade by id from tradeGroup
	switch($action) {
		//invoices_out
		case("pay"): 
		case("refuse"): 
			//
			// Do we have permission to act on this trade?
			if ($cUser->getMode() !="admin" && $trade->getMemberIdFrom() != $this->getMemberId()) {
				throw new Exception("You don't have the rights to do this action.");
			}
		break;
		case("resend"): 
		case("withdraw"): 
			//invoices_in
			// Do we have permission to act on this trade?
			if ($cUser->getMode() !="admin" && $trade->getMemberIdFrom() != $this->getMemberId()) {
				throw new Exception("You don't have the rights to do this action.");
			}
		break;
		
		case("reject"):
		case("accept"):

			//paymens
			// Do we have permission to act on this trade?
			if ($cUser->getMode() !="admin" && $trade->member_id_to() != $this->getMemberId()) {
				throw new Exception("You don't have the rights to do this action.");
			}
		break;
		
	}
	$tradeUtils->Save();


}


    /**
     * @return mixed
     */
    public function getTradesInvoiceIn()
    {
        return $this->trades_invoice_in;
    }

    /**
     * @param mixed $trades_invoice_in
     *
     * @return self
     */
    public function setTradesInvoiceIn($trades_invoice_in)
    {
        $this->trades_invoice_in = $trades_invoice_in;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTradesTradeIn()
    {
        return $this->trades_trade_in;
    }

    /**
     * @param mixed $trades_trade_in
     *
     * @return self
     */
    public function setTradesTradeIn($trades_trade_in)
    {
        $this->trades_trade_in = $trades_trade_in;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTradesInvoiceOut()
    {
        return $this->trades_invoice_out;
    }

    /**
     * @param mixed $trades_invoice_out
     *
     * @return self
     */
    public function setTradesInvoiceOut($trades_invoice_out)
    {
        $this->trades_invoice_out = $trades_invoice_out;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTradesTradeOut()
    {
        return $this->trades_trade_out;
    }

    /**
     * @param mixed $trades_trade_out
     *
     * @return self
     */
    public function setTradesTradeOut($trades_trade_out)
    {
        $this->trades_trade_out = $trades_trade_out;

        return $this;
    }
}
?>