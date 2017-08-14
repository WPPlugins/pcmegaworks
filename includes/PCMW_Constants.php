<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) ){
  die;
}
//==============================================================================
//================ USE CAUTION WHEN MODIFYING THIS =============================
//==============================================================================
//get this from the stored session            
@define('PCPLUGINACTIVE',$_SESSION['pcconfig']['PCPLUGINACTIVE']);
@define('PCMW_SERVERADDRESS', get_site_url( ).'/' );
if(@trim($_SESSION['pcconfig']['PCMW_SERVERPATH']) != '')
    define('PCMW_SERVERPATH', $_SESSION['pcconfig']['PCMW_SERVERPATH'] );
else
    define('PCMW_SERVERPATH', $_SERVER['DOCUMENT_ROOT'] );
@define('PCMW_LOGO', $_SESSION['pcconfig']['PCMW_LOGO'] );
@define('PCMW_ADMIN',$_SESSION['pcconfig']['PCMW_ADMIN']);
@define('PCMW_SITENAME',$_SESSION['pcconfig']['PCMW_SITENAME']);
@define('PCMW_USERKEY',$_SESSION['pcconfig']['PCMW_USERKEY']);
@define('PCMW_GOOGLEMAPKEY',$_SESSION['pcconfig']['PCMW_GOOGLEMAPKEY']);
@define('PCMW_FTPADDRESS',$_SESSION['pcconfig']['PCMW_FTPADDRESS']);
@define('PCMW_PRODUCTFOLDER',$_SESSION['pcconfig']['PCMW_PRODUCTFOLDER']);
@define('PCMW_VERSION',$_SESSION['pcconfig']['PCMW_VERSION']);
@define('PCMW_HOMEPAGE',$_SESSION['pcconfig']['PCMW_HOMEPAGE']);
@define('PCMW_USECUSTOMLOGIN',$_SESSION['pcconfig']['PCMW_USECUSTOMLOGIN']);
@define('PCMW_LOGINMENUOPTION',$_SESSION['pcconfig']['PCMW_LOGINMENUOPTION']);
@define('PCMW_LOGINPAGE',$_SESSION['pcconfig']['PCMW_LOGINPAGE']);
@define('PCMW_USECUSTOMREGISTRATION',$_SESSION['pcconfig']['PCMW_USECUSTOMREGISTRATION']);
@define('PCMW_REGISTRATIONMENUOPTION',$_SESSION['pcconfig']['PCMW_REGISTRATIONMENUOPTION']);
@define('PCMW_REGISTRATIONPAGE',$_SESSION['pcconfig']['PCMW_REGISTRATIONPAGE']);
@define('PCMW_USECONTACTUS',$_SESSION['pcconfig']['PCMW_USECONTACTUS']);
@define('PCMW_USECUSTOMHAWD',$_SESSION['pcconfig']['PCMW_USECUSTOMHAWD']);
@define('PCMW_DEBUG_ARG',$_SESSION['pcconfig']['PCMW_DEBUG_ARG']);
@define('PCMW_RESTRICTPAGES',$_SESSION['pcconfig']['PCMW_RESTRICTPAGES']);
@define('PCMW_POSTLOGINREDIRECT',$_SESSION['pcconfig']['PCMW_POSTLOGINREDIRECT']);
//company data
@define('PCMW_COMPANYNAME',$_SESSION['pcconfig']['PCMW_COMPANYNAME']);
@define('PCMW_COMPANYADDRESS',$_SESSION['pcconfig']['PCMW_COMPANYADDRESS']);
@define('PCMW_COMPANYCITY',$_SESSION['pcconfig']['PCMW_COMPANYCITY']);
@define('PCMW_COMPANYSTATE',$_SESSION['pcconfig']['PCMW_COMPANYSTATE']);
@define('PCMW_COMPANYZIP',$_SESSION['pcconfig']['PCMW_COMPANYZIP']);
@define('PCMW_COMPANYTELEPHONE',$_SESSION['pcconfig']['PCMW_COMPANYTELEPHONE']);
@define('PCMW_SALES',$_SESSION['pcconfig']['PCMW_SALES']);
@define('PCMW_CANHELPDESK',$_SESSION['pcconfig']['PCMW_CANHELPDESK']);
@define('PCMW_SHOWFORMINDICATORS',TRUE);
@define('PCMW_YEAR',date('Y'));

//we'll define the user roles here
@define('PCMW_SUSPENDED',1);
@define('PCMW_BASICUSER',10);  //read only
@define('PCMW_HANDLER',15);
@define('PCMW_PREMIUMUSER',20);
@define('PCMW_MODERATOR',30);  //read/write
@define('PCMW_ADMINISTRATOR',40);
@define('PCMW_SUPERUSERS',50);//superuser
@define('PCMW_DEVUSERS',60);//devuser   

//define user group permissions
@define('PCMW_USERSUSPENDED',0);
@define('PCMW_USERREAD',10);
@define('PCMW_USERREADWRITE',20);
@define('PCMW_USERADMIN',30);

//chat constants
@define('PCMW_NEW',10);
@define('PCMW_TAKEN',20);
@define('PCMW_UNREAD',30);
@define('PCMW_OFFLINE',40);
@define('PCMW_CLOSED',50);

define('PCMW_SUPPORT','support@progressivecoding.net');
define('PCMW_HOSTADDRESS','www.progressivecoding.net/PCPluginStub.php');
define('PCMW_HELPDESKURL','tm.progressivecoding.net/PCMW_TaskServerAPIStub.php');
//get our current directory
$strPluginAddress = str_replace(PCMW_SERVERPATH,'',substr(dirname(__FILE__), 0, strrpos( dirname(__FILE__), '/')));
//make sure backslashes are front slashes for web use
define('PCMW_PLUGINADDRESS',str_replace('\\','/',$strPluginAddress).'/');
if(!defined('PCMW_THEMENAME'))
    define('PCMW_THEMENAME','pcmegatheme');
?>