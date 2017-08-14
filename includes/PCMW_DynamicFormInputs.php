<?php
/*******************************************************************************
* class :PCMW_DynamicFormInputs
* @brief Create forms and single inputs based on the definitions
* array being fed to it in it's parent class
* @requires PCMW_Element.php
*****************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once(dirname(__FILE__) . '/PCMW_Element.php');
class PCMW_DynamicFormInputs{
    var $strBaseCSS = '';
    //form variables
    var $strFormName = '';
    var $boolIsForm = TRUE;
    var $strFormAction = '';
    var $strFormClass = 'container col-md-12 ';
    var $strFormMethod = 'post';
    var $strSubmitButtonClass = 'formbutton pull-left';
    var $arrMiscValues = array();
    var $boolBootstrapForm = FALSE;
    var $boolBootstrapTable = FALSE;
    //do we use a container with this?
    var $boolUseFieldSet = FALSE;
    var $boolAjaxSubmitForm = FALSE;
    var $boolAllowEdits = FALSE;
    var $boolAllowNewElements = FALSE;
    var $boolViewOnly = FALSE;
    //submit button love
    var $boolMakeSubmitButton = FALSE;
    var $strCustomOnclick = '';
    var $arrElementAttributes = array();
    var $arrRandomAttributes = array();
    var $arrTitleAttributes;
    var $strMultipleFormHandlers = '';
    //list of inputs to exclude from the form
    var $arrFormExclusions = array();
    //debug
    var $boolDebugOn = FALSE;
    public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_DynamicFormInputs();
		return( $inst );
   }

   public function __construct(){
    // construct here
    if(PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERREADWRITE,PCMW_DEVUSERS,FALSE,FALSE))
        $this->boolAllowEdits = TRUE;
   }

   //initiate the form or input collection controls
   function InitiateFormControls($arrPOST){
      $this->objBaseObject = new PCMW_Element();
      //lets make special formatting
      $objPrimaryElement = $this->objBaseObject->LoadHTMLTemplate('<div class="'.$this->strBaseCSS.'"></div>');
      if($this->boolUseFieldSet)
         $objPrimaryElement = $this->MakeFieldSetAndLegend($objPrimaryElement);
      if((int)$this->intFormGroupId > 0 && $this->boolAllowNewElements){
       //we have an ID, let's make sure all elements come here
        $this->CreateSubForm($objPrimaryElement,$arrPOST);
      }
      if(!$this->boolIsForm)
         $objFormElement = $objPrimaryElement;
      if($this->boolIsForm)
        $objFormElement = $this->MakeFormContainer($objPrimaryElement);
      if($this->boolBootstrapForm){
        PCMW_FormManager::Get()->MakeBootStrapForm($objFormElement,$arrPOST);
      }
      if($this->boolBootstrapTable){
        PCMW_FormManager::Get()->MakeBootStrapTable($objFormElement,$arrPOST);
      }
      if($this->strMultipleFormHandlers!==''){
		//Convert String to array before passing to InitiateMultipleForms function
		$arrFormHandlers = explode(',',$this->strMultipleFormHandlers);
		$this->InitiateMultipleForms($objFormElement,$arrFormHandlers,$arrPOST);
      }
      else{
        if($this->CombineDataAndContainer($objFormElement,$arrPOST)){
          if($this->boolIsForm && $this->boolMakeSubmitButton && !$this->boolViewOnly)
            @$this->MakeFormSubmitButton($objFormElement,$boolValidate);
        }
      }
      return $this->objBaseObject->CloseDocument();  //
   }

   /**
   * given the definitions create an ala cart form node
   * @param $arrDefinitions
   * @param $arrPOST
   * @return string ( HTML )
   */
   function MakeAlaCartForm($arrDefinitions,$arrPOST){
    $this->objBaseObject = new PCMW_Element();
    //lets make special formatting
    $objPrimaryElement = $this->objBaseObject->LoadHTMLTemplate('<div class="'.$this->strBaseCSS.'"></div>');
    $objParentElement = $this->MakeFormContainer($objPrimaryElement);
    //add our elements here
    $strHTMLInput = '';
    foreach($arrDefinitions as $objInputDefinition){
      //make our definition object
      //$objInputDefinition = new PCMW_FormDefinition();
      //load our values
      //$objInputDefinition->LoadObjectWithArray($arrDefinition);
      //make the input element object
      $objInputElement = new PCMW_InputElement();
      $objInputElement->arrMiscValues = $this->arrMiscValues;
      //in case we need to customize the POST array
      $objInputElement->arrPOST = $arrPOST;
      //there are several ways to make checkboxes, and the storage needs to be
      // recognized as checked or not checked
      if(stristr($objInputDefinition->strElementType,'input:checkbox') &&
         @!is_null($arrPOST[$objInputDefinition->strDefinitionName]) &&
         trim($arrPOST[$objInputDefinition->strDefinitionName]) != '' &&
         (int)$arrPOST[$objInputDefinition->strDefinitionName] > 0)
        $objInputElement->arrPOST['checked'] = 'checked';
      $strHTMLInput .= $objInputElement->ProcessFormDefinition($objInputDefinition, $objInputElement->arrPOST);
    }
    //add any hidden elements
    if(array_key_exists('hiddenelements',$this->arrMiscValues) && sizeof($this->arrMiscValues['hiddenelements']) > 0){
     foreach($this->arrMiscValues['hiddenelements'] as $strName=>$strValue)
        $this->AddHiddenInput($objParentElement,$strName,$strValue);
    }
    //add it to our parent div now
    $this->objBaseObject->AddChildNode($objParentElement,$strHTMLInput, 'div');
    $this->AddFormNonce($objParentElement);
    //remember to turn this switch on $this->boolAjaxSubmitForm
    if($this->boolMakeSubmitButton)
        $this->MakeFormSubmitButton($objParentElement);
    return $this->objBaseObject->CloseDocument();  //
   }


  //add a line break
   function AddLineBreak($objParentElement){
     $this->objBaseObject->AddChildNode($objParentElement,'<!--  -->', 'div',array('class'=>'clearboth'));
     return $objParentElement;
   }

  /**
  * add a hidden element to a form
  * @param $objParentElement
  * @param $strInputName
  * @param $strInputValue
  * @return bool
  */
   function AddHiddenInput(&$objParentElement,$strInputName,$strInputValue){
     $this->objBaseObject->AddChildNode($objParentElement,'', 'input',array('type'=>'hidden','name'=>$strInputName,'value'=>$strInputValue));
     return TRUE;
   }

   //do we add a fieldset
   function MakeFieldSetAndLegend($objParentElement){
     $objFieldSet = $this->objBaseObject->AddChildNode($objParentElement,'', 'div',array('class'=>$this->strFormClass));
     $objPanelDefault = $this->objBaseObject->AddChildNode($objParentElement,'', 'div',array('class'=>'panel'));
     //if($this->strFormName != '')
     if($this->strNewElement != '')
	 $this->objBaseObject->AddChildNode($objParentElement, $this->strNewElement, 'div',array('class'=>'success'));
     $objPanelHeader = $this->objBaseObject->AddChildNode($objParentElement,$this->strFormName, 'div',array('class'=>'panel-heading'));
     $objPanelBody = $this->objBaseObject->AddChildNode($objParentElement,'', 'div',array('class'=>'panel-body'));
     return $objPanelBody;
   }

   //make a submit button
   function MakeFormSubmitButton($objParentElement){ //
    @$this->arrRandomAttributes['content'] = ($this->arrElementAttributes['title'] != "")? $this->arrElementAttributes['title']:'Save';
    $this->arrRandomAttributes['element'] = 'input';
    $this->arrElementAttributes['class'] = $this->strSubmitButtonClass;
    $this->arrElementAttributes['type'] = 'button';
    $this->arrElementAttributes['value'] = $this->arrRandomAttributes['content'];
    $this->arrElementAttributes['onclick'] = 'this.form.submit();';
    if($this->boolAjaxSubmitForm)
        $this->arrElementAttributes['onclick'] = 'void(SubmitSelectedForm(this.form)); return false;';
      //PCMW_Logger::Debug('$this->strCustomOnclick ['.$this->strCustomOnclick.'] LINE '.__LINE__,1);
    if(trim($this->strCustomOnclick) != '')
    	$this->arrElementAttributes['onclick'] = $this->strCustomOnclick;
     return $this->AddRandomElement($objParentElement);
   }


  //add a submit button to a form
   function AddRandomElement($objParentElement){
     $this->objBaseObject->AddChildNode($objParentElement,$this->arrRandomAttributes['content'], $this->arrRandomAttributes['element'],$this->arrElementAttributes);
     return $objParentElement;
   }



   function MakeFormContainer($objParentElement){
     $arrFormParameters = array('enctype'=>'multipart/form-data',
                                'action'=>$this->strFormAction,
                                'method'=>$this->strFormMethod,
                                'name'=>$this->strFormName,
                                'id'=>$this->strFormName,
                                'class'=>$this->strFormClass.'',
                                'role'=>'form');
     return $this->objBaseObject->AddChildNode($objParentElement,'', 'form',$arrFormParameters);
   }

   /**
   * given a form ID, check and see if we have any subforms that go with it
   * @param int $intFormId form id for sub addition
   * @return bool
   */
   function CreateSubForm($objPrimaryElement,$arrPOST,$intFormId=4){
    if($arrDefinitionResults = PCMW_Database::Get()->GetFormDefinitions($intFormId,0,0)){
      foreach($arrDefinitionResults as $arrDefinition){
        $objInputDefinition = PCMW_FormDefinition::Get()->LoadObjectWithArray($arrDefinition);
        $objInputElement = new PCMW_InputElement();
        $strHTMLInput = $objInputElement->ProcessFormDefinition($objInputDefinition, $arrPOST);
        $this->objBaseObject->AddChildNode($objPrimaryElement,$strHTMLInput, 'div',array());
      }
    }
    $this->AddLineBreak($objPrimaryElement);
    return TRUE;
   }


   //combine the data array and the parent object
   function CombineDataAndContainer($objParentElement,$arrPOST){
    $strHTMLInput = '';
      //get our definitions to make the form
      $arrDefinitionResults = PCMW_FormDefinitionsCore::Get()->GetDefinitionsById($this->intFormGroupId);
      //go through each definition and make the input and container
      foreach($arrDefinitionResults as $arrKey=>$objInputDefinition){
        //we can omit an element if an external condition mandates it
        if(array_key_exists($objInputDefinition->strDefinitionName,$this->arrFormExclusions))
            continue 1;
        //we don't want to update the internal forms //DEPRECATED, needs to be verified//
        if($objInputDefinition->intFormGroup > 5)
            $arrPOST['formid'] = $objInputDefinition->intFormGroup;
        //make the input element object
        $objInputElement = new PCMW_InputElement();
        //load our external conditions, defaulted by hierarchy
        $objInputElement->boolViewOnly = $this->boolViewOnly;
        $objInputElement->boolAllowEdits = $this->boolAllowEdits;
        $objInputElement->arrMiscValues = $this->arrMiscValues;
        //in case we need to customize the POST array
        $objInputElement->arrPOST = $arrPOST;
        //there are several ways to make checkboxes, and the storage needs to be
        // recognized as checked or not checked
        if(stristr($objInputDefinition->strElementType,'input:checkbox') &&
           @!is_null($arrPOST[$objInputDefinition->strDefinitionName]) &&
           trim($arrPOST[$objInputDefinition->strDefinitionName]) != '' &&
           (int)$arrPOST[$objInputDefinition->strDefinitionName] > 0)
        $objInputElement->arrPOST['checked'] = 'checked';
        //if we're building a form, we need to increment the count//DEPRECATED, needs to be verified//
        if(array_key_exists('formgroup',$arrPOST) && $arrPOST['formgroup'] > 0){
          $objInputElement->intTotalFormElements = (PCMW_FormManager::Get()->GetFormDefinitions($arrPOST['formgroup'],TRUE) + 1);
        }
        //are we loading an external form name?
        if(array_key_exists('formname',$this->arrMiscValues) && trim($this->arrMiscValues['formname']) != '')
            $this->strFormName = $this->arrMiscValues['formname'];
        //load our failed elements when validation fails
        if(is_array($objInputElement->arrMiscValues) && @in_array($objInputDefinition->strDefinitionName,@$objInputElement->arrMiscValues['failedelements'])){
          $objInputDefinition->strElementClass .= ' redbackground';
        }
        //check to see if it is disabled externally
        if(is_array(@$objInputElement->arrMiscValues['disabledelements']) &&
           @in_array($objInputDefinition->strDefinitionName,$objInputElement->arrMiscValues['disabledelements']))
            $objInputElement->boolViewOnly = TRUE;
        //if we have title class overrides, this would be where they go
        if(sizeof($this->arrTitleAttributes) > 0)
            $objInputElement->arrTitleAttributes = $this->arrTitleAttributes;
        //make our element
        $strHTMLInput .= $objInputElement->ProcessFormDefinition($objInputDefinition, $objInputElement->arrPOST);
        if($this->intFormGroupId == 0){
          PCMW_Logger::Debug('$arrPOST ['.PCMW_Utility::Get()->MakeStringFromArray($objInputElement->arrPOST,TRUE).'] LINE '.__LINE__,1);
          PCMW_Logger::Debug('$arrDefinitionResults ['.PCMW_Utility::Get()->MakeStringFromArray($arrDefinitionResults,TRUE).'] LINE '.__LINE__,1);
        }
      }
      $this->AddFormNonce($objParentElement);
      //throw it all into a div
      $this->objBaseObject->AddChildNode($objParentElement,$strHTMLInput, 'div');
      return TRUE;
   }

   /**
   * add nonce to our form
   * @param $objParentElement
   * @return bool
   */
   function AddFormNonce(&$objParentElement){
     //all done making the form, make our nonce
      $intUniqueID = md5(time().rand(1000,100000));
      $arrNonce  = array('type'=>'hidden',
                         'name'=>'wp_nonce',
                         'value'=>wp_create_nonce($intUniqueID),
                         'id'=>'wp_nonce'
                        );
      $this->objBaseObject->AddChildNode($objParentElement,wp_create_nonce($intUniqueID), 'input',$arrNonce);
      //make our nonce comparison
      $arrNonce  = array('type'=>'hidden',
                         'name'=>'submissionid',
                         'value'=>$intUniqueID,
                         'id'=>'submissionid'
                        );
      $this->objBaseObject->AddChildNode($objParentElement,$intUniqueID, 'input',$arrNonce);
      return TRUE;
   }

   function InitiateMultipleForms($objParentElement,$arrFormHandlers,$arrPOST){
      foreach($arrFormHandlers as $Form){
   		if($arrDefinitionResults = PCMW_Database::Get()->GetFormDefinitionsByAlias($Form,$_SESSION['CURRENTUSER']['pcconfig']['admingroupid'])){
            $strHTMLInput = '';
			foreach($arrDefinitionResults as $arrDefinition){
				$objInputDefinition = PCMW_FormDefinition::Get()->LoadObjectWithArray($arrDefinition);
				$objInputElement = new PCMW_InputElement();
				$strHTMLInput .= $objInputElement->ProcessFormDefinition($objInputDefinition, $arrPOST);
			}
			$this->objBaseObject->AddChildNode($objParentElement,$strHTMLInput, 'div',array());

		}
		else{
			echo 'User either needs admin access or formaliases are invalid';
		}
   	  }
	  $this->MakeFormSubmitButton($objParentElement,$boolValidate);
	  return $this->objBaseObject->CloseDocument();
   }


}//end class
?>