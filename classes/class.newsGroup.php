<?php
class cNewsGroup extends cCollection {
	private $items;  // will be an array of cNews objects
	private $max_s;

	public function __construct($rows=null)
    {
        parent::__construct($rows);
        $this->setItemsClassname("cNews");
        return $this;
    }


	function Load ($condition) {
		global $cDB, $cStatusMessage, $cQueries;
		
		//$this->DeleteOldNews();
	
		$string_query = $cQueries->getMySqlNews($condition);
		$is_success = $this->LoadCollection($string_query);
		print_r($is_success);
		// $i = 0;				
		// while($row = mysqli_fetch_array($query)) {
		// 	$this->newslist[$i] = new cNews;			
		// 	$this->newslist[$i]->LoadNews($row[0]);
		// 	$i += 1;
		// }

		// if($i == 0)
		// 	return false;
		// else
		// 	$this->max_seq = $this->newslist[0]->sequence;
		// 	return true;
	}
	
// 	function DisplayNewsGroup () {
// 		$output = "";
// 		if(!isset($this->newslist))
// 			return $output;
		
// 		foreach($this->newslist as $news) {
// 			if($news->expire_date->Timestamp() > strtotime("yesterday"))
// 				$output .= $news->DisplayNews() . "<BR>";
// 		}
// 		return $output;
// 	}
	
// 	function MakeNewsArray() {
// 		if (!isset($this->newslist))
// 			return false;
			
// 		foreach($this->newslist as $news) {
// 			$list[$news->news_id] = $news->title;
// 		}
// 		return $list;
// 	}

// 	function MakeNewsSeqArray($current_seq=null) { // TODO: OK, this is just ugly...
// 		$prior_seq = 0;									// Should use 1,2,3,4... and reorder
// 		$prior_title = "At top of list";				// all each time.
// 		$lead_txt = "";
// 		$follow_txt = "";
		
// 		if (!isset($this->newslist))
// 			return array("100"=>$prior_title);
		
// 		foreach($this->newslist as $news) {
// 			if ($current_seq == $news->sequence) {
// 				$list[$this->CutZero($current_seq)] = $lead_txt. $prior_title . $follow_txt;
// 			} elseif ($prior_seq != $current_seq or $current_seq == null) {
// 				if ($prior_seq == 0)
// 					$seq = $this->GetNewSeqNum();
// 				else
// 					$seq = $this->GetSeqNumAfter($prior_seq);
					
// 				$list[$seq] = $lead_txt. $prior_title .$follow_txt;
// 			}
			
// 			$prior_seq = $news->sequence;
// 			$saved_title = $prior_title;
// 			$prior_title = $news->title;
// 			$lead_txt = "After '";
// 			$follow_txt = "'";
// 		}
		
// 		if ($current_seq != $news->sequence) {
// 			if ($prior_seq == 0)
// 				$seq = $this->GetNewSeqNum();
// 			else
// 				$seq = $this->GetSeqNumAfter($prior_seq);
		
// 			$list[$seq] = $lead_txt . $prior_title . $follow_txt;
// 		}
		
// 		return $list;	
// 	}	
	
// 	function CutZero($value) {
//    	return preg_replace("/(\.\d+?)0+$/", "$1", $value)*1;
// 	}

// 	function DeleteOldNews () {
// 		global $cDB;
		
// 		$future_date = new cDateTime("-14 days");
		
// 		$delete = $cDB->Query("DELETE FROM ".DATABASE_NEWS." WHERE expire_date < '". $future_date->MySQLDate() ."';");
// 		return $delete;
// 	}
	
// 	function GetSeqNumAfter ($high) {
// 		$low = 0;
// 		foreach($this->newslist as $news) {
// 			if ($news->sequence < $high) {
// 				$low = $news->sequence;
// 				break;
// 			} 
// 		}
		
// 		return $low + (($high - $low) / 2);
// 	}
	
// 	function GetNewSeqNum () {
// 		return round($this->max_seq + 100, -2);
	//}

    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param mixed $items
     *
     * @return self
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaxS()
    {
        return $this->max_s;
    }

    /**
     * @param mixed $max_s
     *
     * @return self
     */
    public function setMaxS($max_s)
    {
        $this->max_s = $max_s;

        return $this;
    }
}

?>