<?php
/**************************************************
* Class :PCMW_AdminUser
* @brief Well-formed object declaration of the 'PCMW_AdminUser' database table.
*
***************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_BaseClass.php');
class PCMW_AdminUser  extends PCMW_BaseClass
{
    public $intAdminUserId;// int(11) NOT NULL AUTO_INCREMENT
    public $intUserId;// int(8) NOT NULL
    public $intHandlerId;// int(9) NOT NULL,
    public $intCustomerId;// int(9) NOT NULL,
    public $intAdminGroupId;// int(9) NOT NULL
    public $intStatus;// int(3) NOT NULL COMMENT
    //hold validation errors
    public $arrValidationErrors;

  function __construct(){
  //construct
  }


  public static function Get(){
		//==== instantiate or retrieve singleton====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_AdminUser();
		return( $inst );
   }

  function Validate($action = "select", $ignore_arr = null)
  {
    $err_arr = NULL;
    $var_arr = Array();

    $var_arr['adminid']['type'] =      'key';
    $var_arr['userid']['type'] =       'key';
    $var_arr['handlerid']['type'] =    'key';
    $var_arr['customerid']['type'] =   'key';
    $var_arr['admingroup']['type'] =   'key';
    $var_arr['status']['type'] =       'int';

    switch($action)
    {
      case 'update':
      case 'select':
        $err_arr = $this->checkTypes($var_arr, $ignore_arr);
        break;
      case 'insert':
        // id will never be checked on insertion
        $ignore_arr['adminid'] = true;
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
      PCMW_Logger::Debug(__CLASS__." class instance failed validation due to following errors: ", 1);
      return FALSE;
    }
  }


  public function LoadObjectWithArray($arrArray){
     $this->intAdminUserId= (int) $arrArray['adminid'];
     $this->intUserId = (int) $arrArray['userid'];
     $this->intHandlerId = (int) $arrArray['handlerid'];
     $this->intCustomerId = (int) $arrArray['customerid'];
     $this->intAdminGroupId = (int) $arrArray['admingroup'];
     $this->intStatus = (int) $arrArray['status'];
     return $this;
  }

    /**
    * given an array of table data, check for updates
    * @param $arrObjectData
    * return bool
    */
    function UpdateObjectWithArray($arrObjectData){
      if(!empty($arrObjectData['adminid']))
         $this->intAdminUserId = (int) $arrObjectData['adminid'];
      if(!empty($arrObjectData['status']))
         $this->intStatus = (int) $arrObjectData['status'];
    }


    /*
    @brief load an array with the PCMW_AdminUser object
    @param $objAdminUser
    @return array(PCMW_AdminUser)
    */
    public function LoadArrayWithObject($objAdminUser=null){
     $arrArray = array();
     (int) $arrArray['adminid'] = $this->intAdminUserId;
     (int) $arrArray['userid'] = $this->intUserId ;
     (int) $arrArray['handlerid'] = $this->intHandlerId;
     (int) $arrArray['customerid'] = $this->intCustomerId ;
     (int) $arrArray['admingroup'] = $this->intAdminGroupId ;
     (int) $arrArray['status'] = $this->intStatus ;
     return $arrArray;
    }

}//end class PCMW_AdminUser

class PCMW_AdminGroup  extends PCMW_BaseClass
{
    public $intAdminGroupId;// int(11) NOT NULL AUTO_INCREMENT
    public $strGroupName;
    public $intGroupStatus;
    public $intClientId;
    //hold validation errors
    public $arrValidationErrors;

  function __construct(){
  //construct
  }


  public static function Get(){
		//==== instantiate or retrieve singleton====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_AdminGroup();
		return( $inst );
   }

  function Validate($action = "select", $ignore_arr = null)
  {
    $err_arr = NULL;
    $var_arr = Array();

    $var_arr['admingroupid']['type'] =      'key';
    $var_arr['groupname'] =                 Array('type' => 'string','min'=>5, 'max' => 35);
    $var_arr['groupstatus']['type'] =       'int';
    $var_arr['clientid']['type'] =          'key';

    switch($action)
    {
      case 'update':
      case 'select':
        $err_arr = $this->checkTypes($var_arr, $ignore_arr);
        break;
      case 'insert':
        // id will never be checked on insertion
        $ignore_arr['admingroupid'] = true;
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
      PCMW_Logger::Debug(__CLASS__." class instance failed validation due to following errors: ", 1);
      return FALSE;
    }
  }


  public function LoadObjectWithArray($arrArray){
     $this->intAdminGroupId= (int) $arrArray['admingroupid'];
     $this->strGroupName = (string) $arrArray['groupname'];
     $this->intGroupStatus = (int) $arrArray['groupstatus'];
     $this->intClientId = (int) $arrArray['clientid'];
     return $this;
  }

    /**
    * given an array of table data, check for updates
    * @param $arrObjectData
    * return bool
    */
    function UpdateObjectWithArray($arrObjectData){
      if(!empty($arrObjectData['admingroupid']))
         $this->intAdminGroupId = (int) $arrObjectData['admingroupid'];
      if(!empty($arrObjectData['groupstatus']))
         $this->intGroupStatus = (int) $arrObjectData['groupstatus'];
    }


    /*
    @brief load an array with the PCMW_AdminGroup object
    @param $objAdminGroup
    @return array(PCMW_AdminGroup)
    */
    public function LoadArrayWithObject($objAdminGroup=null){
     $arrArray = array();
     (int) $arrArray['admingroupid'] = $this->intAdminGroupId;
     (string) $arrArray['groupname'] = $this->strGroupName;
     (int) $arrArray['groupstatus'] = $this->intGroupStatus;
     (int) $arrArray['clientid'] = $this->intClientId;
     return $arrArray;
    }

}//end class PCMW_AdminGroup
?>