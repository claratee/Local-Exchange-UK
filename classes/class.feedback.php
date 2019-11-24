<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}


class cFeedback extends cBasic2 {	
	 private $feedback_id;
	 private $feedback_date;
	 private $status;
	 private $member_id_author;  // id
	 private $member_id_about;	// id
     private $trade_member_id_to; 
     private $trade_member_id_from; 
     private $trade_description;
     private $trade_date;
     private $trade_category;
	 private $rating;
	 private $comment;
//	 private $context;			// indicates whether the author of this feedback was the BUYER or SELLER
	//private  $rebuttals;		// will be an object of class cRebuttalGroup, if rebuttals exist
	//private $category;			// category of the associated trade

    
	// display of feedback. 
	public function showRatingAsStars(){
        $i=1;
        $stars = "";
        while($i<=$this->getRating()){
            $stars .= '<i class="fas fa-star"></i>';
            $i++;
        }
        while($i<=sizeof(ARRAY_FEEDBACK)){
            $stars .= '<i class="fas fa-star off"></i>';
            $i++;
        }
		return "<span class=\"stars\">{$stars}</span>";
	}


	// function __construct ($variables=null) { 
	// 	if(!empty($variables)) $this->Build($variables);
	// }
	


	// function Build ($variables) { 

	// 	// rather than passing them
	// 	$this->setFeedbackId($variables['feedback_id']);
	// 	$this->setFeedbackDate($variables['feedback_date']);
	// 	$this->setStatus($variables['feedback_status']);
	// 	$this->setMemberIdAuthor($variables['feedback_member_id_author']);
	// 	$this->setMemberIdAbout($variables['feedback_member_id_about']);
	// 	$this->setTradeId($variables['trade_id']);
	// 	$this->setTradeDescription($variables['trade_description']);
	// 	$this->setTradeCategory($variables['trade_category']);
	// 	$this->setRating($variables['feedback_rating']);
	// 	$this->setComment($variables['feedback_comment']);
	// 	$this->setContext($variables['feedback_context']);
	// }
/*	function VerifyTradeMembers() { // Prevent accidental or malicious entry of feedback in which
		global $cStatusMessage;					  // seller and buyer do not match up with the recorded trade.
		
		if ($this->member_about->member_id == $this->trade->member_from->member_id) {
			if ($this->member_author->member_id == $this->trade->member_to->member_id)
				return true;
		} elseif ($this->member_about->member_id == $this->trade->member_to->member_id) {
			if ($this->member_author->member_id == $this->trade->member_from->member_id)
				return true;
		} 
		
		$cStatusMessage->Error("Members do not match the trade selected.");
		include("redirect.php");	
	} */

        /*
    // CT hiding for now - for deletion - possible with load feedback
    function FindTradeFeedback ($trade_id, $member_id) {
        global $cDB;
        
        $query = $cDB->Query("SELECT feedback_id FROM ". DATABASE_FEEDBACK ." WHERE trade_id=". $cDB->EscTxt($trade_id) ." AND member_id_author=". $cDB->EscTxt($member_id) .";");
        
        if($row = $cDB->FetchArray($query))
            return $row[0];
        else
            return false;
    } */
    //CT modified a bit
    function FindTradeFeedback ($trade_id) {
        global $cDB;
        
        $condition = "f.trade_id=\"{$trade_id}\" AND f.member_id_author=\"{$this->getMemberIdAuthor()}\" ";
        $feedback = new cFeedback(); 
        if($feedback->Load($condition)) return $feedback->getFeedbackId();
        return false;
    }

    //is used by groups as well as single. pass in the trade_id for single
    function returnTradesIfValid($trade_id=null){
        //CT make sure user has rights to post
        $condition = "
            t.status = \"V\" 
            AND (`t`.`member_id_to` =\"{$this->getMemberIdAuthor()}\" OR `t`.`member_id_from` =\"{$this->getMemberIdAuthor()}\") 
            AND NOT `t`.`type` = '" . TRADE_TYPE_REVERSAL . "' 
            AND `t`.`trade_date` > CURRENT_DATE() - INTERVAL 3 MONTH 

            ";
        //CT trade_id indicates single or group class

        if(!empty($trade_id)) {
            $condition .= " AND t.trade_id=\"{$trade_id}\"";
            $trade = new cTrade(); 
            //print($this->FindTradeFeedback($trade_id));
            if($trade->Load($condition) && !$this->FindTradeFeedback($trade_id)) {
                return $trade;
            } 
        }else{
            //CT if group
            $trades = new cTradeGroup(); 
            if($trades->Load($condition)) 
                $new_trades = array();
                foreach ($trades->getTrades() as $trade) {
                 //remove items you have already left feedback for
                    if(!$this->FindTradeFeedback($trade->getTradeId())) $new_trades[]=$trade;
                }
                //CT replace the trade array on the tradegroup object
                $trades->setTrades($new_trades);
                return $trades;
        }
        return false;
    }

	function Save () {
        global $cDB, $cStatusMessage;

        // if($this->checkForDuplicates($this-<getTradeId())) {
        //     throw new Exception('You have already left feedback about this trade.'); //safety check
        // } 

        //CT all is well
        $keys_array =array(
            'status',
            'member_id_author',
            'member_id_about',
            'trade_id',
            'rating',
            'comment'
        );        
        return $this->insert(DATABASE_FEEDBACK, $keys_array);  
    }

    function ProcessData ($field_array) {
        global $p, $member_about, $member, $cStatusMessage, $cUser;
        $this->Build($field_array);
        if($this->getRating()<1) {
            $cStatusMessage->Error("Enter a rating.");
            return false;
        }
        if(!$this->returnTradesIfValid($this->getTradeId())) {
            throw new Exception("You cannot leave feedback on this trade. You may have left one already. <a href=\"feedback_choose.php?member_id={$this->getMemberIdAuthor()}\">Leave feedback for another trade</a>");
        }    
        $feedback_id = $this->Save();
    
        if($feedback_id) {

                    //log events done by admins
            if($cUser->getMode() == USER_MODE_ADMIN AND LOG_LEVEL > 0){
                //      $keys_array = array('admin_id', 'category', 'action', 'ref_id', 'note');
                $field_array=array();
                $field_array['admin_id'] = $cUser->getMemberId();
                $field_array['category'] = LOG_FEEDBACK;
                $field_array['action'] = LOG_FEEDBACK_BY_ADMIN;
                $field_array['ref_id'] = $feedback_id;
                $field_array['note'] = "";
                $log_entry = new cLogging ($field_array);
                $log_entry->Save();
            }   

            //$output = "Your feedback has been recorded.";
            return $feedback_id;
        } else {
            throw new Exception("There was an error recording your feedback.");
        }

}



//     function SaveFeedback () {
//         global $cDB, $cStatusMessage;
        
// //      $this->VerifyTradeMembers();
//         if($this->FindTradeFeedback($this->trade_id, $this->member_author->member_id)) {
//             $cStatusMessage->Error("Cannot create duplicate feedback.");
//             return false;
//         }
        
//         $insert = $cDB->Query("INSERT INTO ". DATABASE_FEEDBACK ."(feedback_date, status, member_id_author, member_id_about, trade_id, rating, comment) VALUES (now(), ". $cDB->EscTxt($this->status) .", ". $cDB->EscTxt($this->member_author->member_id) .", ". $cDB->EscTxt($this->member_about->member_id) .", ". $cDB->EscTxt($this->trade_id) .", ". $cDB->EscTxt($this->rating) .", ". $cDB->EscTxt($this->comment) .");");

//         if(mysqli_affected_rows() == 1) {
//             $this->feedback_id = mysqli_insert_id();    
//             $query = $cDB->Query("SELECT feedback_date from ". DATABASE_FEEDBACK ." WHERE feedback_id=". $this->feedback_id .";");
//             $row = $cDB->FetchArray($query);
//             $this->feedback_date = $row[0]; 
//             return true;
//         } else {
//             return false;
//         }   
//     }
	
	function Load ($condition) {
		global $cDB, $site_settings, $cStatusMessage;
		
		$string_query = "SELECT f.feedback_id as feedback_id, 
            date_format(feedback_date, \"{$site_settings->getKey('SHORT_DATE')}\") as feedback_date, 
            f.member_id_author member_id_author, 
            f.member_id_about as member_id_about, 
            f.trade_id as trade_id, 
            f.rating as rating, 
            f.comment as comment,
            t.trade_date as trade_date, 
            t.member_id_to as trade_member_id_to, 
            t.member_id_from as trade_member_id_from, 
            t.description as trade_description, 
            c.description as trade_category
            FROM ". DATABASE_FEEDBACK . " f 
            LEFT JOIN ".DATABASE_TRADES." t on f.trade_id=t.trade_id
            LEFT JOIN ".DATABASE_CATEGORIES." c on c.category_id=t.category_id
            WHERE {$condition}
            ORDER BY f.feedback_date desc";
        //print($string_query);
		$query = $cDB->Query($string_query);
		while ($row = $cDB->FetchArray($query)) {		
			$this->Build($row);

			//$rebuttal_group = new cFeedbackRebuttalGroup();
			//if($rebuttal_group->LoadRebuttalGroup($feedback_id))
			//	$this->rebuttals = $rebuttal_group;
			return true;
		} 
		// didnt enter loop so didn't return
		//$cStatusMessage->Error("There was an error getting feedback.  ");
		//include("redirect.php");
		return false;
		
	}

	/*
	function DisplayFeedback () {
		return $this->RatingText() . "<BR>" . $this->feedback_date->StandardDate(). "<BR>". $this->Context() . "<BR>". $this->member_author->PrimaryName() ." (" . $this->member_author->member_id . ")" . "<BR>" . $this->category->description . "<BR>" . $this->comment;
	}
	*/
	function RatingText () {
		if ($this->rating == POSITIVE)
			return "Positive";
		elseif ($this->rating == NEGATIVE)
			return "Negative";
		else
			return "Neutral";
	}	

	function Context ($member_id) {
		if ($this->getMemberIdAuthor == SELLER)
			return "Seller";
		else
			return "Buyer";
	}


    /**
     * @return mixed
     */
    public function getFeedbackId()
    {
        return $this->feedback_id;
    }

    /**
     * @param mixed $feedback_id
     *
     * @return self
     */
    public function setFeedbackId($feedback_id)
    {
        $this->feedback_id = $feedback_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFeedbackDate()
    {
        return $this->feedback_date;
    }

    /**
     * @param mixed $feedback_date
     *
     * @return self
     */
    public function setFeedbackDate($feedback_date)
    {
        $this->feedback_date = $feedback_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     *
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMemberIdAuthor()
    {
        return $this->member_id_author;
    }

    /**
     * @param mixed $member_id_author
     *
     * @return self
     */
    public function setMemberIdAuthor($member_id_author)
    {
        $this->member_id_author = $member_id_author;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMemberIdAbout()
    {
        return $this->member_id_about;
    }

    /**
     * @param mixed $member_id_about
     *
     * @return self
     */
    public function setMemberIdAbout($member_id_about)
    {
        $this->member_id_about = $member_id_about;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTradeId()
    {
        return $this->trade_id;
    }

    /**
     * @param mixed $trade_id
     *
     * @return self
     */
    public function setTradeId($trade_id)
    {
        $this->trade_id = $trade_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTradeDescription()
    {
        return $this->trade_description;
    }

    /**
     * @param mixed $trade_description
     *
     * @return self
     */
    public function setTradeDescription($trade_description)
    {
        $this->trade_description = $trade_description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTradeCategory()
    {
        return $this->trade_category;
    }

    /**
     * @param mixed $trade_category
     *
     * @return self
     */
    public function setTradeCategory($trade_category)
    {
        $this->trade_category = $trade_category;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param mixed $rating
     *
     * @return self
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     *
     * @return self
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param mixed $context
     *
     * @return self
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRebuttals()
    {
        return $this->rebuttals;
    }

    /**
     * @param mixed $rebuttals
     *
     * @return self
     */
    public function setRebuttals($rebuttals)
    {
        $this->rebuttals = $rebuttals;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     *
     * @return self
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTradeDate()
    {
        return $this->trade_date;
    }

    /**
     * @param mixed $trade_date
     *
     * @return self
     */
    public function setTradeDate($trade_date)
    {
        $this->trade_date = $trade_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTradeMemberIdTo()
    {
        return $this->trade_member_id_to;
    }

    /**
     * @param mixed $trade_member_id_to
     *
     * @return self
     */
    public function setTradeMemberIdTo($trade_member_id_to)
    {
        $this->trade_member_id_to = $trade_member_id_to;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTradeMemberIdFrom()
    {
        return $this->trade_member_id_from;
    }

    /**
     * @param mixed $trade_member_id_from
     *
     * @return self
     */
    public function setTradeMemberIdFrom($trade_member_id_from)
    {
        $this->trade_member_id_from = $trade_member_id_from;

        return $this;
    }
}
	



	
?>
