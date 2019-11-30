<?php

include_once("includes/inc.global.php");
// include_once("classes/class.uploads.php");
// $p->site_section = EVENTS;
$p->page_title = "Newsletters";

// $output = "<P><BR>";

$uploadsGroup = new cUploadsGroup();
$condition = "type=\"" . UPLOADS_TYPE_NEWSLETTER . "\"";
$success = $uploadsGroup->Load($condition);

$output = "";
foreach($uploadsGroup->getItems() as $upload) {
	$output .= "<tr>
					<td>{$upload->makeLink()}</td>
					<td>{$upload->getUploadDate()}</td>
				</tr>";
	$i++;
}
$output = "<h3>All newsletters</h3>
	<table class='layout1'>
	<tr>
		<th>Name</th>
		<th>Published</th>
	</tr>${output}</table>
	<div class=\"summary\">{$i} newsletters found.</div>
	";

$output = "
	<div class=\"summary\">Latest newsletter: {$uploadsGroup->getByKey(0)->makeLink()}</div>
	" . $output;

if ($i == 0){
	$output .= "Nothing has been uploaded";
}else{
	$p->DisplayPage($output);
}

?>
