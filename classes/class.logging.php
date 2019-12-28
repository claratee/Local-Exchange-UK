<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

class cLogging extends cSingle {
	private $log_id; //not normally set, unless historic
	private $log_date; // not normally set, unless historic
	private $admin_id; // usually a member_id, but not always
	private $category; // See inc.global.php for constants used in this field
	private $action;	// See inc.global.php for constants used in this field
	private $ref_id; // usually refences a trade_id, feedback_id, or similar
	private $note; // ct extra details if needed


	//you can only save new entries
	function Save(){
        $field_array = array();
        $field_array['admin_id'] = $this->getAdminId();
        $field_array['category'] = $this->getCategory();
        $field_array['action'] = $this->getAction();
        $field_array['note'] = $this->getNote();
		$log_id = $this->insert(DATABASE_LOGGING, $field_array);
		return $log_id;
	}
        //you can only save new entries

/*
	function __construct ($field_array=null) {
		global $cUser;
		//default to current user
		$this->admin_id = $cUser->getMemberId();
		if(!empty($field_array)) {
			$this->Build($field_array);
			//$this->Save();
		}
	}


	function Build($field_array){
		if(!empty($field_array['category'])) $this->category = $field_array['category'];
		if(!empty($field_array['action'])) $this->action = $field_array['action'];
		if(!empty($field_array['ref_id'])) $this->ref_id = $field_array['ref_id'];
		if(!empty($field_array['note'])) $this->note = $field_array['note'];
		if(!empty($field_array['admin_id'])) $this->admin_id = $field_array['admin_id'];
	}
	
	function Save () {
		global $cDB, $cUser;
		//CT rewrite
		$field_array = array();
		$field_array['admin_id']= $cDB->EscTxt($this->admin_id);
		$field_array['category']= $cDB->EscTxt($this->category);
		$field_array['action']= $cDB->EscTxt($this->action);
		$field_array['ref_id']= $cDB->EscTxt($this->ref_id);
		$field_array['note']= $cDB->EscTxt($this->note);
		//$fieldArray['log_date']= NOW(); dont need this as its already built in
		
		$string_query = $cDB->BuildInsertQuery(DATABASE_LOGGING, $field_array);
        
        //print_r($string_query);
        if($log_id = $cDB->QueryReturnId($string_query)){
         	return $log_id;
        }else{
         	throw new Exception("Couldnt insert new log");
        	
        }
 
	}
	*/

    /**
     * @return mixed
     */
    public function getLogId()
    {
        return $this->log_id;
    }

    /**
     * @param mixed $log_id
     *
     * @return self
     */
    public function setLogId($log_id)
    {
        $this->log_id = $log_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogDate()
    {
        return $this->log_date;
    }

    /**
     * @param mixed $log_date
     *
     * @return self
     */
    public function setLogDate($log_date)
    {
        $this->log_date = $log_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminId()
    {
        return $this->admin_id;
    }

    /**
     * @param mixed $admin_id
     *
     * @return self
     */
    public function setAdminId($admin_id)
    {
        $this->admin_id = $admin_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     *
     * @return self
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
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

    /**
     * @return mixed
     */
    public function getRefId()
    {
        return $this->ref_id;
    }

    /**
     * @param mixed $ref_id
     *
     * @return self
     */
    public function setRefId($ref_id)
    {
        $this->ref_id = $ref_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param mixed $note
     *
     * @return self
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }
}


?>
