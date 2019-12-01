<?php
include_once("includes/inc.global.php");
// include_once("classes/class.news.php");
// include_once("classes/class.uploads.php");

$cUser->MustBeLoggedOn();
$p->page_title = "News and Events";

 $output = "Upcoming";

$news = new cNewsGroup();
$condition = "expire_date > (CURRENT_DATE() - INTERVAL 1 DAY)";
$news->Load($condition);


foreach($news->getItems() as $news_item){
	$output .= $news_item->description ."<h2>{$news_item->title}</h2>
	{$news_item->title}
	";
}
// $news->LoadNewsGroup();
// $newstext = $news->DisplayNewsGroup();
// if($newstext != "")
// 	$output .= $newstext;
// else
// 	$output .= "There are no current news items.<P>";

// $newsletters = new cUploadGroup("N");

// if($newsletters->LoadUploadGroup()) {
// 	$output .= "<I>To read the latest ". SITE_SHORT_TITLE . " newsletter, go <A HREF=newsletters.php>here</A>.</I>";
// }

$p->DisplayPage($output);


?>
