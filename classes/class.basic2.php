<?php 
//base class for all database reading classes

abstract class cBasic2 {
	//private $db_table;

    public function  __construct ($field_array=null) {
        if(!empty($field_array)) $this->Build($field_array);
    }
	//seems only to work within the class?
	public function pascalcasify($snakecase){
        return str_replace('_', '', ucwords($snakecase, '_'));
    }
    public function Build($field_array)  {
    	//print_r($field_array);
    	//exit;
        $i=0;
        foreach ($field_array as $key => $value) {
            if (method_exists($this, ($method = 'set'.$this->pascalcasify($key)))){
                //if(is_null($value)) $value = "";
                $this->$method($value);
            }
            $i++;
        }
        return $i;
    } 
    public function LoadDatabaseTable ($string_query)  {
    	global $cDB;
        if($query = $cDB->Query($string_query)){
			if($field_array = $cDB->FetchArray($query)){	
				$is_success = $this->Build($field_array);
				return $is_success;
			} else{
				return false;
				//CT results empty - don't throw an error, just return false - probably not a mistake, just no results found with the condition set.
			}

        }else{
			throw new Exception('Load - Could not execute query.');
		}
        return false;
    } 


    //pass in the fields you want to create an array from
	public function makeFieldArrayFromKeys($keys_array){
		$field_array = array();
		foreach ($keys_array as $key) {
			$method = 'get'.$this->pascalcasify($key);
			if(is_array($this->$method())) {
				$field_array[$key] = print_r($this->$method(), true); //CT captures the value if its an array - used for quickly capturing recipient array for sendmail. generally, not great to use - just for development.
			}else{
				$field_array[$key] = $this->$method();
			}
			//print($this->$method());
			
		}
    	return $field_array;
	}
	//CT abstract class function for updating a record - solar naming
	function update($db_table, $keys_array, $condition) {
        //CT saves all the properties that you pass in on the key array. table columns must have the same names
		global $cDB, $cStatusMessage;
		$context = (DEBUG) ? "DB:{$db_table} " : "";
		if($field_array=$this->makeFieldArrayFromKeys($keys_array)){
			if($string_query = $cDB->BuildUpdateQuery($db_table, $field_array, $condition)){
				if($is_success = $cDB->Query($string_query)){
					//print_r($string_query);
					return $is_success;
				}else{
					throw new Exception("{$context} Update - Could not execute query.");
				}
			}else{
				throw new Exception("{$context} Update - Could not build query from array.");
			}
        	 
		}else{
			throw new Exception("{$context} Update - No recognized properties found in array.");
		}

	}	
	//CT abstract class function for creating a record - solar naming
	function insert($db_table, $keys_array) {
        
		global $cDB, $cStatusMessage;
		$context = (DEBUG) ? "DB:{$db_table} " : "";
		if($field_array=$this->makeFieldArrayFromKeys($keys_array)){
			if($string_query = $cDB->BuildInsertQuery($db_table, $field_array)){
				//CT not all of the dbs will return an id
				
				if($last_used_id = $cDB->QueryReturnId($string_query)){
					return $last_used_id;
				}else{
					//CT chances are it t=did execute, just coulndt return an ID
					throw new Exception('{$context} Create - Could not execute query.');
				} 
			}else{
				throw new Exception('{$context} Create - Could not build query from array.');
			}
		}else{
			throw new Exception('{$context} Create - No recognized properties found in array.');
		}	
	}
	// function Tabl($title, $value){
	// 	return TableFromArray($field_array)
	// }
	function makeLabelArray($title, $value){
		return array("title" => $title, "value" => $value);
	}
	//help for debugging...
	// make a header line for a table
	function TableHeaderFromArray($field_array){
		$output="";
		foreach ($field_array as $key => $value) $output .= "<th>{$key}</th>";
		return "<tr>{$output}</tr>";
	}
	function TableRowFromArray($field_array, $cssClass="odd"){
		$output="";
		foreach ($field_array as $key => $value) $output .= "<td>{$value}</td>";
		return "<tr class=\"$cssClass\">{$output}</tr>";
	}
	//if just a row or multiple rows
    function TableFromArray($field_array) {
    	if(!empty($field_array)) return false;
    	$output ="";
    	if(is_array($field_array[0])){
    		$output .= $this->TableHeaderFromArray($field_array[0]);

    		$cssClass="odd";
    		foreach ($field_array as $row) {
    			$output .= $this->TableRowFromArray($row, $cssClass);
    			//alternate rows
    			$cssClass = ($cssClass=="odd") ? "even" : "odd";
    		}
    	}else{
    		$output .= $this->TableHeaderFromArray($field_array);
    		$output .= $this->TableRowFromArray($field_array);
    	}
		return "<div class=\"scrollable-x\"><table class=\"tabulated layout1\">{$output}</table></div>";
    }


}
?>