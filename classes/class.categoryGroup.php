<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

//group
class cCategoryGroup
{
	private $categories; // array of category

	function  __construct($vars=null) {
		if($vars) {
			$this->Build($vars);
		} 
	}
	public function Load($condition, $order_by="description ASC") {
		global $cDB, $cStatusMessage, $cQueries;
		$query = $cDB->Query($cQueries->getMySqlCategory($condition, $order_by));

		$vars = array();
		while($row = $cDB->FetchArray($query)) $vars[] = $row;
		//print_r($vars);
		$this->Build($vars);
	}
	public function Build($vars){

		$categories = array();
		$i =0;
		while($i < sizeof($vars)) {
			$categories[] = new cCategory($vars[$i]);
			$i++;
		}
		$this->setCategories($categories);
	}

	public function PrepareOutput(){
		$string = "";
		foreach ($this->getCategories() as $category) $string .= $category->PrepareOutput();
		return $string;
	}
	
	// getters and setters
    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param mixed $categories
     *
     * @return self
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    function PrepareCategoryDropdown($selector_name="category_id", $selected_id){
		global $p, $cUser;

		$array = array();


		foreach($this->getCategories() as $category) {
			//print_r($category->getCategoryName());
			$array[$category->getCategoryId()] = $category->getCategoryName();

		}
		//$selector_id, $array, $label_none=null, $selected_id=null, $css_class=null
        return $p->PrepareFormSelector($selector_name, $array, "Select category", $selected_id);
		// $vars = $categories->MakeCategoryArray();
	}

    public function MakeCategoryArray() {	
    	$this->Load();
		$array = array();


		foreach($this->getCategories() as $category) {
			$array[$category->getCategoryId()] = $category->getCategoryName();
		}

		
		return $array;
	}
}

?>
