<?php

//CT  load what you need of current user
//CT password stuff should be in this an nowhere else
class cMemberSelf extends cMember {
    private $auth_change_password; //CT wip...if set, change password is possible. 
    private $login_history; //CT admin mode. unlocks extra functionality in the site - plus logging. 
    private $mode; //CT admin mode. unlocks extra functionality in the site - plus logging. 
    private $expires; //CT timeout for the session. should always be a day. renew whenever there is an interaction. TODO 

 public function  __construct ($field_array=null) {
        //CT instantiate the people! Like a GOD!
        //CT password_reset - for token flow. stays empty most of the time.
        $login_history = new cLoginHistory();
        $this->setLoginHistory($login_history);
        //CT do the do with the array
        parent::__construct($field_array);
    }    
    public function LoginFromCookie()
    {
/*
        if (isset($_COOKIE["login"]) && isset($_COOKIE["pass"]))
        {
            $this->Login($_COOKIE["login"], $_COOKIE["pass"], true);
        }
*/
        return false;
    }
    //CT returns a new time n minutes from current set time.
    private function getNewSessionTimeout($old_timestamp=null){
        //CT would prefer to use date 'Y-m-d H:i:s' but timestmps are easier to work with right now
        global $site_settings;
        $timeout = round($site_settings->getKey("SESSION_TIMEOUT"));
        //Can't be less than 1, or more than 24.
        if($timeout<1) $timeout = 1;
        elseif($timeout>24) $timeout = 24;        //CT if there is already an established session

        $timestamp = strtotime("now");


        $new_timestamp = strtotime(" + {$timeout} minute");


        if($old_timestamp !=null && (($timestamp > $old_timestamp) OR ($new_timestamp < $old_timestamp))){
                return false; //CT return false if past expiry, or the the old expiry set is earlier than the new expiry (indicating that the timeout has been set - and all sessions should respect the new expiry not the old one)
                   
        }
        // otherwise...return the new timeout
        return $new_timestamp;  //return the new date_string
    }
    public function IsLoggedOn()
    {
        global $cStatusMessage;
//      if (isset($_SESSION["user_login"]) and $_SESSION["user_login"] != LOGGED_OUT)
        //CT timeout should be 15 mins;
        //print_r($_SESSION);
        //if(empty($_SESSION["timeout_at"])) return false;
        if (isset($_SESSION["user_login"])) {
            $timeout_at=$this->getNewSessionTimeout($_SESSION["timeout_at"]);
            //print_r($timeout_at);
            if(!$timeout_at) return false;
            //CT refreshes timeout
            $_SESSION["timeout_at"] = $timeout_at;
            //if (DEBUG) $cStatusMessage->Info("Session: {$_SESSION['timeout_at']}");

            //print_r($_SESSION["timeout_at"]);
            return true;
        }
        return false;
    }

    //TODO: need everyone to change password
    //but it will be a struggle
    /* 
        phase 1
        - login in, if old sha, resave password with new crypt. DONE
        - start logging member actions - password reset. distinguish between system and user chosen passwords

        phase 2
        - replace system passwords - send a link for user set passowrd. 

        phase 3
        - prompt users to change passwords
    
    */

    //c

    public function Login($member_id, $password, $from_cookie=false) {
        global $cDB, $cStatusMessage;
        //stash the member_id in object for later
        $error_string ="";
        if (empty($member_id)) $error_string .= "Member ID required. ";
        if (empty($password)) $error_string .= "Password required.";

        if (!empty($error_string)) throw new Exception($error_string);

        $this->setMemberId($member_id);
 
        //TRY new hash encryption for password - it's php side so must be got first
        $query = $cDB->Query("SELECT 
            password as password_hash 
            FROM `".DATABASE_MEMBERS."` m
            WHERE 
            m.member_id = \"{$member_id}\" AND m.status !=\"I\"");
        //print_r($query);
        if ($row = $cDB->FetchArray($query)) {
            $password_hash = $row['password_hash'];
        } else{
            throw new Exception("Wrong member ID or password, or your account is locked. After a number of attempts you may have to reset your password.");
        }
           //if matches, all good
        $is_success = password_verify($password, $password_hash);

        //print($is_success . " / pw: " . $password . " / pw_h: " . $password_hash);


        if(!$is_success) {
            //if that fails, fallback to old, but rewrite password to hash
             $query = $cDB->Query("SELECT 
            m.member_id as member_id
            FROM ".DATABASE_MEMBERS." m
            WHERE 
            member_id = '{$cDB->EscTxt($this->getMemberId())}' 
            AND status =\"A\"
            AND (password=sha('{$cDB->EscTxt($password)}') OR password='{$cDB->EscTxt($password)}') LIMIT 1;");
            //make sure the record is converted
            if ($row = $cDB->FetchArray($query)) {
                $is_success = true;
                //set authorization var to set password
                $this->setAuthChangePassword(true);
                //CT evoke new change password to write it properly in the db for nexst time
                $is_written = $this->ChangePassword($password);
                if(!$is_written) $cStatusMessage->Error("Oops, something went wrong.");
            }
        }
        //  login
        
        //$login_history = new cLoginHistory;
        //if user found and has a valid passwor
        if ($is_success) {
            //if there is no last login, there is no record
            
            $_SESSION["timeout_at"] = $this->getNewSessionTimeout(null);


            $_SESSION["user_login"] = $this->getMemberId();
            $_SESSION["expires"] = $this->getExpires();
            $_SESSION["mode"] = "default";

            $this->refreshSession();

            //CT wip - new mode var to store in session
            $this->changeMode();
            $this->getLoginHistory()->RecordLoginSuccess($member_id);
            //DEBUG
            if (DEBUG) $cStatusMessage->Info("New session: " . print_r($_SESSION, 1));

            return true;
        } 
            
        $consecutive_failures = $this->getLoginHistory()->RecordLoginFailure($member_id);
               $threshold = 6;
        $moretogo=$threshold-$consecutive_failures;
        if($moretogo > 0){

            $cStatusMessage->Error("Wrong member ID or password, or your account is locked. After a number of attempts you may have to reset your password.");
        } else{
            $this->Lock();
        }

        return false;    
    }
    //CT wip. admins are members too. turn off admin controls when not needed - so they have the same experience of a normal member, except when they need it. A bit like "SUDO"...
    public function changeMode($mode="default"){
        global $cUser;
        if($this->getMemberRole()<1) $mode="default";
        if($mode != "admin" && $this->getMemberRole()<1){
            //fallback to the default mode for the user
            $mode = ($cUser->IsLoggedOn()) ? "member" : "guest";
        }

        //print_r($mode);
        $this->setMode($mode);
        $_SESSION["mode"] = $mode;
       // print_r($_SESSION);
    }
    //refresh session from member object - only do on dashboard page
    public function refreshSession(){

        $_SESSION["user_role"] = $this->getMemberRole();
        $_SESSION["user_restriction"] = $this->getRestriction();
        $_SESSION["user_balance"] = $this->getBalance();
        $_SESSION["user_away_date"] = $this->getAwayDate();
        $_SESSION["user_display_name"] = $this->getDisplayName();
        //echo($this->getBalance());
        return true;
    }
    public function Lock(){
        global $cStatusMessage, $cDB;
        $status = "L";
        $this->setStatus($status);
        $field_array=array();
        $field_array['status'] = $status;
        //only allow locking of active accounts...so inactive members cant just gain access again!
        $condition = "member_id=\"{$this->getMemberId()}\" AND status=\"A\"";
        $string_query = $cDB->BuildUpdateQuery(DATABASE_MEMBERS, $field_array, $condition);
        $is_success = $cDB->Query($string_query);
        if($is_success) {
            $cStatusMessage->Error("Your account has been locked.");
            //send email
            if($this->recoverPassword()){
                $cStatusMessage->Error("Check your email for instructions on how to change your password.");
            }else{
                $cStatusMessage->Error("Contact the admin and report error code 112.");
            }
        }

        
    }
    // public function ValidatePassword($pass) {
    //     global $cDB;
    //     $query = $cDB->Query("SELECT member_id, member_role 
    //         FROM ".DATABASE_MEMBERS." WHERE member_id = ". $cDB->EscTxt($this->member_id) ." 
    //         AND (password=sha({$cDB->EscTxt($pass)}) OR password={$cDB->EscTxt($pass)});");  
        
    //     return (empty($cDB->FetchArray($query))) ? true : false;
    // }


    /*
        public function DoLoginStuff($member_id)
        {
            global $cDB;
            //setcookie("login",$user,time()+60*60*24*1,"/");
            //setcookie("pass",$pass,time()+60*60*24*1,"/");

            $this->LoadMember($member_id);
            $_SESSION["user_login"] = $member_id;
        }
    */
    public function SendPasswordLink(){

    }
    public function UserLoginPage() // A free-standing login page
    {
        global $p;
        $string_query = file_get_contents(TEMPLATES_PATH . '/form_login.php', TRUE);
        return $p->ReplacePlaceholders($string_query);
    }

    public function verifyToken($token){
        global $cDB;
        $is_valid = false;
        if($token && $this->getMemberId()){
            //$login_history = new cLoginHistory;
            //$login_history->setMemberId($this->getMemberId());
            //print_r($login_history->getMemberId());
            //pass on token
            $is_valid = $this->getPasswordReset()->verifyToken($token);
            //CT set authority to change password
            if($is_valid) {
                $this->setAuthChangePassword(true);
                //print_r($this->UnLock());
            }
            return $is_valid;
        }
    }
    //CT updated to use better crypt function
    public function ChangePassword($pass) { 
        global $cDB, $cStatusMessage;
        // TODO: Should use SaveMember and should reset $this->password
        // CT can't do this as the save function is now protected in own memberEdit class and password not stored in object.
        $is_success = false;

        if (!empty($this->getAuthChangePassword())){
            $fieldArray = array();
            $fieldArray['password'] = password_hash($pass, PASSWORD_DEFAULT);
            //CT unlock the user
            $fieldArray['status'] = "A";
            $condition = "member_id=\"{$cDB->EscTxt($this->getMemberId())}\"";

//$table_name, $array, $condition
            $query_string = $cDB->BuildUpdateQuery(DATABASE_MEMBERS, $fieldArray, $condition);
            $is_success = $cDB->Query($query_string);
            
            //after done, reset auth to false
            $this->setAuthChangePassword(false);
        }
        
        return $is_success;
    }


    protected function GeneratePassword() {  
        return Text_Password::create(8) . chr(rand(50,57));
    }

    public function MustBeLoggedOn()
    {
        global $p, $cStatusMessage;
        
        if ($this->IsLoggedOn()){
            return true;
        }
        
        // user isn't logged on, but is in a section of the site where they should be logged on.
        $_SESSION['request_uri'] = $_SERVER['REQUEST_URI'];
        $cStatusMessage->SaveMessages();
        header("location:" . HTTP_BASE . "/login_redirect.php");
                
        exit;
    }


    public function Logout() {
        setcookie(session_name(), session_id(), time() - 42000, '/');
        $_SESSION = array();
        session_destroy();

    }

    //CT replaced
    // public function MustBeLevel($level) {
    //     global $p;
    //     $this->MustBeLoggedOn(); // seems prudent to check first.

    //     if ($this->getMemberRole()<$level)
    //     {
    //         $page = "<p class='AccessDenied'>You don't have permissions for this action.</p>";
    //         $p->DisplayPage($page);
    //         exit;

    //     }
    // }
    //CT a replacement to the function above - as admin mode should be explicitly entered into - like SUPER USER mode for unix - even if you are an admin. Safety first!
    public function MustBeLevel($level){
        global $p, $cStatusMessage;
        $this->MustBeLoggedOn();
        if($this->getMode() != "admin"){
            $cStatusMessage->Error('You do not have permission to view this page.');
            $output = "";
            if(!$this->getMemberRole() < $level) {
                $request_uri = $_SERVER['REQUEST_URI'];
                //your add_querystring_var() returns the new url, it doesn't echo it to the screen
                $request_uri= $p->add_querystring_var($request_uri,"mode","admin");

                $output .= "<a href=\"" . $request_uri . "\" class=\"button large\"><i class=\"fas fa-lock\"></i>Turn on admin mode</a>";
            }
            $p->DisplayPage($output);
            exit;
        }
    }
 
     

    public function RegisterWebUser()
    {   
        $field_array = array();
        if (isset($_SESSION["user_login"])) $field_array['member_id'] = $_SESSION["user_login"];
        if (isset($_SESSION["user_balance"])) $field_array['balance'] = $_SESSION["user_balance"];
        if (isset($_SESSION["user_role"])) $field_array['member_role'] = $_SESSION["user_role"];
        if (isset($_SESSION["restriction"])) $field_array['restriction'] = $_SESSION["restriction"];
        if (isset($_SESSION["user_away_date"])) $field_array['away_date'] = $_SESSION["user_away_date"];
        if (isset($_SESSION["user_display_name"])) $field_array['display_name'] = $_SESSION["user_display_name"];
        //CT todo - put this in
        if (isset($_SESSION["expires"])) $field_array['expires'] = $_SESSION["expires"];
        if (isset($_SESSION["mode"])) $field_array['mode'] = $_SESSION["mode"];
        if (isset($_SESSION["timeout_at"])) $field_array['timeout_at'] = $_SESSION["timeout_at"];
        if (isset($_SESSION["request_uri"])) $field_array['request_uri'] = $_SESSION["request_uri"];

        //print_r($_SESSION);
        if(sizeof($field_array)>1){
            $this->Build($field_array);
            // Session regeneration added to boost server-side security.
            session_regenerate_id();
        } else {
            //do something
            //$this->LoginFromCookie();
        } 
            
    }


    /**
     * @return mixed
     */
    protected function getAuthChangePassword()
    {
        //CT this is used for setting authority to be able to change password. always 2 step process.
        return $this->auth_change_password;
    }

    /**
     * @param mixed $auth_change_password
     *
     * @return self
     */
    protected function setAuthChangePassword($auth_change_password)
    {
        $this->auth_change_password = $auth_change_password;

        return $this;
    }

    //CT TODO - modes switch between admin and member.
    public function getMode(){
        return $this->mode;
    }

    public function setMode($mode){
        
        $this->mode = $mode;
//print_r($mode);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param mixed $expires
     *
     * @return self
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLoginHistory()
    {
        return $this->login_history;
    }

    /**
     * @param mixed $login_history
     *
     * @return self
     */
    public function setLoginHistory($login_history)
    {
        $this->login_history = $login_history;

        return $this;
    }
}
?>