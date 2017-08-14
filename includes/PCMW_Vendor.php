<?php
/*********************************************************
* class PCMW_Vendor
* @brief hold the vendor object for use with exterior pieces
* @params
*   -intInvoiceNumber(int)
*   -strVendorName(tsring)
*   -strVendorAddress
*   -intVendorZip
*   -strVendorState
*   -strVendorCity
*   -strVendorWebsite
*   -floatVendorLatitude
*   -floatVendorLongitude
*   -strVendorPhone
*   -intVendorUserId
*   -intVendorStatus
*   -strVendorIcon
*   -intVendorRecordId
*   -strVendorDescription
*   -strVendorProgramAges
*   -strVendorProgramTheme

*requires
    -/PCMW_BaseClass.php
***********************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once('PCMW_BaseClass.php');
class PCMW_Vendor extends PCMW_BaseClass{

  var $intVendorId;
  var $intWPUserId = 0;
  var $intInvoiceNumber = 0;
  var $strVendorName = '';
  var $strVendorAddress = '';
  var $intVendorZip = 0;
  var $strVendorState = '';
  var $strVendorCity = '';
  var $strVendorLocation = '';
  var $strVendorWebsite = '';
  var $floatVendorLatitude = 0.0;
  var $floatVendorLongitude = 0.0;
  var $strVendorPhone = '';
  var $intVendorStatus = 0;
  var $strVendorIcon = 0;
  var $intVendorRecordId = 0;
  var $strVendorDescription = '';
  //hold validation errors
  public $arrValidationErrors;

  var $boolDebugOn = FALSE;
  public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_Vendor();
		return( $inst );
  }


  function __construct(){

  }
   function Validate($action = "select", $ignore_arr = null)
  {
    $err_arr = NULL;
    $var_arr = Array();

    $var_arr['vendorid']['type'] =       'key';
    $var_arr['wp_userid']['type'] =      'key';
    $var_arr['vendorname'] =             Array('type' => 'string', 'min' => 1, 'max' => 4096);
    $var_arr['vendoraddress'] =          Array('type' => 'string', 'max' => 100);
    $var_arr['vendorcity'] =             Array('type' => 'string', 'min' => 1, 'max' => 30);
    $var_arr['vendorstate'] =            Array('type' => 'string', 'max' => 30);
    $var_arr['vendorzip']['type'] =      'key';
    $var_arr['vendordescription'] =      Array('type' => 'string', 'max' => 4096);
    $var_arr['vendortelephone'] =        Array('type' => 'string', 'min' => 1, 'max' => 15);
    $var_arr['vendorwebsite'] =          Array('type' => 'string', 'max' => 100);
    $var_arr['vendorstatus']['type'] =   'int';
    $var_arr['latitude'] =               Array('type' => 'string', 'max' => 1);
    $var_arr['longitude'] =              Array('type' => 'string', 'min' => 1);
    $var_arr['vendoricon'] =             Array('type' => 'string', 'max' => 255);
    $var_arr['vendorrecordid']['type'] = 'key';
    switch($action)
    {
      case 'update':
      case 'select':
        $err_arr = $this->checkTypes($var_arr, $ignore_arr);
        break;
      case 'insert':
        // id will never be checked on insertion
        $ignore_arr['vendorid'] = true;
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
      $_SESSION['errout'][] = $strErrors;
      Debug::Debug_arr($err_arr, DEBUG_LEVEL);  */
      return FALSE;
    }
  }


  public function LoadObjectWithArray($arrArray){
      //we need to make this fit for the locaor logic
       $this->intVendorId = @(int)$arrArray['vendorid'];
       $this->intWPUserId = @(int)$arrArray['wp_userid'];
       $this->strVendorName = (string)stripslashes($arrArray['vendorname']);
       $this->strVendorAddress = (string)stripslashes($arrArray['vendoraddress']);
       $this->strVendorCity = (string)stripslashes($arrArray['vendorcity']);
       $this->strVendorState = (string)stripslashes($arrArray['vendorstate']);
       $this->intVendorZip = (int)stripslashes($arrArray['vendorzip']);
       $this->strVendorDescription = (string)stripslashes($arrArray['vendordescription']);
       $this->strVendorPhone = (string)stripslashes($arrArray['vendortelephone']);
       $this->strVendorWebsite = (string)stripslashes($arrArray['vendorwebsite']);
       $this->intVendorStatus = (int)stripslashes($arrArray['vendorstatus']);
       (float)$this->floatVendorLatitude = @stripslashes($arrArray['latitude']);
       (float)$this->floatVendorLongitude = @stripslashes($arrArray['longitude']);
       $this->strVendorIcon = (string)stripslashes($arrArray['vendoricon']);
       $this->intVendorRecordId = @(int)stripslashes($arrArray['vendorrecordid']);
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
      if(!empty($arrObjectData['wp_userid']))
        $this->intWPUserId = (int) $arrObjectData['wp_userid'];
      if(!empty($arrObjectData['vendorname']))
        $this->strVendorName = (string) $arrObjectData['vendorname'];
      if(!empty($arrObjectData['vendoraddress']))
        $this->strVendorAddress = (string) $arrObjectData['vendoraddress'];
      if(!empty($arrObjectData['vendorcity']))
        $this->strVendorCity = (string) $arrObjectData['vendorcity'];
      if(!empty($arrObjectData['vendorstate']))
        $this->strVendorState = (string) $arrObjectData['vendorstate'];
      if(!empty($arrObjectData['vendorzip']))
        $this->intVendorZip = (int) $arrObjectData['vendorzip'];
      if(!empty($arrObjectData['vendordescription']))
        $this->strVendorDescription = (string) $arrObjectData['vendordescription'];
      if(!empty($arrObjectData['vendortelephone']))
        $this->strVendorPhone = (string) $arrObjectData['vendortelephone'];
      if(!empty($arrObjectData['vendorwebsite']))
        $this->strVendorWebsite = (string) $arrObjectData['vendorwebsite'];
      if(!empty($arrObjectData['vendorstatus']))
        $this->intVendorStatus = (int) $arrObjectData['vendorstatus'];
      if(!empty($arrObjectData['latitude']))
        $this->floatVendorLatitude = (float) $arrObjectData['latitude'];
      if(!empty($arrObjectData['longitude']))
        $this->floatVendorLongitude = (float) $arrObjectData['longitude'];
      if(!empty($arrObjectData['vendoricon']))
        $this->strVendorIcon = (string) $arrObjectData['vendoricon'];
      if(!empty($arrObjectData['vendorrecordid']))
        $this->intVendorRecordId = (int) $arrObjectData['vendorrecordid'];
      return TRUE;                                       
    }

    /*
    @brief load an array with the TABLEOBJECT object
    @param $objTABLEOBJECT
    @return array(TABLEOBJECT)
    */
    public function LoadArrayWithObject($objTABLEOBJECT=null){
     $arrArray = array();
       (int)$arrArray['vendorid'] = $this->intVendorId;
       (int)$arrArray['wp_userid'] = $this->intWPUserId;
       (string)$arrArray['vendorname'] = $this->strVendorName;
       (string)$arrArray['vendoraddress'] = $this->strVendorAddress;
       (string)$arrArray['vendorcity'] = $this->strVendorCity;
       (string)$arrArray['vendorstate'] = $this->strVendorState;
       (int)$arrArray['vendorzip'] = $this->intVendorZip;
       (string)$arrArray['vendordescription'] = $this->strVendorDescription;
       (string)$arrArray['vendortelephone'] = $this->strVendorPhone;
       (string)$arrArray['vendorwebsite'] = $this->strVendorWebsite;
       (int)$arrArray['vendorstatus'] = $this->intVendorStatus;
       (float)$arrArray['latitude'] = $this->floatVendorLatitude;
       (float)$arrArray['longitude'] = $this->floatVendorLongitude;
       (string)$arrArray['vendoricon'] = $this->strVendorIcon;
       (int)$arrArray['vendorrecordid'] = $this->intVendorRecordId;
     return $arrArray;
    }
}//end class   
?>