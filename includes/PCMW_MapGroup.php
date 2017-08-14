<?php
/*********************************************************
* class PCMW_MapGroup
* @brief hold the PCMW_MapGroup object for use with exterior pieces
* @params
*   -intMapGroupId(int)
*   -strGroupName(tsring)
*   -strGroupSettings(tsring)
*   -intEDate (int)
*requires
    -/PCMW_BaseClass.php
***********************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once('PCMW_BaseClass.php');
class PCMW_MapGroup extends PCMW_BaseClass{

  var $intMapGroupId;
  var $strGroupName = '';
  var $strMapGroupSettings = '';
  var $intEDate = 0;
  //hold validation errors
  public $arrValidationErrors;

  var $boolDebugOn = FALSE;
  public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new MapGroup();
		return( $inst );
  }


  function __construct(){

  }
   function Validate($action = "select", $ignore_arr = null)
  {
    $err_arr = NULL;
    $var_arr = Array();

    $var_arr['groupid']['type'] = 'key';
    $var_arr['groupname'] =       Array('type' => 'string', 'min' => 1, 'max' => 75);
    $var_arr['groupsettings'] =   Array('type' => 'string', 'max' => 4096);
    $var_arr['edate'] =           'int';
    switch($action)
    {
      case 'update':
      case 'select':
        $err_arr = $this->checkTypes($var_arr, $ignore_arr);
        break;
      case 'insert':
        // id will never be checked on insertion
        $ignore_arr['groupid'] = true;
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
      /*PCMW_Logger::Debug(__CLASS__." class instance failed validation due to following errors: ", DEBUG_LEVEL);
      $strErrors = '';
      foreach($err_arr as $strType=>$strError)
        $strErrors .= $strType.' - '.$strError;
      $_SESSION['errout'][] = $strErrors;       */
      return FALSE;
    }
  }


  public function LoadObjectWithArray($arrArray){
      //we need to make this fit for the locaor logic
       $this->intMapGroupId = (int)$arrArray['groupid'];
       $this->strGroupName = (string) stripslashes($arrArray['groupname']);
       $this->strMapGroupSettings = (string)stripslashes($arrArray['groupsettings']);
       $this->intEDate = @(int)stripslashes($arrArray['edate']);
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
      if(!empty($arrObjectData['groupname']))
        $this->strGroupName = (string) $arrObjectData['groupname'];
      if(!empty($arrObjectData['groupsettings']))
        $this->strMapGroupSettings = (string) $arrObjectData['groupsettings'];
      if(!empty($arrObjectData['edate']))
        $this->intEDate = (int) $arrObjectData['edate'];
      return TRUE;
    }

    /*
    @brief load an array with the TABLEOBJECT object
    @param $objTABLEOBJECT
    @return array(TABLEOBJECT)
    */
    public function LoadArrayWithObject($objTABLEOBJECT=null){
     $arrArray = array();
       (int)$arrArray['groupid'] = $this->intMapGroupId;
       (string)$arrArray['groupname'] = $this->strGroupName;
       (string)$arrArray['groupsettings'] = $this->strMapGroupSettings;
       (int)$arrArray['edate'] = $this->intEDate;
     return $arrArray;
    }
}//end class 
?>