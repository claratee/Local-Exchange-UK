<?php

include_once("includes/inc.global.php");
include_once("classes/class.info.php");
//include("includes/inc.forms.php");

//must be committee member and above
$cUser->MustBeLevel(1);

$cInfoUtils = new cInfoUtils();

if((!empty($_REQUEST["page_id"]))){
	$page_id = $_REQUEST["page_id"];
}elseif((!empty($_REQUEST["id"]))){
		//CT old style... deprecated. Will remove on another version
	$page_id = $_REQUEST["id"];
}else{
	$page_id =null;
}



if(!empty($page_id)){
	$cInfoUtils->Load($page_id);
	$p->page_title = "Edit page '". $cInfoUtils->getTitle() ."'";
	//CT doesnt go through build function - todo - should it?
	$cInfoUtils->setAction("update");
}else{
	$p->page_title = "Create new page";
	//CT doesnt go through build function - todo - should it?
	$cInfoUtils->setAction("create");
}


if ($_POST["submit"]){
	$field_array = array();
	$field_array['page_id'] = $_POST["page_id"];
	$field_array['action'] = $_POST["action"];
	$field_array['status'] = $_POST["status"];
	$field_array['title'] = $_POST["title"];
	$field_array['body'] = $_POST["body"];
	$field_array['permission'] = $_POST["permission"];
	$field_array['member_id_author'] = $cUser->getMemberId();
	$cInfoUtils->Build($field_array);


	//TODO: less hacky approcach. 
	$error_message = "";
	// error - no title
	if(strlen($cInfoUtils->getTitle()) < 1) $error_message .= "Title is missing. ";

	// error - no content set
	if(strlen($cInfoUtils->getBody()) < 1)  $error_message .= "Content is missing. ";

	$page_id = 0;
	if(empty($error_message)) {

		$page_id = $cInfoUtils->Save();
	} else{
		throw new Exception($error_message);
	}
	//return message success or fail	
	
	//redirect to page if saved
	//print("test " . $is_saved);
	if(!empty($page_id)){

		header("location:" . HTTP_BASE . "/pages.php?page_id={$page_id}&form_action={$cInfoUtils->getAction()}");
	} 
}
//show form
$output .= $cInfoUtils->Display();


$p->DisplayPage($output);
