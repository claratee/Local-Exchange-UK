<?php
class cIncomeTies extends cSingle {
	// doesnt need access to member object, even uses a dedicated db table. So severing ties!
	    //CT construct and build functions come from parent
    
	private $action;
	private $member_id;
    private $member_id_to; 
    private $percent;
    private $updated_at;


    public function Load($member_id) {
		global $cDB;
		$string_query = "select member_id, member_id_to, percent, updated_at from " . DATABASE_INCOME_TIES ." where member_id=\"{$member_id}\"";
        //CT parent function - load and builds from string query on db
        return $this->LoadFromDatabase($string_query);
        // if($has_income_share = $this->LoadDatabaseTable($string_query)){
        // 	//print_r($has_income_share);
        // 	return $has_income_share;
        // }else{
        // 	return false;
        // }
        

	}


	// public function getTie($member_id) {
		
	// 	global $cDB;
		
	// 	$q = "select * from income_ties where member_id=".$cDB->EscTxt($member_id)." limit 0,1";
	// 	$result = $cDB->Query($q);
		
	// 	if (!$result)
	// 		return false;
		
	// 	$row = $cDB->FetchObject($result);
		
	// 	return $row;
	// }
	
	public function Save() {
		
		global $cDB, $cStatusMessage;

		$field_array = array();
		$field_array['member_id'] = $this->getMemberId();
		$field_array['member_id_to'] = $this->getMemberIdTo();
		$field_array['percent'] = $this->getPercent();

		if($this->getAction() == "update"){
			$condition = "member_id={$this->getMemberId()}";
			
			if($string_query = $cDB->BuildUpdateQuery(DATABASE_INCOME_TIES, $field_array, $condition)){
				//hooray!
			} else{
		 		throw new Exception('Could not build update query.');
			}
		}else{
			if($string_query = $cDB->BuildInsertQuery(DATABASE_INCOME_TIES, $field_array)){
				//hooray!
			} else{
		 		throw new Exception('Could not build insert query.');
			}
		}
		//print_r($string_query);
		//exit;
		try{
			$cDB->Query($string_query);
			//now make sure page knows its saved state
			$this->setAction("update");

		 	return true;
		} catch(Exception $e){
			$cStatusMessage->Error("Could not {$this->getAction()} income tie." . $e->getMessage());
		}
		
	}
	public function Delete() {
		
		global $cDB;

		$condition = "member_id=\"{$this->getMemberId()}\"";
		$string_query = "DELETE from " . DATABASE_INCOME_TIES . " WHERE {$condition}";
		try {
			$cDB->Query($string_query);
			//CT this is ropey...sant to just reload again to default
			$this->setAction("create");
			$this->setPercent("10");
			$this->setMemberIdTo("");
			return true;
		} catch (Exception $e){
			$cStatusMessage->Error($e);
		}
	}

	// 	// if($this->getAction() == "create"){
			
	// 		//"insert into " . DATABASE_INCOME_TIES . " set member_id=".$cDB->EscTxt($data["member_id"]).",
	// 			 tie_id=".$cDB->EscTxt($data["tie_id"]).", percent=".$cDB->EscTxt($data["amount"])."";
	// 	}else{

	// 	}
	// 	if (!cIncomeTies::getTie($data["member_id"])) { // has no tie, INSERT row
			
	// 		$q = "insert into income_ties set member_id=".$cDB->EscTxt($data["member_id"]).",
	// 			 tie_id=".$cDB->EscTxt($data["tie_id"]).", percent=".$cDB->EscTxt($data["amount"])."";
				
	// 	}
	// 	else { // has a tie, UPDATE row
			
	// 			$q = "update income_ties set tie_id=".$cDB->EscTxt($data["tie_id"]).", percent=".$cDB->EscTxt($data["amount"])." where member_id=".$cDB->EscTxt($data["member_id"])."";
	// 	}
		
	// 	$result = $cDB->Query($q);
		
	// 	if (!$result)
	// 		return "Error saving Income Share.";
			
	// 	return "Income Share saved successfully.";
	// }
	
	// public function deleteTie($member_id) {
		
	// 	global $cDB;
		
	// 		if (!cIncomeTies::getTie($member_id)) { // has no tie to delete!
			
	// 			return "No Income Share to delete!";
	// 	}
		
	// 	$q = "delete from income_ties where member_id=".$cDB->EscTxt($member_id)."";
		
	// 	$result = $cDB->Query($q);
		
	// 	if (!$result)
	// 		return "Error deleting income Share.";
		
	// 	return "Income Share deleted successfully.";
	// }
	

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
    public function getMemberIdTo()
    {
        return $this->member_id_to;
    }

    /**
     * @param mixed $member_id_to
     *
     * @return self
     */
    public function setMemberIdTo($member_id_to)
    {
        $this->member_id_to = $member_id_to;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * @param mixed $percent
     *
     * @return self
     */
    public function setPercent($percent)
    {
        $this->percent = trim($percent);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param mixed $updated_at
     *
     * @return self
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;

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
}

?>