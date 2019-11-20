<?php 

//for group edit listing - management
class cListingGroupUtils extends cListingGroup
	{

	
	//though this is per listing, its only for context of group

	//CT todo - dont use this. if member goes on holiday, the listings should just be excluded from results. Dont change the listing...
	function InactivateAll($reactivate_date) {
		global $cStatusMessage;
		
		if (!isset($this->listing))
			return true;
		
		foreach($this->getListing() as $listing)	{
			$current_reactivate = new cDateTime($listing->getReactivateDate(), false);
			if(($listing->getReactivateDate() == null or $current_reactivate->Timestamp() < $reactivate_date->Timestamp()) and $listing->status != EXPIRED) {
				$listing->getReactivateDate($reactivate_date->MySQLDate());
				$listing->getStatus(INACTIVE);
				$success = $listing->SaveListing();
				
				if(!$success)
					$cStatusMessage->Error("Could not inactivate listing: '".$listing->getTitle() ."'");
			}
		}
		return true;
	}

	function ExpireAll($condition) {
		//CT works on all matchin query. so be careful...
		global $cStatusMessage, $cDB;
		$array = array("status"=>"E");
		$string_query = $cDB->BuildUpdateQuery(DATABASE_LISTINGS, $array, $condition);	
		//print_r($string_query);	
		return $cDB->Query($string_query);
	}	
	//CT bit like a factory - returns new listing object. rerouting opportunity for extend classes
	public function makeListing($field_array=null)
    {
        return new cListingUtils($field_array);
    }
//CT this shouldnd be used?
	function Display($show_ids=true)
	{
		
		global $cUser,$cDB, $p;
	
		$output = "";
		$current_cat = "";
		$i = 0;
		//print_r($this->getListing());
		if(!empty($this->getListing())) {
			foreach($this->getListing() as $listing) {
			
				
				// CT construct details
				$details = "";
				$memInfo = "";
				$details .=  $this->ListingLink($listing->getListingId(), $listing->getTitle());
				
				$output .= "<li>{$details} <a href=\"edit\">hmm?</a></li>";
			
						
				// Rate
				
			
				
				$i++;	
			}
			$output .= "</ul><br />"; // end the last unordered list
			$output .= $p->Wrap($i . " items found.", "p");
	
		} 
		if($i==0)
			$output = $p->Wrap("No items found.", "p");
								
		return $output;		
	}

}



?>