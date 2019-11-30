<?php

class cUploads extends cBasic2 {
	private $upload_id;
	private $upload_date;
	private $type; // for example "N" for "newsletters"
	private $title;
	private $filename;
	private $note;

	//CT no construct - in basic
	
	function Save() {
		// Copy file uploaded by UploadForm class to uploads directory and
		// save entry for it in the database
		global $cDB, $cStatusMessage;
		
		if(empty($this->getFilename())){
			$this->setFilename($_FILES['userfile']['name']);
		}
		
		$query = $cDB->Query("SELECT null from ". DATABASE_UPLOADS ." WHERE filename ='".$_FILES['userfile']['name']."';");
		
		if($row = mysqli_fetch_array($query)) {
			$cStatusMessage->Error("A file with this name already exists on the server.");
			return false;
		}		
			
		if(move_uploaded_file($_FILES['userfile']['tmp_name'], UPLOADS_PATH . $this->getFilename())) {
			$insert = $cDB->Query("INSERT INTO ". DATABASE_UPLOADS ." (type, title, filename, note) VALUES (". $cDB->EscTxt($this->type) .", ". $cDB->EscTxt($this->title) .", ". $cDB->EscTxt($this->getFilename()) .", ". $cDB->EscTxt($this->note) .");");
						
			if(mysqli_affected_rows() == 1) {
				$this->upload_id = mysqli_insert_id();	
				$query = $cDB->Query("SELECT upload_date FROM ".DATABASE_UPLOADS." WHERE  upload_id=". $this->upload_id.";");
				if($row = mysqli_fetch_array($query))
					$this->upload_date = $row[0];					
				return true;
			} else {
				$cStatusMessage->Error("Could not save database row for uploaded file.");
				return false;
			}				
		} else {
			$cStatusMessage->Error("Could not save uploaded file. This could be because of a permissions problem.  Does the web user have permission to write to the uploads directory?  It could also be that the file is too large.  The current maximum size of file allowed is ".MAX_FILE_UPLOAD." bytes.");
			return false;
		}
	}
	
	// function Load ($condition) {
	// 	global $cDB, $cStatusMessage;
			
	// 	$query = $cDB->Query("SELECT upload_date, type, title, filename, note FROM ".DATABASE_UPLOADS." WHERE {$condition};");
		
	// 	if($row = mysqli_fetch_array($query)) {		
	// 		$this->setUploadId($upload_id);		
	// 		$upload_date = new cDateTime($row[0]);
	// 		$this->setDate($upload_date);		
	// 		$this->setType($row[1]);		
	// 		$this->setTitle($row[2]);
	// 		$this->setFilename($row[3]);
	// 		$this->note = $cDB->UnEscTxt($row[4]);
	// 		return true;
	// 	} else {
	// 		$cStatusMessage->Error("There was an error accessing the uploads table.  Please try again later.");
	// 		include("redirect.php");
	// 	}
		
	// }
	// function Build ($row) {
	// 	global $cDB, $cStatusMessage;
			
		
	// 	$this->setUploadId($row['upload_id']);
	// 	//$this->upload_date = new cDateTime($row['upload_date']);
	// 	$this->setUploadDate($row['upload_date']);
	// 	//$this->upload_date = $row['upload_date'];
	// 	$this->setType($row['type']);		
	// 	//$this->type_text = $row['type_text'];		
	// 	$this->setTitle($row['title']);
	// 	$this->setFileName($row['filename']);
	// 	$this->note = $cDB->UnEscTxt($row['name']);
	// 	return true;

		
	// }

	function DeleteUpload () {
		global $cDB, $cStatusMessage;
		
		if(unlink(UPLOADS_PATH . $this->getFileName())) {
			$delete = $cDB->Query("DELETE FROM ". DATABASE_UPLOADS ." WHERE upload_id = ". $this->upload_id .";");
			if(mysqli_affected_rows() == 1) {
				return true;
			} else {
				$cStatusMessage->Error("File was deleted but could not delete row from database.  The row will have to removed manually.  Please contact your systems administrator.");
				include("redirect.php");
			}			
		} else {
			$cStatusMessage->Error("Could not delete file - ". $this->getFileName() .".  Please try again later.");
			include("redirect.php");
		}
	}

	// function DisplayURL ($text=null) {
	// 	if(empty($text)) $text = $this->title;
	// 	// RF: changed to open file in uploads in new window	
	// 	return "<a href='uploads/{$this->getFileName()}' target='_blank'>{$text}</a>";
	// }
	public function makeLink(){
		return "<a href=\"" . HTTP_BASE . "/" . urlencode($this->getFilename()) . "\" target=\"doc\">{$this->getTitle()}</a>";
	}

	public function DisplayUploadForm($action, $text_fields=null) {
		$output = '<form enctype="multipart/form-data" action="'. $action.'" method="POST">';
		foreach($text_fields as $field)
			$output .= $field .' <input type="text" name="'. $field .'"><BR>';
			
		$output .= '<input type="hidden" name="MAX_FILE_SIZE" value="'.MAX_FILE_UPLOAD.'">Select file to upload <input name="userfile" type="file"><input type="submit" value="Upload"></form>';
		return $output;
	}
 

    /**
     * @return mixed
     */
    public function getUploadId()
    {
        return $this->upload_id;
    }

    /**
     * @param mixed $upload_id
     *
     * @return self
     */
    public function setUploadId($upload_id)
    {
        $this->upload_id = $upload_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUploadDate()
    {
        return $this->upload_date;
    }

    /**
     * @param mixed $upload_date
     *
     * @return self
     */
    public function setUploadDate($upload_date)
    {
        $this->upload_date = $upload_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

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
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param mixed $filename
     *
     * @return self
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

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
