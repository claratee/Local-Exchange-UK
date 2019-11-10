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
	private $strings; // Current site settings are stored here
	//public $currentVar; // Current site settings are stored here
	
	
	// Constructor - we want to get current site settings
	function __construct($settings=null) {
		//$this->getCurrent();
		if (is_null($settings)){
			$this->Load();
		}else{
			$this->Build();
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
		
		if (!$query) throw new Exception("Can't read settings");
        $field_array = array();
        while($row = $cDB->FetchArray($query)) {
        	$name = $row['name'];
        	$value = $row['current_value'];

        	//handle boolians
			if(strtolower($value) == "true") $value=1;
			else if(strtolower($value) == "false") $value=0;

        	$field_array[$name] = $value;
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
		$field_array['HTTP_BASE'] = HTTP_BASE;
		$field_array['IMAGES_PATH'] = IMAGES_PATH;
		$field_array['STYLES_PATH'] = STYLES_PATH;
		$field_array['LOCALX_VERSION'] = LOCALX_VERSION;

		//CT ugh, sorry about this. dont like setting loads of top level variables, but are shoved in a rray to access. not the most elegant.
		$this->setStrings($field_array);
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