<?php

class cMemberGroup extends cBasic2 {
    //CT this should be private 
    private $members; //CT array of member objects
    private $option; //CT option filter for results
    private $order; //CT order of results

    // public function __construct($values=null)
    // {
    //     if(!empty($values)) {
    //         $this->Build($values);
    //     }

    //     return $this;
    // }
    public function getCount(){
        return sizeof($this->getMembers());
    }


    public function Build($field_array){
        $members = array();

        foreach($field_array as $field) // Each result
        {
            $members[] = new cMember($field);
        }
        $this->setmembers($members);  // this will be an array of cmembers
    }
    public function makeSettingFromOption(){
        //settings: condition, label, actions
        switch($this->getOption()){
            case "all":
                //all - no filter
                $condition = "1";
                $label = "Members: both active and inactive";
                $actions_keys = array('edit', 'status');
            break;
            case "inactive":
                //all inactive
                $condition = "m.status = 'I'";
                $label = "Members: inactive only";
                $actions_keys = array('edit', 'status');
            break;
            case "restricted":
                //all active non-fund restricted
                $condition = "m.restriction = '1' AND m.status = 'A' AND m.account_type != 'F'";
                 $label = "Members: restricted";
                $actions_keys = array('edit', 'restrict');
           break;
            case "not-restricted":
                //all active non-fund non-restricted
                $condition = "m.restriction = '0' AND m.status = 'A' AND m.account_type != 'F'";
                $label = "Members: not restricted";
                $actions_keys = array('edit', 'restrict');

            break;
            case "role-committee":
                //all active non-fund non-restricted
                $condition = "m.member_role = 1 AND m.status = 'A' AND m.account_type != 'F'";
                $label = "Members: committee role";
                $actions_keys = array('edit');
            
            break;
            case "role-admin":
                //all active non-fund non-restricted
                $condition = "m.member_role = 2 AND m.status = 'A' AND m.account_type != 'F'";
                $label = "Role: admin";
                $actions_keys = array('edit');

            break;
            case "active":
            default:
                //all active non-fund
                $condition = "m.status = 'A' AND m.account_type != 'F'";
                $label = "Members";
                $actions_keys = array('edit', 'status');

        }
        return array($condition, $label, $actions_keys);
    }

    function Load($condition, $order_by="p.first_name ASC") {
        global $cDB, $cStatusMessage, $cQueries;
        $order_by = " ORDER BY " . $order_by;
        $string_query = $cQueries->getMySqlMember($condition . $order_by);

        $query = $cDB->Query($string_query);
        $i=0;
        $field_array = array();
        while($row = $cDB->FetchArray($query)) $field_array[] = $row;            
        //build from vars
 
        $this->Build($field_array);
    }   
    

    function PrepareMemberDropdown($select_name = "member_id", $member_id=null){
        global $p, $cUser;
        $array = array();
        foreach($this->getMembers() as $member) {
            //print_r($category->getCategoryName());
            $status_text =  ($member->getStatus()=="I") ? " - INACTIVE" : "";
            //$array[$member->getMemberId()] = "#{$member->getMemberId()}: {$member->getDisplayName()}";
            $array[$member->getMemberId()] = "{$member->getDisplayName()} (#{$member->getMemberId()}{$status_text})";
        }
        $output = $p->PrepareFormSelector($select_name, $array, "-- Select member --", $member_id);
        return $output;
    }   

    
    // CT MOVED email updates to
    


    // CT this looks dangerous. avoid using... 
    // Use of this function requires the inclusion of class.listing.php
    // public function ExpireListings4InactiveMembers() {
    //     if(empty($this->getMembers())) {
    //         $condition="`member_id`=\"\"";
    //         if(!$this->Load($condition)){
    //             return false;
    //         }
    //     }
        
    //     foreach($this->members as $member) {
    //         if($member->DaysSinceLastTrade() >= MAX_DAYS_INACTIVE
    //         and $member->DaysSinceUpdatedListing() >= MAX_DAYS_INACTIVE) {
    //             $offer_listings = new cListingGroup(OFFER_LISTING);
    //             $want_listings = new cListingGroup(WANT_LISTING);
                
    //             $offered_exist = $offer_listings->LoadListingGroup(null, null, $member->member_id, null, false);
    //             $wanted_exist = $want_listings->LoadListingGroup(null, null, $member->member_id, null, false);
                
    //             if($offered_exist or $wanted_exist) {
    //                 $expire_date = new cDateTime("+". EXPIRATION_WINDOW ." days");
    //                 if($offered_exist)
    //                     $offer_listings->ExpireAll($expire_date);
    //                 if($wanted_exist)
    //                     $want_listings->ExpireAll($expire_date);
                
    //                 if($member->person[0]->email != null) {
    //                     mail($member->person[0]->email, "Important information about your ". SITE_SHORT_TITLE ." account", wordwrap(EXPIRED_LISTINGS_MESSAGE, 64), "From:". EMAIL_ADMIN); 
    //                     $note = "";
    //                     $subject_note = "";
    //                 } else {
    //                     $note = "\n\n***NOTE: This member does not have an email address in the system, so they will need to be notified by phone that their listings have been inactivated.";
    //                     $subject_note = " (member has no email)";
    //                 }
                    
    //                 mail(EMAIL_ADMIN, SITE_SHORT_TITLE ." listings expired for ". $member->member_id. $subject_note, wordwrap("All of this member's listings were automatically expired due to inactivity.  To turn off this feature, see inc.config.php.". $note, 64) , "From:". EMAIL_ADMIN);
    //             }
    //         }
    //     }
    // }
    public function Display(){
        $string = "";
        foreach ($this->getMembers() as $member) {
            $string .= $member->Display();
        }
        return $string;
    }

    
    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     *
     * @return self
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @param mixed $members
     *
     * @return self
     */
    public function setMembers($members)
    {
        $this->members = $members;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * @param mixed $option
     *
     * @return self
     */
    public function setOption($option)
    {
        $this->option = $option;

        return $this;
    }
}

?>