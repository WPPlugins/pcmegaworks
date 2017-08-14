<?php
require_once(dirname(__FILE__) .DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'PCMW_AJAXCore.php');
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
class PC_AjaxCaller EXTENDS PCMW_AJAXCore{
  var $strRequestAction = FALSE;
  var $arrValues = FALSE;
  function __construct(){
     /*
     */
  }

  function AjaxCall(){
     if($this->strRequestAction == 'loadform')
        return $this->LoadFormGroup($this->arrValues);
     if($this->strRequestAction == 'deletdatabyalias')
        return $this->DeleteDataByAlias($this->arrValues);
     if($this->strRequestAction == 'getformbyalias')
        return $this->LoadFormGroupByAlias($this->arrValues);
     if($this->strRequestAction == 'deletevendor')
        return $this->DeleteVendorOrGroup($this->arrValues);
     if($this->strRequestAction == 'deletevideo')
        return $this->DeleteVideo($this->arrValues);
     if($this->strRequestAction == 'savevideo')
        return $this->SaveVideo($this->arrValues);
     if($this->strRequestAction == 'loadformbyalias')
        return $this->GetFormFromAlias($this->arrValues);
     if($this->strRequestAction == 'getanonymousformbyalias')
        return $this->GetFormFromAlias($this->arrValues);
     if($this->strRequestAction == 'doanonymousaction')
        return $this->PerformAnoymousAction($this->arrValues);
     if($this->strRequestAction == 'sendsupportemail' && PCMW_Abstraction::Get()->ValidateNonce($this->arrValues))
        return $this->SendSupportEmail($this->arrValues);
     if($this->strRequestAction == 'newcontactus' && PCMW_Abstraction::Get()->ValidateNonce($this->arrValues))
        return $this->SendContactUs($this->arrValues);
     if($this->strRequestAction == 'newhawd' && PCMW_Abstraction::Get()->ValidateNonce($this->arrValues))
        return $this->SendFeedback($this->arrValues);
     if($this->strRequestAction == 'uninstallfeature')
        return $this->InstallRemoveFeature($this->arrValues,FALSE);
     if($this->strRequestAction == 'gethowtosubject')
        return $this->GetHowToSubject($this->arrValues);
     if($this->strRequestAction == 'installfeature')
        return $this->InstallRemoveFeature($this->arrValues);
     if($this->strRequestAction == 'getvideo')
        return $this->GetVideo($this->arrValues);
     //CHAT
     if($this->strRequestAction == 'loadchatcontent')
        return $this->LoadChatContent($this->arrValues);
     if($this->strRequestAction == 'loadchatsessions')
        return $this->LoadChatSessions($this->arrValues);
     if($this->strRequestAction == 'newchatmessage')
        return $this->SaveChatMessage($this->arrValues);
     if($this->strRequestAction == 'closechat')
        return $this->CloseChat($this->arrValues);
     //CSS EDIT
     if($this->strRequestAction == 'addcssoption')
        return $this->MakeCSSOptionInput($this->arrValues);
     if($this->strRequestAction == 'savemenusection')
        return $this->SaveMenuSection($this->arrValues);
	 //save global custom css
     if($this->strRequestAction == 'pcmt_custom_css')
        return $this->SaveCustomCSS($this->arrValues);
	 //save global custom css
     if($this->strRequestAction == 'savesitecss')
        return $this->SaveCustomBodyCSS($this->arrValues);
     if(PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,PCMW_SUPERUSERS,FALSE,FALSE)){
       //create and modify form elements
       if(($this->strRequestAction == 'modifyformelement' || $this->strRequestAction == 'createformelement')){
          return $this->GetFormElement($this->arrValues);
       }
       if($this->strRequestAction == 'updateform'){
          return $this->UpdateFormData($this->arrValues);
       }
       if($this->strRequestAction == 'deleteformelement'){
          return $this->DeleteFormElement($this->arrValues);
       }
       if($this->strRequestAction == 'retrynewformelement'){
          return $this->RetryNewFormElement($this->arrValues);
       }
       if($this->strRequestAction == 'copyform'){
          return $this->CopyForm($this->arrValues);
       }
       if($this->strRequestAction == 'updateconfig' && PCMW_Abstraction::Get()->ValidateNonce($this->arrValues))
          return $this->UpdatePCConfigParts($this->arrValues);
       if($this->strRequestAction == 'savechatoptions' && PCMW_Abstraction::Get()->ValidateNonce($this->arrValues))
          return $this->SaveChatOptions($this->arrValues);
     }
     return FALSE;
  }

  /**
  * support for no priv ajax
  * @return HTML || FALSE
  */
  function NoPrivAjaxCall(){
     if($this->strRequestAction == 'getformbyalias')
        return $this->LoadFormGroupByAlias($this->arrValues);
     if($this->strRequestAction == 'newcontactus' && PCMW_Abstraction::Get()->ValidateNonce($this->arrValues))
        return $this->SendContactUs($this->arrValues);
     if($this->strRequestAction == 'newhawd' && PCMW_Abstraction::Get()->ValidateNonce($this->arrValues))
        return $this->SendFeedback($this->arrValues);
     if($this->strRequestAction == 'loadchatcontent')
        return $this->LoadChatContent($this->arrValues);
     if($this->strRequestAction == 'newchatmessage')
        return $this->SaveChatMessage($this->arrValues);
     if($this->strRequestAction == 'closechat')
        return $this->CloseChat($this->arrValues);
     if($this->strRequestAction == 'loadchatsessions')
        return 'true';//in case admin session expires
     return FALSE;
  }
}

  /**
  * handle our ajax request
  * @return string || JSON object
  */
  function pcmw_handle_ajax_request(){
    //make our object
    $objAjaxCall = new PC_AjaxCaller();
    $arrValues = filter_var_array($_REQUEST,FILTER_SANITIZE_STRING);
    $strPayLoad = htmlspecialchars_decode($arrValues['PC_payload']);
    $objAjaxCall->arrValues = json_decode( stripslashes($strPayLoad), TRUE );
    unset($objAjaxCall->arrValues['DB']);
    unset($objAjaxCall->arrValues['cash']);
    if(array_key_exists('dir',$objAjaxCall->arrValues) && $objAjaxCall->arrValues['dir'] != ""){
      $objAjaxCall->strRequestAction = $objAjaxCall->arrValues['dir'];
      $caller = $objAjaxCall->AjaxCall();
      if($caller){
          echo $caller;
          exit;
      }
      else{
          echo "0aleNo method return for the request [".$objAjaxCall->strRequestAction."]. ";
          exit;
      }
    }
    else{
      $strRequest = var_export($objAjaxCall->arrValues,TRUE);
      $strValues = var_export($arrValues,TRUE);
      PCMW_Logger::Debug('$objAjaxCall->arrValues ['.$strRequest.'] $strPayLoad ['.$strPayLoad.'] $strValues ['.$strValues.']'.__LINE__,1);
      echo "0aleNo method return for the request [".$objAjaxCall->strRequestAction."] [".__LINE__."]";
      exit;
    }
  }
  /**
  * handle our ajax request
  * @return string || JSON object
  */
  function pcmw_handle_no_priv_ajax_request(){
    //make our object
    $objAjaxCall = new PC_AjaxCaller();
    $arrValues = filter_var_array($_REQUEST,FILTER_SANITIZE_STRING);
    $strPayLoad = htmlspecialchars_decode($arrValues['PC_payload']);
    $objAjaxCall->arrValues = json_decode( stripslashes($strPayLoad), TRUE );
    unset($objAjaxCall->arrValues['DB']);
    unset($objAjaxCall->arrValues['cash']);
    if(array_key_exists('dir',$objAjaxCall->arrValues) && $objAjaxCall->arrValues['dir'] != ""){
      $objAjaxCall->strRequestAction = $objAjaxCall->arrValues['dir'];
      $caller = $objAjaxCall->NoPrivAjaxCall();
      if($caller){
          echo $caller;
          exit;
      }
      else{
          echo "0aleNo method return for the request [".$objAjaxCall->strRequestAction."]. ";
          exit;
      }
    }
    else{
      $strRequest = var_export($objAjaxCall->arrValues,TRUE);
      $strValues = var_export($arrValues,TRUE);
      PCMW_Logger::Debug('$objAjaxCall->arrValues ['.$strRequest.'] $strPayLoad ['.$strPayLoad.'] $strValues ['.$strValues.']'.__LINE__,1);
      echo "0aleNo method return for the request [".$objAjaxCall->strRequestAction."] [".__LINE__."]";
      exit;
    }
  }

//add our handlers
add_action( 'wp_ajax_PC_AjaxHandler', 'pcmw_handle_ajax_request' );
add_action( 'wp_ajax_nopriv_PC_AjaxHandler', 'pcmw_handle_no_priv_ajax_request' );
?>