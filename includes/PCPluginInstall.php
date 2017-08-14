<?php
/**************************************************************************
* @CLASS PCPluginInstall
* @brief USE THIS TO CREATE NEW CLASSES FOR THE INCLUDES DIRECTORY.
* @REQUIRES:
*  -PCMW_Database.php
*  -PCMW_HostRequest.php
*
**************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_HostRequest.php');
class PCPluginInstall{
   //debugging
   var $boolDebugOn = FALSE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';

   public static function Get(){
    //==== instantiate or retrieve singleton ====
    static $inst = NULL;
    if( $inst == NULL )
      $inst = new PCPluginInstall();
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
    $arrDebugMethods['PCPluginInstall::CLASSMETHOD'] = 1;
    return $arrDebugMethods;
  }

  #ENDREGION

  #REGION INSTALL
  /**
  * check to see if PC plugin has been installed
  * @return bool
  */
  function CheckForInstall(){
    if(array_key_exists('pcconfig',$_SESSION) &&
       array_key_exists('PCMW_USERKEY',$_SESSION['pcconfig']) &&
       array_key_exists('PCMW_VERSION',$_SESSION['pcconfig']) &&
       trim($_SESSION['pcconfig']['PCMW_USERKEY']) != '' &&
       get_option('PCPlugin_Activation')){
        return TRUE;
    }
    PCMW_Abstraction::Get()->AddUserMSG( 'Installation failed, or PCMegaworks has become corrupted. Attempting re-install. Please re-install it to continue usage if this message appears again. ['.__LINE__.']',1);
    return FALSE;
  }

  /**
  * install core functionality of the plugin, so that
  * features can be added as needed
  * @return bool
  */
  function InstallCoreFeatures(){
  if(session_id() == '' || !isset($_SESSION))
        // We need session here
    session_start();
    $strInstallation = 'Begin installation....<br />';
    //in case this was left set
    unset($_SESSION['pcconfig']);
    if($this->CreateConfigValues()){
      $strInstallation .= 'Configuration data created...<br />';
      //make our fresh install
      if($this->MakeUpdateCall(TRUE)){
        $strInstallation .= 'Tables created...<br />';
        if($this->MakeAdminGroup()){
            $strInstallation .= 'Admin groups created...<br />';
            if($this->MakeAdminUser()){
              $strInstallation .= 'Admin users created...<br />';
              PCMW_Abstraction::Get()->AddUserMSG( 'Installation complete!...<br />'.$strInstallation.' ['.__LINE__.']',3);
              PCMW_Abstraction::Get()->RedirectUser('/wp-admin/admin.php?page=PCPluginAdmin');
              return TRUE;
            }
            else{
              PCMW_Abstraction::Get()->AddUserMSG( 'Cannot create admin users...<br />'.$strInstallation.' ['.__LINE__.']',1);
              $strInstallation .= 'Cannot create admin users ['.__LINE__.']'."\r\n";
              PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
              $this->GetTableDrops();
              //send PC failure description.
              $arrrFailureData = PCMW_Abstraction::Get()->GatherDebugData();
              $arrrFailureData['failuredate'] = $strInstallation;
              $this->SendFailedInstallData($arrrFailureData);
              return FALSE;
            }
        }
        else{
          PCMW_Abstraction::Get()->AddUserMSG( 'Cannot create admin groups...<br />'.$strInstallation.' ['.__LINE__.']',1);
          $strInstallation .= 'Cannot create admin groups ['.__LINE__.']'."\r\n";
          PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
          $this->GetTableDrops();
          //send PC failure description.
          $arrrFailureData = PCMW_Abstraction::Get()->GatherDebugData();
          $arrrFailureData['failuredate'] = $strInstallation;
          $this->SendFailedInstallData($arrrFailureData);
          return FALSE;
        }
      }
      PCMW_Abstraction::Get()->AddUserMSG( 'Cannot create tables...<br />'.$strInstallation.' ['.__LINE__.']',1);
      $strInstallation .= 'Cannot create tables['.__LINE__.']'."\r\n";
      PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      $this->GetTableDrops();
      //send PC failure description.
      $arrrFailureData = PCMW_Abstraction::Get()->GatherDebugData();
      $arrrFailureData['failuredate'] = $strInstallation;
      $this->SendFailedInstallData($arrrFailureData);
      return FALSE;
    }
    else{
        PCMW_Abstraction::Get()->AddUserMSG( 'Cannot create config values...<br />'.$strInstallation.' ['.__LINE__.']',3);
        $strInstallation .= 'Cannot create config values ['.__LINE__.']'."\r\n";
        PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
        //send PC failure description.
        $arrrFailureData = PCMW_Abstraction::Get()->GatherDebugData();
        $arrrFailureData['failuredate'] = $strInstallation;
        $this->SendFailedInstallData($arrrFailureData);
        return FALSE;
    }
    //in case somehow this happens
    $this->SendFailedInstallData($arrrFailureData);
    return FALSE;
  }

  /**
  * install the map support
  * @return bool
  */
  function InstallPCMaps(){
   if($this->InstallFeature('maps')){
    PCMW_Logger::Debug('Maps installed. METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
    PCMW_Abstraction::Get()->AddUserMSG( 'Maps installed correctly. ['.__LINE__.']',3);
    return TRUE;
   }
   else{
    $arrInstallation = error_get_last();
    $strInstallation = var_export($arrInstallation,TRUE);
    PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
    PCMW_Abstraction::Get()->AddUserMSG( 'Maps Not installed correctly. ['.__LINE__.']',3);
    return FALSE;
   }  
  }

  /**
  * uninstall PC Maps
  * @return bool
  */
  function UnInstallPCMaps(){
    $this->RemoveTableUpdateIds('maps');
    if(PCMW_Database::Get()->DropTable('vendors')){
        if(PCMW_Database::Get()->DropTable('mapgroups'))
            return PCMW_Database::Get()->DropTable('mapgrouplink');
    }
    return FALSE;
  }

  /**
  * install 404 redirect support
  * @return bool
  */
  function Install404Redirect(){
   if($this->InstallFeature('404redirects')){
    PCMW_Logger::Debug('404 redirects installed. METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
    PCMW_Abstraction::Get()->AddUserMSG( '404 redirects installed correctly. ['.__LINE__.']',3);
    return TRUE;
   }
   else{
    $arrInstallation = error_get_last();
    $strInstallation = var_export($arrInstallation,TRUE);
    PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
    PCMW_Abstraction::Get()->AddUserMSG( '404 redirects Not installed correctly. ['.__LINE__.']',3);
    return FALSE;
   }
  }

  /**
  * uninstall 404 redirect
  * @return bool
  */
  function UnInstall404Redirect(){
    $this->RemoveTableUpdateIds('404redirects');
    return PCMW_Database::Get()->DropTable('404redirects');
  }

  /**
  * install video access
  * @return bool
  */
  function InstallVideoAccess(){
   if($this->InstallFeature('video-access')){
    PCMW_Logger::Debug('Video Access installed. METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
    PCMW_Abstraction::Get()->AddUserMSG( 'Video Access installed correctly. ['.__LINE__.']',3);
    return TRUE;
   }
   else{
    $arrInstallation = error_get_last();
    $strInstallation = var_export($arrInstallation,TRUE);
    PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
    PCMW_Abstraction::Get()->AddUserMSG( 'Video Access Not installed correctly. ['.__LINE__.']',3);
    return FALSE;
   }
  }

  /**
  * uninstall video access
  * @return bool
  */
  function UnInstallVideoAccess(){
    $this->RemoveTableUpdateIds('video-access');
    return PCMW_Database::Get()->DropTable('videoaccess');
  }

  /**
  * install mail blast capability
  * @return bool
  */
  function InstallMailBlast(){
   if($this->InstallFeature('mailblast')){
    PCMW_Logger::Debug('Mail blast installed. METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
    PCMW_Abstraction::Get()->AddUserMSG( 'Mail blast installed correctly. ['.__LINE__.']',3);
    return TRUE;
   }
   else{
    $arrInstallation = error_get_last();
    $strInstallation = var_export($arrInstallation,TRUE);
    PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
    PCMW_Abstraction::Get()->AddUserMSG( 'Mail blast Not installed correctly. ['.__LINE__.']',3);
    return FALSE;
   }
  }

  /**
  * uninstall mail blast
  * @return bool
  */
  function UnInstallMailBlast(){
    $this->RemoveTableUpdateIds('mailblast');
    return PCMW_Database::Get()->DropTable('mailblast');
  }

  /**
  * install basic chat capability
  * @return bool
  */
  function InstallBasicChat(){
   if($this->InstallFeature('basicchat')){
    PCMW_Logger::Debug('Basic chat installed. METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
    PCMW_Abstraction::Get()->AddUserMSG( 'Basic chat installed correctly. ['.__LINE__.']',3);
    return TRUE;
   }
   else{
    $arrInstallation = error_get_last();
    $strInstallation = var_export($arrInstallation,TRUE);
    PCMW_Logger::Debug($strInstallation.' METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
    PCMW_Abstraction::Get()->AddUserMSG( 'Basic chat Not installed correctly. ['.__LINE__.']',3);
    return FALSE;
   }
  }

  /**
  * uninstall basic chat
  * @return bool
  */
  function UnInstallBasicChat(){
    $this->RemoveTableUpdateIds('basicchat');
    PCMW_Database::Get()->DropTable('chatmessage');
    return PCMW_Database::Get()->DropTable('chatsession');
  }

  /**
  * given a feature name, get the update ID's and remove the table creation specific updates from the list
  * @param $strFeatureName
  * @return bool
  */
  function RemoveTableUpdateIds($strFeatureName){
    $arrFeatures = $this->GetFeatureData($strFeatureName);
    $strPreviousUpdates = get_option('PCMW_updates');
    $arrPreviousUpdates = json_decode($strPreviousUpdates,TRUE);
    if($arrFeatures && array_key_exists('sqltables',$arrFeatures) && is_array($arrFeatures['sqltables'])){
     foreach($arrFeatures['sqltables'] as $intUpdateId=>$arrTable){
       if(array_key_exists('tabledelta',$arrTable) && trim($arrTable['tabledelta']) != ''){
         unset($arrPreviousUpdates[$intUpdateId]);
       }
     }
    }
    update_option( 'PCMW_updates', json_encode($arrPreviousUpdates),NULL,'no' );
    return TRUE;
  }

  /**
  * check for updates for this version
  * @param $strLastTable - last table installed when updating group tables
  * @param $boolDebugRemoval - use debugging to capture removal errors
  * @return string ( JSON )
  */
  function GetTableDrops($strLastTable='',$boolDebugRemoval=TRUE){
    //@TODO: make FTP and SQL updates
   $arrRequest = array('purpose'=>'gettabledrops','version'=>$_SESSION['pcconfig']['PCMW_VERSION'],'userkey'=>$_SESSION['pcconfig']['PCMW_USERKEY']);
   if($arrPayLoad = PCMW_HostRequest::Get()->MakeHostRequest($arrRequest)){
        $strPayLoad = var_export($arrPayLoad,TRUE);
        //which tables do we want to remove?
        if($boolDebugRemoval)
        PCMW_Logger::Debug('Installation failed, removing tables:'."\r\n".'['.$strPayLoad.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
    if($arrPayLoad['payload']){
      //$arrDropTables =
      //this is the table that failed install
      if(trim($strLastTable) != '')
          PCMW_Abstraction::Get()->AddUserMSG( 'Could NOT add table ['.$strLastTable.'] Please run the installation again, or contact the helpdesk. ['.__LINE__.']',1);
      $strFailMessages = '';
      $strSuccessMessages = '';
      //remove these tables now
      foreach($arrPayLoad['payload'] as $strTableName){
        if(PCMW_Database::Get()->CheckForTable($strTableName)){
          if(PCMW_Database::Get()->DropTable($strTableName))
            $strSuccessMessages .= 'Removed Table ['.$strTableName.']'."\r\n";
          else
            $strFailMessages .='Could NOT remove table ['.$strTableName.']'."\r\n";
        }
      }
      //do we have anything to add?
      if(trim($strSuccessMessages) != ''){//$strSuccessMessages
        PCMW_Abstraction::Get()->AddUserMSG( $strSuccessMessages.' ['.__LINE__.']',3);
      }
      if(trim($strFailMessages) != ''){//$strFailMessages
        PCMW_Abstraction::Get()->AddUserMSG( $strFailMessages.' ['.__LINE__.']',1);
        if($boolDebugRemoval)
            PCMW_Logger::Debug($strFailMessages.' ['.__LINE__.']',1);
      }
    }
   }
   return FALSE;
  }


  /**
  * Make the primary admin group
  * @return bool
  */
  function MakeAdminGroup(){
   if($arrAccessLevels = PCMW_StaticArrays::Get()->LoadStaticArrayType('accesslevels',FALSE,0,FALSE,0,'',FALSE)){
     foreach($arrAccessLevels as $intLevel=>$arrLevelData){
       $objAdminGroup = new PCMW_AdminGroup();
       $objAdminGroup->intAdminGroupId = $intLevel;
       $objAdminGroup->strGroupName = $arrLevelData[0];
       $objAdminGroup->intGroupStatus = PCMW_USERSUSPENDED;
       if($intLevel > PCMW_SUSPENDED)
        $objAdminGroup->intGroupStatus = PCMW_USERREAD;
       if($intLevel > PCMW_PREMIUMUSER)
        $objAdminGroup->intGroupStatus = PCMW_USERREADWRITE;
       if($intLevel > PCMW_MODERATOR)
        $objAdminGroup->intGroupStatus = PCMW_USERADMIN;
       $objAdminGroup->intClientId = 1;
       PCMW_AdminUserCore::Get()->InsertAdminGroup($objAdminGroup,TRUE);
     }
     return TRUE;
   }
  }


  /**
  * create the admin users table
  * @param $boolFreshInstall flag to get fresh install updates
  * @return bool
  */
  function CheckPluginVersionUpdate($boolFreshInstall=TRUE){
    //load our previous version for comparison
    // fresh install requires all updates for all time, for now
    $strVersion = ($boolFreshInstall)? '001.000.000':$_SESSION['pcconfig']['PCMW_PREVIOUSVERSION'] ;
   $arrRequest = array('purpose'=>'versionupdated',
                       'version'=>$strVersion,
                       'userkey'=>$_SESSION['pcconfig']['PCMW_USERKEY'],
                       'listedversion'=>$_SESSION['pcconfig']['PCMW_VERSION'],
                       'address'=>get_site_url().'/');
   if($arrPayLoad = PCMW_HostRequest::Get()->MakeHostRequest($arrRequest)){
    return $arrPayLoad['payload'];
   }
   return FALSE;
  }

  /**
  * given a feature group, get the SQL and install it
  * @param $strFeatureGroup
  * @return string results
  */
  function GetFeatureData($strFeatureGroup){
   $arrRequest = array('purpose'=>'getfeature',
                       'feature'=>$strFeatureGroup,
                       'userkey'=>$_SESSION['pcconfig']['PCMW_USERKEY'],
                       'address'=>get_site_url().'/');
   if($arrPayLoad = PCMW_HostRequest::Get()->MakeHostRequest($arrRequest))
    return $arrPayLoad['payload'];
   return FALSE;
  }

  /**
  * send failed install data
  * @param $strFailureData
  * @return string results
  */
  function SendFailedInstallData($arrrFailureData){
   $arrRequest = array('purpose'=>'sendfailuredata',
                       'faildata'=>json_encode($arrrFailureData),
                       'userkey'=>$_SESSION['pcconfig']['PCMW_USERKEY'],
                       'address'=>get_site_url().'/');
   if($arrPayLoad = PCMW_HostRequest::Get()->MakeHostRequest($arrRequest))
    return $arrPayLoad['payload'];
   return FALSE;
  }

  /**
  * make the primary admin user
  * @return bool
  */
  function MakeAdminUser(){
   if(PCMW_AdminUserCore::GetAdminUserId(0,get_current_user_id()))
    return TRUE;
   $objAdminUser = new PCMW_AdminUser();
   $objAdminUser->intUserId = get_current_user_id();
   $objAdminUser->intHandlerId = get_current_user_id();
   $objAdminUser->intCustomerId = get_current_user_id();
   $objAdminUser->intAdminGroupId = PCMW_SUPERUSERS;
   $objAdminUser->intStatus = 20;
   $arrPOST = $objAdminUser->LoadArrayWithObject();
   $objFormManager = new PCMW_FormManager();
   return PCMW_AdminUserCore::Get()->CleanAndInsertAdminUser($objAdminUser,$arrPOST,$objFormManager,'');
  }


  /**
  * create config data for plugins
  * @return bool
  */
  function CreateConfigValues(){
    global $current_user;
    $arrConfig = array();
    $strHTTP = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')? 'http://': 'https://';
    $arrConfig['PCMW_SERVERADDRESS'] = get_site_url().'/';
    $arrConfig['PCMW_SERVERPATH'] = $_SERVER['DOCUMENT_ROOT'];
    $arrConfig['PCPLUGINACTIVE'] = 1;
    $arrConfig['PCMW_DEBUG_ARG'] = 1;
    $arrConfig['PCMW_SITENAME'] = get_bloginfo();
    $arrConfig['PCMW_COMPANYNAME'] = get_bloginfo();
    $arrConfig['PCMW_ADMIN'] = $current_user->user_email;
    $arrConfig['PCMW_SALES'] = $current_user->user_email;
    $arrConfig['PCMW_LOGINPAGE'] = 'login';
    $arrConfig['PCMW_REGISTRATIONPAGE'] = 'register';
    $arrConfig['PCMW_LOGO'] = get_header_image();
    //get the user key
    $arrRequest = array('purpose'=>'userkey','address'=>get_site_url().'/','version'=>'0');
    if($arrPayLoad = PCMW_HostRequest::Get()->MakeHostRequest($arrRequest)){
        $arrConfig['PCMW_USERKEY'] = $arrPayLoad['payload']['userkey'];
        $arrConfig['PCMW_CANHELPDESK'] = $arrPayLoad['payload']['canhelpdesk'];
        $arrConfig['PCMW_VERSION'] = $arrPayLoad['payload']['version'];
    }
    $strPCConfig = PCMW_Utility::Get()->JSONEncode($arrConfig);
    if(update_option( 'PCPlugin', $strPCConfig,NULL,'yes' )){
        $_SESSION['pcconfig'] = $arrConfig;
    }
    else{
      $strSession = var_export($_SESSION,TRUE);
      PCMW_Logger::Debug('$strSession not inserted ['.$strSession.'] FILE ['.__FILE__.'] LINE['.__LINE__.']',1);
        //send PC failure description.
        $arrrFailureData = PCMW_Abstraction::Get()->GatherDebugData();
        $arrrFailureData['failuredate'] = $strSession;
        $this->SendFailedInstallData($arrrFailureData);
    }
    return TRUE;
  }

  /**
  * update the config record, assuming a change was made
  * @return bool
  */
  function UpdatePCConfig(){
    $strPCConfig = PCMW_Utility::Get()->JSONEncode($_SESSION['pcconfig']);
    if(!is_admin())
        return TRUE;
    if(!update_option( 'PCPlugin', $strPCConfig,NULL,'yes' )){
        PCMW_Abstraction::Get()->AddUserMSG( 'Settings not updated! ['.__LINE__.']',1);
        return FALSE;
    }
    else{
        PCMW_Abstraction::Get()->AddUserMSG( 'Settings Updated! ['.__LINE__.']',3);
        return TRUE;
    }
  }

  /**
  * get the config values and load them into sessionif they do not exist
  * @return bool
  */
  function LoadConfigFromStorage(){
    if(!array_key_exists('pcconfig',$_SESSION)){
      $_SESSION['pcconfig'] = PCMW_Utility::Get()->JSONDecode(get_option('PCPlugin'));
    }
    return TRUE;
  }

  /**
  * uninstall the application
  */
  function UninstallPCPlugin($dirPath=''){
   //remove database tables
   $this->GetTableDrops('',FALSE);
   //remove files
   if(trim($dirPath) == '')
    $dirPath = substr(dirname(__FILE__), 0, strrpos( dirname(__FILE__), '/'));
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            self::UninstallPCPlugin($file);
        } else {
            unlink($file);
        }
    }
    if(!rmdir($dirPath))
        echo 'Cannot remove directory ['.$dirPath.']';
    //remove user
    unset($_SESSION['CURRENTUSER']);
    //remove plugin data
    unset($_SESSION['pcconfig']);
    //remove PCConfig option
    delete_option('PCPlugin');
    //remove active or inactive state
    delete_option('PCPlugin_Activation');
    //all done, exit;
    return ('0aluPlugin removed!');
  }

  /**
  * On plugin update, check the server for updated database delta data, and update the version
  * @return bool
  */
  function UpdatePluginVersion($boolFreshInstall=TRUE){
    //if(!$boolFreshInstall){
      $arrPluginDetails = get_plugin_data( dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'PCMegaworks.php');
      //update our local version first
      $_SESSION['pcconfig']['PCMW_PREVIOUSVERSION'] = $_SESSION['pcconfig']['PCMW_VERSION'];
      $_SESSION['pcconfig']['PCMW_VERSION'] = $arrPluginDetails['Version'];
    //}
    $this->UpdatePCConfig();
    return $this->MakeUpdateCall($boolFreshInstall);
  }

  /**
  * make the version update
  * @param $boolFreshInstall
  * @return bool
  */
  function MakeUpdateCall($boolFreshInstall){
    //update our PC record and get updates for the difference
    $arrUpdateData = $this->CheckPluginVersionUpdate($boolFreshInstall);
    return $this->ExecuteUpdateResults($arrUpdateData);
  }

  /**
  * get a specific update and install it
  * @param $strUpdateGroup - group to request all SQL updates for
  * @return bool
  */
  function InstallFeature($strFeatureGroup){
   if(trim($strFeatureGroup) == '')
    return FALSE;
   $strPreviousFeatures = get_option('PCMW_features');
   $arrPreviousFeatures = json_decode($strPreviousFeatures,TRUE);
   $boolTablesOnly = FALSE;
   if(array_key_exists($strFeatureGroup,$arrPreviousFeatures) && (int)$arrPreviousFeatures[$strFeatureGroup] < 1)
    $boolTablesOnly = TRUE;
   //has this feature been installed?
   if(!array_key_exists($strFeatureGroup,$arrPreviousFeatures) || $boolTablesOnly){
     //get the feature data
     $arrFeaturesData = $this->GetFeatureData($strFeatureGroup);
     //install our feature
     if($this->ExecuteUpdateResults($arrFeaturesData,$boolTablesOnly)){
      return TRUE;
     }
     else{//this feature could not be installed
     $strFeaturesData = var_export($arrFeaturesData,TRUE);
     PCMW_Logger::Debug('$strFeaturesData ['.$strFeaturesData.']  METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
        //send PC failure description.
        $arrrFailureData = PCMW_Abstraction::Get()->GatherDebugData();
        $arrrFailureData['failuredate'] = $strFeaturesData;
        $this->SendFailedInstallData($arrrFailureData);
        return FALSE;
     }
   }
   else{//do not overwrite feature data
      $strFeaturesData = var_export($arrFeaturesData,TRUE);
      PCMW_Logger::Debug('$strFeaturesData ['.$strFeaturesData.']  METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
        //send PC failure description.
        $arrrFailureData = PCMW_Abstraction::Get()->GatherDebugData();
        $arrrFailureData['failuredate'] = $strFeaturesData;
        $this->SendFailedInstallData($arrrFailureData);
      return FALSE;
   }
  }

  /**
  * given an update set, execute it
  * @param $arrUpdateData CURL response
  * @param $boolTablesOnly
  * @return bool
  */
  function ExecuteUpdateResults($arrUpdateData,$boolTablesOnly=FALSE){
    //get our previous updates
    $strPreviousUpdates = get_option('PCMW_updates');
    if($strPreviousUpdates)
        $arrPreviousUpdates = json_decode($strPreviousUpdates,TRUE);
    else
        $arrPreviousUpdates = array();
    $boolSuccess = FALSE;
    //let's see if there are any tables to update
    if($arrUpdateData && array_key_exists('sqltables',$arrUpdateData) && is_array($arrUpdateData['sqltables'])){
     foreach($arrUpdateData['sqltables'] as $intUpdateId=>$arrTable){
       if(array_key_exists($intUpdateId,$arrPreviousUpdates))
        continue 1;
       if(array_key_exists('tabledelta',$arrTable) && trim($arrTable['tabledelta']) != ''){
         if(substr($arrTable['tabledelta'],0,6) == 'CREATE' && PCMW_Database::Get()->CheckForTable($arrTable['tablename']))
            continue 1;//we have this table already
         if(PCMW_Database::Get()->CreateTable(urldecode(urldecode($arrTable['tabledelta'])))){
            //update our update list
            $arrPreviousUpdates[$intUpdateId] = $intUpdateId;
            PCMW_Logger::Debug('Table ['.$arrTable['tablename'].'] updated. METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
         }
         else{
            PCMW_Logger::Debug('Table ['.$arrTable['tablename'].'] NOT updated! METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
         }
       }
     }
     //run it again for table delta
     foreach($arrUpdateData['sqltables'] as $intUpdateId=>$arrTable){
       if(array_key_exists($intUpdateId,$arrPreviousUpdates))
        continue 1;
       //run data update
       if(!$boolTablesOnly && array_key_exists('datadelta',$arrTable) && trim($arrTable['datadelta']) != ''){
         if(PCMW_Database::Get()->RunRawQuery(urldecode(urldecode($arrTable['datadelta'])))){
            //update our update list
            $arrPreviousUpdates[$intUpdateId] = $intUpdateId;
            PCMW_Logger::Debug('Table ['.$arrTable['tablename'].'] data updated. METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
         }
         else
            PCMW_Logger::Debug('Table ['.$arrTable['tablename'].'] data NOT updated! METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
       }
     }
    }
    else{
       //do we have a reason?
       if(array_key_exists('errormsg',$arrUpdateData)){
        PCMW_Abstraction::Get()->AddUserMSG( $arrUpdateData['errormsg'].' ['.__LINE__.']',1);
        PCMW_Logger::Debug($arrUpdateData['errormsg'].' [data NOT updated!] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
       }
       return FALSE;
    }
    //update our options
    update_option( 'PCMW_updates', json_encode($arrPreviousUpdates),NULL,'no' );
    return TRUE;
  }

  /**
  * install, or disable a page
  * @param $strPageName
  * @param $strPageContent
  * @param $boolActivate
  * @return bool
  */
  function AddSupportingPage($strPageName,$strPageContent,$boolActivate=TRUE){
    if($boolActivate){
      if(!($objMapsPage = get_page_by_title( $strPageName ))){
          $arrMapsPage = array();
          $arrMapsPage['post_title'] = $strPageName;
          $arrMapsPage['post_content'] = $strPageContent;
          $arrMapsPage['post_status'] = 'publish';
          $arrMapsPage['post_type'] = 'page';
          $arrMapsPage['comment_status'] = 'closed';
          $arrMapsPage['ping_status'] = 'closed';
          $arrMapsPage['post_category'] = array(1);
          $arrMapsPage['meta_input'] = array('_wp_page_template'=>'page-templates/full-width.php');
          // Insert the post into the database
          $intMapsPageid = wp_insert_post( $arrMapsPage );
          }
      else {
          $intMapsPageid = $objMapsPage->ID;
          $objMapsPage->post_status = 'publish';
          $intMapsPageid = wp_update_post( $objMapsPage );

      }
    }
    else{
      if(($objMapsPage = get_page_by_title( $strPageName ))){
          $intMapsPageid = $objMapsPage->ID;
          $objMapsPage->post_status = 'draft';
          $intMapsPageid = wp_update_post( $objMapsPage );
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