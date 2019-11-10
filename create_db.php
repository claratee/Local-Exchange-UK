<?php

/* 
// 06/03/14: Chris M: Commented out innoDB support check
/* 30/03/13: Chris M: Have made corrections to this file in response to syntax errors when attempting to run this script on a MySQL 5+ database.*/
/* 30/08/19: CT: Have taken some backward compatibility to a hard break for 2.0 with older, less secure mysql and php versions. This requires PHP7+ and MySQL5+
perhas I am not understanding why there was ISAM for some tables and innoDB for others. so have used innoDb for all,
*/


//
/* End */

$running_upgrade_script = true;
try{
  include_once("includes/inc.global.php");
  //$query = $cDB->Query("SHOW VARIABLES LIKE 'have_innodb';");
//$row = mysqli_fetch_array($query);
//if($row[1] != "YES")  die("Your database does not have InnoDB support. See the installation instructions for more information about InnoDB. Installation aborted.");


  //CT remade
  $success = $cDB->Query("CREATE TABLE " . DATABASE_MEMBERS . " (
    `member_id` varchar(15) NOT NULL,
    `password` varchar(255) NOT NULL,
    `member_role` char(1) NOT NULL,
    `security_q` varchar(25) DEFAULT NULL,
    `security_a` varchar(15) DEFAULT NULL,
    `status` char(1) NOT NULL,
    `member_note` varchar(255) NOT NULL,
    `admin_note` varchar(255) NOT NULL,
    `join_date` date NOT NULL DEFAULT '0000-00-00',
    `expire_date` date NOT NULL DEFAULT '0000-00-00',
    `away_date` date NOT NULL DEFAULT '0000-00-00',
    `account_type` char(1) NOT NULL,
    `email_updates` int(3) UNSIGNED NOT NULL DEFAULT '0',
    `balance` decimal(8,2) NOT NULL DEFAULT '0.00',
    `confirm_payments` int(1) NOT NULL DEFAULT '0',
    `restriction` int(1) NOT NULL DEFAULT '0',
    `opt_in_list` varchar(1) NOT NULL DEFAULT 'N'
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
  print("<p>" . DATABASE_MEMBERS . " Created: {$success}.</p>");


  
  //CT remade
 $success = $cDB->Query("CREATE TABLE " . DATABASE_PERSONS . " (
  `person_id` mediumint(6) UNSIGNED NOT NULL,
  `member_id` varchar(15) NOT NULL,
  `primary_member` char(1) NOT NULL,
  `directory_list` char(1) NOT NULL,
  `first_name` varchar(20) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `mid_name` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `mother_mn` varchar(30) DEFAULT NULL,
  `email` varchar(40) DEFAULT NULL,
  `phone1_area` char(5) DEFAULT NULL,
  `phone1_number` varchar(30) DEFAULT NULL,
  `phone1_ext` varchar(4) DEFAULT NULL,
  `phone2_area` char(5) DEFAULT NULL,
  `phone2_number` varchar(30) DEFAULT NULL,
  `phone2_ext` varchar(4) DEFAULT NULL,
  `fax_area` char(3) DEFAULT NULL,
  `fax_number` varchar(30) DEFAULT NULL,
  `fax_ext` varchar(4) DEFAULT NULL,
  `address_street1` varchar(50) DEFAULT NULL,
  `address_street2` varchar(50) DEFAULT NULL,
  `address_city` varchar(50) NOT NULL,
  `address_state_code` char(50) NOT NULL,
  `address_post_code` varchar(20) NOT NULL,
  `address_country` varchar(50) NOT NULL,
  `about_me` text,
  `age` varchar(20) DEFAULT NULL,
  `sex` varchar(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
  print("<p>" . DATABASE_PERSONS . " Created: {$success}.</p>");

//CT remade
 $success = $cDB->Query("CREATE TABLE " . DATABASE_LISTINGS . "
  `listing_id` int(11) NOT NULL,
  `listing_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `title` varchar(60) NOT NULL,
  `description` text,
  `category_id` smallint(4) UNSIGNED NOT NULL DEFAULT '0',
  `member_id` varchar(15) NOT NULL,
  `rate` varchar(30) DEFAULT NULL,
  `status` char(1) NOT NULL,
  `expire_date` date DEFAULT NULL,
  `reactivate_date` date DEFAULT NULL,
  `type` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
  print("<p>" . DATABASE_LISTINGS . " Created: {$success}.</p>");

//CT remade
 $success = $cDB->Query("CREATE TABLE " . DATABASE_CATEGORIES . "((
  `category_id` smallint(4) UNSIGNED NOT NULL,
  `parent_id` smallint(4) UNSIGNED DEFAULT NULL,
  `description` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
  print("<p>" . DATABASE_CATEGORIES . " Created: {$success}.</p>");

//
 $success = $cDB->Query("CREATE TABLE " . DATABASE_TRADES . "( trade_id mediumint(8) unsigned NOT NULL auto_increment, trade_date timestamp NOT NULL, status char(1) default NULL, member_id_from varchar(15) NOT NULL default '', member_id_to varchar(15) NOT NULL default '', amount decimal(8,2) NOT NULL default '0.00', category smallint(4) unsigned NOT NULL default '0', description varchar(255) default NULL, type char(1) NOT NULL default '', PRIMARY KEY (trade_id)) ".$engineSyntax."=InnoDB;");
  print("<p>" . DATABASE_TRADES . " Created: {$success}.</p>");

//CT updated
 $success = $cDB->Query("CREATE TABLE " . DATABASE_LOGGING . "(
  `log_id` mediumint(8) UNSIGNED NOT NULL,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `admin_id` varchar(15) NOT NULL,
  `category` char(1) NOT NULL,
  `action` char(1) NOT NULL,
  `ref_id` varchar(15) NOT NULL,
  `note` varchar(100) DEFAULT NULL
) {$engineSyntax}=InnoDB DEFAULT CHARSET=utf8;");
  print("<p>" . DATABASE_LOGGING . " Created: {$success}.</p>");

//CT remade
 $success = $cDB->Query("CREATE TABLE " . DATABASE_LOGINS . " (
  `member_id` varchar(15) NOT NULL,
  `total_failed` mediumint(6) UNSIGNED NOT NULL DEFAULT '0',
  `consecutive_failures` mediumint(3) UNSIGNED NOT NULL DEFAULT '0',
  `login_event_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
  print("<p>" . DATABASE_LOGINS . " Created: {$success}.</p>");

//CT remade
 $success = $cDB->Query("CREATE TABLE " . DATABASE_FEEDBACK . "`feedback_id` mediumint(8) UNSIGNED NOT NULL,
  `feedback_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` char(1) NOT NULL,
  `member_id_author` varchar(15) NOT NULL,
  `member_id_about` varchar(15) NOT NULL,
  `trade_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  `rating` char(1) NOT NULL,
  `comment` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
  print("<p>" . DATABASE_FEEDBACK . " Created: {$success}.</p>");

 $success = $cDB->Query("CREATE TABLE " . DATABASE_REBUTTAL . "( rebuttal_id mediumint(6) unsigned NOT NULL auto_increment, rebuttal_date timestamp NOT NULL, feedback_id mediumint(8) unsigned default NULL, member_id varchar(15) NOT NULL default '', comment varchar(255) default NULL, PRIMARY KEY (rebuttal_id)) ".$engineSyntax."=MyISAM;");
  print("<p>" . DATABASE_REBUTTAL . " Created: {$success}.</p>");


//CT remade
 $success = $cDB->Query("CREATE TABLE " . DATABASE_NEWS . " (
  `news_id` mediumint(6) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `sequence` decimal(6,4) NOT NULL DEFAULT '0.0000',
  `expire_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
  print("<p>" . DATABASE_NEWS . " Created: {$success}.</p>");


//CT remade
 $success = $cDB->Query("CREATE TABLE " . DATABASE_SETTINGS . " (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `typ` varchar(10) DEFAULT NULL,
  `current_value` text,
  `options` varchar(255) DEFAULT NULL,
  `default_value` text,
  `max_length` varchar(5) DEFAULT '99999',
  `descrip` text,
  `section` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
  print("<p>" . DATABASE_SETTINGS . " Created: {$success}.</p>");




//CT remade
 $success = $cDB->Query("CREATE TABLE " . DATABASE_UPLOADS . "(
  `upload_id` mediumint(6) UNSIGNED NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `title` varchar(100) NOT NULL,
  `type` char(1) NOT NULL,
  `filename` varchar(100) DEFAULT NULL,
  `note` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
  print("<p>" . DATABASE_UPLOADS . " Created: {$success}.</p>");

//CT new for reset passwords the right way
 $success = $cDB->Query("CREATE TABLE " . DATABASE_PASSWORD_RESET . "(`member_id` varchar(255) NOT NULL,
  `password_reset_token` varchar(255) NOT NULL,
  `password_reset_token_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
  print("<p>" . DATABASE_PASSWORD_RESET . " Created: {$success}.</p>");


//CT new - view based on search.
 $success = $cDB->Query("CREATE TABLE " . DATABASE_VIEW_CONTACTS . "(
`member_id` varchar(15)
,`email` varchar(40)
,`display_name` varchar(51)
,`email_updates` int(3) unsigned
,`member_role` char(1)
);");
  print("<p>" . DATABASE_VIEW_CONTACTS . " Created: {$success}.</p>");

 $success = $cDB->Query("DROP TABLE IF EXISTS " . DATABASE_VIEW_CONTACTS);
  print("<p>" . DATABASE_VIEW_CONTACTS . " Dropped for recreating as view: {$success}.</p>");

 $success = $cDB->Query("CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW " . DATABASE_VIEW_CONTACTS . "AS  select `m`.`member_id` AS `member_id`,`p`.`email` AS `email`,concat(`p`.`first_name`,' ',`p`.`last_name`) AS `display_name`,`m`.`email_updates` AS `email_updates`,`m`.`member_role` AS `member_role` from (`localexchange-dev`.`lets_person` `p` left join `localexchange-dev`.`lets_member` `m` on((`m`.`member_id` = `p`.`member_id`))) where ((`m`.`status` = 'A') and (`m`.`account_type` <> 'F')) order by `m`.`member_id` ;");
  print("<p>" . DATABASE_VIEW_CONTACTS . " Created as view: {$success}.</p>");



  




// Special admin account.
$city = DEFAULT_CITY;
$state = DEFAULT_STATE;
$postcode = DEFAULT_ZIP_CODE;
$country = DEFAULT_COUNTRY;
$date = strftime("%Y-%m-%d", time());

 $success = $cDB->Query("INSERT INTO " . DATABASE_MEMBERS . "(member_id, password, member_role, status, admin_note, join_date, expire_date, account_type, email_updates, balance) VALUES ('admin','5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', '2', 'A','Special account created during install. Ok to inactivate once an Admin Level 2 acct has been created.', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP,'S',7,0.00);");
  print("<p>" . DATABASE_MEMBERS . " populated: {$success}.</p>");

 $success = $cDB->Query("INSERT INTO " . DATABASE_PERSONS . "(person_id, member_id, primary_member, directory_list, first_name, last_name, mid_name, dob, mother_mn, email, phone1_area, phone1_number, phone1_ext, phone2_area, phone2_number, phone2_ext, fax_area, fax_number, fax_ext, address_street1, address_street2, address_city, address_state_code, address_post_code, address_country) VALUES (1,'admin','Y','Y','Special Admin','Account',NULL,NULL,NULL, NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL, NULL, NULL, '{$city}', '{$state}', '{$postcode}','{$country}');");
  print("<p>" . DATABASE_PERSONS . " populated: {$success}.</p>");


// System account.
if (defined("SYSTEM_ACCOUNT_ID")) {
     $success = $cDB->Query("
        INSERT INTO " .
            DATABASE_MEMBERS . "INSERT INTO `lets_member`(member_id, password, member_role, status, admin_note, join_date, expire_date, account_type, email_updates, balance) VALUES ('system','5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', '2', 'A','Special account created during install. Ok to inactivate once an Admin Level 2 acct has been created.', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP,'S',7,0.00);");
      print("<p>" . DATABASE_MEMBERS . " system user populated: {$success}.</p>");


    $system_account_id = SYSTEM_ACCOUNT_ID;
     $success = $cDB->Query("
        INSERT INTO " .
            DATABASE_PERSONS . "(person_id, member_id, primary_member,
                directory_list, first_name, last_name, mid_name, dob, mother_mn,
                email, phone1_area, phone1_number, phone1_ext, phone2_area,
                phone2_number, phone2_ext, fax_area, fax_number, fax_ext,
                address_street1, address_street2, address_city,
                address_state_code, address_post_code, address_country)
            VALUES (2, '{$system_account_id}', 'Y', 'Y', 'system', 'system', NULL,
                NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,
                NULL, NULL, NULL, NULL, '$city', '$state', '$postcode',
                '$country')");
       print("<p>" . DATABASE_PERSONS . " system user populated: {$success}.</p>");

}


$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . "(parent_id, description) VALUES (null,'Arts & Crafts');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Building Services');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Business & Administration');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Children & Childcare');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Computers');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Counseling & Therapy');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Food');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Gardening & Yard Work');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Goods');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Health & Personal');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Household');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Miscellaneous');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Music & Entertainment');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Pets');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Sports & Recreation');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Teaching');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null, 'Transportation');");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Freebies');");

$cDB->Query("CREATE TABLE " . DATABASE_SESSION . "
  `id` char(32) NOT NULL,
  `data` text,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

 
/* BEGIN upgrade to 0.4.0 */

// $cDB->Query("ALTER TABLE `person` ADD `about_me` text") or die ("Error altering person table. Does the web user account have alter table permission?");

// $cDB->Query("ALTER TABLE `person` ADD `age` varchar(20) default NULL") or die ("Error altering person table. Does the web user account have alter table permission?");

// $cDB->Query("ALTER TABLE `person` ADD `sex` varchar(1) default NULL") or die ("Error altering person table. Does the web user account have alter table permission?");

// $cDB->Query("ALTER TABLE `member` ADD `confirm_payments` int(1) default '0'") or die ("Error altering member table. Does the web user account have alter table permission?");

//CT updated
$cDB->Query("CREATE TABLE " . DATABASE_PAGE . " (
  `page_id` int(11) NOT NULL,
  `date` int(30) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text,
  `active` int(1) DEFAULT '1',
  `permission` int(2) DEFAULT NULL,
  `member_id_author` varchar(24) NOT NULL DEFAULT 'system',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

//CT updated
$cDB->Query("CREATE TABLE " . DATABASE_TRADES_PENDING . "
  (
  `trade_pending_id` mediumint(8) UNSIGNED NOT NULL,
  `trade_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `member_id_from` varchar(15) NOT NULL,
  `member_id_to` varchar(15) NOT NULL,
  `amount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `category_id` smallint(4) UNSIGNED NOT NULL DEFAULT '0',
  `description` varchar(255) DEFAULT NULL,
  `type` varchar(1) DEFAULT NULL,
  `member_id_author` varchar(15) DEFAULT NULL,
  `status` varchar(1) DEFAULT 'O',
  `member_to_decision` varchar(2) DEFAULT '1',
  `member_from_decision` varchar(2) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


// Create the new tables...
$cDB->Query("CREATE TABLE " . DATABASE_INCOME_TIES . " (
  `member_id` varchar(15) DEFAULT NULL,
  `member_id_to` varchar(15) DEFAULT NULL,
  `percent` int(3) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");





$cDB->Query("ALTER TABLE ". DATABASE_LOGGING . " ADD PRIMARY KEY (`log_id`)");


$cDB->Query("ALTER TABLE ". DATABASE_CATEGORIES . " ADD PRIMARY KEY (`category_id`);");


$cDB->Query("ALTER TABLE ". DATABASE_PAGE . " ADD PRIMARY KEY (`page_id`)");



$cDB->Query("ALTER TABLE ". DATABASE_FEEDBACK . " ADD PRIMARY KEY (`feedback_id`)");



$cDB->Query("ALTER TABLE ". DATABASE_FEEDBACK_REBUTTAL . " ADD PRIMARY KEY (`rebuttal_id`)");



$cDB->Query("ALTER TABLE ". DATABASE_LISTINGS . " ADD PRIMARY KEY (`listing_id`)");

$cDB->Query("ALTER TABLE ". DATABASE_MEMBERS . " ADD PRIMARY KEY (`member_id`)");


$cDB->Query("ALTER TABLE ". DATABASE_NEWS . " ADD PRIMARY KEY (`news_id`)");

$cDB->Query("ALTER TABLE ". DATABASE_PASSWORD_RESET . " ADD UNIQUE KEY `member_id` (`memb");

$cDB->Query("ALTER TABLE ". DATABASE_PERSONS . " ADD PRIMARY KEY (`person_id`)");
$cDB->Query("ALTER TABLE ". DATABASE_PERSONS . " ADD UNIQUE KEY (`person_id`)");

$cDB->Query("ALTER TABLE ". DATABASE_SESSION . " ADD PRIMARY KEY (`id`),
  ADD KEY `ts` (`ts`)");

$cDB->Query("ALTER TABLE ". DATABASE_SETTINGS . " ADD PRIMARY KEY (`id`)");


$cDB->Query("ALTER TABLE ". DATABASE_TRADES . " ADD PRIMARY KEY (`trade_id`)");


$cDB->Query("ALTER TABLE ". DATABASE_TRADES_PENDING . " ADD PRIMARY KEY (`trade_pending_id`)");


$cDB->Query("ALTER TABLE ". DATABASE_UPLOADS . " ADD PRIMARY KEY (`upload_id`)");


$cDB->Query("ALTER TABLE `lets_admin_activity`
  MODIFY `log_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0");

$cDB->Query("ALTER TABLE ". DATABASE_CATEGORIES. "
  MODIFY `category_id` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45");

$cDB->Query("ALTER TABLE `". DATABASE_PAGE. "
  MODIFY `page_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0");

$cDB->Query("ALTER TABLE ". DATABASE_FEEDBACK. "
  MODIFY `feedback_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0");

$cDB->Query("ALTER TABLE ". DATABASE_FEEDBACK_REBUTTAL. "
  MODIFY `rebuttal_id` mediumint(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0");

$cDB->Query("ALTER TABLE ". DATABASE_LISTINGS. "
  MODIFY `listing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0");

$cDB->Query("ALTER TABLE ". DATABASE_NEWS. "
  MODIFY `news_id` mediumint(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0");

$cDB->Query("ALTER TABLE ". DATABASE_PERSONS. "
  MODIFY `person_id` mediumint(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0");

$cDB->Query("ALTER TABLE ". DATABASE_SETTINGS. "
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52");


$cDB->Query("ALTER TABLE ". DATABASE_TRADES. "
  MODIFY `trade_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0");

$cDB->Query("ALTER TABLE ". DATABASE_TRADES_PENDING. "
  MODIFY `trade_pending_id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0");

$cDB->Query("ALTER TABLE ". DATABASE_UPLOADS. "
  MODIFY `upload_id` mediumint(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0");

//CT attempty to write whole of settings
$cDB->Query("INSERT INTO ". DATABASE_SETTINGS . " (`id`, `name`, `display_name`, `typ`, `current_value`, `options`, `default_value`, `max_length`, `descrip`, `section`) VALUES
(6, 'LEECH_EMAIL_URLOCKED', '\'Account Restricted\' Email', 'longtext', 'Dear Member\r\n\r\nWe have been reviewing members balances as we are concerned to ensure that trading goes back and forth on an equitable basis so that members are able to keep their accounts close to zero.  We recognise that situations sometimes occur that lead to things getting out of balance.  Therefore to assist you, we have restricted expenditure on your account for the time being. If have any queries about this, or if we can assist you in any particular way, please let us know, and we will review the situation in due course. The LETS Administrator ', '', 'Dear Member\r\n\r\nWe have been reviewing members balances as we are concerned to ensure that trading goes back and forth on an equitable basis so that members are able to keep their accounts close to zero.  We recognise that situations sometimes occur that lead to things getting out of balance.  Therefore to assist you, we have restricted expenditure on your account for the time being. If have any queries about this, or if we can assist you in any particular way, please let us know, and we will review the situation in due course. The LETS Administrator ', '', 'Define email that is sent out when restrictions are imposed on an account.', 3),
(8, 'LEECH_EMAIL_URUNLOCKED', '\'Account Restriction Lifted\' Email', 'longtext', 'Restrictions on your account have been lifted.', '', 'Restrictions on your account have been lifted.', '', 'Define email that is sent out when restrictions are lifted on an account.', 3),
(10, 'MEM_LIST_DISPLAY_BALANCE', 'Display Member Balance', 'bool', 'TRUE', '', 'TRUE', '', 'Do you want to display member balances in the Members List? (Balances are always visible to Admins and Committee members regardless of what is set here.)', 7),
(11, 'TAKE_SERVICE_FEE', 'Enable Take Service Charge', 'bool', 'FALSE', '', 'TRUE', '', 'Do you want the option of taking a service charge from members as and when?', 2),
(12, 'SHOW_INACTIVE_MEMBERS', 'Show Inactive Members in Members List', 'bool', 'FALSE', '', 'FALSE', '', 'Do you want to display Inactive members in the Member List?', 7),
(13, 'SHOW_RATE_ON_LISTINGS', 'Show Rate on Listings', 'bool', 'TRUE', '', 'TRUE', '', 'Do you want to display the Rate alongside the offers/wants in the main listings?', 7),
(14, 'SHOW_POSTCODE_ON_LISTINGS', 'Show Postcode on Listings', 'bool', 'TRUE', '', 'TRUE', '', 'Do you want to display the PostCode alongside the offers/wants in the main listings?', 7),
(15, 'NUM_CHARS_POSTCODE_SHOW_ON_LISTINGS', 'Postcode Length (in chars)', 'int', '3', '', '4', '', 'If you have elected to display the postcode on offers/wants listings, how much of the PostCode do you want to show? (the number you enter will be the number of characters displayed, so for eg if you just want to show the first 3 characters of the postcode then put 3.', 7),
(16, 'OVRIDE_BALANCES', 'Enable Balance Override', 'bool', 'FALSE', '', 'FALSE', '', 'Do you want admins to have the option to override Balances on a per member basis? This can be useful during the initial site set-up for inputting existing balances. Link will appear in admin panel if set to TRUE.  Use with CAUTION to avoid the database going out of balance', 6),
(17, 'MEMBERS_CAN_INVOICE', 'Enable Member-to-Member Invoicing', 'bool', 'TRUE', '', 'TRUE', '', 'Do you want to allow members to invoice one-another via the site? (The recipient is always given the option to confirm/reject payment of the invoice)', 2),
(18, 'ALLOW_IMAGES', 'Allow Members to Upload Images', 'bool', 'TRUE', '', 'TRUE', '', 'Do you want to allow members to upload an image of themselves, to be displayed with their personal profile?', 4),
(19, 'SOC_NETWORK_FIELDS', 'Enable Social Networking Fields', 'bool', 'TRUE', '', 'TRUE', '', 'Do you want to enable the Social Networking profile fields (Age, Sex, etc)?', 4),
(20, 'OOB_ACTION', 'Out Of Balance Behaviour', 'multiple', 'SILENT', 'FATAL,SILENT', 'SILENT', '', ' If, whilst processing a trade, the database is found to be out of balance, what should the system do?\r\n\r\nFATAL = Aborts the trade and informs the user why.\r\n\r\nSILENT = Continues with trade, displays no notifications whatsoever (NOTE: you can still set the option below to have an email notification sent to the admin)', 6),
(21, 'OOB_EMAIL_ADMIN', 'Email Admin on Out Of Balance', 'bool', 'TRUE', '', 'TRUE', '', 'Should the system send the Admin an email when the database is found to be out of balance?', 6),
(24, 'EMAIL_FROM', 'Email From Address', 'varchar', 'admin@mydomain.org', '', 'From: reply@my-domain.org', '', 'Email sent from this site will show as coming from this address', 1),
(25, 'USE_RATES', 'Use Rates Fields', 'bool', 'TRUE', '', 'TRUE', '', 'If turned on, listings will include a \"Rate\" field', 7),
(26, 'TAKE_MONTHLY_FEE', 'Enable Monthly Fee', 'bool', 'FALSE', '', 'TRUE', '', 'Do you want to enable Monthly Fees', 2),
(27, 'MONTHLY_FEE', 'Monthly Fee Amount', 'int', '1', '', '1', '', 'How much should the Monthly Fee be?', 2),
(28, 'EMAIL_LISTING_UPDATES', 'Send Listing Updates via Email', 'bool', 'FALSE', '', 'FALSE', '', 'Should users receive automatic updates for new and modified listings?', 1),
(29, 'DEFAULT_UPDATE_INTERVAL', 'Default Email Listings Update Interval', 'multiple', 'WEEKLY', 'NEVER,WEEKLY,MONTHLY', 'NEVER', '', 'If automatic updates are sent, this is the default interval.', 1),
(30, 'EXPIRE_INACTIVE_ACCOUNTS', 'Expire Inactive Accounts ', 'bool', 'FALSE', '', 'FALSE', '', 'Should inactive accounts have their listings automatically expired? This can be a useful feature.  It is an attempt to deal with the age-old local currency problem of new members joining and then not keeping their listings up to date or using the system in any way. It is designed so that if a member doesnt record a trade OR update a listing in a given period of time (default is six months), their listings will be set to expire and they will receive an email to that effect (as will the admin).', 5),
(31, 'MAX_DAYS_INACTIVE', 'Expire Accounts After x Days of Inactivity', 'int', '360', '', '180', '', 'After this many days, accounts that have had no activity will have their listings set to expire.  They will have to reactiveate them individually if they still want them.', 5),
(32, 'EXPIRATION_WINDOW', 'Account Expiration Window', 'int', '15', '', '15', '', 'How many days in the future the expiration date will be set for', 5),
(33, 'DELETE_EXPIRED_AFTER', 'Delete Expired Listings After x Days', 'int', '90', '', '90', '', 'How long should expired listings hang around before they are deleted?', 5),
(34, 'ALLOW_INCOME_SHARES', 'Allow Income Sharing', 'bool', 'TRUE', NULL, 'TRUE', '99999', 'Do you want to allow members to share a percentage of any income they generate with another account of their choosing? The member can specify the exact percentage they wish to donate.', 2),
(35, 'LEECH_NOTICE', 'Message Displayed to Leecher who tries to trade', 'longtext', 'Restrictions have been imposed on your account which prevent you from trading outwards, Please contact the administrator for more information.', '', 'Restrictions have been imposed on your account which prevent you from trading outwards, Please contact the administrator for more information.', '', 'Leecher sees this notice when trying to send money.', 3),
(36, 'SHOW_GLOBAL_FEES', 'Show monthly fees and service charges in global exchange view', 'bool', 'FALSE', NULL, 'FALSE', '', 'Do you want to show monthly fees and service charges in the global exchange view? (Note: individual members will still be able to see this in their own personal exchange history).', 7),
(39, 'SITE_SHORT_TITLE', 'Short title for the site', 'varchar', 'LETS', NULL, 'LETS', '99999', NULL, NULL),
(41, 'SITE_MOTTO', 'Tagline', 'varchar', 'Building local community through trade', NULL, 'Building local community through trade', '100', NULL, NULL),
(42, 'HEADER_LOGO', 'Logo for the header', 'varchar', 'localx_logo.png', 'varchar', 'localx_logo.png', '100', 'logo. make sure in image directory', NULL),
(43, 'SITE_LONG_TITLE', 'Long title for the site', 'varchar', 'Local Exchange Trading Scheme', NULL, 'Local Exchange Trading Scheme', '100', NULL, NULL),
(44, 'PAGE_TITLE_HEADER', 'separator and Postfix for header', 'varchar', ': LETS', NULL, ': LETS', '100', NULL, NULL),
(46, 'EMAIL_FOOTER_LEGAL', 'Footer of email', 'longtext', '<p>You received this email message as a member of the Local Exchange and Trading Scheme (LETS). If you don\'t want to receive messages like this, contact <a href=\"mailto:admin@mydomain.org\">admin@mydomain.org</a>.<br /><a href=\"https://mydomain.org\">Website</a> | <a href=\"https://mydomain.org/privacy.php\">Privacy policy</a></p>', NULL, '<p>You received this email message as a member of the LETS community. If you don\'t want to receive messages like this, contact <a href=\"mailto:admin@mydomain.org\">admin@mydomain.org</a>.</p><p><a href=\"https://mydomain.org\">Website</a> | <a href=\"https://mydomain.org/privacy/php\">Privacy policy</a></p>', '200', 'legal info about the organisation that goes in the footer', NULL),
(47, 'EMAIL_FROM_NAME', 'Email From name', 'varchar', 'LETS', '', 'LETS Support', '', 'Email sent from this site will show as coming from this name', 1),
(48, 'OOB_UNLOCKED', 'is trading allowed right now? Lock site (false) if out of balance', 'bool', 'TRUE', 'TRUE, FALSE', 'TRUE', '', 'is trading allowed right now? Lock site if out of balance', 1),
(49, 'SHOW_DATE_ON_LISTINGS', 'Show Date on Listings', 'bool', 'FALSE', '', 'TRUE', '', 'Do you want to display the Date alongside the offers/wants in the main listings?', 7),
(50, 'EMAIL_LISTING_FOOTER', 'Text at the bottom of the listings email', 'varchar', '--\r\n\r\n<p><b>Join us on Facebook!</b><br />\r\nThere\'s a LETS facebook group for chatter and quick posts. Include your member number when you request to join, the group is only open to LETS members.<br />\r\n<a href=\"https://www.facebook.com/groups/pathtolets/\">https://www.facebook.com/groups/pathtolets/</a> \r\n\r\n<p><b>Want to feature on this listing update?</b><br />\r\nManage your LETS listings yourself from your main profile page. Updated listings are automatically included. <a href=\"https://www.mydomain.org/pathtomembermanagepage\">https://www.mydomain.org/pathtomembermanagepage</a>.</p>', '', '--\r\n\r\n<p><b>Want to feature on this listing update?</b><br />\r\nManage your listings yourself from your main profile page. Updated listings are automatically included.</p>', '', 'On the listings emails, what should the bottom text be?', 1),
(51, 'EMAIL_LISTING_HEADER', 'Text at the top of the listings email', 'varchar', '', '', '', '', 'On the listings emails, what should the top text be?', 1)");




print("<h2>Success! Best check though :). Important: Check your tables have been created properly, and delete this file (create_db.php).</h2>");

} catch(Exception $e){
  print_r($e->getMessage());

}

?>
