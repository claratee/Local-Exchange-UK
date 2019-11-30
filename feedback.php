<?php

include_once("includes/inc.global.php");
$cUser->MustBeLoggedOn();

	try{
		$feedback = new cFeedback(); 
		if(($cUser->isAdminActionPermitted()) && !empty($_REQUEST["member_id"])) {  // Administrator is creating listing for another member
			$member = new cMember();
			
			$member->Load("m.member_id =\"{$_REQUEST["member_id"]}\"");
			$page_title = "Leave feedback for a recent trade by {$member->getDisplayName()}";
			

		}else{
			//CT user the current (session-saved user as member - efficiency)
			$member=$cUser;
			$page_title = "Leave feedback for a recent trade";
		}





		//CT check for errors - hardstop if found
		if(empty($_REQUEST['trade_id'])) {
			throw new Exception("Missing trade ID.");
		} 

		$feedback->setMemberIdAuthor($member->getMemberId());
		$feedback->setTradeId($_REQUEST['trade_id']);


		$trades = $feedback->returnAllValidTrades($_REQUEST['trade_id']);
		//print_r(sizeof($trades->getItems()));

		//if retrun false
		//print_r($trade);
		if(!$trades || sizeof($trades->getItems()) == 0) {

			//CT catchall. could be broken onto different stepped reasons, but would have to rebuild the test.
			throw new Exception("You cannot leave feedback on this trade. <a href=\"feedback_choose.php?member_id={$feedback->getMemberIdAuthor()}\">Leave feedback for another trade</a>");
		} 

		if($_POST['submit']){
			
			if($feedback_id=ProcessData($_POST)){
				$cStatusMessage->Info("Successfully left feedback for trade.");
				//CT leave more feedback.
				$redir_url="feedback_choose.php?member_id={$member->getMemberId()}";
 	 			include("redirect.php");
			}
		} 

		$trade = $trades->getItems()[0];

		$member_id_author = $member->getMemberId();
		if($trade->getMemberIdFrom() == $member->getMemberId()){
			$role_text = "<p>You were the <strong>buyer</strong> in this transaction.</p>";
			$member_id_about = $trade->getMemberIdTo();
		}else{
			$role_text = "<p>You were the <strong>seller</strong> in this transaction.</p>";
			$member_id_about = $trade->getMemberIdFrom();
		}

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

			</tr></table>
			<p>{$role_text}</p>";

		//CT check if the member has not already left feedback
		// if($feedback->checkForDuplicates()) {
		// 	throw new Exception('You have already left feedback about this trade.'); //safety check
		// }
		//$feedback_array = array();

  
        $dropdown_rating = $p->PrepareFormSelector("rating", ARRAY_FEEDBACK, "Select action", $feedback->getRating());
		$member_about = new cMember();
			
		$member_about->Load("m.member_id =\"{$member_id_about}\" AND m.status=\"A\"");

		$output .= "<h3>How well did this trade go?</h3>";
		
		$output .= "<p>This feedback will be shown on trade listings. It will contribute to the rating of {$member_about->getDisplayName()} ({$member_about->getMemberId()}).</p><p>All feedback is public. Before leaving <i>negative</i> feedback, we recommend trying to address your concerns with the other community member.  Often misunderstandings can be resolved to the benefit of both parties. </p>";

			$output .= "
				<form action=\"\" method=\"post\" name=\"\" id=\"\" class=\"layout1\">
					<input type=\"hidden\" id=\"status\" name=\"status\" value=\"A\" />
					<input type=\"hidden\" id=\"trade_id\" name=\"trade_id\" value=\"{$trade->getTradeId()}\" />
					<input type=\"hidden\" id=\"member_id_author\" name=\"member_id_author\" value=\"{$member_id_author}\" />
					<input type=\"hidden\" id=\"member_id_about\" name=\"member_id_about\" value=\"{$member_id_about}\" />

				    <p>
			        	<label for=\"category_id\">
				            <span>Rating *</span>
				            {$dropdown_rating}
				        </label>
				    </p>	
				    </p>	
				    <p>
			        	<label for=\"comment\">
			        		<span>Your review *</span>
				            <textarea id=\"comment\" name=\"comment\">{$feedback->getComment()}</textarea>
				        </label>
				    </p>	   	
				    <p>
			        	<label for=\"amount\">
				            {$form_amount}
				        </label>
				
				    * denotes a required field
					<p class=\"summary\">
						<input name=\"submit\" id=\"submit\" value=\"Submit\" type=\"submit\" />
						
					</p>
				</form>";




// 	if($trade->)




// 		$member_about = new cMember;
// 		$condition="m.member_id='{$_REQUEST["member_id_about"]}' AND m.status='A'";
		

// 		$member_about->Load($condition);
// 		$p->page_title = "Leave Feedback about ". $member_about->getDisplayName();

// 		if($cUser->getMode() == "admin" && !emoty($_REQUEST["member_id"])) {
// 			$member_author = new cMember;

// 			$condition="m.member_id='{$_REQUEST["member_id"]}' AND m.status='A'";
			
// 			$member_author->Load($condition);




// 			$p->page_title .= " for ". $member->getDisplayName();

// 		} else {
			
// 			$member = $cUser;
// 		}
// 		$feedback = new cFeedback();

// 		$field_array = array();
// 		$field_array['member_id_about'] = $member_about->getMemberId();
// 		$field_array['member_id_author'] = $member_author->getMemberId();
// 		$field_array['trade_id'] = $_REQUEST["trade_id"];
// 		$field_array['rating'] = $_REQUEST["rating"];
// 		$field_array['comment'] = $_REQUEST["comments"];

// 		$feedback->Build($field_array);



// 		$intro ="All feedback is public. Before leaving <i>negative</i> feedback, we recommend trying to address your concerns with the other community member.  Often misunderstandings can be resolved to the benefit of both parties.";

// $output .= "
// 		<p>{$intro}</p>
// 		<form action=\"\" method=\"post\" name=\"\" id=\"\" class=\"layout1\">
// 			<input type=\"hidden\" id=\"type\" name=\"type\" value=\"{$trade->getType()}\" />
// 			<input type=\"hidden\" id=\"action\" name=\"action\" value=\"create\" />
// 			{$member_options}
			

// 		    <p>
// 	        	<label for=\"category_id\">
// 		            <span>Rating *</span>
// 		            {$dropdown_rating}
// 		        </label>
// 		    </p>	
// 		    </p>	
// 		    	   	<p>
// 	        	<label for=\"comment\">
// 		            <textarea id=\"comments\" name=\"comments\">{$feedback->getComments()}</textarea>
// 		        </label>
// 		    </p>	   	
// 		    <p>
// 	        	<label for=\"amount\">
// 		            {$form_amount}
// 		        </label>
		
// 		    * denotes a required field
// 			<p class=\"summary\">
// 				<input name=\"submit\" id=\"submit\" value=\"Submit\" type=\"submit\" />
				
// 			</p>
// 		</form>";



// 		$ratings = array(0=>"", POSITIVE=>"Positive", NEUTRAL=>"Neutral", NEGATIVE=>"Negative"); 
// 		$form->addElement("select", "rating", "Feedback Rating", $ratings);
// 		$form->addElement("hidden", "about", $member_about->member_id);
// 		$form->addElement("hidden", "author", $_REQUEST["author"]);
// 		$form->addElement("hidden", "mode", $_REQUEST["mode"]);
// 		$form->addElement("hidden", "trade_id", $_REQUEST["trade_id"]);
// 		$form->addElement('static', null, 'Comments', null);
// 		$form->addElement('textarea', 'comments', null, array('cols'=>60, 'rows'=>4, 'wrap'=>'soft'));
// 		$form->addElement('submit', 'btnSubmit', 'Submit');

// 		//
// 		// Define form rules
// 		//
// 		$form->registerRule('verify_selection','function','verify_selection');
// 		$form->addRule('rating', 'Choose a rating', 'verify_selection');

// 		//
// 		// Then check if we are processing a submission or just displaying the form
// 		//
// 		if ($form->validate()) { // Form is validated so processes the data
// 		   $form->freeze();
// 		 	$form->process("process_data", false);
// 		} else {
// 		   $p->DisplayPage($form->toHtml());  // just display the form
// 		}
	}catch(Exception $e){
		$cStatusMessage->Error($e->getMessage());
	}

	$p->page_title = $page_title;
	$p->DisplayPage($output);

function ProcessData ($field_array) {
    global $feedback, $p, $member_about, $member, $cStatusMessage, $cUser;
    $feedback->Build($field_array);
    if($feedback->getRating()<1) {
        $cStatusMessage->Error("Enter a rating.");
        return false;
    }
 
    $feedback_id = $feedback->Save();

    if($feedback_id) {

                //log events done by admins
        if($cUser->getMode() == USER_MODE_ADMIN AND LOG_LEVEL > 0){
            //      $keys_array = array('admin_id', 'category', 'action', 'ref_id', 'note');
            $field_array=array();
            $field_array['admin_id'] = $cUser->getMemberId();
            $field_array['category'] = LOG_FEEDBACK;
            $field_array['action'] = LOG_FEEDBACK_BY_ADMIN;
            $field_array['ref_id'] = $feedback_id;
            $field_array['note'] = "";
            $log_entry = new cLogging ($field_array);
            $log_entry->Save();
        }   

        //$output = "Your feedback has been recorded.";
        return $feedback_id;
    } else {
        throw new Exception("There was an error recording your feedback.");
    }
}
// include_once("classes/class.feedback.php");
//include("includes/inc.forms.validation.php");
//
// Define form elements
//


?>
