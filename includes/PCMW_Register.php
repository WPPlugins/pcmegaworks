<?php
/**************************************************************************
* @CLASS PCMW_Register
* @brief Handle all things registration related.
* @REQUIRES:
*  -PCMW_Database.php
*  -PCMW_Abstraction.php
*
**************************************************************************/
class PCMW_Register{
   //debugging
   var $boolDebugOn = FALSE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_Register();
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
    $arrDebugMethods['PCMW_Register::CLASSMETHOD'] = 1;
    return $arrDebugMethods;
  }

  /**
  * Handle registration actions
  * @return array ( cleaned $_POST )
  */
  function CheckForRegistration($arrPOST){
   //do we have an action?
   if(array_key_exists('action',$arrPOST) &&
      $arrPOST['action'] == 'register'){
      if($this->PCMW_RegisterUser($arrPOST)){
        $this->MakeSuccessfulRegistrationMessage();
        if(array_key_exists('redirectto',$arrPOST) && trim($arrPOST['redirectto']) != ''){
          PCMW_Abstraction::Get()->RedirectUser($arrPOST['redirectto']);
          exit;
        }
        else
            return TRUE;
      }
   }
   return $arrPOST;
  }


  /**
  * register a new user, and save custom meta
  * @param $arrPOST
  * @return bool
  */
  function PCMW_RegisterUser($arrPOST){
    $strUserName = '';
    $strUserEmail = '';
    //the only way this form is called is if we're using a custom form, get the data
    foreach(PCMW_FormManager::Get()->GetDefinitionByAlias('registration',1,TRUE) as $objDefinition){
      //check to see if this is designated as the username or password field
     $arrDefinitionAttributes = PCMW_Utility::Get()->DecomposeCurlString($objDefinition->strElementAttributes);
     if(array_key_exists('formrole',$arrDefinitionAttributes)){
       if($arrDefinitionAttributes['formrole'] == 'user_login')
         $strUserName = $arrPOST[$objDefinition->strDefinitionName];
       if($arrDefinitionAttributes['formrole'] == 'user_email')
         $strUserEmail = $arrPOST[$objDefinition->strDefinitionName];
     }
     continue 1;
    }
    $strPassword = wp_generate_password();
    $arrUserData = array(
        'user_pass' => $strPassword,
        'user_login' => esc_attr( $strUserName ),
        'user_email' => esc_attr( $strUserEmail ),
    );
    $strEmail = $this->MakeRegistrationEmailText($strUserName,$strPassword);
    //insert the user now
    $intWPUser = wp_insert_user( $arrUserData );
    
    if(!PCMW_Abstraction::Get()->Send_Mail($strUserEmail , PCMW_SITENAME.' Registration' , $strEmail , PCMW_SUPPORT))
      PCMW_Abstraction::Get()->AddUserMSG( 'We&#39;re sorry, this is embarrassing. You&#39;re account was registered but the email could not be sent. Please contact '.PCMW_ADMIN.' for further assistance. ['.__LINE__.']',1);
    //$intWPUser = register_new_user( $strUserName, $strUserEmail );
    if(is_wp_error( $intWPUser )){
      $strErrors = var_export($intWPUser,TRUE);
      PCMW_Abstraction::Get()->AddUserMSG( '<pre>'.$strErrors.'</pre> ['.__LINE__.']',1);
      return FALSE;
    }
    else{
      //save any custom fields being used
      foreach($arrPOST as $strKey=>$varValue)
        update_user_meta( $intWPUser, trim( $strKey ), trim( $varValue ) );
       //make our admin user record now
       $objAdminUser = new PCMW_AdminUser();
       $objAdminUser->intUserId = $intWPUser;
       $objAdminUser->intHandlerId = $intWPUser;
       $objAdminUser->intCustomerId = $intWPUser;
       $objAdminUser->intAdminGroupId = PCMW_BASICUSER;
       $objAdminUser->intStatus = 10;
       $objFormManager = new PCMW_FormManager();
       PCMW_AdminUserCore::Get()->CleanAndInsertAdminUser($objAdminUser,$arrPOST,$objFormManager,'');
      return $intWPUser;
    }
  }

  /**
  * get the successful registration message
  * @return bool
  */
  function MakeSuccessfulRegistrationMessage($boolReturn=FALSE){
    if(!($strMessage = stripslashes(urldecode(get_option('PCMW_regcompletetext'))))){
      $strMessage = '<h2>Success!</h2><p>Check your email for your registration confirmation and password. If you don&#39;t see it shortly, check any spam folders or contact us for further assistance.</p>';
      update_option( 'PCMW_regcompletetext', urlencode($strMessage),'no' );
      if($boolReturn)
        return $strMessage;
    }
    $arrUserPass = array();
    $strMessage = PCMW_Abstraction::Get()->MakeSingleDataReplacements( $arrUserPass,$strMessage);
    PCMW_Abstraction::Get()->AddUserMSG( $strMessage,3);
    return $strMessage;
  }

  /**
  * make the email text and add replacements for a successful registration
  * @param $strUserName
  * @param $strPassWord
  * @return string ( HTML )
  */
  function MakeRegistrationEmailText($strUserName,$strPassWord,$boolReturn=FALSE){
    if(!($strEmail = stripslashes(urldecode(get_option('PCMW_regemailtext'))))){
      //load our default message
      $strEmail = 'Hi %username%<br />';
      $strEmail .= 'Thank you for your interest in '.get_site_url().'/'.'.<br /><br />';
      $strEmail .= 'Use your email address and the following temporary password to login to your account:<br />';
      $strEmail .= 'Password: %password%<br />';
      $strEmail .= 'Login link: '.get_site_url().'/'.'%PCMW_LOGINPAGE%<br /><br />';
      $strEmail .= 'We encourage you to change your password to something you will remember. Please click here: <a href="'.get_site_url().'/'.'wp-admin/profile.php">My Account</a><br /><br />';
      $strEmail .= 'Logging into your account will allow you to change your password, update your email preferences, or update your email address.<br /><br />';
      $strEmail .= 'Thanks and welcome to %PCMW_SITENAME%.<br />';
      update_option( 'PCMW_regemailtext', urlencode($strEmail),'no' );
      if($boolReturn)
        return $strEmail;
    }
    //make our basic replacements
    $strEmail = str_replace("%username%",$strUserName,$strEmail);
    $strEmail = str_replace("%password%",$strPassWord,$strEmail);
    $arrUserPass = array();
    $strEmail = PCMW_Abstraction::Get()->MakeSingleDataReplacements( $arrUserPass,$strEmail);
    return $strEmail;
  }

  /**
  * given the sanitized POST values, validate and store/update the options for use
  * @param $arrPOST
  * @return bool
  */
  function SaveRegistrationText(&$arrPOST){
    //validate our nonce
    if(sizeof($arrPOST) > 0 &&
       array_key_exists('wp_nonce',$arrPOST) &&
       trim($arrPOST['wp_nonce']) != '' &&
       array_key_exists('submissionid',$arrPOST) &&
       trim($arrPOST['submissionid']) != ''){
       if(!wp_verify_nonce($arrPOST['wp_nonce'],$arrPOST['submissionid'])){
        PCMW_Abstraction::Get()->AddUserMSG( 'Registration text/email NOT saved!',1);
        return FALSE;
       }
    }
    else{
     $this->VerifyDefaultRegistrationText($arrPOST);
     return TRUE;
    }
    //get any existing optional text, or get it
    if(!array_key_exists('emailtext',$arrPOST) || trim($arrPOST['emailtext']) == ''){
      $arrPOST['emailtext'] = $this->MakeRegistrationEmailText(NULL,NULL,TRUE);
    }
    if(!array_key_exists('landingtext',$arrPOST) || trim($arrPOST['landingtext']) == ''){
      $arrPOST['landingtext'] = $this->MakeSuccessfulRegistrationMessage(TRUE);
    }
    //replace our carriage returns with line breaks for formatting
    $arrPOST['emailtext'] = str_replace("\r\n",'<br />',stripslashes($arrPOST['emailtext']));
    $arrPOST['landingtext'] = str_replace("\r\n",'<br />',stripslashes($arrPOST['landingtext']));
    //we've got our text
    if(update_option( 'PCMW_regemailtext', urlencode($arrPOST['emailtext']),'no' ))
      PCMW_Abstraction::Get()->AddUserMSG( 'Registration email saved!',3);
    else{
      $strOption = get_option('PCMW_regemailtext');
      if(urlencode($arrPOST['emailtext']) == $strOption)
        PCMW_Abstraction::Get()->AddUserMSG( 'No changes made. Registration email NOT saved!',4);
      else
        PCMW_Abstraction::Get()->AddUserMSG( 'Registration email NOT saved!',1);
    }
    //registration text
    if(update_option( 'PCMW_regcompletetext', urlencode($arrPOST['landingtext']),'no' ))
      PCMW_Abstraction::Get()->AddUserMSG( 'Registration text saved!',3);
    else{
      $strOption = get_option('PCMW_regcompletetext');
      if(urlencode($arrPOST['landingtext']) == $strOption)
        PCMW_Abstraction::Get()->AddUserMSG( 'No changes made. Registration text NOT saved!',4);
      else
      PCMW_Abstraction::Get()->AddUserMSG( 'Registration text NOT saved!',1);
    }
    //replace our line breaks with carriage returns for visibility
    $arrPOST['emailtext'] = str_replace('<br />',"\r\n",$arrPOST['emailtext']);
    $arrPOST['landingtext'] = str_replace('<br />',"\r\n",$arrPOST['landingtext']);
    return TRUE;
  }

  /**
  * verify our default values for registration email and landing page text exist
  * @param $arrPOST
  * @return bool
  */
  function VerifyDefaultRegistrationText(&$arrPOST){
    if(!($arrPOST['emailtext'] = stripslashes(urldecode(get_option('PCMW_regemailtext'))))){
      $arrPOST['emailtext'] = $this->MakeRegistrationEmailText(NULL,NULL,TRUE);
    }
    if(!($arrPOST['landingtext'] = stripslashes(urldecode(get_option('PCMW_regcompletetext'))))){
      $arrPOST['landingtext'] = $this->MakeSuccessfulRegistrationMessage(TRUE);
    }
    //replace our line breaks with carriage returns for visibility
    $arrPOST['emailtext'] = str_replace('<br />',"\r\n",$arrPOST['emailtext']);
    $arrPOST['landingtext'] = str_replace('<br />',"\r\n",$arrPOST['landingtext']);
    return TRUE;
  }

  #ENDREGION



  #REGION DATABASECALLS
  //All functions which will interact with Database.php should go in here
  #ENDREGION

  #REGION DEBUGGINGMETHODS

  /*
  @brief store and display debug information
  @param $strMessage,$boolLogNow
  @param boolean
  */
  function PCMW_LoadDebugLog($strMessage='',$boolLogNow=FALSE,$strMethod='',$boolShowBackTrace=FALSE){
  //$this->PCMW_LoadDebugLog('VALUE ['.$arrPOST .']  LINE ['.__LINE__."]\r\n",FALSE,__METHOD__);
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