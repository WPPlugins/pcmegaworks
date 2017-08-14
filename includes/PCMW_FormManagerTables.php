<?php
/**************************************************************************
* @CLASS PCMW_FormManagerTables
* @brief USE THIS TO CREATE NEW CLASSES FOR THE INCLUDES DIRECTORY.
* @REQUIRES:
*  -PCMW_Database.php
*  -PCMW_StringComparison.php
**************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
class PCMW_FormManagerTables{
   //debugging
   var $boolDebugOn = TRUE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_FormManagerTables();
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
    $arrDebugMethods['PCMW_FormManagerTables::MakeTabbedForms'] = 1;
    $arrDebugMethods['FormManager::MakeTabbedForms'] = 1;
    return $arrDebugMethods;
  }

   /**
   * given a form category, make a tabbed table of individual forms
   * @param $strFormCategory
   * @return string (HMTL tabbed form)
   *
   *     -['tabledescription'] = ['tabledescription']
   *     -['tableheader']
   *        -['headerkey'] = ['headername']
   *     -['tabledata']
   *        -['headerkey'] = ['data']
  */
   function MakeTabbedForms($strFormCategory,$arrData,$strTableName='',$arrPOST){
    //first we get the forms
    $arrForms = $this->DeduceFormAliases($strFormCategory,FALSE,$arrData['formalias'],$arrData);
    $strResultingForm = '';
    //make our base array
    $arrTabbedForms = array('tabledescription'=>$strTableName);
    $arrTabbedForms['tableheader'] = array();//PCMW_Abstraction::Get()->GetFormTitleReplacements($arrForms,$strFormCategory.'titles',$arrData);
    //are these all individual forms, or a collective?
    //let's now load each form by alias
    PCMW_DynamicFormInputs::Get()->boolAjaxSubmitForm = TRUE;
    PCMW_DynamicFormInputs::Get()->strSubmitButtonClass = 'clearboth floatleft formbutton ';
    foreach($arrForms as $strHeaderGroup=>$arrElementData){
      if(!array_key_exists($arrElementData['order'],$arrTabbedForms['tableheader']) || !is_array($arrTabbedForms['tableheader'][$arrElementData['order']]))
        $arrTabbedForms['tableheader'][$arrElementData['order']] = array();
      //add our header and order now
      $arrTabbedForms['tableheader'][$arrElementData['order']] = array($strHeaderGroup,$arrElementData['name']);
      if(array_key_exists('action',$arrElementData) && trim($arrElementData['action']) != '')
       PCMW_DynamicFormInputs::Get()->arrMiscValues['hiddenelements']['action'] = $arrElementData['action'];
      if(array_key_exists('dir',$arrElementData) && trim($arrElementData['dir']) != '')
       PCMW_DynamicFormInputs::Get()->arrMiscValues['hiddenelements']['dir'] = $arrElementData['dir'];
      if(@$arrElementData['viewonly'] == 1)
        PCMW_DynamicFormInputs::Get()->boolMakeSubmitButton = FALSE;
      else
        PCMW_DynamicFormInputs::Get()->boolMakeSubmitButton = TRUE;
      $arrTabbedForms['tabledata'][$strHeaderGroup] = PCMW_DynamicFormInputs::Get()->MakeAlaCartForm($arrElementData['data'],$arrPOST);
      //reset our hidden form elements
      PCMW_DynamicFormInputs::Get()->arrMiscValues = array();
    }
    $strResultingForm .= $this->MakeTabbedFormsTable($arrTabbedForms);
    return $strResultingForm;
   }


  /**
  * given a group of data, load the headnig and rows from the data given
  * @param $arrTableData
  *     -['tabledescription'] = ['tabledescription']
  *     -['tableheader']
  *         -['headerkey'] = ['headername']
  *     -['tabledata'][unique key]
  *         -['headerkey'] = ['columnvalue']
  *         -['linkvalue'] = ['linkvalue'] || ['onclickvalue'] = ['onclickvalue']
  * @return string
  */
  function MakeBootStrapTable($arrTableData,$strTableOrder='asc'){
    $objElement = new PCMW_Element();
    $this->strBootstrapTableId = 'table_'.rand(100,10000);
    $objPrimaryTable = $objElement->LoadHTMLTemplate('<div class="panel panel-default"></div>');
    if(trim($arrTableData['tabledescription']) != '')
     $objElement->AddChildNode($objPrimaryTable,trim($arrTableData['tabledescription']),'div',array('class'=>'panel-heading'));
    //make the wrapper
    $objTableBodyWrapper = $objElement->AddChildNode($objPrimaryTable,'','div',array('class'=>'dataTable_wrapper'));
    //make the primary table now
    $objTableBodyTable = $objElement->AddChildNode($objTableBodyWrapper,'','table',array('class'=>'table table-striped table-bordered table-hover ','role'=>'grid','id'=>$this->strBootstrapTableId));
    //make the primary table now
    $objTableBodyTableHead = $objElement->AddChildNode($objTableBodyTable,'','thead',array());
    //make the primary table now
    $objTableBodyTableHeadRow = $objElement->AddChildNode($objTableBodyTableHead,'','tr',array());
    //make the th values
    foreach($arrTableData['tableheader'] as $strKeys=>$varHeaderValue){
      if(is_array($varHeaderValue))
       $objElement->AddChildNode($objTableBodyTableHeadRow,$varHeaderValue[1],'th',array('class'=>'sorting','tabindex'=>0,'aria-controls'=>'dataTables-example','rowspan'=>1,'colspan'=>1,'aria-label'=>$varHeaderValue[1].': activate to sort column descending'));
      else
       $objElement->AddChildNode($objTableBodyTableHeadRow,$varHeaderValue,'th',array('class'=>'sorting','tabindex'=>0,'aria-controls'=>'dataTables-example','rowspan'=>1,'colspan'=>1,'aria-label'=>$varHeaderValue.': activate to sort column descending'));
    }
    $objTableBodyTableBody = $objElement->AddChildNode($objTableBodyTable,'','tbody',array());
    //make the row data now
    $strRowClass = '';
    foreach($arrTableData['tabledata'] as $strDataKeys=>$arrDataValues){
      $strRowClass = ($strRowClass == 'odd')? 'even': 'odd';
      if(@$arrDataValues['rowclass'] != '')
        $strRowClass = $arrDataValues['rowclass'];
      $objTableBodyTableBodyRow = $objElement->AddChildNode($objTableBodyTableBody,'','tr',array('class'=>$strRowClass.' gradeA','role'=>'row','id'=>@$arrDataValues['rowid']));
      foreach($arrTableData['tableheader'] as $strKeys=>$strHeaderValue){
        $strToolTip = '';
        $strCellData = @$arrDataValues[$strKeys]['value'];
        $arrCellAttributes = array('class'=>'sorting_1');
        if(@is_array($arrDataValues[$strKeys]) && @array_key_exists('linkbadge',$arrDataValues[$strKeys]) && @$arrDataValues[$strKeys]['linkbadge'] != '')
            $strCellData = '<i class="'.$arrDataValues[$strKeys]['linkbadge'].'"></i>&nbsp;&nbsp;'.$strCellData;
        if(@is_array($arrDataValues[$strKeys]) && @array_key_exists('tooltip',$arrDataValues[$strKeys]) && @$arrDataValues[$strKeys]['tooltip'] != ''){
				//ALWAYS required for tooltip/popover in tables
            $arrCellAttributes['data-container'] = "body";
			// tooltip toggle
			$arrCellAttributes['html'] = "true";
			// use html in popover
			$arrCellAttributes['data-html'] = "true";
			// popover/tooltip timer show options
			// popover  toggle
			$arrCellAttributes['data-toggle'] = "popover";
			// tooltip
           $arrCellAttributes['title'] = $arrDataValues[$strKeys]['tooltip'];


        }

        if(@$arrDataValues[$strKeys]['linkvalue'] != ''){
           $strCellData = '<a href="'.$arrDataValues[$strKeys]['linkvalue'].'" '.$strToolTip.' >'.$strCellData.'</a>';
        }
        else if(@is_array($arrDataValues[$strKeys]) && array_key_exists('checkbox',$arrDataValues[$strKeys])){
           $strChecked = ($strCellData === TRUE)? ' CHECKED ': '';
           $strCellData = '<input type="checkbox" '.$strChecked.' '.$strToolTip;
           if($arrDataValues[$strKeys]['ajaxlinkcall'] != '')
            $strCellData .= ' onclick="'.$arrDataValues[$strKeys]['ajaxlinkcall'].'" ';
           $strCellData .=' />';
        }
        else if(@$arrDataValues[$strKeys]['href'] != ''){
          $strClass = '';
          if(trim($arrDataValues[$strKeys]['linkclass']) != '')
            $strClass = $arrDataValues[$strKeys]['linkclass'];
          $strCellData = '<a class="'.$strClass.'" href="'.$arrDataValues[$strKeys]['href'].'" '.$strToolTip.' target="_blank" >'.$strCellData;

          $strCellData .= '</a>';
        }
        else if(@$arrDataValues[$strKeys]['onclickvalue'] != '')
            $strCellData = '<button onclick="'.$arrDataValues[$strKeys]['onclickvalue'].'"  type="button" class="'.$arrDataValues[$strKeys]['linkclass'].'" '.$strToolTip.' >'.$strCellData.'</button>';
        else if(@$arrDataValues[$strKeys]['formlinkvalue'] != '' && @$arrDataValues[$strKeys]['formlinkclass'] != ''){
            $arrGET = $_GET;
            $arrGET['formid'] = $arrDataValues[$strKeys]['formlinkvalue'];
            $strBaseName = strtok($_SERVER["REQUEST_URI"],'?');
            $strBaseName .= '?'.http_build_query($arrGET);
          $strCellData = '<a href="'.$strBaseName.'" class="'.$arrDataValues[$strKeys]['formlinkclass'].'" '.$strToolTip.' >'.$strCellData.'</a>';
        }
        else if(@$arrDataValues[$strKeys]['ajaxlinkcall'] != '' && @$arrDataValues[$strKeys]['formlinkclass'] != '')
          $strCellData = '<a class="'.$arrDataValues[$strKeys]['formlinkclass'].'" onclick="'.$arrDataValues[$strKeys]['ajaxlinkcall'].'" '.$strToolTip.' > '.$strCellData.' </a>';
        else{
          if(@$arrDataValues[$strKeys]['linkclass'] != "")
            $strCellData = '<b class="'.$arrDataValues[$strKeys]['linkclass'].'"  '.$strToolTip.' >'.$strCellData.'</b>';
          else
             $strCellData = '<b  '.$strToolTip.'>'.$strCellData.'</b>';
        }
         $objElement->AddChildNode($objTableBodyTableBodyRow,$strCellData,'td',$arrCellAttributes);
      }
    }
    $strSortScript = "\r\n".'jQuery(document).ready(function() {'."\r\n";
    $strSortScript .= ' if(jQuery(\'#'.$this->strBootstrapTableId.'\')){'."\r\n";
    $strSortScript .= '     jQuery(\'#'.$this->strBootstrapTableId.'\').DataTable({'."\r\n";
    $strSortScript .= '            responsive: true,'."\r\n";
    //$strSortScript .= '            order: [[ 0, "desc" ]]'."\r\n";
    $strSortScript .= '            order: [[ 0, "'.$strTableOrder.'" ]],'."\r\n";
    //$strSortScript .= '            search: '.$strTableSearch."\r\n";
    $strSortScript .= '     });'."\r\n";
    $strSortScript .= ' }'."\r\n";
    $strSortScript .= '});'."\r\n";
    $objElement->AddChildNode($objPrimaryTable,$strSortScript,'script',array());
    return $objElement->CloseDocument();
  }

  /**
    </script>
  * given an order's data fill a tabbed table with the data
  * @param $arrTableData
  *     -['tabledescription'] = ['tabledescription']
  *     -['tabs']
  *        -['tabdata']
  *          -['tableheader']
  *             -['headerkey'] = ['headername']
  *          -['tabledata']
  *             -['headerkey'] = ['columnvalue']
  *             -['linkvalue'] = ['linkvalue'] || ['onclickvalue'] = ['onclickvalue']
  *        -['tabtitle'] = ['title']
  * @return HTML
  */
  function MakeBootStrapTabbedTable($arrTableData){
    $objElement = new PCMW_Element();
    $objPrimaryTable = $objElement->LoadHTMLTemplate('<div class="col-md-12"></div>');
    if(trim($arrTableData['tabledescription']) != '')
     $objElement->AddChildNode($objPrimaryTable,trim($arrTableData['tabledescription']),'h1',array('class'=>'page-header'));
    //make lead now
    $objElement->AddChildNode($objPrimaryTable,'','p',array('class'=>'lead'));
    //make the wrapper
    $objTableBodyRow = $objElement->AddChildNode($objPrimaryTable,'','div',array('class'=>'row'));
    //make the primary table now
    $objTableBodyTable = $objElement->AddChildNode($objTableBodyRow,'','div',array('class'=>'col-md-12'));
    //make the primary table now
    $objTableBodyTableHead = $objElement->AddChildNode($objTableBodyTable,'','thead',array());
    //make the primary table now
    $objTableBodyTableHeadRow = $objElement->AddChildNode($objTableBodyTableHead,'','tr',array());
    //make the th values
    foreach($arrTableData['tableheader'] as $strKeys=>$strHeaderValue){
       $objElement->AddChildNode($objTableBodyTableHeadRow,$strHeaderValue,'th',array());
    }
    $objTableBodyTableBody = $objElement->AddChildNode($objTableBodyTable,'','tbody',array());
    //make the row data now
    $strRowClass = '';
    foreach($arrTableData['tabledata'] as $strDataKeys=>$arrDataValues){
      $strRowClass = ($strRowClass == 'odd')? 'even': 'odd';
      if($arrDataValues['rowclass'] != '')
      $strRowClass = $arrDataValues['rowclass'];
      $objTableBodyTableBodyRow = $objElement->AddChildNode($objTableBodyTableBody,'','tr',array('class'=>$strRowClass.' gradeX'));
      foreach($arrTableData['tableheader'] as $strKeys=>$strHeaderValue){
        $strCellData = $arrDataValues[$strKeys]['value'];
        if($arrDataValues[$strKeys]['linkvalue'] != ''){
           $strCellData = '<a href="'.$arrDataValues[$strKeys]['linkvalue'].'" >'.$strCellData.'</a>';
        }
        else if($arrDataValues[$strKeys]['onclickvalue'] != '')
            $strCellData = '<input type="button" value="'.$strCellData.'" onclick="'.$arrDataValues[$strKeys]['onclickvalue'].'" class="'.$arrDataValues[$strKeys]['linkclass'].'" />';
        else{
          if($arrDataValues[$strKeys]['linkclass'] != "")
            $strCellData = '<b class="'.$arrDataValues[$strKeys]['linkclass'].'">'.$strCellData.'</b>';
          else
             $strCellData = '<b >'.$strCellData.'</b>';
        }
         $objElement->AddChildNode($objTableBodyTableBodyRow,$strCellData,'td',array());
      }
    }
    return $objElement->CloseDocument();
  }

  /**
  * use bootstrap to make a tabbed table
  * @param $arrTableData
  *     -['tabledescription'] = ['tabledescription']
  *     -['tableheader']
  *        -['headerkey'] = ['headername']
  *     -['tabledata']
  *        -['headerkey'] = ['data']
  * @return HTML
  */
  function MakeTabbedFormsTable($arrTableData){
    $objElement = new PCMW_Element();
    $objPrimaryTable = $objElement->LoadHTMLTemplate('<div class="row"></div>');
    $objColMd12 = $objElement->AddChildNode($objPrimaryTable,'','div',array('class'=>'col-md-12'));
    $objPanelDefault = $objElement->AddChildNode($objColMd12,'','div',array('class'=>'panel panel-default paper paper-curve-horiz'));
    if(trim($arrTableData['tabledescription']) != '')
     $objElement->AddChildNode($objPanelDefault,trim($arrTableData['tabledescription']),'div',array('class'=>'panel-heading'));
    //make the panel body
    $objPanelBody = $objElement->AddChildNode($objPanelDefault,'','div',array('class'=>'panel-body'));
    //make the nav tabs
    $objNavTabs = $objElement->AddChildNode($objPanelBody,'','ul',array('class'=>'nav nav-tabs'));
    //make the primary table
    $objTabContent = $objElement->AddChildNode($objPanelBody,'','div',array('class'=>'tab-content'));
    //make the tabs and content now
    $intFirstTab = FALSE;
    foreach($arrTableData['tableheader'] as $varOrder=>$arrTab){
      $strActive = '';
      $strAria = 'false';
      $strTabClass = 'tab-pane fade';
      if(!$intFirstTab){//first one only
        $strActive = 'active';
        $strAria = 'true';
        $strTabClass = 'tab-pane fade active in';
        $intFirstTab = TRUE;
      }
      //tabs
      $objNavList = $objElement->AddChildNode($objNavTabs,'','li',array('class'=>$strActive));
      $objElement->AddChildNode($objNavList,ucfirst($arrTab[1]),'a',array('href'=>'#'.$arrTab[0],'data-toggle'=>'tab','aria-expanded'=>$strAria));
      //content
      $objElement->AddChildNode($objTabContent,$arrTableData['tabledata'][$arrTab[0]],'div',array('class'=>$strTabClass,'id'=>$arrTab[0]));
    }
    return $objElement->CloseDocument();
  }

  /**
    </script>
  * given an order's data fill a tabbed table with the data
  * @param $arrTableData
  *     -['tabledescription'] = ['tabledescription']
  *		-['tabs'] = ['tabs']
  *     -['tabledatacontainer'] = ['tabledatacontainer']
  *     	-['tableheader']
  *         	-['headerkey'] = ['headername']
  *     	-['tabledata']
  *         	-['headerkey'] = ['columnvalue']
  *         	-['linkvalue'] = ['linkvalue'] || ['onclickvalue'] = ['onclickvalue']
  * @return HTML
  */
  function MakeTabbedTable($arrTableData){
    $objElement = new PCMW_Element();
    $objPrimaryTable = $objElement->LoadHTMLTemplate('<div class="col-md-12"></div>');
    if(trim($arrTableData['tabledescription']) != '')
     $objElement->AddChildNode($objPrimaryTable,trim($arrTableData['tabledescription']),'h1',array('class'=>'page-header'));
    //make lead now
    $objElement->AddChildNode($objPrimaryTable,'','p',array('class'=>'lead'));
    //make the wrapper
    $objTableBodyRow = $objElement->AddChildNode($objPrimaryTable,'','div',array('class'=>'row'));
    //make the primary table now
    $objTableBodyTable = $objElement->AddChildNode($objTableBodyRow,'','div',array('class'=>'col-md-12'));
    $objPanel = $objElement->AddChildNode($objTableBodyTable,'','div',array('class'=>'panel panel-default'));
    $objPanelHeading = $objElement->AddChildNode($objPanel,'','div',array('class'=>'panel-heading'));
    $objPanelBody = $objElement->AddChildNode($objPanel,'','div',array('class'=>'panel-body'));
    $objNavTabs = $objElement->AddChildNode($objPanelBody,'','ul',array('class'=>'nav nav-tabs'));
    foreach($arrTableData['tabs'] as $TabKey=>$TabValue){
    	$objNavTabs = $objElement->AddChildNode($objPanelBody,'','li',array('class'=>$TabValue['class'],''));
    	$objNavTabAnchors = $objElement->AddChildNode($objNavTabs,$TabValue['value'],'a',array('href'=>$TabValue['value'],'data-toggle'=>'tab','aria-expanded'=>$TabValue['expanded']));
    }
    $objTabContainer = $objElement->AddChildNode($objPanelBody,'','div',array('class'=>'tab-content'));
    foreach($arrTableData['tabledatacontainer'] as $ContainerKey=>$ContainerValues){
    	$objTabPane = $objElement->AddChildNode($objTabContainer,'','div',array('class'=>'tab-pane fade active in','id'=>$ContainerValues['id']));
    	$objTblHolder = $objElement->AddChildNode($objTabPane,'','div');
    	$objTable = $objElement->AddChildNode($objTblHolder,'','table',array('class'=>'table table-striped table-bordered table-hover'));
    	$objTableHeader = $objElement->AddChildNode($objTable,'','thead');
    	$objTableHeaderRow = $objElement->AddChildNode($objTableHeader,'','tr');
    	foreach($ContainerValues['tableheader'] as $TableHeaderKey=>$TableHeaderValue){
    		$objTableRowHeadings = $objElement->AddChildNode($objTableHeaderRow,$TableHeaderValue,'th');
    	}
    	$objTableBody = $objElement->AddChildNode($objTable,'','tbody');
    	foreach($ContainerValues['tabledata'] as $TableDataKey=>$TableDataValue){
    		$objTableBodyRow = $objElement->AddChildNode($objTableBody,'','tr');
    		$objTableCell = $objElement->AddChildNode($objTableBodyRow,$TableDataValue,'td');
    	}
    }
    return $objElement->CloseDocument();
  }

  /**
  * given an array of data make an accordion
  * @param $arrFormData
  *     -['panelheader'] = ['tabledescription'] {%Choose Station Record:%}
  *     -['form']
  *         -['headerkey'] = ['headername']
  *     -['tabledata']
  *         -['headerkey'] = ['columnvalue']
  *         -['linkvalue'] = ['linkvalue'] || ['onclickvalue'] = ['onclickvalue']
  * @return string HTML
  */
  function MakeBootStrapForm($arrFormData,$arrPOST){
    $objElement = new PCMW_Element();
    //$strTableId = 'table_'.rand(100,10000);
    $objPrimaryTable = $objElement->LoadHTMLTemplate('<div class="panel panel-default paper paper-curve-vert"></div>');
    if(trim($arrPOST['panelheader']) != '')
     $objElement->AddChildNode($objPrimaryTable,trim($arrPOST['panelheader']),'div',array('class'=>'panel-heading'));
    //make body now
    $objFormBody = $objElement->AddChildNode($objPrimaryTable,'','div',array('class'=>'panel-body'));
    //make the wrapper
    $objFormBodyWrapper = $objElement->AddChildNode($objPrimaryTable,'','div',array('class'=>'row'));
    $objInputElement = new PCMW_InputElement();
    if(array_key_exists('formgroup',$arrPOST) && $arrPOST['formgroup'] > 0){
      $objInputElement->intTotalFormElements = ($this->GetFormDefinitions($arrPOST['formgroup'],TRUE) + 1);
    }
    //echo 'POST: '.$arrPOST['formgroup'];
      $arrDefData = PCMW_Database::Get()->GetFormDefinitions($arrPOST['formgroup']);
      $arrDefinitions = PCMW_Database::Get()->CleanQuery($arrDefData);
    foreach($arrDefinitions as $arrKey=>$arrDefinition){
      $objInputDefinition = PCMW_FormDefinition::Get()->LoadObjectWithArray($arrDefinition);
      $strHTMLInput .= $objInputElement->ProcessFormDefinition($objInputDefinition, $arrPOST);
    }
    $objElement->AddChildNode($objFormBodyWrapper,$strHTMLInput,'div',array('class'=>'col-md-8 col-md-offset-2 paper paper-curve-vert'));
    return $objElement->CloseDocument();
  }

  /**
  * given an array of forms arrange a table to view them
  * @param $arrFormTblData
  * @return array
  *     -['tabledescription'] = ['tabledescription']
  *     -['tableheader']
  *         -['headerkey'] = ['headername']
  *     -['tabledata']
  *         -['headerkey'] = ['columnvalue']
  *         -['linkvalue'] = ['linkvalue']
  */
  function GetFormTableData($arrFormTblData){
   $arrBootStrapData = array('tabledescription'=>'Form Manager');
    //let's arrange these
    $arrHeadingValues = array();
    $arrAccessLevels = PCMW_StaticArrays::Get()->LoadStaticArrayType('accesslevels',FALSE,0,TRUE);
    foreach(PCMW_StaticArrays::Get()->LoadStaticArrayType('formheadings') as $arrHeadings)
      $arrHeadingValues[(int)$arrHeadings['modifier']] = $arrHeadings;
    //now prepare them for use
    foreach($arrHeadingValues as $arrHeading)
      $arrBootStrapData['tableheader'][$arrHeading['menuindex']] = $arrHeading['menuvalue'];
    foreach($arrFormTblData as $intKey=>$arrTableData){
      foreach($arrHeadingValues as $arrValues){
        if(array_key_exists($arrValues['menuindex'],$arrTableData)){
          if($arrValues['menuindex'] == 'formid'){
            $arrBootStrapData['tabledata'][$intKey][$arrValues['menuindex']]['formlinkclass'] = 'text-primary';
            $arrBootStrapData['tabledata'][$intKey][$arrValues['menuindex']]['formlinkvalue'] = $arrTableData['formid'];
          }
          elseif($arrValues['menuindex'] == 'formname'){
            $arrBootStrapData['tabledata'][$intKey][$arrValues['menuindex']]['formlinkclass'] = 'text-primary';
            $arrBootStrapData['tabledata'][$intKey][$arrValues['menuindex']]['formlinkvalue'] = $arrTableData['formid'];
          }
          else if($arrValues['menuindex'] == 'admingroup'){
            if($arrTableData[$arrValues['menuindex']] == PCMW_SUSPENDED){
              $arrTableData[$arrValues['menuindex']] = 'Universal';
            }
            else
              @$arrTableData[$arrValues['menuindex']] = @$arrAccessLevels[$arrValues['menuindex']];
          }
          elseif($arrValues['menuindex'] == 'formalias'){

          }

          $arrBootStrapData['tabledata'][$intKey][$arrValues['menuindex']]['value'] = $arrTableData[$arrValues['menuindex']];
        }
        else{
          $arrBootStrapData['tabledata'][$intKey][$arrValues['menuindex']]['value'] = $arrTableData[$arrValues['menuindex']];
        }
      }
    }
    return $arrBootStrapData;
  }


  /**
  * given an array of data make an accordion
  * @param $arrData
  * @param $arrHeadings
  * @return string HTML
  */
  function MakeAccordion($arrAccordionAttributes,$strData,$strTabHeaderStyle){
    $objElement = new PCMW_Element();
    $strCSSClasses = '';
    $objPrimaryTable = $objElement->LoadHTMLTemplate('<div class=""></div>');  
    //load class if available
    $objElement->AddChildNode($objPrimaryTable,$strCSSClasses,'span',array());
    $objPanelDefault = $objElement->AddChildNode($objPrimaryTable,'','div',array('class'=>''));
    //heading container
    //$objTableHeading = $objElement->AddChildNode($objPanelDefault,trim($strTitle),'div',array('class'=>'panel-heading'));
    //make body now
    $objTableBody = $objElement->AddChildNode($objPanelDefault,'','div',array('class'=>''));
    $objTableAccordion = $objElement->AddChildNode($objTableBody,'','div',array('class'=>'panel-group','id'=>'accordion_'.$arrAccordionAttributes['id']));
    //foreach($arrHeadings as $strKeys=>$strHeaderValue){
    $objPanelDefault = $objElement->AddChildNode($objTableAccordion,'','div',array('class'=>'panel panel-default'));
    $objPanelHeading = $objElement->AddChildNode($objPanelDefault,'','div',array('class'=>'panel-heading'));
    $objPanelTitle = $objElement->AddChildNode($objPanelHeading,'','h4',array('class'=>'panel-title','style'=>$strTabHeaderStyle));
    $objElement->AddChildNode($objPanelTitle,$arrAccordionAttributes['lead'],'a',array('class'=>'collapsed',
                                                            'aria-expanded'=>'false',
                                                            'href'=>'#collapse_'.$arrAccordionAttributes['key'].$arrAccordionAttributes['id'],
                                                            'data-parent'=>'#accordion_'.$arrAccordionAttributes['id'],
                                                            'data-toggle'=>'collapse',
                                                            'style'=>'width:100%;display:block;'));
    //make the panel content
    $objPanelParent = $objElement->AddChildNode($objPanelDefault,'','div',array('class'=>'panel-collapse collapse',
                                                                                'aria-expanded'=>'false',
                                                                                'id'=>'collapse_'.$arrAccordionAttributes['key'].$arrAccordionAttributes['id'],
                                                                                'style'=>'height: 0px;'));
    $objElement->AddChildNode($objPanelParent,$strData,'div',array('class'=>'panel-body'));
        //make the content
     // }
    $strForm = $objElement->CloseDocument();
    return $strForm;
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