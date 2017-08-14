<?php
/****************************************
*  Class: PCMW_Element
*  @brief: object container for HTML elements
*
*
****************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
class PCMW_Element{
  var $arrSelfClosingElements = array();
  var $objHTML = null;
  var $objDocument = null;
  var $intHTML = 1;//0 for xml 1 for HTML
  var $boolDebugOn = FALSE;
  public static function Get(){
		//==== instantiate or retrieve singleton from Josh and medad code====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_Element();
		return( $inst );
   }

   public function __construct(){
    // construct here
    if($this->objHTML == null)
      $this->objHTML = new SimpleXMLElement('<html></html>');
   }

   //make a table
   function LoadHTMLTemplate($strHTML,$boolIsXML = FALSE){
     if(phpversion() >= 5 && $boolIsXML){
        if($boolIsXML)
          $this->objHTML = simplexml_load_string($strHTML, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
        else{
          $objHTML = new DOMDocument;
          $this->objHTML = $objHTML->loadXML($strHTML);
          //$this->objHTML = new simplexml_import_dom($objHTML);
        }
     }
     else
        $this->objHTML = new SimpleXMLElement($strHTML);
        return $this->objHTML;
   }

   //add an element
   function FindElementByName($objParentNode, $strElementName){
     $objChildNode = null;
     if($strElementName !=""){
       foreach( $objParentNode->children() as $objChild ){
         //lets get recursive with it.
         if($objChild->children()->count() > 0){
            if($objChildNode = $this->FindElementByName($objChild, $strElementName)){
              return $objChildNode;
            }
         }
         if($objChild["name"] == $strElementName){
           return $objChild;
         }
       }
     }
     return FALSE;
   }

   //do what it says
   function FindElementByTagName($objParentElement,$strTagName,$intIndex = 0){
      $objChildNode = null;
     if($intIndex > 0)
        $objParentElement = $objParentElement[$intIndex];
     if($strTagName !=""){
       foreach( $objParentElement->children() as $objChild ){
         //lets get recursive with it.
         if(count($objChild->children()) > 0){
            if($objChildNode = $this->FindElementByTagName($objChild, $strTagName)){
              return $objChildNode;
            }
         }
         if($objChild->getName() == $strTagName){
           return $objChild;
         }
       }
     }
     return FALSE;
   }

   //we need to update an elements content
   function UpdateElementContent($objElement,$strNewContent){
     $objElement[0] =  ($objElement[0].$strNewContent);
     return $objElement;
   }

   //we need to update an elements content
   function CalculateElementContent($objElement,$intNewValue = 0,$strOperator = '+'){
     if($strOperator == '+')
        $objElement[0] =  ($objElement[0] + $intNewValue);
     if($strOperator == '*')
        $objElement[0] =  ($objElement[0] * $intNewValue);
     if($strOperator == '/')
        $objElement[0] =  ($objElement[0] / $intNewValue);
     if($strOperator == '-')
        $objElement[0] =  ($objElement[0] - $intNewValue);
     if($strOperator == '%')
        $objElement[0] =  ($objElement[0] % $intNewValue);
     return $objElement;
   }

   //we need to replace an elements content
   function ReplaceElementContent($objElement,$strNewContent){
     $objElement[0] =  $strNewContent;
     return $objElement;
   }

   //upate an element
   function UpdateElementAttribute($objParentNode, $arrAttributes){
     $objTempObjectAttributes = array();  
     //unset everything;
     if(is_object($objParentNode)){
       foreach($objParentNode->attributes() as $ka=>$va){
         $objTempObjectAttributes[$ka] = $va;
          if(array_key_exists($ka,$arrAttributes))
              $objParentNode[$ka]= '';
       }
     }
     //reset them now
     foreach($arrAttributes as $kb=>$vb){
       if(trim($kb) == '' )
        continue 1;
        if(!array_key_exists($kb,$objTempObjectAttributes))
            $objParentNode->addAttribute($kb,$vb);
        else
           $objParentNode->attributes()->$kb=$vb;
     }
     return $objParentNode;
   }

   //we need to set attributes
   function SetElementAttributes($objElement,$arrAttributes){

   }

   //lets set the row table cells
   function SetBlankSpacerRow($objParent,$intCellCount,$arrAttributes = array()){
     //lets make the row
     $objTableRow = $this->AddChildNode($objParent, '','tr',array());
     for($i=0;$i<$intCellCount;$i++){
        $this->AddChildNode($objTableRow, '','td',$arrAttributes);
     }
   }

   //add a child to the XML/HTML
   function AddChildNode($objParentNode,$strContent='', $strChildName = '',$arrAttributes = array(),$objNewChild = null){
     if(!is_object($objParentNode))
        PCMW_Logger::Debug('$strContent ['.$strContent.'] is not an object.',1,1);
     if($objNewChild == null)
        $objNewChildElement = $objParentNode->addChild($strChildName);
     else
        $objNewChildElement = $objParentNode->addChild($objNewChild['name']);
     //add the content
     $objNewChildElement = $this->UpdateElementContent($objNewChildElement,$strContent);
     //now we'll add the attributes
     if(is_array($arrAttributes) && sizeof($arrAttributes) > 0)
        $this->UpdateElementAttribute($objNewChildElement, $arrAttributes);
        return  $objNewChildElement;
   }

  //==========================================================================
  // from :http://snipplr.com/view.php?codeview&id=3491
  //============================================================================//

  /**
     * The main function for converting to an XML document.
     * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
     *
     * @param array $data
     * @param string $rootNodeName - what you want the root node to be - defaultsto data.
     * @param SimpleXMLElement $this->objHTML - should only be used recursively
     * @return string XML
     */
     function toXML( $arrData, $strNodeName = 'root',&$objXML=NULL ) {

        // turn off compatibility mode as simple xml throws a wobbly if you don't.
        //if ( ini_get('zend.ze1_compatibility_mode') == 1 ) ini_set ( 'zend.ze1_compatibility_mode', 0 );
        if ( is_null( $objXML ) ){
            $objXML = simplexml_load_string("<$strNodeName/>");
        }
        // loop through the data passed in.
        foreach( $arrData as $strKey => $varChildData ) {
          $boolIsNumeric = FALSE;
          $strKey = PCMW_Utility::Get()->RemoveSpecialFormatting(trim($strKey));
          $strKey = str_replace(array( '&', '"', "'", '<', '>',',','(',')' ),array( '' , '', '' , '' , '','', '','' ),$strKey);
            // no numeric keys in our xml please!
            if ( is_numeric( $strKey ) || strlen($strKey) < 2) {
                //continue 1;
                $boolIsNumeric = TRUE;
                $strKey = (int)$strKey+1;
            }

            // delete any char not allowed in XML element names
            $strKey = str_replace(' ', '_', trim($strKey));
            $strKey = preg_replace('/[^a-z0-9\-\_\.\:]/i', '', $strKey);
            $strKey = str_replace('*', '', $strKey);
            //PCMW_Logger::Debug( '$strKey ='.$strKey.' LINE ['.__LINE__.'] METHOD ['.__METHOD__.']',1);
            // if there is another array found recrusively call this function
            if ( is_array( $varChildData ) ) {
                if ( $boolIsNumeric ) $strKey = (int)$strKey+1;
                //associative arrays and numeric indexes need a parent key
                $objXMLNode = $this->isAssoc( $varChildData ) || $boolIsNumeric ? $objXML->addChild( $strKey,'' ) : $objXML;
                // recrusive call.
                $this->toXml( $varChildData, $strKey, $objXMLNode );
            } else {
                $varChildData = PCMW_Utility::Get()->RemoveSpecialFormatting($varChildData);
                $varChildData = str_replace(array( '&', '"', "'", '<', '>' ),array( '' , '', '' , '' , '' ),trim($varChildData));
                // add single node.
                $varChildData = htmlentities( strip_tags($varChildData) );
                $objXML->addChild( $strKey, $varChildData );
            }
        }

        // pass back as clean XML
        $strXML = $objXML->asXML();
        $strXML = str_replace("\r",'',$strXML);
        $strXML = str_replace("\n",'',$strXML);
        //$strXML = preg_replace('/[ ]{2,}|[\t]/', ' ', trim($strXML));
        PCMW_Logger::Debug( '$strXML ['.$strXML.'] LINE ['.__LINE__.'] METHOD ['.__METHOD__.']',1);
        return $strXML;
    }

    /**
     * Convert an XML document to a multi dimensional array
     * Pass in an XML document (or SimpleXMLElement object) and this recrusively loops through and builds a representative array
     *
     * @param string $this->objHTML - XML document - can optionally be a SimpleXMLElement object
     * @return array ARRAY
     */
     function toArray( $objXML ) {
         $arrReturn = array();
        foreach ($objXML as $objElement) {
          $strTagName = $objElement->getName();
          //echo $strTagName.' - $strTagName<br />';
          if(!array_key_exists($strTagName,$arrReturn)){
            $objElementVars = get_object_vars($objElement);
          //echo '<pre>';
          //print_r($objElementVars);
          //echo '</pre><br />';
            if (!empty($objElementVars)) {

              $arrReturn[$strTagName] = $objElement instanceof SimpleXMLElement ? $this->toArray($objElement) : $objElementVars;
            }
            else {
              $arrReturn[$strTagName] = trim($objElement);
            }
          }
          else{
            $objElementVars = get_object_vars($objElement);
            //echo $strTagName.' - $strTagName<br />';
            if(is_array($arrReturn[$strTagName])){
              $varValue = $arrReturn[$strTagName];
              //reset the value now
              $arrReturn[$strTagName] = array();
              $arrReturn[$strTagName][] = $varValue;
            }
            //add the new value now
            if (!empty($objElementVars)) {
              $arrReturn[$strTagName][] = $objElement instanceof SimpleXMLElement ? $this->toArray($objElement) : $objElementVars;
            }
            else {
              $arrReturn[$strTagName][] = trim($objElement);
            }
          }
        }
        return $arrReturn;
    }

    // determine if a variable is an associative array
     function isAssoc( $array ) {
        return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
    }

  //==========================================================================
  // ^^^^^^^^^^^ from :http://bookofzeus.com/articles/php/convert-simplexml-object-into-php-array/ ^^^^^^^^^^
  // Yes, I was being lazy
  //============================================================================//

   //wrap up the HTML/XML
   function CloseDocument($objAlternativeHTML = null,$boolIsXML = FALSE){
      if(!$boolIsXML){
        if($objAlternativeHTML != null)
          $objHTML = dom_import_simplexml($objAlternativeHTML);
        if($objAlternativeHTML == null)
          $objHTML = dom_import_simplexml($this->objHTML);
        $objDocument = new DOMDocument('1.0', 'utf-8');
        $objImportElement = $objDocument->importNode($objHTML,true);
        $objDocument->appendChild($objImportElement);
        return html_entity_decode($objDocument->saveHTML());
      }
      else{
        return $this->objHTML->asXML();
      }
   }

   //get
}//end class  
?>