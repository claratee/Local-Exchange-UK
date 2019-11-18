<?php

class cPersonUtils extends cPerson
{
    private $action;          

 
	public function  __construct ($field_array=null) {
        global $cUser;
        //init in case of creation...will be overwritten if set
    //        $this->setPrimaryMember="Y";
     //      $this->setDirectoryList="Y";
        //base constructor - build if values exist
        if(!empty($field_array)) $this->Build($field_array);
        //print_r($field_array);
    }

	public function Save() {
        global $cDB, $cUser, $cStatusMessage; 
        //print("MemberId: " . $this->getMemberId());
        //exit;
        //CT being extra paranoid here, but making sure that the user has authority to do this
        if($cUser->getMemberId() != $this->getMemberId()) {
            //CT hardstop if user not authorized
            $cUser->MustBeLevel(1);
        }
        $keys_array = array();
        //CT the commented out keys shouldnt change, so not saving them 
        //$keys_array[] = 'member_id';
        //$keys_array[] = 'person_id';
        //$keys_array[] = 'primary_member';
        $keys_array[]="directory_list"; 
        $keys_array[]="first_name"; 
        $keys_array[]="last_name"; 
        $keys_array[]="email"; 
        $keys_array[]="phone1_number"; 
        $keys_array[]="phone2_number"; 
        $keys_array[]="address_street1"; 
        $keys_array[]="address_street2"; 
        $keys_array[]="address_city"; 
        $keys_array[]="address_state_code"; 
        $keys_array[]="address_post_code";
        $keys_array[]="address_country"; 
        $keys_array[]="about_me";
        $keys_array[]="age";
        $keys_array[]="sex";

        if($this->getAction() == "create"){

            //TODO -
            //make sure status=L, primary member=Y are all set before get to this stage
            $keys_array[] = 'member_id';
            $keys_array[] = 'primary_member';
            $person_id = $this->insert(DATABASE_PERSONS, $keys_array);        
        }else{
            $condition = "member_id=\"{$this->getMemberId()}\"";  
            $person_id = $this->update(DATABASE_PERSONS, $keys_array, $condition);
        }

		return $person_id;
	}
    function DeleteJointPerson() {
        //CT can only be done on joint members, ie not primary - safety is in the db call
        global $cDB, $cStatusMessage;
        $is_success = $cDB->Query("DELETE FROM ".DATABASE_PERSONS." WHERE member_id={$cDB->EscTxt($this->getMemberId())} AND primary_member = \"N\" AND person_id = \"{$this->getPersonId()}\"");
        //if($is_success) print("halloooooo");
        return $is_success;
    }


    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     *
     * @return self
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

}




?>
