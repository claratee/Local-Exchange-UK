<?php 

//for group edit listing - management
class cListingGroupUtils extends cListingGroup
	{

	
	//though this is per listing, its only for context of group


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

	function ExpireAll($expire_date) {
		global $cStatusMessage;
		
		if (empty($this->getListing()))
			return true;
		
		foreach($this->getListing() as $listing)	{
			$listing->getExpireDate($expire_date->MySQLDate());
			$success = $listing->SaveListing(false);
				
			if(!$success)
				$cStatusMessage->Error("Could not expire listing: '".$listing->getTitle()."'");
		}
		return true;
	}	
		// todo - keywords

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
				
				$output .= "<li>{$details} <a href=\"edit\"</li>";
			
						
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