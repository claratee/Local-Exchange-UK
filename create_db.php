<?php

/* 
// 06/03/14: Chris M: Commented out innoDB support check
/* 30/03/13: Chris M: Have made corrections to this file in response to syntax errors when attempting to run this script on a MySQL 5+ database.*/
/* 30/08/19: Claratee: Have taken some backward compatibility to a hard break for 2.0 with older, less secure mysql and php versions. This requires PHP7+ and MySQL5+*/


//
/* End */

$running_upgrade_script = true;
include_once("includes/inc.global.php");

//$query = $cDB->Query("SHOW VARIABLES LIKE 'have_innodb';");
//$row = mysqli_fetch_array($query);
//if($row[1] != "YES")	die("Your database does not have InnoDB support. See the installation instructions for more information about InnoDB. Installation aborted.");

if($cDB->Query("SELECT * FROM " . DATABASE_MEMBERS))	die("Error - database already exists! If you want to create a new database delete the old one first. You may also get this error if you are trying to install the program and your database userid or password in inc.config.php is incorrect.");


$cDB->Query("CREATE TABLE " . DATABASE_MEMBERS . "( member_id varchar(15) NOT NULL default '', password varchar(50) NOT NULL default '', member_role char(1) NOT NULL default '', security_q varchar(25) default NULL, security_a varchar(15) default NULL, status char(1) NOT NULL default '', member_note varchar(100) default NULL, admin_note varchar(100) default NULL, join_date date NOT NULL default '0000-00-00', expire_date date default NULL, away_date date default NULL, account_type char(1) NOT NULL default '', email_updates int(3) unsigned NOT NULL default '0', balance decimal(8,2) NOT NULL default '0.00', PRIMARY KEY (member_id)) ".$engineSyntax."=InnoDB;") or die("Error - database already exists! If you want to create a new database delete the old one first.");
	
$cDB->Query("CREATE TABLE " . DATABASE_PERSONS . "( person_id mediumint(6) unsigned NOT NULL auto_increment, member_id varchar(15) NOT NULL default '', primary_member char(1) NOT NULL default '', directory_list char(1) NOT NULL default '', first_name varchar(20) NOT NULL default '', last_name varchar(30) NOT NULL default '', mid_name varchar(20) default NULL, dob date default NULL, mother_mn varchar(30) default NULL, email varchar(40) default NULL, phone1_area char(5) default NULL, phone1_number varchar(30) default NULL, phone1_ext varchar(4) default NULL, phone2_area char(5) default NULL, phone2_number varchar(30) default NULL, phone2_ext varchar(4) default NULL, fax_area char(3) default NULL, fax_number varchar(30) default NULL, fax_ext varchar(4) default NULL, address_street1 varchar(50) default NULL, address_street2 varchar(50) default NULL, address_city varchar(50) NOT NULL default '', address_state_code char(50) NOT NULL default '', address_post_code varchar(20) NOT NULL default '', address_country varchar(50) NOT NULL default '', PRIMARY KEY (person_id)) ".$engineSyntax."=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

$cDB->Query("CREATE TABLE " . DATABASE_LISTINGS . "( title varchar(60) NOT NULL default '', description text, category_id smallint(4) unsigned NOT NULL default '0', member_id varchar(15) NOT NULL default '', rate varchar(30) default NULL, status char(1) NOT NULL default '', posting_date timestamp NOT NULL, expire_date date default NULL, reactivate_date date default NULL, type char(1) NOT NULL default '', PRIMARY KEY (title, category_id, member_id,type)) ".$engineSyntax."=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

$cDB->Query("CREATE TABLE " . DATABASE_CATEGORIES . "( category_id smallint(4) unsigned NOT NULL auto_increment, parent_id smallint(4) unsigned default NULL, description varchar(30) NOT NULL default '', PRIMARY KEY (category_id)) ".$engineSyntax."=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

$cDB->Query("CREATE TABLE " . DATABASE_TRADES . "( trade_id mediumint(8) unsigned NOT NULL auto_increment, trade_date timestamp NOT NULL, status char(1) default NULL, member_id_from varchar(15) NOT NULL default '', member_id_to varchar(15) NOT NULL default '', amount decimal(8,2) NOT NULL default '0.00', category smallint(4) unsigned NOT NULL default '0', description varchar(255) default NULL, type char(1) NOT NULL default '', PRIMARY KEY (trade_id)) ".$engineSyntax."=InnoDB;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

$cDB->Query("CREATE TABLE " . DATABASE_LOGGING . "( log_id mediumint(8) unsigned NOT NULL auto_increment, log_date timestamp NOT NULL, admin_id varchar(15) NOT NULL default '', category char(1) NOT NULL default '', action char(1) NOT NULL default '', ref_id varchar(15) NOT NULL default '', note varchar(100) default NULL, PRIMARY KEY (log_id)) ".$engineSyntax."=InnoDB;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

$cDB->Query("CREATE TABLE " . DATABASE_LOGINS . "( member_id varchar(15) NOT NULL default '', total_failed mediumint(6) unsigned NOT NULL default '0', consecutive_failures mediumint(3) unsigned NOT NULL default '0', last_failed_date timestamp NOT NULL, last_success_date timestamp NOT NULL default '00000000000000', PRIMARY KEY (member_id)) ".$engineSyntax."=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

$cDB->Query("CREATE TABLE " . DATABASE_FEEDBACK . "( feedback_id mediumint(8) unsigned NOT NULL auto_increment, feedback_date timestamp NOT NULL, status char(1) NOT NULL default '', member_id_author varchar(15) NOT NULL default '', member_id_about varchar(15) NOT NULL default '', trade_id mediumint(8) unsigned NOT NULL default '0', rating char(1) NOT NULL default '', comment text, PRIMARY KEY (feedback_id)) ".$engineSyntax."=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

$cDB->Query("CREATE TABLE " . DATABASE_REBUTTAL . "( rebuttal_id mediumint(6) unsigned NOT NULL auto_increment, rebuttal_date timestamp NOT NULL, feedback_id mediumint(8) unsigned default NULL, member_id varchar(15) NOT NULL default '', comment varchar(255) default NULL, PRIMARY KEY (rebuttal_id)) ".$engineSyntax."=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

$cDB->Query("CREATE TABLE " . DATABASE_NEWS . "( news_id mediumint(6) unsigned NOT NULL auto_increment, title varchar(100) NOT NULL default '', description text NOT NULL, sequence decimal(6,4) NOT NULL default '0.0000', expire_date date default NULL, PRIMARY KEY (news_id)) ".$engineSyntax."=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first.");

$cDB->Query("CREATE TABLE " . DATABASE_UPLOADS . "( upload_id mediumint(6) unsigned NOT NULL auto_increment, upload_date timestamp NOT NULL, title varchar(100) NOT NULL default '', type char(1) NOT NULL default '', filename varchar(100) default NULL, note varchar(100) default NULL, PRIMARY KEY (upload_id)) ".$engineSyntax."=MyISAM;") or die("Error - database already exists! If you want to create a new database delete the old one first.");


// Special admin account.
$city = DEFAULT_CITY;
$state = DEFAULT_STATE;
$postcode = DEFAULT_ZIP_CODE;
$country = DEFAULT_COUNTRY;
$date = strftime("%Y-%m-%d", time());

$cDB->Query("INSERT INTO " . DATABASE_MEMBERS . "(member_id, password, member_role, security_q, security_a, status, member_note, admin_note, join_date, expire_date, away_date, account_type, email_updates, balance) VALUES ('ADMIN','5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', '9',NULL,NULL,'A',NULL,'Special account created during install. Ok to inactivate once an Admin Level 2 acct has been created.', '$date', NULL,NULL,'S',7,0.00);") or die("Error - Could not insert row into member table.");

$cDB->Query("INSERT INTO " . DATABASE_PERSONS . "(person_id, member_id, primary_member, directory_list, first_name, last_name, mid_name, dob, mother_mn, email, phone1_area, phone1_number, phone1_ext, phone2_area, phone2_number, phone2_ext, fax_area, fax_number, fax_ext, address_street1, address_street2, address_city, address_state_code, address_post_code, address_country) VALUES (1,'admin','Y','Y','Special Admin','Account',NULL,NULL,NULL, NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL, NULL, NULL, '$city', '$state', '$postcode','$country');") or die("Error - Could not insert row into person table.");


// System account.
if (defined("SYSTEM_ACCOUNT_ID")) {
    $cDB->Query("
        INSERT INTO " .
            DATABASE_MEMBERS . "(member_id, password, member_role, security_q,
                security_a, status, member_note, admin_note, join_date,
                expire_date, away_date, account_type, email_updates, balance)
            VALUES ('system', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', '0',
                NULL, NULL, 'A', NULL, 'System account created during install.',
                '$date', NULL, NULL, 'O', 7, 0.00)")
    or die("Error - Could not insert row into member table.");

    $system_account_id = SYSTEM_ACCOUNT_ID;
    $cDB->Query("
        INSERT INTO " .
            DATABASE_PERSONS . "(person_id, member_id, primary_member,
                directory_list, first_name, last_name, mid_name, dob, mother_mn,
                email, phone1_area, phone1_number, phone1_ext, phone2_area,
                phone2_number, phone2_ext, fax_area, fax_number, fax_ext,
                address_street1, address_street2, address_city,
                address_state_code, address_post_code, address_country)
            VALUES (2, '$system_account_id', 'Y', 'Y', 'system', 'system', NULL,
                NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,
                NULL, NULL, NULL, NULL, '$city', '$state', '$postcode',
                '$country')")
    or die("Error - Could not insert row into person table.");
}


$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . "(parent_id, description) VALUES (null,'Arts & Crafts');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Building Services');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Business & Administration');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Children & Childcare');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Computers');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Counseling & Therapy');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Food');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Gardening & Yard Work');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Goods');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Health & Personal');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Household');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Miscellaneous');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Music & Entertainment');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Pets');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Sports & Recreation');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Teaching');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null, 'Transportation');") or die("Error - Could not insert row into categories table.");

$cDB->Query("INSERT INTO " . DATABASE_CATEGORIES . " (parent_id, description) VALUES (null,'Freebies');") or die("Error - Could not insert row into categories table.");

$cDB->Query("CREATE TABLE " . DATABASE_SESSION . "(id CHAR(32) NOT NULL, data TEXT, ts TIMESTAMP, PRIMARY KEY(id), KEY(ts))") or
    die("Error - Cannot create session table.");
 
/* BEGIN upgrade to 0.4.0 */

$cDB->Query("ALTER TABLE `person` ADD `about_me` text") or die ("Error altering person table. Does the web user account have alter table permission?");

$cDB->Query("ALTER TABLE `person` ADD `age` varchar(20) default NULL") or die ("Error altering person table. Does the web user account have alter table permission?");

$cDB->Query("ALTER TABLE `person` ADD `sex` varchar(1) default NULL") or die ("Error altering person table. Does the web user account have alter table permission?");

$cDB->Query("ALTER TABLE `member` ADD `confirm_payments` int(1) default '0'") or die ("Error altering member table. Does the web user account have alter table permission?");

$cDB->Query("CREATE TABLE cdm_pages (
  id int(11) NOT NULL auto_increment,
  `date` int(30) default NULL,
  title varchar(255) default NULL,
  body text,
  active int(1) default '1',
  PRIMARY KEY  (id)
) ".$engineSyntax."=MyISAM AUTO_INCREMENT=6;")
 or die("Error creating cdm_pages table.  Does the web user account have add table permission?");

$cDB->Query("CREATE TABLE trades_pending (
  id mediumint(8) unsigned NOT NULL auto_increment,
  trade_date timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  member_id_from varchar(15) NOT NULL default '',
  member_id_to varchar(15) NOT NULL default '',
  amount decimal(8,2) NOT NULL default '0.00',
  category smallint(4) unsigned NOT NULL default '0',
  description varchar(255) default NULL,
  typ varchar(1) default NULL,
  `status` varchar(1) default 'O',
  member_to_decision varchar(2) default '1',
  member_from_decision varchar(2) default '1',
  PRIMARY KEY  (id)
) ".$engineSyntax."=MyISAM AUTO_INCREMENT=17")
	or die("Error creating trades_pending table.  Does the web user account have add table permission?");

/* END upgrade to 0.4.0 */

/* BEGIN upgrade to 1.01 */


// Some alterations to existing tables...
$cDB->Query("ALTER TABLE `cdm_pages` add permission int(2)") or die("Error altering cdm_pages table.  Does the web user account have alter table permission?");


$cDB->Query("ALTER TABLE `member` add restriction int(1)") or die("Error altering member table.  Does the web user account have alter table permission?");

$cDB->Query("alter table member change admin_note admin_note text") or die("Error altering member table.  Does the web user account have alter table permission?");

// Create the new tables...
$cDB->Query("CREATE TABLE `income_ties` (
  `id` int(11) NOT NULL auto_increment,
  `member_id` varchar(15) default NULL,
  `tie_id` varchar(15) default NULL,
  `percent` int(3) default NULL,
  PRIMARY KEY  (`id`)
) ".$engineSyntax."=MyISAM AUTO_INCREMENT=12") or die("Error creating income_ties table.  Does the web user account have add table permission?");


// CT - create tables from scratch

CREATE TABLE IF NOT EXISTS `lets_settings` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `lets_settings`
--

INSERT INTO `lets_settings` (`id`, `name`, `display_name`, `typ`, `current_value`, `options`, `default_value`, `max_length`, `descrip`, `section`) VALUES
(6, 'LEECH_EMAIL_URLOCKED', '''Account Restricted'' Email', 'longtext', 'Dear Member\r\n\r\nWe have been reviewing members balances as we are concerned to ensure that trading goes back and forth on an equitable basis so that members are able to keep their accounts close to zero.  We recognise that situations sometimes occur that lead to things getting out of balance.  Therefore to assist you, we have restricted expenditure on your account for the time being. If have any queries about this, or if we can assist you in any particular way, please let us know, and we will review the situation in due course. The LETS Administrator ', '', 'Dear Member\r\n\r\nWe have been reviewing members balances as we are concerned to ensure that trading goes back and forth on an equitable basis so that members are able to keep their accounts close to zero.  We recognise that situations sometimes occur that lead to things getting out of balance.  Therefore to assist you, we have restricted expenditure on your account for the time being. If have any queries about this, or if we can assist you in any particular way, please let us know, and we will review the situation in due course. The LETS Administrator ', '', 'Define email that is sent out when restrictions are imposed on an account.', 3),
(8, 'LEECH_EMAIL_URUNLOCKED', '''Account Restriction Lifted'' Email', 'longtext', 'Restrictions on your account have been lifted.', '', 'Restrictions on your account have been lifted.', '', 'Define email that is sent out when restrictions are lifted on an account.', 3),
(10, 'MEM_LIST_DISPLAY_BALANCE', 'Display Member Balance', 'bool', 'TRUE', '', 'TRUE', '', 'Do you want to display member balances in the Members List? (Balances are always visible to Admins and Committee members regardless of what is set here.)', 7),
(11, 'TAKE_SERVICE_FEE', 'Enable Take Service Charge', 'bool', 'TRUE', '', 'TRUE', '', 'Do you want the option of taking a service charge from members as and when?', 2),
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
(24, 'EMAIL_FROM', 'Email From Address for news', '', 'admin@camlets.org.uk', '', 'From: reply@my-domain.org', '', 'Email sent from this site will show as coming from this address', 1),
(25, 'USE_RATES', 'Use Rates Fields', 'bool', 'TRUE', '', 'TRUE', '', 'If turned on, listings will include a "Rate" field', 7),
(26, 'TAKE_MONTHLY_FEE', 'Enable Monthly Fee', 'bool', 'TRUE', '', 'TRUE', '', 'Do you want to enable Monthly Fees', 2),
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
(39, 'SITE_SHORT_TITLE', 'Short title for the site', NULL, 'CamLETS', NULL, 'CamLETS', '99999', NULL, NULL),
(40, 'SERVER_PATH_URL', 'Server path url', NULL, '/members', NULL, '/members', '99999', NULL, NULL),
(41, 'SITE_MOTTO', 'Tagline', NULL, 'Building local community through trade', NULL, 'Building local community through trade', '100', NULL, NULL),
(42, 'HEADER_LOGO', 'Logo for the header', NULL, 'mosaic-110.jpg', NULL, 'mosaic-110.jpg', '100', NULL, NULL),
(43, 'SITE_LONG_TITLE', 'Long title for the site', NULL, 'Cambridge Local Exchange Trading Scheme', NULL, 'Cambridge Local Exchange Trading Scheme', '100', NULL, NULL),
(44, 'PAGE_TITLE_HEADER', 'separator and Postfix for header', NULL, ': CamLETS', NULL, ': CamLETS', '100', NULL, NULL),
(45, 'VENDOR_PATH', '/', NULL, '/members/vendor', NULL, '/members/vendor', '200', NULL, NULL),
(46, 'EMAIL_FOOTER_LEGAL', 'Footer of email', NULL, '<p>You received this email message as a member of the Cambridge Local Exchange and Trading Scheme (CamLETS). If you don''t want to receive messages like this, contact <a href="mailto:admin@camlets.org.uk">admin@camlets.org.uk</a>.<br /><a href="https://www.camlets.org.uk">Website</a> | <a href="https://www.camlets.org.uk/privacy">Privacy policy</a></p>', NULL, '<p>You received this email message as a member of the LETS community. If you don''t want to receive messages like this, contact <a href="mailto:admin@camlets.org.uk">admin@camlets.org.uk</a>.</p><p><a href="https://www.camlets.org.uk">Website</a> | <a href="https://www.camlets.org.uk/privacy">Privacy policy</a></p>', '200', 'legal info about the organisation that goes in the footer', NULL),
(47, 'EMAIL_FROM_NAME', 'Email From name', '', 'CamLETS', '', 'LETS Support', '', 'Email sent from this site will show as coming from this name', 1);


// CT alter - stuff below this line matters. mostly modes by me 

//missing - settings with extra fields


// CTviews
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `lets_view_contacts`  AS  select `m`.`member_id` AS `member_id`,`p`.`email` AS `email`,concat(`p`.`first_name`,' ',`p`.`last_name`) AS `display_name`,`m`.`email_updates` AS `email_updates`,`m`.`member_role` AS `member_role` from (`lets_person` `p` left join `lets_member` `m` on((`m`.`member_id` = `p`.`member_id`))) where ((`m`.`status` = 'A') and (`m`.`account_type` <> 'F')) ;

/* END upgrade to 2.0 */

				
// CT change login history table to not set to currrent date - default should be 0
ALTER TABLE `lets_logins` CHANGE `last_failed_date` `last_failed_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';

?>
