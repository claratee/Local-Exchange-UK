<?php
/* 
	* class.settings.php <chris@cdmweb.co.uk>
	*
	* This class was added to handle site settings stored in MySQL.
	*
	* The MySQL method of storing settings was introduced in Version 1.0, prior to this the inc.config.php file stored all settings
	*
	* The file inc.config.php still handles some basic settings, but settings that the Administrator may wish to tinker with are now stored in MySQL and are accessible via admin.php. Doing this also negates the need for the webmaster to copy across so many settings from inc.config.php when upgrading to a new version 
*/
	
if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

class cSettings {
	private $strings; // array of settings
	//public $currentVar; // Current site settings are stored here
	
	
	// Constructor - we want to get current site settings
	function __construct($strings=null) {
		//$this->getCurrent();
		if (is_null($strings)){
			$this->Load();
		}else{
			$this->setStrings($strings);
			//$this->Build();
		}
		//print_r($this->strings);
	}

  


	// // Get and store current site settings
	// public function getCurrent() {
		
	// 	$this->retrieve();
	// 	//print_r("called ret");
	// 	//print_r($this->strings);
	// 	//$this->current = Array();
		
	// 	// Store current settings in easily accessible constants
		
	// 	$stngs = $this->theSettings;
		
	// 	$sql_data = array();
		
			
	// 	foreach($stngs as $s => $ss) {
				
	// 			if ($ss->typ=='bool') {
					
	// 				if (strtolower($ss->current_value)=='false') {
	// 					$ss->current_value = "";
						
	// 				}
	// 				else
	// 					$ss->current_value = 1;
			
	// 				define("".$ss->name."",((boolean) $ss->current_value));	
	// 			}
	// 			else if ($ss->typ=='int')
	// 				define("".$ss->name."",((int) $ss->current_value));
	// 			else
	// 				define("".$ss->name."","".$ss->current_value."");
	// 	}

	// }
	// //CT this is a bit rubbish. looking for an easier way...
	// public function getKey($keyname){
	// 	//print($keyname);
	// 	//print($this->getStrings());
	// 	if(is_null($this->getStrings()[$keyname])){
	// 		return $keyname;
	// 	}
	// 	return $this->getStrings()[$keyname];
	// }
	// public function updateKey($keyname, $value){
	// 	//print($keyname);
	// 	//print($this->getStrings());
	// 	$array = getStrings();

	// 	return $this->getStrings()[$keyname];
	// }

	// Retrieve current settings
	public function Load() {
	
		global $cDB;
		
		//$this->theSettings = Array();
		//$this->strings = Array();
		// CT can we keep small (name value) and ony load when needed?
		$string_query = "select name, current_value from " . DATABASE_SETTINGS . " where 1;";
		
		$query = $cDB->Query($string_query);
		
		//if (!$query) throw new Exception("Can't read settings");
		if (!$query) false;
        $strings = array();
        //CT replace
        $strings['HTTP_BASE'] = HTTP_BASE;
		$strings['IMAGES_PATH'] = IMAGES_PATH;
		$strings['STYLES_PATH'] = STYLES_PATH;
		$strings['LOCALX_VERSION'] = LOCALX_VERSION;
		//
        while($row = $cDB->FetchArray($query)) {
        	$name = $row['name'];
        	$value = $row['current_value'];

        	//handle boolians
			if(strtolower($value) == "true") $value=1;
			else if(strtolower($value) == "false") $value=0;

        	$strings[$name] = $value;
        }
		//$num_results = mysqli_num_rows($result);
		//foreach $row = mysqli_fetch_object($result)

		// if ($num_results>0) {
			
		// 	for ($i=0;$i<$num_results;$i++) {
		// 		$row = mysqli_fetch_object($result);
		// 		$GLOBALS[$row->name]=(!empty($row->current_value)) ? $row->current_value :  $row->default_value;
		// 		//CT would like to get the strings vars typed - like boolean, string, etc
		// 		$this->strings[$row->name] = (!empty($row->current_value)) ? $row->current_value :  $row->default_value;
		// 		//$this->theSettings[] = $row;
		// 	}
		
		// } 
		

		//CT ugh, sorry about this. dont like setting loads of top level variables, but are shoved in a rray to access. not the most elegant.
		$this->setStrings($strings);
	}
	//CT It doesnt feel ok to be writing a bunch of global constants on the site...so not using this (rewrite of an older function). but could in future, if I get convinced
	function makeConstant($field_array){
		if ($field_array['typ']=='bool') {
			
			if (strtolower($field_array['current_value'])=='false') {
				$field_array['current_value'] = "";
				
			}
			else{
				$field_array['current_value'] = 1;
			}

			define("{$field_array['name']}",((boolean)$field_array['current_value']));	
		}
		else if ($field_array['typ']=='int'){
			define("{$field_array['name']}",((int)$field_array['current_value']));
		}
		else{
			define("{$field_array['name']}",$field_array['current_value']);
		}
  	}
	public function split_options($wh) {
		$options = explode(",",$wh);
		return $options;
	}
	public function getKey($key){
		return $this->getStrings()[$key];
	}
	public function setKey($key, $value){
		$field_array = $this->getStrings();

		$field_array[$key] = $value;
		$this->setStrings($field_array);
		return $this;
	}
	public function updateKey($key, $value){
		$condition = "`name`={$key}";
		$field_array=array("current_value"=>$value);
		$string_query = $cDB->BuildUpdateQuery($table_name, $field_array, DATABASE_SETTINGS);
		$is_success = $cDB->Query($string_query);
		if(!$is_success) throw new Exception("Can't update field in settings");
		return $is_success;
	}
	public function updateAll(){
		global $cDB;
		$updates=0;
		foreach ($this->getStrings() as $key => $value) {
			$is_success=$this->updateKey($key, $value);
			if($is_success) $updates++;
		}
		return $updates;

	}
	// // Save new settings
	// public function update() {
		
	// 	global $cDB;
		
	// 	$this->retrieve();
		
	// 	$stngs = $this->theSettings;
		
	// 	$sql_data = array();
		
	// 	foreach($stngs as $s => $ss) {
			
	// 			$sql_data[''.$ss->name.''] = ''.$_REQUEST["".$ss->name.""].'';
	// 	}
		
	// 	foreach ($sql_data as $column => $value) {
			
	// 		$result = $cDB->Query("update ". DATABASE_SETTINGS . " set current_value=".$cDB->EscTxt($value)." where name=".$cDB->EscTxt($column)."");
			
	// 		if (!$result)
	// 			$cStatusMessage->Error("Update failed ".mysqli_error());

	// 			return "<font color=red>Update failed!</font>".mysqli_error();
	// 	}
		
	// 	$this->getCurrent(); // Refresh settings in current memory with new updated settings
		
	// 	return "<div class='message'>Settings updated successfully.</font>";
	// }

    /**
     * @return mixed
     */
    public function getStrings()
    {
        return $this->strings;
    }

    /**
     * @param mixed $settings
     *
     * @return self
     */
    public function setStrings($strings)
    {
        $this->strings = $strings;

        return $this;
    }
}

$site_settings = new cSettings();
?>