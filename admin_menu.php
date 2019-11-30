<?php
include_once("includes/inc.global.php");
//$p->site_section = ADMINISTRATION;

$cUser->MustBeLevel(1);


$p->page_title = "Administration Menu";

$output ="";
//CT hacky placeholder
// $status = (!empty($_GET["status"])) ? $_GET["status"] : "";
// if($status == "success_admin_contact_all") {
// 	$output .= "<div class=\"response success\"></div>";
// } 

$cBalance = new cBalanceTotal();
//CT warn and lock if set that way
$cBalance->checkBalance();
			


// enrolment
$menuArray = array();
$menuArray[] = $p->MenuItemArray("Create new member account", "member_edit.php?action=create");
$menuArray[] = $p->MenuItemArray("Edit a member account", "member_choose.php?action=member_edit&option=member");
// $menuArray[] = $p->MenuItemArray("Add a Joint Member to an Existing Account", "member_choose.php?action=member_contact_create"); // not needed - merged
$menuArray[] = $p->MenuItemArray("Edit or remove a joint member", "member_choose.php?action=member_joint_edit");
$menuHtml = $p->Menu($menuArray);
$title = $p->Wrap("Enrolment", "h3");
$output .= $p->Wrap($title . $menuHtml, "div", "col");

// support
$menuArray = array();
$menuArray[] = $p->MenuItemArray("View members not logged in", "report_no_login.php");
//$menuArray[] = $p->MenuItemArray("Member Going on Holiday", "member_choose.php?action=holiday");
//$menuArray[] = $p->MenuItemArray("Edit a Member Photo", "photo_to_edit.php");
if ($cUser->getMemberRole() > 1) { // if admin 
	$menuArray[] = $p->MenuItemArray("Inactivate a Member Account", "member_choose.php?action=member_status_change&option=member");
	$menuArray[] = $p->MenuItemArray("Re-activate a Member Account", "member_choose.php?action=member_status_change&option=inactive");
	$menuArray[] = $p->MenuItemArray("Send welcome mail and password reset", "member_choose.php?action=member_unlock");
}
$menuHtml = $p->Menu($menuArray);
$title = $p->Wrap("Support", "h3");
$output .= $p->Wrap($title . $menuHtml, "div", "col");

// transactions
$menuArray = array();
if (!empty(OVRIDE_BALANCES) && $cUser->getMemberRole() > 1) {// Only display Override Balance link if it is turned on in config file
	$menuArray[] = $p->MenuItemArray("Edit balances", "balance_to_edit.php?action=balance_to_edit");
}
if ($cUser->getMemberRole() > 1) { // if admin 
	$menuArray[] = $p->MenuItemArray("Manage account restrictions", "member_choose.php?action=member_restrict");
	$menuArray[] = $p->MenuItemArray("Manage pending trades for a member", "member_choose.php?action=trades_pending");
	$menuArray[] = $p->MenuItemArray("Record a trade for a member", "member_choose.php?action=trade&get1=type&get1val=transfer");
	$menuArray[] = $p->MenuItemArray("Raise and invoice for a member", "member_choose.php?action=trade&get1=type&get1val=invoice");
	$menuArray[] = $p->MenuItemArray("Reverse a trade that was Made in Error", "trade_reverse_choose.php");
	$menuArray[] = $p->MenuItemArray("Record Feedback for a Member", "member_choose.php?action=feedback_choose");
	$menuArray[] = $p->MenuItemArray("Manage income shares for a Member", "member_choose.php?action=income_ties");
$menuHtml = $p->Menu($menuArray);
$title = $p->Wrap("Transactions", "h3");
$output .= $p->Wrap($title . $menuHtml, "div", "col");
}
// offered listings
$menuArray = array();
$menuArray[] = $p->MenuItemArray("Manage Offered Listings for a Member", "member_choose.php?action=listing_manage&get1=type&get1val=O");
$menuArray[] = $p->MenuItemArray("Manage Wants Listings for a Member", "member_choose.php?action=listing_manage&get1=type&get1val=W");
$menuHtml = $p->Menu($menuArray);
$title = $p->Wrap("Listings", "h3");
$output .= $p->Wrap($title . $menuHtml, "div", "col");


// content
$menuArray = array();
$menuArray[] = $p->MenuItemArray("Create a new page", "pages_edit.php");
$menuArray[] = $p->MenuItemArray("Manage pages", "pages_manage.php");
$menuHtml = $p->Menu($menuArray);
$title = $p->Wrap("Content", "h3");
$subtitle = $p->Wrap("Information", "h4");
$output .= $p->Wrap($title . $subtitle . $menuHtml, "div", "col");

// news
/*
$menuArray = array();
$menuArray[] = $p->MenuItemArray("Create a News Item", "news_create.php");
$menuArray[] = $p->MenuItemArray("Edit a News Item", "news_to_edit.php?");
$menuArray[] = $p->MenuItemArray("Upload an item", "newsletter_upload.php");
$menuArray[] = $p->MenuItemArray("Delete an item", "newsletter_delete.php");
$menuHtml = $p->Menu($menuArray);
$subtitle = $p->Wrap("News &amp; Events", "h4");
$output .= $p->Wrap($subtitle . $menuHtml, "div", "col");
*/
/*
// Monthly fees
$menuArray = array();
$ts = time();
if (!empty(TAKE_MONTHLY_FEE) && $cUser->getMemberRole() > 1) {

   // $output .= "<strong>Monthly fee</strong><p>";
   
   // File missing??
 //   $output .= "<a href='monthly_fee_list.php'>List of monthly fees</a><br>";
    // CID = Confirmation ID.
	$menuArray[] = $p->MenuItemArray("Take Monthly Fee", "take_monthly_fee.php?CID=$ts");
	$menuArray[] = $p->MenuItemArray("Refund Monthly Fee", "refund_monthly_fee.php?CID=$ts");

}
if (!empty(TAKE_SERVICE_FEE) && $cUser->getMemberRole() > 1) {
	$menuArray[] = $p->MenuItemArray("Take One-Off Service Charge", "service_charge.php?CID=$ts");
	$menuArray[] = $p->MenuItemArray("Refund One-Off Service Charge", "refund_service_charge.php");
	
}*/
$menuHtml = "{$p->Menu($menuArray)}";


$title = $p->Wrap("Admin Fees", "h3");
$output .= $p->Wrap($title . $menuHtml, "div", "col");

if ($cUser->getMemberRole() > 1) { // if admin 
	$menuArray = array();
	$menuArray[] = $p->MenuItemArray("Site settings", "settings.php");
	$menuArray[] = $p->MenuItemArray("Edit or delete listing category", "category_choose.php");
	$menuArray[] = $p->MenuItemArray("MySQL Backup", "mysqli_backup.php");
	$menuHtml = $p->Menu($menuArray);
	$title = $p->Wrap("System &amp; Reporting", "h3");
	$output .= $p->Wrap($title . $menuHtml, "div", "col");
}

if ($cUser->getMemberRole() > 1) { // if admin 
	$menuArray = array();
	$menuArray[] = $p->MenuItemArray("Send an Email to All Members", "admin_contact_all.php");
	$menuHtml = $p->Menu($menuArray);
	$title = $p->Wrap("Miscellanious", "h3");
	$output .= $p->Wrap($title . $menuHtml, "div", "col");
}

$p->DisplayPage($output);

?>
