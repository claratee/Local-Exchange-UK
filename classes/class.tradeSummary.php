<?php
class cTradeSummary{
	//CT rebuilt
	private $member_id;
	private $count;
	private $amount;
	private $last_date; //short date

    /**
     * @param mixed $member_id
     *
     * @return self
     */


	//
	function  __construct ($values=null) {
		//global $cDB, $site_settings;
		//$this->setMemberId($member_id);
		//$this->Load($member_id);
	}
//this doesnt take condition but the member_id...
	public function Load($member_id){
		global $cDB, $site_settings, $cQueries;
       // $this->setMemberId($member_id);
        $string_query = "SELECT
            m.member_id AS member_id,
            m.balance AS balance,
            f.amount AS amount,
            f.count AS count,
            t.trade_date AS last_date
        FROM
            lets_member m
        LEFT JOIN(
            SELECT
                \"{$member_id}\" as member_id,
                SUM(amount) AS amount,
                COUNT(1) AS count
            FROM
                lets_trades
            WHERE (member_id_from  = \"{$member_id}\" OR member_id_to  = \"{$member_id}\") 
            AND 
            NOT TYPE = \"R\" 
            AND NOT STATUS = \"R\"
        ) f ON m.member_id=f.member_id
        LEFT JOIN(
            SELECT
                \"{$member_id}\" as member_id,
                trade_date as trade_date
            FROM
                lets_trades
            WHERE (member_id_from  = \"{$member_id}\" OR member_id_to  = \"{$member_id}\") 
            AND 
            NOT TYPE = \"R\" 
            AND NOT STATUS = \"R\" ORDER BY trade_date DESC LIMIT 1
        ) t ON m.member_id=t.member_id
        WHERE
            m.member_id = \"{$member_id}\" LIMIT 1";
		$query = $cDB->Query($string_query);
		if($row = $cDB->FetchArray($query)) {
			$this->Build($row);
            //print_r($field_array);
			return true;
		}
		return false;
	}
	public function Build($field_array){
            $this->setMemberId($field_array['member_id']);
            $this->setCount($field_array['count']);
			$this->setAmount($field_array['amount']);
			$this->setLastDate($field_array['last_date']);
	}
    function Display () {
        //summary element for use on summary page
        return (empty($this->getLastDate())) ? "No exchanges yet" : "<a href=\"trade_history.php?member_id={$this->getMemberId()}\">{$this->getCount()} exchanges total</a> for a sum of {$this->getAmount()} ". strtolower(UNITS) . ", last traded on ". $this->getLastDate();
    }


    /**
     * @return mixed
     */
    public function getMemberId()
    {
        return $this->member_id;
    }

    /**
     * @param mixed $member_id
     *
     * @return self
     */
    public function setMemberId($member_id)
    {
        $this->member_id = $member_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param mixed $count
     *
     * @return self
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     *
     * @return self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastDate()
    {
        return $this->last_date;
    }

    /**
     * @param mixed $last_date
     *
     * @return self
     */
    public function setLastDate($last_date)
    {
        $this->last_date = $last_date;

        return $this;
    }
}
?>