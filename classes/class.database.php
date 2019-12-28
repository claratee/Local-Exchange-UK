<?php
if (!isset($global))
{
	die(__FILE__." was included directly.  This file should only be included via inc.global.php.  Include() that one instead.");
}

class cDatabase
{
	private $isConnected;
	private $db_link;
	//CT counters for stats - to show improvements in db efficiency
	private $count_connection;
	private $count_query;

	function Database() {
		$this->isConnected = false;
		//CT init counters
		$this->count_connection = 0;
		$this->count_query = 0;
	}

	// function Connect()
	// {

	// 	if ($this->isConnected){
	// 		return;
	// 	}
	// 	$link = ($GLOBALS["___mysqli_ston"] = mysqli_connect(DATABASE_SERVER,DATABASE_USERNAME,DATABASE_PASSWORD)) or die("Problem occur in connection");  

	// 	//$db = ((bool)mysqli_query($link, "USE " . info));  
	// 	$this->db_link = $link;
	// 	$this->isConnected=true;
	// 	// CT iterate
	// 	$this->count_connection++;
	// 	//print("Connection" . $this->count_connection);
	// }

	function Connect()
	{
		if(!empty(DATABASE_PORT)){
			$db_link = mysqli_connect(DATABASE_SERVER,DATABASE_USERNAME,DATABASE_PASSWORD, DATABASE_NAME, DATABASE_PORT);
		} else{
			$db_link = mysqli_connect(DATABASE_SERVER,DATABASE_USERNAME,DATABASE_PASSWORD, DATABASE_NAME);
		}
		// Check connection
		if (mysqli_connect_errno())
		{
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		} else{
	//$db = ((bool)mysqli_query($link, "USE " . info));  
			$this->db_link = $db_link;
			$this->isConnected=true;
			// CT iterate
			$this->count_connection++;
			//print("Connection" . $this->count_connection);

		}
		
	}
	function Disconnect()
	{
		if ($this->isConnected){
			mysqli_close($db_link);
			$this->isConnected=false;
			//echo "disconnected";
		}
	}



	function Query($string_query)
	{
		//CT: returns something useful when executed correctly
		global $cStatusMessage;
		// CT iterate
		if (!$this->isConnected) $this->Connect();

		$result_message="";
		//CT: why is this not a resource?
		if($result = mysqli_query($this->db_link, $string_query)) {
			if(gettype($result) == "object"){
				//ct debug
				//$result_message .= "| R: " . $this->AffectedRows();
			}
			
		} else{
			return false;
			//return false;
			//throw new Exception("Unexpected response from the database.");
		}
		 

		$this->count_query++;
		//debug db call - uncomment out to get a print
		//$debug = true;
		$debug = DEBUG;
		//CT THIS IS TEMPORARY
		if(!empty($_REQUEST['debug']) && $cUser->IsLoggedOn()){
			if($_REQUEST['debug']) $debug = true;
		}
		if($debug) $cStatusMessage->Info("Q.{$this->count_query}: {$string_query} {$result_message}");
		//selects
		if(gettype($result) == "object" || preg_match("DROP", $string_query) || preg_match("ALTER", $string_query)){
			return $result;
		}
		//CT return affected rows if more than 0
		//inserts and updates?
		if(!empty($this->AffectedRows())) return $this->AffectedRows();
		//updates where nothing changed
		return $this->MatchedRows();
		//CT in case of updates where no data is changed, still want to report its done correctly
		
	}

	function QueryReturnId($string_query)
	{
		//CT: returns the last used ID on an insert
		global $cStatusMessage;
		$ret = $this->Query($string_query);
		if(mysqli_insert_id($this->db_link)) {
			return mysqli_insert_id($this->db_link);
		}else{
			return $ret;
		}

		//$last_used_id = mysqli_insert_id($this->db_link);
		//$cStatusMessage->Info("Last used id: " . $last_used_id);
		return $last_used_id;
	}

	function FetchArray($thequery)
	{
		if(empty($thequery)) return false;
		//potential backward compatibility 
		return mysqli_fetch_array($thequery);
	}

	function FetchObject($thequery)
	{
		if(empty($thequery)) return false;
		return mysqli_fetch_object($this->$db_link, $thequery);
	}

	function AffectedRows()
	{
		//return mysqli_affected_rows($this->$db_link);
		return mysqli_affected_rows($this->db_link);
	}
	//CT in the case of update where data is unchanged, but you still want it to return true
	function MatchedRows() {
		// "Rows matched: {n} <- ya want this bit
	   $exploded_array=explode(' ', mysqli_info($this->db_link));
	   return $exploded_array[2];
	}

	function NumRows($thequery)
	{
		if (!$this->isConnected)
			$this->Connect();

		$result = $this->Query($thequery);

		return mysqli_num_rows($this->db_link);
	}

	function MakeSimpleTable($theQuery)
	{
		$query = $this->Query($theQuery);

		/* Printing results in HTML */
		$table = "<TABLE>\n";
		while ($line = mysqli_fetch_array($query, mysqli_ASSOC)) {
			$table .= "\t<TR>\n";
			foreach ($line as $col_value)
			{
				$table .= "\t\t<TD>$col_value</TD>\n";
			}
			$table .= "\t</TR>\n";
		}
		$table .= "</TABLE>\n";

		return $table;
	}

/*
	function EscTxt($text) {
		if($text) {
			if(MAGIC_QUOTES_ON) 
				return "'". $text ."'";
			else 
				return "'". addslashes($text) ."'";
		} else {
			return "null";
		}
	}

	function EscTxt2($text) {  // TODO: Rename to EscQueryTxt() and update through site
		if($text) {
			if(MAGIC_QUOTES_ON) 
				return "='". $text ."'";
			else 
				return "='". addslashes($text) ."'";
		} else {
			return " IS NULL";
		}
	}
*/
   // CT adjusted so that nulls dont overwrite. 
    function BuildUpdateQueryStringFromArray($array){
        $i=0;
        $string = "";
        //name value pair in array creates a Update query set statement
        foreach($array as $key=>$value){
        	//not sure if this is possible
        	if(!is_null($key)){
	            if($i > 0) $string .= ", ";

				if(is_int($value)){
	            	$string .= " `{$key}`=$value ";
				}else{
					$string .= " `{$key}`=\"{$this->EscTxt($value)}\"";
				}
	        	$i++;
	    	}
            
        }
        return $string;
    }
    //ct returns array of the two parts needed for the insert statement - keys and values
    function BuildInsertQueryStringsFromArray($array) {
        $i=0;
        $keys_as_string="";
        $values_as_string="";
        foreach($array as $key=>$value){
            if($i > 0){
                $keys_as_string .=", ";
                $values_as_string .=", ";
            }
            $keys_as_string .= "`{$key}`";
            //set appropriate type for communication with mysql
            if(is_int($value)){
            	$values_as_string .= "{$value}";
            }else{
            	$values_as_string .= "\"{$this->EscTxt($value)}\"";
            }
            
            $i++;
        }

        return array($keys_as_string, $values_as_string);
    }

       // CT arrays of fields wanted 
    function BuildSelectQueryStringFromArray($array){
        $i=0;
        $string = "";
        //name value pair in array creates a Update query set statement
        foreach($array as $key=>$value){
        	//make sure nulls arent set by accident in the db, now that the objects dont load all fields
            if($i > 0) $string .= ", ";
            //m.member_id as member_id to avoid collisions in namespace
            $string .= " {$key} as $value";
            $i++;
        }
        return $string;
    }

    //CT helper function to build update query
    function BuildUpdateQuery($table_name, $array, $condition) {

        $vars_as_string = $this->BuildUpdateQueryStringFromArray($array);
        $string = "UPDATE `{$table_name}` SET {$vars_as_string} WHERE {$condition};";
        return $string;
    }
    //CT helper function to build insert query
    function BuildInsertQuery($table_name, $array) {
        //this is for php7. php5 has these flipped
        list($keys_as_string, $vars_as_string) = $this->BuildInsertQueryStringsFromArray($array);
        $string = "INSERT INTO `{$table_name}` ({$keys_as_string}) VALUES ({$vars_as_string});";
        return $string;
    }    
    //helper function to build select query
    //will take in alias if needed -  $table_name_and_alias = member m
    //CT condition can be cheted to have order by and limit associated with it
    // eg $condition = "m.member_id='{$member_id}' order by m.memberId ASC limit 1";
   function BuildSelectQuery($table_name, $array, $joins, $condition) {
        $vars_as_string = $this->BuildSelectQueryStringFromArray($array);

         $string = "SELECT {$vars_as_string} FROM {$table_name} {$joins} WHERE {$condition};";
        return $string;
    }
        //helper function to build delete query
    function BuildDeleteQuery($table_name, $condition) {
        //$vars_as_string = $this->BuildUpdateQueryStringFromArray($array);
        $string = "DELETE FROM `{$table_name}` WHERE {$condition};";
        return $string;
    }

	/* A HTML screening function, an optional additional security step for data being submitted for storage in MySQL */
	function ScreenHTML($dirty_html) {
		
		global $cUser,$allowedHTML;
		
		// ct using the htmlpurifier library


		$config = HTMLPurifier_Config::createDefault();
		//$config->set('HTML.Allowed', 'p,b,a[href],i');
 	   	//$config->set('URI.Base', 'http://www.example.com');
  	  	//$config->set('URI.MakeAbsolute', true);
   		//$config->set('AutoFormat.AutoParagraph', true);


		$purifier = new HTMLPurifier($config);
		$clean_html = $purifier->purify($dirty_html);
	
		return $clean_html;
	}

	
	
	/* Takes an individual HTML tag and checks it
			$allowed - an Array containing exceptions (e.g. em, i) */
	function ProcessHTMLTag($data,$allowed) {
		
		// ending tags
		if (preg_match("/^\/([a-z0-9]+)/i", $data, $matches)){
			$name = StrToLower($matches[1]);
			if (in_array($name, array_keys($allowed))){
				return '</'.$name.'>';
			}else{
				
				return '';
			}
		}

		// starting tags
		if (preg_match("/^([a-z0-9]+)(.*?)(\/?)$/i", $data, $matches)){
			$name = StrToLower($matches[1]);
			$body = $matches[2];
			$ending = $matches[3];
			if (in_array($name, array_keys($allowed))){
				$params = "";
				preg_match_all("/ ([a-z0-9]+)=\"(.*?)\"/i", $body, 
					$matches_2, PREG_SET_ORDER);
				preg_match_all("/ ([a-z0-9]+)=([^\"\s]+)/i", $body,
					$matches_1, PREG_SET_ORDER);
				$matches = array_merge($matches_1, $matches_2);
			
				foreach($matches as $match){
					$pname = StrToLower($match[1]);
					//if (in_array($pname, $allowed[$name]) || in_array("*",$allowed[$name])){
						$params .= " $pname=\"$match[2]\"";
					//}
		
				}
				
				return '<'.$name.$params.$ending.'>';
		}
		else
			return '';
		}
	}
	
// CT cleanup - just use mysqli_real_escape_string  
	
    function EscTxt($text) {
    	return mysqli_real_escape_string($this->db_link, $text);
    }


    function EscTxt2($text) {
        if( !empty($text)) {
            // if(get_magic_quotes_gpc()) {
            //     $text = stripslashes($text);
            // }

            return "='" . mysqli_real_escape_string($this->db_link, $text) . "'";
        } else if(is_numeric($text)) {
            return "='$text'";
        } else {
            return " IS NULL";
        }
    }


	function UnEscTxt($text) {
		return stripslashes($text);
	}	



}


$cDB = new cDatabase;
?>
