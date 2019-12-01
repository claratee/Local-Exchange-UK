<?php

class cInfo extends cSingle {
		private $page_id; 
		private $title;
		private $body;
		private $updated_at; 
		private $status;
		private $permission; 

		//CT new

		private $page_date; 
		private $member_id_author; // should be stored in a different table with updates 

		//CT put in standard construct and load functions for completeness
		//CT rewrite
		// function __construct($vars=null){
		// 	//can pass vars directly
		// 	if(!empty($vars)) $this->Build($vars);
		// }
		function Load($condition) {

			global $cDB, $cStatusMessage, $cQueries;
			// hould use page_id. by default dont show inactive ones
			//$condition = "";
			$order_by = "page_id ASC";
			$string_query = $cQueries->getMySqlInfoPage($condition, $order_by);
			return $this->LoadFromDatabase($string_query);
		}
		// function Build($field_array) {
		// 	global $cDB, $cStatusMessage;
		// 	//TODO: CT vars on class should use getters and setters
		// 	if($field_array['page_id']) $this->page_id = $field_array['page_id'];
		// 	if(isset($field_array['title'])) $this->title = $cDB->UnEscTxt($field_array['title']);
		// 	//if(isset($field_array['body'])) $this->body = $this->tidyHTML($field_array['body']);
		// 	if(isset($field_array['body'])) $this->body = $cDB->UnEscTxt($field_array['body']);
		// 	if(isset($field_array['date'])) $this->date = $field_array['date'];
		// 	if(isset($field_array['active'])) $this->active = $field_array['active'];
		// 	if(isset($field_array['permission'])) $this->permission = $field_array['permission'];
		// 	//if($field_array['created_at']) $this->created_at = $field_array['created_at'];
		// 	if(isset($field_array['updated_at'])) $this->updated_at = $field_array['updated_at'];
		// 	if(isset($field_array['member_id_author'])) $this->member_id_author = $field_array['member_id_author'];
		// }
		function makePermissionString(){
			global $p, $cUser;
			$vars = ARRAY_PAGES_VISIBILITY_ROLES;
			return $vars[$this->permission];
		}
		function tidyHTML($html) {
			global $cDB;
			//return $html;
			//CT this is not working properly...
			return $cDB->ScreenHTML($html);
		}
		function getExtract($extract_length=80){

			$extract = strip_tags($this->getBody());
			$extract = trim($extract);
			return substr($extract, 0, $extract_length) . "...";
		}
		function Display(){
			global $cUser, $p, $cDB, $site_settings;
			$string = "";
			//$clean_text = $this->tidyHTML($this->body);
			//CT show page
			if(!empty($this->getPageId())){
				if($cUser->isAdminActionPermitted())  {   
					//CT move edit button to page class
					if($this->getStatus()) $string .= "<p class=\"summary\">This page is **INACTIVE** and cannot be seen by members. Change status in edit mode.</p>";
					$string.= "<div class=\"button edit\"><a href=\"pages_edit.php?page_id={$this->getPageId()}\" class=\"button edit\"><i class=\"fas fa-pencil-alt\"></i> edit</a></div>";
					
				}
				//CT put this in permissions object
				$role_string  = ($this->getPermission() > 0 ) ? "Page visible to " . $this->makePermissionString() : "";


				$authorstring = ($cUser->IsLoggedOn()) ? " by {$this->getMemberIdAuthor()}" : "";
				$string .= "<div class=\"content\">{$cDB->UnEscTxt($this->getBody())}</div>";
				$string .= "<div class=\"metadata left\">{$role_string}</div><div class=\"metadata\"> Updated on {$this->getUpdatedAt()}{$authorstring}</div>";
			}
			return $string;
		}


    /**
     * @return mixed
     */
    public function getPageId()
    {
        return $this->page_id;
    }

    /**
     * @param mixed $page_id
     *
     * @return self
     */
    public function setPageId($page_id)
    {
        $this->page_id = $page_id;

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
        
        $this->title = stripslashes($title);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     *
     * @return self
     */
    public function setBody($body)
    {
        $this->body = stripslashes($body);

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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     *
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @param mixed $permission
     *
     * @return self
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageDate()
    {
        return $this->page_date;
    }

    /**
     * @param mixed $page_date
     *
     * @return self
     */
    public function setPageDate($page_date)
    {
        $this->page_date = $page_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMemberIdAuthor()
    {
        return $this->member_id_author;
    }

    /**
     * @param mixed $member_id_author
     *
     * @return self
     */
    public function setMemberIdAuthor($member_id_author)
    {
        $this->member_id_author = $member_id_author;

        return $this;
    }
}


