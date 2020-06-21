<?php

require_once VENDOR_PATH .  'ezyang/htmlpurifier/library/HTMLPurifier.auto.php';
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

//CT create entity for the current user. move somewhere else
$cUser = new cMemberSelf();
$cUser->RegisterWebUser();
//global $site_settings;
include_once(CLASSES_PATH ."class.page.php");


?>
