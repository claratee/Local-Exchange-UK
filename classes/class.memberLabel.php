<?php
//CT for address labels
class cMemberLabel extends cMember {

    // helper function to return the month of expiry
    public function getMonthOfExpiry(){
        $month = date("F",strtotime($this->getExpireDate()));
        $month = strtoupper($month);
        return $month;
    }
    public function Display () {
        $string = "
            <div style=\"border: solid 2px #999; padding:1em; margin-bottom: 1em;\">
                <p>
                    #{$this->getMemberId()}
                </p>
                <p>
                    <strong>{$this->getDisplayName()}</strong>
                </p>
                <p>
                    {$this->getPerson()->getAddressStreet1()}<br />
                    {$this->getPerson()->getAddressStreet2()}<br />
                    {$this->getPerson()->getAddressCity()}<br />
                    {$this->getPerson()->getAddressStateCode()}, {$this->getPerson()->getAddressPostCode()}<br />
                </p>
                <!--
                <p>
                    Telephone: {$this->getDisplayPhone()}
                    <div>Emails: {$this->getDisplayEmail()}</div>
                </p> -->
                <p><br /><small>
                    Renewal: {$this->getMonthOfExpiry()}</small>
                </p>
            </div>";        
        return $string; 

    }
}

?>