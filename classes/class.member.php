<?php



//include_once("Text/Password.php");



class cMember extends cSingle
{
    private $member_id;
    private $is_nested; //if set to true, don't cross ref other classes - prevent circuclar logic ;
	//private $password; //CT not wanted. only part of the cUser class
    private $member_role;
	//private $security_q; //CT not needed
	//private $security_a; //CT not needed
	private $status;
	private $member_note;
	private $admin_note;
	private $join_date;
	private $expire_date;
	private $away_date;
	private $account_type;
	private $email_updates;
	private $balance;
	private $confirm_payments;
    private $restriction;
    private $opt_in_list;
   //CT: extra properties
    private $display_name;  
    private $person;  
    private $joint_person;  
    //reset passwrod facitlity
    private $password_reset;  

    public function  __construct ($field_array=null) {
        //CT instantiate the people! Like a GOD!
        $person = $this->makePerson();
        $person1 = $this->makePerson();
        $this->setPerson($person);
        $this->setJointPerson($person1);
        //CT password_reset - for token flow. stays empty most of the time.
        $password_reset = new cPasswordReset();
        $this->setPasswordReset($password_reset);
        //CT do the do with the array
        if(!empty($field_array)) $this->Build($field_array);
    }

    public function getRestrictionLabel()
    {
        $label = ($this->getRestriction()) ? "Restricted" : "";
        return $label;
    }

    //CT bit like a factory - returns new person object. rerouting opportunity for extend classes
	public function makePerson($field_array=null){
        return new cPerson($field_array);
    }
    //CT rebuilt to be..not so dangerous. do not load passwords and such into memory
    public function Load($condition) {
		global $cDB, $cQueries;
        $string_query = $cQueries->getMySqlMember($condition);
        return $this->LoadFromDatabase($string_query);
	}

    public function buildFieldArrayForJointPerson($old_array){
        //cT CT check if they are all empty, then skip
        $field_array = array();
        $field_array['member_id'] = $old_array['member_id']; 
        $field_array['person_id'] = $old_array['j_person_id']; 
        $field_array['first_name'] = $old_array['j_first_name']; 
        $field_array['last_name'] = $old_array['j_last_name']; 
        $field_array['directory_list'] = $old_array['j_directory_list']; 
        $field_array['primary_member'] = "N"; 
        $field_array['email'] = $old_array['j_email']; 
        $field_array['phone1_number'] = $old_array['j_phone1_number']; 
        return $field_array;
    }
    // public function buildFieldArrayForPerson($old_array){
    //     //cT CT check if they are all empty, then skip
    //     $field_array = array();
    //     $field_array['member_id'] = $old_array['member_id']; 
    //     $field_array['person_id'] = $old_array['person_id']; 
    //     $field_array['first_name'] = $old_array['first_name']; 
    //     $field_array['last_name'] = $old_array['last_name']; 
    //     $field_array['phone1_number'] = $old_array['phone1_number']; 
    //     $field_array['directory_list'] = "Y"; 
    //     $field_array['primary_member'] = "Y"; 
    //     $field_array['email'] = $old_array['email']; 
    //     $field_array['phone1_number'] = $old_array['phone1_number']; 
    //     return $field_array;
    // }
	// public function BuildSingle($row){
 //        global $cDB;
 //        parent::BuildSingle($row);

 //        // extra bits for convenience
 //        $this->getPasswordReset()->Build($row);  //CT call the build function for password - will just set the member_id

 //        $this->getPerson()->Build($row);  //CT call the build function for person

 //        if($this->getAccountType() == "J"){
 //            $joint_person_row = $this->buildFieldArrayForJointPerson($row);
 //            $this->getJointPerson()->Build($joint_person_row);         
 //        }


 //        return true;
 //    }
    public function Build($row){
        global $cDB;
        //print_r($row);
        parent::Build($row);
        // extra bits for convenience
        $this->getPasswordReset()->Build($row);  //CT call the build function for password - will just set the member_id

        //$person_field_array = $this->buildFieldArrayForPerson($field_array);
        $this->getPerson()->Build($row);  //CT call the build function for person
        //CT presets - todo:change
        //if(empty($this->getPerson()->getAge())) $this->getPerson()->setAge(9);
        //if(empty($this->getPerson()->getSex())) $this->getPerson()->setSex(3);
        //print("mebmer" . $this->getPerson()->getMemberId());
        // secondary
        //print_r($this->getAccountType());
        //print_r("sdf");
        if($this->getAccountType() == "J"){
            $joint_person_row = $this->buildFieldArrayForJointPerson($row);
            $this->getJointPerson()->Build($joint_person_row);         
        }


        return true;
    }
//CT here because its useful. sets from other fields. done like this as it might be in cUser which doent necessarily have all the fields.

public function getDisplayName(){
    if(!empty($this->display_name)) {
        return $this->display_name;
    }else{
        $display_name = "";
        //CT 
        if (!empty($this->getPerson()->getFirstName())) $display_name .= $this->getPerson()->getFirstName();
        if (!empty($this->getPerson()->getLastName())) $display_name .= " " . $this->getPerson()->getLastName();

        //make sure user is supposed to be visible
        if($this->getAccountType() == "J" AND $this->getJointPerson()->getDirectoryList() == "Y"){
            $display_name .= " &amp; " . $this->getJointPerson()->getFirstName(); 
            $display_name .= " " . $this->getJointPerson()->getLastName();
        }
        
        if(empty($display_name)) $display_name =  "Member";

        //usually excluded from results - except for admins - useful so we can remove inline checks
        if($this->getStatus() == "I") $display_name .= " *inactive*";


        //sets for next retrieval
        $this->setDisplayName($display_name);
        return $this->display_name;
    }
}
public function setDisplayName($display_name){
    $this->display_name = $display_name;
    return $this;
}


    //helper function to make appropriate buttons for actions
    public function makeButtonsFromAction($action, $member_id){

        switch($action){
            case "edit":
                //all - no filter
                $link = "member_edit.php";
                $icon = "fa-pencil-alt";
                $label = "Edit";
            break;
            case "status":
                //all inactive
                $link = "member_status_change.php";
                $icon = "fa-eye-slash";
                $label = "Status";
            break;
            case "restrict":
                //all inactive
                $link = "member_restriction.php";
                $icon = "fa-flag";
                $label = "Restriction";
            break;

        }
        if(empty($link)) return false;
        $icon_element = (empty($icon)) ? "" : "<i class=\"fas {$icon}\"></i>";
        $button = "<a href=\"{$link}?member_id={$member_id}\" class=\"button\">{$icon_element}{$label}</a>";

        return $button;

    }
    public function makeActionsButtons($actions_keys, $member_id){
        //print_r($member_id);
        $actions_string = "";
        foreach ($actions_keys as $action) {
            $actions_string .= $this->makeButtonsFromAction($action, $member_id);
        }
        return $actions_string;

    }



// these are just for display - not point storing
public function getDisplayPhone(){

    //CT bit complicated but handles various states like missing data
    $display_phone = $this->getPerson()->getPhone1Number();
    //CT only show if joint member is listed
    // if($this->getAccountType() == "J" AND $this->getJointPerson()->getDirectoryList() == "Y"){
    //     //CT only show if joint member has phone number
    //     if (!empty($this->getJointPerson()->getPhone1Number())){
    //             //CT make sure primary member has phone too!
    //         if(!empty($this->getPerson()->getPhone1Number())) $display_phone .= " ({$this->getPerson()->getFirstName()})<br />";
    //         $display_phone .=  $this->getJointPerson()->getPhone1Number() . " ({$this->getJointPerson()->getFirstName()})"; 
    //     }
    // }
    return $display_phone;
}
// these are just for display - not point storing
public function getDisplayEmail(){

    //CT TODO - other options - like firstnames only?
    $display_email = $this->getPerson()->getEmail();
    if($this->getAccountType() == "J" AND $this->getJointPerson()->getDirectoryList() == "Y"){
        $display_email .= ", " . $this->getJointPerson()->getEmail() . " ({$this->getJointPerson()->getFirstName()})"; 
    }
    return $display_email;
}

public function getDisplayLocation(){

    //CT TODO - other options - like firstnames only?
    $string ="{$this->getPerson()->getAddressStreet2()}";
    if (!empty(trim($this->getPerson()->getAddressStreet2())) AND !empty(trim($this->getPerson()->getAddressCity()))){
        $string  .= ", ";
    }
    $string .= "{$this->getPerson()->getAddressCity()}, {$this->getDisplayPostCode()}";
    return $string;
}

    public function getDisplayPostCode()
    {
        

        if (DEFAULT_COUNTRY == "United Kingdom"){
            $array = preg_split("([ /-/_])", $this->getPerson()->getAddressPostcode());
            $postcode = $array[0];
            // CT: hack. just in case postcode has been put in without spaces or other dividers
            if (strlen($postcode) > 4) {
                $postcode = substr($postcode, 0, 3);
            }
        } 
        return $postcode;
    }

/* //CT its easy enough to do a print_r for testing, ths is too much of a faff
	public function ShowMember()
	{
		$output = "Member Data:<BR>";
		$output .= $this->member_id . ", " . $this->password . ", " . $this->member_role . ", " . $this->security_q . ", " . $this->security_a . ", " . $this->status . ", " . $this->member_note . ", " . $this->admin_note . ", " . $this->join_date . ", " . $this->expire_date . ", " . $this->away_date . ", " . $this->account_type . ", " . $this->email_updates . ", " . $this->balance . "<BR><BR>";
		
		$output .= "Person Data:<BR>";
		
		foreach($this->person as $person)
		{
			$output .= $person->ShowPerson();
			$output .= "<BR><BR>";
		}			
						
		return $output;
	}		
// */	
// 	public function UpdateBalance($amount) {
// 		$this->balance += $amount;
// 		return $this->Save();
// 	}
	


    public function makeExpireRelativeDate() {
        $sentence = "";
        $now = date("Y-m-d");


        $datetime1 = new DateTime($now);
        $datetime2 = new DateTime($this->getExpireDate());
        $interval = $datetime1->diff($datetime2);
        //return $interval->format('%R%a days');
        $interval =  $interval->format('%R%a');
        $string = "";
        if (substr($interval, 0, 1) == "+"){
            if(substr($interval,1)<30){
                $classname = "positive";
                $interval =  "due in " . substr($interval,1) . " days";
                $string = "<span class=\"expiry {$classname}\">$interval</span>";
            }
        } else{
            $classname = "negative";
            $interval =   "due " . substr($interval,1) . " days ago";
            $string = "<span class=\"expiry {$classname}\">$interval</span>";
        }
        
 
        return $string;
    }

//CT hmm, I am going to take this out for now
	/* 
	public function VerifyPersonInAccount($person_id) { // Make sure hacker didn't manually change URL
		global $cStatusMessage;
		foreach($this->person as $person) {
			if($person->getPersonId() == $person_id)
				return true;
		}
		$cStatusMessage->Error("Invalid person id in URL.  This break-in attempt has been reported.",ERROR_SEVERITY_HIGH);
		include("redirect.php");
	}
    */
/*
	public function makeLinkEmailForm($email){
        //return "<a href='mailto:{$email}' class='normal'>{$email}</a>";
        return "<a href='mailto:{$email}' class='normal'>{$email}</a>";
    }
	public function AllEmails () {
        $emails='';
		foreach ($this->person as $person) {
			if(!empty($person->getEmail())){
                //$email = $this->makeLinkEmailForm($person->getEmail());
                $email = $person->getEmail();
	            if($person->getPrimaryMember() != "Y") {
	                $emails .= ", ";
	            } 	
                //$emails .= "{$email}'";    
                $emails .= "<a href='mailto:{$email}'>{$email}</a>";    
			}
        }
			
		return $emails;	
	}
	
*/
    //can be used to check for duplicates on member creation, or verify email and member_id combo for password reset
	public function VerifyMemberExists() {
		global $cDB;
        $condition = "m.member_id=\"{$this->getMemberId()}\"";
        //matching email and if NOT inactive
        if(!empty($this->getPerson()->getEmail())){
            $condition .= " AND p.email=\"{$this->getPerson()->getEmail()}\"";
            $condition .= " AND NOT m.status=\"I\"";
        }
        $condition .= " ORDER BY member_id ASC LIMIT 1";
        return $this->Load($condition);
	} 
    /*
        // ct not using Pear.  and replaced the generation of passwords with password reset link.

    // todo: wehen user logs in for the first time, redirect to change password.
    // this is used on password reset and new user flows
    public function GeneratePassword() {  
        //CT This is at least actually random
        $length = "8";
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = "";
        $max = mb_strlen($keyspace, '8bit') - 1;
        if ($max < 1) {
            throw new Exception('$keyspace must be at least two characters long');
        }
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    */

    public function recoverPassword() {
            //global $cStatusMessage;
            // if you've got here, so far so good - no errors found
            if (!$this->VerifyMemberExists()) return false;
            //$this
            $is_success = $this->getPasswordReset()->recoverPassword($this);
            //print_r($is_success);
            return $is_success;
        }




	
	public function makeMemberLink($text=null) {
        global $p;
        if (empty($text)) $text = "#" . $this->getMemberId(); //pass in name, or use member number if not there
        $link = "member_detail.php?member_id=". $this->getMemberId();
		return $p->Link($text, $link);
	}
	
	
	//CT todo - put this somewhere for reuse..
	public function FormatLabelValue($label, $value){
		return "<p class='line'><span class='label'>{$label}: </span><span class='value'>{$value}</span></p>";
	}



    public function MakeJointMemberArray() {
        global $cDB;
        
        $names = array();
        foreach ($this->person as $person) {
            if($person->primary_member != 'Y') {
                $names[$person->person_id] = $person->first_name ." ". $person->last_name;
                }
        }
        
        return $names;  
    }   
    public function getPerson()
    {
        //return $this->primary_person;
        return $this->person;
    }

    /**
     * @param mixed $person
     *
     * @return self
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }
        /**
     * @return mixed
     */
    public function getJointPerson()
    {
        return $this->joint_person;
    }

    /**
     * @param mixed $joint_person
     *
     * @return self
     */
    public function setJointPerson($joint_person)
    {
        $this->joint_person = $joint_person;

        return $this;
    }


		
	/*
	public function DaysSinceLastTrade() {
		global $cDB;
	
		$query = $cDB->Query("SELECT max(trade_date) FROM ". DATABASE_TRADES ." WHERE member_id_to=". $cDB->EscTxt($this->member_id) ." OR member_id_from=". $cDB->EscTxt($this->member_id) .";");
		
		$row = $cDB->FetchArray($query);
		
		if($row[0] != "")
			$last_trade = new cDateTime($row[0]);
		else
			$last_trade = new cDateTime($this->join_date);

		return $last_trade->DaysAgo();
	}*/
	
	public function DaysSinceUpdatedListing() {
		global $cDB;
	
		$query = $cDB->Query("SELECT max(listing_date) FROM ". DATABASE_LISTINGS ." WHERE member_id=". $cDB->EscTxt($this->member_id) .";");
		
		$row = $cDB->FetchArray($query);
		
		if($row[0] != "")
			$last_update = new cDateTime($row[0]);
		else
			$last_update = new cDateTime($this->join_date);

		return $last_update->DaysAgo();
    }

    public function greetMe($name){
        $greeting_array = array();
        $greeting_array[]=array("ar-AE", "Arabic", "اهلا (Ahlan) {$name}!");
        $greeting_array[]=array("cy-GB", "Welsh", "Hylô, {$name}!");
        $greeting_array[]=array("da-DK", "Danish", "Halløj {$name}!");
        $greeting_array[]=array("de-DE", "German", "Hallo {$name}!");
        $greeting_array[]=array("el-GR", "Greek", "Γεια σου (Yassou) {$name}!");
        $greeting_array[]=array("en-AU", "Australian", "G’day Mate!");
        $greeting_array[]=array("en-GB", "British English", "Hiya {$name}");
        $greeting_array[]=array("es-ES", "Spanish", "¿Qué tal {$name}?");
        $greeting_array[]=array("fa-IR", "Farsi", "درود (Salaam) {$name}!");
        $greeting_array[]=array("fr-FR", "French", "Salut {$name}!");
        $greeting_array[]=array("he-IL", "Hebrew", "שָׁלוֹם (Shalom) {$name}!");
        $greeting_array[]=array("hi-IN", "Hindi", "Namaste {$name}!");
        $greeting_array[]=array("it-IT", "Italian", "Ciao {$name}!");
        $greeting_array[]=array("ja-JP", "Japanese", "今日は (Konnichiwa) {$name}!");
        $greeting_array[]=array("ko-KR", "Korean", "안녕 (Anyoung), {$name}!");
        $greeting_array[]=array("nb-NO", "Norwegian", "Hei, {$name}!");
        $greeting_array[]=array("nl-NL", "Dutch", "Goedendag {$name}");
        $greeting_array[]=array("pl-PL", "Polish", "Cześć {$name}!");
        $greeting_array[]=array("pt-PT", "Portuguese", "Oi {$name}!");
        $greeting_array[]=array("ru-RU", "Russian", "Privet {$name}!");
        $greeting_array[]=array("se-SE", "Swedish", "Tjena {$name}!");
        $greeting_array[]=array("sw-KE", "Swahili", "Habari {$name}!");
        $greeting_array[]=array("ta-IN", "Tamil", "Selam {$name}!");
        $greeting_array[]=array("uk-UK", "Ukranian", "Dobriy den, {$name}!");
        $greeting_array[]=array("xh-ZA", "Xhosa (South Africa)", "Molo {$name}!");

        $greeting_array[]=array("zh-CN", "Mandarin Chinese", "Nǐ hǎo {$name}!");
        $n = rand(0,sizeof($greeting_array)-1);
        return "<h3>" . $greeting_array[$n][2] . " <em style='font-weight:normal; font-size:0.8em'>A greeting in {$greeting_array[$n][1]}.</em></h3>";
    }


    /**
     * @return mixed
     */
    public function getMemberId()
    {
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
    public function getIsNested()
    {
        return $this->is_nested;
    }

    /**
     * @param mixed $is_nested
     *
     * @return self
     */
    public function setIsNested($is_nested)
    {
        $this->is_nested = $is_nested;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     *
     * @return self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMemberRole()
    {
        return $this->member_role;
    }

    /**
     * @param mixed $member_role
     *
     * @return self
     */
    public function setMemberRole($member_role)
    {
        $this->member_role = $member_role;

        return $this;
    }

    // /**
    //  * @return mixed
    //  */
    // public function getSecurityQ()
    // {
    //     return $this->security_q;
    // }

    // /**
    //  * @param mixed $security_q
    //  *
    //  * @return self
    //  */
    // public function setSecurityQ($security_q)
    // {
    //     $this->security_q = $security_q;

    //     return $this;
    // }

    // /**
    //  * @return mixed
    //  */
    // public function getSecurityA()
    // {
    //     return $this->security_a;
    // }

    // /**
    //  * @param mixed $security_a
    //  *
    //  * @return self
    //  */
    // public function setSecurityA($security_a)
    // {
    //     $this->security_a = $security_a;

    //     return $this;
    // }

    
    
    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     *
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMemberNote()
    {
        return $this->member_note;
    }

    /**
     * @param mixed $member_note
     *
     * @return self
     */
    public function setMemberNote($member_note)
    {
        $this->member_note = $member_note;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminNote()
    {
        return $this->admin_note;
    }

    /**
     * @param mixed $admin_note
     *
     * @return self
     */
    public function setAdminNote($admin_note)
    {
        $this->admin_note = $admin_note;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getJoinDate()
    {
        return $this->join_date;
    }

    /**
     * @param mixed $join_date
     *
     * @return self
     */
    public function setJoinDate($join_date)
    {
        $this->join_date = $join_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpireDate()
    {
        return $this->expire_date;
    }

    /**
     * @param mixed $expire_date
     *
     * @return self
     */
    public function setExpireDate($expire_date)
    {
        $this->expire_date = $expire_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAwayDate()
    {
        return $this->away_date;
    }

    /**
     * @param mixed $away_date
     *
     * @return self
     */
    public function setAwayDate($away_date)
    {
        $this->away_date = $away_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccountType()
    {
        return $this->account_type;
    }

    /**
     * @param mixed $account_type
     *
     * @return self
     */
    public function setAccountType($account_type)
    {
        $this->account_type = $account_type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmailUpdates()
    {
        return $this->email_updates;
    }

    /**
     * @param mixed $email_updates
     *
     * @return self
     */
    public function setEmailUpdates($email_updates)
    {
        $this->email_updates = $email_updates;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param mixed $balance
     *
     * @return self
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfirmPayments()
    {
        return $this->confirm_payments;
    }

    /**
     * @param mixed $confirm_payments
     *
     * @return self
     */
    public function setConfirmPayments($confirm_payments)
    {
        $this->confirm_payments = $confirm_payments;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRestriction()
    {   
        return $this->restriction;
    }

    /**
     * @param mixed $restriction
     *
     * @return self
     */
    public function setRestriction($restriction)
    {
        
        //$this->restriction = 1;
       $this->restriction = $restriction;

        return $this;
    }

   /**
     * @return mixed
     */
    public function getOptInList()
    {
        return $this->opt_in_list;
    }

    /**
     * @param mixed $opt_in_list
     *
     * @return self
     */
    public function setOptInList($opt_in_list)
    {
        $this->opt_in_list = $opt_in_list;

        return $this;
    }



    /**
     * @return mixed
     */
    public function getPasswordReset()
    {
        return $this->password_reset;
    }


    /**
     * @param mixed $password_reset
     *
     * @return self
     */
    public function setPasswordReset($password_reset)
    {
        $this->password_reset = $password_reset;

        return $this;
    }

 
}

?>
