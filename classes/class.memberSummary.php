<?php
//CT this is the most complicated version of the cMember - 
// for the public detail page for member. Images, feedback, activity etc
class cMemberSummary extends cMember {
    //extra properties
    private $photo;
    private $feedback; // ct activity summary object
    private $stats; // ct activity summary object
 
    //CT load member from db
    public function Load($condition) {
        global $cDB, $cStatusMessage, $cQueries;
        //clean it - needed?

        //CT composite all the summary/profile calls together for efficiency
        //TODO - put stats in here
        
        $query = $cDB->Query("{$cQueries->getMySqlMember($condition)} LIMIT 1");

        //CT this is a loop but there should only be 1
        if($field_array = $cDB->FetchArray($query)) // Each of our SQL results
        {
            //$cStatusMessage->Error(print_r($row, true));
            $is_success = $this->Build($field_array);
            //print_r($is_success);
            if($is_success) {
                $feedback = new cFeedbackSummary();
                $condition = "member_id_about = {$this->getMemberId()}";
                $feedback->Load($condition);
                $this->setFeedback($feedback); 
            }
            // CT extra bits 
                    
        
           //CT choose photo. todo: fix filetype, this is weird.
            if(!empty($values['photo'])){
                $photo = UPLOADS_PATH . stripslashes($values["photo"]);
            }else{
                $photo = IMAGES_PATH . "user-placeholder.svg";
            } 
            $this->setPhoto($photo); 

            return true;
        }
        return false;
    }

    public function Display () {
    $string = "
        <h3>{$this->MemberLink()} {$this->getDisplayName()}</h3>
        <p>{$this->getDisplayLocation()}</p>";        
    return $string; 

    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $photo
     *
     * @return self
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * @param mixed $feedback
     *
     * @return self
     */
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @param mixed $stats
     *
     * @return self
     */
    public function setStats($stats)
    {
        $this->stats = $stats;

        return $this;
    }
}
?>