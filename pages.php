<?php

include_once("includes/inc.global.php");
/*
if ($_GET["destroySess"]==1) {
	
	if ($_GET["confirm"]==1) {
		session_destroy();
		echo "Session Destroyed. <a href=index.php>Continue</a>";
	}
	else {
		echo "Really Destroy Session? <a href=pages.php?destroySess=1&confirm=1>Yes</a> | <a href=javascript:history.back(1)>No (Go back)</a>";
	}
	
	exit;
}
*/
//Design pattern of identifying entities with {entity}_id
if((!empty($_REQUEST["page_id"]))){
	$page_id = $_REQUEST["page_id"];
}elseif((!empty($_REQUEST["id"]))){
		//CT old style... deprecated. Will remove on another version
	$page_id = $_REQUEST["id"];
}else{
	$page_id =null;
}
try{
	$cInfo = new cInfo;
	$condition = "page_id={$page_id}";

	//CT only admins can see inactive pages...future.
	//if(!$cUser->isAdminActionPermitted()) $condition .= " AND active=\"" . ACTIVE . "\"";
	$cInfo->Load($condition);

	switch($cInfo->getPermission()){
		case '3':
			$cUser->MustBeLevel(2); // Admin
		break;
		case '2':
			$cUser->MustBeLevel(1);// Committee
		break;
		case '1':
			$cUser->MustBeLoggedOn();// Members
		break;
	}

	if(!empty($cInfo->getPageId())){
		$p->page_title = $cInfo->getTitle();
		// if($cUser->getMemberRole() > 1){
		// 	$form_action = (!empty($_REQUEST["form_action"])) ? $_REQUEST["form_action"] : null;
		// 	if($form_action == "update") {
		// 		$output .= "<div class=\"response success\">Your changes have been saved.</div>";
		// 	} elseif($form_action == "create"){
		// 		$output .= "<div class=\"response success\">New page created.</div>";
		// 	}
		// }
		$output .=$cInfo->Display();
		$p->DisplayPage($output);

	}else{
		$p->page_title = "Page not found (404)";
		throw new Exception("Page not found.");
	}
} catch(Exception $e){
		if(empty($p->page_title)) $p->page_title = "Error";

		$p->DisplayPage($e->getMessage());
}



?>