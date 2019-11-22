<?php

class cPerson extends cBasic2
{
	private $person_id;			
	private $member_id;
	private $primary_member;
	private $directory_list;
	private $first_name;
	private $last_name;
	private $email;
	private $phone1_number;
	private $phone2_number;

	private $address_street1;
	private $address_street2;
	private $address_city;
	private $address_state_code;
    private $address_post_code;
	private $address_country;
	private $about_me;
    private $age;
    private $sex;
    //CT this is a helper for UK
    //private $safe_post_code;
    //remove these?
    private $phone1_ext;
    private $phone2_area;
    private $phone1_area;
    private $mid_name;
    private $dob;
    private $mother_mn;
    private $phone2_ext;
    private $fax_area;
    private $fax_number;
    private $fax_ext;
  

//CT delete - moved to edit class			
	// public function Save($formAction='update') {
 //        //update or create
	// 	global $cDB, $cStatusMessage;
	// 	/*[chris]*/ // Added store personal profile data
 //        //CT - converted to array so we dont have to set fiedls that are not present
 //        $field_array = Array();
 //        /* $field_array["person_id"] = $this->getPersonId(); */
 //        //dont think you should change this in a save?
 //        //$field_array["primary_member"]=$this->getPrimaryMember(); 
 //        $field_array["directory_list"]=$this->getDirectoryList(); 
 //        $field_array["first_name"]=$this->getFirstName(); 
 //        $field_array["last_name"]=$this->getLastName(); 
 //        //$field_array["mid_name"]=$this->getMidName(); 
 //        //$field_array["dob"]=$this->getDob(); 
 //        //$field_array["mother_mn"]=$this->getMotherMn(); 
 //        $field_array["email"]=$this->getEmail(); 
 //        //$field_array["phone1_area"]=$this->getPhone1Area(); 
 //        $field_array["phone1_number"]=$this->getPhone1Number(); 
 //        //$field_array["phone1_ext"]=$this->getPhone1Ext(); 
 //        //$field_array["phone2_area"]=$this->getPhone2Area(); 
 //        $field_array["phone2_number"]=$this->getPhone2Number(); 
 //        //$field_array["phone2_ext"]=$this->getPhone2Ext(); 
 //        //$field_array["fax_area"]=$this->getFaxArea(); 
 //        //$field_array["fax_number"]=$this->getFaxNumber(); 
 //        //$field_array["fax_ext"]=$this->getFaxExt(); 
 //        $field_array["address_street1"]=$this->getAddressStreet1(); 
 //        $field_array["address_street2"]=$this->getAddressStreet2(); 
 //        $field_array["address_city"]=$this-> getAddressCity(); 
 //        $field_array["address_state_code"]=$this->getAddressStateCode(); 
 //        $field_array["address_post_code"]=$this->getAddressPostcode();
 //        $field_array["address_country"]=$this->getAddressCountry(); 
 //        $field_array["about_me"]=$this->getAboutMe();
 //        $field_array["age"]=$this->getAge();
 //        $field_array["sex"]=$this->getSex();

 //        $is_success = 0;
 //        if($formAction == 'update'){
 //            $condition = "`person_id`=\"{$this->getPersonId()}\""; 
 //            $string_query = $cDB->BuildUpdateQuery(DATABASE_PERSONS, $field_array, $condition);  
 //            $error_message = "Could not save changes to person {$this->getPersonId()} associated with member {$this->getMemberId()}.";
 //        } else{
 //           // create new member
 //            //TODO: must pass in 
 //           //$this->setMemberId();
 //          $field_array["member_id"] = $this->getMemberId();
 //           //print_r($field_array);
 //           $string_query = $cDB->BuildInsertQuery(DATABASE_PERSONS, $field_array);
 //           $is_success = $cDB->Query($string_query);
 //           $error_message = "Could not create person associated with member {$this->getMemberId()}.";
 //        }
 //        // do query
 //        $is_success = $cDB->Query($string_query);
 //        if(!$is_success){
 //            $cStatusMessage->Error($error_message);    
 //        }

 //        //$cStatusMessage->Error("STRING:" . $string);

	// 	return $is_success;
	// }

    private $action;          

 
    public function  __construct ($field_array=null) {
        global $cUser;
        //init in case of creation
        //$this->setPrimaryMember="Y";
        //$this->setDirectoryList="Y";
        //base constructor - build if values exist
        if(!empty($field_array)) $this->Build($field_array);
        //print_r($field_array);
    }
//CT not used - generally uses member load to get member and instantiate from there. Here for completeness
	public function Load($condition)
	{
		global $cDB, $cStatusMessage, $cQueries;
		
		//CT
		 $string_query = $cQueries->getSqlPerson($condition);
        return $this->LoadDatabaseTable($string_query);

        // $is_success = false;

        // if($field_array = $cDB->FetchArray($query)){   
        //     $isSuccess = $this->Build($field_array);
        // } else {
        //     //CT - moved error message out of the redirect - don't you wnat to see errors even if not redirected?
        //     $cStatusMessage->Error("Error accessing member.");

        // }   
   
	}

// public function Save() {
//         global $cDB, $cUser, $cStatusMessage; 

//         //CT being extra paranoid here, but making sure that the user has authority to do this
//         if($cUser->getMemberId() != $this->getMemberId()) {
//             //CT hardstop if user not authorized
//             $cUser->MustBeLevel(1);
//         }
//         $keys_array = array();
//         //CT these shouldnt change, so not saving them either
//         //$keys_array[] = 'member_id';
//         //$keys_array[] = 'person_id';
//         //$keys_array[] = 'primary_member';
//         $keys_array[]="directory_list"; 
//         $keys_array[]="first_name"; 
//         $keys_array[]="last_name"; 
//         $keys_array[]="email"; 
//         $keys_array[]="phone1_number"; 
//         $keys_array[]="phone2_number"; 
//         $keys_array[]="address_street1"; 
//         $keys_array[]="address_street2"; 
//         $keys_array[]="address_city"; 
//         $keys_array[]="address_state_code"; 
//         $keys_array[]="address_post_code";
//         $keys_array[]="address_country"; 
//         $keys_array[]="about_me";
//         $keys_array[]="age";
//         $keys_array[]="sex";

//         if($this->getAction() == "create"){

//             //TODO -
//             //make sure status=L, primary member=Y are all set before get to this stage
//             $keys_array[] = 'member_id';
//             $keys_array[] = 'primary_member';
//             //$keys_array[] = 'status';

//             //temporary password - user should reset when they log in
//             // $password = $this->GeneratePassword();
//             // $field_array["password"] =  password_hash($password, PASSWORD_DEFAULT);
//             try{
//                 $this->insert(DATABASE_PERSONS, $keys_array);
//             }catch (Exception $e){
//                  $cStatusMessage->Error("New person: " . $e->getMessage());

//                  //$cStatusMessage->Error("Could not create person with member Id {$this->getMemberId()}.");
//             }
            
//         }else{
//             $condition = "member_id=\"{$this->getMemberId()}\"";  
//             try{
//                 $this->update(DATABASE_PERSONS, $keys_array, $condition);
//             }catch (Exception $e){
//                  //$cStatusMessage->Error("Could not save person {$this->getPersonId()} with member Id {$this->getMemberId()}.");
//                  $cStatusMessage->Error("Update person: " . $e->getMessage());
//             }
//         }

//         //update or create
//         /*[chris]*/ // Added store personal profile data
//         //CT - converted to array so we dont have to set fiedls that are not present
//         // $field_array = Array();
//         // /* $field_array["person_id"] = $this->getPersonId(); */
//         // //dont think you should change this in a save?
//         // //$field_array["primary_member"]=$this->getPrimaryMember(); 
//         // $field_array["directory_list"]=$this->getDirectoryList(); 
//         // $field_array["first_name"]=$this->getFirstName(); 
//         // $field_array["last_name"]=$this->getLastName(); 
//         // //$field_array["mid_name"]=$this->getMidName(); 
//         // //$field_array["dob"]=$this->getDob(); 
//         // //$field_array["mother_mn"]=$this->getMotherMn(); 
//         // $field_array["email"]=$this->getEmail(); 
//         // //$field_array["phone1_area"]=$this->getPhone1Area(); 
//         // $field_array["phone1_number"]=$this->getPhone1Number(); 
//         // //$field_array["phone1_ext"]=$this->getPhone1Ext(); 
//         // //$field_array["phone2_area"]=$this->getPhone2Area(); 
//         // $field_array["phone2_number"]=$this->getPhone2Number(); 
//         // //$field_array["phone2_ext"]=$this->getPhone2Ext(); 
//         // //$field_array["fax_area"]=$this->getFaxArea(); 
//         // //$field_array["fax_number"]=$this->getFaxNumber(); 
//         // //$field_array["fax_ext"]=$this->getFaxExt(); 
//         // $field_array["address_street1"]=$this->getAddressStreet1(); 
//         // $field_array["address_street2"]=$this->getAddressStreet2(); 
//         // $field_array["address_city"]=$this-> getAddressCity(); 
//         // $field_array["address_state_code"]=$this->getAddressStateCode(); 
//         // $field_array["address_post_code"]=$this->getAddressPostcode();
//         // $field_array["address_country"]=$this->getAddressCountry(); 
//         // $field_array["about_me"]=$this->getAboutMe();
//         // $field_array["age"]=$this->getAge();
//         // $field_array["sex"]=$this->getSex();

//         // $is_success = 0;
//         // if(!empty($this->getPersonId())){
//         //     $condition = "`person_id`=\"{$this->getPersonId()}\""; 
//         //     $string_query = $cDB->BuildUpdateQuery(DATABASE_PERSONS, $field_array, $condition);  
//         //     $error_message = "Could not save changes to person {$this->getPersonId()} associated with member {$this->getMemberId()}.";
//         // } else{
//         //    // create new member
//         //     //TODO: must pass in 
//         //    //$this->setMemberId();
//         //   $field_array["member_id"] = $this->getMemberId();
//         //    //print_r($field_array);
//         //    $string_query = $cDB->BuildInsertQuery(DATABASE_PERSONS, $field_array);
//         //    $is_success = $cDB->Query($string_query);
//         //    $error_message = "Could not create person associated with member {$this->getMemberId()}.";
//         // }
//         // do query
//         // $is_success = $cDB->Query($string_query);
//         // if(!$is_success){
//         //     $cStatusMessage->Error($error_message);    
//         // }

//         //$cStatusMessage->Error("STRING:" . $string);

//         return $is_success;
//     }


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
	
	// function DeletePerson() {
	// 	//can only be done on joint members
	// 	global $cDB, $cStatusMessage;
		
	// 	if($this->primary_member == 'Y') {
	// 		$cStatusMessage->Error("Cannot delete primary member!");	
	// 		return false;
	// 	} 
		
	// 	$delete = $cDB->Query("DELETE FROM ".DATABASE_PERSONS." WHERE person_id=". $cDB->EscTxt($this->getPersonId()));
		
	// 	unset($this->person_id);
		
	// 	if (mysqli_affected_rows() == 1) {
	// 		return true;
	// 	} else {
	// 		$cStatusMessage->Error("Error deleting joint member.");
	// 	}
		
	// }

	/*					
	function ShowPerson()
	{
		$output = $this->getPersonId() . ", " . $this->getMemberId() . ", " . $this->getPrimaryMember() . ", " . $this->getDirectoryList() . ", " . $this->getFirstName() . ", " . $this->getLastName() . ", " . $this->getPersonId() . ", " . $this->dob . ", " . $this->mother_mn . ", " . $this->email . ", " . $this->phone1_area . ", " . $this->phone1_number . ", " . $this->phone1_ext . ", " . $this->phone2_area . ", " . $this->phone2_number . ", " . $this->phone2_ext . ", " . $this->fax_area . ", " . $this->fax_number . ", " . $this->fax_ext . ", " . $this->address_street1 . ", " . $this->address_street2 . ", " . $this->address_city . ", " . $this->address_state_code . ", " . $this->address_post_code . ", " . $this->address_country;
		
		return $output;
	}
    */

	// function Name() {
	// 	return $this->first_name . " " .$this->last_name;	
	// }
			
	// function DisplayPhone($type)
	// {
	// 	global $cStatusMessage;

	// 	switch ($type)
	// 	{
	// 		case "1":
	// 			$phone_area = $this->getPhone1Area();
	// 			$phone_number = $this->getPhone1Number();
	// 			$phone_ext = $this->getPhone1Ext();
	// 			break;
	// 		case "2":
 //                $phone_area = $this->getPhone2Area();
 //                $phone_number = $this->getPhone2Number();
 //                $phone_ext = $this->getPhone2Ext();
	// 			break;
	// 		case "fax":
 //                $phone_area = $this->getFaxArea();
 //                $phone_number = $this->getFaxNumber();
 //                $phone_ext = $this->getFaxExt();
	// 			break;								
	// 		default:
	// 			$cStatusMessage->Error("Phone type does not exist.");
	// 			return "ERROR";
	// 	}

 //        $phone = $phone_number;
		
	// 	return $phone_number;
	// }
    // just lists out all phone numbers and fax numbers set for the person
    function getAllPhones(){
        $phones = array();
        if(!empty($this->getPhone1Number())) $phones[] = "{$this->getPhone1Number()}";
        if(!empty($this->getPhone2Number())) $phones[] = "{$this->getPhone2Number()}";
        //CT no one has faxes anymore! right?
        if(!empty($this->getFaxNumber())) $phones[] = "{$this->getFaxNumber()} (FAX)";
        $i=0;
        $string = "";
        foreach($phones as $phone){
            if($i>0) $string .= ", ";
            $string .= $phone;
            $i++;
        }
        return $string;
    }

//CT - removing - easier to trust people to put usable info in the phone fields than catch them on elaborate validation...its for their own benefit not ours
// // TODO: cPerson should use this class instead of a text field
// class cPhone {
// 	var $area;
// 	var $prefix;
// 	var $suffix;
// 	var $ext;
	
// 	function cPhone($phone_str=null) { // this constructor attempts to break down free-form phone #s
// 		if($phone_str) {						// TODO: Use reg expressions to shorten this thing
// 			$ext = "";
// 			$phone_str = strtolower($phone_str);
// 			if ($loc = strpos($phone_str, "x")) {
// 				$ext = substr($phone_str, $loc+1, 10);
// 				$phone_str = substr($phone_str, 0, $loc); // strip extension off the main string
// 				$ext = ereg_replace("t","",$ext);
// 				$ext = ereg_replace("\.","",$ext);
// 				$ext = ereg_replace(" ","",$ext);
// 				if(!is_numeric($ext))
// 					$ext = "";
// 			}
// 			$phone_str = ereg_replace("\(","",$phone_str);
// 			$phone_str = ereg_replace("\)","",$phone_str);
// 			$phone_str = ereg_replace("-","",$phone_str);
// 			$phone_str = ereg_replace("\.","",$phone_str);
// 			$phone_str = ereg_replace(" ","",$phone_str);
// 			$phone_str = ereg_replace("e","",$phone_str);


// 			if(strlen($phone_str) == 7) {
// 				$this->area = DEFAULT_PHONE_AREA;
// 				$this->prefix = substr($phone_str,0,3);
// 				$this->suffix = substr($phone_str,3,4);
// 				$this->ext = $ext;
// 			} elseif (strlen($phone_str) == 10) {
// 				$this->area = substr($phone_str,0,3);
// 				$this->prefix = substr($phone_str,3,3);
// 				$this->suffix = substr($phone_str,6,4);
// 				$this->ext = $ext;				
// 			} else {
// 				return false;			
// 			}
// 		}
// 	}
	
// 	function TenDigits() {
// 		return $this->area . $this->prefix . $this->suffix;
// 	}
	
// 	function SevenDigits() {
// 		return $this->prefix . $this->suffix;
// 	}
	
// }


// /**
//  * Temporary phone class for UK.  This is to be used in place of all instances
//  * of "cPhone".
//  */
// class cPhone_uk {
// 	var $area;
// 	var $prefix;
// 	var $suffix;
// 	var $ext;
//     var $number;

// 	// this constructor attempts to break down free-form phone #s
// 	function cPhone_uk($phone_str=null) { 
//         // TODO: Use reg expressions to shorten this thing
// 		if( !empty($phone_str)) {						
//             $tmp = preg_replace("/[^\d]/", "", $phone_str);

//             // Most UK phone numbers when written without the areacode are 8
//             // digits.
//             if(strlen($tmp) >= 8) { 
//                 $this->number = $phone_str;
//                 $this->ext = "";
//                 $this->area = DEFAULT_PHONE_AREA;

//                 // We are not using them.  But they are checked in
//                 // verify_phone_number()
//                 $this->prefix = $this->suffix = true;
//             }
//             else {
//                 return false;
//             }
//         }
// 	}
	
// 	function TenDigits() {
// 		return $this->number;
// 	}
	
// 	function SevenDigits() {
// 		return $this->number;
// 	}

    

    
// 
    /**
     * @return mixed
     */
    public function getPersonId()
    {
        return $this->person_id;
    }

    /**
     * @param mixed $person_id
     *
     * @return self
     */
    public function setPersonId($person_id)
    {
        $this->person_id = $person_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMemberId()
    {
        
                //print('member' . $member_id);
        return $this->member_id;
    }

    /**
     * @param mixed $member_id
     *
     * @return self
     */
    public function setMemberId($member_id)
    {
        $this->member_id = $member_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrimaryMember()
    {
        return $this->primary_member;
    }

    /**
     * @param mixed $primary_member
     *
     * @return self
     */
    public function setPrimaryMember($primary_member)
    {
        $this->primary_member = $primary_member;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDirectoryList()
    {
        return $this->directory_list;
    }

    /**
     * @param mixed $directory_list
     *
     * @return self
     */
    public function setDirectoryList($directory_list)
    {
        $this->directory_list = $directory_list;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param mixed $first_name
     *
     * @return self
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param mixed $last_name
     *
     * @return self
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone1Number()
    {
        return $this->phone1_number;
    }

    /**
     * @param mixed $phone1_number
     *
     * @return self
     */
    public function setPhone1Number($phone1_number)
    {
        $this->phone1_number = $phone1_number;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone2Number()
    {
        return $this->phone2_number;
    }

    /**
     * @param mixed $phone2_number
     *
     * @return self
     */
    public function setPhone2Number($phone2_number)
    {
        $this->phone2_number = $phone2_number;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddressStreet1()
    {
        return $this->address_street1;
    }

    /**
     * @param mixed $address_street1
     *
     * @return self
     */
    public function setAddressStreet1($address_street1)
    {
        $this->address_street1 = $address_street1;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddressStreet2()
    {
        return $this->address_street2;
    }

    /**
     * @param mixed $address_street2
     *
     * @return self
     */
    public function setAddressStreet2($address_street2)
    {
        $this->address_street2 = $address_street2;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddressCity()
    {
        return $this->address_city;
    }

    /**
     * @param mixed $address_city
     *
     * @return self
     */
    public function setAddressCity($address_city)
    {
        $this->address_city = $address_city;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddressStateCode()
    {
        return $this->address_state_code;
    }

    /**
     * @param mixed $address_state_code
     *
     * @return self
     */
    public function setAddressStateCode($address_state_code)
    {
        $this->address_state_code = $address_state_code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddressPostCode()
    {
        return $this->address_post_code;
    }

    /**
     * @param mixed $address_post_code
     *
     * @return self
     */
    public function setAddressPostCode($address_post_code)
    {
        $this->address_post_code = $address_post_code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddressCountry()
    {
        return $this->address_country;
    }

    /**
     * @param mixed $address_country
     *
     * @return self
     */
    public function setAddressCountry($address_country)
    {
        $this->address_country = $address_country;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAboutMe()
    {
        return $this->about_me;
    }

    /**
     * @param mixed $about_me
     *
     * @return self
     */
    public function setAboutMe($about_me)
    {
        $this->about_me = $about_me;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param mixed $age
     *
     * @return self
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * @param mixed $sex
     *
     * @return self
     */
    public function setSex($sex)
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSafePostCode()
    {
        return $this->safe_post_code;
    }

    /**
     * @param mixed $safe_post_code
     *
     * @return self
     */
    public function setSafePostCode($safe_post_code)
    {
        $this->safe_post_code = $safe_post_code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone1Ext()
    {
        return $this->phone1_ext;
    }

    /**
     * @param mixed $phone1_ext
     *
     * @return self
     */
    public function setPhone1Ext($phone1_ext)
    {
        $this->phone1_ext = $phone1_ext;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone2Area()
    {
        return $this->phone2_area;
    }

    /**
     * @param mixed $phone2_area
     *
     * @return self
     */
    public function setPhone2Area($phone2_area)
    {
        $this->phone2_area = $phone2_area;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone1Area()
    {
        return $this->phone1_area;
    }

    /**
     * @param mixed $phone1_area
     *
     * @return self
     */
    public function setPhone1Area($phone1_area)
    {
        $this->phone1_area = $phone1_area;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMidName()
    {
        return $this->mid_name;
    }

    /**
     * @param mixed $mid_name
     *
     * @return self
     */
    public function setMidName($mid_name)
    {
        $this->mid_name = $mid_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDob()
    {
        return $this->dob;
    }

    /**
     * @param mixed $dob
     *
     * @return self
     */
    public function setDob($dob)
    {
        $this->dob = $dob;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMotherMn()
    {
        return $this->mother_mn;
    }

    /**
     * @param mixed $mother_mn
     *
     * @return self
     */
    public function setMotherMn($mother_mn)
    {
        $this->mother_mn = $mother_mn;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone2Ext()
    {
        return $this->phone2_ext;
    }

    /**
     * @param mixed $phone2_ext
     *
     * @return self
     */
    public function setPhone2Ext($phone2_ext)
    {
        $this->phone2_ext = $phone2_ext;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFaxArea()
    {
        return $this->fax_area;
    }

    /**
     * @param mixed $fax_area
     *
     * @return self
     */
    public function setFaxArea($fax_area)
    {
        $this->fax_area = $fax_area;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFaxNumber()
    {
        return $this->fax_number;
    }

    /**
     * @param mixed $fax_number
     *
     * @return self
     */
    public function setFaxNumber($fax_number)
    {
        $this->fax_number = $fax_number;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFaxExt()
    {
        return $this->fax_ext;
    }

    /**
     * @param mixed $fax_ext
     *
     * @return self
     */
    public function setFaxExt($fax_ext)
    {
        $this->fax_ext = $fax_ext;

        return $this;
    }
}




?>
