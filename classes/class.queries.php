<?php
//CT holdit class for fragments of db query for re-use. but messy - liable to change when I figure out what I am doing
class cQueries
{
/*    //fragments
	public $mysql_member_display_name = "concat(p.first_name, \" \", p.last_name, if(m.account_type=\"J\") concat(\" and \", j.first_name, \" \", j.last_name),\"\")) as display_name";
    
    public $mysql_member_display_phone = "concat(p.phone1_number, if(m.account_type=\"J\" and j.phone1_number is not null, concat(\" (\", p.first_name, \")\",\", \", j.phone1_number, \" (\", j.first_name, \")\"), \"\")) as display_phone";

    public $mysql_member_display_email = "concat(\"<a href='mailto:\", p.email, \"'>\", p.email, \"</a>\", if(m.account_type=\"J\" and p.email is not null, concat(\", <a href='mailto:\", j.email, \"'>\", j.email, \"</a>\"), \"\")) as display_email";
*/
    // CT use it in summary page 
    public $mysql_feedback_counts = "member_id_about as member_id, COUNT(CASE WHEN `rating` = 1 THEN 1 END) AS feedback_negative, COUNT(CASE WHEN `rating` = 2 THEN 1 END) AS feedback_neutral, COUNT(CASE WHEN `rating` = 3 THEN 1 END) AS feedback_positive, COUNT(1) AS feedback_total ";


    // // here for reuse - optimised for contact details
    // function getMySqlMemberConcise($condition, $order_by){

    // 	$query = "SELECT  
    //         m.member_id as member_id,  
    //         {$this->mysql_member_display_name},
    //         {$this->mysql_member_display_phone},
    //         {$this->mysql_member_display_email},
    //         p1.address_street1 as address_street1, 
    //         p1.address_street2 as address_street2, 
    //         p1.address_city as address_city,
    //         p1.address_state_code as address_state_code,
    //         p1.address_country as address_country,
    //         p1.address_post_code as address_post_code, 
    //         m.balance as balance,
    //         m.account_type as account_type, 
    //         m.restriction as restriction, 
    //         m.expire_date as expire_date  
    //         FROM " . DATABASE_MEMBERS . " m 
    //         left JOIN " . DATABASE_PERSONS . " p1 ON m.member_id=p1.member_id 
    //         left JOIN (select * from " . DATABASE_PERSONS . " person where person.primary_member = 'N') p2 on p1.member_id=p2.member_id WHERE {$condition} ORDER BY {$order_by}";
    //     $query = "SELECT  
    //         m.member_id as member_id,  
    //         {$this->mysql_member_display_name},
    //         {$this->mysql_member_display_phone},
    //         {$this->mysql_member_display_email},
    //         p.address_street1 as address_street1, 
    //         p.address_street2 as address_street2, 
    //         p.address_city as address_city,
    //         p.address_state_code as address_state_code,
    //         p.address_country as address_country,
    //         p.address_post_code as address_post_code, 
    //         p.balance as balance,
    //         p.account_type as account_type, 
    //         p.restriction as restriction, 
    //         p.expire_date as expire_date  
    //         FROM lets_view_member_active p 
    //         left JOIN lets_view_person_joint j ON p.member_id=j.member_id where {$condition}";
    //         return $query;
    // }

    function getMySqlNews($condition){
        $query = "SELECT 
            `news_id`,
            `title`,
            `description`,
            `expire_date`,
            `sequence` 
            FROM " . DATABASE_NEWS . " where {$condition} ORDER BY sequence DESC;";
            return $query;

    }
    function getMySqlPerson($condition){
        $query = "SELECT
            person_id, 
            member_id, 
            primary_member, 
            directory_list, 
            first_name, 
            last_name, 
            mid_name, 
            dob, 
            mother_mn, 
            email, 
            phone1_area, 
            phone1_number, 
            phone1_ext, 
            phone2_area, 
            phone2_number, 
            phone2_ext, 
            fax_area, 
            fax_number, 
            fax_ext, 
            address_street1, 
            address_street2, 
            address_city, 
            address_state_code, 
            address_post_code, 
            address_country, 
            about_me, 
            age, 
            sex 
            FROM " . DATABASE_PERSONS . " WHERE {$condition}";
        return $query;
    }

function getMySqlMember($condition){
        $query = "SELECT
    m.balance AS balance,
    m.join_date AS join_date,
    m.member_role AS member_role,
    m.admin_note AS admin_note,
    m.account_type AS account_type,
    m.status AS `status`,
    m.restriction AS restriction,
    m.expire_date AS expire_date,
    m.away_date AS away_date,
    m.member_id AS member_id,
    m.confirm_payments AS confirm_payments,
    m.email_updates AS email_updates,
    m.opt_in_list AS opt_in_list,
    p.person_id AS person_id,
    p.first_name AS first_name,
    p.last_name AS last_name,
    p.email AS email,
    p.phone1_number AS phone1_number,
    p.age AS age,
    p.sex AS sex,
    p.about_me AS about_me,
    p.address_street1 AS address_street1,
    p.address_street2 AS address_street2,
    p.address_city AS address_city,
    p.address_state_code AS address_state_code,
    p.address_post_code AS address_post_code,
    p.address_country AS address_country,
    j.person_id AS j_person_id,
    j.first_name AS j_first_name,
    j.last_name AS j_last_name,
    j.email AS j_email,
    j.phone1_number AS j_phone1_number,    
    j.directory_list AS j_directory_list
FROM
    " . DATABASE_MEMBERS . " m
LEFT JOIN " . DATABASE_PERSONS . " p ON
    m.member_id = p.member_id
LEFT JOIN(
    SELECT
        *
    FROM
        " . DATABASE_PERSONS . "
    WHERE
        primary_member = 'N'
) j
ON
    m.member_id = j.member_id
WHERE
    p.primary_member = 'Y' AND {$condition}";
        return $query;
    }



/*    //CT this is really simple - load what you need to take action
    function getMySqlMemberSelf($condition){
    	$query = "SELECT m.balance as balance, 
            m.member_role as member_role, 
            m.status as status, 
            m.account_type as account_type, 
            m.expire_date as expire_date, 
            m.away_date as away_date, 
            m.restriction as restriction, 
            {$this->mysql_member_display_name}
            FROM 
            " . DATABASE_VIEW_MEMBER . " m 
            {$this->getMysqlJoinsDisplayName()} WHERE {$condition}";
    	return $query;
    }*/
function getMySqlFeedbackSummary($condition){
    $query = "SELECT
    COUNT(
        CASE WHEN `rating` = 1 THEN 1
        END
    ) AS feedback_negative,
    COUNT(
        CASE WHEN `rating` = 2 THEN 1
    END
    ) AS feedback_neutral,
    COUNT(
        CASE WHEN `rating` = 3 THEN 1
    END
    ) AS feedback_positive,
    COUNT(1) AS feedback_total
    FROM
        " . DATABASE_FEEDBACK . " f
    WHERE
        {$condition}";
}


    // function getMySqlTradeSummary($condition){
    //     $query = "
    //         SELECT 
    //         m.member_id as member_id, 
    //         m.balance as balance, 
    //         f.amount as from_amount, 
    //         f.count as from_count, 
    //         t.amount as to_amount, 
    //         t.count as to_count 
    //         from " . DATABASE_MEMBERS . " m
    //         left join 
    //             (SELECT 
    //                 sum(amount) as amount, 
    //                 count(1) as count, 
    //                 member_id_from as member_id
    //              FROM " . DATABASE_TRADES . "
    //              WHERE 
    //                 NOT type=\"R\" AND NOT status=\"R\" GROUP BY member_id_from 
    //             ) f on m.member_id=f.member_id
    //         left join (
    //             SELECT 
    //                 sum(amount) as amount, 
    //                 count(1) as count,
    //                 member_id_to as member_id 
    //             FROM " . DATABASE_TRADES . "
    //             WHERE 
    //                 NOT type=\"R\" AND NOT status=\"R\" GROUP BY member_id_to
    //             ) t on m.member_id=t.member_id where {$condition}";
    //     return $query;
    // }
    //put 'trade_' prefix to stop name collisions, this table often joined
    function getMySqlTrade($condition){
        $query="SELECT 
             t.trade_id as trade_id, 
           t.trade_date as trade_date, 
            t.status as status, 
            t.member_id_from as member_id_from, 
            t.member_id_to as member_id_to, 
            t.amount as amount, 
            t.description as description, 
            t.type as type, 
            t.category_id as category_id, 
            t.member_id_author as member_id_author, 
            c.description as category_name,
            f.member_id_about as feedback_member_id_about, 
            f.member_id_author as feedback_member_id_author, 
            f.comment as feedback_comment, 
            f.rating as feedback_rating
            FROM " . DATABASE_TRADES . " t 
            LEFT JOIN " . DATABASE_CATEGORIES . " c ON t.category_id = c.category_id
            LEFT JOIN " . DATABASE_FEEDBACK . " f ON t.trade_id = f.trade_id 
            LEFT JOIN " . DATABASE_MEMBERS . " m ON t.member_id_to = m.member_id 
            LEFT JOIN " . DATABASE_MEMBERS . " n ON t.member_id_from = n.member_id 
            WHERE {$condition} 
            ORDER BY trade_id DESC;";
        return $query;
    }
    //     function getMySqlTrade($condition){
    //     $query="SELECT 
    //          t.trade_id as trade_id, 
    //        t.trade_date as trade_date, 
    //         t.status as status, 
    //         t.member_id_from as member_id_from, 
    //         t.member_id_to as member_id_to, 
    //         t.amount as amount, 
    //         t.description as description, 
    //         t.type as trade_type, 
    //         t.category_id as category_id, 
    //         t.member_id_author as member_id_author, 
    //         c.description as category_name,
    //         f.feedback_id as feedback_id_buyer, 
    //         g.feedback_id as feedback_id_seller, 
    //         f.member_id_about as feedback_member_id_about, 
    //         f.member_id_author as feedback_member_id_author, 
    //         f.comment as feedback_comment, 
    //         f.rating as feedback_rating
    //         FROM " . DATABASE_TRADES . " t 
    //         LEFT JOIN " . DATABASE_CATEGORIES . " c ON t.category_id = c.category_id
    //         LEFT JOIN " . DATABASE_FEEDBACK . " f ON t.trade_id = f.trade_id 
    //         LEFT JOIN " . DATABASE_FEEDBACK . " g ON t.trade_id = g.trade_id 
    //         WHERE {$condition} 
    //         ORDER BY trade_id DESC;";
    //     return $query;
    // }
    function getMySqlListing($condition){
        $query="SELECT 
            l.member_id as member_id, 
            l.listing_id as listing_id, 
            l.title as title, 
            l.type as type, 
            l.description as description, 
            l.rate as rate,  
            l.listing_date as listing_date, 
            l.status as status, 
            l.expire_date as expire_date, 
            l.reactivate_date as reactivate_date,
            p.first_name as first_name,
            p.last_name as last_name,
            j.first_name as j_first_name,
            j.last_name as j_last_name,
            p.address_post_code as address_post_code,
            p.address_street2 as address_street2,
            l.category_id as category_id,
            c.description as category_name
            FROM " . DATABASE_LISTINGS . " l 
            LEFT JOIN " . DATABASE_MEMBERS . " m ON l.member_id=m.member_id
            LEFT JOIN " . DATABASE_CATEGORIES . " c ON c.category_id=l.category_id 
            LEFT JOIN " . DATABASE_PERSONS . " p ON m.member_id = p.member_id
            LEFT JOIN(SELECT
                    *
                FROM
                    " . DATABASE_PERSONS . "
                WHERE
                    primary_member = 'N'
                ) j
                ON
                m.member_id = j.member_id
            WHERE {$condition}";
        return $query;
    }
    function getMySqlInfoPage($condition, $order_by){
        $query="SELECT page_id, title, body, permission, member_id_author, updated_at FROM " . DATABASE_PAGE . " WHERE {$condition} ORDER BY {$order_by}";
        return $query;
    }
    function getMySqlCategory($condition, $order_by){
        $query="SELECT 
            c.category_id as category_id, 
            c.parent_id as parent_id, 
            c.description as category_name 
            FROM " . DATABASE_CATEGORIES . " c WHERE {$condition} ORDER BY {$order_by}";
        return $query;

    }
    
}


$cQueries = new cQueries;
?>
