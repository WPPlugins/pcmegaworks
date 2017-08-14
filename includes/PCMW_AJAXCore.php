<?php
/**************************************************************************
* @CLASS PCMW_AJAXCore
* @brief Handle all Ajax related functions. This could get rather large
* due to the unforeseeable
* @REQUIRES:
*  -PCMW_Database.php
*
**************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
class PCMW_AJAXCore extends PCMW_BaseClass{

   //debugging
   var $boolDebugOn = TRUE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_AJAXCore();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
  }

  #REGION UTILITY METHODS

  /*
  @brief Get the methods available for debugging
  *keep this at the top of the class for easy access and update
  @return array()
  */
  function GetDebugMethods(){
    $arrDebugMethods = array();
    //$arrDebugMethods['PCMW_AJAXCore::UpdateFormData'] = 1;
    $arrDebugMethods['PCMW_AJAXCore::PerformAnoymousAction'] = 1;
    $arrDebugMethods['PCMW_AJAXCore::LoadFormGroupByAlias'] = 1;
    return $arrDebugMethods;
  }

  #ENDREGION

  #REGION DATACALCULATION

   /**
  * before gicing a form back, wrap it in a basic CSS wrapper
  * @param $strHTMLCollection collection of form or loose HTML elements
  * @param $strTitle
  * @param $strHeading
  * @return $strHTML
  */
  function MakePopUpFormContainer($strHTMLCollection,$strTitle='',$strHeading=''){
    $objElement = new PCMW_Element();
	// main div
    $objPrimaryTable = $objElement->LoadHTMLTemplate('<div class="popup" data-popup="popup-1" id="popupcontent"></div>');
    $objPopupInner = $objElement->AddChildNode($objPrimaryTable,'','div',array('class'=>'popup-inner'));
    if($strTitle != '')
      //add the heading
      $objElement->AddChildNode($objPopupInner,$strTitle,'h1',array('class'=>'page-header pcmt_h1'));
    if($strHeading != '')
      //add the lead
    $objElement->AddChildNode($objPopupInner,$strHeading,'p',array('class'=>'lead pcmt_p'));
    //insert the main data
    $objHTMLContainer = $objElement->AddChildNode($objPopupInner,'','div',array('class'=>'scrollbox'));
    $objElement->AddChildNode($objHTMLContainer,$strHTMLCollection,'div',array());
    //make the close button
    $objCloseButton = $objElement->AddChildNode($objPopupInner,'','p',array());
    $objElement->AddChildNode($objCloseButton,'Close','a',array("data-popup-close"=>"popup-1","href"=>"#",'onclick'=>"CloseDataPopUp('popup-1');"));
    $objElement->AddChildNode($objPopupInner,'X','a',array( "class"=>"popup-close","data-popup-close"=>"popup-1","href"=>"#",'onclick'=>"CloseDataPopUp('popup-1');"));
    return $objElement->CloseDocument();
  }


  /**
  * Load a form group based on the ID
  * @param int $intFormId unique form group ID
  * @return string HTML
  */
  function LoadFormGroup($arrValues){
    if(trim($arrValues['formalias']) != ''){
      //get the form
      $intFormId = PCMW_Database::Get()->GetFormIdByAlias($arrValues['formalias'],1);
    }
    else if((int)$arrValues['formid'] > 0)
      $intFormId = (int)$arrValues['formid'];
    else
        return '0aleCannot get form at this time. Please try again later.';

    $arrPOST = array();
    $objElementControls = new PCMW_DynamicFormInputs();
    //collection id is to add associative data to the form
    if((int)$arrValues['collectionid'] > 0){
      //let's get the collection and data
    }
    $objElementControls->boolAllowNewElements = FALSE;
    $objElementControls->strFormClass = $arrValues['formcss'];
    $objElementControls->boolIsForm = $arrValues['isform'];
    $objElementControls->intFormGroupId = $intFormId;
    $arrPOST['formgroup'] = $intFormId;
    $objElementControls->boolMakeSubmitButton = $arrValues['makesubmit'];
    $objElementControls->boolUseFieldSet = $arrValues['usefieldset'];
    return '1frm'. $this->MakePopUpFormContainer($objElementControls->InitiateFormControls($arrPOST));
  }


  /**
  * Load a form group based on the ID
  * @param int $intFormId unique form group ID
  * @return string HTML
  */
  function LoadFormGroupByAlias($arrValues){
    $arrValues['dir'] = '';
    $arrValues['admingroupid'] = $_SESSION['CURRENTUSER']['pcgroup']['admingroup'];
    if(trim((string)$arrValues['formalias']) != '' && ($strForm = PCMW_FormManager::Get()->LoadFormGroupByAlias($arrValues))){
      return '1frm'.$this->MakePopUpFormContainer($strForm,$arrValues['title'],$arrValues['heading']);
    }
    else{
      PCMW_Logger::Debug('METHOD ['.__METHOD__.'] Failed ['.__LINE__.']',1);
      return '0aleCannot build form ['.$arrValues['formalias'].'] ['.$_SESSION['CURRENTUSER']['pcgroup']['admingroup'].'] at this time.';
    }
  }

  /**
  * given a form alias and a data ID load and return the completed form for modification
  * @param $arrFormData data to grab the correct form and associated data
  * @return string (HTML)
  */
  function GetFormFromAlias($arrFormData){
    if(array_key_exists('formalias',$arrFormData) && trim((string)$arrFormData['formalias']) != ''){
      //if we have an actual ID we need to preserve it
      $intDataId = ($arrFormData['dataid'] != "new" && (int)$arrFormData['dataid'] > 0)? $arrFormData['dataid']: 0 ;
      $strDataGroupName = ($arrFormData['datagroup'] != "new" && (int)$arrFormData['datagroup'] > 0)? $arrFormData['datagroup']: 0 ;
      $arrFormData['formname'] = $arrFormData['formalias'];
      //load our users admin group
      $arrFormData['admingroupid'] = $_SESSION['CURRENTUSER']['pcgroup']['admingroupid'];
      if((int)$arrFormData['admingroupid'] < 1)
        $arrFormData['admingroupid'] = PCMW_SUSPENDED;
      //reset this to avoid incorrect handling
      $arrFormData['dir'] = '';
      $arrDataBaseFormData = array();
      switch($arrFormData['formalias']){
         case '404redirect':
           $arrDataBaseFormData = PCMW_404Redirect::Get()->Get404Redirects($intDataId);
           $arrDataBaseFormData = $arrDataBaseFormData[0];
         break;
         case 'videoaccess':
           $arrDataBaseFormData = PCMW_VideoAccess::Get()->GetVideo($intDataId,$arrFormData['admingroupid']);
         break;
        /*
         case '':

         break;*/
      }
      //load any data we've aquired with what we were passed (overwrite is on by default)
      $arrFormData = PCMW_Utility::Get()->MergeArrays($arrFormData,$arrDataBaseFormData);
      //make our form from the data and it's alias
      if(($strForm = PCMW_FormManager::Get()->LoadFormGroupByAlias($arrFormData)) != ""){
        return '1frm'.$this->MakePopUpFormContainer($strForm,$arrDataBaseFormData['popuptitle'],$arrDataBaseFormData['heading']);
      }
      else{
       $strPOST = var_export($arrFormData,TRUE);
       PCMW_Logger::Debug('METHOD ['.__METHOD__.'] Failed ['.__LINE__.'] $strPOST  ['.$strPOST.']',1);
       return '0alePlease Insert Group for Updating.';
      }
    }
    else{
     $strPOST = var_export($arrFormData,TRUE);
     PCMW_Logger::Debug('METHOD ['.__METHOD__.'] Failed ['.__LINE__.'] $strPOST ['.$strPOST.']',1);
     PCMW_Abstraction::Get()->AddUserMSG( 'Could not perform action. Please try again later('.__LINE__.').',1);
     return FALSE;
    }
  }

  /**
  * given an anonymous action perform it
  * @param $arrFormData
  * @return mixed
  */
  function PerformAnoymousAction($arrFormData){
      $strAjaxReturn = '1frm';
      $objFormManager = new PCMW_FormManager();
      $strForm = '';
   if(array_key_exists('action',$arrFormData) && trim((string)$arrFormData['action']) != ''){
      //if we have an actual ID we need to preserve it
      $intDataId = ($arrFormData['dataid'] != "new" && (int)$arrFormData['dataid'] > 0)? $arrFormData['dataid']: 0 ;
      $arrDataBaseFormData = array();
      switch($arrFormData['action']){
         //perform an action or return false
         case 'newhelpdesk':
           $arrTicketData = array('ticketdata'=>$arrFormData);
           $arrAdminData = PCMW_Abstraction::Get()->CheckUserStatus();
           $arrTicketData['group'] = $arrAdminData['pcgroup'];
           $arrTicketData['config'] = $_SESSION['pcconfig'];
           $arrTicketData['name'] = $arrAdminData['WPUSEROBJECT']->display_name;
           $arrTicketData['email'] = $arrAdminData['WPUSEROBJECT']->user_email;
           PCMW_TaskServerAPI::Get()->strCurlAddress = PCMW_HOSTADDRESS;
           $arrResults = PCMW_TaskServerAPI::Get()->MakeServerRequest('newticket',$arrTicketData);
           if((int)$arrResults['ticketstatus'] > 0){
            PCMW_Abstraction::Get()->AddUserMSG(urldecode ($arrResults['usermessage']).'<br /><br />Your entry was saved correctly. Your ticket Id is [#'.$arrResults['taskid'].']<br /><br /> '.urldecode ($arrResults['emailcontent']),3);
            if($arrFormData['emailresponse'] == 'true'){
                PCMW_Abstraction::Get()->Send_Mail($arrFormData['handleremail'] , 'Ticket #['.$arrResults['taskid'].']' , urldecode ($arrResults['emailcontent']) , PCMW_SUPPORT);
            }
            return '1gfm'.$this->MakePopUpFormContainer(PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE));

           }
           else{
            $strPOST = var_export($arrResults,TRUE);
            PCMW_Logger::Debug('POST->['.$strPOST.'] $arrResults ['.$arrResults.'] LINE ['.__LINE__.']',1);
            PCMW_Abstraction::Get()->AddUserMSG(urldecode ($arrResults['usermessage']).'<br /> ['.$strPOST.'] <br />Ticket not added.',1);
            return '1frm'.$this->MakePopUpFormContainer(PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE));
           }
         break;
         case 'editmap':
             $arrMapData = PCMW_VendorCore::Get()->GetSingleVendor($arrFormData['dataid']);
             $arrMapData['formalias'] = 'newmap';
             $arrMapData['isform'] = 1;
             $arrMapData['makesubmit'] = 1;
             $arrMapData['admingroupid'] = $_SESSION['CURRENTUSER']['pcgroup']['admingroupid'];
             return '1frm'.$this->MakePopUpFormContainer(PCMW_FormManager::Get()->LoadFormGroupByAlias($arrMapData));
         break;
         case 'editmapgroup':
             $arrMapData = PCMW_VendorCore::Get()->GetSingleMapGroup($arrFormData['dataid']);
             $arrMapData['formalias'] = 'mapgroups';
             $arrMapData['isform'] = 1;
             $arrMapData['makesubmit'] = 1;
             $arrMapData['admingroupid'] = $_SESSION['CURRENTUSER']['pcgroup']['admingroupid'];
             return '1frm'.$this->MakePopUpFormContainer(PCMW_FormManager::Get()->LoadFormGroupByAlias($arrMapData));
         break;
         case 'addremovemap':
            if((int)$arrFormData['dataid'] < 1)
                return FALSE;
            if($arrFormData['datacollection'] == 'true')
              PCMW_Database::Get()->AddVendorToGroup($arrFormData['dataid'],$arrFormData['groupid']);
            else
              PCMW_Database::Get()->RemoveVendorFromGroup($arrFormData['dataid'],$arrFormData['groupid']);
            return TRUE;
         break;
         case 'editmaplinks':
             $arrFormData['groupid'] = $arrFormData['dataid'];
             return '1frm'.$this->MakePopUpFormContainer(PCMW_VendorCore::Get()->MakeVendorsSelectionTable($arrFormData));
         break;
         case 'updateuseradmin':
              $objAdminUser = PCMW_AdminUserCore::Get()->GetAdminUserId(0,$arrFormData['dataid'],0,0,TRUE);
              if((int)$arrFormData['datacollection'] < 1 || (int)$arrFormData['group'] < 1)
                return '0aleForm data is not valid, and we cannot update';
              $objAdminUser->intAdminGroupId = (int)$arrFormData['datacollection'];
              $objAdminUser->intStatus = (int)$arrFormData['group'];
              //update our existing user if we can
              if($arrFormData['dataid'] == get_current_user_id()){
                $_SESSION['CURRENTUSER']['pcgroup']['admingroup'] = $arrFormData['datacollection'];
                $_SESSION['CURRENTUSER']['pcgroup']['status'] = $arrFormData['group'];
              }
              $arrResponse = array('rowid'=>'rowid_'.$arrFormData['dataid']);
              //add or remove mail blast flag       
              if(array_key_exists('pcmw_mail_blast_'.$arrFormData['dataid'],$arrFormData) && $arrFormData['pcmw_mail_blast_'.$arrFormData['dataid']] == 'true')
                $intSuccess = add_user_meta( $arrFormData['dataid'], 'pcmw_mail_blast', '1', TRUE );
              else
                $intSuccess = delete_user_meta( $arrFormData['dataid'], 'pcmw_mail_blast' );
              //update this user
              if($intSuccess || PCMW_Database::Get()->UpdateAdminUser($objAdminUser))
                $arrResponse['classname'] = 'background-green';
              else
                $arrResponse['classname'] = 'background-red';
              return '1urd'.PCMW_Utility::Get()->JSONEncode($arrResponse);
         break;
         case 'uninstall':
            return PCPluginInstall::Get()->UninstallPCPlugin();
         break;
         case 'new404':
              PCMW_404Redirect::Get()->Process404RedirectForm($arrFormData);
              return '0ref';
              return '1gfm'.$this->MakePopUpFormContainer(PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE));
         break;
         case 'delete404':
              $arrResponse = array('rowid'=>'rowid_'.$arrFormData['dataid']);
              if(PCMW_404Redirect::Get()->Delete404Redirect($arrFormData['dataid']))
                //$arrResponse['deleterow'] = 'true';
              return '0ref';
              else
                $arrResponse['classname'] = 'background-red';
              return '1urd'.PCMW_Utility::Get()->JSONEncode($arrResponse);
         break;
         case 'getcssparams':
              return $this->MakeSupportedCSSDefaults($arrFormData['dataid'],$arrFormData['datacollection']);
         break;
         /*case '':

         break;*/
         default:
            return FALSE;
      }
      //load any data we've aquired with what we were passed (overwrite is on by default)
      $arrFormData = PCMW_Utility::Get()->MergeArrays($arrFormData,$arrDataBaseFormData);
      //make our form from the data and it's alias
      if(($strForm .= $objFormManager->LoadFormGroupByAlias($arrFormData)) != ""){
        $strForm = PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE).$strForm;
        return $strAjaxReturn.$this->MakePopUpFormContainer($strForm,$arrDataBaseFormData['title'],$arrDataBaseFormData['heading']);
      }
      else{
        $strPOST = var_export($arrFormData,TRUE);
        $this->PCMW_LoadDebugLog('$strPOST ['.$strPOST.']  LINE '.__LINE__."\r\n",TRUE,__METHOD__);
       return '0aleWe\'re sorry, but this form cannot be loaded at this time.';
      }
    }
    else{
      $strPOST = var_export($arrFormData,TRUE);
      $this->PCMW_LoadDebugLog('$strPOST ['.$strPOST.']  LINE '.__LINE__."\r\n",TRUE,__METHOD__);
     PCMW_Abstraction::Get()->AddUserMSG( 'Could not perfom action. Please try again later('.__LINE__.').',1);
     return FALSE;
    }
  }

  /**
  * given a vendor ID, or map group id delete it
  * @param $arrValues
  * @return str (alert)
  */
  function DeleteVendorOrGroup($arrValues){
    //delete a vendor
    if((int)$arrValues['vendorid'] > 0){
       if(PCMW_Database::Get()->RemoveVendor($arrValues['vendorid']))
         return '0aluRecord removed.';
       else
         return '0aluCouldn\'t remove record';
    }
    //delete a map group
    if((int)$arrValues['mapgroupid'] > 0){
    //remove and links to the group
      if(PCMW_Database::Get()->RemoveMapGroup($arrValues['mapgroupid'])){
        if(PCMW_Database::Get()->RemoveAllGroupLinks($arrValues['mapgroupid']))
         return '0aluGroup removed.';
        else
         return '0aluGroup removed, but links may still exist.';

      }
      else return '0aluCould not remove group.';
    }
    return '0aluNothing happened. Please let us know if this continues.';
  }

  /**
  * given a form alias and a data ID delete the data
  * @param $arrFormData data to grab the correct form and associated data
  * @return string (HTML)
  */
  function DeleteDataByAlias($arrFormData){
    if(array_key_exists('formalias',$arrFormData) && trim((string)$arrFormData['formalias']) != ''){
      //if we have an actual ID we need to preserve it
      switch($arrFormData['formalias']){

        /* case '':

         break;*/
         default:
            return FALSE;
      }
    }
    else{
     PCMW_Abstraction::Get()->AddUserMSG( 'Could not perfom action. Please try again later('.__LINE__.').',1);
     return FALSE;
    }
  }

  /**
   * given an element name or form ID, make a form for element creation/adjustment
   * @param array $arrValues array of data sent via AJAX
   * @return string HTML
   */
   function GetFormElement($arrValues){
    $arrDefinitionData = array();
    $objElementControls = new PCMW_DynamicFormInputs();
    $objElementControls->boolAllowNewElements = FALSE;
    if((int)$arrValues['elementid'] > 0){
      //let's get the collection and data
      $arrDefinitionData = PCMW_FormManager::Get()->GetDefinitionData($arrValues['elementid']);
      $arrDefinitionData['formaction'] = 'updatelement';
      $arrDefinitionData['formid'] = $arrDefinitionData['formgroup'];
    }
    else if((int)$arrValues['formid'] > 0){
      //let's get the collection and data
      $arrDefinitionData = PCMW_FormManager::Get()->GetFormData($arrValues['formid']);
      $arrDefinitionData['formgroup'] = $arrValues['formid'];
    }
    else{
     return '0aleNo form data present to get element data from';
    }
    $objElementControls->boolIsForm = 1;
    $objElementControls->intFormGroupId = 1;
    $objElementControls->boolMakeSubmitButton = 1;
    $objElementControls->boolUseFieldSet = 1;
    return '1frm'. $this->MakePopUpFormContainer($objElementControls->InitiateFormControls($arrDefinitionData),$arrValues['title'],$arrValues['heading']);

   }

   /**
    * gievn a form ID, get the name and send back the form for update
    * @param int $intFormId unique form ID
    * @return string HTML
    */
    function UpdateFormData($arrPOSTData){
      if((int)$arrPOSTData['formid'] < 1)
        return '0aleNo form ID submitted. Cannot get form to update';
      $arrPOST = PCMW_FormManager::Get()->GetFormData($arrPOSTData['formid']);
      $arrPOST['formaction'] = 'updateform';
      $objElementControls = new PCMW_DynamicFormInputs();
      $objElementControls->boolIsForm = 1;
      $objElementControls->intFormGroupId = 5;
      $arrPOST['formgroup'] = $arrPOSTData['formid'];
      $arrPOST['formid'] = $arrPOSTData['formid'];
      $objElementControls->boolMakeSubmitButton = 1;
      $objElementControls->boolUseFieldSet = 1;
      $strReturn = $this->MakePopUpFormContainer($objElementControls->InitiateFormControls($arrPOST),$arrPOSTData['title'],$arrPOSTData['heading']);
      return '1frm'.$strReturn;
    }

      /**
    * delete a form element
    * It is assunmed a message warning of the permanence of this action has been sent
    * @param array $arrPOSTData collection of data
    * @return string status
    */
    function DeleteFormElement($arrPOSTData){
      if((int)$arrPOSTData['elementid'] < 1)
        return '0aleCannot delete form element. No valid ID given.';
      if(PCMW_Database::Get()->DeleteDefinitionData($arrPOSTData['elementid'])){
        return '0ale0001PCMW_Element Deleted';
      }
      else{
        return '0ale0001PCMW_Element NOT deleted. Something went wrong.';
      }
    }

    /**
    * given a form element group and posted data, reload a failed form
    * @param array $arrPOSTData data sent to the server initially
    * @return string HTML
    */
    function RetryNewFormElement($arrPOSTData){
      if((int)$arrPOSTData['formid'] < 1)
        return '0aleCannot gather form data. Exiting.';
      $objElementControls = new PCMW_DynamicFormInputs();
      $objElementControls->boolIsForm = 1;
      $objElementControls->intFormGroupId = 1;
      $arrPOSTData['formgroup'] = $arrPOSTData['formid'];
      $objElementControls->boolMakeSubmitButton = 1;
      $objElementControls->boolUseFieldSet = 1;
      $strCollection = $objElementControls->InitiateFormControls($arrPOSTData);
      return '1frm'.$this->MakePopUpFormContainer($strCollection,$arrPOSTData['title'],$arrPOSTData['heading']);
    }

     /**
    * given a form ID copy it and all of it's subordinate elements
    * @param array $arrPOSTData should contain a form id
    * @return strimg
    */
    function CopyForm($arrPOSTData){
      $strResults = '';
      //get the form
        if((int)$arrPOSTData['formid'] > 0 && PCMW_FormManager::Get()->CopyForm($arrPOSTData))
            $strResults .= 'Form copied correctly.'."\n";
        else
            $strResults .= 'Cannot create new form.'."\n";
      return '0alu'.$strResults;
    }

  /**
  * given the proper data, send support an email
  * @param $arrValues form data
  * @return string ( ajax handler + response )
  */
  function SendSupportEmail($arrValues){
    if(array_key_exists('featuretype',$arrValues) &&
       trim($arrValues['featuretype']) != '' &&
       array_key_exists('featuredescription',$arrValues) &&
       trim($arrValues['featuredescription']) != ''){
       //looks like we have everything, send the message
       $arrCurrentUser = wp_get_current_user();
       $strUserData = var_export($arrCurrentUser->data,TRUE);
       if(PCMW_Abstraction::Get()->Send_Mail(PCMW_SUPPORT , 'Feature Request '.$arrValues['featuretype'] , $arrValues['featuredescription'].'<br /><br />'.$strUserData))
        $arrResults = array('strbackgroundcolor'=>'#10CC4F','strmessage'=>'Thank you! your request was successfully sent. We will email you if we have any questions.');
       else
        $arrResults = array('strbackgroundcolor'=>'#FFA8A8','strmessage'=>'Your request was NOT sent. Please try again, or contact <a href="mailto:'.PCMW_SUPPORT.'">'.PCMW_SUPPORT.'</a> or use the helpdesk option in settings.');
      //give it back now
      return '1dfu'.json_encode($arrResults);
    }
    return '0aleSomething went wrong!';
  }

  /**
  * uninstall or install a feature
  * @param $arrValues - form values affecting the install
  * @param $boolInstall - remove it or not flag
  * @return string ajax result code
  */
  function InstallRemoveFeature($arrValues,$boolInstall=TRUE){
    if($strFeatures = get_option('PCMW_features'))
        $arrFeatures = json_decode($strFeatures,TRUE);
    else
        $arrFeatures = array();
    $strResult = '0aleNo feature exists.';
    //see what we're doing
    switch($arrValues['feature']){
     case 'manage-pcmw-maps':
       //maps and groups
       if($boolInstall){
         if(PCPluginInstall::Get()->InstallPCMaps()){
          $arrFeatures[$arrValues['feature']] = 1;
          $strResult = '0aluMaps installed correctly.';
         }
         else{
          $strResult = '0aleMaps not installed correctly.';
         }
       }
       else{
         if(PCPluginInstall::Get()->UnInstallPCMaps()){
          $arrFeatures[$arrValues['feature']] = 0;
          $strResult = '0aluMaps uninstalled correctly.';
         }
         else{
          $strResult = '0aleMaps not uninstalled.';
         }
       }
     break;
     case 'pcmw-mail-blast':
       //mail blast
       if($boolInstall){
         if(PCPluginInstall::Get()->InstallMailBlast()){
          $arrFeatures[$arrValues['feature']] = 1;
          $strResult = '0aluMail blast installed correctly.';
         }
         else{
          $strResult = '0aleMail blast not installed correctly.';
         }
       }
       else{
         if(PCPluginInstall::Get()->UnInstallMailBlast()){
          $arrFeatures[$arrValues['feature']] = 0;
          $strResult = '0aluMail blast uninstalled correctly.';
         }
         else{
          $strResult = '0aleMail blast not uninstalled.';
         }
       }
     break;
     case 'manage-pcmw-404-redirects':
       //404 redirects
       if($boolInstall){
        if(PCPluginInstall::Get()->Install404Redirect()){
          $arrFeatures[$arrValues['feature']] = 1;
          $strResult = '0alu404 redirects installed correctly.';
         }
         else{
          $strResult = '0ale404 redirects not installed correctly.';
         }
       }
       else{
        if(PCPluginInstall::Get()->UnInstall404Redirect()){
          $arrFeatures[$arrValues['feature']] = 0;
          $strResult = '0alu404 redirects uninstalled correctly.';
         }
         else{
          $strResult = '0ale404 redirects not uninstalled.';
         }
       }
     break;
     case 'video-access':
       //404 redirects
       if($boolInstall){
        if(PCPluginInstall::Get()->InstallVideoAccess()){
          $arrFeatures[$arrValues['feature']] = 1;
          $strResult = '0aluVideo Access Controls installed correctly.';
         }
         else{
          $strResult = '0aleVideo Access Controls not installed correctly.';
         }
       }
       else{
        if(PCPluginInstall::Get()->UnInstallVideoAccess()){
          $arrFeatures[$arrValues['feature']] = 0;
          $strResult = '0aluVideo Access Controls uninstalled correctly.';
         }
         else{
          $strResult = '0aleVideo Access Controls not uninstalled.';
         }
       }
     break;
     case 'basicchat':
       //404 redirects
       if($boolInstall){
        if(PCPluginInstall::Get()->InstallBasicChat()){
          $arrFeatures[$arrValues['feature']] = 1;
          $strResult = '0aluBasic Chat installed correctly.';
         }
         else{
          $strResult = '0aleBasic Chat not installed correctly.';
         }
       }
       else{
        if(PCPluginInstall::Get()->UnInstallBasicChat()){
          $arrFeatures[$arrValues['feature']] = 0;
          $strResult = '0aluBasic Chat uninstalled correctly.';
         }
         else{
          $strResult = '0aleBasic Chat not uninstalled.';
         }
       }
     break;
     default:
        return $strResult;
    }
    update_option( 'PCMW_features', json_encode($arrFeatures),NULL,'no' );
    return $strResult;
  }

  /**
  * given an area of the config variables to update, get and store them
  * @param $arrValues - group of selected config values to be updated
  * @return string ( response code and message )
  */
  function UpdatePCConfigParts($arrValues){
      //get our parts from static array values
      $arrConfigData = PCMW_Database::Get()->GetStaticArrayGroup('configheaders',0,0,$arrValues['action']);
      //load our config modifier data
      $arrModifierData = PCMW_Utility::Get()->DecomposeCurlString($arrConfigData[0]['modifier']);
      $arrConfigData = PCMW_Utility::Get()->JSONDecode($arrModifierData['keys']);
      $arrAlternativeDefinitions = array();
      //get our form elements for validation
      $arrElements = PCMW_FormManager::Get()->GetDefinitionByAlias('pcconfig',PCMW_MODERATOR); //
      foreach($arrElements as $arrElement){
        if(array_key_exists($arrElement['elementname'],$arrConfigData))
            $arrAlternativeDefinitions[$arrElement['definitionid']] = $arrElement;
      }
      //validate
      if(!PCMW_FormManager::Get()->ValidateDefinitionRequires(0,$arrValues,null,'',$arrAlternativeDefinitions))
        return '0alu'.$arrConfigData['menuvalue'].' configuration settings NOT Updated.';
      //make our updates now
      foreach($arrConfigData as $strKey=>$arrOptions){
        if(array_key_exists($strKey,$arrValues) &&
           trim($arrValues[$strKey]) != ''){
             $_SESSION['pcconfig'][$strKey] = $arrValues[$strKey];
           }
      }
      //all done, let's save it now
      if(PCPluginInstall::Get()->UpdatePCConfig()){
        //update our pages now
        $this->MakeFeaturePages();
        return '0alu'.$arrConfigData['menuvalue'].' configuration settings Updated.';
      }
      else
        return '0alu'.$arrConfigData['menuvalue'].' configuration settings NOT Updated.';
  }


  /**
  * Check the session variables for page install or disable actions
  * @return bool
  */
  function MakeFeaturePages(){
   $arrPageOptions = PCMW_Database::Get()->GetStaticArrayGroup('pageoptions');
   $arrOptionData = json_decode($arrPageOptions[0]['modifier'],TRUE);
   foreach($arrOptionData as $strKey=>$arrOptions){
    $boolDisablePage = ((int)$_SESSION['pcconfig'][$strKey] < 1)? FALSE:TRUE ;
    $strPageName = PCMW_Abstraction::Get()->MakeSingleDataReplacements( array(),htmlspecialchars_decode($arrOptions['pagename'],ENT_HTML5 | ENT_QUOTES));
    $strPageContent = PCMW_Abstraction::Get()->MakeSingleDataReplacements( array(),htmlspecialchars_decode($arrOptions['pagecontent'],ENT_HTML5 | ENT_QUOTES));
    PCPluginInstall::Get()->AddSupportingPage($strPageName,'['.$strPageContent.' makesubmit="0"]',$boolDisablePage);
   }
   return TRUE;
  }

  /**
  * given a subject, get the content and return it
  * @param $arrValues
  * @return string ( HTML )
  */
  function GetHowToSubject($arrValues){
   if(array_key_exists('howtosubject',$arrValues) && trim($arrValues['howtosubject']) != ''){
     $arrRequest = array('purpose'=>'gethowtoformsubject',
                         'howtosubject'=>$arrValues['howtosubject'],
                         'userkey'=>$_SESSION['pcconfig']['PCMW_USERKEY'],
                         'address'=>get_site_url());
     if($arrPayLoad = PCMW_HostRequest::Get()->MakeHostRequest($arrRequest)){
        return '1frm'.$this->MakePopUpFormContainer($arrPayLoad['payload']);
     }
     else
        return '0aleSorry, that subject isn\'t available at this time.';
   }
   else
    return '0aleSorry, we couldn\'t get that subject.';
  }

  /**
  * save a video
  * @param $arrValues
  * @return string ( results )
  */
  function SaveVideo($arrValues){
    if(PCMW_VideoAccess::Get()->SaveOrUpdateVideo($arrValues))
        return '0aluVideo Data Updated';
    else
        return '0aluVideo Data NOT Updated!';
  }

  /**
  * delete a video
  * @param $arrValues
  * @return string ( results )
  */
  function DeleteVideo($arrValues){
    if(PCMW_Database::Get()->DeleteVideo($arrValues['videoid']))
        return '0aluVideo Deleted Correctly';
  }

  /**
  * given the chat options, update them
  * @param $arrValues
  * @return string ( status )
  */
  function SaveChatOptions($arrValues){
    //validate our options
    $objChatOptions = new PCMW_ChatOptions();
    $objChatOptions->LoadObjectWithArray($arrValues);
    $objChatOptions->intLastUpdate = time();
    $objChatOptions->intLastUpdatedBy = get_current_user_id();
    if(!PCMW_FormManager::Get()->ValidateDefinitionRequires(0,$arrValues,$objChatOptions))
      return '1dfu'.json_encode(array('strclassname'=>'background-red'),TRUE);
    else{
      //update our options and session
      $this->storeObject('chatoptions', $objChatOptions);
      update_option('PCMW_ChatOptions',json_encode($arrValues,TRUE) );
      return '1dfu'.json_encode(array('strclassname'=>'background-green'),TRUE);
    }
  }

  /**
  * given a session ID, load the chat content
  * @param $arrValues
  * @return string ( result )
  */
  function LoadChatContent($arrValues){
    if((int)$arrValues['chatsession'] < 1)
        return 'true';//empty session need not be loaded
    $objChatSession = PCMW_BasicChat::Get()->GetChatSession($arrValues['chatsession']);
    PCMW_BasicChat::Get()->LoadUserNameAndStatus($objChatSession);
    $strChats = PCMW_BasicChat::Get()->FormChatEntries($objChatSession->arrChatMessages);
    $arrResults = array();
    $arrResults['updateelements'] = array();
    $arrResults['updateelements'][$arrValues['chatsession']] = array('elementid'=>$_SESSION['pc_chatid']);
    $arrResults['updateelements'][$arrValues['chatsession']]['newhtml'] = urldecode($strChats);
    if(PCMW_BasicChat::Get()->intLastResponder != (int)get_current_user_id() && $objChatSession->intStatus == PCMW_UNREAD){
        $arrResults['updateelements']['pcmw_chat_window_'.$_SESSION['pc_chatid']] = array('elementid'=>'pcmw_chat_window_'.$_SESSION['pc_chatid']);
        $arrResults['updateelements']['pcmw_chat_window_'.$_SESSION['pc_chatid']]['flashclass'] = 'background-yellow displayblock';
        $arrResults['updateelements']['pcmw_chat_window_'.$_SESSION['pc_chatid']]['removeclass'] = 'displaynone';
    }
    else{
        $arrResults['updateelements']['pcmw_chat_window_'.$_SESSION['pc_chatid']] = array('elementid'=>'pcmw_chat_window_'.$_SESSION['pc_chatid']);
        $arrResults['updateelements']['pcmw_chat_window_'.$_SESSION['pc_chatid']]['addclass'] = 'displaynone';
    }
    //admins can overtake sessions
    if((int)$arrValues['chatsession'] > 0 && (int)$arrValues['chatsession'] != (int)$_SESSION['pc_chatsession']){
      $_SESSION['pc_chatsession'] = $arrValues['chatsession'];
      $arrResults['updateelements'][$_SESSION['pc_chatid']] = array('elementid'=>'pc_chat_entry');
      PCMW_BasicChat::Get()->boolMakeAdminInterface = TRUE;
      //are we taking this chat over?
      if(array_key_exists('ownchat',$arrValues) && $arrValues['ownchat'] == 'true'){
        if(array_key_exists('anonusername',$objChatSession->arrChatMeta))
            $_SESSION['anonusername'] = $objChatSession->arrChatMeta['anonusername'];
        $strNewControls = PCMW_BasicChat::Get()->MakeChatControls($objChatSession,$_SESSION['pc_chatid']);
        $arrResults['updateelements'][$_SESSION['pc_chatid']]['newhtml'] = $strNewControls;
        $intNewOwnerId = get_current_user_id();
        if($intNewOwnerId != $objChatSession->intOwnerId){
          $objChatSession->intPreviousOwnerId = $objChatSession->intOwnerId;
          $objChatSession->intOwnerId = $intNewOwnerId;
        }
        $objChatSession->intStatus = PCMW_TAKEN;
        PCMW_Database::Get()->UpdateChatSession($objChatSession);
        $arrAvailableSessions = PCMW_BasicChat::Get()->GetChatSessions(get_current_user_id(),FALSE);
        //get our names
        PCMW_BasicChat::Get()->LoadSessionNames($arrAvailableSessions);
        //format for display
        PCMW_BasicChat::Get()->FormatChatSessions($arrAvailableSessions);
        $arrResults['updateelements']['sessions'] = array('elementid'=>'pc_chat_sessions');
        $arrResults['updateelements']['sessions']['newhtml'] = PCMW_BasicChat::Get()->strChatSessions;
      }
    }
    return '1udc'.json_encode($arrResults,TRUE);
  }

  /**
  * load the present chat sessions for this admin user
  * @param $arrValues
  * @return string  ( result )
  */
  function LoadChatSessions($arrValues,$boolResultsOnly = FALSE){
    $arrAvailableSessions = PCMW_BasicChat::Get()->GetChatSessions(get_current_user_id(),FALSE);
    //get our names
    PCMW_BasicChat::Get()->LoadSessionNames($arrAvailableSessions);
    //format for display
    PCMW_BasicChat::Get()->FormatChatSessions($arrAvailableSessions);
    $arrResults = array();
    $arrResults['updateelements'] = array();
    $arrResults['updateelements'][$_SESSION['pc_chatsession']] = array('elementid'=>'pc_chat_sessions');
    $arrResults['updateelements'][$_SESSION['pc_chatsession']]['newhtml'] = PCMW_BasicChat::Get()->strChatSessions;
    return '1udc'.json_encode($arrResults,TRUE);
  }

  /**
  * given a session ID and message, savea  chat message
  * @param $arrValues
  * @return JSON ( transaction update data )
  */
  function SaveChatMessage($arrValues){
    //find out if we have a session ID, or if we need to create one
    $arrResults = array();
    $arrResults['updateelements'] = array();
    if(array_key_exists('anonusername',$arrValues) && $arrValues['anonusername'] != '' && $arrValues['anonusername'] != 'Anon' && trim(@$_SESSION['anonusername']) == '')
        $_SESSION['anonusername'] = $arrValues['anonusername'];
    if((int)$arrValues['chatsession'] < 1 && (int)$_SESSION['pc_chatsession'] < 1){
      //new session
      if(PCMW_BasicChat::Get()->CheckChatOptions()){
        $objChatSession = new PCMW_ChatSession();
        $objChatSession->LoadObjectWithArray($arrValues);
        $objChatSession->intUserId = (int)get_current_user_id();
        $objChatSession->strChatType = PCMW_BasicChat::Get()->objChatOptions->strChatType;
        $objChatSession->intChatAccess = PCMW_BasicChat::Get()->objChatOptions->intChatAccessGroup;
        $objChatSession->intStatus = PCMW_NEW;
        $objChatSession->intUpdateAlert = 0;
        $objChatSession->strChatMeta = json_encode(array('anonusername'=>$_SESSION['anonusername']));
        $objChatSession->intStartDate = time();
        if(!$arrValues['chatsession'] = PCMW_Database::Get()->InsertChatSession($objChatSession))
            return '0aleChat not available';
        else{
         $objChatSession->intChatSessionId = $arrValues['chatsession'];
         $_SESSION['pc_chatsession'] = $arrValues['chatsession'];
         $arrResults['updateelements'][$arrValues['chatsession']] = array('elementid'=>$arrValues['rowid']);
         $arrResults['updateelements'][$arrValues['chatsession']]['newhtml'] = PCMW_BasicChat::Get()->MakeChatControls($objChatSession,$_SESSION['pc_chatid']);
        }
      }
      else return '0aleChat not available';
    }
    else{
        $objChatSession = PCMW_BasicChat::Get()->GetChatSession($_SESSION['pc_chatsession']);
        $objChatSession->intStatus = PCMW_UNREAD;
        //prevent responses from deleting the anon user name
        if(array_key_exists('anonusername',$objChatSession->arrChatMeta) && ($_SESSION['anonusername'] == '' || $_SESSION['anonusername'] == 'Anon'))
            $_SESSION['anonusername'] = $objChatSession->arrChatMeta['anonusername'];
        //update our meta data if it doesn't exist, but does in session
        if((!array_key_exists('anonusername',$objChatSession->arrChatMeta) || trim($objChatSession->arrChatMeta['anonusername']) == '') && $_SESSION['anonusername'] != ''){
            $objChatSession->arrChatMeta['anonusername'] = $_SESSION['anonusername'];
            $objChatSession->strChatMeta = json_encode($objChatSession->arrChatMeta);
        }

        PCMW_Database::Get()->UpdateChatSession($objChatSession);
        $arrResults['updateelements'][$arrValues['chatsession']] = array('elementid'=>$arrValues['rowid']);
    }
    //we have a session, load the message now
    $objMessage = new PCMW_ChatMessage();
    $objMessage->LoadObjectWithArray($arrValues);
    $objMessage->intChatSessionId = $arrValues['chatsession'];
    $objMessage->intUserId = (int)get_current_user_id();
    $objMessage->strMessage = $arrValues['message'];
    if(PCMW_BasicChat::Get()->SubmitMessage($objMessage)){
      $arrResults['updateelements'][$arrValues['chatsession']]['flashclass'] = 'background-green';
    }
    else{
      $arrResults['updateelements'][$arrValues['chatsession']]['flashclass'] = 'background-red';
    }
    //return needs
    /*
      .elementid //parent object element to alter contents of
      .classname //classname for parent object
      .newhtml //new content for the child

    */
    return '1udc'.json_encode($arrResults,TRUE);
  }

  /**
  * given a chat session Id, close it and give back a blank slate
  * @param $arrValues
  * @return string ( results )
  */
  function CloseChat($arrValues){
    $arrResults = array();
    $arrResults['updateelements'] = array();
    $arrResults['updateelements'][$arrValues['chatsession']] = array('elementid'=>'pc_chat_entry');
    if($objChatSession = PCMW_BasicChat::Get()->GetChatSession($arrValues['chatsession'])){
      $objChatSession->intStatus = PCMW_CLOSED;
      if(PCMW_Database::Get()->UpdateChatSession($objChatSession)){
        $objChatSession->intChatSessionId = 0;
        unset($_SESSION['pc_chatsession']);
        $arrResults['updateelements'][$arrValues['chatsession']]['newhtml'] = PCMW_BasicChat::Get()->MakeChatControls($objChatSession,$_SESSION['pc_chatid']);
        $arrResults['updateelements'][$_SESSION['pc_chatid']] = array('elementid'=>$_SESSION['pc_chatid']);
        $arrResults['updateelements'][$_SESSION['pc_chatid']]['newhtml'] = 'Please select a chat';
        //load our sessions again
        $arrAvailableSessions = PCMW_BasicChat::Get()->GetChatSessions(get_current_user_id(),FALSE);
        //get our names
        PCMW_BasicChat::Get()->LoadSessionNames($arrAvailableSessions);
        //format for display
        PCMW_BasicChat::Get()->FormatChatSessions($arrAvailableSessions);
        $arrResults['updateelements']['sessions'] = array('elementid'=>'pc_chat_sessions');
        $arrResults['updateelements']['sessions']['newhtml'] = PCMW_BasicChat::Get()->strChatSessions;
      }
    }
    return '1udc'.json_encode($arrResults,TRUE);
  }

  #REGION CSS HANDLERS

  /**
  * get a custom CSS input form
  * @param $arrValues: array()
  * @param 'sectionname' - section of form to modify. Serves as HTML id prefix.
  * @param 'sectioncount' number of existing
  * @return string ( HTML )
  */
  function MakeCSSOptionInput($arrValues){
    $arrOption = PCMW_CustomMenu::Get()->MakeCSSOptionInput($arrValues);
    return '1afj'.PCMW_Utility::Get()->JSONEncode($arrOption);
  }

  /**
  * get CSS option values supported
  * @param $strParentId - ID to return _value data for
  * @param $strParameter - option to supply predefined parameters for, if applicable
  * @return str ( result )
  */
  function MakeSupportedCSSDefaults($strParentId,$strParameter){
    if($arrProprties = PCMW_CSSInterface::Get()->GetPropertyDefaults($strParameter)){
      $arrProprties['strOptionId'] = $strParentId.'_value';
      return '1rpe'.PCMW_Utility::Get()->JSONEncode($arrProprties);
    }
    else{
      return TRUE;
    }
  }

  /**
  * given a group of element attributes, save them
  * @param $arrValues
  * @return str ( result )
  */
  function SaveMenuSection($arrValues){
    if(PCMW_CustomMenu::Get()->UpdateMenuUpdateOptions($arrValues)){
     return '0aluMenu Section Saved';
    }
    else{
     $arrResults = array('rowid'=>$arrValues['section']);
     $arrResults['classname'] = 'background-red';
     return '1urd'.PCMW_Utility::Get()->JSONEncode($arrResults);
    }
  }

  /**
  * given the custom CSS data, save it back to the 'custom-style.css' file
  * @param $arrValues
  * @return string ( result )
  */
  function SaveCustomCSS($arrValues){
  	if(trim($arrValues['pcmt-custom-css']) != ""){
    	$strCustomStyle = get_theme_root( PCMT_THEMENAME ).DIRECTORY_SEPARATOR.PCMT_THEMEFOLDER.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'custom-style.css';
	  	if(PCMW_Abstraction::Get()->write_w($strCustomStyle,$arrValues['pcmt-custom-css']))
	    	return '0aluCustom CSS Saved';
		else{
		    $arrResults = array('rowid'=>$arrValues['rowid']);
		    $arrResults['classname'] = 'background-red';
		    return '1urd'.PCMW_Utility::Get()->JSONEncode($arrResults);
		}
	}
	else{
	    $arrResults = array('rowid'=>$arrValues['rowid']);
	    $arrResults['classname'] = 'background-red';
	    return '1urd'.PCMW_Utility::Get()->JSONEncode($arrResults);
	}
  }

  /**
  * given the body CSS save the values
  * @param $arrValues
  * @return string ( result )
  */
  function SaveCustomBodyCSS($arrValues){
    if(PCMW_CSSInterface::Get()->SaveSiteCSS($arrValues))
       	return '0aluCustom CSS Saved';
    else{
        $arrResults = array('rowid'=>$arrValues['rowid']);
        $arrResults['classname'] = 'background-red';
        return '1urd'.PCMW_Utility::Get()->JSONEncode($arrResults);
    }
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