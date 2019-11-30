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
		//print("hello");
		global $cDB;
		$i=0;

		foreach ($rows as $row) {
			$classname = $this->getItemsClassname();
			$item = new $classname();
			$item->Build($row);
			$this->addItem($item);
			$i++;
		}
        //do anything that is appropriate for the groupclass - summaries, etc
        $this->Build($rows);
		//print_r(sizeof($this->getItems()));
		return sizeof($this->getItems());
	}
    
    public function LoadCollection($string_query)  {
    	global $cDB;
        if($query = $cDB->Query($string_query)){
        	$field_array = array();
        	while($row = $cDB->FetchArray($query)) $rows[] = $row; 
        	$count_items = $this->BuildCollection($rows);
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