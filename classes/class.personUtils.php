<?php

class cPersonUtils extends cPerson
{
    private $action;          

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
        $field_array["directory_list"] = $this->getDirectoryList(); 
        $field_array["first_name"] = $this->getFirstName(); 
        $field_array["last_name"] = $this->getLastName(); 
        $field_array["email"] = $this->getEmail(); 
        $field_array["phone1_number"] = $this->getPhone1Number(); 
        $field_array["phone2_number"] = $this->getPhone2Number(); 
        $field_array["address_street1"] = $this->getAddressStreet1(); 
        $field_array["address_street2"] = $this->getAddressStreet2(); 
        $field_array["address_city"] = $this->getAddressCity(); 
        $field_array["address_state_code"] = $this->getAddressStateCode(); 
        $field_array["address_post_code"] = $this->getAddressPostCode();
        $field_array["address_country"] = $this->getAddressCountry(); 
        $field_array["about_me"] = $this->getAboutMe();
        $field_array["age"] = $this->getAge();
        $field_array["sex"] = $this->getSex();

        if($this->getAction() == "create"){

            //TODO -
            //make sure status=L, primary member=Y are all set before get to this stage
            $field_array["member_id"] = $this->getMemberId(); 
            $field_array["primaryMember"] = $this->getPrimaryMember(); 
            $person_id = $this->insert(DATABASE_PERSONS, $field_array);        
        }else{
            $condition = "`member_id`=\"{$this->getMemberId()}\" AND `person_id`=\"{$this->getPersonId()}\"";  
            $person_id = $this->update(DATABASE_PERSONS, $field_array, $condition);
        }

		return $person_id;
	}
    function DeleteJointPerson() {
        //CT can only be done on joint members, ie not primary - safety is in the db call
        global $cDB, $cStatusMessage;
        $is_success = $cDB->Query("DELETE FROM ".DATABASE_PERSONS." WHERE `member_id`=\"{$cDB->EscTxt($this->getMemberId())}\" AND `primary_member` = \"N\" AND `person_id` = \"{$this->getPersonId()}\"");
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
