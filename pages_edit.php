<?php

include_once("includes/inc.global.php");
include_once("classes/class.info.php");
//include("includes/inc.forms.php");

//must be committee member and above
$cUser->MustBeLevel(1);


$page_id = (!empty($_GET["page_id"])) ? $_GET["page_id"] : null;
$cInfoUtils = new cInfoUtils();

if(!empty($page_id)){
	$cInfoUtils->Load($page_id);
	$p->page_title = "Edit page '". $cInfoUtils->title ."'";
	//CT doesnt go through build function - todo - should it?
	$cInfoUtils->form_action = "update";
}else{
	$p->page_title = "Create new page";
	//CT doesnt go through build function - todo - should it?
	$cInfoUtils->form_action = "create";
}


if ($_POST["submit"]){
	$field_array = array();
	$field_array['page_id'] = $_POST["page_id"];
	$field_array['form_action'] = $_POST["form_action"];
	$field_array['active'] = $_POST["active"];
	$field_array['title'] = $_POST["title"];
	$field_array['body'] = $_POST["body"];
	$field_array['permission'] = $_POST["permission"];
	$field_array['member_id_author'] = $cUser->getMemberId();
	$cInfoUtils->Build($field_array);


	//TODO: less hacky approcach. 
	$error_message = "";
	// error - no title
	if(strlen($cInfoUtils->title) < 1) $error_message .= "Title is missing. ";

	// error - no content set
	if(strlen($cInfoUtils->body) < 1)  $error_message .= "Content is missing. ";

	$is_saved = 0;
	if(empty($error_message)) {

		$is_saved = $cInfoUtils->Save();
	} else{
		$cStatusMessage->Error($error_message);
	}
	//return message success or fail	
	
	//redirect to page if saved
	if(!empty($is_saved)){
		header("location:" . HTTP_BASE . "/pages.php?page_id={$cInfoUtils->page_id}&form_action={$cInfoUtils->form_action}");
	} 
}
//show form
$output .= $cInfoUtils->Display();


$p->DisplayPage($output);
