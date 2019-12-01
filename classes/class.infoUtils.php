<?php 
class cInfoUtils extends cInfo {
	//whether create or edit
	private $action; 


	function Build($vars) {
		parent::Build($vars);
		//add extra class
		if($vars['action']) $this->action = $vars['action'];
	}

	function PreparePermissionDropdown($page_id=null){
		global $p, $cUser;
		$vars = ARRAY_PAGES_VISIBILITY_ROLES;
		// add extra option if user is an admin 
		if(!$cUser->getMemberRole() > 1) {
			array_pop($vars);
		}
		$select_name = "permission";
		//if used in context of batch page controls
		if(!empty($page_id)) $select_name .= "_{$page_id}";
		$output = $p->PrepareFormSelector($select_name, $vars, null, $this->getPermission());
		return $output;
	}
	
	function PrepareCheckbox(){
		return "<input type=\"checkbox\" id=\"select_id[]\" name=\"select_id[]\" value=\"{$this->getPageId()}\" />";
	}

	function Display(){
		//$body = 
		$output = "
		<form action=\"". HTTP_BASE ."/pages_edit.php\" method=\"post\" name=\"\" id=\"\" class=\"layout2\">
			<input type=\"hidden\" id=\"page_id\" name=\"page_id\" value=\"{$this->getPageId()}\" />
			<input type=\"hidden\" id=\"action\" name=\"action\" value=\"{$this->getAction()}\" />
			<input type=\"hidden\" id=\"member_id_author\" name=\"member_id_author\" value=\"{$this->getMemberIdAuthor()}\" />

			<p>
				<label for=\"title\">
					Title *<br />
					<input maxlength=\"200\" name=\"title\" id=\"title\" type=\"text\" value=\"{$this->getTitle()}\">
				</label>
			</p>
			<p>
				<label for=\"body\">Content *<br />
					<textarea cols=\"80\" rows=\"20\" wrap=\"soft\" name=\"body\" id=\"body\">{$this->getBody()}</textarea>
				</label>
			</p>
			<p>
				<label for=\"permission\">This page can be seen by<br />
					{$this->PreparePermissionDropdown()}
				</label>
			</p>
			<p>
				<input name=\"submit\" id=\"submit\" value=\"Submit\" type=\"submit\" />
				* denotes a required field
			</p>
		</form>";
		return $output;

	}
	function Save () {
		global $p, $cStatusMessage, $cDB, $cQueries;
		$isSuccess = 0;
		//can handle both create and update
			$vars = array();
			$vars['title'] = $cDB->EscTxt($this->getTitle());
			//CT stripping linebreaks....todo: fix
			$body = str_replace("\r\n", "", $this->getBody());
			$vars['body'] = $cDB->EscTxt($body);
			$vars['member_id_author'] = $this->getMemberIdAuthor();
			$vars['permission'] = $this->getPermission();
		if($this->getAction() == "update"){
			//construct vars array
			
			//construct matching condition
			$condition = "page_id=\"{$this->getPageId()};\"";
			//construct query
			$string_query = $cDB->BuildUpdateQuery(DATABASE_PAGE, $vars, $condition);
			// do the query, return the page id if updated
			if($cDB->Query($string_query)){
				return $this->getPageId();
			} else{
				return false;
			}
		} 
		elseif($this->action == "create"){

			$string_query = $cDB->BuildInsertQuery(DATABASE_PAGE, $vars);

						//print_r($string_query);

			//CT returns last used id for display
			return $cDB->QueryReturnId($string_query);
			
		}
		return false;
		
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