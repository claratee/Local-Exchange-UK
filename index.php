<?php
include_once("includes/inc.global.php");
$p->site_section = SITE_SECTION_OFFER_LIST;
//$p->page_title= "Show index page":
$p->page_content = <<<EOT
<h1>Index page</h1>
<p>This will be: Latest news, newsletter, upcoming event, featured offers, wants, latest trades, social networking, new member welcome. new features on site, links to feedback.</p>
<h2><a href="member_profile_menu.php">Your dashboard</a></h2>
EOT;
$p->DisplayPage();

?>
