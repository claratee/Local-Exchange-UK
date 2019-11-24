<?php
class cTradeGroup extends cBasic{
	private $trades;   	// an array of cTrade objects
	private $member_id;
	private $from_date;
	private $to_date;
	private $filter_type;


	
	function Load($condition, $member_id=null) {

		global $cUser, $cDB, $cStatusMessage, $site_settings, $cQueries;

		//$to_date = strtotime("+1 days", strtotime($this->getToDate()));
		$this->setMemberId($member_id);

		/*
		//CT removed this for now as not used - date can get passed in from condition
        // not sure if this is doing it correct but this somehow makes epoch time
        $from_date = date("Ymd", $from_date);
        // this should be far-far away time
        if(empty($to_date)){
            $to_date = date("Ymd", time());
        }
        */

        /*$condition ="(member_id_from LIKE \"{$member_id}\" 
        OR member_id_to LIKE \"{$member_id}\")
        AND trade_date > {$from_date} AND trade_date < {$to_date}"; */
        //$condition .=" AND type != '{$trade_type_refund}'";
        //$condition .=" AND t.status = '" .TRADE_STATUS_APPROVED . "'";
        // $condition ="(member_id_from LIKE \"{$member_id}\" 
        // OR member_id_to LIKE \"{$member_id}\")
        // AND trade_date > {$from_date} AND trade_date < {$to_date} AND type != '{$trade_type_refund}'";
        // if(SHOW_GLOBAL_FEES !=true){
        //     $trade_type_refund = TRADE_TYPE_REVERSAL;
        //     $trade_type = TRADE_TYPE_MONTHLY_FEE;
        //     $trade_type_monthly_refund = TRADE_TYPE_MONTHLY_FEE_REVERSAL;
        //     $condition .= " AND type !='S' AND type != '{$trade_type}' AND type != '{$trade_type_monthly_refund}'";
        // }
        //print();
        if(!($site_settings->getKey('SHOW_GLOBAL_FEES')) AND $cUser->getMemberId() != $member_id AND $cUser->getMode() != "admin"){
            $condition .= " 
                AND NOT t.type = '" . TRADE_TYPE_REVERSAL. "' 
                AND NOT t.type = '" . TRADE_TYPE_MONTHLY_FEE_REVERSAL. "' 
                AND NOT t.status = '" . TRADE_STATUS_REVERSED . "' 
                AND NOT t.status = '" . TRADE_TYPE_MONTHLY_FEE_REVERSAL . "'"; 
        }
       
		$query = $cDB->Query($cQueries->getMySqlTrade($condition));

    	$trades = array();
		// instantiate new cTrade objects and load them
		while($row = $cDB->FetchArray($query)) // Each of our SQL results
		{
			//echo $row['balance'];
			$trade = new cTrade;	
			$trade->Build($row); 
			$trades[] = $trade;	
		}
		$this->setTrades($trades);
		//$cStatusMessage->Error(print_r($this->getTrades(), true));
		if(sizeof($trades) > 0) return true;
		// if no trades
		return false;
	}
    //get a trade by its ID
    function getTradeById($trade_id){
        foreach($this->getTrades() as $trade){
            if($trade->getTradeId() == $trade_id) return $trade;
        }
        //could not find it
        return false;
    }
	function Display($runningbalance=null) {
		global $cDB, $cUser, $p;
		
		//CT restructured so its got a running total and structure like a bank statement
		$output = "
			<tr>
				<th>Date</th>
                <th class='units'>Amount</th>
				<th>From</th>
				<th>To</th>
				<th>Category</th>
				<th>Description</th>
				";

		if(!empty($runningbalance)){
			$output .= "<th class='units balance'>Running balance</th>";
		}
				
			$output .= "</tr>";
		
		if(empty($this->getTrades())) return $p->Wrap('No trades?' . $output, "table", "tabulated");   // No trades yet, presumably
		
		$i=0;
		foreach($this->getTrades() as $trade) {

			$hname = "t{$trade->getTradeId()}";			
            $currentbalance = number_format((float)$runningbalance, 2, '.', '');
            $note = "";
            switch($trade->getStatus()){
                case TRADE_STATUS_REVERSED:
                case TRADE_TYPE_MONTHLY_FEE_REVERSAL: 
                    $note .= "<div>--This trade was reversed.</div>";
                break;
                // case "A":
                //  //$note .= "This trade was brokered by an admin.";
                // break;
                default:
                    //
            }
            switch($trade->getType()){
                case TRADE_TYPE_MONTHLY_FEE_REVERSAL:
                case TRADE_TYPE_REVERSAL:
                    //$note .= "Reversal action.<br />";
                break;
                // case "A":
                //  //$note .= "This trade was brokered by an admin.";
                // break;
                default:
                    //
            }
            if(!empty($trade->getFeedback())){
                $note .= "From #{$trade->getFeedback()->getMemberIdAuthor()}: {$trade->getFeedback()->showRatingAsStars()}";
                if(!empty($trade->getFeedback()->getComment())) {
                    $note .= " &quot;{$trade->getFeedback()->getComment()}&quot; ";
                }
            }
            
            if(!empty($note)) $note = "<div class=\"trade-note\">{$note}</div>";
            $amount = "{$trade->getAmount()}";
            $traded_from = "<a href='member_detail.php?member_id={$trade->getMemberIdFrom()}#{$hname}'>#{$trade->getMemberIdFrom()}</a>";
            $traded_to = "<a href='member_detail.php?member_id={$trade->getMemberIdTo()}#{$hname}'>#{$trade->getMemberIdTo()}</a>";
//print("memberid" . $this->getMemberId());
			if ($trade->getMemberIdTo() == $this->getMemberId())
			{
				if(!empty($runningbalance)){
					$runningbalance = $runningbalance - $trade->getAmount();
				}

				$statusclass = "credit";
				//$traded_from = "<a href='member_detail.php?member_id={$trade->getMemberIdFrom()}#{$hname}'>{$trade->getMemberIdFrom()}</a>";
				//$traded_to = "{$trade->getMemberIdTo()}";

			}
			else
			{				
				if(!empty($runningbalance)){
					$runningbalance = $runningbalance + $trade->getAmount();
				}
                $amount = "-{$amount}";
				$statusclass = "debit";
				//$traded_from = "{$trade->getMemberIdFrom()}";
				//$traded_to = "<a href='member_detail.php?member_id={$trade->getMemberIdTo()}#{$hname}'>{$trade->getMemberIdTo()}</a>";
			}
			//print_r($trade->getStatus());
            if($trade->getStatus() == strval(TRADE_STATUS_REVERSED) OR $trade->getStatus() == strval(TRADE_TYPE_MONTHLY_FEE_REVERSAL) OR ($trade->getType() == strval(TRADE_TYPE_REVERSAL) OR $trade->getType() == strval(TRADE_TYPE_MONTHLY_FEE_REVERSAL))){
                //$statusclass = "{$statusclass} reversal";
                $amount = $trade->getAmount(); //CT dont show the format or styling if it's a reversed or reversal
                $statusclass = "reversal";

			}
				
			
			//CT: use css styles not html colors - cleaner
			$rowclass = ($i % 2) ? "even" : "odd";	

			
			//CT todo: format
			$rb = (!is_null($runningbalance)) ? "<td class='units balance'>{$currentbalance}</td>" : "";

			$output .= "<tr class='{$rowclass} {$statusclass}' id='{$hname}'>
				<td>{$trade->getTradeDate()}</td>
                <td class='units'>{$amount}</td>
				<td>{$traded_from}</td>
				<td>{$traded_to}</td>
				<td>{$trade->getCategoryName()}</td>
				<td><span class=\"metadata\">trade id: {$trade->getTradeId()} </span>{$trade->getDescription()}{$note}</td>
				{$rb}</tr>
				";
				
			
			//$lasttrade=
			$i++;
		}
        if(!is_null($runningbalance)) {
        
            $starting_balance = number_format((float)$runningbalance, 2, '.', '');
            $output .= "<tr class=\"total\">
                <td colspan=\"6\">Opening balance</td>
                <td class='units balance'>{$starting_balance}</td></tr>
                ";


        }
        $output = $p->Wrap($output, "table", "tabulated");
        $output = $p->Wrap($output, "div", "scrollable-x");
        $output .= "<br /><p>{$i} items found.</p>";
		return $output;
	}
	/*
	//CT delete? redundant
	function MakeTradeArray() {
		$trades = "";
		if($this->trade) {
			foreach($this->trade as $trade) {
				if($trade->type != "R" and $trade->status != "R") {
					$trades[$trade->trade_id] = "#". $trade->trade_id ." - ". $trade->amount ." ". UNITS . " FROM ". $trade->member_from->member_id ." TO ". $trade->member_id_to ." ON ". $trade->trade_date;
				}
			}
		}
		
		return $trades;
	}
	*/

	//CT getters and setters
   public function getTrades()
    {
        return $this->trades;
    }

    /**
     * @param mixed $trades
     *
     * @return self
     */
    public function setTrades($trades)
    {
        $this->trades = $trades;

        return $this;
    }
    public function getMemberId()
    {
        return $this->member_id;
    }

    /**
     * @param mixed $member_id
     *
     * @return self
     */
    public function setMemberId($member_id)
    {
        $this->member_id = $member_id;

        return $this;
    }
    //
        public function getFromDate()
    {
        return $this->from_date;
    }

    /**
     * @param mixed $from_date
     *
     * @return self
     */
    public function setFromDate($from_date)
    {
        $this->from_date = $from_date;

        return $this;
    }
    //
        public function getToDate()
    {
        return $this->to_date;
    }

    /**
     * @param mixed $to_date
     *
     * @return self
     */
    public function setToDate($to_date)
    {
        $this->to_date = $to_date;

        return $this;
    }
    //
        public function getFilterype()
    {
        return $this->filter_type;
    }

    /**
     * @param mixed $filter_type
     *
     * @return self
     */
    public function setFilterType($filter_type)
    {
        $this->filter_type = $filter_type;

        return $this;
    }
}


?>