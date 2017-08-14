<?php
/**************************************************************************
* @CLASS PCMW_CustomMenu
* @brief Create a custom menu.
* @REQUIRES:
*  -PCMW_Utility.php
*
**************************************************************************/
class PCMW_CustomMenu{
   //alt class designations
   var $strNavClass;
   var $strParentElementClass;
   var $strElementClass;
   var $strLinkClass;
   //menu orientation
   var $strMenuOrientation = 'horizontal';
   //menu alignment
   var $strMenuAlignment = 'floatleft';
   //use menu
   var $boolUseMenu = FALSE;
   //edit menu option
   var $boolEditMenu = FALSE;
   //header and footer data for the menu
   var $strPreHeaderHTML = '';
   var $strPostHeaderHTML = '';
   //menu options
   var $arrMenuOptions = NULL;  
   //debugging
   var $boolDebugOn = TRUE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_CustomMenu();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
    if(!$this->LoadMenuData())
        return TRUE;
  }

  /**
  * load the menu defaults for editing
  * @return bool
  */
  function LoadMenuData(){
   if(!array_key_exists('pc_menu_options',$_SESSION) || $this->boolEditMenu){
     if(!$strMenuOptions = get_option('PCMW_menucss')){
       $_SESSION['pc_menu_options'] = FALSE;
       return FALSE;
     }
     //load up our options now
     $this->arrMenuOptions = PCMW_Utility::Get()->JSONDecode($strMenuOptions);
     if($this->arrMenuOptions['mainsettings']['usemenu'] == 1 || $this->boolEditMenu){
       $this->EstablishClassDefaults();
       $_SESSION['pc_menu_options'] = $this->arrMenuOptions;
     }
     else{
        $_SESSION['pc_menu_options'] = FALSE;
        return FALSE;
     }
    }
    else if(!$_SESSION['pc_menu_options']){//menu not active
        return FALSE;
    }
    else{//menu options are loaded
      $this->arrMenuOptions = $_SESSION['pc_menu_options'];
     $this->EstablishClassDefaults();
     return TRUE;
    }
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
    $arrDebugMethods['PCMW_CustomMenu::SaveMenuControlSettings'] = 1;
    return $arrDebugMethods;
  }

  /**
  * get the custom menu sections for edit
  * @retun array()
  */
  function GetCustomMenuSections(){
   return array('mainsettings'=>'Main Settings',
                'nav'=>'Nav container',
                'parentelement'=>'Nav list',
                'element'=>'Link container',
                'link'=>'Link'
                );
  }

  /**
  * get the CSS defaults for the menu
  * @return array()
  */
  function LoadMenuCSSDefaults(){
   return array('mainsettings'=>array('usemenu'=>0,'menutype'=>'horizontal'),
                'nav'=>array('class'=>'container'),
                'parentelement'=>array('class'=>'nav navbar-nav'),
                'element'=>array('class'=>'navbar-default'),
                'link'=>array('class'=>'nav-top-link')
                );
  }

  /**
  * get a custom CSS input form
  * @param $arrValues: array()
  * @param 'sectionname' - section of form to modify. Serves as HTML id prefix.
  * @param 'sectioncount' number of existing
  * @return string ( HTML )
  */
  function MakeCSSOptionInput($arrValues,$boolMakeJSRegister = FALSE){
    PCMW_CSSInterface::Get()->boolMakeJSRegister = $boolMakeJSRegister;
    $arrOption = array('strnewdata'=>PCMW_CSSInterface::Get()->MakeCSSDataList($arrValues['sectionname'],$arrValues['sectioncount']));
    (int)$arrValues['sectioncount']++;
    $arrOption['strjavascript'] = 'RegisterCSSHandler';
    $arrOption['objjsonparameters'] = array('strOptionId'=>$arrValues['sectionname'].'_'.$arrValues['sectioncount']);
    return $arrOption;
  }


  /**
  * establish our class defaults for alternative designations
  * @return bool
  */
  function EstablishClassDefaults(){
   //NAV
   if(@trim($this->arrMenuOptions['nav']['class']) != '' && @trim($this->arrMenuOptions['nav']['alternate-class']) == '')
    $this->arrMenuOptions['nav']['alternate-class'] = @$this->arrMenuOptions['nav']['class'];
   //PARENTELEMENT
   if(@trim($this->arrMenuOptions['parentelement']['class']) != '' && @trim($this->arrMenuOptions['parentelement']['alternate-class']) == '')
    $this->arrMenuOptions['parentelement']['alternate-class'] = @$this->arrMenuOptions['parentelement']['class'];
   //ELEMENT
   if(@trim($this->arrMenuOptions['element']['class']) != '' && @trim($this->arrMenuOptions['element']['alternate-class']) == '')
    $this->arrMenuOptions['element']['alternate-class'] = @$this->arrMenuOptions['element']['class'];
   //LINK
   if(@trim($this->arrMenuOptions['link']['class']) != '' && @trim($this->arrMenuOptions['link']['alternate-class']) == '')
    $this->arrMenuOptions['link']['alternate-class'] = @$this->arrMenuOptions['link']['class'];
   //usemenu
   $this->boolUseMenu = (@$this->arrMenuOptions['mainsettings']['usemenu'] == 1)? TRUE: FALSE;
   //menu alignment
   $this->strMenuAlignment = (@trim($this->arrMenuOptions['mainsettings']['menutype']) == '')? $this->strMenuAlignment: $this->arrMenuOptions['mainsettings']['menutype'];
   //menu orientation
   $this->strMenuOrientation = (@trim($this->arrMenuOptions['mainsettings']['menuorient']) == '')? $this->strMenuOrientation: $this->arrMenuOptions['mainsettings']['menuorient'];
   $this->CleanPrePostHTML();
   return TRUE;
  }

  /**
  * clean the pre and post menu HTML sections for use
  * @return bool
  */
  function CleanPrePostHTML(){
   $this->strPreHeaderHTML = @trim($this->arrMenuOptions['mainsettings']['premenuhtml']);
   $this->strPostHeaderHTML = @trim($this->arrMenuOptions['mainsettings']['postmenuhtml']);
   PCMW_Utility::Get()->CleanPrePostHTML($this->strPreHeaderHTML);
   PCMW_Utility::Get()->CleanPrePostHTML($this->strPostHeaderHTML);
   return TRUE;
  }

  /**
  * alternate class switch
  * @return bool
  */
  function SwitchAltClasses(){
   //NAV
   $this->strNavClass = ($this->strNavClass == @$this->arrMenuOptions['nav']['class'])? @$this->arrMenuOptions['nav']['alternate-class'] : @$this->arrMenuOptions['nav']['class'];
   //PARENTELEMENT
   $this->strParentElementClass = ($this->strParentElementClass == @$this->arrMenuOptions['parentelement']['class'])? @$this->arrMenuOptions['parentelement']['alternate-class'] : @$this->arrMenuOptions['parentelement']['class'];
   //ELEMENT
   $this->strElementClass = ($this->strElementClass == @$this->arrMenuOptions['element']['class'])? @$this->arrMenuOptions['element']['alternate-class'] : @$this->arrMenuOptions['element']['class'];
   //LINK
   $this->strLinkClass = ($this->strLinkClass == @$this->arrMenuOptions['link']['class'])? @$this->arrMenuOptions['link']['alternate-class'] : @$this->arrMenuOptions['link']['class'];
  }

  /**
  * load menu options into session
  * @return bool
  */
  function LoadCustomMenuOptions(){
   $strOptions = '<link  href="'.get_template_directory_uri().'/css/pcmegatheme.css" rel="stylesheet" type="text/css" />';
   $strOptions .= '<link  href="'.get_template_directory_uri().'/css/custom-style.css" rel="stylesheet" type="text/css" />';
   $strOptions .= '
   <style type="text/css">
   <!--
   p.lead{
	color:#000000;
   }
   -->
   </style>';
   $strOptions .= '<ul style="list-style:none;color:#000000;background:#FFFFFF;">';
   if(!is_array($this->arrMenuOptions) || sizeof($this->arrMenuOptions) < 1)
      $this->arrMenuOptions = $this->LoadMenuCSSDefaults();
   $strOptions .= $this->LoadMenuPreview();
   $strOptions .= $this->MakeMainSettings();
   foreach($this->GetCustomMenuSections() as $strSection=>$strLabel){
     if($strSection == 'mainsettings')
        continue 1;//no easy way to iterate these, and they are made custom
     $arrAccordionAttributes = array('id'=>str_replace(' ','',$strLabel),'key'=>str_replace(' ','',$strLabel),'lead'=>ucfirst($strLabel));
     $strSectionOptions = '<li style="border:1px solid #CCCCCC;height:100%;" id="'.$strSection.'">';
     $strSectionOptions .= '<form method="post">';
     $strSectionOptions .= '<input type="hidden" value="'.$strSection.'" name="section" />';
     $strSectionOptions .= '<input type="hidden" value="savemenusection" name="dir" />';
     $strSectionOptions .= '<h3>'.$strLabel.'</h3>';
     $strSectionOptions .= '<input type="button" class="btn btn-primary" value="Add '.$strLabel.' style" onclick="LoadNewCSSInterface(\''.$strSection.'\',this.form)"/>';
       $strStyle = '';
       $strClass = '';
       $strAltClass = '';
     if(array_key_exists($strSection,$this->arrMenuOptions) && is_array($this->arrMenuOptions[$strSection]) && sizeof($this->arrMenuOptions[$strSection]) > 0){
       $intCount = 0;
       if(array_key_exists('style',$this->arrMenuOptions[$strSection])){
         //break up our style string
         $arrStyleParts = explode(';',$this->arrMenuOptions[$strSection]['style']);
         if(is_array($arrStyleParts) && sizeof($arrStyleParts) > 0){
           //populate the section array with our properties and parameters
           foreach($arrStyleParts as $strPropertySet){
            if(trim($strPropertySet) == '')
                continue 1;
            $arrPropertyPair = explode(':',$strPropertySet);
            $this->arrMenuOptions[$strSection][$arrPropertyPair[0]] = $arrPropertyPair[1];
           }
         }
       }
       //load the variables for the form
       foreach($this->arrMenuOptions[$strSection] as $strOption=>$strParameter){
        if(trim($strOption) == 'style'){
          $strStyle = $strParameter;
          continue 1;
        }
        else if(trim($strOption) == 'class')
          $strClass = $strParameter;
        else{
          if(trim($strOption) == 'alternate-class')
            $strAltClass = $strParameter;
        }
        $strSectionOptions .= PCMW_CSSInterface::Get()->MakeCSSDataList($strSection,$intCount,$strOption,$strParameter);
        $intCount++;
       }
     }
     $strSectionOptions .= '<div><div class="'.$strClass.'" style="'.$strStyle.'" id="sa_'.$strSection.'_sample">SampleText</div></div><br />';
     $strSectionOptions .= '<div><div class="'.$strAltClass.'" style="'.$strStyle.'" id="sa_'.$strSection.'_sample_alt">Alternate class SampleText</div></div>';
     $strSectionOptions .= '<input type="button" value="Save '.$strLabel.'" class="btn btn-success align-bottom" onclick="SubmitSelectedForm(this.form);" style=""/>';
     $strSectionOptions .= '</form>';
     $strSectionOptions .= '</li>';
     $strOptions .= PCMW_FormManagerTables::Get()->MakeAccordion($arrAccordionAttributes,$strSectionOptions,'');
   }
   $strOptions .= '</ul>';
   return $strOptions;
  }

  /**
  * make main settings interface
  * @return string ( HTML )
  */
  function MakeMainSettings(){
     $arrAccordionAttributes = array('id'=>'MainSettings','key'=>'MainSettings','lead'=>'Main Settings');
     //make the primary control
     $strOptions = '<li style="border:1px solid #CCCCCC;height:100%;" id="mainsettings">';
     $strOptions .= '<form method="post">';
     $strOptions .= '<h3>Main Settings</h3>';
     $strChecked = (@$this->arrMenuOptions['mainsettings']['usemenu'] == 1)? 'checked="checked"':'';
    //use menu
     $strOptions .= '<input type="checkbox" name="usemenu" value="1" '.$strChecked.' />: Use custom menu<br />';
     //menutype
     $strOptions .= '<br /><br />Menu Alignment : <select name="menutype">';
     $strSelected = (@$this->arrMenuOptions['mainsettings']['menutype'] == 'floatleft')? 'SELECTED':'';
     $strOptions .= '<option value="floatleft" '.$strSelected.'>Left</option>';
     $strSelected = (@$this->arrMenuOptions['mainsettings']['menutype'] == 'floatright')? 'SELECTED':'';
     $strOptions .= '<option value="floatright" '.$strSelected.'>Right</option>';
     $strOptions .= '</select>';
     //menu orinettion
     $strOptions .= '<br /><br />Menu Orientation : <select name="menuorient">';
     $strSelected = (@$this->arrMenuOptions['mainsettings']['menuorient'] == 'horizontal')? 'SELECTED':'';
     $strOptions .= '<option value="horizontal" '.$strSelected.'>Horizontal</option>';
     $strSelected = (@$this->arrMenuOptions['mainsettings']['menuorient'] == 'vertical')? 'SELECTED':'';
     $strOptions .= '<option value="vertical" '.$strSelected.'>Vertical</option>';
     $strOptions .= '</select><br />';
     $strOptions .= '<h5>Pre and Post HTML requires square brackets "[","]" instead of the traditional "<",">" brackets.</h5>';
     $strOptions .= 'Pre-Menu HTML<br /><textarea name="premenuhtml" cols="25" rows="7">';
     $strOptions .= @$this->arrMenuOptions['mainsettings']['premenuhtml'];
     $strOptions .= '</textarea><br />';
     $strOptions .= 'Post-Menu HTML<br /><textarea name="postmenuhtml" cols="25" rows="7">';
     $strOptions .= @$this->arrMenuOptions['mainsettings']['postmenuhtml'];
     $strOptions .= '</textarea><br />';
     $strOptions .= '<input type="hidden" value="mainsettings" name="section" />';
     $strOptions .= '<input type="hidden" value="savemenusection" name="dir" />';
     $strOptions .= '<input type="button" value="Save Main Settings" class="btn btn-success align-bottom" onclick="SubmitSelectedForm(this.form);" style="" />';
     $strOptions .= '</form>';
     $strOptions .= '</li>';
     return PCMW_FormManagerTables::Get()->MakeAccordion($arrAccordionAttributes, $strOptions,'');
  }

  /**
  * load the menu preview
  * @return string ( HTML )
  */
  function LoadMenuPreview(){
   $strPreview = '<li style="border:1px solid #CCCCCC;height:100%;" id="menupreview">';
   if(@$this->strMenuOrientation == 'vertical')
   $strPreview .= '<ul class="menu">';
   $strPreview .= $this->MakeCustomMenu();
   if(@$this->strMenuOrientation == 'vertical')
   $strPreview .= '</ul>';
   $strPreview .= '</li><!-- end preview container -->';
   return $strPreview;
  }

  /**
  * Make Menu
  * @return string  ( HTML )
  */
  function MakeCustomMenu(){
    if($this->boolUseMenu || $this->boolEditMenu){
      $strThemeLocation = 'primary';
      $this->SwitchAltClasses();
      if(@$this->strMenuOrientation == 'vertical')
       $strMenu = $this->MakeVerticalCustomMenu( $strThemeLocation );
      else
       $strMenu = $this->MakeHorizontalMenu( $strThemeLocation );
      //give it back
      return $strMenu;
    }
    return FALSE;                                                                                                                                
  }



  /**
  * load the custom options into a form for updating
  * @return bool
  */
  function UpdateMenuUpdateOptions($arrPOST){
    //load up our defaults now
    if(!is_array($this->arrMenuOptions) || sizeof($this->arrMenuOptions) < 1)
      $this->arrMenuOptions = $this->LoadMenuCSSDefaults();
    //we're resetting this now
    $this->arrMenuOptions[$arrPOST['section']] = array();
    //save our main settings differently
    if($arrPOST['section'] == 'mainsettings')
        return $this->SaveMenuControlSettings($arrPOST);
    $intPropertyCount = 1;
    $arrMenuOptions = array();
    //get new sections
    while(array_key_exists($arrPOST['section'].'_'.$intPropertyCount,$arrPOST)){
     //make sure this is one of our options
     if($arrPOST[$arrPOST['section'].'_'.$intPropertyCount.'_value'] != '' && $arrPOST[$arrPOST['section'].'_'.$intPropertyCount] != ''){
       //load our option
       if($arrPOST[$arrPOST['section'].'_'.$intPropertyCount] == 'class'){
         $arrMenuOptions['class'] = $arrPOST[$arrPOST['section'].'_'.$intPropertyCount.'_value'];
       }
       else if($arrPOST[$arrPOST['section'].'_'.$intPropertyCount] == 'alternate-class'){
         $arrMenuOptions['alternate-class'] = $arrPOST[$arrPOST['section'].'_'.$intPropertyCount.'_value'];
       }
       else{
         //style
        $arrMenuOptions['style'] .= $arrPOST[$arrPOST['section'].'_'.$intPropertyCount].':'.$arrPOST[$arrPOST['section'].'_'.$intPropertyCount.'_value'].';';
       }
     }
     $intPropertyCount++;
    }
    //load our option update data
    $this->arrMenuOptions[$arrPOST['section']] = $arrMenuOptions;

    $_SESSION['pc_menu_options'][$arrPOST['section']] = $arrMenuOptions;
    return update_option('PCMW_menucss',PCMW_Utility::Get()->JSONEncode($this->arrMenuOptions));
  }

  /**
  * save primary menu controls
  * @param $arrPOST
  * @return bool
  */
  function SaveMenuControlSettings($arrPOST){
    $boolMenuActive = $this->arrMenuOptions[$arrPOST['section']]['usemenu'];
    $arrMenuOptions = array();
    $arrMenuOptions['usemenu'] = (@$arrPOST['usemenu'] != 1)? 0 : 1;
    $arrMenuOptions['menutype'] = $arrPOST['menutype'];
    $arrMenuOptions['menuorient'] = ($arrPOST['menuorient'] == 'vertical')? 'vertical' : 'horizontal';
    $arrMenuOptions['premenuhtml'] = $arrPOST['premenuhtml'];
    $arrMenuOptions['postmenuhtml'] = $arrPOST['postmenuhtml'];
    //load our option update data
    $this->arrMenuOptions[$arrPOST['section']] = $arrMenuOptions;
    //update session
    $_SESSION['pc_menu_options'][$arrPOST['section']] = $arrMenuOptions;
    if($arrMenuOptions['usemenu'] == 0)
     $_SESSION['pc_menu_options'] = FALSE;
    else if($boolMenuActive == 0 && $arrMenuOptions['usemenu'] == 1){//turning on menu
        $this->LoadMenuData();
        $_SESSION['pc_menu_options'][$arrPOST['section']] = $arrMenuOptions;
    }
    else
        $_SESSION['pc_menu_options'][$arrPOST['section']] = $arrMenuOptions;
    return update_option('PCMW_menucss',PCMW_Utility::Get()->JSONEncode($this->arrMenuOptions));
  }


  /**
  * create the custom menu for display
  * @param $strThemeLocation
  * @return string  ( HTML )
  */
  function MakeVerticalCustomMenu($strThemeLocation,$strIDPreface=''){
    if ( ($strThemeLocation) && ($arrLocations = get_nav_menu_locations()) && isset($arrLocations[$strThemeLocation]) ) {
        $this->SwitchAltClasses();
        $menu = get_term( $arrLocations[$strThemeLocation], 'nav_menu' );
        $menu_items = wp_get_nav_menu_items($menu->term_id);
        $strMenuList = $this->strPreHeaderHTML;
        $strMenuList .= '<div class="sidebar-nav '.$this->strNavClass.'"  style="'.@$this->arrMenuOptions['nav']['style'].'">';
        $strMenuList .= '<nav class="navbar navbar-default" role="navigation">';
        $strMenuList .= '<div class="container-fluid">';
        $strMenuList .= '<div class="menu-header  center-block aligncenter marginauto">';

        $strMenuList .= '<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse" aria-expanded="false">' ."\n";
        $strMenuList .= '<span class="sr-only">'.get_bloginfo( 'name' ).'</span>' ."\n";
        $strMenuList .= '<span class="icon-bar"></span>' ."\n";
        $strMenuList .= '<span class="icon-bar"></span>' ."\n";
        $strMenuList .= '<span class="icon-bar"></span>' ."\n";
        $strMenuList .= '</button>' ."\n";
        $strMenuList .= '</div>';

        $strMenuList .= '<div class="collapse navbar-collapse navbar-ex1-collapse '.$this->strElementClass.'" style="'.@$this->arrMenuOptions['element']['style'].'">' ."\n";
        foreach( $menu_items as $menu_item ) {
          $bool = FALSE;
            if( $menu_item->menu_item_parent == 0 ) {

                $parent = $menu_item->ID;

                $menu_array = array();
                foreach( $menu_items as $submenu ) {
                    if( $submenu->menu_item_parent == $parent ) {
                        $bool = true;
                        $menu_array[] = '<li class="'.$this->strElementClass.' '.$this->strMenuOrientation.'menulist w100p" style="'.@$this->arrMenuOptions['element']['style'].'"><a href="' . $submenu->url . '" class="pc-navbar-brand '.$this->strLinkClass.'" style="'.@$this->arrMenuOptions['link']['style'].'">' . $submenu->title . '</a></li>' ."\n";
                    }
                }
                if( $bool == true && count( $menu_array ) > 0 ) {
                    $strMenuList .= '<!-- menu parent -->';
                    $strMenuList .= '<li class="dropdown '.$this->strElementClass.' '.$this->strMenuOrientation.'menulist" style="'.@$this->arrMenuOptions['element']['style'].'">' ."\n";
                    $strMenuList .= '<a href="'.$menu_item->url.'" class="pc-navbar-brand dropdown-toggle disabled '.$this->strLinkClass.'" style="'.@$this->arrMenuOptions['link']['style'].'" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">' . $menu_item->title . ' <span class="caret"></span></a>' ."\n";

                    $strMenuList .= '<ul class="dropdown-menu w100p">' ."\n";
                    $strMenuList .= implode( "\n", $menu_array );
                    $strMenuList .= '</ul>' ."\n";

                } else {
                    $strMenuList .= '<!-- menu child -->';
                    $strMenuList .= '<li class="'.$this->strElementClass.' '.$this->strMenuOrientation.'menulist" style="'.@$this->arrMenuOptions['element']['style'].'">' ."\n";
                    $strMenuList .= '<a href="' . $menu_item->url . '" class="pc-navbar-brand '.$this->strLinkClass.'" style="'.@$this->arrMenuOptions['link']['style'].'">' . $menu_item->title . '</a>' ."\n";
                }

            }

            // end <li>
            $strMenuList .= '</li>' ."\n";
            //switch the class if it exists
            $this->SwitchAltClasses();
        }
    $objNavObject = null;//wp_nav_menu();
    $strMenuList .= $this->PCMW_AddLogInRegisterLink('',$menu_items,'clear:both;width:100%;');
    $strMenuList .= $this->strPostHeaderHTML;
    $strMenuList .= '</div>' ."\n";
    $strMenuList .= '</div>' ."\n";
    $strMenuList .= '</nav>'."\n";
    $strMenuList .= '</div>'."\n";
    } else {
        $strMenuList = '<!-- no menu defined in location "'.$strThemeLocation.'" -->';
    }
    return $strMenuList;
  }


  function MakeHorizontalMenu( $strThemeLocation,$strIDPreface='' ) {
    if ( ($strThemeLocation) && ($arrLocations = get_nav_menu_locations()) && isset($arrLocations[$strThemeLocation]) ) {

        $strMenuList = '<!-- Begin horizontal menu -->' ."\n";
        $strMenuList .= $this->strPreHeaderHTML;
        $strMenuList .= '<nav class="'.$this->strMenuAlignment.' navbar navbar-default menu-header">' ."\n";
        $strMenuList .= '<div class="container-fluid">' ."\n";
        $strMenuList .= '<!-- Brand and toggle get grouped for better mobile display -->' ."\n";
        $strMenuList .= '<div class="menu-header center-block aligncenter marginauto">' ."\n";
        $strMenuList .= '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-ex1-collapse" aria-expanded="false">' ."\n";
        $strMenuList .= '<span class="sr-only">'.get_bloginfo( 'name' ).'</span>' ."\n";
        $strMenuList .= '<span class="icon-bar"></span>' ."\n";
        $strMenuList .= '<span class="icon-bar"></span>' ."\n";
        $strMenuList .= '<span class="icon-bar"></span>' ."\n";
        $strMenuList .= '</button>' ."\n";
        $strMenuList .= '</div>' ."\n";

        $strMenuList .= '<!-- Collect the nav links, forms, and other content for toggling -->';


        $menu = get_term( $arrLocations[$strThemeLocation], 'nav_menu' );
        $menu_items = wp_get_nav_menu_items($menu->term_id);

        $strMenuList .= '<div class="collapse navbar-collapse navbar-ex1-collapse">' ."\n";
        $strMenuList .= '<ul class="nav navbar-nav '.$this->strParentElementClass.'" style="'.@$this->arrMenuOptions['parentelement']['style'].'">' ."\n";
        foreach( $menu_items as $menu_item ) {
          $bool = FALSE;
            if( $menu_item->menu_item_parent == 0 ) {

                $parent = $menu_item->ID;

                $menu_array = array();
                foreach( $menu_items as $submenu ) {
                    if( $submenu->menu_item_parent == $parent ) {
                        $bool = true;
                        $menu_array[] = '<li class="floatleft '.$this->strElementClass.' w100p" style="'.@$this->arrMenuOptions['element']['style'].'"><a href="' . $submenu->url . '" class="pc-navbar-brand '.$this->strLinkClass.'" style="'.@$this->arrMenuOptions['link']['style'].'">' . $submenu->title . '</a></li>' ."\n";
                        //switch the class if it exists
                        $this->SwitchAltClasses();
                    }
                }
                if( $bool == true && count( $menu_array ) > 0 ) {
                    $strMenuList .= '<!-- menu parent -->';
                    $strMenuList .= '<li class="floatleft '.$this->strElementClass.'" style="'.@$this->arrMenuOptions['element']['style'].'">' ."\n";
                    $strMenuList .= '<a href="' . $menu_item->url . '" class="pc-navbar-brand dropdown-toggle disabled '.$this->strLinkClass.'" style="'.@$this->arrMenuOptions['link']['style'].'">' . $menu_item->title . ' <i class="caret"  data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">&nbsp;</i></a>' ."\n";

                    $strMenuList .= '<ul class="dropdown-menu">' ."\n";
                    $strMenuList .= implode( "\n", $menu_array );
                    $strMenuList .= '</ul>' ."\n";
                    // end <li>
                    $strMenuList .= '</li>' ."\n";

                } else {
                    $strMenuList .= '<!-- menu child -->';
                    $strMenuList .= '<li class="floatleft '.$this->strElementClass.'" style="'.@$this->arrMenuOptions['element']['style'].'">' ."\n";
                    $strMenuList .= '<a href="' . $menu_item->url . '" class="pc-navbar-brand '.$this->strLinkClass.'" style="'.@$this->arrMenuOptions['link']['style'].'">' . $menu_item->title . '</a>' ."\n";
            // end <li>
            $strMenuList .= '</li>' ."\n";
                }

            }

            //switch the class if it exists
            $this->SwitchAltClasses();
        }
        $objNavObject = null;//wp_nav_menu();
        $strMenuList .= $this->PCMW_AddLogInRegisterLink('',$objNavObject);
        $strMenuList .= '</ul><!-- / ul -->' ."\n";
        $strMenuList .= '</div><!-- /.collapse navbar-collapse -->' ."\n";
        $strMenuList .= '</div><!-- /.container-fluid -->' ."\n";
        $strMenuList .= '</nav>' ."\n";
        $strMenuList .= $this->strPostHeaderHTML;

    } else {
        $strMenuList = '<!-- no menu defined in location "'.$strThemeLocation.'" -->';
    }
    return $strMenuList;
  }


  /**
  * given a menu, adjust it for login and registration options
  * @param $strMenuItems
  * @param $objNavObject
  * @return object
  */
  function PCMW_AddLogInRegisterLink($strMenuItems,$objNavObject,$strVertical=''){
    $strHorizontal = '';
    if(trim($strVertical) == '')
        $strHorizontal = 'floatleft ';
    if (is_user_logged_in() &&
        PCMW_USECUSTOMLOGIN > 0 &&
        (is_null($objNavObject) ||
         @$objNavObject->theme_location == 'primary' ||
         @$objNavObject->theme_location == '')) {//
        $strMenuItems .= '<li class="'.$strHorizontal.$this->strElementClass.' '.$this->strMenuOrientation.'menulist" style="'.@$this->arrMenuOptions['element']['style'].' '.$strVertical.'">';
        $strMenuItems .= '<a href="'. site_url('logout/') .'"  class="pc-navbar-brand '.$this->strLinkClass.'" style="'.$strVertical.''.@$this->arrMenuOptions['link']['style'].'">Log Out</a>';
        $strMenuItems .= '</li>';
        //switch the class if it exists
        $this->SwitchAltClasses();
        if(PCMW_Abstraction::Get()->CheckPrivileges(PCMW_USERADMIN,PCMW_ADMINISTRATOR,FALSE,FALSE)){
            $strMenuItems .= '<li class="'.$strHorizontal.$this->strElementClass.' '.$this->strMenuOrientation.'menulist" style="'.@$this->arrMenuOptions['element']['style'].' '.$strVertical.'"><a href="'. site_url('wp-admin/') .'"class="pc-navbar-brand '.$this->strLinkClass.'" style="'.$strVertical.''.@$this->arrMenuOptions['link']['style'].'">Admin</a></li>';
        //switch the class if it exists
        $this->SwitchAltClasses();
        }
        else{
            $strMenuItems .= '<li class="'.$strHorizontal.$this->strElementClass.' '.$this->strMenuOrientation.'menulist" style="'.@$this->arrMenuOptions['element']['style'].' '.$strVertical.'">';
            $strMenuItems .= '<a href="'. site_url('wp-admin/profile.php') .'"  class="pc-navbar-brand '.$this->strLinkClass.'" style="'.$strVertical.''.@$this->arrMenuOptions['link']['style'].' '.$strVertical.'">My Account</a>';
            $strMenuItems .= '</li>';
            //switch the class if it exists
            $this->SwitchAltClasses();
        }
    }
    else if (!is_user_logged_in() &&
            (is_null($objNavObject) ||
             @$objNavObject->theme_location == 'primary' ||
             @$objNavObject->theme_location == '')){//
        if(PCMW_USECUSTOMREGISTRATION > 0){
            $strMenuItems .= '<li class="'.$strHorizontal.$this->strElementClass.' '.$this->strMenuOrientation.'menulist" style="'.@$this->arrMenuOptions['element']['style'].' '.$strVertical.'">';
            $strMenuItems .= '<a href="'. site_url(PCMW_REGISTRATIONPAGE) .'"  class="pc-navbar-brand '.$this->strLinkClass.'" style="'.$strVertical.''.@$this->arrMenuOptions['link']['style'].'">Register</a>';
            $strMenuItems .= '</li>';
            //switch the class if it exists
            $this->SwitchAltClasses();
        }
        if(PCMW_USECUSTOMLOGIN > 0){
            $strMenuItems .= '<li class="'.$strHorizontal.$this->strElementClass.' '.$this->strMenuOrientation.'menulist" style="'.@$this->arrMenuOptions['element']['style'].' '.$strVertical.'">';
            $strMenuItems .= '<a href="'. site_url(PCMW_LOGINPAGE) .'" class="pc-navbar-brand '.$this->strLinkClass.'" style="'.$strVertical.''.@$this->arrMenuOptions['link']['style'].'">Login</a>';
            $strMenuItems .= '</li>';
            //switch the class if it exists
            $this->SwitchAltClasses();
        }
    }
    else{
      //do nothing
    }
    if((int)PCMW_USECONTACTUS > 0){
      $strMenuItems .= '<li class="'.$strHorizontal.$this->strElementClass.' '.$this->strMenuOrientation.'menulist" style="'.@$this->arrMenuOptions['element']['style'].' '.$strVertical.'">';
      $strMenuItems .= '<a href="'. site_url('contact-us') .'" class="pc-navbar-brand '.$this->strLinkClass.'" style="'.$strVertical.''.@$this->arrMenuOptions['link']['style'].'">Contact Us</a>';
      $strMenuItems .= '</li>';
      //switch the class if it exists
      $this->SwitchAltClasses();
    }
    if((int)PCMW_USECUSTOMHAWD > 0){
      $strMenuItems .= '<li class="'.$strHorizontal.$this->strElementClass.' '.$this->strMenuOrientation.'menulist" style="'.@$this->arrMenuOptions['element']['style'].' '.$strVertical.'">';
      $strMenuItems .= '<a href="'. site_url('how-are-we-doing') .'" class="pc-navbar-brand '.$this->strLinkClass.'" style="'.$strVertical.''.@$this->arrMenuOptions['link']['style'].'">How Are We Doing</a>';
      $strMenuItems .= '</li>';
      //switch the class if it exists
      $this->SwitchAltClasses();
    }

    return $strMenuItems;
  }
  #ENDREGION

  #REGION DEBUGGINGMETHODS



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

  #ENDREGION

}//end class  
?>