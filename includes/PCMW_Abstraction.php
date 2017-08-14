<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
class PCMW_Abstraction{
/************************************
* requires:
    - PCMW_Database.php
    - PCMW_Utility.php
    - PCMW_StaticArrays.php
***************************************/
   var $Showerrors = FALSE;
   var $Errors = '';//string to hold user errors
   var $boolShowMessages = TRUE;
   var $arrMesseges = array();//lets store messages too
   var $imagetypes = array();//array for accepted image types
   var $wavtypes = array();// array for holding acceptable audio ytpes
   var $movtypes = array();//array for holding accepted movie types
   var $ReportExceptions = '';
   var $strJavascript = '';
   var $boolDebugOn = FALSE;

  public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_Abstraction();
		return( $inst );
   }

   public function __construct(){
		//do something
   }

/************************************************
  * Mail functions
  * primarily used for sending formatted emails
  /************************************************/



    function Send_Mail($to , $sub , $strMessage , $fromemail = '' ,$strFile = '', $boolHTMLEmail=TRUE){
        $strTemplate = $this->read_r(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'EmailTemplate.txt');
        //
        $strTemplate = $this->MakeSingleDataReplacements( array(),$strTemplate);
        $objMailLibrary = new PCMW_activeMailLib('html');
        $objMailLibrary->To($to);
        $fromemail = ($fromemail == "")?  PCMW_SUPPORT:$fromemail ;
        $objMailLibrary->From($fromemail);
        $objMailLibrary->Reply($fromemail);
        $objMailLibrary->Subject($sub);
        if($boolHTMLEmail){
            $strMessage = str_replace('%CONTENT%',$strMessage,$strTemplate);
        }
        $objMailLibrary->Message($strMessage);
        if($strFile != ""){
            if(is_file($strFile)){
            $arrFileParts = explode(DIRECTORY_SEPARATOR,$strFile);
            $objMailLibrary->Attachment($strFile,$arrFileParts[(sizeof($arrFileParts) - 1)]);
            }
            else
                PCMW_Logger::Debug('Cannot find file ['.$strFile.']',1);
        }
        //send it now
        $objMailLibrary->Send();
        if($objMailLibrary->isSent($to)){
            return true;
        }
        else{
            $strDetailError = 'Cannot send mail to ['.$to.']'."\r\n";
            $strDetailError .= 'with subject ['.$sub.']'."\r\n";
            $strDetailError .= 'for message ['.$strMessage.']'."\r\n";
            $strDetailError .= 'frommail ['.$fromemail.']'."\r\n";
            $strDetailError .= 'file ['.$strFile.']'."\r\n";
            PCMW_Logger::Debug($strDetailError.' in SendMail()',1);
            return false;
            } 
    }

  /************************************************
  * file handling functions
  * open, append, create, write line, write whole, read line, ect.
  /************************************************/
     function read_r($file){
       if(is_file($file)){
          $fh = fopen($file,"r");
          $contents = fread($fh, filesize($file));
          fclose($fh);
          return $contents;
       }
       else
            return FALSE;
    }

     function read_r_line($file,$delimiter = "|"){
     $returnarray = array();
       if(is_file($file)){
          $fh = fopen($file,"r");
          while (!feof($fh)){  //
          $filespot = fgets($fh, 4096);
          $filespot = explode($delimiter,$filespot);
            if(strlen($filespot[0]) > 0)
            $returnarray[$filespot[0]] = $filespot;
          }
          fclose($fh);
          return $returnarray;
       }
       else
            return FALSE;
    }

    function WriteLine($file,$content){
       if(is_file($file)){
          $fh = fopen($file,"a");
          fwrite($fh,$content."\r\n");
          fclose($fh);
          return TRUE;
       }
       else
        $this->ReportToUser(1,'File '.$file.' does not exist. Quiting.');
    }

    function WriteAllLines($file,$content){
       if(is_file($file)){
          $fh = fopen($file,"w+");
          foreach($content as $ka=>$va){
            $va = str_replace("\r","",$va);
            $va = str_replace("\n","",$va);
            fwrite($fh,$va."\r\n");
          }
          fclose($fh);
          return TRUE;
       }
       else
        $this->ReportToUser(1,'File '.$file.' does not exist. Quiting.');
    }

    function write_w($file,$content){
       if(is_file($file)){
          $fh = fopen($file,"w+");
          fwrite($fh,$content);
          fclose($fh);
          return TRUE;
       }
       else{
           $fh = fopen($file,"w+");
          fwrite($fh,$content);
          fclose($fh);
          return TRUE;
            //return FALSE;
       }
    }

       function append_a($file,$content){
         if(is_file($file)){
            $fh = fopen($file,"a+");
            fwrite($fh,$content);
            fclose($fh);
            return TRUE;
         }
         else{
             $fh = fopen($file,"a+");
            fwrite($fh,$content);
            fclose($fh);
            return TRUE;
              //return FALSE;
         }
      }

  /************************************************
  * Log file and error reporting functions
  * write to the log when needed
  /************************************************/

    //create log file to track changes
  function LogFile($type,$message,$src){
     $logtype = array();
     $logtype[1] = 'Error';
     $logtype[2] = 'Information';
     $time = date('Y_m_d',time());
     $src .= $time.'.txt';
     $WM = $logtype[$type].' : ['.date('m-d-Y g:i:s',time()).']'."\r\n";
     $WM .= $message."\r\n";
     $WM .= '______________________________________________________________'."\r\n";
     if(file_exists($src))
        $this->WriteLine($src,$WM);
     else
        $this->write_w($src,$WM);
  }


  function ReportToUser($type,$message,$expandtable = FALSE){
        $Rtypelist = array();
         //type 1 == Error
         //type 2 == Info
         //type 3 == Success
         //type 4 == System
         $Rtypelist[1] = 'Error';
         $Rtypelist[2] = 'System Information';
         $Rtypelist[3] = 'Success';
         $Rtypelist[4] = 'System Message';
         $message = str_replace('%type%',$Rtypelist[$type],$message);
         //remove any lingering log fomatting, if applicable
         $message = str_replace("\r\n","<br />",$message);
         $cout = ($expandtable)? '<table style="height:'.$this->rheight.';" ><tr><td>Report : '.$Rtypelist[$type].'<img src="../images/msgpos.gif" height="12" width="12" onclick="Showreport(this,\'reporttable'.$this->Reportcount.'\')" style="cursor:pointer;margin-left:10px;border:none;" border="0"  /></td></tr><tr><td id="reporttable'.$this->Reportcount.'" style="display:none;">':'<br /><br />';
         if($type == 1)
            $cout .= '<div class="alert alert-danger">
					<p> '.$message.'</p><div class="tn-progress"></div>
				</div><hr>';
         if($type == 2)
            $cout .= '<div class="alert alert-warning">
					<p> '.$message.'</p><div class="tn-progress"></div>
				</div><hr>';
         if($type == 3)
            $cout .= '<div class="alert alert-success">
					<p> '.$message.'</p><div class="tn-progress"></div>
				</div><hr>';
         if($type == 4)
            $cout .= '<div class="bg-primary text-primary">
					<p> '.$message.'</p><div class="tn-progress"></div>
				</div><hr>';
         if($type == 5)
            $cout .= '<div class="bg-warning">
					<p> '.$message.'</p><div class="tn-progress"></div>
				</div><hr>';
         if($type == 6)
            $cout .= '<div class="alert alert-info">
					<p> '.$message.'</p><div class="tn-progress"></div>
				</div><hr>';
         if($type == 7)
           $cout .= '<div class="bg-warning text-muted">
					<p> '.$message.'</p><div class="tn-progress"></div>
				</div><hr>';
         if($type == 8)
         $cout .= '<div class="bg-warning text-danger">
					<p> '.$message.'</p><div class="tn-progress"></div>
				</div><hr>';
         if($type == 9){//random colors
            $cout .= '<div style="'.PCMW_Utility::Get()->MakeRandomColorCombo().';font-size:17px;" class="userreport calendarnoanswerevent" ><b>!</b> '.$message.'</div>';
         }
            $cout = ($expandtable)? $cout.'<br /></td></tr></table>':$cout.'<br />' ;
            $this->Reportcount++;
            if($this->Showerrors)
                echo $cout;
            else
                return $cout;
   }

  //load the error session var
  function AddErrorMSG($msg){
    if(array_key_exists('errout',$_SESSION))
      $_SESSION['errout'][] = $msg;
    else{
       $_SESSION['errout'] = array();
       $_SESSION['errout'][] = $msg;
    }
  }

  //lets clear the errors now
  function ClearDisplayErrors(){
   $this->Showerrors = FALSE;
   $_SESSION['errout'] = array();
   $this->Errors = '';
  }

  //lets get the messages we have stored
  function GetDisplayErrors($objObject = null){
    $arrRepeatErrors = array();
    $this->Errors = '';
    if(array_key_exists('errout',$_SESSION) && sizeof($_SESSION['errout']) > 0){
      $this->Showerrors = FALSE;
      foreach($_SESSION['errout'] as $ka=>$va){
        //$severe = ($va[1] == "" || $va[1] < 1)? 1 :$va[1] ;
        if(!in_array($va,$arrRepeatErrors)){
          $this->Errors .= $this->ReportToUser(1,'Error '.($ka + 1).': '.$va,FALSE);
          //lets add it to the array now that it works.
          $arrRepeatErrors[] = $va;
        }
      }

    }
  }

  //load the error session var
  function AddUserMSG($msg,$intType = 1,$boolExpand = FALSE){
    if(array_key_exists('msgs',$_SESSION) && is_array($_SESSION['msgs']))
      $_SESSION['msgs'][] = array($msg,$intType,$boolExpand);
    else{
       $_SESSION['msgs'] = array();
       $_SESSION['msgs'][] = array($msg,$intType,$boolExpand);
    }
  }

  //lets clear the errors now
  function ClearDisplayMSGs(){
   $_SESSION['msgs'] = array();
   $this->arrMesseges = '';
  }

  //lets get the messages we have stored
  function GetDisplayMSGs(){
    $arrRepeatErrors = array();
    $this->arrMesseges = '';
    if($this->boolShowMessages && array_key_exists('msgs',$_SESSION) && sizeof($_SESSION['msgs']) > 0){
      foreach($_SESSION['msgs'] as $ka=>$va){
        //$severe = ($va[1] == "" || $va[1] < 1)? 1 :$va[1] ;
        if(!in_array($va[0],$arrRepeatErrors)){
          $this->arrMesseges .= $this->ReportToUser($va[1],'[ %type% '.($ka + 1).' ] '.$va[0],$va[2]);
          //lets add it to the array now that it works.
          $arrRepeatErrors[] = $va[0];
        }
      }
    }
  }


  //get messages and errors quickly
  /*
    ARE THESE THE SAME?
  */
  function GetErrorSnapShot(){
    $this->GetDisplayErrors();
    $strErrors =  $this->Errors;
    $this->GetDisplayMSGs();
    $strErrors .=  $this->arrMesseges;
    return $strErrors;
  }

  //Load all of the messages now
  function GetAllDisplayMessages($boolReturnMessages = FALSE){
    //now lets get all of the messages and errors
      $strMessages = '';
              $this->GetDisplayErrors();
              $strMessages .= PCMW_Abstraction::Get()->Errors;
              $this->ClearDisplayErrors();
              $this->GetDisplayMSGs();
              $strMessages .= $this->arrMesseges;
              $this->ClearDisplayMSGs();
     if($boolReturnMessages)
       return $strMessages;
     else  echo $strMessages;
              return TRUE;
  }



  /**
  * check a users access as a priveleged user
  * @retrn bool
  */
  function IsUserPrivileged($boolGroupOnly=FALSE){
   if($_SESSION['CURRENTUSER']['pcgroup']['groupstatus'] >= PCMW_USERADMIN){
     if($boolGroupOnly)
        return TRUE;
     else if($_SESSION['CURRENTUSER']['pcgroup']['status'] > PCMW_USERREADWRITE)
        return TRUE;
     else
        //user does not have privileges
        return FALSE;
   }
   return FALSE;
  }

  /**
  * given a group and user group minimum, check to see if a user has privilege
  * @param $intGroupPrivilege
  * @param $intUserPrivilege
  * @param $boolGroupOnly
  * @return bool
  */
  function CheckPrivileges($intGroupPrivilege=0,$intUserPrivilege=0,$boolGroupOnly=FALSE,$boolRedirectUser=TRUE){
   if($arrCurrentUser = PCMW_Abstraction::CheckUserStatus()){
     if(is_array($arrCurrentUser) && $arrCurrentUser['pcgroup']['groupstatus'] >= $intGroupPrivilege){

       if($boolGroupOnly)
          return $arrCurrentUser;
       else if($arrCurrentUser['pcgroup']['admingroup'] >= $intUserPrivilege)
          return $arrCurrentUser;
       else{
          //user does not have privileges
          if($boolRedirectUser)
            PCMW_Abstraction::RedirectUser(FALSE,PCMW_HOMEPAGE);
          return FALSE;
       }
     }
     if($boolRedirectUser)
       PCMW_Abstraction::RedirectUser(FALSE,PCMW_HOMEPAGE);
     return FALSE;
   }
   else{
     $strPage = PCMW_Utility::GetScriptSelf();
     if($strPage != 'PCMW_AS.php' && !array_key_exists('redirect',$_SESSION))
        $_SESSION['redirect'] = $strPage;
     if($boolRedirectUser)
        PCMW_Abstraction::RedirectUser(PCMW_LOGINPAGE);
     return FALSE;
   }
  }


  //we need something to check if a user is logged in or not
  public static function CheckUserStatus(){
    if(array_key_exists('CURRENTUSER',$_SESSION) && array_key_exists('pcgroup',$_SESSION['CURRENTUSER']))
      return  $_SESSION['CURRENTUSER'];
    else
      return FALSE;
  }

  /**
  * get the list of available access levels
  * @return array
  */
  function GetAvailableAccessLevels(){
   $arrLevels = array();
   if($arrForms = PCMW_StaticArrays::Get()->LoadStaticArrayType('accesslevels',FALSE,0,FALSE,0,'',FALSE)){
     foreach($arrForms as $intLevel=>$arrLevelData){
       PCMW_Abstraction::MakeFormDataReplacements( array() , $arrLevelData );
       if($_SESSION['CURRENTUSER']['pcgroup']['admingroupid'] >= $intLevel){
          if(array_key_exists('maxlevel',$arrLevelData)){
            if($intLevel < $arrLevelData['maxlevel'])
                continue 1;
          }
          $arrLevels[$intLevel] = $arrLevelData[0];
       }
     }
   }
   return $arrLevels;
  }



   //lets check a checkbox for an 'on' value
   function CheckCheckBoxValue($arrPOST,$strName){
     if(sizeof($arrPOST) > 0){
       if(array_key_exists($strName,$arrPOST) && esc_attr($arrPOST[$strName]) == 'on')
          return TRUE;
     }
     //nope
     return FALSE;
   }


  //lets figure out if this is an image or not
  function IsImage($strFileName){
    //load the image type into a member variable
    //$arrImageExtensions = $this->LoadImageExtensions();
    $arrImageExtensions = explode(".",$strFileName);
    /*   //php 5.3+ method
    */
    //lets get the file mime type
    $objFileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $strFileInfo = finfo_file($objFileInfo, (PCMW_SERVERPATH.DIRECTORY_SEPARATOR.$strFileName));
    finfo_close($objFileInfo);
    if(in_array($strFileInfo,$this->imagetypes))
      return TRUE;
    else
      return FALSE;
    if(in_array(strtolower($arrImageExtensions[(sizeof($arrImageExtensions) - 1)]),$arrImageExtensions))
      return TRUE;
    else
      return FALSE;
  }


  //we need to clip text and return it
  function ClipText($text,$length){
    $strreturn = wordwrap($text, $length, "<br />");//the return string
    return $strreturn;
  }

  //Josh keeps saying he believes there should be a
  //central function for redirection. Thats what this will do

  public static function RedirectUser($page = FALSE,$strEntireUrl=''){
    if($page){
      @header('Location : '.get_site_url().'/'.$page);
      //if headers fail...
      ?>
         <script language="JavaScript" type="text/javascript">
              window.location = "<?php echo get_site_url().'/'.$page; ?>";
          </script>
       <?php
    }
    else if(trim($strEntireUrl) != ''){
      @header('Location : '.$strEntireUrl);
      //if headers fail...
      ?>
         <script language="JavaScript" type="text/javascript">
              window.location = "<?php echo $strEntireUrl;?>";
          </script>
      <?php
    }
    else{
      @header('Location : '.PCMW_HOMEPAGE);
      //if headers fail...
      ?>
         <script language="JavaScript" type="text/javascript">
              window.location = "<?php echo PCMW_HOMEPAGE;?>";
          </script>
       <?php
    }
  }

  //send the curl string
  function FireCurl($strPostString){   
     $objCurl  = curl_init(PCMW_HOSTADDRESS);
      curl_setopt($objCurl, CURLOPT_RETURNTRANSFER,1);
      curl_setopt($objCurl, CURLOPT_POST, TRUE);
      curl_setopt($objCurl, CURLOPT_POSTFIELDS, $strPostString);
      curl_setopt($objCurl, CURLOPT_URL,PCMW_HOSTADDRESS);
      curl_setopt($objCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($objCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Content-Length: " . strlen($strPostString)));
      curl_setopt($objCurl, CURLOPT_HEADER , 0);
      curl_setopt($objCurl, CURLOPT_VERBOSE, TRUE);
      curl_setopt($objCurl, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($objCurl, CURLOPT_CONNECTTIMEOUT ,10);
      curl_setopt($objCurl, CURLOPT_TIMEOUT, 180);

      //send
      $boolResult = curl_exec($objCurl);
      $arrDebugInfo = curl_getinfo($objCurl);
      //debug? anyone?
      if((!$boolResult || (curl_errno($objCurl) > 0 || $this->boolDebugOn > 0)) && $arrDebugInfo){ //curl_errno($objCurl) &&
         $strCurlResults = var_export($arrDebugInfo,TRUE);
          $strCurlResults .= 'curl_errno['. curl_errno($objCurl) . ']'."\r\n";
          $strCurlResults .=  'curl_error[' .curl_error($objCurl).']'."\r\n";
          $strCurlResults .=  ' Curl String[' .$strPostString.']'."\r\n";
          $strCurlResults .=  ' bool result[' .(int)$boolResult.']'."\r\n";
          $strCurlResults .=  ' $strURI [' .PCMW_HOSTADDRESS.']'."\r\n";
          $strCurlResults .=  ' sizeof($arrDebugInfo) [' .sizeof($arrDebugInfo).']'."\r\n";
          PCMW_Logger::Debug($strCurlResults.' LINE ['.__LINE__.'] METHOD ['.__METHOD__.']',1);
      }
      //close the connection
  	curl_close($objCurl);
      //return the status
      return $boolResult;
  }

  //lets convert a date
  function ConvertStringDateToInteger($strDate){
  PCMW_Logger::Debug('METHOD ['.__METHOD__.']',1,TRUE);
  return PCMW_Utility::Get()->ConvertStringDateToInteger($strDate);
  }

/*
* WERE NOT PRESENTLY IMPLEMENTING CAPTCHA HERE, SO WE NEED TO DEPRECATE THIS, OR UPDATE IT
 //we need to make the Captcha
 public static function MakeNewCaptcha(){
 //lets set thew return variable
   if(!PCMW_Abstraction::Get()->CheckUserStatus()){
	   $Cap = '';
	   $imgname = Captcha::Get()->MakeNewCaptchaImage();
	   if($imgname != ""){
         $strCaptcha = '<div class="form-group col-md-12 margintop20">';
         $strCaptcha .= '<h4>Please enter the letters and characters you see. They <b>are</b> case sensitive.<br /><span id="capterror"></span></h4>';
         $strCaptcha .= '<img src="'.get_site_url().'/Cauthorize/'.$imgname.'.gif" height="40" id="rimg" width="160" alt="'.$imgname.'" class="floatleft" style="width:20%;margin-top:5px;" />';
         $strCaptcha .= '<input type="text" id="captinput" name="captinput"  class="form-control input-md floatleft" style="vertical-align:text-top;width:40%;height:40px;margin-top:5px;" />';
         $strCaptcha .= '<input type="button" value="New Image" onclick="reload_captcha();" class="form-control input-md floatleft" style="width:20%;margin-top:5px;height:40px;" />';
         $strCaptcha .= '</div>';
	   }
	   return $strCaptcha;
   }
   else{
   	return FALSE;
   }
 }
 */

   //we will apply the class to the link based on what we think the page is
  function LinkHighlight($strPages, $strClassName = ''){
    //lets break the pages string apart
    $arrPages = explode(',',$strPages);
    //get the pagename
    $arrScript = explode(DIRECTORY_SEPARATOR,$_SERVER['SCRIPT_FILENAME']);
    $strThisScript = $arrScript[(sizeof($arrScript) - 1)];
    foreach($arrPages as $ka=>$va){
     //echo $strThisScript.'==='.$_SERVER['SCRIPT_FILENAME'].'<br />';
       if($va == $strThisScript ||
          $va == $_SERVER['SCRIPT_FILENAME'])
          return $strClassName;
    }
    //nothing matched somehow, retun blank
    return '';
  }

/*************************************
* PCMW_Utility functions
*
**************************************/


  /**
  * look through the object event handler variables to determine if there are
  * any values in our posted data to make replacements on
  * @param array $arrPOSTData prefilled or posted data to use in the replacement process
  * @return bool
  */
  public static function MakeFormDataReplacements( $arrPOSTData,&$arrSubject,$objObject=null){
      foreach($arrSubject as $varKey=>$varMember){
        if(!is_string($varMember))
            continue 1;

        if((preg_match_all("/\[[^\]]*\]/", $varMember, $arrMatch)) > 0){
          foreach($arrMatch[0] as $ka=>$va){
          $strBracketsRemoved = str_replace(array("[","]"),'',$va);
            if(array_key_exists($strBracketsRemoved,$arrPOSTData))
              $varMember = str_replace($va,$arrPOSTData[$strBracketsRemoved],$varMember);
          }
        }
        if(!is_null($objObject) && (preg_match_all("/{(.*?)}/", $varMember, $arrMatch)) > 0){
          foreach($arrMatch[0] as $kb=>$vb){
          $strBracketsRemoved = str_replace(array("{","}"),'',$vb);
            if(property_exists($this, $strBracketsRemoved) && is_string($objObject->{$strBracketsRemoved}))
              $varMember = str_replace($vb,$objObject->{$strBracketsRemoved},$varMember);
          }
        }
        if((preg_match_all('#\((.*?)\)#', $varMember, $arrMatch)) > 0){
          foreach($arrMatch[0] as $kc=>$vc){
          $strBracketsRemoved = str_replace(array("(",")"),'',$vc);
            if(defined($strBracketsRemoved))
              $varMember = str_replace($vc,constant($strBracketsRemoved),$varMember);
          }
        }
        $arrSubject[$varKey] = $varMember;
      }
      return TRUE;
  }


  /**
  * look through the object event handler variables to determine if there are
  * any values in our posted data to make replacements on
  * @param array $arrPOSTData prefilled or posted data to use in the replacement process
  * @return bool
  */
  function MakeSingleDataReplacements( $arrPOSTData,$strSubject,$objObject=null){
        if((preg_match_all('#\((.*?)\)#', $strSubject, $arrMatch)) > 0){
          foreach($arrMatch[0] as $kc=>$vc){
          $strBracketsRemoved = str_replace(array("(",")"),'',$vc);
            if(defined($strBracketsRemoved))
              $strSubject = str_replace($vc,constant($strBracketsRemoved),$strSubject);
          }
        }
        if((preg_match_all('#\%(.*?)\%#', $strSubject, $arrMatch)) > 0){
          foreach($arrMatch[0] as $kc=>$vc){
          $strBracketsRemoved = str_replace("%",'',$vc);
            if(defined($strBracketsRemoved))
              $strSubject = str_replace($vc,constant($strBracketsRemoved),$strSubject);
          }
        }
        if((preg_match_all("/\[[^\]]*\]/", $strSubject, $arrMatch)) > 0){
          foreach($arrMatch[0] as $ka=>$va){
          $strBracketsRemoved = str_replace(array("[","]"),'',$va);
              $strSubject = @str_replace($va,$arrPOSTData[$strBracketsRemoved],$strSubject);
          }
        }

        if(!is_null($objObject) && (preg_match_all("/{(.*?)}/", $strSubject, $arrMatch)) > 0){
          foreach($arrMatch[0] as $kb=>$vb){
          $strBracketsRemoved = str_replace(array("{","}"),'',$vb);
            if(property_exists($this, $strBracketsRemoved) && is_string($objObject->{$strBracketsRemoved}))
              $strSubject = str_replace($vb,$objObject->{$strBracketsRemoved},$strSubject);
          }
        }
        return $strSubject;
  }


  /*
  @brief Given a subject and purpose
  @param $arrParentArray,$arrTitleReplacements
  @return array (parent array)
  */
  function GetFormTitleReplacements($arrSubject,$strPurpose,$arrData=array()){
    $arrTitles = PCMW_StaticArrays::Get()->LoadStaticArrayType($strPurpose,FALSE);
    //check them for conditional values
    //remove non-applicable form titles
    $this->ValidateArrayValues($arrTitles,$arrData);
    foreach($arrSubject as $ka=>$va){
      //remove any constant values
      if(is_array($arrTitles) && array_key_exists($va,$arrTitles) && $arrTitles[$va] != ""){
        $arrSubject[$ka] = array($va,$arrTitles[$va]);
        $arrSubject[$ka][1] = $this->MakeSingleDataReplacements( array(),$arrSubject[$ka][1]);
      }
      else
        unset($arrSubject[$ka]);
    }
    return $arrSubject;
  }

  /**
  * given an array of data check the structure for conditions and verify the
  * element is valid for display
  * @return bool
  */
  function ValidateArrayValues(&$arrPrimary,$arrData){
    foreach($arrPrimary as $intKey=>$varData){
     if(is_array($varData) && array_key_exists('conditions',$varData)){//let's check for conditions
       $arrConditions = json_decode($varData['conditions'],TRUE);
       foreach($arrConditions as $varCondition){
        if(is_array($varCondition)){
         foreach($varCondition as $varIndex=>$strValue){
           //this is an == operator that get's split on retrieval
           if(!stristr($varIndex,'condition'))
            $strValue = $varIndex.'='.$strValue;
           if(!PCMW_StringComparison::Get()->MakeStringComparison($strValue,$arrData)){
              unset($arrPrimary[$intKey]);//skip this header, it's not applicable here
              continue 2;
           }
         }
         //all passed
         $arrPrimary[$intKey] = $varData[0];
        }
        else{
          if(!PCMW_StringComparison::Get()->MakeStringComparison($varCondition,$arrData))
              unset($arrPrimary[$intKey]);//skip this header, it's not applicable here
          else{
            $arrPrimary[$intKey] = $varData[0];
          }
        }
       }
     }
    }
    //$strFormData = var_export($arrPrimary,TRUE);
    //PCMW_Logger::Debug('$arrPrimary ['.$strFormData.'] {'.__LINE__.'} ['.__METHOD__.']'."\r\n",1);
    return TRUE;
  }

  /**
  * check a browser for cookies
  * @return bool
  */
  function CheckBrowserCookies(){
    $arrGET = filter_var_array($_GET,FILTER_SANITIZE_STRING);
    if( !isset( $_SESSION['cookies_ok'] ) ) {
      if( isset( $arrGET['cookie_test'] ) ) {
        if( !isset( $_COOKIE['PHPSESSID'] ) ) {
            return FALSE;
        }
        else {
            $_SESSION['cookies_ok'] = true;
            return TRUE;
        }
      }
      if( !isset( $_COOKIE['PHPSESSID'] ) ){
          PCMW_Abstraction::Get()->RedirectUser($_SERVER['REQUEST_URI'].'&cookie_test=1');
          exit();
      }
    }
    return TRUE;
  }

  /**
  * prepare mail blast array for select box display
  * @return array
  */
  public static function GetMailBlastList(){
    if($arrBlasts = PCMW_Database::Get()->GetMailBlastHistory()){
      $arrFirstOption = array(0=>'Select');
      $arrOptions = PCMW_Utility::Get()->MakeNameValueArray('mailblastid','mailsubject',$arrBlasts,$arrFirstOption);               
      return $arrOptions;
    }
    return array();
  }

  /**
  * load the redirects into an associative array
  * @return array
  */
  function Load404Redirects(){
    if(!PCMW_Database::Get()->CheckForTable('PC_404redirects')){
      $_SESSION['PC_noRedirects'] = 1;
      return TRUE;
    }
    $arrRedirects = PCMW_404Redirect::Get()->Get404Redirects();
    $arrResultedRedirects = array();
    if(is_array($arrRedirects) && sizeof($arrRedirects) > 0){
      foreach($arrRedirects as $arrRedirect){
        $arrResultedRedirects[rtrim($arrRedirect['404page'],'/')] = $arrRedirect['404redirect'];
      }
    }
    return $arrResultedRedirects;
  }

  /**
  * gather up the Debug log data and last error
  * @return array()
  */
  function GatherDebugData(){
   $arrErrorData = array();
   $arrErrorData['lasterror'] = error_get_last();
   //get the debug log data for today
   $strLogFile = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Logs'.DIRECTORY_SEPARATOR.'LOG_'.date('Y_m_d',time()).'.txt';
   $arrErrorData['debuglog'] = $this->read_r($strLogFile);
   //all done
   return $arrErrorData;
  }


  /**
  * validate Nonce
  * @param $arrPOST
  * @return bool
  */
  function ValidateNonce(&$arrPOST){
    if(array_key_exists('wp_nonce',$arrPOST) &&
       trim($arrPOST['wp_nonce']) != '' &&
       array_key_exists('submissionid',$arrPOST) &&
       trim($arrPOST['submissionid']) != ''){
       if(!wp_verify_nonce($arrPOST['wp_nonce'],$arrPOST['submissionid']))
        return FALSE;
       unset($arrPOST['wp_nonce']);
       unset($arrPOST['submissionid']);
       //good to go
       return TRUE;
    }
    return FALSE;
  }

  /**
  * get the available roles
  * @return array()  
  */
  public static function GetRoles(){
   $objWPRoles = new WP_Roles();
   return $objWPRoles->get_names();
  }
}//end class
?>