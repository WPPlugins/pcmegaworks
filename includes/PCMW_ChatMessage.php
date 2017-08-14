<?php
/**************************************************
* Class :PCMW_ChatMessage
* @brief Well-formed object declaration of the 'PC_chatmessage' database table.
*
***************************************************/
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_BaseClass.php'); 
/**
 * @class PCMW_ChatMessage
 */
class PCMW_ChatMessage  extends PCMW_BaseClass
{
    public $intMessageId;// int(11) NOT NULL AUTO_INCREMENT
    public $strMessage;// text NOT NULL
    public $intUserId;// int(9) DEFAULT NOT NULL
    public $strAttachments;// text NULL
    public $intChatSessionId;// int(11) NOT NULL
    public $intEDate;// int(12) NOT NULL
    //hold the users name
    public $strUserName;
    //hold the online status of a user
    public $boolUserOnline;
    //hold validation errors
    public $arrValidationErrors;

  function __construct(){
  //construct
  }


  public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_ChatMessage();
		return( $inst );
   }

  function Validate($action = "select", $ignore_arr = null)
  {
    $err_arr = NULL;
    $var_arr = Array();

    $var_arr['messageid']['type'] =   'key';
    $var_arr['message'] =           Array('type' => 'string', 'max' => 4096);
    $var_arr['userid']['type'] =    'key';
    $var_arr['attachments'] =       Array('type' => 'string', 'max' => 4096);
    $var_arr['sessionid']['type'] = 'key';
    $var_arr['edate']['type'] =     'int';

    switch($action)
    {
      case 'update':
      case 'select':
        $err_arr = $this->checkTypes($var_arr, $ignore_arr);
        break;
      case 'insert':
        // id will never be checked on insertion
        $ignore_arr['messageid'] = true;
        $err_arr = $this->checkTypes($var_arr, $ignore_arr);
        break;
      case 'empty':
      default:
        break;
    }
    if(!is_array($err_arr))
    {
      return TRUE;
    }
    else
    {
      $this->arrValidationErrors = $err_arr;
      $strErrors = __CLASS__.' class instance failed validation due to following errors: ';
      foreach($err_arr as $strType=>$strError)
        $strErrors .= $strType.' - '.$strError;
      $_SESSION['errout'][] = $strErrors;
      PCMW_Logger::Debug($err_arr, PCMW_DEBUG_ARG);
      return FALSE;
    }
  }


  public function LoadObjectWithArray($arrArray){
    $this->intMessageId = (int) $arrArray['messageid'];
    $this->strMessage = (string) $arrArray['message'];
    $this->intUserId = (int) $arrArray['userid'];
    $this->strAttachments = (string) $arrArray['attachments'];
    $this->intChatSessionId = (int) $arrArray['sessionid'];
    $this->intEDate = (int) $arrArray['edate'];
     return $this;

    }

    /**
    * given an array sent in JSON format, rehydrate the object
    * @param $arrObject
    * @return $this
    */
    public function LoadObjectWithArrayObject($arrArray){
      foreach($arrArray as $varKey=>$varValue){
        if(property_exists($this,$varKey))
            $this->{$varKey} = $varValue;
      }
      return $this;
    }

    /**
    * update an object with an object
    * @param $objUpdatingObject
    * @return bool
    */
    function UpdateObjectWithObject($objUpdatingObject){
     $arrObjectVars = get_object_vars($objUpdatingObject);
     foreach($arrObjectVars as $strName => $varValue)
        $this->$strName = $varValue;
     return TRUE;
    }

    /**
    * given an array of table data, check for updates
    * @param $arrObjectData
    * return bool
    */
    function UpdateObjectWithArray($arrObjectData){
      if(!empty($arrObjectData['message']))
        $this->strMessage = (string) $arrObjectData['message'];
      if(!empty($arrObjectData['attachments']))
        $this->strAttachments = (string) $arrObjectData['attachments'];
      if(!empty($arrObjectData['sessionid']))
        $this->intChatSessionId = (int) $arrObjectData['sessionid'];
      if(!empty($arrObjectData['edate']))
        $this->intEDate = (int) $arrObjectData['edate'];
    }

    /*
    @brief load an array with the PCMW_ChatMessage object
    @param $objPCMW_ChatMessage
    @return array(PCMW_ChatMessage)
    */
    public function LoadArrayWithObject(){
     $arrArray = array();
     (int) $arrArray['messageid'] = $this->intMessageId;
     (string) $arrArray['message'] = $this->strMessage;
     (int) $arrArray['userid'] = $this->intUserId;
     (string) $arrArray['attachments'] = $this->strAttachments;
     (int) $arrArray['sessionid'] = $this->intChatSessionId;
     (int) $arrArray['edate'] = $this->intEDate;
     return $arrArray;
    }

}//end class PCMW_ChatMessage
?>