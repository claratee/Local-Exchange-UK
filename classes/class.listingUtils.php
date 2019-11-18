<?php 

class cListingUtils extends cListing {

	private $form_action; 
	//private $form_mode; 

		    /**
     * @param mixed $form_action
     *
     * @return self
     */

/**
     * @return mixed
     */
    public function getFormAction()
    {
        return $this->form_action;
    }

    /**
     * @param mixed $form_action
     *
     * @return self
     */
    public function setFormAction($form_action)
    {
        $this->form_action = $form_action;

        return $this;
    }
    	    /**
     * @param mixed $form_action
     *
     * @return self
     */

// /**
//      * @return mixed
//      */
//     public function getFormMode()
//     {
//         return $this->form_mode;
//     }

//     *
//      * @param mixed $form_mode
//      *
//      * @return self
     
//     public function setFormMode($form_mode)
//     {
//         $this->form_mode = $form_mode;

//         return $this;
//     }


	// function Build($vars) {
	// 	parent::Build($vars);
	// 	//add extra class
	// 	//if($vars['form_action']) $this->setFormAction($vars['form_action']);
	// }

    function PrepareStatusDropdown($listing_id){
        global $p;
        $vars = array("I" => "Inactive", "A" => "Active");
        $select_name = "status";
        //if used in context of batch page controls
        if(!empty($page_id)) $select_name .= "_{$listing_id}";
        $output = $p->PrepareFormSelector($select_name, $vars, null, $this->getStatus());
        return $output;
    }
    //includes the category making gubbins
	function PrepareCategoryDropdown(){
		global $p, $cUser;
		$categories = new cCategoryGroup();
        $categories->Load(1);
        //PrepareCategoryDropdown($selector_name="category_id", $selected_id)
        return $categories->PrepareCategoryDropdown("category_id", $this->getCategoryId());
		// $vars = $categories->MakeCategoryArray();

		// //print_r($vars);
		// // add extra option if user is an admin 
		// //print_r($vars);
		// $select_name = "category_id";
		// //if used in context of batch page controls
		// //if(!empty($category_id)) $select_name .= "_{$category_id}";
		// $output = $p->PrepareFormSelector($select_name, $vars, "-- Select category --", $this->getCategory());
		// return $output;
	}
	function PrepareMemberDropdown(){
		global $p, $cUser;
		$member_group = new cMemberGroup();
        list($condition, $label) = $member_group->makeCondition("active");
		$member_group->Load($condition);
        $output = $p->PrepareFormSelector("action", $vars, "Select action", null);
		return $member_group->PrepareMemberDropdown("member_id", $this->getMemberId());
	}	
		

	public function Save() {

		///tod - adapt
        global $cDB, $cUser, $cStatusMessage; 
        //exit the action if not logged in
        $cUser->MustBeLoggedOn();  
        //$cStatusMessage->Error("save data");    
        //Rejigged fo$keys_array


        //only allow user themself and committee to make changes to these fields, and execute. Doublecheck!!!
        if($this->getMember()->getMemberId() == $cUser->getMemberId() || $cUser->getMemberRole() > 0){
            $keys_array = array(
                "status",
                "rate",
                "title",
                "description",
                "category_id",
                "reactivate_date",
                "type",
            );
   //          $field_array["status"]=$this->getStatus();           
   //          //$field_array["status"]=$this->getReactivateDate();           
			// $field_array["title"]=$this->getTitle();
			// $field_array["description"]=$this->getDescription();
			// $field_array["category_id"]=$this->getCategoryId();
			// $field_array["rate"]=$this->getRate();
			// //$field_array["listing_date"]=now; //this should be automatic - leave it to the db
			// $field_array["reactivate_date"]=$this->getReactivateDate();
			// $field_array["type"]=$this->getType();
			// $field_array["member_id"]=$this->getMemberId();
        
            $listing_id = 0;

            //can handle both create and update
            if($this->getFormAction() == "update"){
            	$condition = "`member_id`=\"{$this->getMemberId()}\" AND listing_id = \"{$this->getListingId()}\""; 
                
                //CT don't save the secondary member here, just the primary
                if($this->update(DATABASE_LISTINGS, $keys_array, $condition)){
                    return $this->getListingId();
                }
            } 
            else{
                //TODO -
                $this->setStatus("A");
                $keys_array[]="member_id";
                return  $this->insert(DATABASE_LISTINGS, $keys_array); //CT should return the id 
            }
            return false;
        }
    }







	
	function DeleteListing($title,$member_id,$type_code) {
		global $cDB, $cStatusMessage;
		
		$query = $cDB->Query("DELETE FROM {" . DATABASE_LISTINGS . " WHERE title=".$cDB->EscTxt($title)." AND member_id=". $cDB->EscTxt($member_id) ." AND type=".  $cDB->EscTxt($type_code) .";");

		return mysqli_affected_rows();
	}
}

?>