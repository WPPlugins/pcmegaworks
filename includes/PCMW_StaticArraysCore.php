<?php
/**************************************************************************
* @CLASS PCMW_StaticArrays
* @brief This will give us a portal to static, then eventually dynamic arrays
* for use with process flow restrictions and permissions
* @REQUIRES:
*   -PCMW_Database.php
*   -PCMW_Utility.php
*   -PCMW_Abstraction.php
*   -PCMW_Logger.php
*   -PCMW_StaticArray.php
*   -PCMW_StringComparison.php
*
**************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_StaticArray.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_StringComparison.php');
class PCMW_StaticArrays{

   //debugging
   public static $boolDebugOn = TRUE;
   public static $intDebugLevel = 1;
   public static $strDebugMessage = '';
   public $boolModifierOnly = FALSE;

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_StaticArrays();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
  }

  /*
  @brief Get the methods available for debugging
  *keep this at the top of the class for easy access and update
  @return array()
  */
  public static function GetDebugMethods(){
    $arrDebugMethods = array();
    $arrDebugMethods['PCMW_StaticArrays::LoadStaticArrayType'] = 1;
    return $arrDebugMethods;
  }


  /*
  @brief Get an array of data based on loaded or passed studyid
  @brief if no study id is passed or loaded and loadlocalstudy is true, FALSE will return
  @param $strPurpose,$boolFullRecord,$intStudyId,$boolLoadLocalStudy
  @return array(){
  */
  public static function LoadStaticArrayType($strPurpose,$boolFullRecord=TRUE,$intAdminGroup=0,$boolIgnoreModifier=FALSE,$intClientId=0,$varMenuIndex='',$boolGroupByIndex=TRUE){
    $strDebugMessage = '';
    //ok, we have a study Id, get the array
    if(($arrArray = PCMW_Database::Get()->GetStaticArrayGroup($strPurpose,$intAdminGroup,$intClientId,$varMenuIndex,$boolGroupByIndex))){
      //$strFormData = var_export($arrArray,TRUE);
      //self::PCMW_LoadDebugLog('$strFormData ['.$strFormData .'] $strGroupAlias ['.$strGroupAlias.'] $intAdminGroupId ['.$intAdminGroupId.'] LINE '.__LINE__."\r\n",TRUE,__METHOD__);
      if($boolFullRecord)
      //you asked for it
        return $arrArray;
      if(sizeof($arrArray) == 1 && PCMW_StaticArrays::Get()->boolModifierOnly)
        return $arrArray[0]['modifier'];
      $arrReturnArray = array();
      foreach($arrArray as $arrRow){
        if($arrRow['modifier'] == '' || $boolIgnoreModifier)
           //no sub array to bother with
          $arrReturnArray[$arrRow['menuindex']] = (trim($arrRow['menuvalue']) == '')? 'NULL':$arrRow['menuvalue'];
        else{
          //we need to check for a modifier subarray
          $arrSubArray = PCMW_Utility::Get()->DecomposeCurlString($arrRow['modifier']);
          if(is_array($arrSubArray) && $arrRow['menuvalue'] == '')
              $arrReturnArray[$arrRow['menuindex']] = $arrSubArray;
          if(is_array($arrSubArray) && $arrRow['menuvalue'] != ''){
              array_unshift($arrSubArray,$arrRow['menuvalue']);
              $arrReturnArray[$arrRow['menuindex']] = $arrSubArray;
          }
          //single value array, defined by subindex
          if((!is_array($arrSubArray)) && $arrRow['menuvalue'] != '')
              $arrReturnArray[$arrRow['menuindex']] = (array($arrRow['menuvalue']=>$arrRow['modifier']));
        }
      }
      //should be all we need
      return $arrReturnArray;
    }
    return FALSE;
  }


  /**
  * given an array of data check the structure for conditions and verify the
  * element is valid for display
  * @return bool
  */
  public static function ValidateArrayValues(&$arrArray,$arrData,$boolArrange=FALSE){
    $arrReturnArray = array();
    foreach($arrArray as $arrArrayData){
      $arrModifier = PCMW_Utility::Get()->DecomposeCurlString($arrArrayData['modifier']);
      foreach($arrModifier as $intKey=>$varData){
       if(is_array($varData) && array_key_exists('conditions',$varData)){//let's check for conditions
         $arrConditions = json_decode($varData['conditions'],TRUE);
         foreach($arrConditions as $varCondition){
          if(is_array($varCondition)){
           foreach($varCondition as $varIndex=>$strValue){
             //this is an == operator that get's split on retrieval
             if(!stristr($varIndex,'condition'))
              $strValue = $varIndex.'='.$strValue;
             if(!PCMW_StringComparison::Get()->MakeStringComparison($strValue,$arrData)){
                unset($arrArray[$intKey]);//skip this header, it's not applicable here
                continue 2;
             }
           }
           //all passed
           if($boolArrange && array_key_exists('formorder',$arrModifier))
            $arrReturnArray[$arrModifier['formorder']] = array($arrArrayData['menuindex'],$arrArrayData['menuvalue']);
           else
            $arrReturnArray[$intKey] = $arrArrayData['menuvalue'];
          }
          else{
            if(!PCMW_StringComparison::Get()->MakeStringComparison($varCondition,$arrData))
                unset($arrArray[$intKey]);//skip this header, it's not applicable here
            else{
             if($boolArrange && array_key_exists('formorder',$arrModifier))
              $arrReturnArray[$arrModifier['formorder']] = array($arrArrayData['menuindex'],$arrArrayData['menuvalue']);
             else
              $arrReturnArray[$intKey] = $arrArrayData['menuvalue'];
            }
          }
         }
       }
       else{
         //we still need these
           if($boolArrange && array_key_exists('formorder',$arrModifier))
            $arrReturnArray[$arrModifier['formorder']] = array($arrArrayData['menuindex'],$arrArrayData['menuvalue']);
           else
            $arrReturnArray[$intKey] = $arrArrayData['menuvalue'];
       }
      }
    }
    ksort($arrReturnArray);
    $arrArray = $arrReturnArray;
    return TRUE;
  }
  #ENDREGION

  #REGION DEBUGGINGMETHODS

  /*
  @brief store and display debug information
  @param $strMessage,$boolLogNow
  @param boolean
  */
  public static function PCMW_LoadDebugLog($strMessage='',$boolLogNow=FALSE,$strMethod='',$boolShowBackTrace=FALSE){
  //$this->PCMW_LoadDebugLog('VALUE ['.$arrPOST .'-  LINE '.__LINE__."\r\n",FALSE,__METHOD__);
    //PCMW_Abstraction::Get()->AddUserMSG( 'message METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
    if(self::$boolDebugOn){
      if($strMessage != "")
      //add the message
        self::$strDebugMessage .= $strMessage;
      if($boolLogNow){
        if($strMethod == "" || ($strMethod != "" && array_key_exists($strMethod,self::GetDebugMethods()))){
          if((int)self::$boolDebugOn == 2)
            PCMW_Abstraction::Get()->AddUserMSG( '$this->strDebugMessage ['.self::$strDebugMessage.'] METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',9);
          else
            PCMW_Logger::Debug(self::$strDebugMessage.' METHOD ['.$strMethod.']  this method ['.__METHOD__.'] LINE ['.__LINE__.']',self::$intDebugLevel,$boolShowBackTrace);
        }
        //clean up
        self::$strDebugMessage = '';
      }
    }
  }

  #ENDREGION

}//end class    
?>