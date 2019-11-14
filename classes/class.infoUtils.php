<?php 
class cInfoUtils extends cInfo {
	//whether create or edit
	var $action; 


	function Build($vars) {
		parent::Build($vars);
		//add extra class
		if($vars['action']) $this->action = $vars['action'];
	}
	function PreparePermissionDropdown($page_id=null){
		global $p, $cUser;
		$vars = array("0" => "Guests", "1" => "Members", "2" => "Committee");
		// add extra option if user is an admin 
		if($cUser->getMemberRole() > 1) {
			$vars[3] = "Administrators";
		}
		$select_name = "permission";
		//if used in context of batch page controls
		if(!empty($page_id)) $select_name .= "_{$page_id}";
		$output = $p->PrepareFormSelector($select_name, $vars, null, $this->permission);
		return $output;
	}
	
	function PrepareCheckbox(){
		return "<input type=\"checkbox\" id=\"select_id[]\" name=\"select_id[]\" value=\"{$this->page_id}\" />";
	}

	function Display(){

		$output = "
		<form action=\"". HTTP_BASE ."/pages_edit.php\" method=\"post\" name=\"\" id=\"\" class=\"layout2\">
			<input type=\"hidden\" id=\"page_id\" name=\"page_id\" value=\"{$this->page_id}\" />
			<input type=\"hidden\" id=\"action\" name=\"action\" value=\"{$this->action}\" />
			<!-- <input type=\"hidden\" id=\"active\" name=\"active\" value=\"1\" /> -->
			<input type=\"hidden\" id=\"active\" name=\"active\" value=\"{$this->active}\" />
			<input type=\"hidden\" id=\"member_id_author\" name=\"member_id_author\" value=\"{$this->member_id_author}\" />

			<p>
				<label for=\"title\">
					Title *<br />
					<input maxlength=\"200\" name=\"title\" id=\"title\" type=\"text\" value=\"{$this->title}\">
				</label>
			</p>
			<p>
				<label for=\"body\">Content *<br />
					<textarea cols=\"80\" rows=\"20\" wrap=\"soft\" name=\"body\" id=\"body\">{$this->body}</textarea>
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
			$vars['title'] = $cDB->EscTxt($this->title);
			//CT stripping linebreaks....todo: fix
			$body = str_replace("\r\n", "", $this->body);
			$vars['body'] = $cDB->EscTxt($body);
			$vars['member_id_author'] = $this->member_id_author;
			$vars['permission'] = $this->permission;
		if($this->action == "update"){
			//construct vars array
			
			//construct matching condition
			$condition = "page_id=\"{$this->page_id};\"";
			//construct query
			$string_query = $cDB->BuildUpdateQuery(DATABASE_PAGE, $vars, $condition);
			// do the query, return the page id if updated
			if($cDB->Query($string_query)){
				return $this->page_id;
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
}

?>