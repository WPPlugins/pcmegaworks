<?php
/**************************************************
* Class :PCMW_ChatOptions
* @brief Well-formed object declaration of the 'PCMW_ChatOptions' database table.
*
***************************************************/
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_BaseClass.php');   
/**
 * @class PCMW_ChatOptions
 */
class PCMW_ChatOptions  extends PCMW_BaseClass
{
    public $intAllPages;//use the chat on all pages
    public $strtDefaultOwnerGroup;//which group ( and above ) can field chats
    public $intChatAccessGroup;//lowest access level that can customers can be to chat
    public $strChatType;//group - users with certain capabilities, single - one on one only, access - by admin group
    public $intStaffCanClose;//Can the appointed staff group close chats?
    public $intCustomerAttachments;// can the customer add attachments
    public $intOwnerAttachments;//can the owner share attachments
    public $intLastUpdate;//last date the settings were updated
    public $intLastUpdatedBy;// last user to update the settings
    //hold validation errors
    public $arrValidationErrors;

  function __construct(){
  //construct
  }


  public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_ChatOptions();
		return( $inst );
   }

  function Validate($action = "select", $ignore_arr = null)
  {
    $err_arr = NULL;
    $var_arr = Array();

    $var_arr['allpages']['type'] =       'int';
    $var_arr['ownergroup'] =             Array('type' => 'string', 'max' => 15);
    $var_arr['accessgroup']['type'] =    'int';
    $var_arr['chattype'] =               Array('type' => 'string', 'max' => 15);
    $var_arr['staffclose']['type'] =     'int';
    $var_arr['customerattach']['type'] = 'int';
    $var_arr['staffattach']['type'] =    'int';
    $var_arr['lastupdate']['type'] =     'int';
    $var_arr['updateby']['type'] =       'int';

    switch($action)
    {
      case 'update':
      case 'select':
        $err_arr = $this->checkTypes($var_arr, $ignore_arr);
        break;
      case 'insert':
        // id will never be checked on insertion
        $ignore_arr['id'] = true;
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
    $this->intAllPages = (int) $arrArray['allpages'];
    $this->strDefaultOwnerGroup = (string) $arrArray['ownergroup'];
    $this->intChatAccessGroup = (int) $arrArray['accessgroup'];
    $this->strChatType = (string) $arrArray['chattype'];
    $this->intStaffCanClose = (int) $arrArray['staffclose'];
    $this->intCustomerAttachments = (int) $arrArray['customerattach'];
    $this->intOwnerAttachments = (int) $arrArray['staffattach'];
    $this->intLastUpdate = (int) @$arrArray['lastupdate'];
    $this->intLastUpdatedBy = (int) @$arrArray['updateby'];
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
      if(!empty($arrObjectData['allpages']))
        $this->intAllPages = (int) $arrObjectData['allpages'];
      if(!empty($arrObjectData['ownergroup']))
        $this->strDefaultOwnerGroup = (string) $arrObjectData['ownergroup'];
      if(!empty($arrObjectData['accessgroup']))
        $this->intChatAccessGroup = (int) $arrObjectData['accessgroup'];
      if(!empty($arrObjectData['chattype']))
        $this->strChatType = (string) $arrObjectData['chattype'];
      if(!empty($arrObjectData['staffclose']))
        $this->intStaffCanClose = (int) $arrObjectData['staffclose'];
      if(!empty($arrObjectData['customerattach']))
        $this->intCustomerAttachments = (int) $arrObjectData['customerattach'];
      if(!empty($arrObjectData['staffattach']))
        $this->intOwnerAttachments = (int) $arrObjectData['staffattach'];
      if(!empty($arrObjectData['lastupdate']))
        $this->intLastUpdate = (int) $arrObjectData['lastupdate'];
      if(!empty($arrObjectData['updateby']))
        $this->intLastUpdatedBy = (int) $arrObjectData['updateby'];
    }

    /*
    @brief load an array with the PCMW_ChatOptions object
    @return array(PCMW_ChatOptions)
    */
    public function LoadArrayWithObject(){
     $arrArray = array();
     (int) $arrArray['allpages'] = $this->intAllPages;
     (string) $arrArray['ownergroup'] = $this->strDefaultOwnerGroup;
     (int) $arrArray['accessgroup'] = $this->intChatAccessGroup;
     (string) $arrArray['chattype'] = $this->strChatType;
     (int) $arrArray['staffclose'] = $this->intStaffCanClose;
     (int) $arrArray['customerattach'] = $this->intCustomerAttachments;
     (int) $arrArray['staffattach'] = $this->intOwnerAttachments;
     (int) $arrArray['lastupdate'] = $this->intLastUpdate;
     (int) $arrArray['updateby'] = $this->intLastUpdatedBy;
     return $arrArray;
    }

}//end class PCMW_ChatOptions
?>