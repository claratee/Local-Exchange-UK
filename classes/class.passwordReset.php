<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

//class cLoginHistory extends cBasic{  
class cPasswordReset extends cBasic2{  
	private $member_id;
    private $password_reset_token; 
    private $password_reset_token_date;
    //private $db_table;
	

	// CT canot use cBasic class as this requires explicit build
	protected function Load ($condition)  {
		global $cDB, $cStatusMessage, $cUser;
		
    	$query_string = "SELECT 
    		`member_id`, 
            `password_reset_token`,
            `password_reset_token_date`
              FROM `". DATABASE_PASSWORD_RESET ."` WHERE $condition";
		$query = $cDB->Query($query_string);

		if($row = $cDB->FetchArray($query)) {       
            $this->Build($row);
            return true; 
        }else{

        }
       
        //continue if select was unsuccessful. 
     //    //CT create new line
    	// //object is empty, but will be populated shortly no worries
    	// $this->setMemberId($member_id);
    	// $field_array=$this->makeFieldArrayFromKeys();	
    	// $string_query = $cDB->BuildInsertQuery(DATABASE_PASSWORD_RESET, $field_array);
     //        //TODO - wirtie insert
     //    $is_success = $cDB->Query($string_query);
     //    return $is_success;
	} 

    
	//CT renamed SaveLoginHistory
	protected function Save(){
        $keys_array = array('member_id', 'password_reset_token');
		$condition = "member_id=\"{$cDB->EscTxt($this->getMemberId())}\"";		
		$this->update($keys_array, $condition);
		return $is_success;
	}
	

	
    // CT find out if the token is valid
    public function verifyToken ($token) {
        $is_valid = false;
        if(null == $this->getPasswordResetToken()) {
            $condition = "member_id = \"{$this->getMemberId()}\" AND password_reset_token = \"{$token}\"";
            //check date within 24 hours also
            $is_valid = $this->Load($condition);
        } else{
            $is_valid = ($this->getPasswordResetToken() == $token);
        }
        if($is_valid){
            $expiry_interval = 172800; //172800 == 48 hours
           //CT so far so good! has it expired? invalida after 48 hours
        //print($_SERVER['REQUEST_TIME'] . "<br />");
        //print_r(strtotime($this->getPasswordResetTokenDate()));
           $is_valid = ($_SERVER['REQUEST_TIME'] - strtotime($this->getPasswordResetTokenDate()) < $expiry_interval);
        }
        return $is_valid;
    }
    //CT only return token when its set. Otherwise it cannot be retrieved. is it ok that this is public??
    protected function generateNewToken(){
        global $cDB;
        if(empty($this->getMemberId())) throw new Exception('No member ID set. Cannot continue');
        $this->setPasswordResetToken(bin2hex(random_bytes(50)));
        $this->setPasswordResetTokenDate(date("Y-m-d H:i:s"));
        //print("hello " . $this->getMemberId());
        $field_array = array();
        $field_array['password_reset_token'] = $this->getPasswordResetToken();      
        $field_array['member_id'] = $this->getMemberId();      
        //$field_array['password_reset_token_date'] = $this->getPasswordChangeTokenDate(); 
        $condition = "member_id = \"{$this->getMemberId()}\"";
        $string_query = $cDB->BuildUpdateQuery(DATABASE_PASSWORD_RESET, $field_array, $condition);
        $is_success = $cDB->Query($string_query); 
        //if uppdate wasnt successful (ie no record found) insert instead
        if($cDB->AffectedRows()<1) {
            $string_query = $cDB->BuildInsertQuery(DATABASE_PASSWORD_RESET, $field_array);
            $is_success = $cDB->Query($string_query); 
        }
        //print_r($is_success);
        return $is_success;
    }
    public function recoverPassword($member) {
        global $cStatusMessage;
        // if you've got here, so far so good - no errors found
        //the reset password stuff is in the user object
        //generate and safe new token

        if($this->generateNewToken()){
//get joint members too. 

            $message_array = array();
            $message_array['subject'] = "ACTION REQUIRED: Password set";
            $message_array['message'] = "<p>Hi,</p><p>Follow the link below to set a password and unlock your account. This link can only be used once, and will expire within 48 hours.</p><p><font size=\"4\"><a href=\"".SERVER_DOMAIN.SERVER_PATH_URL."/password_change.php?member_id={$this->getMemberId()}&token={$this->getPasswordResetToken()}\">Change your password</a></font>.</p>";
            //CT 
            $mailer = new cMail($message_array);
            //CT a bit awkward, but set recipients after object already instantiated
            $mailer->buildRecipientsFromMemberObject($member);
            //CT should be try catch
            $is_success=$mailer->sendMail();
        }else{
            throw new Exception('Could not send email.');   
        }
        return $is_success;
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
    protected function getPasswordResetToken()
    {
        return $this->password_reset_token;
    }

    /**
     * @param mixed $password_reset_token
     *
     * @return self
     */
    protected function setPasswordResetToken($password_reset_token)
    {
        $this->password_reset_token = $password_reset_token;

        return $this;
    }

    /**
     * @return mixed
     */
    protected function getPasswordResetTokenDate()
    {
        return $this->password_reset_token_date;
    }

    /**
     * @param mixed $password_reset_token_date
     *
     * @return self
     */
    protected function setPasswordResetTokenDate($password_reset_token_date)
    {
        $this->password_reset_token_date = $password_reset_token_date;

        return $this;
    }


}
	

?>
