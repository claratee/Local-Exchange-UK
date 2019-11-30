<?php

include_once("includes/inc.global.php");

$cUser->MustBeLevel(2);
$p->page_title = "Reverse an Exchange";

//include_once("classes/class.trade.php");
//include("includes/inc.forms.php");

//
// Define form elements
//
if(!empty($_GET['trade_id'])) {
	$trade_id = urldecode($_GET['trade_id']);
}elseif(!empty($_POST['trade_id'])) {
	$trade_id = $_POST['trade_id'];
} else{
	$cStatusMessage->Error("Trade ID missing.");	
	$redir_url="trade_reverse_choose.php";
	include("redirect.php");
}


try{

	
	$condition="`t`.`trade_id`={$trade_id} AND `t`.`status`=\"V\"";
	$trade = new cTradeUtils();
	$trade->Load($condition);

	if(!empty($_POST['submit'])){
		ProcessData($_POST);
	}
	// $trade->setAction("reverse");

		$output .= "<table class=\"tabulated\">
			<tr>
				<th>Date</th>
                <th class='units'>Amount</th>
				<th>From</th>
				<th>To</th>
				<th>Category</th>
				<th>Description</th>
			</tr>
			<tr class=\"\">
				<td>
					{$trade->getTradeDate()}
				</td>
				<td class=\"units\">
					{$trade->getAmount()}
				</td>	
								<td class=\"\">
					{$trade->getMemberIdFrom()}
				</td>	
								<td class=\"\">
					{$trade->getMemberIdTo()}
				</td>			
				<td>
					{$trade->getCategoryName()}
				</td>
				<td>
					{$trade->getDescription()} <span class=\"metadata\">trade id: {$trade->getTradeId()}</span>
				</td>

			</tr></table><br />";
            $output .= "

            <form action=\"{$_SERVER['PHP_SELF']}\" method=\"post\" name=\"form\" id=\"form\" class=\"layout2\">
	            <input type=\"hidden\" id=\"action\" name=\"action\" value=\"reverse\" />
	            <input type=\"hidden\" id=\"trade_id\" name=\"trade_id\" value=\"{$trade->getTradeId()}\" />
	            <input type=\"hidden\" id=\"status\" name=\"status\" value=\"{$trade->getStatus()}\" />
	            <input type=\"hidden\" id=\"member_id_to\" name=\"member_id_to\" value=\"{$trade->getMemberIdFrom()}\" />
	            <input type=\"hidden\" id=\"member_id_from\" name=\"member_id_from\" value=\"{$trade->getMemberIdTo()}\" />
				<p>
	                <label for=\"reason\">
	                    <span>Enter a brief explanation why you are reversing the trade.</span>
	                    <input maxlength=\"200\" name=\"reason\" id=\"reason\" type=\"text\" value=\"{$_POST['reason']}\" autocomplete=\"off\" />
	                </label>
            	</p>
                <p><input name=\"submit\" id=\"submit\" class=\"large\" value=\"Reverse Trade\" type=\"submit\" /></p>
            </form>";


}catch(Exception $e){
	$cStatusMessage->Error($e->getMessage());
}
// $form->addElement("select", "trade_id", "Choose the exchange to reverse", $trades->MakeTradeArray());
// $form->addElement("html", "<TR></TR>");
// $form->addElement('static', null, 'Enter a brief explanation. Information about the original exchange will be automatically included.', null);
// $form->addElement('textarea', 'description', null, array('cols'=>50, 'rows'=>2, 'wrap'=>'soft', 'maxlength' => 75));
// $form->addElement('submit', 'btnSubmit', 'Reverse');

//
// Define form rules
//
//$form->addRule('description', 'Enter a description', 'required');


//
// Then check if we are processing a submission or just displaying the form
//
// if ($form->validate()) { // Form is validated so processes the data
//    $form->freeze();
//  	$form->process('process_data', false);
// } else {
//    $p->DisplayPage($form->toHtml());  // just display the form
// }
$p->DisplayPage($output);

function ProcessData ($values) {
	global $p, $cStatusMessage, $trade;
	$trade->Build($values);

	$success = $trade->Save();	
	
	if($success){

		$output = "Trade has been reversed.";
		include("redirect.php");
	}
	else{
		throw new Exception("There was an error reversing the trade.");
	}
	
   
}


// function displayForm() { // TODO: Should use SaveMember and should reset $this->password
//        global $p;
       
       
//             //CT todo - use template.
//             $output = "
//             <form action=\"{$_SERVER['PHP_SELF']}?member_id={$trade->getTradeId()}\" method=\"post\" name=\"form\" id=\"form\" class=\"layout2\">
// 	            {$archive_dropdown}
//                 <input name=\"action\" id=\"action\" type=\"hidden\" value=\"reverse\">
//                 <input name=\"status\" id=\"status\" type=\"hidden\" value=\"R\">
//                 <h3>Reverse trade {$trade_id} {$trade->getCategoryName()} {$trade->getDescription()}</h3>

//                 <p><input name=\"submit\" id=\"submit\" class=\"large\" value=\"Reverse trade\" type=\"submit\" /></p>
//             </form>";
            
        
//         return $output;
//     }

	// $form->addElement("hidden", "member_id", $_REQUEST["member_id"]);

	// if($member->status == 'A') {
	// 	$form->addElement("static", null, "Are you sure you want to inactivate this member?  They will no longer be able to use this website, and all their listings will be inactivated as well.", null);
	// 	$form->addElement("static", null, null, null);
	// 	$form->addElement('submit', 'btnSubmit', 'Inactivate');
	// } else {
	// 	$form->addElement("static", null, "Are you sure you want to re-activate this member?  Their listings will need to be reactivated individually or new ones created.", null);
	// 	$form->addElement("static", null, null, null);
	// 	$form->addElement('submit', 'btnSubmit', 'Re-activate');
	// }

	// if ($form->validate()) { // Form is validated so processes the data
	//    $form->freeze();
	//  	$form->process("process_data", false);
	// } else {  // Display the form
	// 	$p->DisplayPage($form->toHtml());
	// }

?>
