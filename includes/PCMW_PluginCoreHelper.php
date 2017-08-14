<?php
/**************************************************************************
* @CLASS PCMW_PluginCoreHelper
* @brief Helper calls for PluginCore.
* @REQUIRES:
*  -Database.php
*
**************************************************************************/
class PCMW_PluginCoreHelper{
   //debugging
   var $boolDebugOn = FALSE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_PluginCoreHelper();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
  }

  #REGION UTILITYMETHODS

  /*
  @brief Get the methods available for debugging
  *keep this at the top of the class for easy access and update
  @return array()
  */
  function GetDebugMethods(){
    $arrDebugMethods = array();
    $arrDebugMethods['PCMW_PluginCoreHelper::CLASSMETHOD'] = 1;
    return $arrDebugMethods;
  }

  /**
  * handle login routing by detecting the page and settings
  * @param $strScriptSelf - our detected location
  * @return bool
  */
  function HandleLoginRouting($strScriptSelf){
   $strLoginPage = str_replace('/','',PCMW_LOGINPAGE);
   //are we visiting the custom page by error?
    if((int)PCMW_USECUSTOMLOGIN < 1 &&
      $strScriptSelf == rtrim(PCMW_LOGINPAGE,'/') &&
      trim(PCMW_LOGINPAGE) != '' &&
      trim($strScriptSelf) != ''){
      PCMW_Abstraction::Get()->RedirectUser('wp-login.php');
      exit;
    }
    //is it time to make our login form?
    if($strScriptSelf == $strLoginPage && PCMW_USECUSTOMLOGIN > 0){
      //clean up our GET variables
      $arrPOST = filter_var_array($_POST,FILTER_SANITIZE_STRING);
      if ( is_user_logged_in() && PCMW_Abstraction::Get()->CheckUserStatus()){
          $this->PCMW_DirectUserPostLogin(FALSE,FALSE);
      }
      //create our page if it doesn't exist
      if(!get_page_by_path( $strLoginPage , OBJECT )){
        PCPluginInstall::Get()->AddSupportingPage(PCMW_LOGINPAGE,'[makePCform id="300" makesubmit="0"]');
        //make our logout page
        PCPluginInstall::Get()->AddSupportingPage('logout','[PC_LogOutUser]');
        $this->PCMW_ClearCredentials();
        PCMW_Abstraction::Get()->RedirectUser(PCMW_LOGINPAGE);
        exit;
      }
      return TRUE;
    }
    if($strScriptSelf == 'logout' && PCMW_USECUSTOMLOGIN > 0){
      $this->PCMW_ClearCredentials();
      PCMW_Abstraction::Get()->RedirectUser(PCMW_LOGINPAGE);
      exit;
    }
    //did someone wind up on the wrong page?
    if($strScriptSelf == 'wp-login.php' && (int)PCMW_USECUSTOMLOGIN > 0){
      //$_SESSION['previouspage'] = @$_SERVER['HTTP_REFERER'];
      //clean up our GET variables
      $arrGET = filter_var_array($_GET,FILTER_SANITIZE_STRING);
      if(@($arrGET['action'] == 'logout' || (array_key_exists('loggedout',$arrGET) && trim($arrGET['loggedout']) != ''))){
          //clear credentials
          $this->PCMW_ClearCredentials();
          PCMW_Abstraction::Get()->RedirectUser(PCMW_LOGINPAGE);
          exit;
      }
      else{
        if(PCMW_USECUSTOMLOGIN > 0 && $strScriptSelf != rtrim(PCMW_LOGINPAGE,'/')){
          //clear credentials
          $this->PCMW_ClearCredentials();
          PCMW_Abstraction::Get()->RedirectUser(PCMW_LOGINPAGE);
          exit;
        }
      }
    }
    return TRUE;
  }

  /**
  * direct a user POST login
  * @requires PCMW_POSTLOGINREDIRECT constant in settings
  * @return viod
  */
  function PCMW_DirectUserPostLogin($user_login, $user){
      if (  PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,PCMW_ADMINISTRATOR,FALSE,FALSE) ){
          PCMW_Abstraction::Get()->RedirectUser('/wp-admin');
          exit;
      }
      else{
          PCMW_Abstraction::Get()->RedirectUser();
          exit;
      }
  }

  /**
  * handle login redirects if applicable
  * @return bool
  */
  function HandleLoginRedirects(){
   //make sure we have a page to redirect to, and it's not one of ours
   if(array_key_exists('previouspage',$_SESSION) &&
      trim($_SESSION['previouspage']) != '' &&
      trim($_SESSION['previouspage']) != 'wp-login.php' &&
      trim($_SESSION['previouspage']) != get_site_url().'/'.PCMW_LOGINPAGE &&
      trim($_SESSION['previouspage']) != get_site_url().'/'.'logout/'){
      $strRedirect = $_SESSION['previouspage'];
      unset($_SESSION['previouspage']);
      PCMW_Abstraction::Get()->RedirectUser($strRedirect);
      exit;
   }
   return TRUE;
  }

  /**
  * make sure our plugin DB data is loaded
  * @return bool
  */
  function VerifyUserLoginData(){
    global $current_user;
    //make sure we have everything loaded
    if ( is_user_logged_in() && !PCMW_Abstraction::Get()->CheckUserStatus()) {
      if(!array_key_exists('PCMW_ADMIN',$_SESSION['pcconfig']) || $_SESSION['pcconfig']['PCMW_ADMIN'] == ''){
        $_SESSION['pcconfig']['PCMW_ADMIN'] = $current_user->user_email;
        $_SESSION['pcconfig']['PCMW_SALES'] = $current_user->user_email;
        PCPluginInstall::Get()->UpdatePCConfig();
      }
      $_SESSION['CURRENTUSER'] = array();
      $_SESSION['CURRENTUSER']['WPUSEROBJECT'] = $current_user;
      $_SESSION['CURRENTUSER']['pcgroup'] = PCMW_AdminUserCore::GetAdminUserId(0,get_current_user_id());
      PCPluginCore::Get()->objCurrentUser = $_SESSION['CURRENTUSER'];
       //nothing failed, or needed redirection
       $this->PCMW_DirectUserPostLogin(FALSE,FALSE);
    }
    return TRUE;
  }

  /**
  * handle registration routing by detecting the page and settings
  * @param $strScriptSelf - our detected location
  * @return bool
  */
  function HandleRegistrationRouting($strScriptSelf){
    $strRegistrationPage = str_replace('/','',PCMW_REGISTRATIONPAGE);
    //are we visiting the custom registration page in error?
    if((int)PCMW_USECUSTOMREGISTRATION < 1 &&
      $strScriptSelf == rtrim(PCMW_REGISTRATIONPAGE,'/') &&
      trim(PCMW_REGISTRATIONPAGE) != '' &&
      trim($strScriptSelf) != ''){
      wp_safe_redirect('/wp-login.php?action=register');
      exit;
    }
    //did someone wind up on the wrong page?
    if($strScriptSelf == 'wp-login.php' && PCMW_USECUSTOMREGISTRATION > 0){
      $_SESSION['previouspage'] = @$_SERVER['HTTP_REFERER'];
      //clean up our GET variables
      $arrGET = filter_var_array($_GET,FILTER_SANITIZE_STRING);
      if(@$arrGET['action'] == 'register' && PCMW_USECUSTOMREGISTRATION > 0){
      //create our page if it doesn't exist
        if(!get_page_by_path( $strRegistrationPage , OBJECT )){
          PCPluginInstall::Get()->AddSupportingPage(PCMW_REGISTRATIONPAGE,'[makePCform id="303" makesubmit="0"]');
          PCMW_Abstraction::Get()->RedirectUser(PCMW_REGISTRATIONPAGE);
          exit;
        }
        PCMW_Abstraction::Get()->RedirectUser(PCMW_REGISTRATIONPAGE);
        exit;
      }
    }
    if(PCMW_USECUSTOMREGISTRATION > 0 && !get_page_by_path( $strRegistrationPage , OBJECT )){
      PCPluginInstall::Get()->AddSupportingPage(PCMW_REGISTRATIONPAGE,'[makePCform id="303" makesubmit="0"]');
      PCMW_Abstraction::Get()->RedirectUser(PCMW_REGISTRATIONPAGE);
      exit;
    }
     return TRUE;
  }

  /**
  * handle 404 redirects
  * @param $strScriptSelf - our detected location
  * @return bool
  */
  function Handle404Redirection($strScriptSelf){
    //do we have any redirects stored?
    if(!array_key_exists('PC_Redirects',$_SESSION) && !array_key_exists('PC_noRedirects',$_SESSION)){
      $_SESSION['PC_Redirects'] = PCMW_Abstraction::Get()->Load404Redirects();
    }
    //redirect as needed
    $strCurrentURL = $_SERVER['HTTP_HOST'].'/'.$strScriptSelf;
    $strCurrentURL = rtrim($strCurrentURL,'/');                                                                                             
    if(is_array($_SESSION['PC_Redirects'])){
      if(@array_key_exists($strCurrentURL,$_SESSION['PC_Redirects']) && @trim($_SESSION['PC_Redirects'][$strCurrentURL]) != ''){
        wp_safe_redirect($_SESSION['PC_Redirects'][$strCurrentURL]);
        exit;
      }
      else if(@array_key_exists('http://'.$strCurrentURL,$_SESSION['PC_Redirects']) && @trim($_SESSION['PC_Redirects']['http://'.$strCurrentURL]) != ''){
        wp_safe_redirect($_SESSION['PC_Redirects']['http://'.$strCurrentURL]);
        exit;
      }
      else if(@array_key_exists('https://'.$strCurrentURL,$_SESSION['PC_Redirects']) && @trim($_SESSION['PC_Redirects']['https://'.$strCurrentURL]) != ''){
        wp_safe_redirect($_SESSION['PC_Redirects']['https://'.$strCurrentURL]);
        exit;

      }
      else return TRUE;
    }
    return TRUE;
  }


  /**
  * normalize a url path for local use
  * @param $strURLPart
  * @return $strURLPart
  */
  function NormalizeAddress($strURLPart,$strBaseURL=''){
    if(trim($strBaseURL) == '')
        $strBaseURL = get_site_url().'/';
    if(strpos($strURLPart,$strBaseURL) === FALSE &&
      !stristr($strURLPart,'http')){
        if(strpos($strURLPart,'../') !== FALSE)
            $strURLPart = str_replace('../',(rtrim($strBaseURL,'/').'/'),$strURLPart);
        else
            $strURLPart = rtrim($strBaseURL,'/').'/'.$strURLPart;
      }
    return $strURLPart;
  }

  /**
  * given a style sheet or block, extract images
  * @param $strPageName
  * @param $strCSSAddress
  * @param $strBaseAddress
  * @param $arrSourceArray
  * @param $arrResultArray
  * @return array
  */
  function ExtractImageSources($strPageName,$strCSSAddress,$strBaseAddress,$arrSourceArray,&$arrResultArray){
   //refine our list from nested images
   foreach($arrSourceArray as $strImageParts){
    $strImageParts = str_replace("'","",$strImageParts);
    if(($arrListImages = PCMW_Utility::Get()->MakeSubjectArray($strImageParts,','))){
      //break out our additional arguments
      foreach($arrListImages as $strImageReference){
        if(($arrArgParts = PCMW_Utility::Get()->MakeSubjectArray($strImageReference,' '))){
           $strImageAddress = $this->NormalizeAddress($arrArgParts[0],$strBaseAddress);
           $arrResultArray[$strPageName][$strCSSAddress][$strImageAddress] = PCMW_Utility::Get()->GetURLHeaderHTTP($strImageAddress,TRUE);
         }
         else{
           $strNormalizedAddress = $this->NormalizeAddress($strImageReference,$strBaseAddress);
           $arrResultArray[$strPageName][$strCSSAddress][$strNormalizedAddress] = PCMW_Utility::Get()->GetURLHeaderHTTP($strNormalizedAddress,TRUE);
         }
      }
    }
    else{
      if(($arrArgParts = PCMW_Utility::Get()->MakeSubjectArray($strImageParts,' '))){
         $strImageAddress = $this->NormalizeAddress($arrArgParts[0],$strBaseAddress);
         $arrResultArray[$strPageName][$strCSSAddress][$strImageAddress] = PCMW_Utility::Get()->GetURLHeaderHTTP($strImageAddress,TRUE);
       }
       else{
         $strNormalizedAddress = $this->NormalizeAddress($strImageParts,$strBaseAddress);
         $arrResultArray[$strPageName][$strCSSAddress][$strNormalizedAddress] = PCMW_Utility::Get()->GetURLHeaderHTTP($strNormalizedAddress,TRUE);
       }
    }
   }
    return $arrResultArray;
  }

  /**
  * gather and crawl css files for images
  * @param $objDom
  * @param $strPageName
  * @param $arrResults
  * @return bool
  */
  function ScrapePageForCSS($objDom,$strPageName,&$arrResults){
    //get css
    $arrCSSFiles = $objDom->getElementsByTagName('link');
    foreach($arrCSSFiles as $objLinks) {
        if( strtolower($objLinks->getAttribute('rel')) == "stylesheet" ) {
           $strCSSAddress = $this->NormalizeAddress($objLinks->getAttribute('href'));
           $strBaseAddress = PCMW_Utility::Get()->GetURLDepth($strCSSAddress,2);
           $boolValidFile = PCMW_Utility::Get()->GetURLHeaderHTTP($strCSSAddress);
           if(!$boolValidFile || !($strCSS = file_get_contents($strCSSAddress)))
              continue 1;
           $arrMatches = array();
           preg_match_all('/url\((.+?)\)/i', $strCSS, $arrMatches);
           if(sizeof(($arrImages = preg_replace('/url\((.+?)\)/i', '$1', $arrMatches[0]))) < 1)
              continue 1;
           //add our page since we have images to account for
           $arrResults[$strPageName][$strCSSAddress] = array();
           $this->ExtractImageSources($strPageName,$strCSSAddress,$strBaseAddress,$arrImages,$arrResults);
           //unset it if we don't have any images
           if(sizeof($arrResults[$strPageName][$strCSSAddress]) < 1)
              unset($arrResults[$strPageName][$strCSSAddress]);
        }
    }
    return TRUE;
  }


  /**
  * check raw HTML for images
  * @param $objDom
  * @param $strPageName
  * @param $arrResults
  * @return bool
  */
  function ScrapeRawHTML($objDom,$strPageName,&$arrResults){
    //get the images
    $arrPageImages = $objDom->getElementsByTagName('img');
    $arrResults[$strPageName]['page'] = array();
    foreach ($arrPageImages as $objImage) {
      $strImageAddress = $this->NormalizeAddress($objImage->getAttribute('src'));
      $arrResults[$strPageName]['page'][$strImageAddress] = PCMW_Utility::Get()->GetURLHeaderHTTP($strImageAddress,TRUE);
    }
    //check the style tags too
    $arrPageStyles = $objDom->getElementsByTagName('style');
    foreach ($arrPageStyles as $objStyles) {
      $strStyleContent = $objStyles->nodeValue;
      $arrMatches = array();
      preg_match_all('/url\((.+?)\)/i', $strStyleContent, $arrMatches);
      if(sizeof(($arrImages = preg_replace('/url\((.+?)\)/i', '$1', $arrMatches[0]))) < 1)
         continue 1;
      $this->ExtractImageSources($strPageName,$strPageName,'',$arrImages,$arrResults);
    }
    //unset empty results
    if(sizeof($arrResults[$strPageName]['page']) < 1)
        unset($arrResults[$strPageName]['page']);
    return TRUE;
  }


  /**
  * gven a base URL, get the links and determine if they are valid
  * @param $strURL
  * @param $intDepth
  * @param $arrResults
  * @param $strTag
  * @return bool
  */
  function CrawlPageLinks($strURL, $intDepth = 5,&$arrResults,$strTag='<a>'){
    $arrViewed = array();
    if(($intDepth == 0) or (in_array($strURL, $arrViewed))){
        return;
    }
    $arrCURLResults = PCMW_Utility::Get()->MakeQuickCURL($strURL);
    $varResult = $arrCURLResults['result'];
    $arrHeaders = $arrCURLResults['headers'];
    if( $varResult ){
        $strStrippedPage = strip_tags($varResult, $strTag);
        if($strTag == '<a>')
            preg_match_all("/<a[\s]+[^>]*?href[\s]?=[\s\"\']+"."(.*?)[\"\']+.*?>"."([^<]+|.*?)?<\/a>/", $strStrippedPage, $arrMatches, PREG_SET_ORDER );
        if($strTag == '<img>')
            preg_match('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)([^\s]+(\.(?i)(jpg|png|gif|bmp))$)@', $strStrippedPage, $arrMatches);
        foreach($arrMatches as $arrMatch){
            $strHref = $arrMatch[1];
            $strHostURL = $strHref;
                if (0 !== strpos($strHref, 'http')) {
                    $strPath = '/' . ltrim($strHref, '/');
                    if (extension_loaded('http')) {
                        $strHref = http_build_url($strHref , array('path' => $strPath));
                    } else {
                        $arrParts = parse_url($strHref);
                        $strHref = @$arrParts['scheme'] . '://';
                        if (isset($arrParts['user']) && isset($arrParts['pass'])) {
                            $strHref .= $arrParts['user'] . ':' . $arrParts['pass'] . '@';
                        }
                        $strHref .= @$arrParts['host'];
                        if (isset($arrParts['port'])) {
                            $strHref .= ':' . $arrParts['port'];
                        }
                        $strHref .= $strPath;
                        if(@!is_array($arrResults[$arrParts['host']]))
                            @$arrResults[$arrParts['host']] = array();
                    }
                }
                if(trim($strHref) != ''){
                  $arrSecondaryParts = parse_url($strHref);
                  if(@trim($arrSecondaryParts['host']) != ""){
                    if(@!is_array($arrResults[$arrSecondaryParts['host']]))
                        $arrResults[$arrSecondaryParts['host']] = array();
                    $arrResults[$arrSecondaryParts['host']][$strHref] = $arrHeaders['http_code'];
                  }
                }
                $this->CrawlPageLinks($strHref, ($intDepth - 1), $arrResults,$strTag);
            }
    }
    if(@trim($strHref) != ''){
      $arrThirdParts = parse_url($strHref);
      if(@trim($arrThirdParts['host']) != ""){
        if(!is_array($arrResults[$arrThirdParts['host']]))
            $arrResults[$arrThirdParts['host']] = array();
        $arrResults[$arrThirdParts['host']][$strHref] = $arrHeaders['http_code'];
      }
    }
    return TRUE;
  }

  /**
  * crawl  site for images, and get the results
  * @return array()
  */
  function CrawlPageForImages(){
    $arrPages = get_pages();//get all our WP pages
    $arrResults = array();
    foreach($arrPages as $arrPage){
      if(!($objDom = PCMW_Utility::Get()->LoadDOMObject(get_site_url().'/'.$arrPage->post_name)))
        continue 1;
      $arrResults[get_site_url().'/'.$arrPage->post_name] = array();
      $this->ScrapeRawHTML($objDom,get_site_url().'/'.$arrPage->post_name,$arrResults);
      $this->ScrapePageForCSS($objDom,get_site_url().'/'.$arrPage->post_name,$arrResults);
    }
    return $arrResults;
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
        if($strMethod == "" || ($strMethod != "" && array_key_exists($strMethod,$this->PCMW_GetDebugMethods()))){
          if((int)$this->boolDebugOn == 2)
            PCMW_Abstraction::Get()->AddUserMSG( '$this->strDebugMessage ['.$this->strDebugMessage.'] METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',4);
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