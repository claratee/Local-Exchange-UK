<?php

class cUploadsGroup extends cCollection {
	//private $type;
	
	// function __construct($type) {
	// 	$this->type = $type;
	// }
	function __construct($rows=null) {
		//global $cDB;
		parent::__construct($rows);
        $this->setItemsClassname("cUploads"); // name of class for items array
	}
	function Load($condition) {
		global $cDB;
		$string_query = "SELECT upload_id, upload_date, title, type, filename FROM ".DATABASE_UPLOADS." WHERE {$condition} ORDER BY upload_date DESC;";
		$this->setItemClassname("cUploads");
		return $this->LoadCollection($string_query);
	}
	// //CT builds the type of object that items is supposed to be
	// function Build($rows) {
	// 	//print("hello");
	// 	global $cDB;
	// 	$i=0;
	// 	foreach ($rows as $row) {
	// 		$item = new cUploads();
	// 		$item->Build($row);
	// 		$this->addItem($item);
	// 		$i++;
	// 		//print_r($i);
	// 	}
	// 	return $this->countItems();
	// }
	//    /**
 //     * @return mixed
 //     */

    

    // /**
    //  * @param mixed $type
    //  *
    //  * @return self
    //  */
    // public function setType($type)
    // {
    //     $this->type = $type;

    //     return $this;
    // }

}
// class cUploadGroupCT extends cUploadGroup {
	
// 	function __construct($type="") {
// 		$this->type = $type;
// 		//$this->LoadUploadGroup();
// 	}
	
// 	function Load ($condition) {
// 		global $cDB, $cStatusMessage;
// 		// if (!empty($this->type))
// 		// { 
// 		// 	$typeText = "WHERE type={$cDB->EscTxt($this->type)} ";
// 		// } 
// 		// else
// 		// {
// 		// 	$typeText = "";
// 		// }
// 		//CT TODO: put somewhere. database, like categories?
		
// 			N=newsletters
// 			P=member photos
// 			I=images
// 			L=legal documents
// 			F=forms
// 			C=Calendar

		
// 		$query = $cDB->Query("SELECT 
// 			upload_id, 
// 			upload_date, 
// 			title, 
// 			type, 
// 			filename, 
// 			note 
// 			FROM ".DATABASE_UPLOADS." WHERE {$condition}
// 			ORDER BY type, upload_date DESC;");
		
		
// 		$i = 0;				
// 		while($row = mysqli_fetch_array($query)) {
// 			$this->uploads[$i] = new cUpload;			
// 			$this->uploads[$i]->ConstructUpload ($row);
// 			$i += 1;
// 		}

// 		if($i == 0)
// 			return false;
// 		else
// 			return true;
// 	}
	

// }

// class cUploadForm {

// 	function DisplayUploadForm($action, $text_fields=null) {
	
// 	$output = '<form enctype="multipart/form-data" action="'. $action.'" method="POST">';
// 	foreach($text_fields as $field)
// 		$output .= $field .' <input type="text" name="'. $field .'"><BR>';
		
// 	$output .= '<input type="hidden" name="MAX_FILE_SIZE" value="'.MAX_FILE_UPLOAD.'">Select file to upload <input name="userfile" type="file"><input type="submit" value="Upload"></form>';
// 	return $output;
// 	}


//     /**
//      * @return mixed
//      */
//     public function getUploadId()
//     {
//         return $this->upload_id;
//     }

//     /**
//      * @param mixed $upload_id
//      *
//      * @return self
//      */
//     public function setUploadId($upload_id)
//     {
//         $this->upload_id = $upload_id;

//         return $this;
//     }

//     /**
//      * @return mixed
//      */
//     public function getUploadDate()
//     {
//         return $this->upload_date;
//     }

//     /**
//      * @param mixed $upload_date
//      *
//      * @return self
//      */
//     public function setUploadDate($upload_date)
//     {
//         $this->upload_date = $upload_date;

//         return $this;
//     }

//     /**
//      * @return mixed
//      */
//     public function getType()
//     {
//         return $this->type;
//     }

//     /**
//      * @param mixed $type
//      *
//      * @return self
//      */
//     public function setType($type)
//     {
//         $this->type = $type;

//         return $this;
//     }

//     /**
//      * @return mixed
//      */
//     public function getTitle()
//     {
//         return $this->title;
//     }

//     /**
//      * @param mixed $title
//      *
//      * @return self
//      */
//     public function setTitle($title)
//     {
//         $this->title = $title;

//         return $this;
//     }

//     /**
//      * @return mixed
//      */
//     public function getFilename()
//     {
//         return $this->getFileName();
//     }

//     /**
//      * @param mixed $filename
//      *
//      * @return self
//      */
//     public function setFilename($filename)
//     {
//         $this->getFileName() = $filename;

//         return $this;
//     }

//     /**
//      * @return mixed
//      */
//     public function getNote()
//     {
//         return $this->note;
//     }

//     /**
//      * @param mixed $note
//      *
//      * @return self
//      */
//     public function setNote($note)
//     {
//         $this->note = $note;

//         return $this;
//     }
// 
 
//}

?>
