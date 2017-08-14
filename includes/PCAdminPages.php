<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_VendorCore.php');
 /**************************************************************************
 * @CLASS PCAdminPages
 * @brief Admin page helper
 * @REQUIRES:
 *  -PCMW_Database.php
 *
 **************************************************************************/
class PCAdminPages{
   //debugging
   var $boolDebugOn = FALSE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';

   public static function Get(){
        //==== instantiate or retrieve singleton ====
        static $inst = NULL;
        if( $inst == NULL )
            $inst = new PCAdminPages();
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
    $arrDebugMethods['PCAdminPages::CLASSMETHOD'] = 1;
    return $arrDebugMethods;
  }

  #ENDREGION

  #REGION FORMS

  /**
  * make the form manager page
  * -We'll first check to see if we're submitting a form to be saved
  * --if it fails, we package the form data into a curl string repopulate the form
  * --else, we serve the form for modification
  * -Display the form if one is selected for modification
  * @return string ( HTML )
  */
  function MakeFormManager(){
    /*echo '<pre>';
    print_r($_SESSION);

    echo '</pre>'; */
    $strFormManager = $this->IncludeHTMLHeader();
    $arrPOST = filter_var_array($_POST,FILTER_SANITIZE_STRING);
    $arrGET = filter_var_array($_GET,FILTER_SANITIZE_STRING);
    if(@(string)$arrPOST['formid'] < 0)
        $arrPOST['formid'] = $arrPOST['formgroup'];
    //let's check for save data, and for direct submissions
    if(array_key_exists('formaction',$arrPOST) &&
       $arrPOST['formaction'] != "" &&
       wp_verify_nonce($arrPOST['wp_nonce'],$arrPOST['submissionid'])){
      if(!PCMW_FormManager::Get()->HandleFormActions($arrPOST)){
        //let's wrap the data up for sending back
        $arrPOST['dir'] = 'retrynewformelement';
        $strPOSTData = PCMW_Utility::Get()->MakeCurlString($arrPOST);
        $strFormManager .= '  <script language="JavaScript" type="text/javascript">
          /*<![CDATA[*/
            strPOSTData = "'.$strPOSTData.'";
            if(strPOSTData != ""){
                SubmitSelectedString(strPOSTData);
            }
          /*]]>*/
          </script>';
      }
    }
  //get the existing forms, above the admin level for modification
  $arrAllForms = PCMW_Database::Get()->GetFormData(0,300);
  //filter the results
  $arrFormTableData = PCMW_FormManager::Get()->GetFormTableData($arrAllForms);
  $arrFormTableData['tabledescription'] = '<p class="lead">Choose form from form manager OR click new form.</p>';
  //================ Form controls ===================
    //new form control data
    $arrNewFormButton = array('formalias'=>'newformelementbutton',
                          'isform'=>1  ,
                          'makesubmit'=>0,
                          'admingroupid'=>PCMW_MODERATOR,
                          'header'=>'newformelementbutton',
                          'title'=>'newformelementbutton');
    $strNewFormButton = PCMW_FormManager::Get()->LoadFormGroupByAlias($arrNewFormButton);
    //form modification
    $strFormModification = '<h2>Please select a form to modify.</h2>';
    if(array_key_exists('formid',$arrGET) && (int)$arrGET['formid'] > 0){
      //build our control form data
       $arrFormModification = array('formalias'=>'formcontrols',
                                    'isform'=>1  ,
                                    'makesubmit'=>0,
                                    'admingroupid'=>PCMW_MODERATOR,
                                    'formid'=>@$arrGET['formid'],
                                    'header'=>'formcontrols',
                                    'title'=>'formcontrols');
      $strFormModification = PCMW_FormManager::Get()->LoadFormGroupByAlias($arrFormModification);
    }
  //================ /Form controls ===================
    // Add messages
    $strFormManager .=  PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
    $strTableOrder = 'asc';
    $strFormManager .=  PCMW_FormManager::Get()->MakeBootStrapTable($arrFormTableData,$strTableOrder);
    $strFormManager .=  '<div class="row">'.$strNewFormButton;
    $strContentForm = '';
    if(array_key_exists('formid',$arrGET)){
      //================ Form content ===================
      $arrContentForm = $arrPOST;
      $arrContentForm['isform']= 1;
      $arrContentForm['formid'] = $arrGET['formid'];
      $arrContentForm['makesubmit']= 0;
      $arrContentForm['admingroupid']= PCMW_SUSPENDED;
      $strContentForm = PCMW_FormManager::Get()->LoadFormGroupByAlias($arrContentForm,(int)$arrGET['formid']);
      //================ Form content ===================
      $strFormManager .= $strFormModification;
      $arrFormData = PCMW_FormManager::Get()->GetFormData($arrGET['formid']);
      //add form title
      $strFormManager .=  '<div class="panel-heading">['.$arrFormData['formid'].']' . $arrFormData['formname'].' Form </div>';
      $strFormManager .=  '<div class="panel-heading">Shortcode: <h2>[makePCform id="'.$arrGET['formid'].'"] </h2>(Paste this where you want your form to show in a post or page)</div>';
    }
    $strFormManager .=  $strContentForm;
    $strFormManager .=  '</div>';
    $strFormManager .= $this->IncludeHTMLFooter();
    return $this->WrapContentsInPaper($strFormManager, $strHeader='', $strLead='');
  }

  /**
  * make the map manager page
  * @return string ( HTML )
  */
  function MakeMapManager(){
   $arrPOST = filter_var_array($_POST,FILTER_SANITIZE_STRING);
   //if we're getting an id, with or without value, we're inserting or updating
   if(array_key_exists('vendorid',$arrPOST) &&
      wp_verify_nonce($arrPOST['wp_nonce'],$arrPOST['submissionid'])){
     $objVendor = new PCMW_Vendor();
     $objVendor->LoadObjectWithArray($arrPOST);
     $objFormManager = new PCMW_FormManager();
     if(!PCMW_VendorCore::Get()->CleanAndInsertVendor($objVendor,$arrPOST,$objFormManager,''))
        PCMW_Abstraction::Get()->AddUserMSG( 'Location Not Added Or Updated  ['.__LINE__.']',1);
     else
        PCMW_Abstraction::Get()->AddUserMSG( 'Location Added Or Updated  ['.__LINE__.']',3);
   }
   $strMapManager = '<h1>Manage Map Locations</h1>';
   $strMapManager .=  '<input type="button" class="btn input-md" value="Create New Map Location" onclick="GetFormByAlias(\'newmap\',1,1)" />';
   $strMapManager .= $this->MakePCMW_VendorsTable();
   $strMapManager .=  PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
   $strMapManager .= $this->IncludeHTMLFooter();
   return $this->WrapContentsInPaper($strMapManager, 'Manage Map Locations', 'Add, remove, and edit map locations.');
  }

  /**
  * make the map group manager page
  * @return string ( HTML )
  */
  function MakeMapGroupManager(){
   $arrPOST = filter_var_array($_POST,FILTER_SANITIZE_STRING);
   //if we're getting an id, with or without value, we're inserting or updating
   if(array_key_exists('groupid',$arrPOST) &&
      wp_verify_nonce($arrPOST['wp_nonce'],$arrPOST['submissionid'])){
     $arrPOST['groupsettings'] = PCMW_Utility::Get()->JSONEncode($arrPOST);
     $objMapGroup = new PCMW_MapGroup();
     $objMapGroup->LoadObjectWithArray($arrPOST);
     $objFormManager = new PCMW_FormManager();
     if(!PCMW_VendorCore::Get()->CleanAndInsertMapGroup($objMapGroup,$arrPOST,$objFormManager,''))
        PCMW_Abstraction::Get()->AddUserMSG( 'Map Group Not Added Or Updated  ['.__LINE__.']',1);
     else
        PCMW_Abstraction::Get()->AddUserMSG( 'Map Group Added Or Updated  ['.__LINE__.']',3);
   }
   $strMapManager = $this->IncludeHTMLHeader();
   $strMapManager .= '<h1>Manage Map Groups</h1>';
   $strMapManager .=  '<input type="button" class="btn input-md" value="Create New Map Group" onclick="GetFormByAlias(\'mapgroups\',1,1)" />';
   $strMapManager .= $this->MakeMapGroupsTable();
   $strMapManager .=  PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
   return $this->WrapContentsInPaper($strMapManager, 'Manage Map Groups', 'Create groups of maps to be shown as a<br /> cluster and all options for this map group such as directions, sidebars ect.');
  }


  /**
  * make the map group manager page
  * @return string ( HTML )
  */
  function MakeAdminGroupManager(){
   $strMapManager = $this->IncludeHTMLHeader();
   $strMapManager .= '<h1>Manage Admin Users</h1>';
   $strMapManager .= $this->MakeAdminUsersTable();
   $strMapManager .=  PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
   $strMapManager .= $this->IncludeHTMLFooter();
   return $this->WrapContentsInPaper($strMapManager, $strHeader='', $strLead='');
  }

  /**
  * make the video manager page
  * @return string ( HTML )
  */
  function ManageVideos(){
   $strVideoManager = $this->IncludeHTMLHeader();
   $strVideoManager .= '<h1>Manage Video Access</h1>';
   $strVideoManager .=  '<input type="button" class="btn input-md" value="Create New Video" onclick="GetFormByAlias(\'videoaccess\',1,0);" />';
   $strVideoManager .= PCMW_VideoAccess::Get()->MakePCMW_VideoTable();
   $strVideoManager .=  PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
   $strVideoManager .= $this->IncludeHTMLFooter();
   return $this->WrapContentsInPaper($strVideoManager, $strHeader='', $strLead='');
  }

  /**
  * make the mail blast form for user delivery
  * @return string ( HTML )
  */
  function MakeMailBlastForm($arrPOST){
   $strMailBlast = $this->IncludeHTMLHeader();
   $strMailBlast .=  PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
   $strMailBlast .= '<h1>Mail Blast</h1>';
   $strLead = 'Create an HTML mail message and send it!';
   $arrMailBlast = $arrPOST;
   $arrMailBlast['isform']= 1;
   $arrMailBlast['formalias'] = 'mailblast';
   $arrMailBlast['makesubmit']= 0;
   $arrMailBlast['admingroupid']= PCMW_MODERATOR;
   $strMailBlast .= PCMW_FormManager::Get()->LoadFormGroupByAlias($arrMailBlast);
   $strMailBlast .= $this->IncludeHTMLFooter();
   return $this->WrapContentsInPaper($strMailBlast, $strHeader='', $strLead);
  }

  /**
  * Make http 404 redirect options
  * return string ( HTML )
  */
  function Make404RedirectOptions(){
   $str404Redirect = $this->IncludeHTMLHeader();
   $str404Redirect .= '<h1>404 and redirects</h1>';
   $str404Redirect .= '<input type="button" onclick="GetFormByAlias(\'404redirect\',1,0)" value="New Redirect" class="btn" />';
   $str404Redirect .= PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
   //make the table
   $str404Redirect .= PCMW_404Redirect::Get()->MakePCMW_404RedirectTable();
   $str404Redirect .= $this->IncludeHTMLFooter();
   return $this->WrapContentsInPaper($str404Redirect, '404 redirects', 'Create redirection from previous sites, or commonly visited pages that have moved.<br />This accepts the complete URL only, and slugs without the complete url will not be redirected.<br />IE: mysite.mydomain.com/great-post could redirect to http://mysite.mydomain.com/new-post.');
  }

  /**
  * make the config page
  * @return string ( HTML )
  */
  function MakePCConfig($arrPCConfig){
   $strPCConfig = $this->IncludeHTMLHeader();
   $arrPOST = filter_var_array($_POST,FILTER_SANITIZE_STRING);
   $strPCConfig .= PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
   $arrData = json_decode(get_option('PCMW_features'),TRUE);
   $arrPOST = PCMW_Utility::Get()->MergeArrays($_SESSION['pcconfig'],$arrPOST,TRUE);
   $arrData['formalias'] = 'pcconfig';
   $strPCConfig .= PCMW_FormManager::Get()->MakeTabbedForms('configheaders',$arrData,'Configure PC Plugin',$arrPOST);
   $strPCConfig .= $this->IncludeHTMLFooter();
   return $this->WrapContentsInPaper($strPCConfig, 'Configuration', 'Configure PCMegaworks for full functionality.');
  }

  /**
  * create and capture the registration text options
  * return string ( HTML )
  */
  function MakeRegistrationText(){
   $strPCRegistrationText = $this->IncludeHTMLHeader();
   $arrPCRegistrationText = filter_var_array($_POST,FILTER_UNSAFE_RAW);
   PCMW_Register::Get()->SaveRegistrationText($arrPCRegistrationText);
   $arrPCRegistrationText['formalias'] = 'registrationtext';
   $arrPCRegistrationText['isform'] = 1;
   $arrPCRegistrationText['makesubmit'] = 1;
   $arrPCRegistrationText['admingroupid'] = PCMW_MODERATOR;
   $arrPCRegistrationText['header'] = 'Registration text options';
   $arrPCRegistrationText['lead'] = 'Edit the registration landing page and email text. This includes macros for site data, and allows HTML input. To change the email template edit the \'EmailTemplate.txt\' file in the templates folder of the plugin.';
   $strPCRegistrationText .= PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
   $strPCRegistrationText .= PCMW_FormManager::Get()->LoadFormGroupByAlias($arrPCRegistrationText);
   $strPCRegistrationText .= $this->IncludeHTMLFooter();
   return $this->WrapContentsInPaper($strPCRegistrationText, $arrPCRegistrationText['header'], $arrPCRegistrationText['lead']);
  }

  /**
  * make the chat options page
  * @return string ( HTML )
  */
  function MakeChatOptions(){
   $strPCChatOptions = $this->IncludeHTMLHeader();
   $strOptions = get_option('PCMW_ChatOptions');
   $arrPCChatOptions = json_decode($strOptions,TRUE);
   $arrPCChatOptions['formalias'] = 'chatoptions';
   $arrPCChatOptions['isform'] = 1;
   $arrPCChatOptions['makesubmit'] = 0;
   $arrPCChatOptions['formname'] = 'pc_chat_options';
   $arrPCChatOptions['admingroupid'] = PCMW_MODERATOR;
   $arrPCChatOptions['header'] = 'Control the chat experience with these options';
   $arrPCChatOptions['lead'] = 'Add the shortcode "[PCMW_Chat ]" to any page where the chat should show if not on all pages is selected.';
   $strPCChatOptions .= PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
   $strPCChatOptions .= PCMW_FormManager::Get()->LoadFormGroupByAlias($arrPCChatOptions);
   $strPCChatOptions .= $this->IncludeHTMLFooter();
   return $this->WrapContentsInPaper($strPCChatOptions, $arrPCChatOptions['header'], $arrPCChatOptions['lead']);
  }

  /**
  * make the CSS interface page
  * @return string ( HTML )
  */
  function ManageCSSInterface(){
   return 'This feature is not yet available';
   /*$strCSSOptions = $this->IncludeHTMLHeader();
   $arrCSSOptions['formalias'] = 'cssoptions';
   $arrCSSOptions['isform'] = 1;
   $arrCSSOptions['makesubmit'] = 0;
   $arrCSSOptions['formname'] = 'pc_css_options';
   $arrCSSOptions['admingroupid'] = PCMW_MODERATOR;
   //this will eventually contain interface alias options for expanded controls
   // over custom forms
   $arrPOST = filter_var_array($_POST,FILTER_UNSAFE_RAW);
   //temp header and lead until full implementation
   $arrCSSOptions['header'] = 'Control CSS';
   $arrCSSOptions['lead'] = 'Change the styles or classes';
   $strCSSOptions .= PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
   $strCSSOptions .= $this->IncludeHTMLFooter();
   return $this->WrapContentsInPaper($strCSSOptions, $arrCSSOptions['header'], $arrCSSOptions['lead']); */
  }

  /**
  * make the help forms
  * @return ( HTML form )
  */
  function MakeHowToForm(){
   $strPCHowTo = $this->IncludeHTMLHeader();
   $arrPCHowTo['header'] = 'How To\'s';
   $arrPCHowTo['lead'] = 'This feature requires access to the internet. In order to provide up to date information without updating the plugin, we keep the how to\'s on our server.';
   $strPCHowTo .= PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
   $strPCHowTo .= $this->AssembleHowToOptions();
   $strPCHowTo .= $this->IncludeHTMLFooter();
   return $this->WrapContentsInPaper($strPCHowTo, $arrPCHowTo['header'], $arrPCHowTo['lead']);
  }

  /**
  * add the header for page calls
  * @param $boolDirectInclude
  * @return bool || string

  */
  function IncludeHTMLHeader($boolDirectInclude = FALSE,$boolFrontFacing=FALSE){
    $strHeader = '<script language="JavaScript" type="text/javascript"> var PCMW_SERVERADDRESS = "'.get_site_url().'/'.PCMW_PLUGINADDRESS.'"; </script>';
    if($boolDirectInclude){
        echo $strHeader;
        return TRUE;
    }
    else return $strHeader;
  }

  /**
  * add the footer for page calls
  * @param $boolDirectInclude
  * @return bool || string
  */
  function IncludeHTMLFooter($boolDirectInclude = FALSE){
    $strFooter = '<script language="JavaScript" type="text/javascript">
      /*]]>*/
      jQuery(document).ready(function() {
      jQuery(\'a[data-toggle="tab"]\').on( \'shown.bs.tab\', function (e) {
          jQuery.fn.dataTable.tables( {visible: true, api: true} ).columns.adjust();
      } );
      jQuery(\'[data-toggle="tooltip"]\').tooltip({\'placement\': \'top\', \'html\' : \'true\'});
        jQuery("selector").tooltip();
        // popover
      jQuery(\'[data-toggle="popover"]\').popover({trigger: \'hover\',\'placement\': \'top\',\'html\': \'true\'});  
      });
    </script>';
    if($boolDirectInclude){
        echo $strFooter;
        return TRUE;
    }
    else return $strFooter;
  }

  /**
  * wrap the content in the bootstrap paper
  * @param $strContent
  * @return string HTML
  */
  function WrapContentsInPaper($strContent, $strHeader='', $strLead=''){
   $strWrapper = '';
   if(trim($strHeader) != '')
    $strWrapper .= '<h1 class="page-header">'.$strHeader.'</h1>';
   if(trim($strLead) != '')
    $strWrapper .= '<p class="lead">'.$strLead.'</p>';
   $strWrapper .= '<div class="row">';
   $strWrapper .= '<div class="col-md-10 col-md-offset-1 paper paper-curver-vert">';
   $strWrapper .= $strContent;
   $strWrapper .= '</div><!-- /.col-md-10 offset-1 -->';
   $strWrapper .= '</div><!-- /.row -->';
   return $strWrapper;
  }

  /**
  * get the headers and make a selection form out of it for how to's
  * @return string ( HTML )
  */
  function AssembleHowToOptions(){
    $strSubjects = '';
    foreach($this->GetDefaultFeatureHeaders(FALSE) as $strSubject=>$arrSchema){
      $strSubjects .= '<div class="form-group col-md-2 floatleft" style="height:225px;overflow:scroll;text-align:center;" >';
      $strSubjects .= '<a href="JavaScript:GetHowToSubject(\''.$strSubject.'\');" border="0" style="outline : none;" >';
      $strSubjects .= '<i class="fa fa-3x '.$arrSchema['icon'].' fa-fw"></i>';
      $strSubjects .= '<br />';
      $strSubjects .= '<b>'.$arrSchema['title'].'</b>';
      $strSubjects .= '<br />';
      $strSubjects .= '<hr />';
      $strSubjects .= $arrSchema['description'];
      $strSubjects .= '</a>';
      $strSubjects .= '</div>';
    }
    return $strSubjects;
  }

  /**
  * get the default feature options headers
  * @return json encoded string
  */
  function GetDefaultFeatureHeaders($boolOmitShortCodes = TRUE){
    $arrFeatureHeaders = array('pcmw-settings'=>array('title'=>'Settings',
                                                      'feature'=>0,
                                                      'icon'=>'fa-gear',
                                                      'group'=>PCMW_ADMINISTRATOR,
                                                      'description'=>'Change and manage PCMegaworks settings.'));
    if(PCMW_USECUSTOMREGISTRATION > 0){
      $arrFeatureHeaders['pcmw-registration-options'] = array('title'=>'Registration Options',
                                                        'feature'=>0,
                                                        'icon'=>'fa-pencil',
                                                        'group'=>PCMW_ADMINISTRATOR,
                                                        'description'=>'Set the registration email body text and landing page verbiage.');
    }
    $arrFeatureHeaders['manage-pcmw-forms'] = array('title'=>'Manage Forms',
                                                      'feature'=>0,
                                                      'icon'=>'fa-table',
                                                      'group'=>PCMW_ADMINISTRATOR,
                                                      'description'=>'Update, add, and manage simple forms.');
    $arrFeatureHeaders['manage-pcmw-admin-users'] = array('title'=>'Manage Admin Users',
                                                      'feature'=>0,
                                                      'icon'=>'fa-users',
                                                      'group'=>PCMW_ADMINISTRATOR,
                                                      'description'=>'Assign admin user rights.');
    if(!$boolOmitShortCodes)
    $arrFeatureHeaders['pcmw-shortcode-reference'] = array('title'=>'Shortcode Reference',
                                                      'feature'=>0,
                                                      'icon'=>'fa-sticky-note',
                                                      'group'=>PCMW_PREMIUMUSER,
                                                      'description'=>'Shortcode usage instructions.');
    $arrFeatureHeaders['manage-pcmw-maps'] = array('title'=>'Map Options',
                                                      'feature'=>1,
                                                      'icon'=>'fa-map-marker',
                                                      'group'=>PCMW_PREMIUMUSER,
                                                      'description'=>'Add, remove, and edit map locations and groups.<br />');
    $arrFeatureHeaders['pcmw-mail-blast'] = array('title'=>'Mail Blast',
                                                      'feature'=>1,
                                                      'icon'=>'fa-envelope-o',
                                                      'group'=>PCMW_PREMIUMUSER,
                                                      'description'=>'Create a mail blast, or send one.<br /><br />');
    $arrFeatureHeaders['manage-pcmw-404-redirects'] = array('title'=>'404 Redirects',
                                                      'feature'=>1,
                                                      'icon'=>'fa-exchange',
                                                      'group'=>PCMW_PREMIUMUSER,
                                                      'description'=>'Add 404 options for popular pages that are moved, or for new sites where the URL structure is different.');
    $arrFeatureHeaders['video-access'] = array('title'=>'Video Access Control',
                                                      'feature'=>1,
                                                      'icon'=>'fa-film',
                                                      'group'=>PCMW_PREMIUMUSER,
                                                      'description'=>'Control access to videos based on access group.');
    $arrFeatureHeaders['basicchat'] = array('title'=>'Basic Chat',
                                                      'feature'=>1,
                                                      'icon'=>'fa-user-plus',
                                                      'group'=>PCMW_ADMINISTRATOR,
                                                      'description'=>'Use the simple chat interface to communicate with your customers in real time.');

    /*$arrFeatureHeaders['pc_custom_menu'] = array('title'=>'Custom Menu',
                                                      'feature'=>0,
                                                      'icon'=>'fa-navicon',
                                                      'group'=>PCMW_ADMINISTRATOR,
                                                      'description'=>'Use the custom Bootstrapped menu with edit options.');*/
    $arrFeatureHeaders['pcmw-check-links'] = array('title'=>'Check for Broken Links',
                                                      'feature'=>0,
                                                      'icon'=>'fa-link',
                                                      'group'=>PCMW_ADMINISTRATOR,
                                                      'description'=>'Check your site for broken links.');
    $arrFeatureHeaders['pcmw-check-images'] = array('title'=>'Check for Broken Images',
                                                      'feature'=>0,
                                                      'icon'=>'fa-image',
                                                      'group'=>PCMW_ADMINISTRATOR,
                                                      'description'=>'Check your site for images that should be there, but aren\'t.');
    $arrFeatureHeaders['pcmw-feature-request'] = array('title'=>'Feature Request',
                                                      'feature'=>0,
                                                      'icon'=>'fa-road',
                                                      'group'=>PCMW_ADMINISTRATOR,
                                                      'description'=>'Request a feature for future releases.');
    $arrFeatureHeaders['pcmw-helpdesk'] = array('title'=>'Help Desk',
                                                      'feature'=>0,
                                                      'icon'=>'fa-bug',
                                                      'group'=>PCMW_PREMIUMUSER,
                                                      'description'=>'Submit a helpdesk ticket to Progressive Coding.');
    if($boolOmitShortCodes)
      $arrFeatureHeaders['pcmw-howtos'] = array('title'=>'How to\'s',
                                                        'feature'=>0,
                                                        'icon'=>'fa-server',
                                                        'group'=>PCMW_PREMIUMUSER,
                                                        'description'=>'How to make forms, manage users, use shortcodes, 404 redirects, and more!');
    //give it back
    return $arrFeatureHeaders;
  }


  /**
  * make our interface page
  * @return string ( HTML )
  *     -['tabledescription'] = ['tabledescription']
  *     -['tabs']
  *        -['tabdata']
  *          -['tableheader']
  *             -['headerkey'] = ['headername']
  *          -['tabledata']
  *             -['headerkey'] = ['columnvalue']
  *             -['linkvalue'] = ['linkvalue'] || ['onclickvalue'] = ['onclickvalue']
  *        -['tabtitle'] = ['title']
  */
  function MakePCMWInterface(){
    $arrSettingsData = array();
    $strFeatures = get_option('PCMW_features');
    $arrFeatures = json_decode($strFeatures,TRUE);
    $arrSettingsData['tabledescription'] = 'Adjust PC Mega works.';
    $arrSettingsData['tableheader'] = array('manageforms'=>'Manage Forms',
                                            'manageadminusers'=>'Manage Admin Users',
                                            'shortcodes'=>'Short Code reference',
                                            'settings'=>'Settings',
                                            'helpdesk'=>'Help Desk',
                                            'maps'=>'Manage Maps',
                                            '404redirects'=>'404 Redirects',
                                            'mailblast'=>'Mail Blast');
    $arrSettingsData['tabs']['tabdata'] = array();
    return $arrSettingsData;
  }


  /**
  * make our settings page
  * @return string ( HTML )
  *     -['tabledescription'] = ['tabledescription']
  *     -['tabs']
  *        -['tabdata']
  *          -['tableheader']
  *             -['headerkey'] = ['headername']
  *          -['tabledata']
  *             -['headerkey'] = ['columnvalue']
  *             -['linkvalue'] = ['linkvalue'] || ['onclickvalue'] = ['onclickvalue']
  *        -['tabtitle'] = ['title']
  */
  function MakePCMWSettings(){
    $arrSettingsData = array();
    $strFeatures = get_option('PCMW_configoptions');
    $arrFeatures = json_decode($strFeatures,TRUE);
    $arrSettingsData['tabledescription'] = 'Adjust PC Mega Works settings.';
    $arrSettingsData['tableheader'] = array('manageforms'=>'Manage Forms',
                                            'manageadminusers'=>'Manage Admin Users',
                                            'shortcodes'=>'Short Code reference',
                                            'settings'=>'Settings',
                                            'helpdesk'=>'Help Desk',
                                            'maps'=>'Manage Maps',
                                            '404redirects'=>'404 Redirects',
                                            'mailblast'=>'Mail Blast');
    $arrSettingsData['tabs']['tabdata'] = array();
    return $arrSettingsData;
  }

  /**
  * make vendors table
  * @return string HTML
  *     -['tabledescription'] = ['tabledescription']
  *     -['tableheader']
  *         -['headerkey'] = ['headername']
  *     -['tabledata'][unique key]
  *         -['headerkey'] = ['columnvalue']
  *         -['linkvalue'] = ['linkvalue'] || ['onclickvalue'] = ['onclickvalue']
  */
  function MakePCMW_VendorsTable(){
    $arrTableData = array();
    $arrTableData['tabledescription'] = 'Manage Map Locations';
    //define our columns
    $arrTableData['tableheader'] = array('vendorid'=>'ID',
                                         'vendorname'=>'Name',
                                         'vendoraddress'=>'Address',
                                         'vendordescription'=>'Description',
                                         'vendorstatus'=>'Status');
    //get the status list
    $arrStatus = PCMW_StaticArrays::Get()->LoadStaticArrayType('activestatus',FALSE);
    //make the vendor table data
    $arrTableData['tabledata'] = array();
    if(sizeof($arrVendors = PCMW_VendorCore::Get()->GetAllVendors(FALSE,FALSE)) > 0 && is_array($arrVendors)){
      foreach($arrVendors as $arrVendor){
        $arrTableData['tabledata'][$arrVendor['vendorid']] = array();
        foreach($arrTableData['tableheader'] as $strKey=>$strValue){
          $arrTableData['tabledata'][$arrVendor['vendorid']][$strKey] = array();
          if($strKey == 'vendorid'){
            $arrTableData['tabledata'][$arrVendor['vendorid']][$strKey]['linkbadge'] = 'fa fa-1x fa-cog';
            $arrTableData['tabledata'][$arrVendor['vendorid']][$strKey]['linkclass'] = 'btn btn-primary';
            $arrTableData['tabledata'][$arrVendor['vendorid']][$strKey]['onclickvalue'] = 'AddAnonymousAction(\'editmap\','.$arrVendor['vendorid'].');';
            $arrTableData['tabledata'][$arrVendor['vendorid']][$strKey]['value'] = $arrVendor['vendorid'].' Edit';
          }
          else if($strKey == 'vendorstatus')
              @$arrTableData['tabledata'][$arrVendor['vendorid']][$strKey]['value'] = $arrStatus[$arrVendor[$strKey]];
          else
              $arrTableData['tabledata'][$arrVendor['vendorid']][$strKey]['value'] = $arrVendor[$strKey];
        }
      }
    }
    return PCMW_FormManager::Get()->MakeBootStrapTable($arrTableData);
  }

  /**
  * make map groups table
  * @return string HTML
  *     -['tabledescription'] = ['tabledescription']
  *     -['tableheader']
  *         -['headerkey'] = ['headername']
  *     -['tabledata'][unique key]
  *         -['headerkey'] = ['columnvalue']
  *         -['linkvalue'] = ['linkvalue'] || ['onclickvalue'] = ['onclickvalue']
  */
  function MakeMapGroupsTable(){
    $arrTableData = array();
    $arrTableData['tabledescription'] = 'Manage Map Groups';
    //define our columns
    $arrTableData['tableheader'] = array('groupid'=>'Group ID',
                                         'groupname'=>'Name',
                                         'delete'=>'Delete',
                                         'edit'=>'Edit',
                                         'addmaps'=>'Add Maps',
                                         'shortcode'=>'Short Code');
    //make the MapGroups table data
    $arrTableData['tabledata'] = array();
    if(sizeof($arrMapGroups = PCMW_Database::Get()->GetAllMapGroups()) > 0 && is_array($arrMapGroups)){
      foreach($arrMapGroups as $arrMapGroup){
        $arrTableData['tabledata'][$arrMapGroup['groupid']] = array();
        foreach($arrTableData['tableheader'] as $strKey=>$strValue){
          $arrTableData['tabledata'][$arrMapGroup['groupid']][$strKey] = array();
          if($strKey == 'edit'){
            $arrTableData['tabledata'][$arrMapGroup['groupid']][$strKey]['linkbadge'] = 'fa fa-1x fa-cog';
            $arrTableData['tabledata'][$arrMapGroup['groupid']][$strKey]['linkclass'] = 'btn btn-primary';
            $arrTableData['tabledata'][$arrMapGroup['groupid']][$strKey]['onclickvalue'] = 'AddAnonymousAction(\'editmapgroup\','.$arrMapGroup['groupid'].');';
            $arrTableData['tabledata'][$arrMapGroup['groupid']][$strKey]['value'] = 'Edit Group';
          }
          elseif($strKey == 'delete'){
            $arrTableData['tabledata'][$arrMapGroup['groupid']][$strKey]['linkbadge'] = 'fa fa-1x fa-exclamation-triangle';
            $arrTableData['tabledata'][$arrMapGroup['groupid']][$strKey]['linkclass'] = 'btn btn-danger';
            $arrTableData['tabledata'][$arrMapGroup['groupid']][$strKey]['onclickvalue'] = 'DeleteVendor(0,'.$arrMapGroup['groupid'].');';
            $arrTableData['tabledata'][$arrMapGroup['groupid']][$strKey]['value'] = 'Delete';
          }
          else if($strKey == 'addmaps'){
            $arrTableData['tabledata'][$arrMapGroup['groupid']][$strKey]['linkbadge'] = 'fa fa-1x fa-plus';
            $arrTableData['tabledata'][$arrMapGroup['groupid']][$strKey]['linkclass'] = 'btn btn-success';
            $arrTableData['tabledata'][$arrMapGroup['groupid']][$strKey]['onclickvalue'] = 'AddAnonymousAction(\'editmaplinks\','.$arrMapGroup['groupid'].');';
            $arrTableData['tabledata'][$arrMapGroup['groupid']][$strKey]['value'] = 'Add Maps';
          }
          else if($strKey == 'shortcode'){
              $strShortCode = '[makePCmap groupid="'.$arrMapGroup['groupid'].'"]';
              $arrTableData['tabledata'][$arrMapGroup['groupid']][$strKey]['value'] = $strShortCode;
          }
          else
              $arrTableData['tabledata'][$arrMapGroup['groupid']][$strKey]['value'] = $arrMapGroup[$strKey];
        }
      }
    }
    return PCMW_FormManager::Get()->MakeBootStrapTable($arrTableData);
  }


  /**
  * make admin users table
  * @return string HTML
  *     -['tabledescription'] = ['tabledescription']
  *     -['tableheader']
  *         -['headerkey'] = ['headername']
  *     -['tabledata'][unique key]
  *         -['headerkey'] = ['columnvalue']
  *         -['linkvalue'] = ['linkvalue'] || ['onclickvalue'] = ['onclickvalue']

    $blogusers = get_users( 'orderby=nicename' );
    foreach ( $blogusers as $user ) {
	    $strDiv .=  '<span>' . esc_html( $user->user_email ) . '</span>';
    }
  */
  function MakeAdminUsersTable(){
    $arrTableData = array();
    $arrTableData['tabledescription'] = 'Manage Admin Users';
    //define our columns
    $arrTableData['tableheader'] = array('updateuser'=>'Update',
                                         'username'=>'Name',
                                         'email'=>'Email',
                                         'admingroupid'=>'Group',
                                         'mailblast'=>'Mail Blast',
                                         'status'=>'Permissions');
    //get the status list
    $arrAdminUserStatus = PCMW_StaticArrays::Get()->LoadStaticArrayType('adminuserstatus',FALSE,0,TRUE);
    $arrGroupSelectAttributes = array('class'=>'form-control input-lg');
    $arrAccessLevels = PCMW_StaticArrays::Get()->LoadStaticArrayType('accesslevels',FALSE,0,TRUE);
    $arrAccessSelectAttributes = array('class'=>'form-control input-lg');
    //make the vendor table data
    $arrTableData['tabledata'] = array();
    $arrWPUsers = get_users( 'orderby=nicename' );
    $arrAdminUsers = PCMW_AdminUserCore::Get()->GetWPUserAdminGroups($arrWPUsers);
    foreach($arrAdminUsers as $intWPUserId=>$arrAdminUser){
      //get our meta for custom fields
      $arrUserMeta = get_user_meta($arrAdminUser['WPUSEROBJECT']->data->ID);
      //set our field names
      $arrTableData['tabledata'][$intWPUserId] = array();
      $arrAccessSelectAttributes['selectname'] = 'access_'.$intWPUserId;
      $arrGroupSelectAttributes['selectname'] = 'group_'.$intWPUserId;
      //translate our select names to existing values
      $arrAdminUser['access_'.$intWPUserId] = $arrAdminUser['pcgroup']['admingroup'];
      $arrAdminUser['group_'.$intWPUserId] = $arrAdminUser['pcgroup']['status'];
      foreach($arrTableData['tableheader'] as $strKey=>$strValue){
        $arrTableData['tabledata'][$intWPUserId][$strKey] = array();
        $arrTableData['tabledata'][$intWPUserId]['rowid'] = 'rowid_'.$intWPUserId;
        if($strKey == 'updateuser'){
          $arrTableData['tabledata'][$intWPUserId][$strKey]['linkbadge'] = 'fa fa-1x fa-cog';
          //AddAnonymousAction(strAction,intDataId,strDataCollection,strMoreData)
          $arrTableData['tabledata'][$intWPUserId][$strKey]['linkclass'] = 'btn btn-primary';
          $intUniqueID = md5(time().rand(1000,100000));
          $strOnClick = "AddAnonymousAction(";
          $strOnClick .= "'updateuseradmin',";
          $strOnClick .= $intWPUserId.",";
          $strOnClick .= "GEO('access_".$intWPUserId."').value,";
          $strOnClick .= "'wp_nonce=".wp_create_nonce($intUniqueID);
          $strOnClick .= "&submissionid=".$intUniqueID;
          $strOnClick .= "&group='+GEO('group_".$intWPUserId."').value";
          $strOnClick .= "+'&pcmw_mail_blast_".$intWPUserId."='+GEO('pcmw_mail_blast_".$intWPUserId."').checked";
          $strOnClick .= ");";
          $arrTableData['tabledata'][$intWPUserId][$strKey]['onclickvalue'] = $strOnClick;
          $arrTableData['tabledata'][$intWPUserId][$strKey]['value'] = $strValue;
        }
        else if($strKey == 'admingroupid'){
            $strGroupSelect = PCMW_Utility::Get()->MakeSimpleSelectBox($arrAccessLevels,$arrAdminUser,$arrAccessSelectAttributes);
            $arrTableData['tabledata'][$intWPUserId][$strKey]['value'] = $strGroupSelect;
        }
        else if($strKey == 'mailblast'){
            $strChecked = (@(int)$arrUserMeta['pcmw_mail_blast'][0] > 0)? ' CHECKED="true" ':'' ;
            $strMailBlast = '<input type="checkbox" name="pcmw_mail_blast_'.$intWPUserId.'" id="pcmw_mail_blast_'.$intWPUserId.'" '.$strChecked.' />';
            $arrTableData['tabledata'][$intWPUserId][$strKey]['value'] = $strMailBlast;
        }
        else if($strKey == 'status'){
            $strGroupSelect = PCMW_Utility::Get()->MakeSimpleSelectBox($arrAdminUserStatus,$arrAdminUser,$arrGroupSelectAttributes);
            $arrTableData['tabledata'][$intWPUserId][$strKey]['value'] = $strGroupSelect;
        }
        else if($strKey == 'email')
            $arrTableData['tabledata'][$intWPUserId][$strKey]['value'] = $arrAdminUser['WPUSEROBJECT']->user_email;
        else if($strKey == 'username')
            $arrTableData['tabledata'][$intWPUserId][$strKey]['value'] = $arrAdminUser['WPUSEROBJECT']->user_nicename;
        else
            $arrTableData['tabledata'][$intWPUserId][$strKey]['value'] = $arrAdminUser[$strKey];
      }
    }
    return PCMW_FormManager::Get()->MakeBootStrapTable($arrTableData);
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