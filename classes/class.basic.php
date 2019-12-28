<?php 
//base class for all database reading classes

abstract class cBasic {

	public function  __construct ($field_array=null) {
        if(!empty($field_array)) $this->Build($field_array);
    }
	//seems only to work within the class?
	public function pascalcasify($snakecase){ //snake to pascal
        return str_replace('_', '', ucwords($snakecase, '_'));
    }
    public function snakecasify($pascalcase){ //pascalcase to snake
    	$pascalcase[0] = strtolower($pascalcase[0]);
    	$func = create_function('$c', 'return "_" . strtolower($c[1]);');
  		return preg_replace_callback('/([A-Z])/', $func, $pascalcase);
    }
    
    public function Build($field_array)  {
    	//CT base function for building simple properties for class where value is just a string
    	$i = 0;
        foreach ($field_array as $key => $value) {
            if (method_exists($this, ($method = 'set'.$this->pascalcasify($key)))){
                 //if(is_null($value)) $value = "";
                 $this->$method($value);
                 $i++;
            }
        }
        return $i;
    } 
    public function LoadFromDatabase ($string_query)  {
    	global $cDB;
        if($query = $cDB->Query($string_query)){
        	$field_array = array();
        	while($row = $cDB->FetchArray($query)) $count_fields = $this->Build($row);
			return $count_fields;
        }else{
			throw new Exception('Load - Could not execute query.');
		}
        return false;
    } 

	//CT used for debugging.
    public function Display(){
    	if(!DEBUG) return;
        $string = "";
        $methods = get_class_methods(get_class($this));
        //print_r($methods);
        $string = "<h4>" . get_class($this) . "</h4>";
        foreach ($methods as $method_name) {
        	if(substr($method_name, 0,3) == "get"){
        		$var_name = substr($method_name, 3, strlen($method_name) - 1);
        		$var_name = $this->snakecasify($var_name);

        		$value = (is_array($this->$method_name()) || is_object($this->$method_name())) ? "[object or array]" : $this->$method_name();
        		$string .= "<p><span><strong>{$var_name}:</strong></span> " . $value . "</p>";
        	}
            
        }
        return $string;
    }

}
?>