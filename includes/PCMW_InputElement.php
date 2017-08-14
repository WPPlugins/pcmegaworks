<?php
/**************************************************************************
* @CLASS PCMW_InputElement
* @brief take a database row for an element definition and construct it
* @REQUIRES:
*  -PCMW_Database.php
*  -PCMW_Element.php
*  -PCMW_FormDefinition.php
*  -PCMW_StaticArraysCore.php
*
**************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_StaticArraysCore.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_FormDefinition.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_Element.php');
class PCMW_InputElement{
  //these are not handled with simple element declaration and value placement
   var $arrSpecialElementTypes = array('select'=>array('select','selectoption','multiselect'),
                                       'textarea'=>array('textarea','count','accordion','accordionsubmit'),
                                       'input'=>array('checkbox','checkboxnt','datalist'),
                                       'table'=>'table',
                                       'a'=>array('a','img'),
                                       'div'=>array('time') );
   //these elements self terminate, and cannot have content
   //between the tag declaration
   var $arrSelfClosingElements = array('img',
                                       'br',
                                       'hr',
                                       'area',
                                       'base',
                                       'basefont',
                                       'link',
                                       'meta',
                                       'param' );
   //these elements have nested elements
   var $arrNestedElements = array('ul',
                                  'div',
                                  'span');
   var $objElement = NULL;
   var $objInputDefinition = NULL;
   var $intClientId = 0;
   var $intProductId = 0;
   var $arrMiscValues = array();
   var $arrPOST = array();
   var $boolAllowEdits = TRUE;
   var $boolViewOnly = FALSE;
   var $intTotalFormElements = 0;
   var $boolIsAdmin = FALSE;
   var $arrInputAttributes = array();
   //debugging
   var $boolDebugOn = FALSE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';
   var $arrTitleAttributes = array();//array('class'=>'clearleft formconstructorhead');

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_InputElement();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
    $this->boolIsAdmin = PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,PCMW_SUPERUSERS,FALSE,FALSE);
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
    $arrDebugMethods['PCMW_InputElement::GatherIndexValueCollection'] = 1;
    $arrDebugMethods['PCMW_InputElement::MakeEventHandlerReplacements'] = 1;
    $arrDebugMethods['PCMW_InputElement::MakeTableAsInput'] = 1;
    $arrDebugMethods['PCMW_InputElement::GatherIndexValueCollection'] = 1;
    $arrDebugMethods['PCMW_InputElement::MakeSpecialElement'] = 1;
    $arrDebugMethods['PCMW_InputElement::ProcessFormDefinition'] = 1;
    $arrDebugMethods['PCMW_InputElement::GetFormDataByAlias'] = 1;
    $arrDebugMethods['PCMW_InputElement::MakeCheckBoxCollection'] = 1;
    $arrDebugMethods['PCMW_InputElement::MMakeArrayReplacements'] = 1;
    return $arrDebugMethods;
  }

  /**
  * Check the passed string against the types specified in the member variable
  * @param string $strTagName the element tag name
  * @param string $strInputType the type and tag name of the element
  * @return bool
  */
  function CheckSpecialElementTypes($strTagName,$strInputType){
    if(array_key_exists($strTagName,$this->arrSpecialElementTypes)){
       if(is_array($this->arrSpecialElementTypes[$strTagName])){
         if(in_array($strInputType,$this->arrSpecialElementTypes[$strTagName]))
          return TRUE;
       }
       else{
         if($this->arrSpecialElementTypes[$strTagName] == $strInputType)
          return TRUE;
       }
    }
    return FALSE;
  }

  /**
  * check the total element count and make a simple array of values denoting order
  * @return array
  */
  function GetDefinitionCount(){
    $arrCountArray = array();
    for($i=1;$i<($this->intTotalFormElements + 1);$i++)
      $arrCountArray[$i] = $i;
    return $arrCountArray;
  }

  /**
  * given an input string, and a potential source for predefined
  * data combine the two and return the HTML string for addition
    -determine the method to create the element and any special handling
    -create an element parent object to nest the input in
    -create and de-macro the event handlers for this element
    -decompose and build the element attributes
    -construct the appropriate element structure
    -return the finished product
  * @param object $objInputDefinition definition table row to build from
  * @return string HTML
  */
  function ProcessFormDefinition(PCMW_FormDefinition $objInputDefinition, $arrPOSTData){
    $this->objInputDefinition = $objInputDefinition;
    $this->objElement = new PCMW_Element();
    $this->objElement->LoadHTMLTemplate('<'.$this->objInputDefinition->strElementParentType.' class="'.$this->objInputDefinition->strParentElementClass.'" id="'.$this->objInputDefinition->strDefinitionName.'_parent" ></'.$this->objInputDefinition->strElementParentType.'>');
    //load the expand option ===TEMP ===
    //$strSpan = '<i class="fa fa-arrows-v pointer"  data-toggle="collapse" data-target="#'.$this->objInputDefinition->strDefinitionName.'_parent"></i>';
    //$this->objElement->UpdateElementContent($this->objElement->objHTML,$strSpan);
    //let's replace our macros now
    $this->MakeEventHandlerReplacements($arrPOSTData);
    //now let's load the attributes
    $this->arrInputAttributes = PCMW_Utility::Get()->DecomposeCurlString(htmlspecialchars_decode($this->objInputDefinition->strElementAttributes));
    //make sure we can use this definition
    $this->arrPOST = $arrPOSTData;
    if(!$this->CheckSpecialConditions())
        return FALSE;
    $this->LoadCommonEventHandlers();
    $this->BuildNonIterativeAttributes();
    //figure out what kind of input we're making
    //special imputs require special formatting
    $arrTypeParts = explode(':',$this->objInputDefinition->strElementType);
    $this->IsElementRequired($arrTypeParts[1]);
    //let's load the title, if one exists
    if($arrTypeParts[1] !== 'checkboxnt'){
    	$this->LoadElementTitle($arrTypeParts[1]);
    }
    if($this->CheckSpecialElementTypes($arrTypeParts[0],$arrTypeParts[1])){
      //since we are dealing with a group of special cases, we need to manually construct this
      return $this->MakeSpecialElement($arrTypeParts,$arrPOSTData);
    }
    // we do not have any special formatting for this element, so we will check
    //for self closing elements and nested elements
    else if(in_array($arrTypeParts[0],$this->arrSelfClosingElements)){
      //this is not a  simple attribute defined image
      if($arrTypeParts[0] == 'img' && $arrTypeParts[1] != 'img')
          $this->MakeHeadedImage($arrPOSTData,$arrTypeParts);
      //we're simply making an image for display purposes
      else{
        $strAttributes = '';
        //we will need to manually create this element
        foreach($this->arrInputAttributes as $ka=>$va){
          if($ka != "" && $ka != 'conditions')
            $strAttributes .= $ka.'="'.$va.'"';
        }
        $strNewContent = '<'.$arrTypeParts[0].' '.$strAttributes.' />';
        $this->objElement->UpdateElementContent($this->objElement->objHTML,$strNewContent);
      }
      //add a tooltip if we have one
      $this->AddToolTip();
      return $this->objElement->CloseDocument();
    }
    else if(in_array($arrTypeParts[0],$this->arrNestedElements)){
    $strContent = @$arrPOSTData[$this->objInputDefinition->strDefinitionName];
    if(@!array_key_exists($this->objInputDefinition->strDefinitionName,$arrPOSTData) && !$strContent = $this->GatherIndexValueCollection())
        $strContent = $this->objInputDefinition->strDefaultValues;
      $objParentElement = $this->objElement->AddChildNode($this->objElement->objHTML,$strContent,$arrTypeParts[0],$this->arrInputAttributes);
      //add a tooltip if we have one
      $this->AddToolTip();
      return $this->objElement->CloseDocument();
    }
     else if($arrTypeParts[0] == 'captcha'){
       //this should be a captcha
       $strCaptchaData = PCMW_Abstraction::Get()->MakeNewCaptcha();
       $this->objElement->AddChildNode($this->objElement->objHTML,$strCaptchaData,'div',$this->arrInputAttributes);
      //add a tooltip if we have one
      $this->AddToolTip();
       return $this->objElement->CloseDocument();
     }
    else{
     //these are standard inputs
      return $this->MakeStandardInput($arrTypeParts,$arrPOSTData);
    }
      //PCMW_Logger::Debug(' check METHOD ['.__METHOD__.'] LINE '.__LINE__,1);
  }

  #ENDREGION

  #REGION DATACALCULATION
  /**
  * check the loaded definition for the required flag
  * @param $strElementType
  * @return bool
  */
  function IsElementRequired($strElementType){
    if($this->objInputDefinition->intValidationId > 0 && $strElementType != 'hidden'){
     $arrInputAttributes = array('style'=>'color:#FF0000;font-size:20px;float:left;','title'=>'Required field');
     $this->objElement->AddChildNode($this->objElement->objHTML,'*','span',$arrInputAttributes);
    }
    return TRUE;
  }

  /**
  * given a string of columns and tables, extract build information about an
  * index:value collection
  *     - well formed string of tables and
  *       columns to gather table.column:table.column-table.column-table.column
  * The structure will be index:value-content-order
  * @return array
  */
  function GatherIndexValueCollection(){
  //Are we a nested function?
    $arrTypeParts = explode(':',$this->objInputDefinition->strElementType);
    if(stristr($this->objInputDefinition->strDefaultValues,"%")){
      //call a static function somewhere
      $this->objInputDefinition->strDefaultValues = str_replace("%",'',$this->objInputDefinition->strDefaultValues);
      if(is_callable($this->objInputDefinition->strDefaultValues,TRUE)){
        $arrArguments = array();
        //these are arguments we're sending to the function
        if(stristr($this->objInputDefinition->strDefaultValues,"#")){
          $arrParts = explode('#',$this->objInputDefinition->strDefaultValues);
          //reload the original value
          $this->objInputDefinition->strDefaultValues = $arrParts[0];
          //parse the arguments
          #TODO Make this accept macro values
          for($i=1;$i<sizeof($arrParts);$i++){
            if(stristr($arrParts[$i],'array|')){
              $strBracketsRemoved = str_replace(array("array|","|"),'',$arrParts[$i]);
              $arrArguments[] = PCMW_Utility::Get()->DecomposeCurlString($strBracketsRemoved);//this is all the arguments it can take
            }
            else if(stristr($arrParts[$i],'{')){
            //we must be looking for a member variable
              $strBracketsRemoved = str_replace(array("{","}"),'',$arrParts[$i]);
              if($strBracketsRemoved == 'FALSE'){
                  $arrArguments[] = FALSE;
              }
              else if($strBracketsRemoved == 'TRUE'){
                  $arrArguments[] = TRUE;
              }
              else{
                $arrArguments[] = $this->{$strBracketsRemoved};
              }
            }
            else if($arrParts[$i] == 'FALSE'){
                $arrArguments[] = FALSE;
            }
            else if($arrParts[$i] == 'TRUE'){
                $arrArguments[] = TRUE;
            }
            else
                $arrArguments[] = $arrParts[$i];
          }
        }
        try{
            $arrUserFunction = @call_user_func_array($this->objInputDefinition->strDefaultValues,$arrArguments);
        }
        catch(Exception $e){
         $strLastError = var_export($e,TRUE);
         $strOtherLastError = var_export(error_get_last());
         PCMW_Logger::Debug('$strLastError ['.$strLastError.'] $strOtherLastError ['.$strOtherLastError.'] METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
        }
        return $arrUserFunction;
      }
      else{
        if((int)$this->boolIsAdmin > 0)
            PCMW_Abstraction::Get()->AddUserMSG( 'function ['.$this->objInputDefinition->strDefaultValues.'] is not callable METHOD ['.__METHOD__.'] LINE ['.__LINE__.']',1);
      }
    }
    //Are we a collection from the static arrays table?
    else if(stristr($this->objInputDefinition->strDefaultValues,'purpose:')){
      //this should return a collection of static array data
      $arrValuePoints = explode(':',$this->objInputDefinition->strDefaultValues);
      if(stristr($arrValuePoints[1],"#")){
        $arrArguments = explode('#',$arrValuePoints[1]);
        $arrArguments = $this->MakeArrayReplacements($arrArguments);
        $arrStaticArray = PCMW_StaticArrays::Get()->LoadStaticArrayType($arrArguments[0],
                                                                        FALSE,
                                                                        @$arrArguments[1],
                                                                        @$arrArguments[2],
                                                                        @$arrArguments[3],
                                                                        @$arrArguments[4]);
      }
      else if(stristr($arrValuePoints[1],"@")){
        $arrValuePoints[1] = str_replace('@','',$arrValuePoints[1]);
        //we're adjusting the modiferonly member variable
        PCMW_StaticArrays::Get()->boolModifierOnly = TRUE;
        return PCMW_StaticArrays::Get()->LoadStaticArrayType($arrValuePoints[1],
                                                                        FALSE);
      }
      else{
        $arrStaticArray = PCMW_StaticArrays::Get()->LoadStaticArrayType($arrValuePoints[1],
                                                                        FALSE);  
      }
      $arrStaticArray = $this->MakeArrayReplacements($arrStaticArray);
      return $arrStaticArray;
    }
    //Are we a collection of database tables?
    else if(stristr($this->objInputDefinition->strDefaultValues,':')){
      $arrReturnList = array();
      //check for prepended values
      if(stristr($this->objInputDefinition->strDefaultValues,'+')){
        $arrDefaultValueParts = explode('+',$this->objInputDefinition->strDefaultValues);
        $this->objInputDefinition->strDefaultValues = $arrDefaultValueParts[0];
        $arrReturnList = PCMW_Utility::Get()->DecomposeCurlString($arrDefaultValueParts[1]);
      }
      //break at the primary separator
      $arrValuePoints = explode(':',$this->objInputDefinition->strDefaultValues);
      $arrTableComponents = array();
      //this should leave us with decimal delimited strings of table and column
      foreach($arrValuePoints as $ka=>$va){
        $arrTableAndColumn = explode('.',$va);
        $arrTableComponents[$arrTableAndColumn[0]][] = $arrTableAndColumn[1];
      }
      //we should have an assembled array of tables and columns
      if(sizeof($arrTableComponents) == 1){
        //for simplicity at this point we're going to determine that a member
        //variable client id being > 0, means we are looking for a client ID
        //driven data set
        $strQuery = 'SELECT ';
        $arrIndexValueCollection = array();
        foreach($arrTableComponents as $strTableName=>$strColumnNames){
          //is this a simple index->name list?
          if(sizeof($strColumnNames) == 2){
            $arrIndexValueCollection = $strColumnNames;
          }
          if(sizeof($strColumnNames) > 2){
            $arrIndexValueCollection = $strColumnNames;
          }
          foreach($strColumnNames as $intIndex=>$strColumnName){
            $strComma = ($intIndex == (sizeof($strColumnNames) - 1))? '':',' ;
            $strQuery .= $strColumnName.$strComma;
          }
          $strQuery  .= ' FROM '.$strTableName;
        }
        //let's run the query now
        if(($arrCollection = PCMW_Database::Get()->RunRawQuery($strQuery))){
          foreach($arrCollection as $arrResults){
            $arrReturnList[$arrResults[$arrIndexValueCollection[0]]] = '';
            foreach($arrResults as $kb=>$vb){
             if($kb != $arrIndexValueCollection[0])
                $arrReturnList[$arrResults[$arrIndexValueCollection[0]]] .= $vb.' ';
            }
          }
        }
        return $arrReturnList;
      }
      else{
       //this is likely a join and we need to know more about the request
       //we will support this later
      }
    }
    else{
     //this is the value it is set at
        return $this->objInputDefinition->strDefaultValues;
    }
    return FALSE;
  }

  /**
  * given a special formatting type input, let's make the parent and children HTML
  * @param array $arrInputType tag name and type
  * @param array $arrInputAttributes assembled array of attributes
  * @param array $arrPOSTData data from the DB or post form
  * @return bool
  */
  function MakeSpecialElement($arrTypeParts,$arrPOSTData){
    if($arrTypeParts[1] != $arrTypeParts[0])
      $this->arrInputAttributes['type'] = $arrTypeParts[1];
    if($arrTypeParts[0] == 'textarea'){
      $strValue = '';
      if(array_key_exists($this->objInputDefinition->strDefinitionName,$this->arrPOST)
         && $this->arrPOST[$this->objInputDefinition->strDefinitionName] != '')
         $strValue = $this->arrPOST[$this->objInputDefinition->strDefinitionName];
         if(trim($strValue) == '' && trim($this->objInputDefinition->strDefaultValues) != '')
           $strValue = $this->objInputDefinition->strDefaultValues;
      //now add the count character container
      if($arrTypeParts[1] == 'count'){
         $this->arrInputAttributes['onkeyup'] = "return count_tag_chars(this,event,'".$this->objInputDefinition->strDefinitionName."_atb',".$this->objInputDefinition->intElementMax.")";
         $this->arrInputAttributes['class'] .= ' w100p pcmt_textarea';
         $this->objElement->AddChildNode($this->objElement->objHTML,$strValue,$arrTypeParts[0],$this->arrInputAttributes);
         $arrCountAttributes = array('id'=>$this->objInputDefinition->strDefinitionName.'_atb','name'=>$this->objInputDefinition->strDefinitionName.'_atb');
         $strCountContent = 'Available('.($this->objInputDefinition->intElementMax - strlen(stripslashes($strValue))).') Used (0)';
         $this->arrInputAttributes['class'] .= ' clearboth';
         $this->objElement->AddChildNode($this->objElement->objHTML,$strCountContent,'span',$arrCountAttributes);
      }
      else if($arrTypeParts[1] == 'accordion' || $arrTypeParts[1] == 'accordionsubmit'){
         //accordion textarea
         $strTitle = $this->objInputDefinition->strElementTitle;
         if(stristr($this->objInputDefinition->strElementTitle,':')){
            $arrTitleParts = explode(':',$this->objInputDefinition->strElementTitle);
            $strTitle = $arrTitleParts[1];
         }
         $strKey = rand(100,10000);
         $objTableAccordion = $this->objElement->AddChildNode($this->objElement->objHTML,'','div',array('class'=>'panel-group','id'=>'textaccordion_'.$this->objInputDefinition->strDefaultValues,'style'=>'display:none;'));
         $objPanelDefault = $this->objElement->AddChildNode($objTableAccordion,'','div',array('class'=>'panel panel-default'));
         $objPanelHeading = $this->objElement->AddChildNode($objPanelDefault,'','div',array('class'=>'panel-heading'));
         $objPanelTitle = $this->objElement->AddChildNode($objPanelHeading,'','h4',array('class'=>'panel-title pcmt_h4'));
         $this->objElement->AddChildNode($objPanelTitle,$strTitle,'a',array('class'=>'collapsed',
                                                                'aria-expanded'=>'false',
                                                                'href'=>'#collapse_'.$strKey.$this->objInputDefinition->strDefaultValues,
                                                                'data-parent'=>'#textaccordion_'.$this->objInputDefinition->strDefaultValues,
                                                                'data-toggle'=>'collapse'));
        //make the panel content
        $objPanelParent = $this->objElement->AddChildNode($objPanelDefault,'','div',array('class'=>'panel-collapse collapse',
                                                                                    'aria-expanded'=>'false',
                                                                                    'id'=>'collapse_'.$strKey.$this->objInputDefinition->strDefaultValues,
                                                                                    'style'=>'height: 0px;'));
         $this->objElement->AddChildNode($objPanelParent,$strValue,$arrTypeParts[0],$this->arrInputAttributes);
         if($arrTypeParts[1] == 'accordionsubmit')
            $this->objElement->AddChildNode($objPanelParent,'','input',array('type'=>'button','value'=>'Submit','onclick'=>'SubmitSelectedForm(this.form)'));
      }
      else{
         $this->objElement->AddChildNode($this->objElement->objHTML,$strValue,$arrTypeParts[0],$this->arrInputAttributes);
      }
    }
    if($arrTypeParts[0] == 'select'){
      //multi select needs to be defined in the parent element
      if($arrTypeParts[1] == 'multiselect'){
        $this->arrInputAttributes['multiple'] = 'multiple';
        $this->arrInputAttributes['name'] = ($this->arrInputAttributes['name'].'[]');
      }
      $objSelect = $this->objElement->AddChildNode($this->objElement->objHTML,'',$arrTypeParts[0],$this->arrInputAttributes);
      if($arrTypeParts[1] == 'selectoption')
         $this->objElement->AddChildNode($objSelect,'Select','option',array());
      foreach($this->GatherIndexValueCollection() as $ka=>$va){
         $arrOptions = array();
         if(@is_array($this->arrPOST[$this->objInputDefinition->strDefinitionName]) && in_array($ka,$this->arrPOST[$this->objInputDefinition->strDefinitionName])){
            $arrOptions['selected'] = 'selected';

         }
         else{
           if(@$this->arrPOST[$this->objInputDefinition->strDefinitionName] != ""
                           && $this->arrPOST[$this->objInputDefinition->strDefinitionName] == $ka)
                            $arrOptions['selected'] = 'selected';
         }
         $arrOptions['value'] = $ka;
         $this->objElement->AddChildNode($objSelect,$va,'option',$arrOptions);
      }

    }
    if($arrTypeParts[0] == 'input' && substr($arrTypeParts[1],0,8) == 'checkbox'){
      //this should be a checkbox
      $this->MakeCheckBoxCollection($this->arrPOST,$arrTypeParts);
    }
    if($arrTypeParts[0] == 'input' && $arrTypeParts[1] == 'datalist'){
      //duplicate these for customization
      $intputAttributes = $this->arrInputAttributes;
      $intputAttributes['list'] = $this->objInputDefinition->strDefinitionName.'_list';
      $intputAttributes['value'] = $this->arrPOST[$this->objInputDefinition->strDefinitionName];
      $this->objElement->AddChildNode($this->objElement->objHTML,'',$arrTypeParts[0],$intputAttributes);
      $objSelect = $this->objElement->AddChildNode($this->objElement->objHTML,'',$arrTypeParts[1],array('id'=>$intputAttributes['list']));
      foreach($this->GatherIndexValueCollection() as $ka=>$va){
         $arrOptions = array();
         $arrOptions['value'] = $va;
         $this->objElement->AddChildNode($objSelect,'','option',$arrOptions);
      }
    }
    if($arrTypeParts[0] == 'a'){
      //this should be a checkbox
      $this->MakeHyperLink($this->arrPOST,$arrTypeParts,$this->arrInputAttributes);
    }
    if($arrTypeParts[0] == 'table'){
    $this->PCMW_LoadDebugLog('$arrTypeParts[0] ['.$arrTypeParts[0] .'-  LINE '.__LINE__."\r\n",TRUE,__METHOD__);
      //this should be a checkbox
      $strTableData = $this->MakeTableAsInput($this->arrPOST,$arrTypeParts,$this->arrInputAttributes);
      $this->objElement->AddChildNode($this->objElement->objHTML,$strTableData,$arrTypeParts[0],$this->arrInputAttributes);
    }

    if($arrTypeParts[1] == 'time'){
    $this->PCMW_LoadDebugLog('$arrTypeParts[0] ['.$arrTypeParts[0] .'-  LINE '.__LINE__."\r\n",TRUE,__METHOD__);
      //this should be a checkbox
      $arrTimeForm = array('formalias'=>'time',
                           'isform'=>0  ,
                           'makesubmit'=>0,
                           'admingroupid'=>0);
      PCMW_FormManager::Get()->arrMiscValues['timeelement'] = $this->objInputDefinition->strDefinitionName;
      $arrTimeForm = PCMW_Utility::Get()->MergeArrays($this->arrPOST,$arrTimeForm);
      $strTimeForm = PCMW_FormManager::Get()->LoadFormGroupByAlias($arrTimeForm);
      $this->objElement->AddChildNode($this->objElement->objHTML,$strTimeForm,$arrTypeParts[0],$this->arrInputAttributes);
    }
    //add a tooltip if we have one
    $this->AddToolTip();
    return $this->objElement->CloseDocument();
  }

  /**
  * make a collection of checkboxes or a singular box
  * @param array $arrPOSTData collction of posted data
  * @return bool
  */
  function MakeCheckBoxCollection($arrPOSTData,$arrTypeParts){
    if($this->objInputDefinition->strDefaultValues == ""){
      $this->arrInputAttributes['type'] = 'checkbox';
      $this->arrInputAttributes['style'] = 'height:25px;width:25px;';
      $this->arrInputAttributes['name'] = $this->objInputDefinition->strDefinitionName;
      $this->arrInputAttributes['id'] = $this->objInputDefinition->strDefinitionName;
      $this->arrInputAttributes['onclick'] = $this->objInputDefinition->strOnclick;
      $this->arrInputAttributes['class'] = $this->objInputDefinition->strElementClass.' pcmt_checkbox';//.' form-control';
      $this->arrInputAttributes['value'] = $this->GatherIndexValueCollection();
      if($this->arrInputAttributes['value'] == '')
        $this->arrInputAttributes['value'] = $this->arrInputAttributes['name'];
      if((array_key_exists('checked',$arrPOSTData) && $arrPOSTData['checked'])
         || (@(string)trim($arrPOSTData[$this->objInputDefinition->strDefinitionName]) != '' && (string)trim($arrPOSTData[$this->objInputDefinition->strDefinitionName]) != '0')
         || @(string)trim($arrPOSTData[$this->objInputDefinition->strDefinitionName]) == 'true')//assuming the value of the box exists and is valid
         $this->arrInputAttributes['checked'] = TRUE;
      $this->objElement->AddChildNode($this->objElement->objHTML,'',$arrTypeParts[0],$this->arrInputAttributes);
      	$CustomCheckbox = $this->objElement->AddChildNode($this->objElement->objHTML,'','div',array('class'=>'btn-group','style'=>'border:aliceblue;'));
      	$Label1 = $this->objElement->AddChildNode($CustomCheckbox,$CustomCheckbox,'label',array('for'=>$this->objInputDefinition->strDefinitionName,'class'=>'btn btn-default'));
      	$Span1 = $this->objElement->AddChildNode($Label1,'','span',array('class'=>'glyphicon glyphicon-ok'));
      	$Span2 = $this->objElement->AddChildNode($Label1,'&nbsp;','span');
        if(stristr($this->objInputDefinition->strElementTitle,'notitle:'))
          $this->objInputDefinition->strElementTitle = str_replace('notitle:','',$this->objInputDefinition->strElementTitle);
      	$Label2 = $this->objElement->AddChildNode($CustomCheckbox,$this->objInputDefinition->strElementTitle,'label',array('for'=>$this->objInputDefinition->strDefinitionName,'class'=>'btn btn-default active','style'=>''));

    }
    else{
        $this->GatherIndexValueCollection();
    }
    return TRUE;
  }

  /**
  * check to see if the element uses a tooltip, and fill it if so
  * @return bool
  */
  function AddToolTip(){
   if(array_key_exists('tooltip',$this->arrInputAttributes) &&
      !stristr($this->objInputDefinition->strElementType,':hidden') &&
      !stristr($this->objInputDefinition->strElementType,'div') &&
      !stristr($this->objInputDefinition->strElementType,'span') &&
      !stristr($this->objInputDefinition->strElementType,':checkbox') &&
      !stristr($this->objInputDefinition->strElementType,':checkbox')){
      $this->arrInputAttributes = $this->MakeArrayReplacements( $this->arrInputAttributes);
     if(trim($this->arrInputAttributes['tooltip']) == '' && stristr($this->objInputDefinition->strElementTitle,'notitle'))
        $this->arrInputAttributes['tooltip'] = $this->objInputDefinition->strElementTitle;
     $this->arrInputAttributes['tooltip'] = str_replace('notitle:','',$this->arrInputAttributes['tooltip']);
     //return empties
     if($this->arrInputAttributes['tooltip'] == '') return TRUE;
     //load our attributes
     $arrToolTipAttributes = array('class'=>'fa fa-sticky-note fa-1x',
                                   'title'=>$this->arrInputAttributes['tooltip'],
                                   'data-toggle'=>'tooltip',
                                   'data-placement'=>'top');
     $this->objElement->AddChildNode($this->objElement->objHTML,'&nbsp;','i',$arrToolTipAttributes);
   }
   return TRUE;
  }

  /**
  * Check to see if special conditions exist to load or not load this element
  * @return bool
  */
  function CheckSpecialConditions(){
    if(esc_attr(@$_GET['page']) == 'manage-forms')
        return TRUE;
     if(is_array($this->arrInputAttributes) && array_key_exists('conditions',$this->arrInputAttributes)){//let's check for conditions
       $arrConditions = json_decode($this->arrInputAttributes['conditions']);         foreach($arrConditions as $varCondition){
        if(is_array($varCondition)){
         foreach($varCondition as $varIndex=>$strValue){
           //this is an == operator that get's split on retrieval
           if(!stristr($varIndex,'condition'))
            $strValue = $varIndex.'='.$strValue;
           if(!PCMW_StringComparison::Get()->MakeStringComparison($strValue,$this->arrPOST)){
              return FALSE;
           }
         }
        }
        else{
          if(!PCMW_StringComparison::Get()->MakeStringComparison($varCondition,$this->arrPOST))
            return FALSE;
        }
       }
     }
   //else
   return TRUE;
  }

  /**
  * make a hyperlink
  * @param $arrPOSTData
  * @param $arrTypeParts
  * @return bool
  */
  function MakeHyperLink($arrPOSTData,$arrTypeParts){
    if($arrTypeParts == 'img'){
      $this->objElement->AddChildNode($this->objElement->objHTML,'',$arrTypeParts[1],$this->arrInputAttributes);
    }
    else{
      $this->arrInputAttributes['href'] = $this->objInputDefinition->strOnclick;
      $this->arrInputAttributes['target'] = '_blank';
      $this->objElement->AddChildNode($this->objElement->objHTML,$this->objInputDefinition->strElementTitle,$arrTypeParts[0],$this->arrInputAttributes);
    }
    return TRUE;
  }

  /**
  * given an image tag build it with or without a heading
  * @param array $arrPOSTData
  * @param array $arrTypeParts
  * @return bool
  */
  function MakeHeadedImage($arrPOSTData,$arrTypeParts){
    $this->arrInputAttributes['src'] = get_site_url().'/'.$arrTypeParts[1];
      $this->objElement->AddChildNode($this->objElement->objHTML,'',$arrTypeParts[0],$this->arrInputAttributes);
    return TRUE;
  }

  /**
  * given an element type and POSt values, make a standard input element
  * @param array $arrTypeParts tag name and type
  * @param array $arrInputAttributes assembled array of attributes
  * @param array $arrPOSTData data from the DB or post form
  * @return string HTML
  */
  function MakeStandardInput($arrTypeParts,$arrPOSTData){
    if($arrTypeParts[1] != $arrTypeParts[0])
      $this->arrInputAttributes['type'] = $arrTypeParts[1];
    if(array_key_exists($this->objInputDefinition->strDefinitionName,$arrPOSTData)
       && $arrPOSTData[$this->objInputDefinition->strDefinitionName] != '')
      $this->arrInputAttributes['value'] = $arrPOSTData[$this->objInputDefinition->strDefinitionName];
    else if(trim($this->objInputDefinition->strDefaultValues) != "" && !is_null($this->objInputDefinition->strDefaultValues))
      $this->arrInputAttributes['value'] = $this->objInputDefinition->strDefaultValues;
    else if($this->arrInputAttributes['type'] == 'button' || $this->arrInputAttributes['type'] == 'submit'){
        $this->arrInputAttributes['value'] = $this->objInputDefinition->strElementTitle;
        if(trim($this->objInputDefinition->strElementClass) == '')
            $this->arrInputAttributes['class'] = 'btn btn-default pcmt_'.$arrTypeParts[1];
    }
    else
        $this->arrInputAttributes['value'] = '';
        $strTitle = $this->objInputDefinition->strElementTitle;
        if(stristr($strTitle,'notitle:'))
          $strTitle = str_replace('notitle:','',$strTitle);
        $this->arrInputAttributes['title'] = $strTitle;
        $this->arrInputAttributes['placeholder'] = $strTitle;
    $this->objElement->AddChildNode($this->objElement->objHTML,'',$arrTypeParts[0],$this->arrInputAttributes);
    //add a tooltip if we have one
    $this->AddToolTip();
    return $this->objElement->CloseDocument();
  }

  /**
  * Extract the title data and any formatting associated with it
  * @param sting $strType type of element to buyild a title for
  * @return bool
  */
  function LoadElementTitle($strType){
   $this->LoadEditElementOption();
   $arrGET =  filter_var_array($_GET,FILTER_SANITIZE_STRING);
   //external flag to prevent title creation
   if(array_key_exists('notitle',$this->arrTitleAttributes) || $strType == 'table')
    return TRUE;
   //check to see if we have an element container overide for the title
   if(stristr($this->objInputDefinition->strElementTitle,':')){
    $arrTitleParts = explode(':',$this->objInputDefinition->strElementTitle);
    $arrTitleParts[1] = (trim($arrTitleParts[1]) == '')? 'label': $arrTitleParts[1];
    if($arrTitleParts[0] == 'notitle')
        return TRUE;
    $this->objElement->AddChildNode($this->objElement->objHTML,$arrTitleParts[1],$arrTitleParts[0],$this->arrTitleAttributes);
   }
   else if($strType == 'button' ||
           $strType == 'submit' ||
           $strType == 'a' ||
           trim($this->objInputDefinition->strElementTitle) == '' ||
           (stristr($this->objInputDefinition->strElementType,':hidden') &&    
            (!array_key_exists('pcmw_option',$arrGET) || $arrGET['pcmw_option'] != 'manage-pcmw-forms')))//no need for a title on buttons
    return TRUE;
    //checkboxes may not need titles
   else if(substr($strType,0,10) == 'checkboxnt'){
        $arrTitleParts = explode(':',$this->objInputDefinition->strElementTitle);
        $arrTitleParts[1] = (trim($arrTitleParts[1]) == '')? 'label': $arrTitleParts[1];
        return $this->objElement->AddChildNode($this->objElement->objHTML,$arrTitleParts[0],'span',array());
   }
   else{
     //"label" tags are default
    $this->objElement->AddChildNode($this->objElement->objHTML,$this->objInputDefinition->strElementTitle,'label',$this->arrTitleAttributes);
   }
   //add a line break now
   if($strType !== 'checkboxnt'){
   	$this->objElement->AddChildNode($this->objElement->objHTML,'','br',array());
   }
   return TRUE;
  }

  /**
  * look through the object event handler variables to determine if there are
  * any values in our posted data to make replacements on
  * @param array $arrPOSTData prefilled or posted data to use in the replacement process
  * @return bool
  */
  function MakeEventHandlerReplacements( $arrPOSTData){
      foreach($this->objInputDefinition as $varKey=>$varMember){
        if(!is_string($varMember))
            continue 1;

        if((preg_match_all("/\[[^\]]*\]/", $varMember, $arrMatch)) > 0){
          foreach($arrMatch[0] as $ka=>$va){
          $strBracketsRemoved = str_replace(array("[","]"),'',$va);
            if(array_key_exists($strBracketsRemoved,$this->arrPOST))
              $varMember = str_replace($va,$this->arrPOST[$strBracketsRemoved],$varMember);
            if(array_key_exists($strBracketsRemoved,$this->arrMiscValues))
              $varMember = str_replace($va,$this->arrMiscValues[$strBracketsRemoved],$varMember);
          }
        }
        if((preg_match_all("/{(.*?)}/", $varMember, $arrMatch)) > 0){
          foreach($arrMatch[0] as $kb=>$vb){
          $strBracketsRemoved = str_replace(array("{","}"),'',$vb);
            if(property_exists($this, $strBracketsRemoved) && is_string($this->{$strBracketsRemoved}))
              $varMember = str_replace($vb,$this->{$strBracketsRemoved},$varMember);
          }
        }
        if((preg_match_all('#\((.*?)\)#', $varMember, $arrMatch)) > 0){
          foreach($arrMatch[0] as $kc=>$vc){
          $strBracketsRemoved = str_replace(array("(",")"),'',$vc);
            if(defined($strBracketsRemoved))
              $varMember = str_replace($vc,constant($strBracketsRemoved),$varMember);
          }
        }
        $this->objInputDefinition->{$varKey} = $varMember;
      }
      return TRUE;
  }

  /**
  * look through the object event handler variables to determine if there are
  * any values in our posted data to make replacements on
  * @param array $arrPOSTData prefilled or posted data to use in the replacement process
  * @return bool
  */
  function MakeArrayReplacements( $arrSubject,$arrPOSTData=array()){
      foreach($arrSubject as $varKey=>$varMember){
        if(!is_string($varMember))
            continue 1;

        if((preg_match_all("/\[[^\]]*\]/", $varMember, $arrMatch)) > 0){
          foreach($arrMatch[0] as $ka=>$va){
          $strBracketsRemoved = str_replace(array("[","]"),'',$va);
            if(array_key_exists($strBracketsRemoved,$this->arrPOST))
              $varMember = str_replace($va,$this->arrPOST[$strBracketsRemoved],$varMember);
            if(array_key_exists($strBracketsRemoved,$this->arrMiscValues))
              $varMember = str_replace($va,$this->arrMiscValues[$strBracketsRemoved],$varMember);
          }
        }
        if((preg_match_all("/{(.*?)}/", $varMember, $arrMatch)) > 0){
          foreach($arrMatch[0] as $kb=>$vb){
          $strBracketsRemoved = str_replace(array("{","}"),'',$vb);
            if(property_exists($this, $strBracketsRemoved) && is_string($this->{$strBracketsRemoved}))
              $varMember = str_replace($vb,$this->{$strBracketsRemoved},$varMember);
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
      return $arrSubject;
  }


  /**
  * given an element ID from the database, load the edit button
  * @return bool
  */
  function LoadEditElementOption(){
   $arrGET =  filter_var_array($_GET,FILTER_SANITIZE_STRING);
   if($this->objInputDefinition->intDefinitionId > 0 &&
      $this->objInputDefinition->intFormGroup > 5 &&
      PCMW_SHOWFORMINDICATORS &&
      (array_key_exists('pcmw_option',$arrGET) && $arrGET['pcmw_option'] == 'manage-pcmw-forms') &&
      (($this->objInputDefinition->intFormGroup >= 300 &&
       PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,PCMW_ADMINISTRATOR,FALSE,FALSE)) ||
      PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,PCMW_DEVUSERS,FALSE,FALSE))){
      $this->objElement->AddChildNode($this->objElement->objHTML,'Form Group Id ['.$this->objInputDefinition->intFormGroup.'-'.(int)PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,PCMW_DEVUSERS,FALSE,FALSE).']','div',array());
      $arrAttributes = array('class'=>'fa fa-edit fa-1x text-primary',
                             //'class'=>'floatleft displayinline',
                             'onclick'=>'ModifyFormElement('.$this->objInputDefinition->intDefinitionId.');',
                             'style'=>'height:25px;width:25px;cursor:pointer;',
                             'title'=>'Edit '.$this->objInputDefinition->strElementTitle,
                             'data-toggle'=>'tooltip',
                             'data-placement'=>'top');
      $this->objElement->AddChildNode($this->objElement->objHTML,'','i',$arrAttributes);
      //add the delete element option
      $arrAttributes = array('class'=>'fa fa-minus-circle fa-1x text-danger',
                             //'class'=>'floatleft displayinline clearright',
                             'onclick'=>'DeleteFormElement('.$this->objInputDefinition->intDefinitionId.');',
                             'style'=>'height:23px;width:23px;cursor:pointer;',
                             'title'=>'Delete '.$this->objInputDefinition->strElementTitle,
                             'data-toggle'=>'tooltip',
                             'data-placement'=>'top');
      $this->objElement->AddChildNode($this->objElement->objHTML,'','i',$arrAttributes);
   }
   return TRUE;
  }

  /**
  * given a list of attributes fill the common event handlers
  * @param array $arrInputAttributes pre existing attributes by reference
  * @return bool
  */
  function LoadCommonEventHandlers(){
    if(trim($this->objInputDefinition->strOnclick) != "")
        $this->arrInputAttributes['onclick'] = $this->objInputDefinition->strOnclick;
    if(trim($this->objInputDefinition->strOnChange) != "")
        $this->arrInputAttributes['onchange'] = $this->objInputDefinition->strOnChange;
    if(trim($this->objInputDefinition->strOnKeyUp) != "")
        $this->arrInputAttributes['onkeyup'] = $this->objInputDefinition->strOnKeyUp;
    return TRUE;
  }

  /**
  * build the non-iterative attributes for defining the element
  * @param array $arrInputAttributes pre existing attributes by reference
  * @return bool
  */
  function BuildNonIterativeAttributes(){
    //name is mandatory for all elements
    if(trim($this->objInputDefinition->strDefinitionName) == "")
        return FALSE;
    else{
        $this->arrInputAttributes['id'] = $this->objInputDefinition->strDefinitionName;
        $this->arrInputAttributes['name'] = $this->objInputDefinition->strDefinitionName;
        //remove no title tag for display purposes
        $strTitle = $this->objInputDefinition->strElementTitle;
        if(stristr($strTitle,'notitle:'))
          $strTitle = str_replace('notitle:','',$strTitle);
        $this->arrInputAttributes['title'] = $strTitle;
        $this->arrInputAttributes['placeholder'] = $strTitle;
    }
    if($this->boolViewOnly)
        $this->arrInputAttributes['disabled'] = 'disabled';
    if(trim($this->objInputDefinition->intElementMax) != "")
        $this->arrInputAttributes['maxlength'] = $this->objInputDefinition->intElementMax;
    else return FALSE;//no max length, no go
    $arrTypeParts = explode(':',$this->objInputDefinition->strElementType);
    if(trim($this->objInputDefinition->strElementClass) != "")
        @$this->arrInputAttributes['class'] .= $this->objInputDefinition->strElementClass.' pcmt_'.$arrTypeParts[0];
    else
        @$this->arrInputAttributes['class'] .= 'pcmt_'.$arrTypeParts[0];
    return TRUE;
  }


  /**
  * given a table object and an array of definitions load the headings
  * @param object $objElementClass element class instance
  * @param object $objElementTable element class table
  * @param array $arrDefinitions group of definitions to make the headings from
  * @return bool
  */
  function FillTableizedHeaderRow(PCMW_Element $objElementClass, &$objElementTable,$arrDefinitions){
    $objHeadingRow = $objElementClass->AddChildNode($objElementTable,'','tr',array());
    $intIndex = 0;
    $intCount = (sizeof($arrDefinitions) - 1);
    foreach($arrDefinitions as $arrDefinition){
      if($arrDefinition['elementtype'] != 'input:hidden'){
        $arrInputAttributes = array('class'=>'formcolumnhead  '.$arrDefinition['parentelementclass']);//grouplist
        if($intIndex < 1)
          $arrInputAttributes['class']  .= ' topleftroundededges5px';
        if($intIndex == $intCount)
          $arrInputAttributes['class']  .= ' toprightroundededges5px';
        $objElementClass->AddChildNode($objHeadingRow,$arrDefinition['elementtitle'],'th',$arrInputAttributes);
        $intIndex++;
      }
      else
        $intCount--;
    }
    return TRUE;
  }

  /**
  * given an directive get the iterative data and build a list table
  */
  function MakeTableAsInput($arrPOSTData,$arrTypeParts,$arrInputAttributes='deprecate'){
    $this->arrTitleAttributes['notitle'] = 1;
    $objElement = new PCMW_Element();
    $objParentTable = $objElement->LoadHTMLTemplate('<table style="width:100%;" cellpadding="0" cellspacing="0" class="table table-striped table-bordered table-hover"></table>');
    $this->arrInputAttributes = array('style'=>'border:1px solid #dcdcdc;padding:15px;');
    if(array_key_exists('admingroupid',$arrPOSTData) && trim($arrPOSTData['admingroupid']) != '')
        $intAdminGroupId = $arrPOSTData['admingroupid'];
    else
        $intAdminGroupId = $_SESSION['currentuser']['admingroupid'];
    $intFormGroupId = $this->GetFormDataByAlias($this->objInputDefinition->strDefinitionName,$intAdminGroupId,TRUE);
    $arrFormGroup = $this->GetFormDefinitions($intFormGroupId);
    $arrDefinitionObjects = array();


    //load the header
    $this->FillTableizedHeaderRow($objElement, $objParentTable,$arrFormGroup);
    $arrIndexCollection = $this->GatherIndexValueCollection();
    foreach($arrIndexCollection as $ka=>$va){
      PCMW_Utility::Get()->CleanTableData($va);
      $objDataRow[$ka] = $objElement->AddChildNode($objParentTable,'','tr',array());
      foreach($arrFormGroup as $arrFormData){
        $objDefinition = new PCMW_FormDefinition();
        $objDefinition->LoadObjectWithArray($arrFormData);
        if(stristr($objDefinition->strElementType,':hidden')){
            continue 1;
        }
        $objInputElement = new PCMW_InputElement();
        $objInputElement->arrTitleAttributes['notitle'] = 1;
        $strCellElement = $objInputElement->ProcessFormDefinition($objDefinition, $va);
         $objElement->AddChildNode($objDataRow[$ka],$strCellElement,'td',$this->arrInputAttributes);

      }

    }
    //all done, return the closed form
    $strDataList = $objElement->CloseDocument();
    unset($this->arrTitleAttributes['notitle']);
    return $strDataList;
  }
  #ENDREGION


  #REGION DATABASECALLS
  //All functions which will interact with PCMW_Database.php should go in here

  /**
  * given a form ID, get the form data for update or other purposes
  * @param string $strGroupAlias name of the form grojup to aquire
  * @param int $intAdminGroup admin group the user belongs to
  * @param bool $boolIdOnly get he form ID only
  * @return array
  */
  function GetFormDataByAlias($strGroupAlias,$intAdminGroupId,$boolIdOnly=FALSE){
    $arrFormData = array();
    if($arrFormData = PCMW_Database::Get()->GetFormDataByAlias($strGroupAlias,$intAdminGroupId)){
      if($boolIdOnly)
        return $arrFormData[0]['formid'];
      return $arrFormData[0];
    }
    return $arrFormData;
  }


  /**
  * given a form ID, get the definitions and associated data
  * @param int $intFormId unique for ID
  * @param bool $boolCountOnly return the number of definitions only
  * @return array || int || FALSE
  */
  function GetFormDefinitions($intFormId,$boolCountOnly=FALSE){
    if($arrFormDefinitions = PCMW_Database::Get()->GetFormDefinitions($intFormId)){    
       if($boolCountOnly)
        return sizeof($arrFormDefinitions);
       return $arrFormDefinitions;
    }
    return FALSE;
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