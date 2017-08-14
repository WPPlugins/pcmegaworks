<?php
/**************************************************
* Class :PCMW_StaticArray
* @brief manipulate the static arrays table to provide loosely associated
* arrays based on purpose note and intent. Prevents static arrays in pages,
* while allowing simple grouping for clients, stations or other abstract data
* @REQUIRES
*   -PCMW_BaseClass.php
*
***************************************************/
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_BaseClass.php');
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
/**
 * @class PCMW_StaticArray
 * @brief Well-formed object declaration of the 'PCMW_StaticArray' database table.
 */
class PCMW_StaticArray extends PCMW_BaseClass
{
    public $intStaticArrayId;// int(11) NOT NULL AUTO_INCREMENT
    public $varMenuIndex;// varchar(20) NOT NULL
    public $varMenuValue;// varchar(100) NOT NULL
    public $strPurpose;// varchar(20) NOT NULL
    public $intClientId;// int(9) DEFAULT '0'
    public $intForeignId;// int(8) DEFAULT '0'
    public $strModifier;// text
    public $strPurposeNote;// varchar(255) DEFAULT NULL
    //hold validation errors
    public $arrValidationErrors;

  function __construct()
  {
  }


  public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_StaticArray();
		return( $inst );
   }

  function Validate($action = "select", $ignore_arr = null)
  {
    $err_arr = NULL;
    $var_arr = Array();

    $var_arr['id']['type'] =            'key';
    $var_arr['modulename'] =            Array('type' => 'string', 'max' => 100);
    $var_arr['moduleplacement'] =       Array('type' => 'string', 'min' => 1, 'max' => 4096);
    $var_arr['moduleplacement'] =       Array('type' => 'string', 'max' => 30);
    $var_arr['modulestatus']['type'] =  'key';
    $var_arr['pagename'] =              Array('type' => 'string', 'max' => 50);

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
      PCMW_Logger::Debug(__CLASS__." class instance failed validation due to following errors: ", 1);
      Debug::Debug_arr($err_arr, 1);
      return FALSE;
    }
  }



  public function LoadObjectWithArray($arrArray){
    $this->intStaticArrayId = (int) $arrArray['arrayid'];
    $this->varMenuIndex = (string) $arrArray['menuindex'];
    $this->varMenuValue = (string) $arrArray['menuvalue'];
    $this->strPurpose = (string) $arrArray['purpose'];
    $this->intClientId = (int) $arrArray['clientid'];
    $this->intForeignId = (int) $arrArray['foreignid'];
    $this->strModifier = (string) $arrArray['modifier'];
    $this->strPurposeNote = (string) $arrArray['purposenote'];
     return $this;

    }

    /*
    @brief load an array with the PCMW_StaticArray object
    @param $objStaticArray
    @return array(PCMW_StaticArray)
    */
    public function LoadArrayWithObject($objStaticArray){
     $arrArray = array();
     (int) $arrArray['arrayid'] = $this->intStaticArrayId;
     (string) $arrArray['menuindex'] = $this->varMenuIndex;
     (string) $arrArray['menuvalue'] = $this->varMenuValue;
     (string) $arrArray['purpose'] = $this->strPurpose;
     (int) $arrArray['clientid'] = $this->intClientId;
     (int) $arrArray['foreignid'] = $this->intForeignId;
     (string) $arrArray['modifier'] = $this->strModifier;
     (string) $arrArray['purposenote'] = $this->strPurposeNote;
     return $arrArray;
    }

}//end class PCMW_StaticArray
?>