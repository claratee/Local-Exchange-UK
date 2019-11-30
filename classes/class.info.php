<?php

class cInfo extends cBasic2 {
		var $page_id; 
		var $title;
		var $body;
		var $updated_at; 
		var $active;
		var $permission; 

		//CT new

		var $page_date; 
		var $member_id_author; // should be stored in a different table with updates 

		//CT put in standard construct and load functions for completeness
		//CT rewrite
		function __construct($vars=null){
			//can pass vars directly
			if(!empty($vars)) $this->Build($vars);
		}
		function Load($page_id, $active=1) {

			global $cDB, $cStatusMessage, $cQueries;
			// hould use page_id. by default dont show inactive ones
			$condition = "page_id={$page_id} AND active={$active}";
			$order_by = "page_id ASC";

			$query = $cDB->Query($cQueries->getMySqlInfoPage($condition, $order_by));
	
			while($row = $cDB->FetchArray($query)) {
				$this->Build($row);
				return true;
			}
			return false; //failed
		}
		function Build($field_array) {
			global $cDB, $cStatusMessage;
			//TODO: CT vars on class should use getters and setters
			if($field_array['page_id']) $this->page_id = $field_array['page_id'];
			if(isset($field_array['title'])) $this->title = $cDB->UnEscTxt($field_array['title']);
			//if(isset($field_array['body'])) $this->body = $this->tidyHTML($field_array['body']);
			if(isset($field_array['body'])) $this->body = $cDB->UnEscTxt($field_array['body']);
			if(isset($field_array['date'])) $this->date = $field_array['date'];
			if(isset($field_array['active'])) $this->active = $field_array['active'];
			if(isset($field_array['permission'])) $this->permission = $field_array['permission'];
			//if($field_array['created_at']) $this->created_at = $field_array['created_at'];
			if(isset($field_array['updated_at'])) $this->updated_at = $field_array['updated_at'];
			if(isset($field_array['member_id_author'])) $this->member_id_author = $field_array['member_id_author'];
		}
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

			$extract = strip_tags($this->body);
			$extract = trim($extract);
			return substr($extract, 0, $extract_length) . "...";
		}
		function Display(){
			global $cUser, $p, $cDB, $site_settings;
			$string = "";
			//$clean_text = $this->tidyHTML($this->body);
			//CT show page
			if(!empty($this->page_id)){
				if($cUser->isAdminActionPermitted())  {   
					//CT move edit button to page class
					$string.= "<div class=\"button edit\"><a href=\"pages_edit.php?page_id={$this->page_id}\" class=\"button edit\"><i class=\"fas fa-pencil-alt\"></i> edit</a></div>";
				}
				//CT put this in permissions object
				$role_string  = ($this->permission > 0 ) ? "Page visible to " . $this->makePermissionString() : "";


				$authorstring = ($cUser->IsLoggedOn()) ? " by #{$this->member_id_author}" : "";
				$string .= "<div class=\"content\">{$cDB->UnEscTxt($this->body)}</div>";
				$string .= "<div class=\"metadata left\">{$role_string}</div><div class=\"metadata\"> Updated on {$this->updated_at}{$authorstring}</div>";
			}
			return $string;
		}

}


