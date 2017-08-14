<?php
/**************************************************
* Class :PCMW_FormGroup
* @brief Hold the attribute loaded into it
* -REQUIRES
*   -PCMW_FormDefinition.php
*
***************************************************/  
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_BaseClass.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_FormDefinition.php');
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
/**
 * @class PCMW_FormGroup
 * @brief Well-formed object declaration of the 'PCMW_FormGroup' database table.
 */
class PCMW_FormGroup  extends PCMW_BaseClass
{
    public $intFormId;// int(11) NOT NULL AUTO_INCREMENT
    public $strFormName;// varchar(255) NOT NULL
    public $intAdminGroup;// int(11) NULL
    public $strGroupAlias;// VARCHAR( 35 ) NOT NULL
    public $arrFormDefinitions = array();
    //hold validation errors
    public $arrValidationErrors;

  function __construct()
  {
  }


  public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_FormGroup();
		return( $inst );
   }

  function Validate($action = "select", $ignore_arr = null)
  {
    $err_arr = NULL;
    $var_arr = Array();

    $var_arr['formid']['type'] =      'key';
    $var_arr['formname'] =            Array('type' => 'string','min'=>5, 'max' => 255);
    $var_arr['admingroup']['type'] =  'key';
    $var_arr['formalias'] =    Array('type' => 'string','min'=>5, 'max' => 35);

    switch($action)
    {
      case 'update':
      case 'select':
        $err_arr = $this->checkTypes($var_arr, $ignore_arr);
        break;
      case 'insert':
        // id will never be checked on insertion
        $ignore_arr['formid'] = true;
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
     $this->intFormId = (int) $arrArray['formid'];
     $this->strFormName = (string) $arrArray['formname'];
     $this->intAdminGroup = (int) $arrArray['admingroup'];
     $this->strGroupAlias = (string)$arrArray['formalias'];
     return $this;

   }

    /**
    * load the member array of Forms with an object
    * @param object $objForm Form object
    * @return bool
    */
    function LoadDefinitionObject($objFormDefinition){
      if(is_object($objFormDefinition)){
        $this->arrFormDefinitions[] = $objFormDefinition;
        return TRUE;
      }
      return FALSE;
    }


    /**
    * load the member array of Forms with an object
    * @param array $arrForm Form object
    * @return bool
    */
    function LoadDefinitionObjectWitharray($arrFormDefinition){
      $objFormDefinition = new PCMW_FormDefinition();
      $objFormDefinition = $objFormDefinition->LoadObjectWithArray($arrFormDefinition);
      if(is_object($objFormDefinition)){
        $this->arrFormDefinitions[] = $objFormDefinition;
        return TRUE;
      }
      return FALSE;
    }

    /*
    @brief load an array with the PCMW_FormGroup object   
    @return array(PCMW_FormGroup)
    */
    public function LoadArrayWithObject(){
     $arrArray = array();
     (int) $arrArray['formid'] = $this->intFormId;
     (string) $arrArray['formname'] = $this->strFormName;
     (int) $arrArray['admingroup'] = $this->intAdminGroup;
     (string)$arrArray['formalias'] = $this->strGroupAlias;
     return $arrArray;
    }

}//end class PCMW_FormGroup
?>