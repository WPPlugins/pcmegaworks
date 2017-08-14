<?php
/**************************************************************************
* @CLASS PCMW_CSSInterface
* @brief Handle all things CSS related for various interfaces.
* @REQUIRES:
*  -???.php
*
**************************************************************************/
class PCMW_CSSInterface{

   //javascript handlers
   var $strJavaScriptRegisters = '';
   //make JS handlers when not using Ajax
   var $boolMakeJSRegister = TRUE;
   //debugging
   var $boolDebugOn = FALSE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_CSSInterface();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
  }

  #REGION UTILITY METHODS
  /**
  * get the available tags for editing ( body style )
  * @return array
  */
  function GetAvailableEditTags(){
    $arrAvailableTags = array('body'=>'Body',
    						  'content'=>'Whole page',
    						  'p'=>'Paragraph',
    						  'h1'=>'H1 X Large Heading',
    						  'h2'=>'H2 Large Heading',
    						  'h3'=>'H3 Medium Heading',
    						  'h4'=>'H4 Small Heading',
    						  'h5'=>'H5 X Small Heading',
    						  'h6'=>'H6 Tiny Heading',
    						  'a'=>'Links',
    						  'li'=>'Lists',
    						  'input'=>'Input',
    						  'textarea'=>'Texta Area',
    						  'button'=>'Button',
    						  'select'=>'Select',
    					);
    return $arrAvailableTags;
  }

  /**
  * get the array of available supported css types we will offer interface values for
  * @param $boolIncludeClass - include class options
  * @return array()
  */
  function GetOfferedCSSProperties($boolIncludeClass = TRUE){
   $arrProperties = array('background-color',
                          'color',
                          'padding',
                          'margin',
                          'font-family',
                          'font-size',
                          'border',
                          'text-decoration',
                          'text-align',
   );
   if($boolIncludeClass){
    $arrProperties[] = 'class';
    $arrProperties[] = 'alternate-class';
   }
   return $arrProperties;
  }

  /**
  * given a property, load the potential defaults for it
  * @param $strProperty
  * @return array() || FALSE
  */
  function GetPropertyDefaults($strProperty){
    switch($strProperty){
     case 'color':
        return array('strnewtype'=>'color','varvalue'=>'','default'=>'#000000');
     break;
     case 'background-color':
        return array('strnewtype'=>'color','varvalue'=>'','default'=>'#FFFFFFF');
     break;
     case 'padding':
        return array('strnewtype'=>'text','varvalue'=>'','default'=>'0px');
     break;
     case 'margin':
        return array('strnewtype'=>'text','varvalue'=>'','default'=>'0px');
     break;
     case 'font-size':
        return array('strnewtype'=>'text','varvalue'=>'','default'=>'16px');
     break;
     case 'border':
        return array('strnewtype'=>'text','varvalue'=>'','default'=>'0px solid #FFFFFF');
     break;
     case 'font-family':
        return array('strnewtype'=>'select','arroptions'=>array(array('varvalue'=>'Georgia, serif','strText'=>'Georgia, serif'),
                                                                array('varvalue'=>'"Palatino Linotype", "Book Antiqua", Palatino, serif','strText'=>'"Palatino Linotype", "Book Antiqua", Palatino, serif'),
                                                                array('varvalue'=>'"Times New Roman", Times, serif','strText'=>'"Times New Roman", Times, serif'),
                                                                array('varvalue'=>'Arial, Helvetica, sans-serif','strText'=>'Arial, Helvetica, sans-serif'),
                                                                array('varvalue'=>'"Arial Black", Gadget, sans-serif','strText'=>'"Arial Black", Gadget, sans-serif'),
                                                                array('varvalue'=>'"Comic Sans MS", cursive, sans-serif','strText'=>'"Comic Sans MS", cursive, sans-serif'),
                                                                array('varvalue'=>'Impact, Charcoal, sans-serif','strText'=>'Impact, Charcoal, sans-serif'),
                                                                array('varvalue'=>'"Lucida Sans Unicode", "Lucida Grande", sans-serif','strText'=>'"Lucida Sans Unicode", "Lucida Grande", sans-serif'),
                                                                array('varvalue'=>'Tahoma, Geneva, sans-serif','strText'=>'Tahoma, Geneva, sans-serif'),
                                                                array('varvalue'=>'"Trebuchet MS", Helvetica, sans-serif','strText'=>'"Trebuchet MS", Helvetica, sans-serif'),
                                                                array('varvalue'=>'Verdana, Geneva, sans-serif','strText'=>'Verdana, Geneva, sans-serif'),
                                                                array('varvalue'=>'"Courier New", Courier, monospace','strText'=>'"Courier New", Courier, monospace'),
                                                                array('varvalue'=>'"Lucida Console", Monaco, monospace','strText'=>'"Lucida Console", Monaco, monospace')),'default'=>'Tahoma, Geneva, sans-serif');
     break;
     case 'text-decoration':
        return array('strnewtype'=>'select','arroptions'=>array(array('varvalue'=>'none','strText'=>'None'),
                                                                array('varvalue'=>'underline','strText'=>'Underline'),
                                                                array('varvalue'=>'overline','strText'=>'Overline'),
                                                                array('varvalue'=>'line-through','strText'=>'Line Through'),
                                                                array('varvalue'=>'initial','strText'=>'Default'),
                                                                array('varvalue'=>'inherit','strText'=>'Inherit from parent')),'default'=>'none');
     break;
     case 'text-align':
        return array('strnewtype'=>'select','arroptions'=>array(array('varvalue'=>'left','strText'=>'Left'),
                                                                array('varvalue'=>'center','strText'=>'Center'),
                                                                array('varvalue'=>'right','strText'=>'Right')),'default'=>'left');
     break;
     default:
        return FALSE;
    }
  }

  /**
  * create a datalist for CSS selection
  * @param $strListId
  * @param $intCount
  * @param $strValue - pre-existing value
  * @return string ( HTML )
  */
  function MakeCSSDataList($strListId,$intCount,$strKey,$strValue=''){
    (int)$intCount++;
    $arrAttributes = array('strOptionId'=>$strListId.'_'.$intCount);
    $this->MakeJavaScriptRegister($arrAttributes);
    $strDataList = '<div style="width:100%;">';
    $strDataList .= '<input list="dl_'.$strListId.'_'.$intCount.'_" name="'.$strListId.'_'.$intCount.'" id="'.$strListId.'_'.$intCount.'" placeholder="Option" value="'.$strKey.'" style="width:inherit;">';
    $strDataList .= '<datalist id="dl_'.$strListId.'_'.$intCount.'_">';
    foreach($this->GetOfferedCSSProperties() as $strProperty)
        $strDataList .= '<option value="'.$strProperty.'">';
    $strDataList .= '</datalist> = ';
    if($strKey != ''){
        $varProperties = $this->GetPropertyDefaults($strKey);
        if($varProperties){
           if($varProperties['strnewtype'] == 'select'){
             $strDataList .= '<input list="sa_'.$strListId.'_'.$intCount.'" name="'.$strListId.'_'.$intCount.'_value" id="'.$strListId.'_'.$intCount.'_value" placeholder="Value" value="'.$strValue.'" onblur="LoadBodyStyleSample(\''.$strListId.'\',\''.$intCount.'\',this.value);" >';
             $strDataList .= '<datalist id="sa_'.$strListId.'_'.$intCount.'">';
             foreach($varProperties['arroptions'] as $arrValues){
                $strSelected = ($arrValues['varvalue'] == $strValue)? ' SELECTED ': '';
                $strDataList .= '<option value="'.$arrValues['varvalue'].'" '.$strSelected.' >'.$arrValues['strText'].'</option>';
             }
             $strDataList .= '</datalist>';
           }
           else{
          //make our input
            $strDataList .= '<input type="'.$varProperties['strnewtype'].'" name="'.$strListId.'_'.$intCount.'_value" id="'.$strListId.'_'.$intCount.'_value" placeholder="Value" value="'.$strValue.'" onblur="LoadBodyStyleSample(\''.$strListId.'\',\''.$intCount.'\',this.value);" />';
          }
        }
        else
            $strDataList .= '<input type="text" name="'.$strListId.'_'.$intCount.'_value" id="'.$strListId.'_'.$intCount.'_value" placeholder="Value" value="'.$strValue.'" onblur="LoadStyleSample(\''.$strListId.'\',this.form);" />';
    }
    else
        $strDataList .= '<input type="text" name="'.$strListId.'_'.$intCount.'_value" id="'.$strListId.'_'.$intCount.'_value" placeholder="Value" value="'.$strValue.'" onblur="LoadStyleSample(\''.$strListId.'\',this.form);" />';
    $strDataList .= '<i class="fa fa-close fa-1x text-danger" onclick="RemoveElementProperty(\''.$strListId.'_'.$intCount.'\');">&nbsp;</i>';
    $strDataList .= '</div>';
    $strDataList .= $this->MakeJavaScriptTrailer();
    return $strDataList;
  }

  /**
  * make a JavaScript handler option
  * @param arrAttributes
  * @return bool
  */
  function MakeJavaScriptRegister($arrAttributes){
    $this->strJavaScriptRegisters .= 'RegisterCSSHandler(JSON.parse(\''.json_encode($arrAttributes).'\'));'."\r\n";
    return TRUE;
  }

  /**
  * add JavaScript execution trailer
  * @return string
  */
  function MakeJavaScriptTrailer(){
    if(!$this->boolMakeJSRegister)
        return '';
    //add controls for JavScript handlers
    $strTrailer = "\r\n".'<script>'."\r\n";
    $strTrailer .= $this->strJavaScriptRegisters;
    $strTrailer .= '</script>'."\r\n";
    return $strTrailer;
  }

  /**
  * create a datalist for CSS selection
  * @param $strTag
  * @param $arrExistingValues
  * @param $arrAvailableProperties
  * @return string ( HTML )
  */
  function MakeCSSBodyDataList($strTag,$arrExistingValues,$arrAvailableProperties){
    $strDataList = '<div style="width:100%;" id="'.$strTag.'">';
    $strDataList .= '<input type="button" class="btn btn-primary" value="Add '.$strTag.' option" onclick="LoadNewCSSInterface(\''.$strTag.'\',GEO(\''.$strTag.'\',\'\'))"/>';
      foreach($arrExistingValues[$strTag] as $strStartingProperty=>$strStartingValue){
      //make our JS handler register
      $arrAttributes = array('strOptionId'=>$strTag.'_'.$strStartingProperty);
      $this->MakeJavaScriptRegister($arrAttributes);
      $varProperties = $this->GetPropertyDefaults($strStartingProperty);
      $strDataList .= '<div>';
      $strDataList .= '<input list="bc_'.$strTag.'_'.$strStartingProperty.'" name="'.$strTag.'_'.$strStartingProperty.'" id="'.$strTag.'_'.$strStartingProperty.'" placeholder="Option" value="'.$strStartingProperty.'" style="width:inherit;">';
      $strDataList .= '<datalist id="bc_'.$strTag.'_'.$strStartingProperty.'">';
      foreach($this->GetOfferedCSSProperties() as $strProperty)
        $strDataList .= '<option value="'.$strProperty.'">';
        $strDataList .= '</datalist>';
        if($varProperties){
           if($varProperties['strnewtype'] == 'select'){
             $strDataList .= '<input list="sa_'.$strTag.'_'.$strStartingProperty.'" name="'.$strTag.'_'.$strStartingProperty.'_value" id="'.$strTag.'_'.$strStartingProperty.'_value" placeholder="Value" value="'.$strStartingValue.'" onblur="LoadBodyStyleSample(\''.$strTag.'\',\''.$strStartingProperty.'\',this.value);" >';
             $strDataList .= '<datalist id="sa_'.$strTag.'_'.$strStartingProperty.'">';
             foreach($varProperties['arroptions'] as $arrValues){
                $strSelected = ($arrValues['varvalue'] == $strStartingValue)? ' SELECTED ': '';
                $strDataList .= '<option value="'.$arrValues['varvalue'].'" '.$strSelected.' >'.$arrValues['strText'].'</option>';
             }
             $strDataList .= '</datalist>';
           }
           else{
          //make our input
            $strDataList .= '<input type="'.$varProperties['strnewtype'].'" name="'.$strTag.'_'.$strStartingProperty.'_value" id="'.$strTag.'_'.$strStartingProperty.'_value" placeholder="Value" value="'.$strStartingValue.'" onblur="LoadBodyStyleSample(\''.$strTag.'\',\''.$strStartingProperty.'\',this.value);" />';
          }
        }
        else
            $strDataList .= '<input type="text" name="'.$strTag.'_'.$strStartingProperty.'_value" id="'.$strTag.'_'.$strStartingProperty.'_value" placeholder="Value" value="'.$strStartingValue.'" onblur="LoadBodyStyleSample(\''.$strTag.'\',\''.$strStartingProperty.'\',this.value);" />';
        //remove option
        $strDataList .= '<i class="fa fa-close fa-1x text-danger" onclick="RemoveElementProperty(\''.$strTag.'_'.$strStartingProperty.'\');">&nbsp;</i><br />';
        $strDataList .= '</div>';
      }
    $strDataList .= '</div>';
    $strDataList .= $this->MakeJavaScriptTrailer();
    return $strDataList;
  }

  /**
  * given the CSS filename, find the Theme options section and parse it into usable values
  * @param $strFileName
  * @return array
  */
  function ParseStyleSheet($strFileName='custom-style.css',$arrEditTags){
    $arrExistingCSS = array();
    $arrCSSClumpEnd = FALSE;
	$strExistingCSS = PCMW_Abstraction::Get()->read_r(get_theme_root( PCMT_THEMENAME ).DIRECTORY_SEPARATOR.PCMT_THEMEFOLDER.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.$strFileName);
    if($strExistingCSS){
      $arrCSSClumpStart = explode('/**** BODY CSS ****/',$strExistingCSS);
      $arrCSSClumpEnd = explode('/**** /BODY CSS ****/',$arrCSSClumpStart[1]);
    }
    //go through each potential declaration
    foreach($arrEditTags as $strTag=>$strLabel){
        $arrExistingCSS[$strTag] = array();
        //does this option exist?
        $arrCSSClumpEnd[0] = str_replace("\r\n","",$arrCSSClumpEnd[0]);
        if($arrCSSClumpEnd && preg_match_all("/".$strTag."{([^}]*)}/", $arrCSSClumpEnd[0], $arrMatch)){
          //$arrMatch[0][0] = str_replace("\r\n","",$arrMatch[0][0]);
            //replace our delimiter
            //$strLead = (".pcmt_".);
            $strValues = str_replace(array(".pcmt_",$strTag."{","}"),array("","",""),stripslashes($arrMatch[0][0]));
            $arrDeclarations = explode(';',trim($strValues));
            $arrDeclarations = array_filter($arrDeclarations, 'strlen');
            //break down our properties
            foreach($arrDeclarations as $strDeclaration){
              $arrCSSParts = explode(':',trim($strDeclaration));
              $arrExistingCSS[$strTag][$arrCSSParts[0]] = $arrCSSParts[1];
            }
        }
    }
    return $arrExistingCSS;
  }

  /**
  * scope resolved method to save global CSS
  * @param $arrValues
  * @return bool
  */
  function SaveSiteCSS($arrValues){
    $strCustomBodyStyle = get_theme_root( PCMT_THEMENAME ).DIRECTORY_SEPARATOR.PCMT_THEMEFOLDER.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'custom-style.css';
    $arrEditTags = $this->GetAvailableEditTags();
    $arrExistingStyle = $this->ParseStyleSheet('custom-style.css',$arrEditTags);
    $arrCSSProperties = $this->GetOfferedCSSProperties(FALSE);
    $strNewBodyCSS = '/**** BODY CSS ****/'."\r\n";
    $arrProperties = array();//we make a copy for comparison
    foreach($arrEditTags as $strTag=>$strLabel){
        $arrProperties[$strTag] = array();
        $strNewBodyCSS .= '.pcmt_'.$strTag.'{'."\r\n";
        foreach($arrCSSProperties as $strPropertyName){
          if(array_key_exists($strTag.'_'.$strPropertyName,$arrValues)){
            $arrDefault = $this->GetPropertyDefaults($strPropertyName);
            $strDefault = (trim($arrValues[$strTag.'_'.$strPropertyName.'_value']) == '')? $arrDefault['default']:$arrValues[$strTag.'_'.$strPropertyName.'_value'] ;
            $strNewBodyCSS .= $strPropertyName.':'.$strDefault.';'."\r\n";
            $arrProperties[$strTag][$strPropertyName] = 1;
          }
        }
        //find custom additions
        $intCustomCount = 1;
        while(array_key_exists($strTag.'_'.$intCustomCount,$arrValues)){
          if(trim($arrValues[$strTag.'_'.$intCustomCount]) != ''){
            $strNewBodyCSS .= $arrValues[$strTag.'_'.$intCustomCount].':'.$arrValues[$strTag.'_'.$intCustomCount.'_value'].';'."\r\n";
            $arrProperties[$strTag][$arrValues[$strTag.'_'.$intCustomCount]] = 1;
          }
          $intCustomCount++;
        }
        //go back and get previous custom css
        foreach($arrExistingStyle[$strTag] as $strCustomProperty=>$strCustomValue){
          if(!array_key_exists($strCustomProperty,$arrProperties[$strTag]))
            $strNewBodyCSS .= $arrValues[$strTag.'_'.$strCustomProperty].':'.$arrValues[$strTag.'_'.$strCustomProperty.'_value'].';'."\r\n";
        }
        $strNewBodyCSS .= '}'."\r\n\r\n";
    }
    //$strNewBodyCSS .= ''."\r\n";
    //$strNewBodyCSS .= ''."\r\n";
    $strNewBodyCSS .= '/**** /BODY CSS ****/';
    $strExistingCSS = PCMW_Abstraction::Get()->read_r($strCustomBodyStyle);
    $arrCSSClumpStart = explode('/**** BODY CSS ****/',$strExistingCSS);
    $arrCSSClumpEnd = explode('/**** /BODY CSS ****/',$arrCSSClumpStart[1]);
    $strNewCSS .= $arrCSSClumpStart[0].$strNewBodyCSS.$arrCSSClumpEnd[1];
    //wrap it up
    return PCMW_Abstraction::Get()->write_w($strCustomBodyStyle,$strNewCSS);
  }

  #ENDREGION


  /*
  @brief Get the methods available for debugging
  *keep this at the top of the class for easy access and update
  @return array()
  */
  function GetDebugMethods(){
    $arrDebugMethods = array();
    $arrDebugMethods['PCMW_CSSInterface::CLASSMETHOD'] = 1;
    return $arrDebugMethods;
  }

  /*
  @brief store and display debug information
  @param $strMessage,$boolLogNow
  @param boolean
  */
  function PCMW_LoadDebugLog($strMessage='',$boolLogNow=FALSE,$strMethod='',$boolShowBackTrace=FALSE){
  //$this->PCMW_LoadDebugLog('VALUE ['.$arrPOST .']  LINE '.__LINE__."\r\n",FALSE,__METHOD__);
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
}//end class
?>