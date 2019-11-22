<?php

// This include file is for processes that only need to be run periodically, 
// using the cSystemEvent class's TimeForEvent() method to limit their
// execution so that they don't bog the system down.
//
// This file is meant to be included in one or more pages in the system
// which is regularly visited by users.  It's best to include it AFTER a page has
// been displayed, also to prevent excessive page load times.


// The following will expire listings for inactive members as set
// in inc.config.php.  
//CT I think it's customary to use $event as error (try catch etc), so changed varname 
//$e = new cSystemEvent(LOG_ACCOUNT_INACTIVATE_AUTO);
// $log_event = new cLoggingSystemEvent();
// //if(EXPIRE_INACTIVE_ACCOUNTS and $event->TimeForEvent(LOG_ACCOUNT_INACTIVATE_AUTO, null)) {
// if(EXPIRE_INACTIVE_ACCOUNTS) {
// 	//CT removed timeforevent - as there was no interval set, assume that it should be run every time, so can call it directly
// 	$members = new cMemberGroup;
// 	$members->ExpireListings4InactiveMembers();

// 	$log_event->Save();
// }


// The following three events are for automatic email updates regarding new modified 
// listings
//CT test

try{
	print(!$site_settings->getKey('EMAIL_LISTING_UPDATES'));
	if($site_settings->getKey('EMAIL_LISTING_UPDATES')){
		//MONTHLY
		$log_event = new cLoggingSystemEvent();
		//if($log_event->TimeForEvent(LOG_SEND_UPDATE_MONTHLY, MONTHLY*1440)){
		if($log_event->TimeForEvent(LOG_SEND, LOG_SEND_UPDATE_MONTHLY, MONTHLY)){
			$mailer = new cMail;
			$mailed = $mailer->EmailListingUpdates(LOG_SEND_UPDATE_MONTHLY);
			//print("mailed " .$mailed);
			//CT uses the log through mailer instead of direct from logevent...
			//if($mailed) $mailer->logEvent(LOG_SEND_UPDATE_MONTHLY);
				//$log_event->CreateSystemEvent(LOG_SEND, LOG_SEND_UPDATE_MONTHLY);
				//$log_event->Save();
			//}
		}
		// WEEKLY
		$log_event = new cLoggingSystemEvent();
		//if($log_event->TimeForEvent(LOG_SEND_UPDATE_WEEKLY, WEEKLY*1440)){
		//CT category, action, desired interval
		if($log_event->TimeForEvent(LOG_SEND, LOG_SEND_UPDATE_WEEKLY, WEEKLY)){
			$mailer = new cMail;
			$mailed = $mailer->EmailListingUpdates(LOG_SEND_UPDATE_WEEKLY);
			//CT all send events are potentially logged, so is done through sendmail object
			//print("mailed " .$mailed);
			//CT uses the log through mailer instead of direct from logevent.
			//if($mailed) $mailer->logEvent(LOG_SEND_UPDATE_WEEKLY);

			// if($mailed){
			// 	$log_event->CreateSystemEvent(LOG_SEND_UPDATE_WEEKLY);
			// 	$log_event->Save();
			// }
		}
		//DAILY
		$log_event = new cLoggingSystemEvent();
		//if($log_event->TimeForEvent(LOG_SEND_UPDATE_DAILY, DAILY*1440)){
		if($log_event->TimeForEvent(LOG_SEND, LOG_SEND_UPDATE_DAILY, DAILY)){
			$mailer = new cMail;
			$mailed = $mailer->EmailListingUpdates(LOG_SEND_UPDATE_DAILY);
			//print("mailed " .$mailed);
			//CT uses the log through mailer instead of direct from logevent...
			//if($mailed) $mailer->logEvent(LOG_SEND_UPDATE_DAILY);
			
			// if($mailed){
			// 	$log_event->CreateSystemEvent(LOG_SEND_UPDATE_DAILY);
			// 	$log_event->Save();
			// }
		}
	}
}catch(Exception $e){
	$cStatusMessage->Error($e->getMessage());
}


?>
