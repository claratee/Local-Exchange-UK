<?php

class cPerson extends cSingle
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

    private $action;          

 

//CT not used - generally uses member load to get member and instantiate from there. Here for completeness
	    //CT rebuilt to be..not so dangerous. do not load passwords and such into memory
    public function Load($condition) {
		global $cDB, $cQueries;
        $string_query = $cQueries->getMySqlPerson($condition);
        return $this->LoadFromDatabase($string_query);
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
