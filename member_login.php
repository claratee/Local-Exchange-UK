<?php
include_once("includes/inc.global.php");
//p->site_section = SITE_SECTION_OFFER_LIST;

//print_r("sdas");
if($cUser->IsLoggedOn())
{
	//ct - forward to profile page for something to do
	header("location:" . SERVER_PATH_URL ."/member_profile_menu.php");
}
else 
{
	$output = $cUser->UserLoginPage();
	$p->DisplayPage($output);
}


?>
