<?php
/**************************************************
* Class :PCMW_Video
* @brief Hold the attribute loaded into it
*
***************************************************/
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_BaseClass.php');
// If this file is called directly, abort.
/*
/**
 * @class PCMW_Video
 * @brief Well-formed object declaration of the 'PCMW_Video' database table.
 */
class PCMW_Video extends PCMW_BaseClass
{
    var $intVideoId;
    var $strVideoTitle;
    var $strVideoSource;
    var $strVideoType;
    var $intAccessLevel;
    var $strCSSClass;
    var $intVideoHeight;
    var $intVideoWidth;
    var $intShowControls;
    var $intHideSource;
    var $intAddWatermark;
    var $intUDate;
    //hold validation errors
    public $arrValidationErrors;

  function __construct()
  {
  
  }


  public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_Video();
		return( $inst );
   }

  function Validate($action = "select", $ignore_arr = null)
  {
    $err_arr = NULL;
    $var_arr = Array();
    $var_arr['videoid']['type'] =      'key';
    $var_arr['videotitle'] =           Array('type' => 'string','min' => 1, 'max' => 100);
    $var_arr['videosource'] =          Array('type' => 'string', 'min' => 1, 'max' => 255);
    $var_arr['videotype'] =            Array('type' => 'string', 'min' => 1, 'max' => 15);
    $var_arr['accesslevel']['type'] =  'int';
    $var_arr['cssclass'] =             Array('type' => 'string', 'max' => 100);
    $var_arr['videoheight']['type'] =  'int';
    $var_arr['videowidth']['type'] =   'int';
    $var_arr['showcontrols']['type'] = 'int';
    $var_arr['hidesource']['type'] =   'int';
    $var_arr['addwatermark']['type'] = 'int';
    $var_arr['udate']['type'] =        'int';

    switch($action)
    {
      case 'update':
      case 'select':
        $err_arr = $this->checkTypes($var_arr, $ignore_arr);
        break;
      case 'insert':
        // id will never be checked on insertion
        $ignore_arr['videoid'] = true;
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
    $this->intVideoId = (int) stripslashes($arrArray['videoid']);
    $this->strVideoTitle = (string) stripslashes(trim($arrArray['videotitle']));
    $this->strVideoSource = (string) stripslashes(trim($arrArray['videosource']));
    $this->strVideoType = (string) stripslashes($arrArray['videotype']);
    $this->intAccessLevel = (int) stripslashes(trim($arrArray['accesslevel']));
    $this->strCSSClass = (string) stripslashes(trim($arrArray['cssclass']));
    $this->intVideoHeight = (int) stripslashes(trim($arrArray['videoheight']));
    $this->intVideoWidth = (int) stripslashes(trim($arrArray['videowidth']));
    $this->intShowControls = (int) stripslashes($arrArray['showcontrols']);
    $this->intHideSource = (int) stripslashes(trim($arrArray['hidesource']));
    $this->intAddWatermark = (int) stripslashes($arrArray['addwatermark']);
    $this->intUDate = (int) stripslashes(trim($arrArray['udate']));
     return $this;

    }

    /*
    @brief load an array with the Definition object
    @param $objDefinition
    @return array(Definition)
    */
    public function LoadArrayWithObject(){
     $arrArray = array();
     $arrArray['videoid'] = (int) $this->intVideoId;
     $arrArray['videotitle'] = (string) stripslashes(trim($this->strVideoTitle));
     $arrArray['videosource'] = (string) stripslashes(trim($this->strVideoSource));
     $arrArray['videotype'] = (string) stripslashes(trim($this->strVideoType));
     $arrArray['accesslevel'] = (int) $this->intAccessLevel;
     $arrArray['cssclass'] = (string) stripslashes(trim($this->strCSSClass));
     $arrArray['videoheight'] = (int) $this->intVideoHeight;
     $arrArray['videowidth'] = (int) $this->intVideoWidth;
     $arrArray['showcontrols'] = (int) $this->intShowControls;
     $arrArray['hidesource'] = (int) $this->intHideSource;
     $arrArray['addwatermark'] = (int) $this->intAddWatermark;
     $arrArray['udate'] = (int) $this->intUDate;
     return $arrArray;
    }

}//end class Definition
?>