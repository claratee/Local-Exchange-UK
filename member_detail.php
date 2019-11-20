<?php
include_once("includes/inc.global.php");
//include_once("class.trade.php");
include_once("classes/class.listing.php");
$p->site_section = PROFILE;

$cUser->MustBeLoggedOn();

$is_success = false;
$member = new cMember();
$member_id = (!empty($_REQUEST["member_id"]))? $cDB->EscTxt($_REQUEST["member_id"]) : $member_id=$cUser->getMemberId();

if($cUser->getMode()=="admin"){
    $condition = "m.member_id=\"{$member_id}\" LIMIT 1";
} else{
    $condition = "m.member_id=\"{$member_id}\" and status=\"A\" LIMIT 1";
}   

$is_success = $member->Load($condition); 
if(!$is_success){
    $p->page_title = "Member Not Found";
    $cStatusMessage->Error("Member not found or is inactive.");
}else{


    if($member->getStatus() == "I") {
        $cStatusMessage->Info("This member is INACTIVE. They cannot log in and their profile and listings are hidden from view for non-admin users.");
    }
    if($member->getRestriction() == "1") {
        $cStatusMessage->Info("This member is RESTRICTED. They can only earn, and not spend.");
    }
    $output = "";


    if($is_success){

        $p->page_title = "{$member->getDisplayName()} ({$member_id})";

    		$stats = new cTradeSummary();
            //$condition = "m.member_id=\"{$member_id}\"";
            $stats->Load($member_id);

            $feedback = new cFeedbackSummary();
            $feedback->Load("member_id_about = \"{$member_id}\"");
            //$this->setFeedback($feedback); 

            $mBalance = $member->getBalance() . " " . strtolower(UNITS);
     
            //CT todo: date format
            $mSince = $member->getJoinDate();
            
            //CT primary person
            //print_r($member->getPerson());
            $pName = "{$member->getPerson()->getFirstName()} {$member->getPerson()->getLastName()}";



            $pAge = ARRAY_AGE[$member->getPerson()->getAge()];
            $pSex = ARRAY_SEX[$member->getPerson()->getSex()];


            $pAbout = (empty($member->getPerson()->getAboutMe())) ? '<em>No description supplied.</em>' : "\"" .stripslashes($member->getPerson()->getAboutMe()) . "\"";
            $pEmail = "<a href=\"mailto:{$member->getPerson()->getEmail()}\" class=\"normal\">{$member->getPerson()->getEmail()}</a>";
            $pPhones = $member->getPerson()->getAllPhones();
            $renewal = "{$member->getExpireDate()} {$member->makeExpireRelativeDate()}";
            //CT template

            // CT TODO make better this is aweful
            // append secondary member if exists
            
            
            $joint_member_text ="";
            //print_r($member->getJointPerson()->getDirectoryList());
            if ($member->getAccountType() == "J" AND !is_null($member->getJointPerson())) {

                if($member->getJointPerson()->getDirectoryList() == "N"){
                    if(($cUser->getMemberId() == $member->getMemberId()) OR $cUser->getMode()=="admin"){
                        $joint_member_text ="
                        <p class=\"line\">
                            There is a joint member, but hidden from view. <a href=\"member_joint_edit.php?member_id=0634\" class=\"button edit\"><i class=\"fas fa-pencil-alt\"></i> edit or remove joint member</a>
                        </p> 
                        ";
                    }
                }else{
                    $sName = "{$member->getJointPerson()->getFirstName()} {$member->getJointPerson()->getLastName()}";
                

                    $joint_member_text ="
                        <h3>Joint Member</h3>
                        <div class=\"group contact\">
                            <p><a href=\"member_joint_edit.php?member_id={$member->getMemberId()}\" class=\"button edit\"><i class=\"fas fa-pencil-alt\"></i> edit or remove joint member</a></p>
                            <p class=\"line\">
                                <span class=\"label\">Name: </span>
                                <span class=\"value\">{$sName}</span>
                            </p>        <p class=\"line\">
                                <span class=\"label\">Email: </span>
                                <span class=\"value\"><a href=\"mailto:{$member->getJointPerson()->getEmail()}\" class=\"normal\">{$member->getJointPerson()->getEmail()}</a></span>
                            </p>
                            <p class=\"line\">
                                <span class=\"label\">Phone: </span>
                                <span class=\"value\">{$member->getJointPerson()->getAllPhones()}</span>
                            </p>
                        </div>  
                        ";
                }
                    
                 
            } 
            // but yucky...but gets it done
            include_once (TEMPLATES_PATH . '/menu_quick_edit.php');

            $output .="
                
                <div class=\"profile-wrap detail\">
                    <div class=\"profile-inner\">
                        <div class=\"profile-text\">
                            <!--START include member_summary -->

                            <div class=\"member-summary\">    
                                
                                <div class=\"group basic\">
                                    <p class=\"line\">
                                        <span class=\"label\">Location: </span>
                                        <span class=\"value\">{$member->getDisplayLocation()}</span>
                                    </p>    
                                    <p class=\"line\">
                                        <span class=\"label\">Member since: </span>
                                        <span class=\"value\">{$mSince}</span>
                                    </p>
                                    <p class=\"line\">
                                        <span class=\"label\">Renewal date: </span>
                                        <span class=\"value\">{$renewal}</span>
                                    </p>
                                </div>      
                                <div class=\"group activity\">
                                    <p class=\"line\">
                                        <span class=\"label\">Balance: </span>
                                        <span class=\"value\">{$mBalance}</span>
                                    </p>        
                                    <p class=\"line\">
                                        <span class=\"label\">Activity: </span>
                                        <span class=\"value\">{$stats->Display()}</span>
                                    </p>
                                    <p class=\"line\">
                                        <span class=\"label\">Feedback: </span>
                                        <span class=\"value\">{$feedback->Display()}</span>
                                    </p>
                                </div>
                            </div>
                            <!--END include member_summary -->
                            <!--START include person_summary -->

                            <div class=\"person-summary\">    
                                <div class=\"group contact\">
                                    <p class=\"line\">
                                        <span class=\"label\">Name: </span>
                                        <span class=\"value\">{$pName}</span>
                                    </p>        
                                    <p class=\"line\">
                                        <span class=\"label\">Email: </span>
                                        <span class=\"value\">{$pEmail}</span>
                                    </p>
                                    <p class=\"line\">
                                        <span class=\"label\">Phone: </span>
                                        <span class=\"value\">{$pPhones}</span>
                                    </p>
                                </div>  
                                <div class=\"group social\">
                                    <p class=\"line\">
                                        <span class=\"label\">Age: </span>
                                        <span class=\"value\">{$pAge}</span>
                                    </p>
                                    <p class=\"line\">
                                        <span class=\"label\">Gender: </span>
                                        <span class=\"value\">{$pSex}</span>
                                    </p>
                                </div>

                            </div>
                            <!--END include person_summary -->
                            {$joint_member_text}
                        </div>
                        <div class=\"profile-avatar\">
                            
                            photo
                        </div>
                    </div>
                    <div class=\"profile-about\">
                       {$pAbout}
                    </div>

                </div>
            ";
                
    $is_success = false;
    //$member = new cMemberSummary();
    $listing_id = (empty($_REQUEST['listing_id'])) ? "" : $_REQUEST['listing_id'];

    if(!empty($listing_id)){
        $condition = "listing_id={$listing_id}";
        $listing->Load($condition);
        if(!empty($listing->__get('listing_id'))) $is_success = true; 
    }    
            

     if(!empty($member_id)){
     	// CT show offers
    	$output .= "<h2>" . OFFER_LISTING_HEADING . "</h2>";
        
    	$listings = new cListingGroup();
        // Load($member_id, $category_id, $timeframe, $include_expired, $status, $type)

        $condition = $listings->makeFilterCondition($member_id, OFFER_LISTING_CODE, null, null, null, null);
    	$listings->Load($condition);

    	$output .= $listings->Display(false);
    	// 	// CT Show want
        $output .= "<h2>" . WANT_LISTING_HEADING . "</h2>";
    	$listings = new cListingGroup();
        $condition = $listings->makeFilterCondition($member_id, WANT_LISTING_CODE, null, null, null, null);
        $listings->Load($condition);
    	$output .= $listings->Display(false);
     }
    } 
}
$p->DisplayPage($output); 

include("includes/inc.events.php");
