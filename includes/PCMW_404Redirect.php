<?php
/**************************************************************************
* @CLASS PCMW_404Redirect
* @brief Insert, update, delete or get 404 redirects.
* @REQUIRES:
*  -PCMW_Database.php
*  -PCMW_Abstraction.php
*  -PCMW_FormManagerCore.php
*
**************************************************************************/
class PCMW_404Redirect{
   //debugging
   var $boolDebugOn = FALSE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_404Redirect();
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
    $arrDebugMethods['PCMW_404Redirect::CLASSMETHOD'] = 1;
    return $arrDebugMethods;
  }

  /**
  * given the post data, clean, validate and insert it
  * @return bool
  */
  function Process404RedirectForm($arrPOST){
   $arrPOST = filter_var_array($arrPOST,FILTER_SANITIZE_STRING);
   if(array_key_exists('action',$arrPOST) &&
      $arrPOST['action'] == 'new404' &&
      wp_verify_nonce($arrPOST['wp_nonce'],$arrPOST['submissionid'])){
      $this->RemoveSuffix($arrPOST['404page'],$arrPOST['404redirect']);
      if(!is_array($_SESSION['PC_Redirects']))
          $_SESSION['PC_Redirects'] = array();
      //check for an ID, and update or insert accordingly
      if(array_key_exists('redirectid',$arrPOST) && (int)$arrPOST['redirectid'] > 0){
        //get our redirect so we can update the session variable
        if($arrRedirect = $this->Get404Redirects($arrPOST['redirectid'])){
          if($this->Update404Redirect($arrPOST['redirectid'],$arrPOST['404page'],$arrPOST['404redirect'])){
            $_SESSION['PC_Redirects'][$arrRedirect[0]['404page']] = $arrPOST['404redirect'];
            unset($_SESSION['PC_noRedirects']);
            return TRUE;
          }
        }
        return FALSE;
      }
      else{
        if($this->InsertNewRedirect($arrPOST['404page'],$arrPOST['404redirect'])){
            $_SESSION['PC_Redirects'][$arrPOST['404page']] = $arrPOST['404redirect'];
            unset($_SESSION['PC_noRedirects']);
            return TRUE;
        }
        return FALSE;
      }
   }
   else return FALSE;
  }

  /**
  * remove trailing and leading slashes and http designation
  * @param $str404Page
  * @param $str404Redirect
  * @return bool
  */
  function RemoveSuffix(&$str404Page,&$str404Redirect){
   $str404Page = rtrim($str404Page,'/');
   $str404Redirect = rtrim($str404Redirect,'/');
   return TRUE;
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
  function MakePCMW_404RedirectTable(){
    $arrTableData = array();
    $arrTableData['tabledescription'] = 'Manage 404 Redirects';
    //define our columns
    $arrTableData['tableheader'] = array('redirectid'=>'ID',
                                         '404page'=>'404 page',
                                         '404redirect'=>'Redirect',
                                         'delete'=>'Delete',
                                         'update'=>'Update');
    //make the vendor table data
    $arrTableData['tabledata'] = array();
    if(is_array($arr404Redirects = $this->Get404Redirects()) > 0 && sizeof($arr404Redirects) > 0){
      foreach($arr404Redirects as $arr404Redirect){
        $arrTableData['tabledata'][$arr404Redirect['redirectid']] = array();
        $arrTableData['tabledata'][$arr404Redirect['redirectid']]['rowid'] = 'rowid_'.$arr404Redirect['redirectid'];
        foreach($arrTableData['tableheader'] as $strKey=>$strValue){
          $arrTableData['tabledata'][$arr404Redirect['redirectid']][$strKey] = array();
          if($strKey == 'delete'){
            $arrTableData['tabledata'][$arr404Redirect['redirectid']][$strKey]['linkbadge'] = 'fa fa-1x fa-exclamation-triangle';
            $arrTableData['tabledata'][$arr404Redirect['redirectid']][$strKey]['linkclass'] = 'btn btn-danger';
            @$arrTableData['tabledata'][$arr404Redirect['redirectid']][$strKey]['value'] = $strValue;
            $strOnClick = 'AddAnonymousAction(\'delete404\','.$arr404Redirect['redirectid'].')';
            $arrTableData['tabledata'][$arr404Redirect['redirectid']][$strKey]['onclickvalue'] = $strOnClick;
          }
          else if($strKey == 'update'){
            $arrTableData['tabledata'][$arr404Redirect['redirectid']][$strKey]['linkbadge'] = 'fa fa-1x fa-cog';
            $arrTableData['tabledata'][$arr404Redirect['redirectid']][$strKey]['linkclass'] = 'btn btn-primary';
            @$arrTableData['tabledata'][$arr404Redirect['redirectid']][$strKey]['value'] = $strValue;
            $strOnClick = 'GetFormAndDataByAlias(\'404redirect\','.$arr404Redirect['redirectid'].',1,0)';
            $arrTableData['tabledata'][$arr404Redirect['redirectid']][$strKey]['onclickvalue'] = $strOnClick;
          }
          else
              $arrTableData['tabledata'][$arr404Redirect['redirectid']][$strKey]['value'] = $arr404Redirect[$strKey];
        }
      }
    }
    else{
      PCMW_Abstraction::Get()->AddUserMSG( 'No redirects exist, please create one. ['.__LINE__.']',2);
      return FALSE;
    }
    return PCMW_FormManager::Get()->MakeBootStrapTable($arrTableData);
  }


  #ENDREGION



  #REGION DATABASECALLS
  //All functions which will interact with Database.php should go in here

  /**
  * given a redirect ID, or nothing get the redirects
  * @param $intRedirectId
  * @return array
  */
  function Get404Redirects($intRedirectId=0){
    if($arrRedirect = PCMW_Database::Get()->Get404Redirects($intRedirectId)){
      return $arrRedirect;
    }
    return FALSE;
  }

  /**
  * given a redirect ID, delete it
  * @param $intRedirectId
  * @return bool
  */
  function Delete404Redirect($intRedirectId){
    if((int)$intRedirectId < 1)
      return FALSE;
    else if(PCMW_Database::Get()->Delete404Redirect($intRedirectId)){
      PCMW_Abstraction::Get()->AddUserMSG( 'Redirect deleted correctly. ['.__LINE__.']',3);
      return TRUE;
    }
    else{
      PCMW_Abstraction::Get()->AddUserMSG( 'Could not delete this redirect. ['.__LINE__.']',1);
      return FALSE;
    }
  }

  /**
  * given a redirect ID, page and redirect update a redirect
  * @param $intRedirectId
  * @param $str404Page
  * @param $str404Redirect
  * @return bool
  */
  function Update404Redirect($intRedirectId,$str404Page,$str404Redirect){
    if(trim($str404Page) == '' || trim($str404Redirect) == ''){
      PCMW_Abstraction::Get()->AddUserMSG( 'The URL '.$str404Page.' or the redirect '.$str404Redirect.' is blank. ['.__LINE__.']',1);
      return FALSE;
    }
    else if((int)$intRedirectId < 1){
      PCMW_Abstraction::Get()->AddUserMSG( 'We could not update this record. Please contact '.PCMW_SUPPORT.' for further assistance. ['.__LINE__.']',1);
      PCMW_Logger::Debug('Redirect ID ['.$intRedirectId.'] is not valid. METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
      return FALSE;
    }
    else if(PCMW_Database::Get()->Update404Redirect($intRedirectId,$str404Page,$str404Redirect)){
       //update our session
       $_SESSION['PC_Redirects'][$str404Page] = $str404Redirect;
       PCMW_Abstraction::Get()->AddUserMSG( 'Redirect Updated! ['.__LINE__.']',3);
       return TRUE;
    }
    else{
       PCMW_Abstraction::Get()->AddUserMSG( 'We could not update this redirect. Please contact '.PCMW_SUPPORT.' for further assistance. ['.__LINE__.']',1);
       PCMW_Logger::Debug('Redirect ID ['.$intRedirectId.'] could not be updated for some reason. METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
       return FALSE;
    }
  }

  /**
  * given a page and redirect, insert a new record
  * @param $str404Page
  * @param $str404Redirect
  * @return int
  */
  function InsertNewRedirect($str404Page,$str404Redirect){
    if(trim($str404Page) == '' || trim($str404Redirect) == ''){
      PCMW_Abstraction::Get()->AddUserMSG( 'The URL '.$str404Page.' or the redirect '.$str404Redirect.' is blank. ['.__LINE__.']',1);
      return FALSE;
    }
    else if($intRedirectId = PCMW_Database::Get()->Insert404Redirect($str404Page,$str404Redirect)){
       //update our session
       $_SESSION['PC_Redirects'][$str404Page] = $str404Redirect;
       PCMW_Abstraction::Get()->AddUserMSG( 'Redirect Inserted! ['.__LINE__.']',3);
       return TRUE;
    }
    else{
       PCMW_Abstraction::Get()->AddUserMSG( 'We could not insert this redirect. Please contact '.PCMW_SUPPORT.' for further assistance. ['.__LINE__.']',1);
       PCMW_Logger::Debug('Redirect ID ['.$intRedirectId.'] could not be inserted for some reason. METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
       return FALSE;
    }
  }
  #ENDREGION

  #REGION DEBUGGINGMETHODS

  /*
  @brief store and display debug information
  @param $strMessage,$boolLogNow
  @param boolean
  */
  function LoadDebugLog($strMessage='',$boolLogNow=FALSE,$strMethod='',$boolShowBackTrace=FALSE){
  //$this->LoadDebugLog('VALUE ['.$arrPOST .']  LINE ['.__LINE__."]\r\n",FALSE,__METHOD__);
    //Abstraction::Get()->AddUserMSG( 'message METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
    if($this->boolDebugOn){
      if($strMessage != "")
      //add the message
        $this->strDebugMessage .= $strMessage;
      if($boolLogNow){
        if($strMethod == "" || ($strMethod != "" && array_key_exists($strMethod,$this->GetDebugMethods()))){
          if((int)$this->boolDebugOn == 2)
            Abstraction::Get()->AddUserMSG( '$this->strDebugMessage ['.$this->strDebugMessage.'] METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',9);
          else
            Debug::Debug_er($this->strDebugMessage.' METHOD ['.$strMethod.']  this method ['.__METHOD__.'] LINE ['.__LINE__.']',$this->intDebugLevel,$boolShowBackTrace);
        }
        //clean up
        $this->strDebugMessage = '';
      }
    }
  }

  #ENDREGION

}//end class  
?>