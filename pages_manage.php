<?php

include_once("includes/inc.global.php");
include_once("classes/class.info.php");

$cUser->MustBeLevel(1);

$p->page_title = "Manage pages on site";
$pageGroup = new cInfoUtilsGroup;
//$pageGroup->Load($cUser->getMemberRole());

if ($_POST["submit"]){
	//$permission = array();
	// CT arrange permission array according to role selection - makes it easier to process mysql
	// foreach($pageGroup->cdm_pages as $page){
	// 	//$value=$_POST["permission_" . $page->page_id];
	// 	$permission[$_POST["permission_" . $page->page_id]][] = $page->page_id;
	// 	//find out value of each permission setting for pages
	// 	//$vars['permission'][$page->page_id] = $_POST["permission_" . $page->page_id];
	// }
	//$vars['permission'] = $permission;
	$vars['select_id'] = $_POST["select_id"];
	$vars['action'] = $_POST["action"];
	//print_r($vars);
	if($pageGroup->Save($vars)){
		$cStatusMessage->Info("Your changes were saved.");
	}
}
$pageGroup->Load($cUser->getMemberRole());


$i=1;
$row_output =  "";
foreach($pageGroup->cdm_pages as $page) {
	//stripy columns
	$className= ($i%2) ? "even" : "odd";
	$row_output .=  "
		<tr class=\"{$className}\">
			<td>{$page->PrepareCheckbox()}</td>
			<td><a href=\"pages.php?page_id={$page->getPageId()}\">{$page->getTitle()} </a><span class=\"metadata\">page_id: {$page->getPageId()}</span></td>
			<td>{$page->getUpdatedAt()} by {$page->getMemberIdAuthor()}</td>
			<td>{$page->makePermissionString()}</td>			
			<td><a href=\"pages_edit.php?page_id={$page->getPageId()}\" class=\"button edit\"><i class=\"fas fa-pencil-alt\"></i> edit</a></td>
		</tr>";
	$i++;
}

$output .= "
	<a href=\"pages_edit.php?action=create\" class=\"button\">Create new page</a>
	<!-- START bulk form pages -->
	<form method=\"post\">
		<div class=\"scrollable-x\"><table class=\"tabulated\">
			<tr>
				<th></th>
				<th>Page</th>
				<th>Updated</th>
				<th>Visible to</th>
				<th>Actions</th>
			</tr>
			{$row_output}
		</table></div>
		<p><label>Bulk actions: {$pageGroup->PrepareActionDropdown()}</label><span class=\"metadata\">{$i} items found</span></p>
		<p><input id=\"submit\" name=\"submit\" type=\"submit\" value=\"Apply changes\"></p>
	</form>
	<!-- END bulk form pages -->
";

$p->DisplayPage($output);
