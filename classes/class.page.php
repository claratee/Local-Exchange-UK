<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

class cPage {
	var $page_title;
	var $page_title_image; // Filename, no path
	var $page_content; // Filename, no path
	var $page_header;	// HTML
	var $page_footer;	// HTML
	var $page_sidebar; 	// An array of cMenuItem objects
	var $keywords;		
	var $site_section;
	var $top_buttons;		// An array of cMenuItem objects    TODO: Implement top buttons...
	var $errors;			// array. CT: added for debugging. todo - show for admin only? array.
	var $page_msg;			// CT: todo - show actions completed and other messages?

	function __construct() {
		global $cUser, $site_settings;
		
		$this->keywords = SITE_KEYWORDS;
		//print_r('page');
		//print_r($site_settings);
		//print_r($site_settings->getKey('SITE_SHORT_TITLE'));
		$variables = $site_settings->getStrings();

		$string = file_get_contents(TEMPLATES_PATH . 'header.php', TRUE);
		$this->page_header = $this->ReplacePlaceholders($string);

		$string = file_get_contents(TEMPLATES_PATH . 'footer.php', TRUE);
		$this->page_footer = $this->ReplacePlaceholders($string);
		
		//CT move to template
		if($cUser->IsLoggedOn()){
			$login_toggle_link = 'member_logout.php';
			$login_toggle_text = 'Log out';
		} else {
			$login_toggle_link = 'login.php';
			$login_toggle_text = 'Log in';
		}
		if(!($site_settings->getKey('USER_MODE')) && $cUser->getMemberRole() > 0){
			$admin_menu_link = "<li><a href=\"" . HTTP_BASE . "/admin_menu.php\">Administration menu</a></li>";
		}else{
			$admin_menu_link="";
		}
		//you can switch mode via url
		if (!empty($_REQUEST['mode'])) $cUser->changeMode($_REQUEST['mode']);
	
	
		
		$variables = new stdClass();
		$variables = (object) [
		    'login_toggle_link' => $login_toggle_link,
		    'login_toggle_text' => $login_toggle_text,
		    'admin_menu_link' => $admin_menu_link
		];



		//$string = file_get_contents(TEMPLATES_PATH . '/sidebar.php', TRUE);
		$string = "<!--START include navigation -->
			<div class=\"navigation\">
					<input type=\"checkbox\" id=\"nav\" class=\"hidden\" />
					<label for=\"nav\" class=\"nav-open\"><i></i><i></i><i></i></label>
					<div class=\"nav-container\">
						<nav>
							<ul class=\"menu\">
								{$site_settings->getKey("MAIN_MENU")}
							</ul>
						</nav>
					</div>
				</div>
			<!--END include navigation -->";
		$this->page_sidebar = $this->ReplacePlaceholders($string, $variables);
			
	}	
	//CT replaces strings {LIKE-THIS} with either settings file strings
	// this is a temporary mesasure til we get moustache or a proper template engine
	function ReplacePlaceholders($string, $variables=null){
		global $site_settings, $cStatusMessage;
		//CT first replace the bits that are from constants
		$settings = $site_settings->getStrings();
					
		//print_r($GLOBALS);
		
		foreach($settings as $key => $value){
		    $string = str_replace("{{" . $key . "}}", $value, $string);
		}
		if(!empty($variables)) {
			//$cStatusMessage->Error(print_r($variables, true));
			foreach($variables as $key => $value){
				//$cStatusMessage->Error($key . " " . $value);
			    $string = str_replace("{{" . $key . "}}", $value, $string);
			}
		}

		// CT then do properly
		return $string;
	}			
	function ReplaceVarInString($string, $varname, $value){
		return str_replace("{{" . $varname . "}}", $value, $string);
	}

	//CT adds a new key/value to querystring, replacing an old one if exists.
	function addQueryStringVar($url, $key, $value) {
		$url = $this->removeQueryStringVar($url, $key); //CT added removal - so this replaces

		// $url = preg_replace('/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
		// $url = substr($url, 0, -1);
		
		if (strpos($url, '?') === false) {
			return ($url . '?' . $key . '=' . $value);
		} else {
			return ($url . '&' . $key . '=' . $value);
		}
		
	}
	//CT thanks random stranger on the internet...
	function removeQueryStringVar($url, $key) {
		$url = preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
		$url = substr($url, 0, -1);
		return $url;
		
	}


	function AddTopButton ($button_text, $url) { // Top buttons aren't integrated into header yet...
		$this->top_buttons[] = new cMenuItem($button_text, $url);
	}

	function MakeDocHeader() {
		global $cUser, $site_settings;
		
		$title = (isset($this->page_title)) ? $this->page_title : "";
		$string = file_get_contents(TEMPLATES_PATH . 'doc_header.php', TRUE);
		$string = $this->ReplacePlaceholders($string);
		$string = $this->ReplaceVarInString($string, '$title', $title);
		return $string;
	}
	function MakePageHeader() {
		return $this->page_header ;
	}
	//CT: simple adding of errors
	function AddError($error){
		$this->errors[] = $error;
	}

									
	function MakePageFooter() {
		return $this->Wrap($this->page_footer, "div", "footer");
	}	
	function MakeDocFooter() {
		return file_get_contents(TEMPLATES_PATH . 'doc_footer.php', TRUE);
	}	
	function MakePageMenu() {
		return $this->page_sidebar;
	}	
	// function makeLinkSelf($var, $value){
	// 	global $cUser;
		
	// 	$location = $_SERVER['REQUEST_URI'];

	// 	if(!empty($replace_array)){
	// 		//$replace_key = key($replace_array);
	// 		//$replace_query = "{$replace_key}={$replace_array[$replace_key]}";			
	// 	}
	// 	$queries=$replace_query;
	// 		foreach ($_GET as $key => $value) {
	// 		if($key != $replace_key) $queries .=  "&{$key}={$value}";
	// 	}

	// 	//$querystring = $_SERVER['QUERY_STRING'];
	// 	$link = "{$location}?{$queries}"; 
		
	// 	return "{$link}";

	// } 
	//CT new - needed for emails 
	function stripLineBreaks($string){
		return preg_replace( "/\r|\n/", "", $string);
	}

	//CT admin mode needs to be entered in specifically by the user. strip at the top of the page
	function makeModeToggleButton(){
		global $cUser, $site_settings, $p;
		//CT put somewhere sensible - this aint it
		if($cUser->getMemberRole() > 0 AND $cUser->IsLoggedOn() AND $site_settings->getKey('USER_MODE')){
			//put links into admin mode

			$location = $_SERVER['PHP_SELF'];
			$queries ="";
			foreach ($_GET as $key => $value) {
				if($key != "mode") $queries .=  "&{$key}={$value}";
			}

			//$querystring = $_SERVER['QUERY_STRING'];

			if($cUser->getMode() == USER_MODE_ADMIN) {
				$desired_mode="default";
				$admin_toggle_text="Admin mode is ON. <a href=\"admin_menu.php\">Admin menu</a>";
				$checked = "checked";
				$class="on";
			} else{			
				$desired_mode="admin";
				$admin_toggle_text="Admin mode is OFF.";
				$checked = "";
				$class="off";
			}
	//			  <input type=\"checkbox\" {$checked} onclick=\"javascript:window.location='{$location}?mode={$desired_mode}'\">
			$link = $p->addQueryStringVar($_SERVER['REQUEST_URI'], "mode", $desired_mode);

			$toggle_button ="<div class=\"switch-strip {$class}\"><label class=\"switch\">
				  <input type=\"checkbox\" {$checked} onclick=\"javascript:window.location='{$link}'\">
				  <span class=\"slider round\"></span> 
				</label> {$admin_toggle_text}</div>";
			return "{$toggle_button}";

		} else{
			return "";
		}
	}

	function MakePageContent() {
		global $cUser;
		if(strlen($this->page_content)<1) { 
			// set error message - something is wrong!
			$this->AddError('No page content set');
			$this->page_content = "Nothing to show.";
		}
		
		$title = "<h1>{$this->page_title}</h1>";
		$content = "";
		if($cUser->getRestriction()==true) $content .= "<div class=\"warning\">Warning</div>";
		//print_r($cStatusMessage->arrErrors);
		$content .= $title  . $this->MakeErrorContent() . $this->MakeInfoContent() . $this->page_content;
		return $this->Wrap($content, "div", "content");
	}
	//CT validators for form inputs
	// function isDateValid($date_string, $criteria=null){
	// 	//CT datestring should be yyyy-mm-dd
	// 	list($y, $m, $d) = array_pad(explode('-', $date_string, 3), 3, 0);
	// 	if(ctype_digit("$y$m$d") && checkdate($m, $d, $y)){
	// 		if ($criteria = paststrtotime("0:00")> strtotime($date_string)) return true;
	// 	}
	// 	return false;
	// }


	function isDateValid($date_string, $min_date_string=null, $max_date_string=null){
		//CT datestring should be yyyy-mm-dd
		list($y, $m, $d) = array_pad(explode('-', $date_string, 3), 3, 0);
		if(ctype_digit("$y$m$d") && checkdate($m, $d, $y)){
			//CT if set, can't be smaller than min, or larger than max. 
			if (!empty($min_date_string) && (strtotime($date_string) < strtotime($min_date_string))) {
				return false;
			}
			if (!empty($max_date_string) && (strtotime($date_string) > strtotime($max_date_string))) {
				return false;
			}
			return true;
		}
		return false;
	}
	function isEmailValid($email, $domain_check = false){
			//CT: check email - with optional DNS
	    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
	        if ($domain_check && function_exists('checkdnsrr')) {

	            list (, $domain)  = explode('@', $email);
	            if (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A')) {
	                return true;
	            }
	            return false;
	        }
	        return true;
	    }
	    return false;
	}


	function MakeErrorContent() {
		//global $cUser;
		global $cStatusMessage;
		//CT: fix this. append errors sent by old system to show both
		//$this->errors=array_merge($this->errors,$cStatusMessage->arrErrors);
		//$this->errors=array_merge($this->errors,$cStatusMessage->arrErrors);
		//$this->AddError("test");
		if(count($cStatusMessage->arrErrors)<1) return "";
		//$output = "<div class=\"errors\"><p>Messages:</p><ul>";
		//var_dump
		$output ="";
		foreach ($cStatusMessage->arrErrors as $error) $output .= "<li>{$error[ERROR_ARRAY_MESSAGE]}</li>";
		$output = "<div class=\"response fail\"><ul>{$output}</ul></div>";
		return $output;
	}	
	function MakeInfoContent() {
		global $cStatusMessage;

		//global $cUser;
		//CT: fix this. append errors sent by old system to show both
		//$this->errors=array_merge($this->errors,$cStatusMessage->arrErrors);
		//$this->errors=array_merge($this->errors,$cStatusMessage->arrErrors);
		//$this->AddError("test");
		//if(count($cStatusMessage->arrInfo)<1) return "";
		//print($arrErrors);
		//$output = "<div class=\"errors\"><p>Messages:</p><ul>";
		//var_dump
		if(count($cStatusMessage->arrInfo)<1) return "";

		$output ="";
		foreach ($cStatusMessage->arrInfo as $info) $output .= "<li>{$info}</li>";
		$output = "<div class=\"response info\"><ul>{$output}</ul></div>";
		return $output;
	}	
	//CT: transitional functin - should be backwards compativle.		
	function DisplayPage($content="") {
		global $cStatusMessage, $cUser;

		if(strlen($content)>0) $this->page_content = $content;
		//print "cnte" . $p->page_content;
		$header = $this->MakePageHeader();
		//$output .= "<div class=\"main\">";
		$main = $this->MakePageMenu() . $this->MakePageContent();
		$main = $this->Wrap($main, "div", "main");
		$footer = $this->MakePageFooter();
		$adminStrip = $this->makeModeToggleButton();
		$page = $this->Wrap($adminStrip . $header . $main . $footer, "div", "page");
		//CT - wrap in div for control
		$output = $this->MakeDocHeader() . $page . $this->MakeDocFooter();
		print $output;
	}
	function MenuItemArray($string, $link){
		$arr = array(
			"string" => $string, 
			"link" => $link
		);
		return $arr;
	}	
	function Menu($array){
		//$array is text, link;
		$menu = "";
		foreach ($array as $key => $value){
			$menu .= $this->Wrap($value['string'], "li", null, $value['link']);
			//$menu .= $p->Wrap($value['string'], "li", null, $value['link']);
		}
		return $this->Wrap($menu, "ul");
	}
	//CT: new funtion for layout of text

    function Wrap($string, $elementName, $cssClass=null, $link=null){
		if (!empty($link)) $string=$this->Link($string, $link);
		
		$cText = (!empty($cssClass)) ? " class='{$cssClass}'" : "";
		
		return "<{$elementName} {$cText}>{$string}</{$elementName}>";
	}
	//CT todo - make better. bit of a hack
    function WrapLabelValue($label, $value){
    	$string = "<p class=\"line\">
			<span class=\"label\">{$label}</span>
			<span class=\"value\">{$value}</span>
			</p>";
		return $string;
	}
	function Link($string, $link){
		return "<a href='{$link}'>{$string}</a>";
	}
	function WrapForm($string, $action, $method="get", $cssClass=""){
		//default method as get, and cssclass
		return "<form action='{$action}' method='{$method}' class='{$cssClass}'>{$string}</form>";
	}
	//CT removed forms from PEAR...so this is a faff, but I dont have alternative library
	//create a form element
	//CT dropdown or select form element
	function PrepareFormSelector($selector_id, $array, $label_none=null, $selected_id=null, $css_class=null) {
		//the value for nothing selected as first element.
		$output = "";
		// first option of select - none selected
		if (!empty($label_none)) $output .= "<option value=\"\">-- {$label_none} --</option>";
		foreach($array as $key=>$item){
			$selected_attribute = ($key == $selected_id) ? " selected=\"selected\"" : "";
			$output .= "<option value=\"{$key}\" {$selected_attribute}>{$item}</option>";
		}
		//wrap option list in select element
		$class_attribute="";
		if (!empty($css_class)) $class_attribute = " class=\"{$css_class}\"";
		$output = "<select name=\"{$selector_id}\" id=\"{$selector_id}\"{$class_attribute}>{$output}</select>";
		return $output;		
	}
	//CT date selector - a bit hacked to avoid PEAR
	//sorry this is English only
	// WIP - decided to use date in input field instead, as its easier to input. 
	function PrepareDateSelector($selector_prefix, $selected_date=null, $custom_options=null){
	    $options = array(
	        'start_year' => 2001,
	        'end_year' => 2040
	    );
	    if(!empty($custom_options)){
		    //set the _options with the ones past in
		    foreach($custom_options as $$key => $value) $options[$key]=$value;    	
	    }
	    
	    


	    $array_years = array();
	    for($i=$options['start_year']; $i<=$options['end_year']; $i++){
	    	$array_years["{$i}"] = $i;
	    }

	    $array_days = array();
	    for($i=1; $i<=31; $i++){
	    	$array_days["{$i}"] = $i;
	    }
	    $selector_id = $selector_prefix . "_years";
	    $dropdown_years = $this->PrepareFormSelector($selector_id, $array_years, null, null, "dropdown_years");
	    $selector_id = $selector_prefix . "_months";
	    $dropdown_months = $this->PrepareFormSelector($selector_id, ARRAY_MONTHS, null, null, "dropdown_months");
	    $selector_id = $selector_prefix . "_days";

	    $dropdown_days = $this->PrepareFormSelector($selector_id, $array_days, null, null, "dropdown_days");
		
	    return "{$dropdown_days} {$dropdown_months} {$dropdown_years}";

	}

	//CT good for simple form elements
	function WrapFormElement($type, $name, $label='', $value='', $cssClass=''){
		switch ($type){
			case 'text':
				$output = "<label class='text'><span>{$label}:</span><input type='text' value='{$value}' name='{$name}'  /></label>";
			break;
			case 'password':
				$output = "<label class='password'><span>{$label}:</span><input type='password' name='{$name}' /></label>";
			break;
			case 'hidden':
				$output = "<input type='hidden' value='{$value}' name='{$name}' /></label>";
			break;
			case 'textarea': 
				$output = "<label class='textarea'><span>{$label}:</span><textarea name='{$name}' id='{$name}' rows='5'>{$value}</textarea></label>";
			break;
			case 'checkbox':
				$output = "<label class='checkbox'><input type='text' value='{$value}' name='{$name}' /><span>{$label}</span></label>";
			break;
			case 'submit':
				$output = "<input type='submit' value='{$value}' name='{$name}' />";
			break;
			default:
				$output= 'nothing';
				//
		}
		if($type !="hidden") $output=$this->Wrap($output, 'div', "l_".$type);
		return $output;
	}
	function FormatShortDate($d){
		global $site_settings;
		//localise to country
		//return date_format(strtotime($d), $site_settings->getKey('SHORT_DATE'));
		return date_format(date_create($d), 'd/m/y');
	}
	function FormatLongDate($d){
		//localise to country
		return date_format(date_create($d), $site_settings->getKey('LONG_DATE'));

	}
	
}

$p = new cPage;

?>
