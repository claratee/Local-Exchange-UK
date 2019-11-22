<?php

class cLoggingSystemEvent extends cLogging {

 	//CT checking last time this action was ran
 //    function MostRecentLog($condition) {
	// 	global $cDB;	
	// 	//print_r($string_query);

	// 	$query = $cDB->Query($string_query);

	// 	//print_r($string_query);
	// 	//return "2019-10-31 21:03:24";
	// 	if($field_array = $cDB->FetchArray($query))	{
	// 		//return new cDateTime($row['log_date']);			
	// 		return new $field_array['log_date'];			
	// 	}
	// 		//return $row['log_date'];
	// 	else{
	// 		return false;
	// 	}
	// }
	//I dislike $category is actually event type. ugh.
	function TimeForEvent ($category, $action, $interval) {
		global $cDB;
		//CT more compact - but probably not readable! basically look for events in the category in the deemed interval
		$condition = "
			admin_id=\"EVENT_SYSTEM\" 
			AND category=\"{$category}\" 
			AND action=\"{$action}\" 
			AND log_date > CURRENT_DATE() - INTERVAL {$interval} DAY
		";
		$query = $cDB->query("SELECT COUNT(log_date) as is_not_due FROM ". DATABASE_LOGGING ." WHERE {$condition}");
		$field_array = $cDB->FetchArray($query);
		//return true if no even run in last interval
		return !$field_array['is_not_due'];

	}
	//$keys_array = array('admin_id', 'category', 'action', 'ref_id', 'note');
	function CreateSystemEvent($category, $action, $ref_id=null, $note=null){
		//CT replicating effect from old site - apparently action and ref_id are the same as category????
		//just building in some flexibility so we can change this in the future
		//fix this wen you have time
		//if(empty($action)) $action = $category;
		//if(empty($ref_id)) $action = $category;
		$field_array=array();
		$field_array['admin_id'] = "EVENT_SYSTEM";
		$field_array['category'] = $category;
		$field_array['action'] = $action;
		$field_array['ref_id'] = $ref_id;
		$field_array['note'] = $note;
		//print_r($field_array);
		return $this->Build($field_array);
	}
}
?>
