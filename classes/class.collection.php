<?php 
//base class for all collection classes
abstract class cCollection extends cBasic {

    private $items = []; //CT array of objects
	private $items_classname;

    public function  __construct ($rows=null) {
        $this->setItemsClassname("cSingle");
        if(!empty($rows)) $this->BuildCollection($rows);
    }

	function BuildCollection($rows) {
		global $cDB;
		//$i=0;
        //print_r($rows);
		foreach ($rows as $row) {
			$classname = $this->getItemsClassname();
			$item = new $classname();
			$item->Build($row);
            //print_r($item->getDescription());
			$this->addItem($item);
			//$i++;
		}
        //do anything that is appropriate for the groupclass - summaries, etc
        $is_success = $this->Build($rows);
		return $i;
	}
    function Build($rows){
        //placeholder class - write in the child class any summary or extra stuff
        return true;
    }
    
    public function LoadCollection($string_query)  {
    	global $cDB;
        //print_r(expression)
        if($query = $cDB->Query($string_query)){

        	$field_array = array();
            // foreach ($cDB->FetchArray($query) as $row) {
            //     $rows[] = $row;
            // }
            //print_r($rows);
        	while($row = $cDB->FetchArray($query)) $rows[] = $row; 
        	$count_items = $this->BuildCollection($rows);
            //print_r("$count_items");
			return $count_items;
        }else{
			throw new Exception('Load - Could not execute query.');
		}
        return false;
    } 

	public function Display(){
    	//CT basic - used for debugging. should be overwritten by class if you want to use it
		if(!DEBUG) return;
        $string = parent::Display();
		if (method_exists($this, 'getItems')){
	        foreach ($this->getItems() as $item) {
	            $string .= $item->Display() . "\r\n";
	        }
	    }
        return $string;
    }

    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param mixed $uploads
     *
     * @return self
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }
    public function addItem($item)
    {
        $this->items[] = $item;
        return $this;
    }
    public function removeByKey($key)
    {
        $array = $this->getItems();
        unset($array, $key);
        $this->setItems($array);
        return $this;
    }
    public function retrieveByKey($key)
    {
        return $this->items[$key];
    }



    /**
     * @return mixed
     */
    public function getItemsClassname()
    {
        return $this->items_classname;
    }

    /**
     * @param mixed $items_classname
     *
     * @return self
     */
    public function setItemsClassname($items_classname)
    {
        $this->items_classname = $items_classname;

        return $this;
    }
    public function countItems()
    {
        return sizeof($this->getItems());
    }
}
?>