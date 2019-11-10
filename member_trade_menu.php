<?php
include_once("includes/inc.global.php");
$p->page_title = "My trades and transactions";

$cUser->MustBeLoggedOn();
//CT fix this
$output = '
	<p><a href="trade.php?type=T" class="button large"><i class="fas fa-sign-out-alt"></i>Pay another member</a> <a href="trade.php?type=I" class="button large"><i class="fas fa-receipt"></i>Invoice another member</a></p>
	<p><a href="trades_pending.php">invoices and transactions pending</a></p>
	<h3>Feedback</h3>
	<ul>
		<li><a href="feedback_all.php?member_id=' . $cUser->getMemberId() . '">View My Feedback</a></li>
		<li><a href="member_choose.php?action=feedback_all">View Another Member\'s Feedback</a></li>
		<li><a href="feedback_choose.php?member_id=' . $cUser->getMemberId() . '">Leave Feedback for a Recent Exchange</a></li>
	</ul>
	<h3>Exchange History</h3>
	<ul>
		<li><a href="trade_history.php?member_id=' . $cUser->getMemberId() . '">View My Balance and Exchange History</a></li>
		<li><a href="member_choose.php?action=trade_history"">View Another Member\'s Exchange History</a></li>
		<li><a href="timeframe_choose.php?action=trade_history_all">View All Trades in a Specified Time Period</a></li>
	</ul>
	<h3>Income Sharing</h3>
	<ul>
		<li><a href="income_ties.php">Manage Income Shares</a></li>
		<li><a href="member_contact_create.php?member_id=' . $cUser->getMemberId() . '">Add a Joint Member to My Account</a></li>
		<li><a href="member_contact_choose.php?member_id=' . $cUser->getMemberId() . '">Edit/Delete a Joint Member</a></li>
	</ul>';
// $menuArray = array();
// $menuArray[] = $p->MenuItemArray("View pending trades and invoices to pay", "trades_pending.php?member_id={$member_id}");
// $menuArray[] = $p->MenuItemArray("Transfer '". UNITS . "' to another member", "trade.php?type=transfer");
// $menuArray[] = $p->MenuItemArray("Invoice another member", "trade.php?type=invoice");
// $menuArray[] = $p->MenuItemArray("View my balance and trade history", "trade_history.php?member_id={$member_id}");
// $menuArray[] = $p->MenuItemArray("View my feedback", "feedback_all.php?member_id={$member_id}");
// $menuArray[] = $p->MenuItemArray("Leave Feedback for a Recent exchange", "feedback_choose.php?member_id={$member_id}");
// $menuArray[] = $p->MenuItemArray("Community: view exchanges for everyone in a Specified Time Period", "timeframe_choose.php?member_id={$member_id}");
// $menuHtml = $p->Menu($menuArray);
//$title = $p->Wrap("Wanted listings", "h3");
//$output .= $p->Wrap($menuHtml, "div", "col");

$p->DisplayPage($output);

?>
