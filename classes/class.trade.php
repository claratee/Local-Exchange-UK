<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

// include_once("class.category.php");
//include_once("class.feedback.php");


//CT should extend cBasic so it can use builder
class cTrade extends cBasic2{
    //
	private $trade_id;
	private $trade_date;
	private $status;
	private $member_id_from;
	private $member_id_to;
	private $member_id_author; //CT new - member doing action. good to have the record, eh
	//private $member_from;
	//private $member_to; //mrmbrt
	private $amount;
    private $description;
    private $type; //code of action to be performed   
    private $category;       // CT - category object NOT USED
    private $category_id;       // CT - just the id.
	private $category_name;		// CT - just the description is fine here - 1 string.
	private $feedback;	// CT object
 

//CT

/*
income ties
add feedback straight after trade


do the same for feedback and feedback rebuttal
*/



	// function __construct($variables=null) {
	// 	if(!empty($variables)) {
	// 		$this->Build($variables);
	// 	}
	// }

	// function Build($variables) {	
	// 	global $cStatusMessage;
	// 	//$cStatusMessage->Error("row:"  . print_r($variables, true));
	// 	if(!empty($variables['status'])) $this->setStatus($variables['status']);  // V for valid
	// 	if(!empty($variables['id'])) $this->setTradeId($variables['id']);  // V for valid
	// 	if(!empty($variables['date'])) $this->setTradeDate($variables['date']);  // V for valid
	// 	if(!empty($variables['amount'])) $this->setAmount($variables['amount']);
	// 	if(!empty($variables['description'])) {
 //            $description = $variables['description'];
 //            if($variables['status'] == 'R'){
 //                $description .= " <span class=\"note\">[REVERSED]</span>";
 //            }
 //            $this->setDescription($description);
 //        }
	// 	if(!empty($variables['feedback_id'])) {
	// 		$feedback = new cFeedback($variables);
	// 		$this->setFeedback($feedback);
	// 	}
	// 	if(!empty($variables['member_id_from'])) {
	// 		$this->setMemberIdFrom($variables['member_id_from']);
	// 		//load nice names etc
	// 		//$this->setMemberFrom = new;
	// 	}
	// 	if(!empty($variables['member_id_to'])) {
	// 		$this->setMemberIdTo($variables['member_id_to']);
	// 		//load nice names etc
	// 		//$this->setMemberTo = new;

	// 	}
	// 	if(!empty($variables['type'])) $this->setType($variables['type']);
 //        //`CT category object
 //        $category = new cCategory($variables);
	// 	$this->setCategory($category);
	// }
	
	/*function ShowTrade() {
		global $cDB;
		
		$content = $this->trade_id .", ". $this->trade_date .", ". $this->status .", ". $this->member_from->getMemberId() .", ". $this->member_id_to .", ". $this->amount .", ". $this->category->id .", ". $this->description .", ". $this->type;
		
		return $content;
	}*/
    // function __construct($field_array=null){
    //     parent::__construct($field_array);
    //     //CT do feedback stuff
    //     $feedback = new cFeedback;
    //     $this->setFeedback($feedback);
    // }

	// CT not used? not tested...generally dont get one trade
	function Load($condition) {
		global $cDB, $cStatusMessage,  $cQueries;
		//CT - efficiency - combine db calls. categories, feedback 
        //$condition = "trade_id={$cDB->EscTxt($trade_id)}";
        $order = "";
		$query = $cDB->Query($cQueries->getMySqlTrade($condition));

		
		if($row = $cDB->FetchArray($query)) {		
			return $this->Build($row);


			
   //          $feedback_id = $feedback->FindTradeFeedback($trade_id, $this->member_from->getMemberId());
			// if($feedback_id) {
			// 	$this->feedback_buyer = new cFeedback;
			// 	$this->feedback_buyer->LoadFeedback($feedback_id);
			// }
			// $feedback_id = $feedback->FindTradeFeedback($trade_id, $this->member_to->getMemberId());
			// if($feedback_id) {
			// 	$this->feedback_seller = new cFeedback;
			// 	$this->feedback_seller->LoadFeedback($feedback_id);
			// }
			
		} else {
			throw new Exception("Trade not found.");
			//include("redirect.php");
		}				
	}

    function Build($field_array) {
        global $cDB, $cStatusMessage,  $cQueries;
             
 
        //CT find a nicer way??
        if(!empty($field_array['feedback_member_id_author'])){
            $feedback_array=array();
            $feedback_array['member_id_author'] = $field_array['feedback_member_id_author'];
            $feedback_array['member_id_about'] = $field_array['feedback_member_id_about'];
            $feedback_array['comment'] = $field_array['feedback_comment'];
            $feedback_array['rating'] = $field_array['feedback_rating'];
            
            $feedback = new cFeedback;
            $feedback->Build($feedback_array); 
            $this->setFeedback($feedback);
        }
        return parent::Build($field_array);

                        
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
    public function getMemberIdFrom()
    {
        return $this->member_id_from;
    }

    /**
     * @param mixed $member_id_from
     *
     * @return self
     */
    public function setMemberIdFrom($member_id_from)
    {
        $this->member_id_from = $member_id_from;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMemberIdTo()
    {
        return $this->member_id_to;
    }

    /**
     * @param mixed $member_id_to
     *
     * @return self
     */
    public function setMemberIdTo($member_id_to)
    {
        $this->member_id_to = $member_id_to;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMemberFrom()
    {
        return $this->member_from;
    }

    /**
     * @param mixed $member_from
     *
     * @return self
     */
    public function setMemberFrom($member_from)
    {
        $this->member_from = $member_from;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMemberTo()
    {
        return $this->member_to;
    }

    /**
     * @param mixed $member_to
     *
     * @return self
     */
    public function setMemberTo($member_to)
    {
        $this->member_to = $member_to;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     *
     * @return self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

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
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * @param mixed $category_id
     *
     * @return self
     */
    public function setCategoryId($category_id)
    {
        $this->category_id = $category_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategoryName()
    {
        return $this->category_name;
    }

    /**
     * @param mixed $category_name
     *
     * @return self
     */
    public function setCategoryName($category_name)
    {
        $this->category_name = $category_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * @param mixed $feedback
     *
     * @return self
     */
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;

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
}


?>
