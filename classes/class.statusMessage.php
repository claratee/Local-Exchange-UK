<?php
if (!isset($global))
{
	die(__FILE__." was included directly.  This file should only be included via inc.global.php.  Include() that one instead.");
}



class cStatusMessage
{

	var $retval;
	var $retobj;

	var $arrErrors;
	var $arrInfo;

	function __construct()
	{
		//init
		$this->arrErrors = array();
		$this->arrInfo = array();

		//print_r($_SESSION);
		if (!empty($_SESSION["info_saved"]))
		{
			$this->arrInfo = $_SESSION["info_saved"];
			unset ($_SESSION["info_saved"]);	// don't want the errors to keep appearing...
		}
		if (!empty($_SESSION["errors_saved"]))
		{
			$this->arrErrors = $_SESSION["errors_saved"];
			unset ($_SESSION["errors_saved"]);	// don't want the errors to keep appearing...
		}


	}

	function Error($message, $severity=0, $file="", $line=0)
	{
		if ($severity==0)
			$severity = ERROR_SEVERITY_LOW;

		$this->arrErrors[]=array(ERROR_ARRAY_MESSAGE => $message,
					ERROR_ARRAY_SEVERITY => $severity,
					ERROR_ARRAY_FILE => $file,
					ERROR_ARRAY_LINE => $line);

		if ($severity==ERROR_SEVERITY_STOP)
			$this->DoStopError();
	}
	//reusing the error function - much of this is not needed
	function Info($message)
	{
		// if ($severity==0)
		// 	$severity = ERROR_SEVERITY_LOW;
		//print_r("hellu");
		$this->arrInfo[]=$message;

		// if ($severity==ERROR_SEVERITY_STOP)
		// 	$this->DoStopError();
	}

	function SaveMessages()
	{	// we're about to redirect, but want to remember the errors, so put them in session temporarily.

		$_SESSION["errors_saved"] = $this->arrErrors;
		$_SESSION["info_saved"] = $this->arrInfo;
	}

	function DoStopError()
	{

		$box = $this->ErrorBox();

		die ($box);
	}
/*
	function ErrorBox()
	{
		$msg="";

		foreach($this->arrErrors as $oneErr)
		{
			$msg.=$this->ErrorBoxError($oneErr) . "<br />";
		}

		$output = "<div class='ErrorBoxMsg'>Errors occured on this page:<br />";
		$output .= $msg."</div>";

		return $output;
	}

	function ErrorBoxError($oneErr)
	{
		//if ($oneErr[ERROR_ARRAY_SEVERITY]==ERROR_SEVERITY_INFO && !DEBUG)
		//	return "";

		$output="<DIV class=ErrorBoxLine>".$this->SeverityNote($oneErr[ERROR_ARRAY_SEVERITY]).$oneErr[ERROR_ARRAY_MESSAGE];

		if  (DEBUG && $oneErr[ERROR_ARRAY_FILE] != "")
		{
			$output.="<DIV class=FileLine> ".$oneErr[ERROR_ARRAY_FILE];
			if ($oneErr[ERROR_ARRAY_LINE] != 0)
				$output.=" (".$oneErr[ERROR_ARRAY_LINE].")";
			$output.="</DIV>";
		}

		$output .= "</DIV>";

		return $output;
	}
*/

	function SeverityNote($sev)
	{
		switch($sev)
		{
			case ERROR_SEVERITY_INFO:
//				return "(INFO) ";
				return "";
				break;
			case ERROR_SEVERITY_LOW:
				return "(LOW) ";
				break;
			case ERROR_SEVERITY_MED:
				return "(MED) ";
				break;
			case ERROR_SEVERITY_HIGH:
				return "(HIGH) ";
				break;
			case ERROR_SEVERITY_STOP:
				return "(STOP) ";
				break;
			default:
				return "";
				break;
		}

	}


	function ReturnValue($message, $obj="")
	{
		$this->retval = $message;
		$this->retobj = $obj;
	}
}


$cStatusMessage = new cStatusMessage;
?>
