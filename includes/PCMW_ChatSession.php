<?php
/**************************************************
* Class :PCMW_ChatSession
* @brief Well-formed object declaration of the 'PC_chatsession' database table.
*
***************************************************/
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_BaseClass.php');
/**
* @class PCMW_ChatSession
*/
class PCMW_ChatSession  extends PCMW_BaseClass
{
    public $intChatSessionId;// int(11) NOT NULL AUTO_INCREMENT
    public $intUserId;// int(9) DEFAULT NOT NULL
    public $intOwnerId;// int(9) DEFAULT NOT NULL
    public $intPreviousOwnerId;// int(9) DEFAULT NOT NULL
    public $strChatType;// varchar(15) NOT NULL "group","single","access"
    public $intChatAccess;// int ( 4 ) DEFAULT PCMW_PREMIUMUSER
    public $intStatus;// tinyint(1) NOT NULL
    public $intUpdateAlert;// tinyint(1) NOT NULL
    public $strChatMeta;// text NULL
    public $intStartDate;// int(12) NOT NULL
    public $intEndDate;// int(12) NULL
    public $intLastUpdate;// int(12) NOT NULL
    //hold the messages
    public $arrChatMessages;
    //hold the user name
    public $strUserName;
    //hold the owner name
    public $strOwnerName;
    //hold the previous owner name
    public $strPreviousOwnerName;
    //hold the owner online status
    public $boolOwnerOnline;
    //hold the previous owner online status
    public $boolPreviousOwnerOnline;
    //hold the chat meta data
    public $arrChatMeta;
    //hold validation errors
    public $arrValidationErrors;

  function __construct(){
  //construct
  }

  public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_ChatSession();
		return( $inst );
   }

  function Validate($action = "select", $ignore_arr = null)
  {
    $err_arr = NULL;
    $var_arr = Array();

    $var_arr['sessionid']['type'] = 'key';
    $var_arr['userid']['type'] = 'key';
    $var_arr['onwerid']['type'] = 'key';
    $var_arr['previousowner']['type'] = 'key';
    $var_arr['chattype'] =   Array('type' => 'string', 'max' => 15);
    $var_arr['chataccess']['type'] = 'int';
    $var_arr['chatstatus']['type'] = 'int';
    $var_arr['updatealert']['type'] = 'int';
    $var_arr['chatmeta'] =   Array('type' => 'string', 'max' => 4096);
    $var_arr['startdate']['type'] = 'int';
    $var_arr['enddate']['type'] = 'int';
    $var_arr['lastupdate']['type'] = 'int';

    switch($action)
    {
      case 'update':
      case 'select':
        $err_arr = $this->checkTypes($var_arr, $ignore_arr);
        break;
      case 'insert':
        // id will never be checked on insertion
        $ignore_arr['sessionid'] = true;
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
    $this->intChatSessionId = (int) $arrArray['sessionid'];
    $this->intUserId = (int) $arrArray['userid'];
    $this->intOwnerId = (int) $arrArray['ownerid'];
    $this->intPreviousOwnerId = (int) $arrArray['previousowner'];
    $this->strChatType = (string) $arrArray['chattype'];
    $this->intChatAccess = (int) $arrArray['chataccess'];
    $this->intStatus = (int) $arrArray['chatstatus'];
    $this->intUpdateAlert = (int) $arrArray['updatealert'];
    $this->strChatMeta = (string) $arrArray['chatmeta'];
    $this->intStartDate = (int) $arrArray['startdate'];
    $this->intEndDate = (int) $arrArray['enddate'];
    $this->intLastUpdate = (int) $arrArray['lastupdate'];
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
      if(!empty($arrObjectData['ownerid']))
        $this->intOwnerId = (int) $arrObjectData['ownerid'];
      if(!empty($arrObjectData['previousowner']))
        $this->intPreviousOwnerId = (int) $arrObjectData['previousowner'];
      if(!empty($arrObjectData['chattype']))
        $this->strChatType = (string) $arrObjectData['chattype'];
      if(!empty($arrObjectData['chataccess']))
        $this->intChatAccess = (int) $arrObjectData['chataccess'];
      if(!empty($arrObjectData['chatstatus']))
        $this->intStatus = (int) $arrObjectData['chatstatus'];
      if(!empty($arrObjectData['updatealert']))
        $this->intUpdateAlert = (int) $arrObjectData['updatealert'];
      if(!empty($arrObjectData['chatmeta']))
        $this->strChatMeta = (string) $arrObjectData['chatmeta'];
      if(!empty($arrObjectData['enddate']))
        $this->intEndDate = (int) $arrObjectData['enddate'];
      if(!empty($arrObjectData['lastupdate']))
        $this->intLastUpdate = (int) $arrObjectData['lastupdate'];
    }

    /*
    @brief load an array with the PCMW_ChatSession object
    @param $objPCMW_ChatSession
    @return array(PCMW_ChatSession)
    */
    public function LoadArrayWithObject(){
     $arrArray = array();
     (int) $arrArray['sessionid'] = $this->intChatSessionId;
     (int) $arrArray['userid'] = $this->intUserId;
     (int) $arrArray['ownerid'] = $this->intOwnerId;
     (int) $arrArray['previousowner'] = $this->intPreviousOwnerId;
     (string) $arrArray['chattype'] = $this->strChatType;
     (int) $arrArray['chataccess'] = $this->intChatAccess;
     (int) $arrArray['chatstatus'] = $this->intStatus;
     (int) $arrArray['updatealert'] = $this->intUpdateAlert;
     (string) $arrArray['chatmeta'] = $this->strChatMeta;
     (int) $arrArray['startdate'] = $this->intStartDate;
     (int) $arrArray['enddate'] = $this->intEndDate;
     (int) $arrArray['lastupdate'] = $this->intLastUpdate;
     return $arrArray;
    }

}//end class PCMW_ChatSession
?>