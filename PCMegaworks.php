<?php
/*/
 * @link              http://progressivecoding.net
 * @since             000.001.002
 * @package           PCMegaworks
 * @wordpress-plugin
 * Plugin Name:       PCMegaworks
 * Plugin URI:        http://progressivecoding.net/WP/
 * Description:       A simple consolidation of commonly used functionality all in one place. One plugin that does all the simple things.
 * License:           GPL-2.0+
 * Version:           001.034.035
 * Author:            Trey Melton
 * Author URI:        http://progressivecoding.net
 * Text Domain:       progressivecoding
 * Domain Path:       /PCMegaworks
 /*/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
if(session_id() == '' || !isset($_SESSION))
      // We need session here
  session_start();
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCPluginHeader.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_AS.php');
register_activation_hook( __FILE__, 'PCMW_ActivatePCPlugin' );
register_deactivation_hook( __FILE__, 'PCMW_DeActivatePCPlugin' );
register_uninstall_hook(    __FILE__, 'PCMW_UninstallPCPlugin' );
//load our config if not loaded yet
PCPluginInstall::Get()->LoadConfigFromStorage();
//make sure we have a serveraddress
if(get_option('PCPlugin_Activation') && @(int)$_SESSION['pcconfig']['PCPLUGINACTIVE'] > 0){
  if(PCPluginInstall::Get()->CheckForInstall())
      RegisterShortCodes();
}
  /**
  * register the shortcodes
  * @return bool
  */
  function RegisterShortCodes(){
    //register our hooks
    PCPluginCore::Get()->PCMW_SpecialHookRegister();
    //===========================================================================
    //=========              shortcode functions                         ========
    //=========          add the shortcodes for interfacing              ========
    //===========================================================================
    add_shortcode( 'PCLogin', 'PCMW_GetLoginForm' );
    add_shortcode( 'makePCmap', 'PCMW_RenderPCMap' );
    add_shortcode( 'makePCform', 'PCMW_RenderPCForm' );
    add_shortcode( 'PCMW_VendorList', 'PCMW_ShowVendorList' );
    add_shortcode( 'PCMW_ContactUs', 'PCMW_GetContactUs' );
    add_shortcode( 'PCMW_HAWD', 'PCMW_GetHAWD' );
    add_shortcode( 'PCMW_Video', 'PCMW_GetVideo' );
    add_shortcode( 'PCMW_Chat', 'PCMW_GetChat' );
    add_shortcode( 'PC_LogOutUser', 'PCMW_LogOutUser' );
    add_shortcode( 'PCMW_MakeMenu', 'PCMW_MakeMenu' );
    return TRUE;
  }

  /**
  * make a menu for testing
  * @return string
  */
  function PCMW_MakeMenu(){                                        
   return PCMW_CustomMenu::Get()->MakeCustomMenu();
  }


  /**
  * get the login form
  * @return ''
  */
  function PCMW_GetLoginForm(){
    return PCPluginCore::Get()->MakeCustomLoginPage();
  }

  /**
  * log a user out
  * @return bool
  */
  function PCMW_LogOutUser(){
    return PCPluginCore::Get()->PCMW_ClearCredentials();
  }

  /**
  * make a map
  * @return strHTML
  */
  function PCMW_RenderPCMap($arrParams){
    $strMap = PCAdminPages::Get()->IncludeHTMLHeader();
    $strMap .= PCMapRender::Get()->RenderMap($arrParams);
    $strMap .= PCAdminPages::Get()->IncludeHTMLFooter();
    return $strMap;
  }

  /**
  * list the vendors
  * @return string
  */
  function PCMW_ShowVendorList($arrParams){
   return PCMW_VendorCore::Get()->GetActiveVendorsList($arrParams);
  }

  /**
  * get a form and display it
  * @param @arrAtrributes
  *   -alias - form alias to allow heirarchical form construction
  *   -id - specific form ID
  *   -type - data to populate the form with for updates
  *   -isform - 1 or 0 indicator that collection is a form
  *   -makesubmit - make a general submit button
  *@return string (HTML)
  */
  function PCMW_RenderPCForm($arrAttributes){
    $arrFormAttributes = array();
    $intFormId = 0;
    $strForm = '';                                     
    if(array_key_exists('alias',$arrAttributes) && trim($arrAttributes['alias']) != '')
        $arrFormAttributes['formalias'] = $arrAttributes['alias'];
    if(array_key_exists('type',$arrAttributes) && trim($arrAttributes['type']) != '')
        $arrFormAttributes['type'] = $arrAttributes['type'];
    if(array_key_exists('isform',$arrAttributes) && trim($arrAttributes['isform']) != '')
        $arrFormAttributes['isform'] = $arrAttributes['isform'];
    if(array_key_exists('makesubmit',$arrAttributes) && trim($arrAttributes['makesubmit']) != '')
        $arrFormAttributes['makesubmit'] = $arrAttributes['makesubmit'];
    if(array_key_exists('id',$arrAttributes) && trim($arrAttributes['id']) != '')
        $intFormId = $arrAttributes['id'];
    //make our header
    $strForm .= PCAdminPages::Get()->IncludeHTMLHeader();
    $strForm .= PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
    //main content
    $strForm .= PCMW_FormManager::Get()->LoadFormGroupByAlias($arrFormAttributes,$intFormId);
    //add the footer
    $strForm .= PCAdminPages::Get()->IncludeHTMLFooter();
    return $strForm;
  }


  /**
  * make the contact us link
  * @param $arrAttributes
  *  text - text to display for the link or button
  *  class - css classname for button or link styling
  *  type - button or link ( default is link )
  * @return string HTML
  */
  function PCMW_GetContactUs($arrAttributes){
    return PCPluginCore::Get()->HandleContactUs($arrAttributes);
  }

  /**
  * make the how are we doing link
  * @param $arrAttributes
  *  text - text to display for the link or button
  *  class - css classname for button or link styling
  *  type - button or link ( default is link )
  * @return string HTML
  */
  function PCMW_GetHAWD($arrAttributes){
    return PCPluginCore::Get()->HandleHowAreWeDoing($arrAttributes);
  }

  /**
  * get the chat interface
  * @return string ( HTML )
  */
  function PCMW_GetChat(){
   $objChatSession = new PCMW_ChatSession();
   $objChatSession->intChatSessionId = 123;
   $strChatContent = 'content here!';
   //set our shortcode call variable
   PCMW_BasicChat::Get()->boolDirectCall = TRUE;
   return PCMW_BasicChat::Get()->CreateChatDisplay();
  }

  /**
  * activate the plugin
  */
  function PCMW_ActivatePCPlugin(){
    PCMW_Logger::Debug('Activating Plugin.... METHOD ['.__FILE__.'] LINE['.__LINE__.']',1);
    $_SESSION['pcconfig']['PCPLUGINACTIVE'] = 1;
    //set our update type
    $boolFreshInstall = FALSE;
    if(!get_option('PCPlugin_Activation')){
      add_option( 'PCPlugin_Activation', time(),NULL,'yes' );
      $boolFreshInstall = TRUE;
    }
    //make sure our homepage matches
    if(@$_SESSION['PCMW_HOMEPAGE'] == '')
      $_SESSION['pcconfig']['PCMW_HOMEPAGE'] = get_home_url();
    if(get_option('PCPlugin_Deactivation')){
        delete_option('PCPlugin_Deactivation');
        $boolFreshInstall = FALSE;//don't get updates for fresh install
    }
    if ( is_admin() ){
    //figure out if we're updating or installing
      if($boolFreshInstall){
        if(!PCPluginInstall::Get()->InstallCoreFeatures()){
            PCMW_Logger::Debug('Cannot install core features. Exiting. METHOD ['.__FILE__.'] LINE['.__LINE__.']',1);
            return FALSE;
        }
      }
      //we have our initial install
      PCMW_Logger::Debug('Plugin activation complete.... METHOD ['.__FILE__.'] LINE['.__LINE__.']',1);
      //check for updates now
      if(PCPluginInstall::Get()->UpdatePluginVersion($boolFreshInstall))
          PCMW_Logger::Debug('Update complete.... METHOD ['.__FILE__.'] LINE['.__LINE__.']',1);
      else
          PCMW_Logger::Debug('Version ['.$_SESSION['pcconfig']['PCMW_VERSION'].'] up to date.... METHOD ['.__FILE__.'] LINE['.__LINE__.']',1);
        //$_SESSION['previouspage'] = '/wp-admin/admin.php?page=PCPluginAdmin';
      return TRUE;
    }
    return FALSE;
  }

  /**
  * deactivate the plugin
  */
  function PCMW_DeActivatePCPlugin(){
    global $pagenow;
    PCMW_Logger::Debug('Deactivating Plugin.... METHOD ['.__FILE__.'] LINE['.__LINE__.']',1);
    $_SESSION['pcconfig']['PCPLUGINACTIVE'] = 0;
    delete_option('PCPlugin_Activation');
    PCPluginInstall::Get()->UpdatePCConfig();
    unset($_SESSION['pcconfig']);
    unset($_SESSION['CURRENTUSER']);
    add_option( 'PCPlugin_Deactivation', time(),NULL,'yes' );
    return TRUE;
  }

  /**
  * deactivate the plugin
  */
  function PCMW_UninstallPCPlugin(){
    delete_option('PCPlugin_Deactivation');
    delete_option('PCMW_updates');
    delete_option('PCMW_features');
    delete_option('PCMW_regcompletetext');
    delete_option('PCMW_regemailtext');
    PCPluginInstall::Get()->UninstallPCPlugin();
    return TRUE;
  }

  /*
  * This will be added in a later version
  */
  function PCMW_GetVideo($arrAttributes){
    $strVideo = PCMW_VideoAccess::Get()->PCMW_GetVideo($arrAttributes);
    return $strVideo;
  }
?>