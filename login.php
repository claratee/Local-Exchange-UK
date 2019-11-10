<?php
include_once("includes/inc.global.php");
if (!empty($_GET["action"])){
	$action = $_GET["action"];
}

if (!empty($_POST["action"])){
	$action = $_POST["action"];
}

if ($action=="logout")
{
	$cUser->Logout();
	$redir_url="member_dashboard.php";
}

if ($action=="login")
{
	if (!empty($_POST["location"])){
		$redir_url = $_POST["location"];
	}


	$member_id = $_POST["user"];

	$password = $_POST["pass"];
	//
	try{
		$cUser->Login($member_id,$password);
	} catch(Exception $e){
		$cStatusMessage->Error($e->getmessage());
	}



}

include("redirect.php");	// if nothing in particular is set, will redirect to home, but this allows the user login
				// process to potentially set an alternate location.

?>
