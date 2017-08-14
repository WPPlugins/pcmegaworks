<?php
/**************************************************
* Class :PCMW_FormDefinition
* @brief Hold the attribute loaded into it
*
***************************************************/
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_BaseClass.php');
// If this file is called directly, abort.
/*                     
/**
 * @class PCMW_FormDefinition
 * @brief Well-formed object declaration of the 'PCMW_FormDefinition' database table.
 */
class PCMW_FormDefinition extends PCMW_BaseClass
{
    public $intDefinitionId;// INT NOT NULL AUTO_INCREMENT PRIMARY KEY
    public $strDefinitionName;// VARCHAR( 100 ) NOT NULL
    public $strElementTitle;// VARCHAR( 255 ) NOT NULL
    public $intElementMax;// INT( 9 ) NOT NULL
    public $strElementType;// VARCHAR( 30 ) NOT NULL
    public $strElementParentType;// VARCHAR( 30 ) NOT NULL DEFAULT  'div'
    public $strElementClass;// VARCHAR( 255 ) NULL
    public $strParentElementClass;// VARCHAR( 255 ) NULL
    public $intValidationId;// INT( 9 ) NOT NULL DEFAULT  '0'
    public $strDefaultValues;// VARCHAR( 20 ) NULL
    public $intFormOrder;// INT( 9 ) NOT NULL
    public $strOnclick;// VARCHAR( 255 ) NULL
    public $strOnChange;// VARCHAR( 255 ) NULL
    public $strOnKeyUp;// VARCHAR( 255 ) NULL
    public $intFormGroup;// INT ( 11 ) NOT NULL
    public $strElementAttributes;// text COMMENT 'curl string of attributes for the element'
    //hold validation errors
    public $arrValidationErrors;

  function __construct()
  {
  
  }


  public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_FormDefinition();
		return( $inst );
   }

  function Validate($action = "select", $ignore_arr = null)
  {
    $err_arr = NULL;
    $var_arr = Array();

    $var_arr['definitionid']['type'] =          'key';
    $var_arr['elementname'] =                   Array('type' => 'string','min' => 1, 'max' => 100);
    $var_arr['elementtitle'] =                  Array('type' => 'string', 'min' => 1, 'max' => 255);
    $var_arr['maxlength']['type'] =             Array('type' => 'string', 'min' => 1, 'max' => 11);
    $var_arr['elementtype'] =                   Array('type' => 'string', 'min' => 1, 'max' => 30);
    $var_arr['parentelementtype'] =             Array('type' => 'string', 'max' => 30);
    $var_arr['elementclass'] =                  Array('type' => 'string', 'max' => 255);
    $var_arr['parentelementclass'] =            Array('type' => 'string', 'max' => 255);
    $var_arr['elementvalidationid']['type'] =   'key';
    $var_arr['defaultvaluesid'] =               Array('type' => 'string', 'max' => 75);
    $var_arr['formorder']['type'] =             'int';
    $var_arr['onclick'] =                       Array('type' => 'string', 'max' => 255);
    $var_arr['onchange'] =                      Array('type' => 'string', 'max' => 255);
    $var_arr['onkeyup'] =                       Array('type' => 'string', 'max' => 255);
    $var_arr['elementattributes'] =             Array('type' => 'string', 'max' => 4096);

    switch($action)
    {
      case 'update':
      case 'select':
        $err_arr = $this->checkTypes($var_arr, $ignore_arr);
        break;
      case 'insert':
        // id will never be checked on insertion
        $ignore_arr['definitionid'] = true;
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
      PCMW_Logger::Debug($err_arr, 1);
      return FALSE;
    }
  }


  public function LoadObjectWithArray($arrArray){
    $this->intDefinitionId = (int) stripslashes($arrArray['definitionid']);
    $this->strDefinitionName = (string) stripslashes(trim($arrArray['elementname']));
    $this->strElementTitle = (string) stripslashes(trim($arrArray['elementtitle']));
    $this->intElementMax = (int) stripslashes($arrArray['maxlength']);
    $this->strElementType = (string) stripslashes(trim($arrArray['elementtype']));
    $this->strElementParentType = (string) stripslashes(trim($arrArray['parentelementtype']));
    $this->strElementClass = (string) stripslashes(trim($arrArray['elementclass']));
    $this->strParentElementClass = (string) stripslashes(trim($arrArray['parentelementclass']));
    $this->intValidationId = (int) stripslashes($arrArray['elementvalidationid']);
    $this->strDefaultValues = (string) stripslashes(trim($arrArray['defaultvaluesid']));
    $this->intFormOrder = (int) stripslashes($arrArray['formorder']);
    $this->strOnclick = (string) stripslashes(trim($arrArray['onclick']));
    $this->strOnChange = (string) stripslashes(trim($arrArray['onchange']));
    $this->strOnKeyUp = (string) stripslashes(trim($arrArray['onkeyup']));
    $this->intFormGroup = (int) stripslashes($arrArray['formgroup']);
    $this->strElementAttributes = (string) stripslashes(trim($arrArray['elementattributes']));
     return $this;

    }

    /*
    @brief load an array with the Definition object
    @param $objDefinition
    @return array(Definition)
    */
    public function LoadArrayWithObject($objDefinition=null){
     $arrArray = array();
     (int) $arrArray['definitionid'] = $this->intDefinitionId;
     (string) $arrArray['elementname'] = trim($this->strDefinitionName);
     (string) $arrArray['elementtitle'] = trim($this->strElementTitle);
     (int) $arrArray['maxlength'] = $this->intElementMax;
     (string) $arrArray['elementtype'] = trim($this->strElementType);
     (string) $arrArray['parentelementtype'] = trim($this->strElementParentType);
     (string) $arrArray['elementclass'] = trim($this->strElementClass);
     (string) $arrArray['parentelementclass'] = trim($this->strParentElementClass);
     (int) $arrArray['elementvalidationid'] = $this->intValidationId;
     (string) $arrArray['defaultvaluesid'] = trim($this->strDefaultValues);
     (int) $arrArray['formorder'] = $this->intFormOrder;
     (string) $arrArray['onclick'] = trim($this->strOnclick);
     (string) $arrArray['onchange'] = trim($this->strOnChange);
     (string) $arrArray['onkeyup'] = trim($this->strOnKeyUp);
     (int) $arrArray['formgroup'] = $this->intFormGroup;
     (string) $arrArray['elementattributes'] = trim($this->strElementAttributes);
     return $arrArray;
    }

}//end class Definition
?>