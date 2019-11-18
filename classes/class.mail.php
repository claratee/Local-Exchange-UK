<?php

class cMail extends cBasic2 {

	//CT new class - attempt to fix the sender for all mails in system
    
    private $recipients;  // array of members to be sent. optimise for multiple recipient

    //email stuff
	private $reply_to_name;
	private $reply_to_email;
	//private $php_version; 
    //private $to_name; 
    private $subject; 
    private $message;
    //the final step before sending mail
    private $headers;
    private $formatted_subject; 
    private $formatted_message;
/*
// emails that need to use this class
anon - contact admin
member - member to member
admin - send to everyone (group->item) - should these be stored and shown on pages? yes!
system - weekly updates (group->item)
system - invoiced
system - weekly updates
system - reset password
system - site created
//NEW
member - contact admin (new)
anon - apply for membership (new)
system - invoice rejected (new)
system - new payment made to you (new)
system - new feedback to you (new)
system - core - new BAD feedback of member (new)

// nice to have
monthly community health (sent to member secretary) (group->item)
monthly economy (sent to treasurer) (group->item)
newsletter - new download available (group->item)

*/

//CT dont need the constructor, it should be in basic.
	// function __construct($field_array=null){
 //        global $site_settings;
	// 	//evoke build with the known bits
 //        //$field_array['email_from'] = $site_settings->getKey('EMAIL_FROM');
 //        //$field_array['email_from_name'] = $site_settings->getKey('EMAIL_FROM_NAME');
 //        //print("email " . $site_settings->getKey('EMAIL_FROM'));
 //        //subject
 //        //body
 //        //recipients display name, email
 //        if(!empty)
	// 	$this->Build($field_array);
	// }
	// // function Build($variables){
	// //     parent::Build($variables);
	// // }



	function sendMail() {
		global $cStatusMessage, $site_settings;

        //test if it can be done
        if(empty($this->getSubject())) throw new Exception('Missing subject.');
        if(empty($this->getMessage())) throw new Exception('Missing message.');

        $this->setHeaders($this->makeHeaders());     
        $this->setFormattedSubject($this->makeFormattedSubject());     
        $this->setFormattedMessage($this->makeFormattedMessage());     
        $fail_count=0;
        $success_count=0;
        foreach ($this->getRecipients() as $recipient) {
            //$to = "\"{$recipient['display_name']}\" {$recipient['email']}";
            $to = "\"{$recipient['display_name']}\" <{$recipient['email']}>";
            //$is_success = mail($to, $this->getFormattedSubject(), $this->getFormattedMessage(), $this->getHeaders());
           // $is_success = mail($to, "$this->getFormattedSubject()", $this->getFormattedMessage(), $this->getHeaders());
            print_r($to);
            $is_success = mail($to, $this->getFormattedSubject(), $this->getFormattedMessage(), $this->getHeaders());
            //$is_success = true;
            // print($this->getFormattedMessage());
            if($is_success) $success_count++;
            else {
                $fail_count++;
                if(!DEBUG) $cStatusMessage->Error("Could not send to " . $recipient['email'] . " ? " . $recipient['display_name']);
            }
        }
        $total=sizeof($this->getRecipients());
        
        $note = "Attempted mail: {$total} success: {$success_count}, failed: {$fail_count}";
        if(DEBUG) $cStatusMessage->Info($note);
        //CT logs - just in case its used for spam, we need a record
        // dont think we need to log every sendemail...was here for debugg
        // if(LOG_LEVEL > 0) {//Log if enabled

       /*$keys_array = array('admin_id', 'category', 'action', 'ref_id', 'note');
             $field_array=array();
             $field_array['category'] = LOG_SEND_ANNOUNCEMENT;
             $field_array['action'] = "A";
             $field_array['ref_id'] = "";
             $field_array['note'] = $note;
             $log_entry = new cLogging ($field_array);
             $log_entry->Save();
        }*/
    

        return empty($fail_count);
        
	}
    function makeHeaders(){
        //CT always send FROM the domain it has authority to use, REPLY-TO can be dynamic 
        global $site_settings;
        $string = "MIME-Version: 1.0\r\n";
        $string .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $string .= "From: \"{$site_settings->getKey('EMAIL_FROM_NAME')}\" <{$site_settings->getKey('EMAIL_FROM')}>\r\n";
        $string .= "Reply-To: \"{$this->getReplyToName()}\" <{$this->getReplyToEmail()}>\r\n";
        $string .= "X-Mailer: " . phpversion();
        return $string;
    }
    function makeFormattedSubject(){
        global $site_settings;
        return "[{$site_settings->getKey('SITE_SHORT_TITLE')}] {$this->getSubject()}";
    }
    function makeFormattedMessage(){
        //CT uses a template to make it look nice
        global $p;
        //CT 
        $string = $this->getMessage();
        $string = $p->ReplacePlaceholders($string);
         
        $template = file_get_contents(TEMPLATES_PATH . '/mail_admin.html', TRUE);
        $template = $p->ReplaceVarInString($template, '$message', $string);
        $template = $p->stripLineBreaks($string);
       return $template;
    }
    function makeHtmlFromLineBreaks(){
        //CT uses a template to make it look nice
        global $p;
        $paragraphs = preg_split('/\n+/', $this->getMessage());
        $string = "";
        foreach($paragraphs as $parag)
        {
            if(strlen($parag) > 0){
                $string .= "<p>{$parag}</p>";
            }
        }
        //$string = $p->ReplaceVarInString($string, '$to_name', $this->getToName());
        return $string;
    }
    //logn winded name...just not sure about alternative
    function buildRecipientsFromMemberObject($member, $scope="all"){
        global $cStatusMessage;

        $recipients=array();
        $recipients[0] = array(
            'member_id'=>$member->getMemberId(), 
            'display_name'=>"{$member->getPerson()->getFirstName()} {$member->getPerson()->getLastName()}", 
            'email'=>"{$member->getPerson()->getEmail()}" 
        );
        //include joint member, for the most part. option just to use primary by limiting scope
        if($member->getAccountType() == "J" && $scope=="all") {
            $recipients[1] = array(
                'member_id'=>$member->getMemberId(), 
                'display_name'=>"{$member->getJointPerson()->getFirstName()} {$member->getJointPerson()->getLastName()}", 
                'email'=>$member->getJointPerson()->getEmail() 
            );
        }  
        $this->setRecipients($recipients);
    }
    // loads from a view table that includes joint members
    //you can put in a limit witht eh condition for testing
    function loadRecipients($condition="1"){
        global $cDB, $cStatusMessage;

        $fields = array(
            'm.member_id'=>'member_id', 
            'm.display_name'=>'display_name', 
            'm.email'=>'email'
        );
        $string_query = $cDB->BuildSelectQuery(DATABASE_VIEW_CONTACTS . " m", $fields, "", $condition);
        //print_r($string_query);
        $query = $cDB->Query($string_query);
        //
        $recipients = array();
        while($row = $cDB->FetchArray($query)) $recipients[] = $row;
        
       
        $this->setRecipients($recipients);
    }


    //CT not sure if this should be here...but best place for now.
public function EmailListingUpdates($timeframe) {
        global $cStatusMessage, $site_settings;

        //$mailer = new cMail();
        //load members if not loaded
        try{
            switch ($timeframe) {
                case DAILY:
                    $period = "day";
                break;
                case WEEKLY:
                    $period = "week";
                break;           
                case MONTHLY:
                    $period = "monthly";
                break;
                default:
                    $period = "all time";
                
            }
            //CT get listings conditional vars
            $member_id = "%";
            $category_id = "%";
            $keyword = "%";
            
            //CT OFFERED
            $listings = new cListingGroup();
            //CT UX - the period should be double what was specified...give better view to items, so they are visible for 2 weeks not 1 week.
            //CT O=offered
            //makeFilterCondition(null, null, null, $timeframe);
            //offered
            // instantiate new cOffer objects and load them
            //CT dont send if nothing to send. to_send gets set to true if there is something to send, otherwise no.
            $to_send=false;

            $listings = new cListingGroup();
            $condition = $listings->makeFilterCondition(null,null, null, $timeframe*2, "O");
           if($listings->Load($condition)){
                $offered_text .= $listings->Display(true);
                $to_send=true;
            }else{
                 $offered_text .= "<p>No new offered listings posted in last {$period}.</p>";
            }

            //CT UX - the period should be double what was specified...give bugger to items, so they are visible for 2 weeks not 1 week. still gets sent out at the interval specified.
            //wants
            // instantiate new cOffer objects and load them
            $wanted_text = "<h2>Wanted listings</h2><a href=\"" . HTTP_BASE ."/listings.php?type=W\">View all wanted listings</a>";
            //show_id=true
            $listings = new cListingGroup();
            $condition = $listings->makeFilterCondition(null,null, null, $timeframe*2, "W");
           if($listings->Load($condition)){
                $wanted_text .= $listings->Display(true);
                $to_send=true;
            }else{
                 $wanted_text .= "<p>No new wanted listings posted in last {$period}.</p>";
            }
 
            if($to_send){
                $field_array = array();
                $field_array['subject'] = "New and updated listings during the last {$period}";
                $field_array['message'] = "{$site_settings->getKey('EMAIL_LISTING_HEADER')}{$offered_text}{$wanted_text}{$site_settings->getKey('EMAIL_LISTING_FOOTER')}";
                //print($field_array['message']);
                $this->Build($field_array);
                //print_r($message_array['message']);
                //$mailer = new cMail($message_array);
                //CT send to ALL users with role ADMIN - so security risk user "admin" can go away.
                $condition="`email_updates`={$timeframe} ORDER BY member_id";
                $this->loadRecipients($condition);
                return $this->sendMail();
            }

        } catch(Exception $e){
            //CT should fail silently - for not, want to see it for debugging
            $cStatusMessage->Error($e->getMessage());
        }
    }


    /**
     * @return mixed
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param mixed $recipients
     *
     * @return self
     */
    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReplyToEmail()
    {
        return $this->reply_to_email;
    }

    /**
     * @param mixed $email_from
     *
     * @return self
     */
    public function setReplyToEmail($reply_to_email)
    {
        $this->reply_to_email = $reply_to_email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReplyToName()
    {
        return $this->reply_to_name;
    }

    /**
     * @param mixed $email_from_name
     *
     * @return self
     */
    public function setReplyToName($reply_to_name)
    {
        $this->reply_to_name = $reply_to_name;

        return $this;
    }

    // /**
    //  * @return mixed
    //  */
    // public function getPhpVersion()
    // {
    //     return $this->php_version;
    // }

    // *
    //  * @param mixed $php_version
    //  *
    //  * @return self
     
    // public function setPhpVersion($php_version)
    // {
    //     $this->php_version = $php_version;

    //     return $this;
    // }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     *
     * @return self
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        global $cDB, $p;
        $message = $this->message;
        return $p->stripLineBreaks($message);
    }

    /**
     * @param mixed $message
     *
     * @return self
     */
    public function setMessage($message)
    {
        global $cDB;
        $this->message = $message;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param mixed $headers
     *
     * @return self
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormattedSubject()
    {
        return $this->formatted_subject;
    }

    /**
     * @param mixed $formatted_subject
     *
     * @return self
     */
    public function setFormattedSubject($formatted_subject)
    {
        $this->formatted_subject = $formatted_subject;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormattedMessage()
    {
        return $this->formatted_message;
    }

    /**
     * @param mixed $formatted_message
     *
     * @return self
     */
    public function setFormattedMessage($formatted_message)
    {
        $this->formatted_message = $formatted_message;

        return $this;
    }
}

?>