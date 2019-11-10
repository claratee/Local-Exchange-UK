<?php
/*CT TODO needs cleanup. doesnt work right now. */

// CT todo move the functions on teh page and put adequate security features
//goodness. why isnt this extending a groups class
class cTradesPending {
	
	//CT why are the trades not stored in the object, and then these are just sizeof each array?
	//TODO fix this
	var $member_id; // CT adding this here for convenience..not my style to make is a public variable but just trying to move quick
	var $numPending = 0; // Total num trades pending
	
	var $numIn = 0; // Number of trades directed TO us that we must act on
	var $numOut = 0; // Number of trades sent FROM us that we are awaiting action on
	
	var $numToPay = 0; // Num Invoices we need to pay
	var $numToConfirm = 0; // Num payments we need to confirm
	var $numToBePayed = 0; // Num invoices awaiting payment on
	var $numToHaveConfirmed = 0; // Num payments awaiting confirmation on
	var $member_displayname; // Num payments awaiting confirmation on
	
	function  __construct($member_id) {
		
		global $cDB;
		
		$this->member_id = $member_id;
		// Get all trades involving this memberID that are currently marked as Open
		$query = $cDB->query("
			SELECT 
				`member_id_from`,
				`member_id_to`,
				`member_from_decision`,
				`member_to_decision`,
				`type`
			 from " . DATABASE_TRADES_PENDING . " where (member_id_to=\"".$cDB->EscTxt($member_id)."\" or
			member_id_from=\"".$cDB->EscTxt($member_id)."\") and status='O';");
			
		if (!$query || mysqli_num_rows($query)<1) // None found = none pending!
			return;
			
		//$num_results = mysqli_num_rows($query);
		
		while($field_array = $cDB->FetchArray($query)){
			//print_r($field_array);
			//CT sorry rewriting so it can be read

			// Is this - An Invoice TO memberID that hasn't yet been acted on?
			if ($field_array["type"]==TRADE_TYPE_INVOICE && $field_array["member_id_from"]==$member_id && $field_array["member_from_decision"]==1) {
		
				$this->numToPay++;
			}
			
			// Is this - A Payment TO memberID that hasn't yet been acted on?
			if ($field_array["type"]==TRADE_TYPE_TRANSFER && $field_array["member_id_to"]==$member_id && $field_array["member_to_decision"]==1) {
	
				$this->numToConfirm++;
			}
			
			// Is this - An Invoice FROM memberID that hasn't yet been acted on?
			if ($field_array["type"]==TRADE_TYPE_INVOICE && $field_array["member_id_to"]==$member_id && $field_array["member_to_decision"]==1) {
			
				$this->numToBePayed++;
			
			}
			
			// Is this - An Payment FROM memberID that hasn't yet been acted on?
			if ($field_array["type"]==TRADE_TYPE_TRANSFER && $field_array["member_id_from"]==$member_id && $field_array["member_from_decision"]==1) {
				
				$this->numToHaveConfirmed++;
			}
			
		}
		
		$this->numIn = $this->numToPay + $this->numToConfirm;
		$this->numOut = $this->numToBePayed + $this->numToHaveConfirmed;
		$this->numPending = $this->numIn + $this->numOut;
	}

}
?>