<?php
/**************************************************************************
* @CLASS PCMW_FormManager
* @brief Create, update and get definitions for forms.
* @REQUIRES:
*  -PCMW_Database.php
*  -PCMW_Utility.php
*
**************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
  die;
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_FormManagerTables.php');
class PCMW_FormManager extends PCMW_FormManagerTables{

   var $arrExternalData = array();
   var $arrMiscValues = array();
   var $arrIgnoreFields = array();//ignore these on secondary validation
   //form alias term override
   var $strFormAliasOverRide = NULL;
   //debugging
   var $boolDebugOn = FALSE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_FormManager();
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
    $arrDebugMethods['PCMW_FormManager::MakeTableizedList'] = 1;
    $arrDebugMethods['PCMW_FormManager::LoadFormGroupByAlias'] = 1;
    $arrDebugMethods['PCMW_FormManager::MakeSpotApprovalForm'] = 1;
    $arrDebugMethods['PCMW_FormManager::BuildSubTotalTable'] = 1;
    $arrDebugMethods['PCMW_FormManager::GetCreativeTitleTable'] = 1;
    $arrDebugMethods['PCMW_FormManager::GetStationsTableData'] = 1;
    $arrDebugMethods['PCMW_FormManager::GetModifyStationData'] = 1;
    $arrDebugMethods['PCMW_FormManager::RemoveFormTitledValues'] = 1;
    $arrDebugMethods['PCMW_FormManager::ValidateDefinitionRequires'] = 1;
    $arrDebugMethods['PCMW_FormManager::LoadFormGroupByAlias'] = 1;
    $arrDebugMethods['PCMW_FormManager::StoreErrors'] = 1;
    $arrDebugMethods['PCMW_FormManager::DeduceFormAliases'] = 1;
    $arrDebugMethods['PCMW_FormManager::FilterConditionalForms'] = 1;
    return $arrDebugMethods;
  }

  /**
  * go through the form variables for inputs and add necessary variable datas
  * @param $objDynamicFormInputs
  * @return bool
  */
  function LoadInputsWithExternalData(PCMW_DynamicFormInputs &$objDynamicFormInputs){
   foreach($this->arrExternalData as $ka=>$va)
    $objDynamicFormInputs->{$ka} = $va;
   return TRUE;
  }

  /**
  * get the form groups and the associated names and return them as an array
  * @return array
  */
  function GetFormAndAdminGroupArray(){
    $arrGroups = PCMW_FormManager::Get()->GetFormData();
    return PCMW_Utility::Get()->MakeSpecialArray($arrGroups,'formname - {groupname} [formalias]', 'formid');
  }


  /**
  * determine who is logged in and extract the proper client form
  * @param $strFormCategory
  * @param $strFormGroup
  * @param $intAccess
  * @param $boolValidate
  * @return string
  */
  function DeduceFormAliases($strFormCategory,$boolValidate=FALSE,$strFormGroup='',$arrData=array()){
    $arrBaseForms = array();
    $arrElements = $this->GetDefinitionByAlias($strFormGroup,PCMW_MODERATOR,TRUE);
    //get the static array of forms for this base form
    if($arrForms = PCMW_StaticArrays::Get()->LoadStaticArrayType($strFormCategory,TRUE,0,FALSE,0,'',FALSE)){
     $this->FilterConditionalForms($arrForms,$arrData);
     //go through our form datas and build the definition array
      foreach($arrForms as $arrFormData){
        $arrModdData = PCMW_Utility::Get()->DecomposeCurlString($arrFormData['modifier']);
        if(array_key_exists('feature',$arrModdData) && $arrModdData['feature'] == 1){
         if(!array_key_exists($arrFormData['menuindex'],$arrData) || $arrData[$arrFormData['menuindex']] < 1)
            continue 1;
        }
        if(!array_key_exists($arrFormData['menuindex'],$arrBaseForms) || !is_array($arrBaseForms[$arrFormData['menuindex']])){
          $arrBaseForms[$arrFormData['menuindex']] = array();
          $arrBaseForms[$arrFormData['menuindex']]['name'] = $arrFormData['menuvalue'];
          $arrBaseForms[$arrFormData['menuindex']]['order'] = $arrModdData['order'];
          $arrBaseForms[$arrFormData['menuindex']]['viewonly'] = @$arrModdData['viewonly'];
          $arrBaseForms[$arrFormData['menuindex']]['action'] = $arrFormData['menuindex'];
          $arrBaseForms[$arrFormData['menuindex']]['dir'] = @$arrModdData['dir'];
          $arrBaseForms[$arrFormData['menuindex']]['data'] = array();
        }
        //get our elements
        $arrFormElements = json_decode($arrModdData['keys'],TRUE);
        foreach($arrFormElements as $strElementName=>$arrOptions){
          foreach($arrElements as $objDefinition){
            if($objDefinition->strDefinitionName == $strElementName){
              if(array_key_exists('viewonly',$arrOptions) && $arrOptions['viewonly'] == 1){
               $objDefinition->strElementAttributes .= '&readonly';
              }
              $arrBaseForms[$arrFormData['menuindex']]['data'][$objDefinition->intDefinitionId] = $objDefinition;
            }
          }
        }
      }
    }
    else{
     return FALSE;
    }
    return $arrBaseForms;
  }


  /**
  * given an array of forms, sort them and eliminate any with non matching
  * conditions, if they exist
  * @param $arrForms
  * @param $arrData
  * @return bool
  */
  function FilterConditionalForms(&$arrForms,$arrData){
    foreach($arrForms as $intKey=>$varData){
     $arrModdParts = PCMW_Utility::Get()->DecomposeCurlString($varData['modifier']);
     if(is_array($arrModdParts) && array_key_exists('conditions',$arrModdParts)){//let's check for conditions
       $arrConditions = json_decode($arrModdParts['conditions'],TRUE);
       foreach($arrConditions as $varCondition){
        if(is_array($varCondition)){
         foreach($varCondition as $varIndex=>$strValue){
           //this is an == operator that get's split on retrieval
           if(!stristr($varIndex,'condition'))
            $strValue = $varIndex.'='.$strValue;
           if(!PCMW_StringComparison::Get()->MakeStringComparison($strValue,$arrData)){
              unset($arrForms[$intKey]);//skip this header, it's not applicable here
              continue 2;
           }
         }
         //all passed
        }
        else{
          if(!PCMW_StringComparison::Get()->MakeStringComparison($varCondition,$arrData))
              unset($arrForms[$intKey]);//skip this header, it's not applicable here
        }
       }
     }
    }
    return TRUE;
  }

  #ENDREGION


  #REGION DATACALCULATION

  /**
  * this will be saving form data and such
  * @param array $arrPOSTData  data we got from the form
  * @return bool
  */
  function HandleFormActions($arrPOST){
    //let's check to make sure we have an action
    if(array_key_exists('formaction',$arrPOST)){
      switch($arrPOST['formaction']){
         case 'newform':
            //are we creating a new form from scratch or a copy?
            if(array_key_exists('copyformid',$arrPOST) && (int)$arrPOST['copyformid'] > 0){
              return $this->CopyForm($arrPOST);
            }
            else{
                $objDefinitionGroup = new PCMW_FormGroup();
                $objDefinitionGroup->LoadObjectWithArray($arrPOST);
                if($intNewFormId = PCMW_FormDefinitionsCore::Get()->CleanAndInsertDefinitionGroup($objDefinitionGroup,
                                                                                                  $arrPOST,
                                                                                                  $this,
                                                                                                  '')){
                //make a page for the form
                if(array_key_exists('makepage',$arrPOST) && trim($arrPOST['makepage']) == 'makepage')
                  PCPluginInstall::Get()->AddSupportingPage($objDefinitionGroup->strFormName,'[makePCform id="'.$intNewFormId.'" makesubmit="0"]',TRUE);
                //create a "formid" definition as well, and 28 is a known good ID
                $arrDefinitions = $this->GetDefinitionByAlias('formidtemplate',PCMW_MODERATOR,TRUE);
                $objDefinition = $arrDefinitions[key($arrDefinitions)];//assign it to the first definition since there is only one
                $objDefinition->intFormGroup = $intNewFormId; //add to our group
                $objDefinition->intDefinitionId = 0;//reset our id
                if(PCMW_FormDefinitionsCore::Get()->InsertOrUpdateDefinition($objDefinition)){
                  PCMW_Abstraction::Get()->AddUserMSG( 'Form Group '.$objDefinitionGroup->strFormName.' created.',3);
                  return TRUE;
                }
              }
              else{
                  PCMW_Abstraction::Get()->AddUserMSG( 'Form Group '.$objDefinitionGroup->strFormName.' NOT created.',1);
              }
            }
            return FALSE;
         break;
         case 'updateform':
                $objDefinitionGroup = new PCMW_FormGroup();
                $objDefinitionGroup->LoadObjectWithArray($arrPOST);
                if($intNewFormId = PCMW_FormDefinitionsCore::Get()->CleanAndInsertDefinitionGroup($objDefinitionGroup,
                  $arrPOST,
                  $this,
                  '')){
                    //make a page for the form
                    if(array_key_exists('makepage',$arrPOST) && trim($arrPOST['makepage']) == 'makepage')
                      PCPluginInstall::Get()->AddSupportingPage($objDefinitionGroup->strFormName,'[makePCform id="'.$intNewFormId.'" makesubmit="0"]',TRUE);
                    PCMW_Abstraction::Get()->AddUserMSG( 'Form updated.',3);
                    return TRUE;
                }
                else{
                    PCMW_Abstraction::Get()->AddUserMSG( 'Could not perfom action. Please try again later.',1);
                }
                return FALSE;
         break;
         case 'newelement':
                $objDefinition = new PCMW_FormDefinition();
                $objDefinition->LoadObjectWithArray($arrPOST);
                if($intNewFormId = PCMW_FormDefinitionsCore::Get()->CleanAndInsertDefinition($objDefinition,
                  $arrPOST,
                  $this,
                  '')){
                  PCMW_Abstraction::Get()->AddUserMSG( 'New definition created.',3);
                  return TRUE;
                }
                else
                    PCMW_Abstraction::Get()->AddUserMSG( 'Some form elements could not be validated. Please check and try again.',1);
                return FALSE;
         break;
         case 'updatelement':
              $objDefinition = new PCMW_FormDefinition();
              $objDefinition->LoadObjectWithArray($arrPOST);
              if($intNewFormId = PCMW_FormDefinitionsCore::Get()->CleanAndInsertDefinition($objDefinition,
                $arrPOST,
                $this,
                '')){
                PCMW_Abstraction::Get()->AddUserMSG( 'Definition updated.',3);
                return TRUE;
              }
              else{
                  PCMW_Abstraction::Get()->AddUserMSG( 'Some form elements could not be validated. Please check and try again. ['.__LINE__.'].',1);
                  $strPOST = var_export($arrPOST,TRUE);
                  PCMW_Logger::Debug('Some form elements could not be validated. ['.$strPOST.'] METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
              }
            return FALSE;
         break;
         default:
            return FALSE;
      }
    }
    else{
     PCMW_Abstraction::Get()->AddUserMSG( 'Could not perfom action. Please try again later.',1);
     return FALSE;
    }
  }


  /**
  * look through the object event handler variables to determine if there are
  * any values in our posted data to make replacements on
  * @param array $arrPOSTData prefilled or posted data to use in the replacement process
  * @return bool
  */
  function MakeFormDataReplacements( $arrPOSTData,&$objInputDefinition){
      foreach($objInputDefinition as $varKey=>$varMember){
        if(!is_string($varMember))
            continue 1;

        if((preg_match_all("/\[[^\]]*\]/", $varMember, $arrMatch)) > 0){
          foreach($arrMatch[0] as $ka=>$va){
          $strBracketsRemoved = str_replace(array("[","]"),'',$va);
            if(array_key_exists($strBracketsRemoved,$arrPOSTData))
              $varMember = str_replace($va,$arrPOSTData[$strBracketsRemoved],$varMember);
          }
        }
        if((preg_match_all("/{(.*?)}/", $varMember, $arrMatch)) > 0){
          foreach($arrMatch[0] as $kb=>$vb){
          $strBracketsRemoved = str_replace(array("{","}"),'',$vb);
            if(property_exists($this, $strBracketsRemoved) && is_string($this->{$strBracketsRemoved}))
              $varMember = str_replace($vb,$this->{$strBracketsRemoved},$varMember);
          }
        }
        if((preg_match_all('#\((.*?)\)#', $varMember, $arrMatch)) > 0){
          foreach($arrMatch[0] as $kc=>$vc){
          $strBracketsRemoved = str_replace(array("(",")"),'',$vc);
            if(defined($strBracketsRemoved))
              $varMember = str_replace($vc,constant($strBracketsRemoved),$varMember);
          }
        }
        $objInputDefinition[$varKey] = $varMember;
      }
      return TRUE;
  }



  /**
  * given a defintiion and a parent object create the input
  * @param array $arrFormDefinition
  * @param array $arrIterativeData data which we can apply to the input element class object as member variables
  * @return bool
  */
  function MakeDefinedElement($arrFormDefinition,$arrIterativeData=NULL){
    $objInputDefinition = PCMW_FormDefinition::Get()->LoadObjectWithArray($arrFormDefinition);
    $objInputElement = new PCMW_InputElement();
    $objInputElement->arrMiscValues = $this->arrMiscValues;
    if(in_array($objInputDefinition->strDefinitionName,$objInputElement->arrMiscValues['disabledelements']))
      $objInputElement->boolViewOnly = TRUE;
    //turn the title off to allow graceful cell stacking
    $objInputElement->arrTitleAttributes['notitle']=1;
    if(is_array($arrIterativeData) && sizeof($arrIterativeData) > 0){
      foreach($arrIterativeData as $ka=>$va){
        //put these into the member scope so we can extract literal data
        if(property_exists ($objInputElement,$ka))
            $objInputElement->{$ka} = $va;
      }
    }
    return $objInputElement->ProcessFormDefinition($objInputDefinition, $arrIterativeData);
  }

  #ENDREGION


  #REGION DATABASECALLS
  //All functions which will interact with PCMW_Database.php should go in here

    /**
    * given a form ID copy it and all of it's subordinate elements
    * @param array $arrPOSTData should contain a form id
    * @return string
    */
    function CopyForm($arrPOSTData){
      $strResults = '';
      //get the form
      if(array_key_exists('formid',$arrPOSTData) && $arrPOSTData['formid'] > 0){
        $arrPOST = $this->GetFormDefinitions($arrPOSTData['formid']);
        $objFormGroup = new PCMW_FormGroup();
        //load our first one into an object
        $objFormGroup->LoadObjectWithArray($arrPOST[0]);
        $objFormGroup->strFormName = $objFormGroup->strFormName.'(copy)';
      }
      else if(array_key_exists('copyformid',$arrPOSTData) && $arrPOSTData['copyformid'] > 0){
        if(array_key_exists('admingroupid',$arrPOSTData) && trim($arrPOSTData['admingroupid']) != '')
            $intAdminGroupId = $arrPOSTData['admingroupid'];
        else
            $intAdminGroupId = $_SESSION['CURRENTUSER']['pcgroup']['admingroupid'];
            $arrPOST = $this->GetFormDefinitions($arrPOSTData['copyformid']);
            $objFormGroup = new PCMW_FormGroup();
            //load our first one into an object
            $objFormGroup->LoadObjectWithArray($arrPOST[0]);
            $objFormGroup->intAdminGroup = $intAdminGroupId;
            if(trim($arrPOSTData['formname']) == '')
                $objFormGroup->strFormName = $objFormGroup->strFormName.'(copy)';
            else
                $objFormGroup->strFormName = $arrPOSTData['formname'];
            if(trim($arrPOSTData['formalias']) == '')
                $objFormGroup->strFormName = $objFormGroup->strFormName.'_'.rand();
            else
                $objFormGroup->strGroupAlias = $arrPOSTData['formalias'];
      }
      else
        return FALSE;
      //make the new form now
      if(($intNewFormId = PCMW_Database::Get()->InsertNewFormData($objFormGroup))){
         foreach($arrPOST as $arrFormDefinition){
           $arrFormDefinition['formgroup'] = $intNewFormId;
           $objNewDefinition = PCMW_FormDefinition::Get()->LoadObjectWithArray($arrFormDefinition);
           PCMW_Database::Get()->InsertNewDefinition($objNewDefinition);
         }
          //make a page for the form
          if(array_key_exists('makepage',$arrPOST) && trim($arrPOST['makepage']) == 'makepage')
            PCPluginInstall::Get()->AddSupportingPage($objDefinitionGroup->strFormName,'[makePCform id="'.$intNewFormId.'" makesubmit="0"]',TRUE);
         return TRUE;
      }
      else
          return FALSE;
      return FALSE;
  }

  /**
  * get the available formids and names for end user listing
  * @return array()
  */
  public static function GetEndUserForms(){
    //get the available forms
    $arrAllForms = PCMW_Database::Get()->GetFormData(0,300);
    $arrReturnForms = array(0=>'Select');
    return PCMW_Utility::Get()->MakeNameValueArray('formid','formname',$arrAllForms,$arrReturnForms);
  }


  /**
  * given a form ID, get the definitions and associated data
  * @param int $intFormId unique for ID
  * @param bool $boolCountOnly return the number of definitions only
  * @return array || int || FALSE
  */
  function GetFormDefinitions($intFormId,$boolCountOnly=FALSE){
    return PCMW_InputElement::Get()->GetFormDefinitions($intFormId,$boolCountOnly);
  }

  /**
  * given a definition ID, get the data and return a simple array
  * @param int $intDefinitionId unique ID of a single definition
  * @return array || object
  */
  function GetDefinitionData($intDefinitionId){
    $arrDefinitionData = array();
    if($arrDefinitionData = PCMW_Database::Get()->GetDefinitionData($intDefinitionId)){
      return $arrDefinitionData[0];
    }
    return $arrDefinitionData;
  }

  /**
  * given a form ID, get the form data for update or other purposes
  * @param int $intFormId unique form ID
  * @return array
  */
  function GetFormData($intFormId=0){
    $arrFormData = array();
    if($intFormId < 1 && PCMW_Abstraction::Get()->CheckPrivileges()){
    //admins can get all groups
      if($arrFormData = PCMW_Database::Get()->GetFormGroupAdminData()){
        return $arrFormData;
      }
    }
    else{
      if($arrFormData = PCMW_Database::Get()->GetFormData($intFormId)){
        return $arrFormData[0];
      }
    }
    return $arrFormData;
  }

  /**
  * given a form ID, get the form data for update or other purposes
  * @param string $strGroupAlias name of the form group to aquire
  * @param int $intAdminGroup admin group the user belongs to
  * @param bool $boolIdOnly get he form ID only
  * @return array
  */
  function GetFormDataByAlias($strGroupAlias,$intAdminGroupId,$boolIdOnly=FALSE){
    return PCMW_InputElement::Get()->GetFormDataByAlias($strGroupAlias,$intAdminGroupId,$boolIdOnly);
  }

  /**
  * given an alias and potentially an admin group, get the defintiion data
  * @param $strFormAlias
  * @param $intAdminGroup
  * @return array || FALSE
  */
  function GetDefinitionByAlias($strFormAlias,$intAdminGroup,$boolAsObjects=FALSE){
   if($arrFormData = PCMW_Database::Get()->GetFormDefinitionsByAlias($strFormAlias,$intAdminGroup)){
     if($boolAsObjects){
      $arrDefinitionObjects = array();
      foreach($arrFormData as $arrDefinition){
        $objDefinition = new PCMW_FormDefinition();
        $arrDefinitionObjects[$arrDefinition['definitionid']] = $objDefinition->LoadObjectWithArray($arrDefinition);
      }
      return $arrDefinitionObjects;
     }
     return $arrFormData;
   }
   return FALSE;
  }


  /**
  * Load a form group based on the ID
  * @param array $arrValues custom form values to be used for creating a given form
  * @return string HTML
  */
  function LoadFormGroupByAlias($arrValues,$intFormId=0){
    $objElementControls = new PCMW_DynamicFormInputs();
    $this->LoadInputsWithExternalData($objElementControls);
    $intAdminGroupId = 0;
    if(array_key_exists('admingroupid',$arrValues) && ((int)$arrValues['admingroupid'] > 0 || (int)$_SESSION['CURRENTUSER']['pcgroup']['admingroupid'] < 1))
        $intAdminGroupId = $arrValues['admingroupid'];
    else
        $intAdminGroupId = PCMW_SUSPENDED;
    if((int)$intFormId > 0)
      $intFormGroupId  = $intFormId;
    else
    (int)$intFormGroupId = $this->GetFormDataByAlias(@$arrValues['formalias'],$intAdminGroupId,TRUE);
    if($intFormGroupId){
      if(array_key_exists('bootstrapform', $arrValues) && $arrValues['bootstrapform'] = TRUE ){
        $objElementControls->boolBootstrapForm = TRUE;
      }
      $objElementControls->boolAllowNewElements = FALSE;
      $objElementControls->boolIsForm = (array_key_exists('makesubmit',$arrValues) && trim(@$arrValues['isform']) != '') ? @$arrValues['isform']:1;
      $objElementControls->intFormGroupId = $intFormGroupId;
      //Not sure what happened below, but saving a copy in cas eit had a reason
      $objElementControls->arrMiscValues = $this->arrMiscValues;
      $objElementControls->strBaseCSS = (array_key_exists('formParentClass',$arrValues)) ? $arrValues['formParentClass']:'';
      $objElementControls->strFormClass = (array_key_exists('formClass',$arrValues)) ? $arrValues['formClass']:'container col-md-12 ';
      $arrValues['formgroup'] = $intFormGroupId;
      $objElementControls->boolAjaxSubmitForm = (array_key_exists('ajaxsubmit',$arrValues) && trim($arrValues['ajaxsubmit']) != '')? $arrValues['ajaxsubmit']:0;
      $objElementControls->boolMakeSubmitButton = (array_key_exists('makesubmit',$arrValues) && trim($arrValues['makesubmit']) != '')? $arrValues['makesubmit']:1;
      $objElementControls->boolUseFieldSet = (array_key_exists('usefieldset',$arrValues) && trim($arrValues['usefieldset']) != '')? $arrValues['usefieldset']:0;
      $objElementControls->strFormName = (array_key_exists('formname',$arrValues)) ? $arrValues['formname']:'';
      $objElementControls->strFormAction = (array_key_exists('formaction',$arrValues)) ? $arrValues['formaction']:'';
      $objElementControls->strCustomOnclick = (array_key_exists('customonclick',$arrValues) && trim($arrValues['customonclick']) != '')? $arrValues['customonclick']:'';
      $objElementControls->arrElementAttributes = (array_key_exists('ElementAttributes',$arrValues)) ? $arrValues['ElementAttributes']:$objElementControls->arrElementAttributes;
      $objElementControls->strSubmitButtonClass = (array_key_exists('SubmitButtonClass',$arrValues)) ? $arrValues['SubmitButtonClass']:$objElementControls->strSubmitButtonClass;
      $strForm = $objElementControls->InitiateFormControls($arrValues);
      return $strForm;
    }
    else{
      $strValues = var_export($arrValues,TRUE);
      $this->PCMW_LoadDebugLog('cannot create form for values ['.$strValues.']   LINE '.__LINE__."\r\n",TRUE,__METHOD__);
      return FALSE;
    }
  }


  /**
  * Get the groups available for assignment for various reasons
  * @param bool $boolSingleArray get a limited name value pair array
  * @return array
  */
  public static function GetAdminGroups($boolSingleArray=TRUE){
    $arrGroupData = array();
    //if this is not an admin, they can see ONLY their group
    $intClientId = 0;
    $intIsAdmin = (int)PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,PCMW_SUPERUSERS,FALSE,FALSE);
    if(($intIsAdmin < 1))
        $intClientId = $_SESSION['CURRENTUSER']['pcgroup']['userid'];
    if($arrGroupData = PCMW_Database::Get()->GetAdminGroups($intClientId,0,TRUE)){
      if($boolSingleArray){
        $arrGroups = PCMW_Utility::Get()->MakeNameValueArray('admingroupid','groupname',$arrGroupData);
        if($intIsAdmin)
            array_unshift($arrGroups,'All');
        return $arrGroups;
      }
      else
        return $arrGroupData;
    }
    return $arrGroupData;
  }

  /**
  * find custom form entries and execute their actions
  * @return bool
  */
  function HandleCustomFormActions(){
   //sanitize our POST
   $arrPOST = filter_var_array($_POST,FILTER_SANITIZE_STRING);
   if(!PCMW_Abstraction::Get()->ValidateNonce($arrPOST))
    return FALSE;
   //get the form
   if(array_key_exists('formid',$arrPOST) && (int)$arrPOST['formid'] > 0){
     $arrFormData = PCMW_FormManager::Get()->GetFormData($arrPOST['formid']);
     //get our custom form actions
     switch($arrFormData['formresponse']){
         case 'loguserin':
               return PCMW_Login::Get()->CheckForLogin($arrPOST);
         break;
         case 'emailform':
               return PCMW_FormManager::Get()->PackageFormForEmail($arrPOST, $arrFormData['formname']);
         break;
         case 'storeform':
               return PCMW_FormManager::Get()->PackageFormForInsert($arrPOST,$arrFormData);
         break;
         case 'registeruser':
               return PCMW_Register::Get()->CheckForRegistration($arrPOST);
         break;
         default:
            return FALSE;
     }
   }
   else{
    //no form action
    return FALSE;
   }
  }

  /**
  * package form data and send it to the admin
  * @param $arrFormData
  * @param $strFormName
  * @return bool
  */
  function PackageFormForEmail($arrFormData, $strFormName){
    //remove our form driver data
    unset($arrFormData['dir']);
    unset($arrFormData['formid']);
    $strFormData = var_export($arrFormData,TRUE);
    //looks like we have everything, send the message
    if(PCMW_Abstraction::Get()->Send_Mail(PCMW_ADMIN , 'New '.$strFormName , '<pre>'.$strFormData.'</pre>')){
      PCMW_Abstraction::Get()->AddUserMSG( 'Thank you! your request was successfully sent. We will email you if we have any questions.',3);
     return TRUE;
    }
    else{
        PCMW_Abstraction::Get()->AddUserMSG( 'Your request was NOT sent. Please try again, or contact <a href="mailto:'.PCMW_SUPPORT.'">'.PCMW_SUPPORT.'</a> or use the helpdesk option.',1);
      $strFormResults = var_export($arrFormData,TRUE);
      PCMW_Logger::Debug('Method failed storage! ['.$strFormResults.'] ['.$arrFormDescription['formname'].'] METHOD ['.$strMethod.']  this method ['.__METHOD__.'] LINE ['.__LINE__.']',1);
     return FALSE;
    }
  }

  /**
  * package form data and store it in the form results table
  * @param $arrFormData
  * @param $arrFormDescription
  * @return bool
  */
  function PackageFormForInsert($arrFormData,$arrFormDescription){
    $strFormData = json_encode($arrFormDescription,TRUE);
    if(!PCMW_Database::Get()->InsertFormResults($arrFormDescription['formid'],
                                               $strFormData,
                                               @$_SESSION['CURRENTUSER']['admingroupid'],
                                               get_current_user_id())){
      PCMW_Abstraction::Get()->AddUserMSG( 'Your request was NOT sent. Please try again later',1);
      $strFormResults = var_export($arrFormData,TRUE);
      PCMW_Logger::Debug('Method failed storage! ['.$strFormResults.'] ['.$arrFormDescription['formname'].'] METHOD ['.$strMethod.']  this method ['.__METHOD__.'] LINE ['.__LINE__.']',1);
      return FALSE;
    }
    PCMW_Abstraction::Get()->AddUserMSG( 'Thank you! your request was successfully sent. We will email you if we have any questions.',3);
    return TRUE;
  }

  /**
  * given a form alias and form submitted data, remove the input title valued
  * entries to avoid storing titles as values
  * @param $arrPOSTData form submitted data
  * @param $strFormAlias
  * @return bool
  */
  function RemoveFormTitledValues(&$arrPOSTData,$strFormAlias){
    if($arrDefinitionData = PCMW_Database::Get()->GetFormDefinitionsByAlias($strFormAlias,$arrPOSTData['admingroupid'])){
      foreach($arrDefinitionData as $arrDefinition){
        //replace any title and form macro data
        $this->MakeFormDataReplacements( $arrPOSTData,$arrDefinition);
        if(trim($arrPOSTData[$arrDefinition['elementname']]) == trim($arrDefinition['elementtitle'])){
          unset($arrPOSTData[$arrDefinition['elementname']]);
        }
      }
    }
    return TRUE;
  }

  /**
  * get the form group components and verify them against what we have in hand
  * this will most likely be for elements of various flavors or a form group
  * @param int $intBaseFormId the initial form we are validating against
  * @param array $arrPOSTData posted data from the form
  * @param $objTableObject - SQL table object to validate against
  * @param $strFormAlias - form alias to gather definitions with
  * @param $arrDirectForm - remove empty POST values
  * @param $strInsertType - insert type to define our validation
  */
  function ValidateDefinitionRequires($intBaseFormId=0,&$arrPOSTData,$objTableObject=null,$strFormAlias='',$arrDirectForm = array(),$strInsertType='insert'){
    $boolFormValid = TRUE;
    $boolNoForm = FALSE;
    $arrReturnData = array();
    //double pointers are not handled well in php
    $arrTempPOST = $arrPOSTData;
    $arrTitles = array();//pass these to the display processor
    //sanitize our form data
    PCMW_Utility::Get()->CleanTableData($arrTempPOST);
    //update our pointer
    $arrPOSTData = $arrTempPOST;
    //let's check for formid or group
    if(@(int)$arrPOSTData['formid'] < 1 && @(int)$arrPOSTData['formgroup'] > 0)
        $arrPOSTData['formid'] = $arrPOSTData['formgroup'];
    //create our defaults
    $this->arrMiscValues['failedelements'] = array();
    $arrDefinitionData = array();
    $intAdminGroupId = 0;
    if(is_array($arrPOSTData) && array_key_exists('admingroupid',$arrPOSTData) && (int)$arrPOSTData['admingroupid'] > 0)
        $intAdminGroupId = @(int)$arrPOSTData['admingroupid'];
    else if(array_key_exists('CURRENTUSER',$_SESSION))
        $intAdminGroupId = @(int)$_SESSION['CURRENTUSER']['pcgroup']['admingroupid'];
    else
      $intAdminGroupId = PCMW_SUSPENDED;
    //do we have a form id in our POST data? ( most common use with LoadGroupByAlias )
    if(is_array($arrPOSTData) && array_key_exists('formid',$arrPOSTData) && (int)$arrPOSTData['formid'] > 0 && trim($strFormAlias) == '')
      $arrDefinitionData = PCMW_FormDefinitionsCore::Get()->GetDefinitionsById($arrPOSTData['formid'],TRUE);
    //Use a directly passed form ID to get the group
    else if((int)$intBaseFormId > 0)
      $arrDefinitionData = PCMW_FormDefinitionsCore::Get()->GetDefinitionsById($intBaseFormId,TRUE);
    //Use a form alias passed in
    else if((string)$strFormAlias != '' && is_null($this->strFormAliasOverRide))
      $arrDefinitionData = PCMW_FormDefinitionsCore::Get()->GetDefinitionsByAlias($strFormAlias,$intAdminGroupId,TRUE);
    //Use a member variable to set the form ID
    else if(!is_null($this->strFormAliasOverRide))
      $arrDefinitionData = PCMW_FormDefinitionsCore::Get()->GetDefinitionsByAlias($this->strFormAliasOverRide,$intAdminGroupd,TRUE);
    //Pass the form data in directly. Use primarily in ala cart form processing
    else if(is_array($arrDirectForm) && sizeof($arrDirectForm) > 0){
      $arrDefinitionData = $arrDirectForm;
    }
    //we have no form data, and an object is assumed
    else{
      $boolNoForm = TRUE;
    }
    //definitions are there, right?
    if(is_array($arrDefinitionData) && sizeof($arrDefinitionData) > 0){
      //check the attributes for subforms by breaking the definition attributes
      @$this->AddSubFormDefinitions($arrDefinitionData,$intAdminGroup);
      foreach($arrDefinitionData as $arrDefinition){
        $arrDefinition['elementtitle'] = str_replace('notitle:','',$arrDefinition['elementtitle']);
        if(strtolower($arrDefinition['elementtype']) == 'span' ||
           strtolower($arrDefinition['elementtype']) == 'div' ||
           strtolower($arrDefinition['elementtype']) == 'input:button')
          continue 1;
        $this->MakeFormDataReplacements($arrPOSTData,$arrDefinition);
        //set the title for error message replacements
        $arrTitles[$arrDefinition['elementname']] = $arrDefinition['elementtitle'];
        //if we need to validate this, check here
          if((int)$arrDefinition['elementvalidationid'] > 0 && !array_key_exists($arrDefinition['elementname'],$this->arrIgnoreFields)){
            if(array_key_exists($arrDefinition['elementname'],$arrPOSTData) &&
              (trim($arrPOSTData[$arrDefinition['elementname']]) == '' ||
               $arrPOSTData[$arrDefinition['elementname']] == $arrDefinition['elementtitle']) &&
               trim($arrPOSTData[$arrDefinition['elementname']]) !== 0){
              PCMW_Abstraction::Get()->AddUserMSG( 'Field [ '.$arrDefinition['elementtitle'].' ] ['.$arrDefinition['elementtype'].'] ['.$arrDefinition['elementvalidationid'].'] ['.$arrDefinition['definitionid'].'] is not valid. Please check the value and try again.',1);
              $this->arrMiscValues['failedelements'][] = $arrDefinition['elementname'];
              $boolFormValid = FALSE;
            }
          }
          else{ //this doesn't need validation, but we don't want the element title as a value
            if(@$arrPOSTData[$arrDefinition['elementname']] == $arrDefinition['elementtitle']){
               @$arrPOSTData[$arrDefinition['elementname']] = '';
            }
            $this->arrIgnoreFields[$arrDefinition['elementname']] = TRUE;
          }
        //fill this with the form data
        @$arrReturnData[$arrDefinition['elementname']] = $arrPOSTData[$arrDefinition['elementname']];
      }
    }
    else if($boolNoForm){
        $boolFormValid = TRUE;
    }
    else{
        $boolFormValid = FALSE;
    }
    if(!is_null($objTableObject)){
      //let's remove anything not in our form. If it's not there, we don;t need it for an insert or update
      foreach($objTableObject->LoadArrayWithObject() as $strKey=>$strValue){
        if(!array_key_exists($strKey,$arrTitles))
            $this->arrIgnoreFields[$strKey] = true;
      }
      if(!$objTableObject->Validate($strInsertType,$this->arrIgnoreFields)){
        $this->StoreErrors($objTableObject->arrValidationErrors,$arrTitles);
        $boolFormValid = FALSE;
      }
    }
    //replace this now, though a copy needs to be made on the caller side to prevent loss
    $arrPOSTData = $arrReturnData;
    $this->strFormAliasOverRide = NULL;
    return $boolFormValid;
  }

  /**
  * Given an array of definitions see if any of them have sub forms and add them
  * @param $arrDefinition
  * @return bool
  */
  function AddSubFormDefinitions(&$arrDefinitions,$intAdminGroup){
    foreach($arrDefinitions as $arrDefinition){
      $arrAttributes = PCMW_Utility::Get()->DecomposeCurlString($arrDefinition['elementattributes']);
      if(array_key_exists('subform',$arrAttributes)){
        $arrNewDefinitionData = PCMW_FormDefinitionsCore::Get()->GetDefinitionsByAlias($arrAttributes['subform'],$intAdminGroup,TRUE);
        foreach($arrNewDefinitionData as $intDefinitionId=>$arrNewDefinition)
          $arrDefinitions[] = $arrNewDefinition;
      }
    }
    return TRUE;
  }

  function StoreErrors($arrErrors,$arrTitles){
    foreach($arrErrors as $strName=>$arrError){
      if(!in_array($strName,$this->arrMiscValues['failedelements']))
            $this->arrMiscValues['failedelements'][] = $strName;
      if(array_key_exists('expected_length',$arrError)){
        PCMW_Abstraction::Get()->AddUserMSG( $arrTitles[$strName].' was expecting a value between '.$arrError['expected_length'].' characters and got '.$arrError['length'],1);
      }
      if(array_key_exists('expected_type',$arrError)){
        $strValue = (is_null($arrError['type']) || strtolower($arrError['type']) == 'null')? 'nothing': $arrError['type'];
        PCMW_Abstraction::Get()->AddUserMSG( $arrTitles[$strName].' ['.$strName.'] expected a ['.$arrError['expected_type'].'] but got '.$strValue,1);
      }
      if(array_key_exists('expected_value',$arrError)){
        PCMW_Abstraction::Get()->AddUserMSG( $arrTitles[$strName].' expected ['.$arrError['expected_value'].'] but got '.$arrError['value'],1);
      }
    }
    return TRUE;
  }
  #ENDREGION

  #REGION DEBUGGINGMETHODS

  /*
  @brief store and display debug information
  @param $strMessage,$boolLogNow
  @param boolean
  */
  function PCMW_LoadDebugLog($strMessage='',$boolLogNow=FALSE,$strMethod='',$boolShowBackTrace=FALSE){
  //$this->PCMW_LoadDebugLog('VALUE ['.$arrPOST .']  LINE '.__LINE__."\r\n",FALSE,__METHOD__);
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