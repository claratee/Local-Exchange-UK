<?php

class cPersonUtils extends cPerson
{
    private $action;          

 
	public function  __construct ($field_array=null) {
        global $cUser;
        //init in case of creation
        $this->setPrimaryMember="Y";
        $this->setDirectoryList="Y";
        //base constructor - build if values exist
        if(!empty($field_array)) $this->Build($field_array);
        print_r($field_array);
    }

	public function Save() {
        global $cDB, $cUser, $cStatusMessage; 

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
            $person_id = $this->CreateAbstract(DATABASE_PERSONS, $keys_array);        
        }else{
            $condition = "member_id=\"{$this->getMemberId()}\"";  
            $person_id = $this->SaveAbstract(DATABASE_PERSONS, $keys_array, $condition);
        }

        //update or create
		/*[chris]*/ // Added store personal profile data
        //CT - converted to array so we dont have to set fiedls that are not present
        // $field_array = Array();
        // /* $field_array["person_id"] = $this->getPersonId(); */
        // //dont think you should change this in a save?
        // //$field_array["primary_member"]=$this->getPrimaryMember(); 
        // $field_array["directory_list"]=$this->getDirectoryList(); 
        // $field_array["first_name"]=$this->getFirstName(); 
        // $field_array["last_name"]=$this->getLastName(); 
        // //$field_array["mid_name"]=$this->getMidName(); 
        // //$field_array["dob"]=$this->getDob(); 
        // //$field_array["mother_mn"]=$this->getMotherMn(); 
        // $field_array["email"]=$this->getEmail(); 
        // //$field_array["phone1_area"]=$this->getPhone1Area(); 
        // $field_array["phone1_number"]=$this->getPhone1Number(); 
        // //$field_array["phone1_ext"]=$this->getPhone1Ext(); 
        // //$field_array["phone2_area"]=$this->getPhone2Area(); 
        // $field_array["phone2_number"]=$this->getPhone2Number(); 
        // //$field_array["phone2_ext"]=$this->getPhone2Ext(); 
        // //$field_array["fax_area"]=$this->getFaxArea(); 
        // //$field_array["fax_number"]=$this->getFaxNumber(); 
        // //$field_array["fax_ext"]=$this->getFaxExt(); 
        // $field_array["address_street1"]=$this->getAddressStreet1(); 
        // $field_array["address_street2"]=$this->getAddressStreet2(); 
        // $field_array["address_city"]=$this-> getAddressCity(); 
        // $field_array["address_state_code"]=$this->getAddressStateCode(); 
        // $field_array["address_post_code"]=$this->getAddressPostcode();
        // $field_array["address_country"]=$this->getAddressCountry(); 
        // $field_array["about_me"]=$this->getAboutMe();
        // $field_array["age"]=$this->getAge();
        // $field_array["sex"]=$this->getSex();

        // $is_success = 0;
        // if(!empty($this->getPersonId())){
        //     $condition = "`person_id`=\"{$this->getPersonId()}\""; 
        //     $string_query = $cDB->BuildUpdateQuery(DATABASE_PERSONS, $field_array, $condition);  
        //     $error_message = "Could not save changes to person {$this->getPersonId()} associated with member {$this->getMemberId()}.";
        // } else{
        //    // create new member
        //     //TODO: must pass in 
        //    //$this->setMemberId();
        //   $field_array["member_id"] = $this->getMemberId();
        //    //print_r($field_array);
        //    $string_query = $cDB->BuildInsertQuery(DATABASE_PERSONS, $field_array);
        //    $is_success = $cDB->Query($string_query);
        //    $error_message = "Could not create person associated with member {$this->getMemberId()}.";
        // }
        // do query
        // $is_success = $cDB->Query($string_query);
        // if(!$is_success){
        //     $cStatusMessage->Error($error_message);    
        // }

        //$cStatusMessage->Error("STRING:" . $string);

		return $person_id;
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
