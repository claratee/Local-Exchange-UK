<?php
//CT TODO - merge with member group? todo where should this go...system balance
class cBalanceTotal  {
	private $system_balance; //CT should be 0 if system is in balance.
	
	function __construct(){
	    global $cDB;
        $query_string= "SELECT SUM(balance) as balance from ". DATABASE_MEMBERS .";";
        $query = $cDB->Query($query_string);
        if($row = $cDB->FetchArray($query)) {
            //$is_system_balanced = ($row["balance"] == 0) ? true : false;
            $this->setSystemBalance($row["balance"]);
        } else{
        	throw new Exception('Could not get balance.');   
        }
    }
    public function checkBalance(){
		global $site_settings, $cStatusMessage;
    	if($this->getSystemBalance() != 0) {
    		if(DEBUG) $cStatusMessage->Error("Site out of balance by {$this->getSystemBalance()}."); 
    		//if (OOB_EMAIL_ADMIN==true) {

    		if(LOG_LEVEL > 0) {//Log if enabled

	            //      $keys_array = array('admin_id', 'category', 'action', 'ref_id', 'note');
	            $field_array=array();
	            $field_array['category'] = "B";
	            $field_array['action'] = "A";
	            $field_array['ref_id'] = "";
	            $field_array['note'] = "System out of balance";
	            $log_entry = new cLogging ($field_array);
	            $log_entry->Save();
	        }

		                //CT TODO - mail	
			switch($site_settings->getKey("OOB_ACTION")) { // How should we handle the out-of-balance event?
				case("FATAL"): // FATAL: The original method for dealing which is to abort the transaction
				    //CT - lock site from  trades. 
					$site_settings->setKey("OOB_LOCKED",true);
				    //throw new Exception("The trade database is out of balance! This trade cannot be completed.");
			    break;
			    default: 
			    	// SILENT: Just ignore the situation and don't burden the user with warnings/error messages
			}
    	}else{
    		return true;
    	}
    	//CT todo - if site is already locked dont send email
		if ($site_settings->getKey("OOB_EMAIL_ADMIN") == true AND $this->getSystemBalance() != 0) {
			//mail admin 
			
	    		//mail admin 
				$message_array = array();
		        $message_array['subject'] = "System is out of balance";
		        $message_array['message'] = "<p>Hi Admin,</p><p>The system is out of balance by {$this->getSystemBalance()}. This was detected on " . date("Y-m-d H:i:s", time() - date("Z")) . "</p>";
		        $message_array['recipients'][0] = array('display_name'=>"Admin", 'email'=>$site_settings->getKey('EMAIL_ADMIN'));
		        //use this if you want to send to all admins instead
		        //$condition = "member_role=\"2\"";
		        //$mailer->loadRecipients($condition);

		        $mailer = new cMail($message_array);
		        $mailer->sendMail(LOG_SEND_OUT_OF_BALANCE);
				// Admin wishes to receive an email notifying him/her when db is found to be out-of-balance
	    	
			// Admin wishes to receive an email notifying him/her when db is found to be out-of-balance

		}		
    }

    //CT replace with function above
	// public function Balanced() {
	// 	global $cDB, $cStatusMessage;
		
	// 	$query = $cDB->Query("SELECT sum(balance) from ". DATABASE_MEMBERS .";");
		
	// 	if($row = $cDB->FetchArray($query)) {
	// 		$this->balance = $row[0];
			
	// 		if($row[0] == 0)
	// 			return true;
	// 		else
	// 			return false;
	// 	} else {
	// 		$cStatusMessage->Error("Could not query database for balance information. Please try again later.");
	// 		return false;
	// 	}		
	// }

    

    /**
     * @return mixed
     */
    public function getSystemBalance()
    {
        return $this->system_balance;
    }

    /**
     * @param mixed $system_balance
     *
     * @return self
     */
    public function setSystemBalance($system_balance)
    {
        $this->system_balance = $system_balance;

        return $this;
    }
}


?>