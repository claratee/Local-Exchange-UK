<?php

class cMemberUtils extends cMember {
    
    //var $mode;
    private $action;


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

        if($cUser->getMode()!="admin") return false;
        

            $message_array = array();
            $message_array['subject'] = "Welcome to {$site_settings->getKey('SITE_SHORT_TITLE')}!";
            $message_array['message'] = "<p>Welcome to {$site_settings->getKey('SITE_LONG_TITLE')}!!</p><p>Your unique member ID is <b>{$this->getMemberId()}</b>. You will need this when you log in and when you communicate to other members for trading.</p><p>An account has been created for you at <a href=\"". HTTP_BASE . "/member_profile_menu.php?member_id={$this->getMemberId()}&logout=true\">" . SERVER_DOMAIN ."</a>. Before you can log in, you must reset your password.</p><p><font size=\"4\"><a href=\"". HTTP_BASE ."/password_reset.php?member_id={$this->getMemberId()}&member_id={$this->getPerson()->getEmail()}&logout=true\">Set a password now</a></font>.</p><p>Thank you for joining us, and we look forward to seeing you at the next trade meeting.</p><p>Need help? You can reply to this email or contact the site admin at any time.</p><p>Happy trading!</p>";
            //CT

            $mailer = new cMail($message_array);
            //CT a bit awkward, but set recipients after object already instantiated
            //$mailer->buildRecipientsFromMemberObject($this);
            //scope
            $mailer->buildRecipientsFromMemberObject($this, "primary");
            //CT should be try catch
            $is_success=$mailer->sendMail();
        //}else{
        //    throw new Exception('Could not send email.');   
        //}
        return $is_success;
    
    }
    //CT bit like a factory - returns new person object. rerouting opportunity for extend classes
    public function makePerson($field_array=null){
        return new cPersonUtils($field_array);
    }
    public function Save(){
        
        global $cDB, $cUser, $cStatusMessage; 
        if($cUser->getMemberId() != $this->getMemberId()) {
            //CT hardstop if user not authorized
            $cUser->MustBeLevel(1);
        }
 
        //$cStatusMessage->Error("save data");    
        //Rejigged for safety
        $keys_array = array();

        //only allow committee and up to make Could not execute queryto these fields
        //$admin 
        if($cUser->getMode()=="admin"){
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
        if (null !=($this->getEmailUpdates())) $keys_array[] = 'email_updates';
        if (null !=($this->getConfirmPayments())) $keys_array[] = 'confirm_payments';
        try{
            switch($this->getAction()){
                case "create":
                    //TODO -
                    //make sure status=L, primary member=Y are all set before get to this stage
                    //$keys_array=array();
                    $keys_array[] = 'member_id';
                    //$keys_array[] = 'primary_member';
                   // $keys_array[] = 'status';

                    //temporary password - user should reset when they log in
                    // $password = $this->GeneratePassword();
                    // $field_array["password"] =  password_hash($password, PASSWORD_DEFAULT);
                    //CT cant do anything with password here anymore - security. only user themselves can change their password.
                    $member_id = $this->insert(DATABASE_MEMBERS, $keys_array);
                    //CT sets default password
                    $this->DefaultPassworForNewMember();
                break; 
                case "update":
                    // print_r($keys_array);
                    $condition = "member_id=\"{$cDB->EscTxt($this->getMemberId())}\"";      
                    //try{
                    $this->update(DATABASE_MEMBERS, $keys_array, $condition);
                    //}catch (Exception $e){
                    //} 
                break;
                case "joint_create":
                    //make sure status=L, primary member=Y are all set before get to this stage
                    $keys_array[] = 'member_id';
                    //$keys_array[] = 'primary_member';
                    //$keys_array[] = 'status';

                    //temporary password - user should reset when they log in
                    // $password = $this->GeneratePassword();
                    // $field_array["password"] =  password_hash($password, PASSWORD_DEFAULT);
                    $is_success = $this->insert(DATABASE_MEMBERS, $keys_array);
                    if(!$is_success) {
                        //report and return on fail
                        $cStatusMessage->Error("Could not create the member '". $this->getMemberId() ."'.");
                        return false;
                    }
                break;            
                case "joint_update":
                    // print_r($keys_array);
                    $condition = "member_id=\"{$cDB->EscTxt($this->getMemberId())}\"";      
                    //try{
                        $this->update(DATABASE_MEMBERS, $keys_array, $condition);
                    //}catch (Exception $e){
                    //    $cStatusMessage->Error("Update member: " . $e->getMessage());
                    //} 
                break;
            }
        } catch (Exception $e){
                $cStatusMessage->Error("{$this->getAction()}: " . $e->getMessage());
        } 
        
        //person
            $member_id = $this->getMemberId();
        try{
            //CT getting desperate - shuldnt have to set this here but not know why its blank
            $this->getPerson()->setMemberId($member_id);
            //print("mbmer " . $this->getPerson()->getMemberId());
            $this->getPerson()->Save();
        }catch (Exception $e){
            $cStatusMessage->Error("Create/update person: " . $e->getMessage());
        } 
        //joint person
        if($this->account_type == "J"){
            try{
                $this->getJointPerson()->setMemberId($member_id);
                $this->getJointPerson()->Save();
            }catch (Exception $e){
                $cStatusMessage->Error("Create/update joint person: " . $e->getMessage());
            } 
        }
        
        return true;
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