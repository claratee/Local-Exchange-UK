<?php

/************************************************************
This file includes necesary class files and other include files.
It also defines global constants, and kicks off the session. 
It should be included by all pages in the site.  It does not
need to be edited for site installation, and in fact should
only be modified with care.
************************************************************/

/*********************************************************/
/******************* GLOBAL CONSTANTS ********************/


// These constants should only be changed with extreme caution
define("REDIRECT_ON_ERROR", true);
define("FIRST", true);
define("LONG_LONG_AGO", "1970-01-01");
define("FAR_FAR_AWAY", "2040-01-01");
define("ACTIVE","A");
define("INACTIVE","I");
define("EXPIRED","E");
define("DISABLED","D");
define("LOCKED","L");
define("BUYER","B");
define("SELLER","S");
define("POSITIVE","3");
define("NEGATIVE","1");
define("NEUTRAL","2");
define ("OFFER_LISTING_CODE", "O");
define ("WANT_LISTING_CODE", "W");
define("DAILY",1);
define("WEEKLY",7);
define("MONTHLY",30);
define("NEVER",0);


//CT moved from the error class file - they were declared globally outside of the class
define ("ERROR_ARRAY_SEVERITY", 0);
define ("ERROR_ARRAY_MESSAGE", 1);
define ("ERROR_ARRAY_FILE", 2);
define ("ERROR_ARRAY_LINE", 3);

define ("ERROR_SEVERITY_INFO",1);
define ("ERROR_SEVERITY_LOW",2);
define ("ERROR_SEVERITY_MED",3);
define ("ERROR_SEVERITY_HIGH",4);
define ("ERROR_SEVERITY_STOP",5);




// The following constants are used for logging. Add new categories if
// needed, but edit existing ones with caution.
//CT renamed so they are easier to see what they are for. 
//who
// define("TRADE_BY_MEMBER","T"); //CT normal trade
// define("TRADE_BY_ADMIN","A"); 
// //define("TRADE_ENTRY","T"); //replaced
// define("TRADE_BY_SYSTEM","S"); //CT NEW - automatic trade log 

//types
//define("TRADE","T"); // CT replaced with TRADE_TYPE_TRANSFER

//note new column "member_id_author" gives record of who did the strade -so we can repurpose "type" to show direction and anything else needed
//types - also for logging admin_activity as category
//ACTION in trade table
define("TRADE_TYPE_TRANSFER","T"); //CT transfer
define("TRADE_TYPE_INVOICE","I"); //CT NEW invoice. yes, we are tracking how the trade was done - as a transfer or invoice. 
define("TRADE_TYPE_MONTHLY_FEE","M"); //

//TYPE in trade table -reversals. for info only (status will be I)
define("TRADE_TYPE_REVERSAL","R"); //CT NEW info only - record. 
define("TRADE_TYPE_MONTHLY_FEE_REVERSAL", "N"); //CT dont see whoy this is not just a reversal.

//STATUS in trade table
define("TRADE_STATUS_REVERSED","R"); //CT - REVERSED. new
define("TRADE_STATUS_APPROVED","V"); //CT - valid - meaning active, done, approved.
//CT 
//STATUS in pending table - whether they are counted or not
define("TRADE_PENDING_STATUS_OPEN","O"); //CT - open in pending table
define("TRADE_PENDING_STATUS_FINAL","F"); //CT - as above. final in pending table 
define("TRADE_PENDING_STATUS_CANCELLED","W"); //CT - withdrawn NEW - cancelled. hide from everywhere.

//DECISION -Pending  - moved from pending hardcode
 define("TRADE_PENDING_DECISION_DEFAULT","1"); //1 = Member hasn't made a decision regarding this trade - either it is Open or it has been Fulfilled (see 'status' column)
 define("TRADE_PENDING_DECISION_REMOVED","2"); //2 = Member has removed trade from his own records
 define("TRADE_PENDING_DECISION_REJECTED","3"); //3 = Member (payee) has rejected this trade
 define("TRADE_PENDING_DECISION_ACCEPTED_REJECTED","4"); //4 = Member has accepted that this trade has been rejected

//CATEGORIES for logging 
define("LOG_TRADE","T"); //CT transfer
define("LOG_ACCOUNT","A"); //CATEGORY new. category for account admin events.
define("LOG_SEND","S"); //CATEGORY CT new. category for mail events.
define("LOG_FEEDBACK","F"); // Logging event category


//ACTIONS for logging.
//CT note - actions come from the trade object - TRADE_TYPE_TRANSFER etc
define("LOG_ACCOUNT_CREATE","C"); // new. create
define("LOG_ACCOUNT_ACTIVATE","A"); // new. make active. corresponds with the status in member table
define("LOG_ACCOUNT_INACTIVATE","I"); // new. make inactive. corresponds with the status in member table.
define("LOG_ACCOUNT_INACTIVATE_AUTO","E"); // refactor of ACCOUT_EXPIRATION
define("LOG_ACCOUNT_ARCHIVE","X"); // new. archiving account for GDPR
//CT actions
define("LOG_SEND_UPDATE_DAILY","D"); // daily email update - refactor of DAILY_LISTING_UPDATES
define("LOG_SEND_UPDATE_WEEKLY","W"); // weekly email update - refactor of WEEKLY_LISTING_UPDATES
define("LOG_SEND_UPDATE_MONTHLY","M"); // monthly email update - refactor of MONTHLY_LISTING_UPDATES

//
define("LOG_SEND_OUT_OF_BALANCE","B"); // new. Out of balance warning mail
define("LOG_SEND_WELCOME","W"); // send mail on creation of account or admin action password reset
define("LOG_SEND_PASSWORD_RESET","P"); // send mail on password reset
//CT these are a bit messy. sort out
define("LOG_SEND_TRADE_PENDING_REJECTED","R"); // send mail on rejection of trade
define("LOG_SEND_TRADE_PENDING_ACCEPT_REJECTION","A"); // send mail on accepting of trade
define("LOG_SEND_TRADE_PENDING_RESENT","Q"); // send mail on accepting of trade
define("LOG_SEND_TRADE_PENDING_ACCEPT","U"); // send mail on accepting of trade



//these as above, but content and recipient gets saved
define("LOG_SEND_ALL","A"); // send mail to all members - refactored LOG_SEND_ALL
define("LOG_SEND_CONTACT","C"); // send mail via contact form
//define("LOG_SEND_TRADE","trade"); // send mail on trade
//define("LOG_SEND_TRADE","T"); // send mail 
//define("LOG_SEND_TRADE","trade"); // send mail on trade


//types



// define("TRADE_BY_ADMIN","A");
// define("TRADE_ENTRY","T");
// define("TRADE_TYPE_REVERSAL","R");
// define("TRADE_TYPE_MONTHLY_FEE_REVERSAL", "N");
define("FEEDBACK","F"); // Logging event category
define("LOG_FEEDBACK_BY_ADMIN","A"); //CT refactored FEEDBACK_BY_ADMIN

//CT new. user object can go into explicit admin mode.
define("USER_MODE_ADMIN", "admin");
define("USER_MODE_DEFAULT", "default");

/*
//CT TODO - the above is great, but it would be better if 
* log more actions incl success/fail details
* separate the labels from the logic (tradeentry etc)
* use more than one letter so its readable
* namespace the configs with a prefix like LOG_*.

actions
making a trade for someone (incl to and from)
creating account
editing
resetting password
sending mail
editing page
creating page
editing post
creating post
deleting post
*/

/*********************************************************/
define("LOCALX_VERSION", "2.0.alpha-claratee-1");

/**********************************************************/
/***************** DATABASE VARIABLES *********************/

define ("DATABASE_LISTINGS","lets_listings");
define ("DATABASE_PERSONS","lets_person");
define ("DATABASE_INCOME_TIES","lets_income_ties");
define ("DATABASE_MEMBERS","lets_member");
//define ("DATABASE_TRADES","lets_trades");
define ("DATABASE_TRADES","lets_trades");
define ("DATABASE_TRADES_PENDING","lets_trades_pending");
define ("DATABASE_LOGINS","lets_logins");
define ("DATABASE_PASSWORD_RESET","lets_password_reset_token");
define ("DATABASE_LOGGING","lets_admin_activity");
define ("DATABASE_USERS","lets_member");
define ("DATABASE_CATEGORIES", "lets_categories");
define ("DATABASE_FEEDBACK", "lets_feedback");
define ("DATABASE_REBUTTAL", "lets_feedback_rebuttal");
define ("DATABASE_NEWS", "lets_news");
define ("DATABASE_UPLOADS", "lets_uploads");
define ("DATABASE_SESSION", "lets_session");
define ("DATABASE_SETTINGS", "lets_settings");
define ("DATABASE_PAGE", "lets_cdm_pages");
define ("DATABASE_CONTACT", "lets_contact"); //record of emails sent by system - when logging turned on
// CT views for speed
define ("DATABASE_VIEW_CONTACTS", "lets_view_emails");
define ("DATABASE_VIEW_MEMBER", "lets_view_member");


/*********************************************************/
// This section is deprecated.  It has been relocated to 
// inc.config.php, and would be removed but for a bunch of
// references to the following two, now bogus, values...

// TODO: Clean up all references and remove the two lines below
define ("SITE_SECTION_DEFAULT",-1);		
define ("SITE_SECTION_OFFER_LIST",0); 
/*********************************************************/


$global = ""; 	// $global lets other includes know that 
					// inc.global.php has been included

//CT use this to map to the config appropriate for your context
require_once("inc.config-local.php");
//include_once("inc.config-local.php");
//include_once("inc.config-remote.php");

/* CT third party cleaner for html - prevent xss atttack. */
require_once VENDOR_PATH .  'ezyang/htmlpurifier/library/HTMLPurifier.auto.php';




/* Initial session handling code starts */
//CT not writing to db - doesnt
//require_once("session_handler.php");
session_name("LOCAL_EXCHANGE");
session_start();
//ob_start();
/* Initial session handling code ends */

//CT TODO: tidy and consolidate 
//CT campaign against spaghetti code...linking all classes from here instead of in other files
//BASE CLASSES
include_once(CLASSES_PATH ."class.basic.php");
include_once(CLASSES_PATH ."class.single.php");
include_once(CLASSES_PATH ."class.collection.php");
include_once(CLASSES_PATH ."class.basic2.php");
//HELPERS
include_once(CLASSES_PATH ."class.logging.php");
include_once(CLASSES_PATH ."class.loggingSystemEvent.php");
include_once(CLASSES_PATH ."class.datetime.php");
include_once(CLASSES_PATH ."class.statusMessage.php"); //CT renamed from error so it can handle success messages too
include_once(CLASSES_PATH ."class.site.php");
include_once(CLASSES_PATH ."class.queries.php");
include_once(CLASSES_PATH ."class.database.php");
include_once(CLASSES_PATH ."class.settings.php");

//UPLOAD
include_once(CLASSES_PATH ."class.uploads.php");
include_once(CLASSES_PATH ."class.uploadsGroup.php");

//INFO PAGES
include_once(CLASSES_PATH ."class.info.php");
include_once(CLASSES_PATH ."class.infoUtils.php");
include_once(CLASSES_PATH ."class.infoGroupUtils.php");

//PERSON
include_once(CLASSES_PATH ."class.person.php");
include_once(CLASSES_PATH ."class.personUtils.php");
//include_once(CLASSES_PATH ."class.personSecondary.php");

//NOTIFICATION
include_once("classes/class.mail.php");

//FEEDBACK
include_once(CLASSES_PATH ."class.feedback.php");
include_once(CLASSES_PATH ."class.feedbackSummary.php");
include_once(CLASSES_PATH ."class.feedbackGroup.php");
include_once(CLASSES_PATH ."class.feedbackRebuttal.php");
include_once(CLASSES_PATH ."class.feedbackRebuttalGroup.php");

//CATEGORY
include_once(CLASSES_PATH ."class.category.php");
include_once(CLASSES_PATH ."class.categoryGroup.php");
//LISTING
include_once(CLASSES_PATH ."class.listing.php");
include_once(CLASSES_PATH ."class.listingGroup.php");
include_once(CLASSES_PATH ."class.listingGroupUtils.php");
include_once(CLASSES_PATH ."class.listingUtils.php");
// TRADE
include_once(CLASSES_PATH ."class.trade.php");
include_once(CLASSES_PATH ."class.balanceTotal.php");

include_once(CLASSES_PATH ."class.tradeUtils.php");
include_once(CLASSES_PATH ."class.tradeSummary.php");
include_once(CLASSES_PATH ."class.tradeGroup.php");
//CT this should be replaced
include_once(CLASSES_PATH ."class.tradesPending.php");
//CT new....wip
// include_once(CLASSES_PATH ."class.tradeGroupPending.php");

include_once(CLASSES_PATH ."class.incomeTies.php");

//MEMBERS
include_once(CLASSES_PATH ."class.member.php");
include_once(CLASSES_PATH ."class.memberSummary.php");
//include_once(CLASSES_PATH ."class.memberConcise.php");
include_once(CLASSES_PATH ."class.memberSelf.php");
include_once(CLASSES_PATH ."class.memberUtils.php");
include_once(CLASSES_PATH ."class.memberLabel.php");
include_once(CLASSES_PATH ."class.memberGroup.php");
// include_once(CLASSES_PATH ."class.memberGroupMenu.php");
include_once(CLASSES_PATH ."class.login_history.php");
include_once(CLASSES_PATH ."class.passwordReset.php");

//NEWS
include_once(CLASSES_PATH ."class.news.php");
include_once(CLASSES_PATH ."class.newsGroup.php");


//CT create entity for the current user. 
$cUser = new cMemberSelf();
$cUser->RegisterWebUser();
//global $site_settings;
include_once(CLASSES_PATH ."class.page.php");


//CT this should be done in the php ini, or on the local config. not global
// The following is necessary because of a PHP 4.4 bug with passing references
//error_reporting( E_ALL & ~E_NOTICE );

// For maintenance, see inc.config.php
if(DOWN_FOR_MAINTENANCE and !$running_upgrade_script) {
	$p->DisplayPage(MAINTENANCE_MESSAGE);
	exit;
}

// [chris] Uncomment this line to surpress non-fatal Warning and Notice errors
error_reporting(E_ALL &~ (E_NOTICE | E_WARNING));	
//CT: todo - put somewhere better. create site class
// function showMessage($msg){
// 	echo "<p>" . $msg . "</p>";
// }
//CT Replaced with statusMessage error class


?>
