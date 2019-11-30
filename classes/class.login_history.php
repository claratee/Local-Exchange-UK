<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

//class cLoginHistory extends cBasic{  
class cLoginHistory extends cSingle{  
    private $action;
    private $member_id;
	private $total_failed;
	private $consecutive_failures;
	private $login_event_date; // CT never set, just controlled by mysql
	
	// CT canot use cBasic class as this requires explicit build
	function Load($condition)  {
		global $cDB, $cStatusMessage, $cUser;
		
    	$string_query = "SELECT 
    		`member_id`, 
            `total_failed`, 
            `consecutive_failures`
              FROM `".DATABASE_LOGINS . 
              "` WHERE $condition";
        //$string="SELECT `member_id`, `total_failed`, `consecutive_failures`, `login_event_date`, `last_password_change_date` FROM `lets_logins` WHERE member_id='0018'";
		return $this->LoadFromDatabase($string_query);
        //CT create new line
    	//object is empty, but will be populated shortly no worries
    	// $this->setMemberId($member_id);
    	// $field_array=$this->makeFieldArrayFromObject();	
    	// $string_query = $cDB->BuildInsertQuery(DATABASE_LOGINS, $field_array);
     //        //TODO - wirtie insert
     //    $is_success = $cDB->Query($string_query);
     //    return $is_success;
	} 

    
//CT replace this with the one on basic2 class - from keys
	function makeFieldArrayFromObject(){
		$field_array=array();
    	$field_array['member_id'] = $this->getMemberId();
    	$field_array['total_failed'] = $this->getTotalFailed();
        $field_array['consecutive_failures'] = $this->getConsecutiveFailures();
        //CT teh deate changes anyway - no need to set
        //$field_array['login_event_date'] = $this->getLoginEventDate();
    	return $field_array;
	}
	//CT renamed SaveLoginHistory
	function Save() {
        
		global $cDB, $cStatusMessage;

		$field_array=$this->makeFieldArrayFromObject();	
		if($this->getAction() == "update") {
            $condition = "member_id=\"{$cDB->EscTxt($this->getMemberId())}\"";      
            $this->update(DATABASE_LOGINS, $field_array, $condition);
        }else{
            //CT inserts new object where most fields are 0
            $this->insert(DATABASE_LOGINS, $field_array);
        }
	}
	

	function RecordLoginSuccess ($member_id) {
        global $cDB;
        $condition = "`member_id`=\"{$cDB->EscTxt($member_id)}\"";
		if($this->Load($condition)) {
            $this->setAction("update");
		}else{
            $this->setAction("create");
            $this->setTotalFailed(0);

        }
        $this->setMemberId($member_id);
        $this->setConsecutiveFailures(0);
        $this->Save();
	}

    function RecordLoginFailure ($member_id) {
        global $cDB;
        $condition = "`member_id`=\"{$cDB->EscTxt($member_id)}\"";
        if($this->Load($condition)) {
            $this->setAction("update");
            $this->setTotalFailed($this->getTotalFailed()+1);
            $this->setConsecutiveFailures($this->getConsecutiveFailures()+1);
        }else{
            $this->setAction("create");
            $this->setMemberId($member_id);
            $this->setTotalFailed(1);
            $this->setConsecutiveFailures(1);
        }
        $this->Save();
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
        //print("member id is {$member_id}");
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalFailed()
    {
        return $this->total_failed;
    }

    /**
     * @param mixed $total_failed
     *
     * @return self
     */
    public function setTotalFailed($total_failed)
    {
        $this->total_failed = $total_failed;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConsecutiveFailures()
    {
        return $this->consecutive_failures;
    }

    /**
     * @param mixed $consecutive_failures
     *
     * @return self
     */
    public function setConsecutiveFailures($consecutive_failures)
    {
        $this->consecutive_failures = $consecutive_failures;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLoginEventDate()
    {
        return $this->login_event_date;
    }

    /**
     * @param mixed $login_event_date
     *
     * @return self
     */
    public function setLoginEventDate($login_event_date)
    {
        $this->login_event_date = $login_event_date;

        return $this;
    }


    /**
     * @return mixed
     */


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
