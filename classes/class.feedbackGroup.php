<?php
class cFeedbackGroup extends cCollection {
	//private $member_id;
	private $member_id;		// for convenience
	private $context;		// Buyer or Seller or Both
//	private $timeframe_date;
	private $num_positive;
	private $num_negative;
	private $num_neutral;
    private $total;
    private $percent_positive;
	//private $feedback;		// now items. will be an array of cFeedback objects
	

    
  

    public function __construct($rows=null) {
        //global $cDB;
        parent::__construct($rows);
        $this->setItemsClassname("cFeedback"); // name of class for items array
    }
    // load db feedback from condtion
	public function Load ($condition) {
		global $cDB, $p, $cStatusMessage, $site_settings;
		// CT choose whether feedback is for someone or left by someone
		//$context_field = ($this->getContext() == "about") ? "member_id_about" : "member_id_author";
		//$cStatusMessage->Error($this->getContext());
		$string_query = "SELECT 
            f.feedback_id as feedback_id, 
			f.feedback_date as feedback_date, 
			f.member_id_author as member_id_author, 
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
		
        return $this->LoadCollection($string_query);
        
		
	}
    public function Build($field_array){
        $is_built = sizeof($this->getItems());
        if(!$is_built) return false;
         // CT init
        $feedback_list = array();
        $num_positive = 0;
        $num_negative = 0;
        $num_neutral = 0;

        $i=0;
        foreach ($this->getItems() as $feedback) {
            if($feedback->getRating() == 3) $num_positive++;
            if($feedback->getRating() == 1) $num_negative++;
            if($feedback->getRating() == 2) $num_neutral++;
            $i++;
        }
        
        $total = $num_positive + $num_negative + $num_neutral;

        $this->setTotal($total);
        $this->setNumPositive($num_positive);
        $this->setNumNegative($num_negative);
        $this->setNumNeutral($num_neutral);
        //$this->setFeedback($feedback_list);
        if($num_positive > 0 && $total > 0){
            $this->setPercentPositive(number_format($num_positive /  $total * 100, 0));
        } else {
            $this->setPercentPositive(0);
        }
        
        //$cStatusMessage->Error($i);
        if(sizeof($this->getItems()) > 0) return true;
        return false;
       
    }
	
	function PercentPositive() {
		return number_format(($this->getNumPositive() / ($this->getNumPositive() + $this->getNumNegative() + $this->getNumNeutral())) * 100, 0); 
	}
	
	function TotalFeedback() {
		return $this->getNumPositive() + $this->getNumNegative() + $this->getNumNeutral();
	}
	
	function Display($context="about") {
        global $p;		
		// $output = "
		// 	<tr>
		// 		<th>Buyer</th>
		// 		<th>Seller</th>
		// 		<th>Feedback</th>
  //               <th>Trade</th>
		// 	</tr>";
        if($context=="about"){
            $who_title="From";
        }else{
            $who_title="About";
        }
	       $output = "
            <tr>
                <th>Feedback</th>
                <th>Context</th>
                <th>${who_title}</th>
            </tr>";
		
		
		$i=0;
		foreach($this->getItems() as $feedback) {
			$rowclass = ($i % 2) ? "even" : "odd";	

//			$member_author = $feedback->getMemberIdAuthor();
			if($context == "about") {
                $context_label = ($feedback->getTradeMemberIdFrom() == $feedback->getMemberIdAuthor()) ? "Seller" : "Buyer";
				$who = "<a href='member_detail.php?member_id={$feedback->getMemberIdAuthor()}'>{$feedback->getMemberIdAuthor()}</a>";
			}else {
				$who = "<a href='member_detail.php?member_id={$feedback->getMemberIdAbout()}'>{$feedback->getMemberIdAbout()}</a>";
                $context_label = ($feedback->getTradeMemberIdTo() == $feedback->getMemberIdAuthor()) ? "Seller" : "Buyer";
			}
			//feeback visual
			$stars=$feedback->showRatingAsStars();

			$trade_description = $feedback->getTradeDescription();
			if($feedback->getRating() == NEGATIVE){
				$rowclass .= " negative";
                $rating_summary = "Negative";
			}elseif ($feedback->getRating() == POSITIVE){
				$rowclass .= " positive";
                $rating_summary = "Positive";
			}else {
				$rowclass .= " neutral";
                $rating_summary = "Neutral";
			}	
				
            // $output .= "<tr class='$rowclass'>
            //     <td>{$member_author}</td>
            //     <td>{$member_about}</td>
            //     <td>{$stars} {$feedback->getComment()}</td>
            //     <td><div class=\"\"> Trade from " . $p->FormatShortDate($feedback->getTradeDate()) .": {$feedback->getTradeDescription()} - {$feedback->getTradeCategory()} <div class=\"metadata\">#{$feedback->getTradeId()}</div></div></td>";
            
            $output .= "<tr class='$rowclass'>
                <td>{$stars} <em>{$rating_summary}</em>. {$feedback->getComment()}</td>                
                <td><div class=\"\">Trade on " . $p->FormatShortDate($feedback->getTradeDate()) .": {$feedback->getTradeCategory()}. {$context_label}. <div class=\"metadata\">#{$feedback->getTradeId()}</div></div></td>
                <td>{$who}</td>";
            
			// if(isset($feedback->rebuttals))
			// 	$output .= $feedback->rebuttals->DisplayRebuttalGroup($feedback->member_about->getMemberId()); // TODO: Shouldn't have to pass this value, should incorporate into cFeedbackRebuttal
			
			// if($feedback->rating != POSITIVE) {

			// 	if ($member_viewing == $feedback->member_about->getMemberId())
			// 		$text="Reply";
			// 	elseif ($member_viewing == $feedback->member_author->getMemberId())
			// 		$text="Follow up";

			// 	$output .= "<br /><a href='feedback_reply.php?feedback_id={$feedback->feedback_id}&author={$member_viewing}&about={$feedback->member_author->getMemberId()}''>{$text}</a> "; 
			// }
			
			$output .= "</tr>";
			$i++;
		}	
		return "<div class='scrollable-x'><table class='tabulated'>{$output}</table></div>";
	}
    function DisplaySummary () {
        //summary element for use on summary page
        return (empty($this->getTotal())) ? "No feedback yet" : "{$this->getPercentPositive()}% positive ({$this->getTotal()} total. {$this->getNumNegative()} negative, {$this->getNumNeutral()} neutral)";
    }
	
    /**
     * @return mixed
     */
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
    public function getSinceDate()
    {
        return $this->since_date;
    }

    /**
     * @param mixed $timeframe_date
     *
     * @return self
     */
    public function setSinceDate($timeframe_date)
    {
        $this->since_date = $timeframe_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumPositive()
    {
        return $this->num_positive;
    }

    /**
     * @param mixed $num_positive
     *
     * @return self
     */
    public function setNumPositive($num_positive)
    {
        $this->num_positive = $num_positive;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumNegative()
    {
        return $this->num_negative;
    }

    /**
     * @param mixed $num_negative
     *
     * @return self
     */
    public function setNumNegative($num_negative)
    {
        $this->num_negative = $num_negative;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumNeutral()
    {
        return $this->num_neutral;
    }

    /**
     * @param mixed $num_neutral
     *
     * @return self
     */
    public function setNumNeutral($num_neutral)
    {
        $this->num_neutral = $num_neutral;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     *
     * @return self
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPercentPositive()
    {
        return $this->percent_positive;
    }

    /**
     * @param mixed $percent_positive
     *
     * @return self
     */
    public function setPercentPositive($percent_positive)
    {
        $this->percent_positive = $percent_positive;

        return $this;
    }

}
?>