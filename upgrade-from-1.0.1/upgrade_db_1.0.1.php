<?php
include_once("../includes/inc.global.php");
// WIP 2019-11-10 Clara Todd clarabara@gmail.com

// significant changes from 1.0.1.
// I found I needed to rename the tables with a prefix -  "lets_" is a good choice. Saves hassle of name collisions of your host is stingy with the database access, and you only get one in your account (and you run multiple sites or have a dev environment remotely)

// cleanup db steps before migration
// export with the site admin tools 
// in my experience, this does not export quite right - but easy enough to fix.

// while in text file bk state of db
// search and replace to put linebreaks before INSERT, DROP, CREATE.
// replace 'CURRENT_TIMESTAMP()' with CURRENT_TIMESTAMP()

// if you are not working in your own db or your db has other info in it that you want to keep (like the live site) - you might want to prefix your tables with something . like lets_



// PSEUDOANON - for testing server
// make db safe for testing. replace with your own email so you can test. Use a webmail service that respects "+tag" on addresses so you can retain ref to the account.

// UPDATE `lets_person` SET `last_name`=concat("Deckard", member_id), `email`=concat("username+", member_id, "@example.com"),`address_street1`=concat(member_id, " Jetson Parkway")



// ****************************************** #
// ****************************************** #

// Below are the migrations for the the site db.


// simplify the listings table. use an listing_id instead of the complicated b-tree with 4 p-keys - so you can actually change titles etc like users expect to do.


$string_queries = array();
  $string_queries[]="ALTER TABLE " . DATABASE_LISTINGS . " DROP PRIMARY KEY;";

# add an appropriate id that autoincrements.
  $string_queries[]="ALTER TABLE " . DATABASE_LISTINGS . " ADD `listing_id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`listing_id`);";
# name the date column to be the same as everywhere else
  $string_queries[]="ALTER TABLE " . DATABASE_LISTINGS . " CHANGE `posting_date` `listing_date` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `listing_id`;";
  $string_queries[]="ALTER TABLE " . DATABASE_LISTINGS . " CHANGE `category_code` `category_id` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0';";

#consistent naming for page_id on page table
  $string_queries[]="ALTER TABLE " . DATABASE_PAGE . " CHANGE `id` `page_id` INT(11) NOT NULL AUTO_INCREMENT;";

#use the same date and title format as everywhere else, and convert the existing date
  $string_queries[]="ALTER TABLE " . DATABASE_PAGE . " ADD `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `permission`;";
  $string_queries[]="UPDATE " . DATABASE_PAGE . " SET `updated_at`=FROM_UNIXTIME(`date`, '%Y-%m-%d %H:%I:%s');";
  $string_queries[]="ALTER TABLE " . DATABASE_PAGE . " ADD `member_id_author` VARCHAR(24) NOT NULL DEFAULT 'system' AFTER `permission`;";

#set up new column for opt-in printed directory
  $string_queries[]="ALTER TABLE " . DATABASE_MEMBERS . " ADD `opt_in_list` VARCHAR(1) NOT NULL DEFAULT 'N' AFTER `restriction`;";
  $string_queries[]="ALTER TABLE " . DATABASE_MEMBERS . " CHANGE `member_note` `member_note` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL, CHANGE `admin_note` `admin_note` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL, CHANGE `expire_date` `expire_date` DATE NULL DEFAULT NULL, CHANGE `away_date` `away_date` DATE NULL DEFAULT NULL, CHANGE `confirm_payments` `confirm_payments` INT(1) NULL DEFAULT '0', CHANGE `restriction` `restriction` INT(1) NULL DEFAULT '0';";
  $string_queries[]="ALTER TABLE " . DATABASE_MEMBERS . " CHANGE `member_note` `member_note` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `admin_note` `admin_note` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `expire_date` `expire_date` DATE NOT NULL DEFAULT '0000-00-00', CHANGE `away_date` `away_date` DATE NOT NULL DEFAULT '0000-00-00', CHANGE `confirm_payments` `confirm_payments` INT(1) NOT NULL DEFAULT '0', CHANGE `restriction` `restriction` INT(1) NOT NULL DEFAULT '0';";



//#create a view table with just names, emails and the user preferences...just for convenience
  $string_queries[]="CREATE VIEW " . DATABASE_VIEW_CONTACTS . "  AS  select `m`.`member_id` AS `member_id`,`p`.`email` AS `email`,concat(`p`.`first_name`,' ',`p`.`last_name`) AS `display_name`,`m`.`email_updates` AS `email_updates`,`m`.`member_role` AS `member_role` from (`lets_person` `p` left join `lets_member` `m` on((`m`.`member_id` = `p`.`member_id`))) where ((`m`.`status` = 'A') and (`m`.`account_type` <> 'F')) order by `m`.`member_id` ;";



//vars that should be configured.
  $string_queries[]="INSERT INTO " . DATABASE_SETTINGS . " (`id`, `name`, `display_name`, `typ`, `current_value`, `options`, `default_value`, `max_length`, `descrip`, `section`) VALUES
(39, 'SITE_SHORT_TITLE', 'Short title for the site', NULL, 'LETS', NULL, 'LETS', '99999', NULL, NULL),
(41, 'SITE_MOTTO', 'Tagline', NULL, 'Building local community through trade', NULL, 'Building local community through trade', '100', NULL, NULL),
(42, 'HEADER_LOGO', 'Logo for the header', NULL, 'mosaic-110.jpg', NULL, 'localx_logo.png', '100', NULL, NULL),
(43, 'SITE_LONG_TITLE', 'Long title for the site', NULL, 'Local Exchange Trading Scheme', NULL, 'Local Exchange Trading Scheme', '100', NULL, NULL),
(44, 'PAGE_TITLE_HEADER', 'separator and Postfix for header', NULL, ': LETS', NULL, ': LETS', '100', NULL, NULL),
(46, 'EMAIL_FOOTER_LEGAL', 'Footer of email', NULL, '<p>You received this email message as a member of the Local Exchange and Trading Scheme (LETS). If you don\'t want to receive messages like this, contact <a href=\"mailto:admin@camlets.org.uk\">admin@mydomain.org</a>.<br /><a href=\"https:#www.camlets.org.uk\">Website</a> | <a href=\"https://mydomain.org?page_id=2\">Privacy policy</a></p>', NULL, '<p>You received this email message as a member of the LETS community. If you don\'t want to receive messages like this, contact <a href=\"mailto:admin@mydomain.org\">admin@mydomain.org</a>.</p><p><a href=\"https://mydomain.org\">Website</a> | <a href=\"https://mydomain.org?page_id=2\">Privacy policy</a></p>', '200', 'legal info about the organisation that goes in the footer', NULL),
(47, 'EMAIL_FROM_NAME', 'Email From name', '', 'LETS', '', 'LETS Support', '', 'Email sent from this site will show as coming from this name', 1);";


  $string_queries[]="INSERT INTO " . DATABASE_SETTINGS . " (`id`, `name`, `display_name`, `typ`, `current_value`, `options`, `default_value`, `max_length`, `descrip`, `section`) VALUES (NULL, 'OOB_UNLOCKED', 'is trading allowed right now? Lock site if out of balance', 'bool', 'TRUE', 'TRUE, FALSE', 'FALSE', '', '', '1');";


  $string_queries[]="INSERT INTO " . DATABASE_SETTINGS . " (`id`, `name`, `display_name`, `typ`, `current_value`, `options`, `default_value`, `max_length`, `descrip`, `section`) VALUES (NULL, 'SHOW_DATE_ON_LISTINGS', 'Show Date on Listings', 'bool', 'FALSE', '', 'TRUE', '', 'Do you want to display the Date alongside the offers/wants in the main listings?', '7');";

  $string_queries[]="INSERT INTO " . DATABASE_SETTINGS . " (`id`, `name`, `display_name`, `typ`, `current_value`, `options`, `default_value`, `max_length`, `descrip`, `section`) VALUES (NULL, 'EMAIL_LISTING_HEADER', 'Text at the top of the listings email', 'varchar', '', '', '', '', 'On the listings emails, what should the top text be?', '1');";

  $string_queries[]="INSERT INTO " . DATABASE_SETTINGS . " (`id`, `name`, `display_name`, `typ`, `current_value`, `options`, `default_value`, `max_length`, `descrip`, `section`) VALUES (NULL, 'EMAIL_LISTING_FOOTER', 'Text at the bottom of the listings email', 'varchar', '--\r\n\r\n<p><b>Join us on Facebook!</b><br />\r\nThere\'s a CamLETS facebook group for chatter and quick posts. Include your member number when you request to join, the group is only open to CamLETS members.<br />\r\n<a href=\"https://www.facebook.com/groups/camlets.org.uk/\">https://www.facebook.com/groups/camlets.org.uk/</a> \r\n\r\n<p><b>Want to feature on this listing update?</b><br />\r\nManage your CamLETS listings yourself from your main profile page. Updated listings are automatically included. <a href=\"http://www.cam.letslink.org/members/member_profile_all_in_one.php\">http://www.cam.letslink.org/members/member_profile_all_in_one.php</a>.</p>', '', '--\r\n\r\n<p><b>Want to feature on this listing update?</b><br />\r\nManage your listings yourself from your main profile page. Updated listings are automatically included.</p>', '', 'On the listings emails, what should the bottom text be?', '1');";


//#logins table - bugfix - all logins were logged as failure as it was set with current timestamp. changed title of the field so it becomes "login_date" column for any attempts, assume the count for consecutive failures would be the logic.
  $string_queries[]="ALTER TABLE " . DATABASE_LOGINS . " CHANGE `last_failed_date` `login_event_date` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;";


  $string_queries[]="ALTER TABLE " . DATABASE_LOGINS . " DROP `last_success_date`;";


#prepare for future password stuff - allow saving of hashes, will be longer than 50 chars as current
  $string_queries[]="ALTER TABLE " . DATABASE_MEMBERS . " CHANGE `password` `password` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;";


#save record of who did the trade. sadly, no records exist for records thus far....not only if invoice or transfer!
# dont change date when updated

  $string_queries[]="ALTER TABLE " . DATABASE_TRADES . " CHANGE `trade_date` `trade_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;";

  $string_queries[]="ALTER TABLE " . DATABASE_TRADES . " ADD `member_id_author` VARCHAR(15) NULL DEFAULT NULL AFTER `type`;";

  $string_queries[]="ALTER TABLE " . DATABASE_TRADES . " CHANGE `category` `category_id` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0';";
  $string_queries[]="UPDATE " . DATABASE_TRADES . " SET `member_id_author`=(
    CASE WHEN type = \"A\" THEN \"admin\"
	ELSE member_id_from
	END
	);";

  $string_queries[]="UPDATE " . DATABASE_TRADES . " SET `type`=(
    CASE WHEN type = \"A\" THEN \"T\"
	ELSE type
	END
	);";

  $string_queries[]="ALTER TABLE  " . DATABASE_TRADES . " CHANGE `trade_date` `trade_date` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;";

#CT pending - table to share same structure as trades as much as poss. 
#CT including NOT swapping round invoices when they are committed (retain the info that a trade originated as an invoice in the trade table). A transfer from member1 to member2 is from=member1 to=member2 an invoice that is sent by member2 for member1 to pay is STILL from=member1 to=member2. 

  $string_queries[]="ALTER TABLE  " . DATABASE_TRADES_PENDING . "  CHANGE `trade_date` `trade_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;";
  $string_queries[]="ALTER TABLE " . DATABASE_TRADES_PENDING . " CHANGE `typ` `type` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
  $string_queries[]="ALTER TABLE " . DATABASE_TRADES_PENDING . " CHANGE `category` `category_id` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0';";
  $string_queries[]="ALTER TABLE " . DATABASE_TRADES_PENDING . " CHANGE `id` `trade_pending_id` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT;";
  $string_queries[]="ALTER TABLE " . DATABASE_TRADES_PENDING . " ADD `member_id_author` VARCHAR(15) NULL DEFAULT NULL AFTER `type`;";

#CT this is the slightly crazy inversion of the invoices goes...move values over to a temp column
  $string_queries[]="ALTER TABLE " . DATABASE_TRADES_PENDING . " ADD `member_id_to_temp` VARCHAR(15) NULL DEFAULT NULL AFTER `type`;";
  $string_queries[]="UPDATE " . DATABASE_TRADES_PENDING . " SET member_id_to_temp=member_id_to WHERE `type`=\"I\";";
  $string_queries[]="UPDATE " . DATABASE_TRADES_PENDING . " SET member_id_to=member_id_from WHERE `type`=\"I\";";
  $string_queries[]="UPDATE " . DATABASE_TRADES_PENDING . " SET member_id_from=member_id_to_temp WHERE `type`=\"I\";";
  $string_queries[]="ALTER TABLE " . DATABASE_TRADES_PENDING . " DROP `member_id_to_temp`;";

#CT this is the slightly crazy inversion of the invoices decision goes...move values over to a temp column
  $string_queries[]="ALTER TABLE " . DATABASE_TRADES_PENDING . " ADD `member_to_decision_temp` VARCHAR(15) NULL DEFAULT NULL AFTER `type`;";
  $string_queries[]="UPDATE " . DATABASE_TRADES_PENDING . " SET member_to_decision_temp=member_to_decision WHERE `type`=\"I\";";
  $string_queries[]="UPDATE " . DATABASE_TRADES_PENDING . " SET member_to_decision=member_from_decision WHERE `type`=\"I\";";
  $string_queries[]="UPDATE " . DATABASE_TRADES_PENDING . " SET member_from_decision=member_to_decision_temp WHERE `type`=\"I\";";
  $string_queries[]="ALTER TABLE " . DATABASE_TRADES_PENDING . " DROP `member_to_decision_temp`;";

#CT this retains a record of who was the source of the trade...unknown right now, so setting to system
  $string_queries[]="UPDATE " . DATABASE_TRADES_PENDING . " SET `member_id_author`=\"system\"; ";

#CT renaming and rejigging. if id is not used - the member_id must be unique though
  $string_queries[]="ALTER TABLE " . DATABASE_INCOME_TIES . " CHANGE `id` `income_tie_id` INT(11) NOT NULL AUTO_INCREMENT;";
  $string_queries[]="ALTER TABLE " . DATABASE_INCOME_TIES . " CHANGE `tie_id` `member_id_to` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
  $string_queries[]="ALTER TABLE " . DATABASE_INCOME_TIES . "  ADD `updated_at` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP  AFTER `percent`;";
  $string_queries[]="ALTER TABLE " . DATABASE_INCOME_TIES . " DROP `income_tie_id`;";
  $string_queries[]="ALTER TABLE " . DATABASE_INCOME_TIES . " ADD UNIQUE( `member_id`);";

#password reset
  $string_queries[]="CREATE TABLE " . DATABASE_PASSWORD_RESET . " (
  `member_id` varchar(255) NOT NULL,
  `password_reset_token` varchar(255) NOT NULL,
  `password_reset_token_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

  $string_queries[]="ALTER TABLE " . DATABASE_PASSWORD_RESET . "
  ADD UNIQUE KEY `member_id` (`member_id`);";

$string_queries[]="ALTER TABLE " . DATABASE_MEMBERS . "
  ADD UNIQUE KEY `member_id` (`member_id`);";

//CT sets up more useful categories of actions - all "send mail" events are category S, and action remains the original value.
$string_queries[]="UPDATE " . DATABASE_LOGGING .  " set category='" . LOG_SEND . "' where category='" . LOG_SEND_UPDATE_WEEKLY . "' or category='" . LOG_SEND_UPDATE_DAILY . "' or category='" . LOG_SEND_UPDATE_MONTHLY . "';";


$string_queries[]="INSERT INTO " . DATABASE_SETTINGS .  "  (`id`, `name`, `display_name`, `typ`, `current_value`, `options`, `default_value`, `max_length`, `descrip`, `section`) VALUES (NULL, 'USER_MODE', 'Enable usermodes', 'bool', 'TRUE', '', 'TRUE', '', 'Do you want to allow members in admin role to explicitly enter admin mode before access to certain actions?', '4');";
//$string_queries[]="UPDATE " . DATABASE_LOGGING .  "set `admin_id`='" . SYSTEM_ACCOUNT_ID . "' where `admin_id`='EVENT_SYSTEM';";

try{
  $complete = doCreateScript();

} catch(Exception $e){
  $statusMessage->Error($e->getMessage());
  $p->DisplayPage("");
}

function doCreateScript(){
  global $string_queries, $cDB;
  /* CT counters for feedback */
  $success_count=0;
  $fail_count=0;
  $total_count=0;
  echo "<h2>Starting create script</h2>";
  foreach ($string_queries as $string_query) {
    $total_count++;
    if($cDB->Query($string_query)){
      $r = "Success";
      $success_count++;
    }else{
      $r = "<font color=\"red\">Fail</font>";
      $fail_count++;
    }
    echo "{$total_count})  Run \"" . substr($string_query, 0, 30) . "\"...{$r}<br />";

  }
  echo "<h2>Script completed - {$total_count} total - {$fail_count} Fail, {$success_count} Success.</h2>";

}

?>
