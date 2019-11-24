<?php

class cMemberUtils extends cMember {
    
    //var $mode;
    private $action; //CT this is the switch to allwo certain behaviours 


   // public function  __construct ($field_array=null) {
   //      //CT call the parent first
   //      parent::__construct($field_array);
   //      //CT overwrite the people with the people that do stuff
   //      $person = new cPersonUtils();
   //      $person1 = new cPersonUtils();
   //      $this->setPerson($person);
   //      $this->setJointPerson($person1);
   //  }
    //CT create or save


    public function sendWelcomeEmail(){
        global $cUser, $cStatusMessage, $site_settings;

        if(!$cUser->getMemberRole() > 0) return false;
        

            $message_array = array();
            $message_array['subject'] = "Welcome to our community!";
            $message_array['message'] = "
                <p>Welcome to {$site_settings->getKey('SITE_LONG_TITLE')}!</p>
                <p>An account has been created for you. Take note of your unique member ID, as you will need this when you log in and when you communicate to other members for trading.</p> Before you can log in, you must create a password.</p>
                <ul>
                    <li>Member ID: {$this->getMemberId()}</li>
                    <li>Website: <a href=\"". HTTP_BASE ."?logout=true\">" . HTTP_BASE ."</a></li>
                </ul>
                <p>IMPORTANT: Before you can start using your account, you need to set a new password.</p>
                <p><font size=\"4\"><a href=\"". HTTP_BASE ."/password_reset.php?member_id=" . urlencode($this->getMemberId()) . "&email=" . urlencode($this->getPerson()->getEmail()) . "&logout=true\">Set a password now</a></font>.</p>
                <p>Thank you for joining us, and we look forward to seeing you at the next trade meeting.</p><p>Need help? You can reply to this email or contact the site admin at any time.</p><p>Happy trading!</p>";
            //CT

            $mailer = new cMail($message_array);
            //CT a bit awkward, but set recipients after object already instantiated
            //$mailer->buildRecipientsFromMemberObject($this);
            //scope
            $mailer->buildRecipientsFromMemberObject($this, "primary");
            //CT should be try catch
            $is_success=$mailer->sendMail(LOG_SEND_WELCOME);
        //}else{
        //    throw new Exception('Could not send email.');   
        //}
        return $is_success;
    
    }
    //CT bit like a factory - returns new person object. rerouting opportunity for extend classes
    public function makePerson($field_array=null){
        return new cPersonUtils($field_array);
    }
        //includes the category making gubbins
    public function PrepareRestrictionDropdown(){
        global $p, $cUser;
        $vars = array("0" => "No restriction", "1" => "Restriction");
        // add extra option if user is an admin 
        $select_name = "restriction";
        //if used in context of batch page controls
        //if(!empty($page_id)) $select_name .= "_{$page_id}";
        $output = $p->PrepareFormSelector($select_name, $vars, null, $this->getRestriction());
        return $output;
    }

    public function Save(){
        
        global $cDB, $cUser, $cStatusMessage, $site_settings; 
        // if($cUser->getMemberId() != $this->getMemberId()) {
        //     //CT hardstop if user not authorized
        //     $cUser->MustBeLevel(1);
        // }
 
        //$cStatusMessage->Error("save data");    
        //Rejigged for safety
        $keys_array = array();

        //only allow committee and up to make Could not execute queryto these fields
        //$admin 
        //CT used for update actions
        $condition = "member_id=\"{$cDB->EscTxt($this->getMemberId())}\"";      

        if($this->getAction() == "create" OR $this->getAction() == "update"){
            if (null !=($this->getEmailUpdates())) $keys_array[] = 'email_updates';
            if (null !=($this->getConfirmPayments())) $keys_array[] = 'confirm_payments';
            if(null !=($this->getAccountType())) $keys_array[] = 'account_type'; //CT non-admins can only change from single to joint or back again

            if((($cUser->getMemberRole() > 0 AND !($site_settings->getKey('USER_MODE'))) OR ($site_settings->getKey('USER_MODE') && $cUser->getMode() == USER_MODE_ADMIN)) && !empty($_REQUEST['member_id'])){
                if(null !=($this->getMemberId())) $keys_array[] = 'member_id';
                if(null !=($this->getMemberRole())) $keys_array[] = 'member_role';
                if(null !=($this->getAccountType())) $keys_array[] = 'account_type';
                if(null !=($this->getAdminNote())) $keys_array[] = 'admin_note';
                if(null !=($this->getJoinDate())) $keys_array[] = 'join_date';
                if(null !=($this->getExpireDate())) $keys_array[] = 'expire_date';
                if(null !=($this->getRestriction())) $keys_array[] = 'restriction';
                if(null !=($this->getStatus())) $keys_array[] = 'status';
            //$admin_action=true;
            }
        }
        
        try{
            switch($this->getAction()){
                case "create":
                    $cUser->MustBeLevel(1);
                    //TODO -
                    //make sure status=L, primary member=Y are all set before get to this stage
                    //$keys_array=array();
                    //$keys_array[] = 'member_id';
                    //$keys_array[] = 'primary_member';
                   // $keys_array[] = 'status';

                    //temporary password - user should reset when they log in
                    // $password = $this->GeneratePassword();
                    // $field_array["password"] =  password_hash($password, PASSWORD_DEFAULT);
                    //CT cant do anything with password here anymore - security. only user themselves can change their password.
                    $member_id = $this->insert(DATABASE_MEMBERS, $keys_array);
                    $this->setMemberId($member_id);
                    //CT sets default password
                    $this->DefaultPassworForNewMember();
                break; 
                case "update":
                    // print_r($keys_array);
                    //try{
                    $is_success = $this->update(DATABASE_MEMBERS, $keys_array, $condition);
                    
                    //}catch (Exception $e){
                    //} 
                break;
                case "change_status":
                    $cUser->MustBeLevel(1);
                    $keys_array[] = 'status';
                    $is_success = $this->update(DATABASE_MEMBERS, $keys_array, $condition); 
                break;
                case "archive":
                    $cUser->MustBeLevel(1);
                        if($this->getMemberId() == $site_settings->getKey('SITE_MEMBER_ID')) throw new Exception("You cannot archive permanently the central site account.", 1);
                            $this->setAdminNote("");
                            $keys_array[] = 'admin_note';
                            $is_success = $this->update(DATABASE_MEMBERS, $keys_array, $condition); 

                        if($this->getBalance() != 0){
                             $trade_field_array = $this->makeTradeArrayForBalanceTransfer();
                            //CT commit trade
                            $trade = new cTradeUtils($trade_field_array);
                            $is_success = $trade->ProcessData($trade_field_array);
                        }else{
                            $is_success = true;
                        }
                break;
                case "joint_create":
                    //make sure status=L, primary member=Y are all set before get to this stage
                    //$keys_array[] = 'member_id';
                    $this->setAccountType("J");
                    $keys_array[] = 'account_type';
                    $is_success = $this->update(DATABASE_MEMBERS, $keys_array, $condition);

                break;
                case "joint_delete":
                    $this->setAccountType("S");
                    $keys_array[] = 'account_type';
                    $is_success = $this->update(DATABASE_MEMBERS, $keys_array, $condition);

                break; 

                case "joint_update":
                    //no change for member - so just behave as if it was successful
                    $is_success = true;
                break;
            }
        } catch (Exception $e){
                $cStatusMessage->Error("{$this->getAction()}: " . $e->getMessage());
        } 
        //CT return at this stage if something went wrong...
        if(!$is_success) {
            return false;
        }
        try{
            //CT so far so good - member table changes done. Now do the person table changes
            $this->getPerson()->setMemberId($this->getMemberId());
            switch($this->getAction()){
                case "create":
                case "update":
                    $this->getPerson()->setAction($this->getAction());
                    $is_success = $this->getPerson()->Save();
                break;
                case "change_status":
                    //CT do nothing - all good.
                break;
                case "archive":
                    //CT blank all PII fields
                    $field_array = array(
                        'member_id'=>$this->getMemberId(),
                        'person_id'=>$this->getPerson()->getPersonId(),
                        'first_name'=>substr($this->getPerson()->getFirstName(), 0, 1) . "******",
                        'last_name'=>substr($this->getPerson()->getLastName(), 0, 1) . "******",
                        'primary_member'=>"Y",
                        'about_me'=>"The record of this member was archived on " . date('Y-m-d') . " by an admin."
                    );
                
                    $person = $this->makePerson($field_array);
                    $this->setPerson($person);
                    $this->getPerson()->Save();
                    if(!empty($this->getJointPerson()->getPersonId())){
                        $field_array = array(
                            'first_name'=>"Archived",
                            'last_name'=>"Member",
                            'member_id'=>$this->getMemberId(),
                            'person_id'=>$this->getJointPerson()->getPersonId(),
                            'primary_member'=>"N"
                        );
                        $person1 = $this->makePerson($field_array);
                        $this->setJointPerson($person1);
                        $this->getJointPerson()->Save();
                    }

                break;
                case "joint_create":
                    $this->getJointPerson()->setAction('create');
                    $field_array["primary_member"]="N";
                    $field_array["account_type"]="J";
                    $is_success = $this->getJointPerson()->Save();
                break;
                case "joint_update":
                    $this->getJointPerson()->setAction('update');
                    $is_success = $this->getJointPerson()->Save();
                break;
                case "joint_delete":
                    $is_success = $this->getJointPerson()->DeleteJointPerson();
                break;              
            }
        } catch (Exception $e){
            $cStatusMessage->Error("Create/update person: " . $e->getMessage());
        } 
        
        return $is_success;
    }

    public function makeTradeArrayForBalanceTransfer(){

        global $site_settings;
        $trade_field_array = array();
        if($this->getBalance() > 0){
            $trade_field_array["member_id_to"]=$site_settings->getKey("SITE_MEMBER_ID"); 
            $trade_field_array["member_id_from"]=$this->getMemberId(); 

        }elseif($this->getBalance() < 0){
            $trade_field_array["member_id_from"]=$site_settings->getKey("SITE_MEMBER_ID"); 
            $trade_field_array["member_id_to"]=$this->getMemberId(); 
        }      
        else{
            return false;
        }
    //CT only do donation if more than 1 unit - safety for recursion error
        $trade_field_array["action"]="create"; 
        $trade_field_array["member_id_author"]="system"; //automatic 
        //$field_array["status"]=$this->getStatus(); 
        $trade_field_array["amount"]=abs($this->getBalance()); //make sure the amount is an absoulte number...ie not negative
        $trade_field_array["category_id"]="43"; //system business 
        $trade_field_array["description"]="Transfer of " . UNITS . " balance of {$this->getMemberId()} on archive of account."; 
        $trade_field_array["type"]=TRADE_TYPE_TRANSFER; 
        return $trade_field_array;      
    }
    public function DefaultPassworForNewMember(){
        global $cDB;
        //CT this puts rubbish in the password field so no one can log in without going through password reset process. Needed for when you are creating an account, and don't want to expose pw to anyone but the user.
        if($this->getAction()!="create") return false;
        $temp_token=bin2hex(random_bytes(50));
        $string_query = "UPDATE " . DATABASE_MEMBERS . " SET `password`=\"{$temp_token}\" WHERE member_id=\"{$this->getMemberId()}\"";
        //returns confirmation its done or not
        return $cDB->Query($string_query);
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
        //CT put on person too...
        $this->getPerson()->setAction($action);
        $this->getJointPerson()->setAction($action);
        return $this;
    }
}

?>