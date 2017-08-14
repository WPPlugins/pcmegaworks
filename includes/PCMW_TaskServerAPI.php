<?php
/**************************************************************************
* @CLASS PCMW_TaskServerAPI
* @brief USE THIS TO CREATE NEW CLASSES FOR THE INCLUDES DIRECTORY.
* @REQUIRES:
*  -PCMW_Database.php
*
**************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
class PCMW_TaskServerAPI{
   var $intSitetId = 0;
   var $objSiteRoot = null;
   var $arrPayLoadPailOut = array();
   var $arrPayLoadPailIn = array();
   var $arrCurrentUser = array();
   var $strCurlAddress = '';
   //debugging
   var $boolDebugOn = TRUE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_TaskServerAPI();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
    $this->strCurlAddress = PCMW_HELPDESKURL;
  }

  #REGION UTILITY METHODS
  //All general use functions specific to this class go here

  /*
  @brief Get the methods available for debugging
  *keep this at the top of the class for easy access and update
  @return array()
  */
  function GetDebugMethods(){
    $arrDebugMethods = array();
    $arrDebugMethods['PCMW_TaskServerAPI::PackageRequestContents'] = 1;
    //$arrDebugMethods['PCMW_TaskServerAPI::MakeServerRequest'] = 1;
    //$arrDebugMethods['PCMW_TaskServerAPI::UnPackPayLoad'] = 1;
    //$arrDebugMethods['PCMW_TaskServerAPI::ValidateCommunication'] = 1;
    //$arrDebugMethods['PCMW_TaskServerAPI::CheckHandShake'] = 1;
    $arrDebugMethods['PCMW_TaskServerAPI::MakeServerRequest'] = 1;
    //$arrDebugMethods['PCMW_TaskServerAPI::JSONEncode'] = 1;
    $arrDebugMethods['PCMW_TaskServerAPI::FirePayload'] = 1;
    return $arrDebugMethods;
  }


  /**
  * capture incoming data and debug/sanitize
  * @param $arrPOST incoming payload data
  * @return bool
  */
  function SanitizePayload(){
   PCMW_Utility::Get()->CleanTableData($this->arrPayLoadPailIn);
   foreach($this->arrPayLoadPailIn as $varKey=>$varValue)
    $this->arrPayLoadPailIn[$varKey] = htmlspecialchars($varValue);
   return TRUE;
  }


  /**
  * return the form errors
  * @param $strPayLoadContainer
  * @return string
  */
  function PackageErrors($strPayLoadContainer){
    return $this->JSONEncode(array($strPayLoadContainer=>PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE)));
  }


  /**
  * encode a PHP array into json
  * @aram array $arrValues values to be encoded
  * @return string
  */
  public static function JSONEncode($arrValues,$objEncodeType= JSON_FORCE_OBJECT){//JSON_FORCE_OBJECT
     //try to encode it now
     if($strJsonData = json_encode($arrValues,$objEncodeType))
        return $strJsonData;
     switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $strError = ' - No errors';
        break;
        case JSON_ERROR_DEPTH:
            $strError = ' - Maximum stack depth exceeded';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            $strError = ' - Underflow or the modes mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            $strError = ' - Unexpected control character found';
        break;
        case JSON_ERROR_SYNTAX:
            $strError = ' - Syntax error, malformed JSON';
        break;
        case JSON_ERROR_UTF8:
            $strError =' - Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
        default:
            $strError = ' - Unknown error';
        break;
    }
     PCMW_Logger::Debug('JSONENCOE failed ['.$strError.'] LINE ['.__LINE__.'] ',1);
  }

  /**
  * decode a json array string into a php array
  * @param string $strValues values to be decoded
  * @param bool $boolAssociatve return an associative array or numerically index array
  * @return array
  */
  public static function JSONDecode($strValues, $boolAssociatve=TRUE){
     return json_decode($strValues, $boolAssociatve);
  }

  /**
  * make a handshake for data request. Outgoing may or may not have come by request
  * @param $intClientId unique client ID
  * @return string
  */
  function MakeServerHandShake(){
    return PCMW_USERKEY;
  }


  /**
  * check session for a handshake token to make sure this is our communication
  * @return bool
  */
  function CheckHandShake(){
      $strLocalHandShake = $this->MakeServerHandShake();
      if($this->arrPayLoadPailIn['handshake'] == $strLocalHandShake){
        return TRUE;
      }
    //not ours
    return FALSE;
  }

  /**
  * unpack the payload and ID's
  * @param $arrPOST
  * @return array(payload)
  */
  function UnPackPayLoad($arrPOST,$boolAsArray=TRUE){
    $this->arrPayLoadPailIn = $this->JSONDecode(stripslashes($arrPOST['payload']),$boolAsArray);//payload is used to kick off form
    //sanitize our payload
    return $this->SanitizePayload();
  }

  #ENDREGION

  #REGION DATACALCULATION
  /**
  * Load and check our communication requests
  * @param $arrPOST
  * @return array (payload)
  */
  function ValidateCommunication(){
    //check for user data
    if(!$this->CheckHandShake()){
      $strError = 'We\'re sorry, something has gone wrong. Please log in and try again.';
      return FALSE;
    }
    return TRUE;
  }


  /**
  * given a transaction ID, load the station version, then ad version and job
  * to evaluate if we are ready to send the delivered status confirmation
  * @param $intTransactionId unique transaction ID from FTP SERVER
  * @return bool
  */
  function ProcessFTPResponse($arrPayLoad){
  return TRUE;
    $objFTPInstance = new FTPInstance();
    $objFTPInstance->LoadObjectWithArrayObject($arrPayLoad['ftpinstance']);
    //prevent duplicate email alerts bug, till we can find/fix
    $intPreviousStatus = $objStationVersion->intStatus;
    $objStationVersion->intStatus = $objFTPInstance->intInstanceStatus;
    return TRUE;
  }

  /**
  * given a payload send it to our server
  * @param $strPayload
  * @return string results
  */
  function FirePayload($strPayload,$strAPIURL=''){
   if($strAPIURL == '')
    $strAPIURL = $this->strCurlAddress;
   if($strAPIURL == '')
    $strAPIURL = PCMW_HOSTADDRESS;
   //send the curl string
    $objCurl  = curl_init($strAPIURL);
    curl_setopt($objCurl, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($objCurl, CURLOPT_POST, TRUE);
    curl_setopt($objCurl, CURLOPT_POSTFIELDS, $strPayload);
    curl_setopt($objCurl, CURLOPT_URL,$strAPIURL);
    curl_setopt($objCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($objCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Content-Length: " . strlen($strPayload)));
    curl_setopt($objCurl, CURLOPT_HEADER , 0);
    curl_setopt($objCurl, CURLOPT_VERBOSE, TRUE);
    curl_setopt($objCurl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($objCurl, CURLOPT_CONNECTTIMEOUT ,10);
    curl_setopt($objCurl, CURLOPT_TIMEOUT, 180);

    //send
    $boolResult = curl_exec($objCurl);
    $arrDebugInfo = curl_getinfo($objCurl);
    //debug? anyone?
    if((!$boolResult || ((int)curl_errno($objCurl) > 0 && $this->boolDebugOn > 0)) && $arrDebugInfo){ //curl_errno($objCurl) &&
       $strCurlResults = var_export($arrDebugInfo,TRUE);
        $strCurlResults .= 'curl_errno['. curl_errno($objCurl) . ']'."\r\n";
        $strCurlResults .=  'curl_error[' .curl_error($objCurl).']'."\r\n";
        $strCurlResults .=  ' Curl String[' .$strPayload.']'."\r\n";
        $strCurlResults .=  ' RAW result[' .$boolResult.']'."\r\n";
        if(is_numeric($boolResult) || is_bool($boolResult))
            $strCurlResults .=  ' bool result[' .(int)$boolResult.']'."\r\n";
        $strCurlResults .=  ' $strAPIURL [' .$strAPIURL.']'."\r\n";
        $strCurlResults .=  ' sizeof($arrDebugInfo) [' .sizeof($arrDebugInfo).']'."\r\n";
        PCMW_Logger::Debug($strCurlResults.' Means Fail ['.$this->strCurlAddress.'] . LINE ['.__LINE__.'] METHOD ['.__METHOD__.']',1);
    }
    //close the connection
	curl_close($objCurl);
    //return the status
    return $boolResult;
  }

  /**
  * package the local contents for delivery
  * @param $strDataRequestType type of data we are requesting or sending
  * @param $arrPayLoad added data to pass with this request
  * @return string
  */
  function PackageRequestContents($strDataRequestType,$arrPayLoad=array(),$arrContents = array(),$boolEncodeTags=FALSE){
     //pack the payload
     $this->arrPayLoadPailOut = PCMW_Utility::Get()->MergeArrays($this->arrPayLoadPailOut,$arrPayLoad);
     $this->arrPayLoadPailOut['purpose'] = $strDataRequestType;
     //make the handshake
     $this->arrPayLoadPailOut['handshake'] = $this->MakeServerHandShake();
     $arrContents['payload'] = $this->JSONEncode($this->arrPayLoadPailOut);
     if($boolEncodeTags)
        $arrContents['payload'] = urlencode($arrContents['payload']);
     return PCMW_Utility::Get()->MakeCurlString($arrContents,array(),TRUE);
  }


  /**
  * given an array of data send the Client server a request
  * @param $strDataRequestType
  * @param $arrPayLoad
  * @return array
  */
  function MakeServerRequest($strDataRequestType,$arrPayLoad=array(),$boolAsArray=TRUE,$boolEncodeTags=TRUE){
    $strPayloadString = $this->PackageRequestContents($strDataRequestType,$arrPayLoad,array(),$boolEncodeTags);
    $strRequestData = $this->FirePayload($strPayloadString,$this->strCurlAddress);
    $arrReturnParts = PCMW_Utility::Get()->DecomposeCurlString($strRequestData);
    if($this->UnPackPayLoad($arrReturnParts,$boolAsArray))
        return $this->arrPayLoadPailIn;
    return FALSE;
  }


  #ENDREGION



  #REGION DEBUGGINGMETHODS

  /*
  @brief store and display debug information
  @param $strMessage,$boolLogNow
  @param boolean
  */
  function PCMW_LoadDebugLog($strMessage='',$boolLogNow=FALSE,$strMethod='',$boolShowBackTrace=FALSE){
  //$this->PCMW_LoadDebugLog('VALUE ['.$arrPOST .']-  LINE '.__LINE__."\r\n",FALSE,__METHOD__);
    //PCMW_Abstraction::Get()->AddUserMSG( 'message METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
    if($this->boolDebugOn){
      if($strMessage != "")
      //add the message
        $this->strDebugMessage .= $strMessage;
      if($boolLogNow){
        if($strMethod == "" || ($strMethod != "" && array_key_exists($strMethod,$this->GetDebugMethods()))){
          if((int)$this->boolDebugOn == 2)
            PCMW_Abstraction::Get()->AddUserMSG( '$this->strDebugMessage ['.$this->strDebugMessage.'] METHOD ['.$strMethod.'] LINE ['.__LINE__.']',9);
          else
            PCMW_Logger::Debug($this->strDebugMessage.' METHOD ['.$strMethod.']  this method ['.__METHOD__.'] LINE ['.__LINE__.']',$this->intDebugLevel,$boolShowBackTrace);
        }
        //clean up
        $this->strDebugMessage = '';
      }
    }
  }

  #ENDREGION

}//end class  
?>