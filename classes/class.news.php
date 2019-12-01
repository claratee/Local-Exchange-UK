<?php

class cNews extends cSingle {
	private $news_id;
	private $title;
	private $description;
	private $expire_date;
	private $sequence;

//CT use baseclass construct
	// function __construct($title=null, $description=null, $expire_date=null, $sequence=null) {
	// 	if($title) {
	// 		$this->title = $title;
	// 		$this->description = $description;
	// 		$this->expire_date = new cDateTime($expire_date);
	// 		$this->sequence = $sequence;
	// 	}
	// }
	
	function Build($field_array){
        //print_r($vars);
        //$this->__set('type_description', $this->TypeDesc($this->getType()));
        //lazy load of vars      
		//print_r($field_array);
        $is_success=parent::Build($field_array);
        //stop if not buided
        print_r($is_success);
        return $is_success;
       
    } 
	function SaveNewNews () {
		global $cDB, $cStatusMessage;
		
		$insert = $cDB->Query("INSERT INTO ". DATABASE_NEWS ." (title, description, expire_date, sequence) VALUES (".$cDB->EscTxt($this->title) .", ". $cDB->EscTxt($this->description) .", '". $this->expire_date->MySQLDate() ."', ". $this->sequence .");");

		if(mysqli_affected_rows() == 1) {
			$this->news_id = mysqli_insert_id();		
			return true;
		} else {
			$cStatusMessage->Error("Could not save news item.");
			return false;
		}		
	}
	
	function SaveNews () {
		global $cDB, $cStatusMessage;			
		
		$update = $cDB->Query("UPDATE ".DATABASE_NEWS." SET title=". $cDB->EscTxt($this->title) .", description=". $cDB->EscTxt($this->description) .", expire_date='". $this->expire_date->MySQLDate(). "', sequence=". $this->sequence ." WHERE news_id=". $cDB->EscTxt($this->news_id) .";");

		return $update;	
	}
	
	function Load ($condition) {
		global $cDB, $cStatusMessage;
		
//		$this->ExpireNews();
				
		$query = $cDB->Query("SELECT title, description, expire_date, sequence FROM ".DATABASE_NEWS." WHERE  news_id=". $cDB->EscTxt($news_id) .";");
		
		if($row = mysqli_fetch_array($query)) {		
			$this->news_id = $news_id;
			$this->title = $cDB->UnEscTxt($row[0]);
			$this->description = $cDB->UnEscTxt($row[1]);		
			$this->expire_date = new cDateTime($row[2]);
			$this->sequence = $row[3];
			return true;
		} else {
			$cStatusMessage->Error("There was an error accessing the news table.  Please try again later.");
			include("redirect.php");
		}
		
	}

	function DisplayNews () {
		$output = "<STRONG>". $this->title ."</STRONG><P>";
		$output .= $this->description ."<P>";
		return $output;
	}

    /**
     * @return mixed
     */
    public function getNewsId()
    {
        return $this->news_id;
    }

    /**
     * @param mixed $news_id
     *
     * @return self
     */
    public function setNewsId($news_id)
    {
        $this->news_id = $news_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @param mixed $sequence
     *
     * @return self
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }
}



?>
