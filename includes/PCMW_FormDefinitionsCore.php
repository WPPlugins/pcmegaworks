<?php
/**************************************************************************
* @CLASS PCMW_FormDefinitionsCore
* @brief Create, update and get definitions for forms.
* @REQUIRES:
*  -includes/PCMW_Database.php
*  -includes/PCMW_Utility.php
*
**************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
class PCMW_FormDefinitionsCore{

   //debugging
   var $boolDebugOn = FALSE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';

   public static function Get(){
		//==== instantiate or retrieve singleton from Josh and medad code====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_FormDefinitionsCore();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
  }

  #REGION UTILITY METHODS
  //All general use functions specific to this class go here

  /*
  @brief Get the methods available for debugging
  *keep this at the top of the class for easy access and update
  @return array()
  */
  function GetDebugMethods(){
    $arrDebugMethods = array();
    $arrDebugMethods['PCMW_FormDefinitionsCore::CLASSMETHOD'] = 1;
    return $arrDebugMethods;
  }

  #ENDREGION

  #REGION DATACALCULATION
  //All methods which handle specific data calculation and manipulation methods should go here

  #ENDREGION


  #REGION DATABASECALLS
  //All functions which will interact with PCMW_Database.php should go in here

  /**
  * given a form ID, get the definitions
  * @param $intFormId
  * @return array of objects
  */
  function GetDefinitionsById($intDefinitionId,$boolAsArray=FALSE){
    $arrDefinitions = array();
    if($arrDefinitionData = PCMW_Database::Get()->GetFormDefinitions($intDefinitionId)){
      if($boolAsArray)
        return $arrDefinitionData;
       foreach($arrDefinitionData as $arrDefinition){
         $arrDefinitions[$arrDefinition['definitionid']] = new PCMW_FormDefinition();
         $arrDefinitions[$arrDefinition['definitionid']]->LoadObjectWithArray($arrDefinition);
       }
    }
    //$strDefintiions = var_export($arrDefinitions,TRUE);
    //PCMW_Logger::Debug('$strDefintiions ['.$strDefintiions.'] METHOD ['.__METHOD__.'] LINE '.__LINE__,1);
    return $arrDefinitions;
  }

  /**
  * given an alias and admin group, get the definitions
  * @param $strFormAlias
  * @param $intAdminGroup
  * @return array of objects
  */
  function GetDefinitionsByAlias($strFormAlias,$intAdminGroup=0,$boolAsArray=FALSE){
    $arrDefinitions = array();
    if($arrDefinitionData = PCMW_Database::Get()->GetFormDefinitionsByAlias($strFormAlias,$intAdminGroup)){
      if($boolAsArray)
        return $arrDefinitionData;
       foreach($arrDefinitionData as $arrDefinition){
         $arrDefinitions[$arrDefinition['definitionid']] = new PCMW_FormDefinition();
         $arrDefinitions[$arrDefinition['definitionid']]->LoadObjectWithArray($arrDefinition);
       }
    }
    return $arrDefinitions;
  }

  /**
  * given a Definition object, validate and insert, or update it
  * @param $objDefinition Assembled object
  * @param $arrPOST  post data to validate against
  * @param &$objFormManager instance of form manager for error compiling
  * @param $strFormAlias form alias to extract definition requires from
  * @param $arrIgnoreFields Fields to ignore validation for
  * @return bool || int (id)
  */
  function CleanAndInsertDefinition($objDefinition,$arrPOST,&$objFormManager,$strFormAlias,$arrIgnoreFields=array()){
     $objFormManager->arrIgnoreFields =PCMW_Utility::Get()->MergeArrays($objFormManager->arrIgnoreFields,$arrIgnoreFields);
    $strAction = 'insert';
    if($objDefinition->intDefinitionId > 0)
        $strAction = 'update';
        $strDefinitionObject = var_export($objDefinition,TRUE);
        //PCMW_Logger::Debug('$strDefinitionObject ['.$strDefinitionObject.'] $strAction['.$strAction.']',1);
        //return TRUE;
    if(!$objFormManager->ValidateDefinitionRequires(0,$arrPOST,$objDefinition,$strFormAlias,TRUE,$strAction)){
     //load the errors for all to see
     $strErrors = var_export($objDefinition->arrValidationErrors,TRUE);
     PCMW_Logger::Debug('['.__CLASS__.'] validation Errors ['.$strErrors.'] LINE ['.__LINE__.'] METHOD ['.__METHOD__.']',1);
     return FALSE;
    }
    //we're done, execute
    return $this->InsertOrUpdateDefinition($objDefinition);
  }

  /**
  * given a validated Definition object, insert/update it and give back the ID
  * @param $objDefinition
  * @return int definition id
  */
  function InsertOrUpdateDefinition($objDefinition){
    if($objDefinition->intDefinitionId > 0)
      return PCMW_Database::Get()->UpdateDefinition($objDefinition);
    else
      return PCMW_Database::Get()->InsertNewDefinition($objDefinition);
  }


  /**
  * given a validated Definition Group object, insert/update it and give back the ID
  * @param $objDefinitionGroup
  * @return int definition group id
  */
  function InsertOrUpdateDefinitionGroup($objDefinitionGroup){
    if($objDefinitionGroup->intFormId > 0)
      return PCMW_Database::Get()->UpdateFormData($objDefinitionGroup);
    else
      return PCMW_Database::Get()->InsertNewFormData($objDefinitionGroup);
  }


  /**
  * given a DefinitionGroup object, validate and insert, or update it
  * @param $objDefinitionGroup Assembled object
  * @param $arrPOST  post data to validate against
  * @param &$objFormManager instance of form manager for error compiling
  * @param $strFormAlias form alias to extract DefinitionGroup requires from
  * @param $arrIgnoreFields Fields to ignore validation for
  * @return bool || int (id)
  */
  function CleanAndInsertDefinitionGroup($objDefinitionGroup,$arrPOST,&$objFormManager,$strFormAlias,$arrIgnoreFields=array()){
     $objFormManager->arrIgnoreFields =PCMW_Utility::Get()->MergeArrays($objFormManager->arrIgnoreFields,$arrIgnoreFields);

    $strAction = 'insert';
    if($objDefinitionGroup->intFormId > 0)
        $strAction = 'update';
    if(!$objFormManager->ValidateDefinitionRequires(0,$arrPOST,$objDefinitionGroup,$strFormAlias,TRUE,$strAction)){
     //load the errors for all to see
     $strErrors = var_export($objDefinitionGroup->arrValidationErrors,TRUE);
     PCMW_Logger::Debug('['.__CLASS__.'] validation Errors ['.$strErrors.'] LINE ['.__LINE__.'] METHOD ['.__METHOD__.']',1);
     return FALSE;
    }
    //all done
    return $this->InsertOrUpdateDefinitionGroup($objDefinitionGroup);
  }

  #ENDREGION

  #REGION DEBUGGINGMETHODS

 /*
  @brief store and display debug information
  @param $strMessage,$boolLogNow
  @param boolean
  */
  function PCMW_LoadDebugLog($strMessage='',$boolLogNow=FALSE,$strMethod='',$boolShowBackTrace=FALSE){
  //$this->PCMW_LoadDebugLog('VALUE ['.$arrPOST .'-  LINE '.__LINE__."\r\n",FALSE,__METHOD__);
    //PCMW_Abstraction::Get()->AddUserMSG( 'message METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
    if($this->boolDebugOn){
      if($strMessage != "")
      //add the message
        $this->strDebugMessage .= $strMessage;
      if($boolLogNow){
        if($strMethod == "" || ($strMethod != "" && array_key_exists($strMethod,$this->GetDebugMethods()))){
          if((int)$this->boolDebugOn == 2)
            PCMW_Abstraction::Get()->AddUserMSG( '$this->strDebugMessage ['.$this->strDebugMessage.'] METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',9);
          else
            PCMW_Logger::Debug($this->strDebugMessage.' METHOD ['.$strMethod.']  this method ['.__METHOD__.'] LINE ['.__LINE__.']',$this->intDebugLevel,$boolShowBackTrace);
        }
        //clean up
        $this->strDebugMessage = '';
      }
    }
  }

  #ENDREGION

}//end class
?>