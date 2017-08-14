<?php
/**************************************************************************
* @CLASS PCMW_BasicChat
* @brief Store and manage chat messages and sessions. Make HTML shells and display
* @REQUIRES:
*  -PCMW_Database.php
*  -PCMW_BaseClass.php
*  -PCMW_ChatOptions.php
*  -PCMW_ChatSession.php
*  -PCMW_ChatMessage.php
*
**************************************************************************/
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_ChatOptions.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_ChatSession.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_ChatMessage.php');
class PCMW_BasicChat extends PCMW_BaseClass{//
   //hold the available options
   var $objChatOptions;
   //was this directly called?
   var $boolDirectCall = FALSE;
   //make the admin interface?
   var $boolMakeAdminInterface = FALSE;
   //hold the available sessions
   var $strChatSessions;
   //see who responded last
   var $intLastResponder = 0;
   //debugging
   var $boolDebugOn = FALSE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_BasicChat();
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
    $arrDebugMethods['PCMW_BasicChat::CLASSMETHOD'] = 1;
    return $arrDebugMethods;
  }

  /**
  * create a human readable status translation list
  * @return array
  */
  function MakeStatusTranslation(){
   $arrTranslation = array(
                PCMW_NEW=>'New message. No one has spoken to this user yet.',
                PCMW_TAKEN=>'Taken message. Someone has been talking to this user.',
                PCMW_UNREAD=>'New message from user. Message has not yet been read.',
                PCMW_OFFLINE=>'User is offline.',
                PCMW_CLOSED=>'Chat has been closed.',
   );
   return $arrTranslation;
  }

  /**
  * given the chat content, create the display
  * @param $arrChatContent
  * @return string ( HTML )
  */
  function CreateChatDisplay(){
   if(!$this->CheckChatFeature())
    return FALSE;//feature not enabled or installed
   //register our style and javascript
   $this->RegisterScriptsAndStyles();
   if($this->CheckChatOptions()){
     $objChatSession = new PCMW_ChatSession();
     $objChatSession->intChatSessionId = 0;
     $objChatSession->strChatType = 'single';// varchar(15) NOT NULL "group","single","access"
     $objChatSession->intChatAccess = PCMW_SUSPENDED;// int ( 4 ) DEFAULT PCMW_PREMIUMUSER
     $objChatSession->intStatus = PCMW_NEW;// tinyint(1) NOT NULL
     $objChatSession->intUpdateAlert = 0;// tinyint(1) NOT NULL
     $strChatMessages = 'Type to chat';
     $boolOpenSession = (!PCMW_Abstraction::Get()->CheckUserStatus() && $this->objChatOptions->intChatAccessGroup == PCMW_SUSPENDED)? TRUE:FALSE ;
     //direct call means a short code was used and the interface should be shown
     //all pages should show no  matter what
     //last we check to verify our user can see the chat
     if(($this->boolDirectCall || $this->objChatOptions->intAllPages > 0) &&
         (PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERSUSPENDED,$this->objChatOptions->intChatAccessGroup,FALSE,FALSE) || $boolOpenSession)){
        //decide what kind of interface we're making
        if($this->CheckUserRole($this->objChatOptions->strDefaultOwnerGroup)){
         //make the admin+ interface
         $this->boolMakeAdminInterface = TRUE;
         $arrAvailableSessions = $this->GetChatSessions(get_current_user_id(),FALSE);
         //get our names
         $this->LoadSessionNames($arrAvailableSessions);
         //format for display
         $this->FormatChatSessions($arrAvailableSessions);
    //$strSessions = var_export($arrUserSessions,TRUE);
    //  PCMW_Logger::Debug( '$strSessions ['.$strSessions.'] ['.__LINE__.']',1);
         //get our present ID
         if(!array_key_exists('pc_chatsession',$_SESSION) || (int)$_SESSION['pc_chatsession'] < 1){
            $intSessionId = $this->GetInitialSession($arrAvailableSessions);
            $_SESSION['pc_chatsession'] = $intSessionId;
         }
         else
            $intSessionId = $_SESSION['pc_chatsession'];
         if((int)$intSessionId > 0 && $objChatSession = $this->GetChatSession($intSessionId)){
           $this->LoadUserNameAndStatus($objChatSession);
           $strChatMessages = $this->FormChatEntries($objChatSession->arrChatMessages);
         }
         else{
           $this->strChatSessions = '<div class="pc_chat_sessions" id="pc_chat_sessions">No chat<br />available</div>';
         }
        }
        else{
            //make our basic user interface
          if(is_user_logged_in()){
            if($objChatSession = $this->GetUserChat(get_current_user_id())){
                $this->LoadUserNameAndStatus($objChatSession);
                $strChatMessages = $this->FormChatEntries($objChatSession->arrChatMessages);
            }
            else{
               //make our dummy chat session and data
               $objChatSession = new PCMW_ChatSession();
               $objChatSession->intChatSessionId = 0;
               $objChatSession->strChatType = 'single';// varchar(15) NOT NULL "group","single","access"
               $objChatSession->intChatAccess = PCMW_SUSPENDED;// int ( 4 ) DEFAULT PCMW_PREMIUMUSER
               $objChatSession->intStatus = PCMW_NEW;// tinyint(1) NOT NULL
               $objChatSession->intUpdateAlert = 0;// tinyint(1) NOT NULL
               $strChatMessages = 'Type to chat';
            }
          }
          else if(@$_SESSION['pc_chatsession'] > 0){//user is not logged in, but has a chat open
            if($objChatSession = $this->GetChatSession($_SESSION['pc_chatsession'])){
              $_SESSION['pc_chatsession'] = $objChatSession->intChatSessionId;
              $this->LoadUserNameAndStatus($objChatSession);
              $strChatMessages = $this->FormChatEntries($objChatSession->arrChatMessages);
            }
          }
          else{
           //make our dummy chat session and data
           $objChatSession = new PCMW_ChatSession();
           $objChatSession->intChatSessionId = 0;
           $objChatSession->strChatType = 'single';// varchar(15) NOT NULL "group","single","access"
           $objChatSession->intChatAccess = PCMW_SUSPENDED;// int ( 4 ) DEFAULT PCMW_PREMIUMUSER
           $objChatSession->intStatus = PCMW_NEW;// tinyint(1) NOT NULL
           $objChatSession->intUpdateAlert = 0;// tinyint(1) NOT NULL
           $strChatMessages = 'Type to chat';
          }
        }
        //make our interface now
       $strShell = $this->MakeChatShell($strChatMessages, $objChatSession);
       return $strShell;
     }
     else{
       return FALSE;
     }
   }
   else{
    //give a message to the admin
    if($this->boolDirectCall && PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,PCMW_ADMINISTRATOR,FALSE,FALSE))
        PCMW_Abstraction::Get()->AddUserMSG( 'Please configure chat to use it. ['.__LINE__.']',1);
    return FALSE;
   }
   return FALSE;
  }

  /**
  * register our scripts and styles for use with chat
  * @return bool
  */
  function RegisterScriptsAndStyles(){
    //styles
    wp_enqueue_style( 'pcmw_chatstyle', get_site_url().PCMW_PLUGINADDRESS.'css/chatstyle.css',array(),'4.6.3');
    //scripts
    wp_enqueue_script('pcmw_ajaxcore',get_site_url().PCMW_PLUGINADDRESS.'js/AjaxCore.js',array(),'');
    wp_enqueue_script('pcmw_pcplugin',get_site_url().PCMW_PLUGINADDRESS.'js/PCPlugin.js',array(),'');
    return TRUE;
  }

  /**
  * check to see if the present user is in the required role to manage a chat
  * @param $strUserRole;
  * @return bool
  */
  function CheckUserRole($strUserRole){
    $objUser = get_userdata( get_current_user_id() );
    if(empty( $objUser ))
        return FALSE;//no roles no go
    return in_array( $strUserRole,$objUser->roles );
  }

  /**
  * find the initial session ID
  * @param $arrSessions
  * @return int || FALSE
  */
  function GetInitialSession($arrSessions){
   $intLastId = 0;
   if(is_array($arrSessions) && sizeof($arrSessions) > 0){
     foreach($arrSessions as $objSession){
       if($objSession->intStatus == PCMW_TAKEN){
        return $objSession->intChatSessionId;//this one is open and assigned to this user
       }
       else $intLastId = $objSession->intChatSessionId;
     }
   }
   return $intLastId;
  }

  /**
  * get the chat content for a given session ID
  * @param $intSessionId
  * @return array()
  */
  function GetChatSession($intSessionId=0){
    if($arrMessages = PCMW_Database::Get()->GetSessionChat($intSessionId)){
      $objSession = new PCMW_ChatSession();
      foreach($arrMessages as $arrSingleMessage){
        if((int)$objSession->intChatSessionId < 1){
            $objSession->LoadObjectWithArray($arrSingleMessage);
            $objSession->arrChatMeta = json_decode($objSession->strChatMeta,TRUE);
            $objSession->arrChatMessages = array();
            $this->intLastResponder = $arrSingleMessage['userid'];
        }
        $objSession->arrChatMessages[$arrSingleMessage['messageid']] = new PCMW_ChatMessage();
        $objSession->arrChatMessages[$arrSingleMessage['messageid']]->LoadObjectWithArray($arrSingleMessage);
      }
      return $objSession;
    }
    else return FALSE;
  }

  /**
  * get the chat content for the user
  * @param $intUserId
  * @return array()
  */
  function GetUserChat($intUserId){
    if($arrMessages = PCMW_Database::Get()->GetUserChat($intUserId)){
      $objSession = new PCMW_ChatSession();
      foreach($arrMessages as $arrSingleMessage){
        if((int)$objSession->intChatSessionId < 1){
            $objSession->LoadObjectWithArray($arrSingleMessage);
            $objSession->arrChatMeta = json_decode($arrUserSessions[$arrSession['sessionid']]->strChatMeta,TRUE);
            $objSession->arrChatMessages = array();
            $this->intLastResponder = $arrSingleMessage['userid'];
        }
        $objSession->arrChatMessages[$arrSingleMessage['messageid']] = new PCMW_ChatMessage();
        $objSession->arrChatMessages[$arrSingleMessage['messageid']]->LoadObjectWithArray($arrSingleMessage);
      }
      return $objSession;
    }
    else return FALSE;
  }

  /**
  * given a userid, get the available chat sessions that are unowned or owned by this user
  * @param $intUserId
  * @param $boolAsArray
  * @return string ( HTML ) || array() || bool
  */
  function GetChatSessions($intUserId,$boolAsArray=TRUE){
   if($arrSessions = PCMW_Database::Get()->GetUserSessions($intUserId)){
    if($boolAsArray)
      return $arrSessions;
    //load the sessions into objects
    $arrUserSessions = array();
    foreach($arrSessions as $arrSession){
      $arrUserSessions[$arrSession['sessionid']] = new  PCMW_ChatSession();
      $arrUserSessions[$arrSession['sessionid']]->LoadObjectWithArray($arrSession);
      $arrUserSessions[$arrSession['sessionid']]->arrChatMeta = json_decode($arrUserSessions[$arrSession['sessionid']]->strChatMeta,TRUE);
    }
    return $arrUserSessions;
   }
   return FALSE;
  }

  /**
  * given a chat session object, load the names and online status
  * @param $objSession
  * @return bool
  */
  function LoadUserNameAndStatus(&$objSession){
    $arrUsersData = array();//hold our list to prevent dupes
    //load our names
    if($objSession->intOwnerId > 0){
        $objUser = get_user_by( 'ID', $objSession->intOwnerId );
        $arrUsersData[$objSession->intOwnerId] = $objUser->user_nicename;
        $objSession->strOwnerName = $objUser->user_nicename;
    }
    if($objSession->intPreviousOwnerId > 0){
        $objUser = get_user_by( 'ID', $objSession->intPreviousOwnerId );
        $arrUsersData[$objSession->intOwnerId] = $objUser->user_nicename;
        $objSession->strPreviousOwnerName = $objUser->user_nicename;
    }
    if($objSession->intUserId > 0){
        $objUser = get_user_by( 'ID', $objSession->intUserId);
        $arrUsersData[$objSession->intOwnerId] = $objUser->user_nicename;
        $objSession->strUserName = $objUser->user_nicename;
    }
    else if(array_key_exists('anonusername',$objSession->arrChatMeta) && trim($objSession->arrChatMeta['anonusername']) != ''){
        $objSession->strUserName = $objSession->arrChatMeta['anonusername'];
    }
    else if(array_key_exists('anonusername',$_SESSION) && $_SESSION['anonusername'] != ''){
        $objSession->strUserName = $_SESSION['anonusername'];
        $objSession->arrChatMeta['anonusername'] = $_SESSION['anonusername'];
    }
    else
        $objSession->strUserName = 'Anon';
    //get our message names now
    foreach($objSession->arrChatMessages as $objMessage){
      if($objMessage->intUserId > 0){
          if(array_key_exists($objMessage->intUserId,$arrUsersData)){
            $objMessage->strUserName = $arrUsersData[$objMessage->intUserId];
            continue 1;
          }
          $objUser = get_user_by( 'ID', $objMessage->intUserId);
          $arrUsersData[$objMessage->intUserId] = $objUser->user_nicename;
          $objMessage->strUserName = $objUser->user_nicename;
      }
      else if(trim($objSession->arrChatMeta['anonusername']) != ''){
          $objMessage->strUserName = $objSession->arrChatMeta['anonusername'];
      }
      else if(array_key_exists('anonusername',$_SESSION) && $_SESSION['anonusername'] != ''){
          $objMessage->strUserName = $_SESSION['anonusername'];
      }
      else
        $objMessage->strUserName = 'Anon';
    }
    unset($arrUsersData);
    return TRUE;
  }

  /**
  * given a list of sessions, load the user, owner and previous owner data
  * @param $arrSessions
  * @return bool
  */
  function LoadSessionNames(&$arrSessions){
    $arrUsersData = array();//hold our list to prevent dupes
    foreach($arrSessions as $objSession){
      if($objSession->intOwnerId > 0){
          $objUser = get_user_by( 'ID', $objSession->intOwnerId );
          $arrUsersData[$objSession->intOwnerId] = $objUser->user_nicename;
          $objSession->strOwnerName = $objUser->user_nicename;
      }
      if($objSession->intPreviousOwnerId > 0){
          $objUser = get_user_by( 'ID', $objSession->intPreviousOwnerId );
          $arrUsersData[$objSession->intOwnerId] = $objUser->user_nicename;
          $objSession->strPreviousOwnerName = $objUser->user_nicename;
      }
      if($objSession->intUserId > 0){
          $objUser = get_user_by( 'ID', $objSession->intUserId);
          $arrUsersData[$objSession->intOwnerId] = $objUser->user_nicename;
          $objSession->strUserName = $objUser->user_nicename;
      }
      else if(trim($objSession->arrChatMeta['anonusername']) != ''){
          $objSession->strUserName = $objSession->arrChatMeta['anonusername'];
      }
      else
          $objSession->strUserName = 'Anon';
    }
    unset($arrUsersData);
    return TRUE;
  }


  /**
  * check the chat options in session, load them if they do not exist
  * chat option variables:
    public $intAllPages;//use the chat on all pages
    public $intDefaultOwnerGroup;//which group ( and above ) can field chats
    public $intChatAccessGroup;//lowest access level that can customers can be to chat
    public $strChatType;//group - users with certain capabilities, single - one on one only, access - by admin group
    public $intStaffCanClose;//Can the appointed staff group close chats?
    public $intCustomerAttachments;// can the customer add attachments
    public $intOwnerAttachments;//can the owner share attachments
    public $intLastUpdate;//last date the settings were updated
    public $intLastUpdatedBy;// last user to update the settings
  * @return bool
  */
  function CheckChatOptions(){
   if($this->objChatOptions = $this->getObject('chatoptions'))
    return TRUE;//loaded up and ready
   else{
     //check to see if we have the wp_option for this
     if($strOptions = get_option('PCMW_ChatOptions')){
       //decode our json stored options
       $arrOptions = json_decode($strOptions,TRUE);
       //create our chat options object
       $this->objChatOptions = new PCMW_ChatOptions();
       //load the object
       $this->objChatOptions->LoadObjectWithArray($arrOptions);
       //store the object
       $this->storeObject('chatoptions', $this->objChatOptions);
       return TRUE;
     }
    return FALSE;//no chat options exist, so we can't start
   }
  }

  /**
  * see if the feature is turned on
  * return bool
  */
  function CheckChatFeature(){
    $strFeatures = get_option('PCMW_features');
    $arrFeatures = json_decode($strFeatures,TRUE);
    if(!is_array($arrFeatures) || (!array_key_exists('basicchat',$arrFeatures) || (int)$arrFeatures['basicchat'] < 1))
        return FALSE;
    //verify chat session
    if(!array_key_exists('pc_chatsession',$_SESSION) || trim($_SESSION['pc_chatsession']) == '')
        $_SESSION['pc_chatsession'] = 0;
    //verify chat ID    
    if(!array_key_exists('pc_chatid',$_SESSION) || trim($_SESSION['pc_chatid']) == '')
        $_SESSION['pc_chatid'] = md5(rand());
    return TRUE;
  }



  /**
  * format the chat sessions
  * @param $arrSessions
  * @return string ( HTML )
  */
  function FormatChatSessions($arrSessions){
    $arrStatusTranslation = $this->MakeStatusTranslation();
    if(sizeof($arrSessions) > 0){
      $this->strChatSessions = '<ul>';
      foreach($arrSessions as $objSession){
        $strRowCSS = '';
        if((int)$_SESSION['pc_chatsession'] > 0 && $objSession->intChatSessionId == @$_SESSION['pc_chatsession'])
            $strRowCSS = 'pc_selected_session';
        if($objSession->intStatus == PCMW_UNREAD)
            $strRowCSS .= ' background-bluegreen';
        $this->strChatSessions .= '<li class="'.$strRowCSS.'">';
        if(!$this->IsUserLoggedIn($objSession->intUserId))
            $this->strChatSessions .= '<i class="fa fa-caret-right fa-1x pc_chat_status_3"><img src="'.get_site_url().PCMW_PLUGINADDRESS.'images/'.PCMW_CLOSED.'.png" alt="User active" height="15" width="15" data-toggle="tooltip" title="User is off line" /></i>';
        else{
            $this->strChatSessions .= '<i class="fa fa-caret-right fa-1x pc_chat_status_'.$objSession->intStatus.' pointer" onclick="SetNewAdminChat(\''.$objSession->intChatSessionId.'\',\''.@$_SESSION['pc_chatid'].'\');"  data-toggle="tooltip" title="Status: '.$arrStatusTranslation[$objSession->intStatus].' ['.$objSession->strOwnerName.'] Click to engage!" >';
            $strSrc = get_theme_mod('pcmt_chat_'.$objSession->intStatus.'_icon',get_site_url().PCMW_PLUGINADDRESS.'images/'.$objSession->intStatus.'.png');
            $this->strChatSessions .= '<img src="'.$strSrc.'" alt="User active" height="15" width="15" data-toggle="tooltip" title="Status: '.$arrStatusTranslation[$objSession->intStatus].' ['.$objSession->strOwnerName.'] Click to engage!" class="pointer" onclick="SetNewAdminChat(\''.$objSession->intChatSessionId.'\',\''.@$_SESSION['pc_chatid'].'\');"  /></i>';
        }
        $this->strChatSessions .= $objSession->strUserName.':';
        $this->strChatSessions .= '<div style="font-size:8px;">'.date('m-d H:i',$objSession->intStartDate).'['.$this->intLastResponder.' '.get_current_user_id().']</div>';
        $this->strChatSessions .= '</li>';
      }
      $this->strChatSessions .= '</ul>';
    }
    return '';
  }

  /**
  * format an array of chat entries for the reader
  * @param $arrChatMessages
  * @param $arrUserData
  * @return string ( HTML )
  */
  function FormChatEntries($arrChatMessages){
   if(sizeof($arrChatMessages) > 0){
     $strMessages = '';
     foreach($arrChatMessages as $intSessionId=>$objMessage){
       $strMessages .= '<div class="pc_chat_message">';
       $strMessages .= $objMessage->strUserName.':'.$objMessage->strMessage;
       $strMessages .= '<br /><span style="font-size:10px;">'.date('m-d-y H:i',$objMessage->intEDate).'['.$_SESSION['pc_chatsession'].']</span>';
       $strMessages .= '</div>';
     }
     return $strMessages;
   }
   else return 'Type to Text';
  }

  /**
  * load any admin options we want to include
  * @return string ( HTML )
  */
  function MakeChatAdminOptions($intSessionId){
    if(PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,PCMW_ADMINISTRATOR,FALSE,FALSE) || $this->objChatOptions->intStaffCanClose > 0)
    return '<i class="fa fa-close fa-2x pointer text-danger" onclick="CloseChat('.$intSessionId.')" title="Close selected chat session" data-toggle="tooltip" data-placement="top">&nbsp;</i>';
    else return '';
  }


  /**
  * make the available controls for a chat session
  * @param $objChatSession
  * @return string ( HTML )
  */
  function MakeChatControls($objChatSession,$strSessionHash){
    $strControls = '<div class="row">';
    $strControls .= '<div class="col-md-12">';
    $strControls .= '<div class="col-md-8">';
    $strControls .= '<textarea id="chattext" name="chattext" class="w100p" rows="3" placeholder="Enter text" onkeyup="DetectChatEnter(this.form,event,this,'.$objChatSession->intChatSessionId.');"></textarea>';
    $strControls .= '</div>';
    $strControls .= '<div class="col-md-4 w100p">';
    $strControls .= '<div class="">';
    $strControls .= '<input type="hidden" value="'.$objChatSession->intChatSessionId.'" id="chat_'.$strSessionHash.'" name="chat_'.$strSessionHash.'" />';
    if($this->boolMakeAdminInterface && (int)$objChatSession->intChatSessionId > 0){
     //make special options here
     $strControls .= $this->MakeChatAdminOptions($objChatSession->intChatSessionId);
    }
    $strControls .= '<input type="button" value="Send" class="btn btn-primary" onclick="DetectChatEnter(this.form,event,this,'.$objChatSession->intChatSessionId.');" />';

    if(!$this->boolMakeAdminInterface && ($objChatSession->strUserName == '' || $objChatSession->strUserName == 'Anon'))
        $strControls .= '<input type="text" placeholder="Enter your name here" value="'.$objChatSession->strUserName.'" name="anonusername" style="width:inherit;" />';
    $strControls .= '</div>';                                                                                                                                 $strControls .= '</div>';                                                                                                                                 $strControls .= '</div>';
    return $strControls;
  }

  /**
  * make the chat shell
  * @param $strChatContent
  * @param $objChatSession
  * @return string ( HTML )
  */
  function MakeChatShell($strChatContent, $objChatSession){
    $_SESSION['pc_chatsession'] = (int)$objChatSession->intChatSessionId;
    $strAdminInterface = '';
    $strShellCSS = 'pc_chat_shell border-radius';
    $strShellExtraStyle = 'max-height:'.get_theme_mod('pcmt_chat_height','350').'px;';
    $strShellExtraStyle.= 'max-width:'.get_theme_mod('pcmt_chat_width','400').'px;';
    $strChatEntryCSS = 'pc_chat_entry';
    $strIsAdmin = 'false';
    //make our admin controls here
    if($this->boolMakeAdminInterface){
      $strShellCSS = 'pc_admin_chat_shell';
      $strShellExtraStyle = 'max-height:'.get_theme_mod('pcmt_admin_chat_height','350').'px;';
      $strShellExtraStyle.= 'max-width:'.get_theme_mod('pcmt_admin_chat_width','400').'px;';
      $strChatEntryCSS = 'pc_admin_chat_entry';
      $strIsAdmin = 'true';
    }
    $strShell = PCMW_Abstraction::Get()->GetAllDisplayMessages(TRUE);
    $strShell .= '<form method="post" class="rounded border-radius">';
    $strShell .= '<div class="'.$strShellCSS.'" style="'.$strShellExtraStyle;
    $strShell .= 'right:'.get_theme_mod('pcmt_chat_align','3').'vw;';
    $strShell .= 'font-family:'.get_theme_mod('pcmt_chat_font','Tahoma').';';
    $strShell .= 'padding:'.get_theme_mod('pcmt_chat_padding','#CCCCCC').'px;';
    $strShell .= '">';
    $strShell .= '<div class="pc_chat_header rounded" id="header_'.$_SESSION['pc_chatid'].'" style="';
    $strShell .= 'color:'.get_theme_mod('pcmt_chat_header_text_color','#000000').';';
    $strShell .= 'background-color:'.get_theme_mod('pcmt_chat_header_bg','#FFFFFF').';';
    $strShell .= '">';
    $strShell .= get_theme_mod('pcmt_chat_header_html','Chat Now!');
    $strShell .= '<div style="min-width:1px;min-height:1px;float:left;z-index:100;width:15px;"><i class="fa fa-comments displaynone"  id="pcmw_chat_window_'.$_SESSION['pc_chatid'].'">&nbsp;</i></div>';
    $strCollapseText = '<i class="fa fa-arrow-down">&nbsp;</i>';
    $strShellStyle = get_theme_mod('pcmt_chat_collapse','block');
    if(array_key_exists('pcmw_chat_collapse',$_COOKIE) && $_COOKIE['pcmw_chat_collapse'] > 0){
        $strCollapseText = '<i class="fa fa-arrow-up">&nbsp;</i>';
        $strShellStyle = 'none';
    }
    $strShell .= '<div class="pc_chat_close" onclick="ExpandContractElement(\'collapse_'.$_SESSION['pc_chatid'].'\',this);SetChatCollapseState(this);">'.$strCollapseText.'</div>';
    $strShell .= '</div>';//pc_chat_header
    $strShell .= '<div id="collapse_'.$_SESSION['pc_chatid'].'" style="display:'.$strShellStyle.'">';
    if($this->boolMakeAdminInterface){
        $strShell .= '<div class="pc_chat_sessions" id="pc_chat_sessions">';
        $strShell .= $this->strChatSessions;
        $strShell .= '</div>';
    }
    $strShell .= '<div class="pc_chat_content" id="'.$_SESSION['pc_chatid'].'"  style="';
        $strShell .= 'background-color:'.get_theme_mod('pcmt_chat_body_bg','#FFFFFF').';';
        $strShell .= 'color:'.get_theme_mod('pcmt_chat_body_text_color','#000000').';';
        $strShell .= '">';
    $strShell .= $strChatContent;
    $strShell .= '</div>';//pc_chat_content
    $strShell .= '<div class="'.$strChatEntryCSS.'" id="pc_chat_entry">';
    if(((int)$objChatSession->intChatSessionId > 0 && $this->boolMakeAdminInterface) || !$this->boolMakeAdminInterface){
      $strShell .= $this->MakeChatControls($objChatSession,$_SESSION['pc_chatid']);
    }
    $strShell .= '</div>';//pc_chat_entry
    $strShell .= '</div>';//collapse
    $strShell .= '</div>';//pc_chat_shell
    $strShell .= '</form>';//end form
    $strShell .= '<script language="JavaScript" type="text/javascript" >jQuery( document ).ready(function() {SetChatCheckIntervals("'.$strIsAdmin.'","'.$_SESSION['pc_chatid'].'");});</script>';//
    return $strShell;
  }

  /**
  * check to see if a user is logged in
  * @param $intUserId
  */
  function IsUserLoggedIn($intUserId){
   if((int)$intUserId < 1)
    return TRUE;//temp users cannot be checked
   //$arrUserSession = WP_Session_Tokens::get_instance( $intUserId );
   //$strUserData = var_export($arrUserSession,TRUE);
   //PCMW_Logger::Debug('$strUserData ['.$strUserData.'] $intUserId ['.$intUserId.']',1);
   return TRUE;
  }

  /**
  * given a chat submission record it
  * @param $objMessage
  * @return bool
  */
  function SubmitMessage($objMessage){
   return PCMW_Database::Get()->InsertChatMessage($objMessage);
  }
  #ENDREGION
                           


  #REGION DEBUGGINGMETHODS
 /*
  @brief store and display debug information
  @param $strMessage
  @param $boolLogNow
  @param $strMethod
  @param $boolShowBackTrace
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