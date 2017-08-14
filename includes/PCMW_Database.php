<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
class PCMW_Database{

  /*******************************
  * Make the constructor and get methods
  ***********************************/

  public $WPDB;
  public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_Database();
		return( $inst );
   }

   public function __construct(){
    global $wpdb;
        $this->WPDB = $wpdb;
   }


  /**
   * Given a string, prepare it for entry into a database and return db-safe
   * string.
   *
   * Currently calls mysql_real_escape_string on the htmlentities()'d string.
   * NOTE: any data which requires entities to be decoded will have to be
   * decoded in the data's containing class.
   *
   * @param string $strvar
   * @return string
   */
  function safe($strvar, $encode = false, $lrn2br = false)
  {
    // needs a flag because we don't want to do this for all strings
    if($lrn2br === true)
      $strvar = nl2br($strvar);

    if($encode === true)
    {
      $strvar = htmlspecialchars($strvar, ENT_COMPAT, 'UTF-8', false);
    }
    if(!get_magic_quotes_gpc())
    {
      //PCMW_Logger::Debug('In: [' . $strvar . ']', 1);
      if(version_compare(PHP_VERSION, '5.5.0') >= 0)
        $strvar = mysqli_real_escape_string($this->db_link,$strvar);
      else
        $strvar = mysql_real_escape_string($strvar);

      //PCMW_Logger::Debug('Out: [' . $strvar . ']', 1);
    }
    else
    {
      //PCMW_Logger::Debug('In: [' . $strvar . ']', 1);
      if(version_compare(PHP_VERSION, '5.5.0') >= 0)
        $strvar = mysqli_real_escape_string(stripslashes($this->db_link,$strvar));
      else
        $strvar = mysql_real_escape_string(stripslashes($strvar));
      //PCMW_Logger::Debug('Out: [' . $strvar . ']', 1);
    }
    if(trim($strvar) == '')
        $strvar = NULL;
    return $strvar;
  } // end function safe()

  /**
  * check to see if a table exists
  * @$strTable
  * @return bool
  */
  function CheckForTable($strTable){
    if($strTable == '')
        return TRUE;//DO NOT OVERWRITE TABLES
    if($this->WPDB->get_var("SHOW TABLES LIKE 'PC_".$strTable."'") != 'PC_'.$strTable)
     return FALSE;
    return TRUE;
  }

  /**
  * Drop a table
  * @$strTable
  * @return bool
  */
  function DropTable($strTable){
    if($strTable == '')
        return TRUE;//DO NOT OVERWRITE TABLES
    if($this->WPDB->query("DROP TABLE PC_".$strTable))
     return TRUE;
    return FALSE;
  }


  #REGION VENDORS

  /**
  * create the form definitions table
  * @return bool
  */
  function CreateTable($strQuery){
    $charset_collate = $this->WPDB->get_charset_collate();
    $strQuery = str_replace('%TABLE%',$this->WPDB->prefix,$strQuery);
    $strQuery = str_replace('%COLLATE%',$charset_collate,$strQuery);
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    //PCMW_Logger::Debug('$strQuery ['.$strQuery.'] ',1);
    return dbDelta($strQuery);
  }

  /**
  * given a raw query, run it
  * @param string $strRawQuery raw SQL query to fire
  * @return resource || FALSE
  */
  function RunRawQuery($strQuery){
     if($varResult = $this->WPDB->query($strQuery)){
         return $varResult;
     }
     else{
         PCMW_Logger::Debug('Query failed ['.$strQuery.'] ['.__METHOD__.']',1);
         return FALSE;
     }
  }

  /**
  * get the last insert ID for queries
  * @return last insert id
  */
  function GetLastInsertId(){
    return $this->WPDB->insert_id;
  }

  //we need to insert a program
  function InsertVendor($objVendor){
    $strQuery = 'INSERT INTO PC_vendors(wp_userid,'.
                                  'vendorname,'.
                                  'vendoraddress,'.
                                  'vendorcity,'.
                                  'vendorstate,'.
                                  'vendorzip,'.
                                  'vendordescription,'.
                                  'vendortelephone,'.
                                  'vendorwebsite,'.
                                  'vendorstatus,'.
                                  'vendoricon,'.
                                  'vendorrecordid,'.
                                  'latitude,'.
                                  'longitude) VALUES("'.$this->safe((int)$objVendor->intWPUserId).'","'.
                                                     $this->safe($objVendor->strVendorName).'","'.
                                                     $this->safe($objVendor->strVendorAddress).'","'.
                                                     $this->safe($objVendor->strVendorCity).'","'.
                                                     $this->safe($objVendor->strVendorState).'","'.
                                                     $this->safe((int)$objVendor->intVendorZip).'","'.
                                                     $this->safe($objVendor->strVendorDescription).'","'.
                                                     $this->safe($objVendor->strVendorPhone).'","'.
                                                     $this->safe($objVendor->strVendorWebsite).'","'.
                                                     $this->safe((int)$objVendor->intVendorStatus).'","'.
                                                     $this->safe((string)$objVendor->strVendorIcon).'","'.
                                                     $this->safe((int)$objVendor->strVendorIcon).'","'.
                                                     $this->safe((float)$objVendor->floatVendorLatitude).'","'.
                                                     $this->safe((float)$objVendor->floatVendorLongitude).'")';
                                 //$this->intVendorRecordId = (int)stripslashes($arrArray['vendorrecordid']);
       //PCMW_Logger::Debug('$strQuery ['.$strQuery.'] ',1);
       if($this->WPDB->query($strQuery)){
           return $this->WPDB->insert_id;
       }
       else{
           PCMW_Logger::Debug('Cannot insert PCMW_Vendor ['.$strQuery.'] ['.__METHOD__.']',1);
           return FALSE;
       }
  }


    /**
    * given a vendorid, delete it
    * @param $intVendorId
    * @return bool
    */
    function RemoveVendor($intVendorId){
      $strQuery = 'DELETE FROM PC_vendors WHERE vendorid = "'.$intVendorId.'"';
      if($this->WPDB->query($strQuery)){
          return TRUE;
      }
      else{
          PCMW_Logger::Debug('Cannot delete from mapgroup ['.$strQuery.'] ['.__METHOD__.']',1);
          return FALSE;
      }
    }

  //we need to insert a program
  function UpdateVendor($objVendor){
     $strQuery = 'UPDATE PC_vendors SET wp_userid = "'.$this->safe($objVendor->intWPUserId).'",'.
                                 'vendorname = "'.$this->safe($objVendor->strVendorName).'",'.
                                 'vendoraddress = "'.$this->safe($objVendor->strVendorAddress).'",'.
                                 'vendorcity = "'.$this->safe($objVendor->strVendorCity).'",'.
                                 'vendorstate = "'.$this->safe($objVendor->strVendorState).'",'.
                                 'vendorzip = "'.$this->safe($objVendor->intVendorZip).'",'.
                                 'vendordescription = "'.$this->safe($objVendor->strVendorDescription).'",'.
                                 'vendortelephone = "'.$this->safe($objVendor->strVendorPhone).'",'.
                                 'vendorwebsite = "'.$this->safe($objVendor->strVendorWebsite).'",'.
                                 'vendorstatus = "'.$this->safe($objVendor->intVendorStatus).'",'.
                                 'vendoricon = "'.$this->safe((string)$objVendor->strVendorIcon).'",'.
                                 'latitude = "'.$this->safe($objVendor->floatVendorLatitude).'",'.
                                 'longitude = "'.$this->safe($objVendor->floatVendorLongitude).'"'.
                                 ' WHERE vendorid = '.$objVendor->intVendorId;
                                 //$this->intVendorRecordId = (int)stripslashes($arrArray['vendorrecordid']);
       if($results = $this->WPDB->query($strQuery)){
           return TRUE;
       }
       else{
           PCMW_Logger::Debug('Cannot update vendor [ ['.$strQuery.'] ['.__METHOD__.']',1);
           return FALSE;
       }
  }


  //we need to get all the programs
  function GetAllVendors($boolActiveOnly=FALSE,$strOrderBy='',$intStartLimit=0,$intStopLimit=0){
    $strQuery = 'SELECT * FROM PC_vendors';
     if($boolActiveOnly)
        $strQuery .= ' WHERE vendorstatus > 1';
       if(trim($strOrderBy) != '')
        $strQuery .= $strOrderBy;
       if((int)$intStopLimit > 0)
        $strQuery .= ' LIMIT '.$intStartLimit.','.$intStopLimit;
       //PCMW_Logger::Debug('$strQuery ['.$strQuery.'] ['.__METHOD__.']',1);
       if($results = $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $results;
       }
       else{
           PCMW_Logger::Debug('Cannot get ANY vendors somehow. ['.$strQuery.'] ['.__METHOD__.']',1);
           return FALSE;
       }
  }

  //we need to get a program by name
  function GetVendorByName($strName){
    $strQuery = 'SELECT * FROM PC_vendors WHERE vendorname = "'.$strName.'"';
       if($results = $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $results;
       }
       else{
           PCMW_Logger::Debug('Cannot get vendor ['.$strQuery.'] ['.__METHOD__.']',1);
           return FALSE;
       }
  }

  //we need to get a program by it's program id
  function GetVendorById($intId){
    $strQuery = 'SELECT * FROM PC_vendors WHERE vendorid = '.$intId;
       if($results = $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $results;
       }
       else{
           PCMW_Logger::Debug('Cannot get vendor ['.$strQuery.'] ['.__METHOD__.']',1);
           return FALSE;
       }
  }
  #ENDREGION

  #REGION MAP GROUPS

  //we need to insert a program
  function InsertMapGroup($objMapGroup){
    $strQuery = 'INSERT INTO PC_mapgroups(groupname,'.
                                      'groupsettings,'.
                                      'edate) VALUES("'.$this->safe($objMapGroup->strGroupName).'",'.
                                                         "'".$this->safe($objMapGroup->strMapGroupSettings)."',".
                                                         '"'.time().'")';
       //PCMW_Logger::Debug('$strQuery ['.$strQuery.'] ',1);
       if($this->WPDB->query($strQuery)){
           return $this->WPDB->insert_id;
       }
       else{
           PCMW_Logger::Debug('Cannot insert mapgroup ['.$strQuery.'] ['.__METHOD__.']',1);
           return FALSE;
       }
  }


    /**
    * given a groupid, delete it
    * @param $intGroupId
    * @return bool
    */
    function RemoveMapGroup($intGroupId){
      $strQuery = 'DELETE FROM PC_mapgroups WHERE groupid = "'.$intGroupId.'"';
      if($this->WPDB->query($strQuery)){
          return TRUE;
      }
      else{
          PCMW_Logger::Debug('Cannot delete from mapgroup ['.$strQuery.'] ['.__METHOD__.']',1);
          return FALSE;
      }
    }

  //we need to insert a program
  function UpdateMapGroup($objMapGroup){
     $strQuery = 'UPDATE PC_mapgroups SET groupname = "'.$this->safe($objMapGroup->strGroupName).'",'.
                                       "groupsettings = '".$this->safe($objMapGroup->strMapGroupSettings)."',".
                                       'edate = "'.time().'"'.
                                       ' WHERE groupid = '.$objMapGroup->intMapGroupId;
       if($results = $this->WPDB->query($strQuery)){
           return TRUE;
       }
       else{
           PCMW_Logger::Debug('Cannot update mapgroup [ ['.$strQuery.'] ['.__METHOD__.']',1);
           return FALSE;
       }
  }


  //we need to get all the programs
  function GetAllMapGroups($boolActiveOnly=FALSE,$strOrderBy='',$intStartLimit=0,$intStopLimit=0){
    $strQuery = 'SELECT * FROM PC_mapgroups';
       if(trim($strOrderBy) != '')
        $strQuery .= $strOrderBy;
       if((int)$intStopLimit > 0)
        $strQuery .= ' LIMIT '.$intStartLimit.','.$intStopLimit;
           PCMW_Logger::Debug('$strQuery ['.$strQuery.'] ['.__METHOD__.']',1);
       if($results = $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $results;
       }
       else{
           PCMW_Logger::Debug('Cannot get ANY mapgroups somehow. ['.$strQuery.'] ['.__METHOD__.']',1);
           return FALSE;
       }
  }

  //we need to get a program by name
  function GetMapGroupByName($strName,$boolJoinLinks=FALSE){
    $strQuery = 'SELECT * FROM PC_mapgroups AS mg';
    if($boolJoinLinks)
        $strQuery .= ' LEFT JOIN PC_mapgrouplink AS ml ON mg.groupid = ml.lgroupid';
    $strQuery .= '  WHERE groupname = "'.$strName.'"';
    if($results = $this->WPDB->get_results($strQuery,ARRAY_A)){
      return $results;
    }
    else{
      PCMW_Logger::Debug('Cannot get mapgroup ['.$strQuery.'] ['.__METHOD__.']',1);
      return FALSE;
    }
  }

  //we need to get a program by it's program id
  function GetMapGroupById($intId,$boolJoinLinks=FALSE){
    $strQuery = 'SELECT * FROM PC_mapgroups AS mg' ;
    if($boolJoinLinks)
        $strQuery .= ' LEFT JOIN mapgrouplink AS ml ON mg.groupid = ml.lgroupid';
    $strQuery .= ' WHERE mg.groupid = '.$intId;
    if($results = $this->WPDB->get_results($strQuery,ARRAY_A)){
        return $results;
    }
    else{
        PCMW_Logger::Debug('Cannot get mapgroup ['.$strQuery.'] ['.__METHOD__.']',1);
        return FALSE;
    }
  }
  #ENDREGION

  #REGION MAP LINKS
    /**
    * given a map id and groupid, add it to the link list
    * @param $intMapId
    * @param $intGroupId
    * @return bool
    */
    function AddVendorToGroup($intMapId,$intGroupId){
      $strQuery = 'INSERT INTO PC_mapgrouplink VALUES(NULL,"'.$intMapId.'", "'.$intGroupId.'")';
      if($this->WPDB->query($strQuery)){
          return TRUE;
      }
      else{
          PCMW_Logger::Debug('Cannot insert map to group ['.$strQuery.'] ['.__METHOD__.']',1);
          return FALSE;
      }
    }

    /**
    * given a map id and groupid, add it to the link list
    * @param $intMapId
    * @param $intGroupId
    * @return bool
    */
    function RemoveVendorFromGroup($intMapId,$intGroupId){
      $strQuery = 'DELETE FROM PC_mapgrouplink WHERE lvendorid = "'.$intMapId.'" AND lgroupid = "'.$intGroupId.'"';
      if($this->WPDB->query($strQuery)){
          return TRUE;
      }
      else{
          PCMW_Logger::Debug('Cannot delete from mapgroup ['.$strQuery.'] ['.__METHOD__.']',1);
          return FALSE;
      }
    }

    /**
    * given a groupid, delete all the links to this group
    * @param $intGroupId
    * @return bool
    */
    function RemoveAllGroupLinks($intGroupId){
      $strQuery = 'DELETE FROM PC_mapgrouplink WHERE lgroupid = "'.$intGroupId.'"';
      if($this->WPDB->query($strQuery)){
          return TRUE;
      }
      else{
          PCMW_Logger::Debug('Cannot delete from mapgroup ['.$strQuery.'] ['.__METHOD__.']',1);
          return FALSE;
      }
    }

    /**
    * given a groupid, get all of the maps listed
    * @param $intGroupId
    * @return array
    */
    function GetVendorsByGroup($intGroupId,$boolJoinVendors=FALSE){
      $strQuery = 'SELECT * FROM PC_mapgrouplink AS mgl';
      if($boolJoinVendors)
        $strQuery .= ' JOIN PC_vendors AS v ON v.vendorid = mgl.lvendorid';
      $strQuery .= ' WHERE mgl.lgroupid = "'.$intGroupId.'"';
      if($results = $this->WPDB->get_results($strQuery,ARRAY_A)){
          return $results;
      }
      else{
          PCMW_Logger::Debug('Cannot get mapgroup ['.$strQuery.'] ['.__METHOD__.']',1);
          return FALSE;
      }
    }
  #ENDREGION

  #REGION PCMW_FormDefinitions

    /**
    * given a definition group, get the definitions
    * @param int $intFormGroup group the definitions belong to
    * @param int $intAdminGroup  AdminGroup id of the form
    * @return resource
    */
    function GetFormDefinitions($intFormGroup=0,$intAdminGroup=0,$intMinimum=0){
       if($intFormGroup < 1)
        return FALSE;
       $strQuery = 'SELECT * FROM PC_formdefinitions AS fd JOIN PC_formgroups AS fg ON (fd.formgroup = fg.formid) WHERE fd.formgroup = "'.$intFormGroup.'"';
       if((int)$intAdminGroup > 0)
         $strQuery .= ' AND fg.admingroup = "'.$intAdminGroup.'"';
       if((int)$intMinimum > 0)
        $strQuery .= ' AND fd.formgroup > '.$intMinimum;
       $strQuery .= ' ORDER BY fd.formorder ASC';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($resResource= $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $resResource;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".' ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * given a definition group, get the definitions
    * @param int $intFormGroup group the definitions belong to
    * @param int $intClientId  AdminGroup id of the form
    * @return resource
    */
    function GetFormDefinitionsByAlias($strFormAlias,$intAdminGroup){
       if(trim($strFormAlias) == '' /*|| (int)$intAdminGroup < 1*/)
        return FALSE;
       $strQuery = 'SELECT * FROM PC_formdefinitions AS fd JOIN PC_formgroups AS fg ON (fd.formgroup = fg.formid) WHERE formid IN (';
       $strQuery .= ' SELECT * FROM (SELECT formid FROM PC_formgroups WHERE ';
       $strQuery .= ' (formalias = "'.$strFormAlias.'" AND admingroup <= "'.$intAdminGroup.'")';
       $strQuery .= ' OR (formalias = "'.$strFormAlias.'" AND admingroup = "0")';
       $strQuery .= ' ORDER BY formid DESC LIMIT 1) temp_tab)';
       $strQuery .= ' ORDER BY fd.formorder ASC';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($resResource= $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $resResource;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".' ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * given a definition id, get the associated data
    * @param int $intDefinitionId  client id of the form
    * @return resource
    */
    function GetDefinitionData($intDefinitionId){
       if($intDefinitionId < 1)
        return FALSE;
       $strQuery = 'SELECT * FROM PC_formdefinitions  WHERE definitionid = "'.$intDefinitionId.'"';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($resResource= $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $resResource;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".' ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * given a definition id, delete the record
    * @param int $intDefinitionId  definition id of the form
    * @return bool
    */
    function DeleteDefinitionData($intDefinitionId){
       if($intDefinitionId < 1)
        return FALSE;
       $strQuery = 'DELETE FROM PC_formdefinitions  WHERE definitionid = "'.$intDefinitionId.'"';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($this->WPDB->query($strQuery)){
         PCMW_Logger::Debug('ELEMENT DELETED Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
         return TRUE;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * given an object of definition data, insert it
    * @param object $objDefinition form element definition data
    * @return int last insert id
    */
    function InsertNewDefinition($objDefinition){
     $strQuery = 'INSERT INTO PC_formdefinitions(elementname,
                                              elementtitle,
                                              maxlength,
                                              elementtype,
                                              parentelementtype,
                                              elementclass,
                                              parentelementclass,
                                              elementvalidationid,
                                              defaultvaluesid,
                                              formorder,
                                              onclick,
                                              onchange,
                                              onkeyup,
                                              formgroup,
                                              elementattributes
                                              ) VALUES ("'.$this->safe($objDefinition->strDefinitionName).'",
                                                        "'.$this->safe($objDefinition->strElementTitle).'",
                                                        "'.$this->safe($objDefinition->intElementMax).'",
                                                        "'.$this->safe($objDefinition->strElementType).'",
                                                        "'.$this->safe($objDefinition->strElementParentType).'",
                                                        "'.$this->safe($objDefinition->strElementClass).'",
                                                        "'.$this->safe($objDefinition->strParentElementClass).'",
                                                        "'.$this->safe($objDefinition->intValidationId).'",
                                                        "'.$this->safe($objDefinition->strDefaultValues).'",
                                                        "'.$this->safe($objDefinition->intFormOrder).'",
                                                        "'.$this->safe($objDefinition->strOnclick).'",
                                                        "'.$this->safe($objDefinition->strOnChange).'",
                                                        "'.$this->safe($objDefinition->strOnKeyUp).'",
                                                        "'.$this->safe($objDefinition->intFormGroup).'",
                                                        "'.$this->safe($objDefinition->strElementAttributes).'")';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($this->WPDB->query($strQuery)){
           return $this->WPDB->insert_id;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }


    /**
    * given an object of definition data, update it
    * @param object $objDefinition form element definition data
    * @return bool
    */
    function UpdateDefinition($objDefinition){

    $strQuery = 'UPDATE PC_formdefinitions SET ';
    $strQuery .= 'elementname = "'.$this->safe($objDefinition->strDefinitionName).'",';
    $strQuery .= 'elementtitle = "'.$this->safe($objDefinition->strElementTitle).'",';
    $strQuery .= 'maxlength = "'.$this->safe($objDefinition->intElementMax).'",';
    $strQuery .= 'elementtype = "'.$this->safe($objDefinition->strElementType).'",';
    $strQuery .= 'parentelementtype = "'.$this->safe($objDefinition->strElementParentType).'",';
    $strQuery .= 'elementclass = "'.$this->safe($objDefinition->strElementClass).'",';
    $strQuery .= 'parentelementclass = "'.$this->safe($objDefinition->strParentElementClass).'",';
    $strQuery .= 'elementvalidationid = "'.$this->safe($objDefinition->intValidationId).'",';
    $strQuery .= 'defaultvaluesid = "'.$this->safe($objDefinition->strDefaultValues).'",';
    $strQuery .= 'formorder = "'.$this->safe($objDefinition->intFormOrder).'",';
    $strQuery .= 'onclick = "'.$this->safe($objDefinition->strOnclick).'",';
    $strQuery .= 'onchange = "'.$this->safe($objDefinition->strOnChange).'",';
    $strQuery .= 'onkeyup = "'.$this->safe($objDefinition->strOnKeyUp).'",';
    $strQuery .= 'elementattributes = "'.$this->safe($objDefinition->strElementAttributes).'"';
    $strQuery .= ' WHERE definitionid = "'.$this->safe($objDefinition->intDefinitionId).'"';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($this->WPDB->query($strQuery)){
           return TRUE;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * given a formid, get the associated data
    * @param int $intFormId  client id of the form
    * @return resource
    */
    function GetFormData($intFormId,$intStart=0){
       if($intFormId < 1){
        $strQuery = 'SELECT * FROM PC_formgroups';
        if((int) $intStart > 0)
            $strQuery .= ' WHERE formid >= '.$intStart;
       }
       else{
        $strQuery = 'SELECT * FROM PC_formgroups  WHERE formid = "'.$intFormId.'"';
       }
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($resResource= $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $resResource;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * given a formalias, get the associated data
    * @param $strFormAlias  alias id of the form
    * @param $intAdminGroup  admin group to get the group for
    * @return resource
    */
    function GetFormIdByAlias($strFormAlias,$intAdminGroup=0){
        $strQuery = 'SELECT formid FROM PC_formgroups WHERE formalias = "'.$strFormAlias.'"';
        if((int) $intAdminGroup > 0)
            $strQuery .= ' AND admingroup = '.$intAdminGroup;
        else
            $strQuery .= ' AND admingroup = 1';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($intFormId = $this->WPDB->get_var($strQuery)){
           return $intFormId;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * get the form groups for administrative purposes
    * @param int $intFormId  client id of the form
    * @return resource
    */
    function GetFormGroupAdminData(){
       $strQuery = 'SELECT * FROM PC_formgroups AS fg LEFT JOIN PC_admingroups AS ag on fg.admingroup = ag.admingroupid';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($resResource= $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $resResource;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * given a form synonym, get the associated data
    * @param string $strGroupAlias alias for the form in question
    * @param int $intAdminGroupId  adin group id of the form
    * @return resource
    */
    function GetFormDataByAlias($strGroupAlias,$intAdminGroupId){
       if(trim($strGroupAlias) == ""){
        PCMW_Logger::Debug('$strGroupAlias ['.$strGroupAlias.'] $intAdminGroupId ['.$intAdminGroupId.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
        return FALSE;
       }
       $strQuery = 'SELECT * FROM PC_formgroups  WHERE (admingroup <= "'.(int)$intAdminGroupId.'" AND formalias = "'.$strGroupAlias.'") OR (admingroup = "1" AND formalias = "'.$strGroupAlias.'") OR (admingroup IS NULL AND formalias = "'.$strGroupAlias.'") ORDER BY formid DESC LIMIT 1';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($resResource= $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $resResource;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * insert a new set of form data
    * as this only has one field for now, we will allow it to be a simple array
    * @param $objDefinitionGroup pertinent data to a form group
    * @return int Last insert ID
    */
    function InsertNewFormData($objDefinitionGroup){

     $strQuery = 'INSERT INTO PC_formgroups(formname,
                                         admingroup,
                                         formalias
                                         ) VALUES ("'.$this->safe($objDefinitionGroup->strFormName).'",
                                                   "'.$this->safe($objDefinitionGroup->intAdminGroup).'",
                                                   "'.$this->safe($objDefinitionGroup->strGroupAlias).'")';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($this->WPDB->query($strQuery)){
           return $this->WPDB->insert_id;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * update a set of form data
    * as this only has one field for now, we will allow it to be a simple array
    * @param $objDefinitionGroup pertinent data to a form group
    * @return int Last insert ID
    */
    function UpdateFormData($objDefinitionGroup){
    $strQuery = 'UPDATE PC_formgroups SET ';
    $strQuery .= 'formname = "'.$this->safe($objDefinitionGroup->strFormName).'",';
    $strQuery .= 'admingroup = "'.$this->safe($objDefinitionGroup->intAdminGroup).'",';
    $strQuery .= 'formalias = "'.$this->safe($objDefinitionGroup->strGroupAlias).'"';
    $strQuery .= ' WHERE formid = "'.$this->safe($objDefinitionGroup->intFormId).'"';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($this->WPDB->query($strQuery)){
           return TRUE;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }
  #ENDREGION

  #REGION PCMW_ADMIN GROUPS
    /**
    * get the available admin groups
    * @param int $intClientId unique client userid
    * @return resource
    */
    function GetAdminGroups($intClientId=0,$intAdminGroupId=0,$boolGetAll=FALSE){
       $strQuery = 'SELECT * FROM PC_admingroups AS ag';
       if($intClientId > 0)
        $strQuery .= ' LEFT JOIN PC_adminusers AS au ON ag.admingroupid = au.admingroup WHERE au.userid = "'.$intClientId.'"';
       else if($intAdminGroupId > 0)
        $strQuery .= ' LEFT JOIN PC_adminusers AS au ON ag.admingroupid = au.admingroup WHERE ag.admingroupid = "'.$intAdminGroupId.'"';
       else if($boolGetAll){
         //do nothing, since we'll get them all bac this way
       }
       else return FALSE;
       $strQuery .= ' ORDER BY ag.admingroupid';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1,1);
      if($resResource= $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $resResource;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * insert a user into the the admin group
    * @param $objAdminUser
    * @return last insert ID
    */
    function InsertAdminGroupUser($objAdminUser){
      $strQuery = 'INSERT INTO PC_adminusers(userid,
                                          handlerid,
                                          admingroup,
                                          status
                                          ) VALUES ("'.$this->safe($objAdminUser->intUserId).'",
                                                    "'.$this->safe($objAdminUser->intHandlerId).'",
                                                    "'.$this->safe($objAdminUser->intAdminGroupId).'",
                                                    "'.$this->safe($objAdminUser->intStatus).'")';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($this->WPDB->query($strQuery)){
           return $this->WPDB->insert_id;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * Update an admin user account
    * @param $objAdminUser
    * @return bool
    */
    function UpdateAdminUser($objAdminUser){
      $strQuery = 'UPDATE PC_adminusers SET ';
      $strQuery .= 'userid = "'.$this->safe($objAdminUser->intUserId).'",';
      $strQuery .= 'admingroup = "'.$this->safe($objAdminUser->intAdminGroupId).'",';
      $strQuery .= 'status = "'.$this->safe($objAdminUser->intStatus).'"';
      $strQuery .= ' WHERE adminid = "'.$this->safe($objAdminUser->intAdminUserId).'"';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
        if($this->WPDB->query($strQuery)){
           return TRUE;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }


    /**
    * insert a user into the the admin group
    * @param $objAdminGroup
    * @return last insert ID
    */
    function InsertAdminGroup($objAdminGroup){
      $strQuery = 'INSERT INTO PC_admingroups(admingroupid,
                                           groupname,
                                           groupstatus,
                                           clientid
                                           ) VALUES ("'.$this->safe($objAdminGroup->intAdminGroupId).'",
                                                     "'.$this->safe($objAdminGroup->strGroupName).'",
                                                     "'.$this->safe($objAdminGroup->intGroupStatus).'",
                                                     "'.$this->safe($objAdminGroup->intClientId).'")';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($this->WPDB->query($strQuery)){
           return $this->WPDB->insert_id;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * Update an admin user account
    * @param $objAdminGroup
    * @return bool
    */
    function UpdateAdminGroup($objAdminGroup){
      $strQuery = 'UPDATE PC_admingroups SET ';
      $strQuery .= 'groupname = "'.$this->safe($objAdminGroup->strGroupName).'",';
      $strQuery .= 'groupstatus = "'.$this->safe($objAdminGroup->intGroupStatus).'",';
      $strQuery .= ' WHERE admingroupid = "'.$this->safe($objAdminGroup->intAdminGroupId).'"';
        //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
        if($this->WPDB->query($strQuery)){
           return TRUE;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * given an admin user id, customerid, handler id or user id, get the admin
    * user
    * @param $intAdminUserId
    * @param $intUserId
    * @param $intHandlerId
    * @param $intCustomerId
    * @return resource || FALSE
    */
    function GetAdminUser($intAdminUserId=0,$intUserId=0,$intHandlerId=0,$intCustomerId=0){
       $strQuery = 'SELECT * FROM PC_adminusers AS au JOIN PC_admingroups AS ag ON au.admingroup = ag.admingroupid';
       if((int)$intAdminUserId > 0)
        $strQuery .= ' WHERE au.adminid='.$intAdminUserId;
       else if((int)$intUserId > 0)
        $strQuery .= ' WHERE au.userid='.$intUserId;
       else if((int)$intHandlerId > 0)
        $strQuery .= ' WHERE au.handlerid='.$intHandlerId;
       else if((int)$intCustomerId > 0)
        $strQuery .= ' WHERE au.customerid='.$intCustomerId;
       else{
         PCMW_Logger::Debug('Cannot get admin user. No IDs given.  METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
         return FALSE;
       }
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1,1);
      if($resResource= $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $resResource;
       }
       else{                                                                                          PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }

    }

  #ENDREGION



  #REGION STATICARRAYS

  /*
  @brief given an array keyword an possibly a foriegn id,
  @get the single layer index and value for an array
  @param $strArrayGroup,$intStudyId
  @return array()
  */
  function GetStaticArrayGroup($strArrayGroup,$intAdminGroup=0,$intClientId=0,$varMenuIndex='',$boolGroupByIndex=TRUE){
    $strQuery = 'SELECT * FROM PC_staticarrays WHERE (purpose = "'.$strArrayGroup.'" AND clientid = 0 AND foreignid = 0)';
    if((int)$intAdminGroup < 1 && (int)$intClientId < 1 && $varMenuIndex != ''){
        $strQuery .= ' AND menuindex = "'.$varMenuIndex.'" ';
    }
    if((int)$intAdminGroup > 0 && (int)$intClientId < 1){
        $strQuery .= ' OR (purpose = "'.$strArrayGroup.'" AND clientid = 0 AND foreignid = '.$intAdminGroup.')';
    }
    if((int)$intAdminGroup > 0 && (int)$intClientId > 0){
        $strQuery .= ' OR (purpose = "'.$strArrayGroup.'" AND clientid = '.$intClientId.' AND foreignid = '.$intAdminGroup.')';
    }
    if((int)$intAdminGroup < 1 && (int)$intClientId > 0){
        $strQuery .= ' OR (purpose = "'.$strArrayGroup.'" AND clientid = '.$intClientId.' AND foreignid = 0)';
    }
    if($varMenuIndex == '' && $boolGroupByIndex)
        $strQuery .= ' GROUP BY menuindex ORDER BY clientid DESC';
    //PCMW_Logger::Debug('Query ['.$strQuery.'] in '.__METHOD__.' LINE '.__LINE__,1);
    if($resResource = $this->WPDB->get_results($strQuery,ARRAY_A)){
       return $resResource;
    }
    else{
      PCMW_Logger::Debug('Query FAILED'."\r\n".' ['.$strQuery.'] in '.__METHOD__.' LINE '.__LINE__,1);
      return FALSE;
    }
  }

  #ENDREGION

  #REGION MAILBLAST
  /**
  * given a userid, subject, and message store a mailblast
  * @param $intUserId
  * @param $strSubject
  * @param $strMessage
  * @return bool
  */
  function StoreMailBlast($intUserId,$strSubject,$strMessage){

      $strQuery = 'INSERT INTO PC_mailblast(wpuser,
                                            mailsubject,
                                            mailmessage,
                                            blastdate
                                            ) VALUES ("'.$this->safe($intUserId).'",
                                                     "'.$this->safe($strSubject).'",
                                                     "'.$this->safe($strMessage).'",
                                                     "'.time().'")';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($this->WPDB->query($strQuery)){
           return $this->WPDB->insert_id;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
  }


    /**
    * get all mail blasts historically
    * @param $intMailBlastId
    * @return resource
    */
    function GetMailBlastHistory($intMailBlastId=0){
       $strQuery = 'SELECT * FROM PC_mailblast';
       if((int)$intMailBlastId > 0)
        $strQuery .= ' WHERE mailblastid = "'.$this->safe($intMailBlastId).'"';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($resResource= $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $resResource;
       }
       else{
         PCMW_Logger::Debug('Query FAILED'."\r\n".' ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * delete a mail blast by ID
    * @return resource
    */
    function DeleteMailBlast($intBlastId){
      if((int)$intBlastId < 1)
        return FALSE;
       $strQuery = 'DELETE FROM PC_mailblast WHERE mailblastid = "'.$intBlastId.'"';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      return $this->WPDB->query($strQuery);
    }

    /**
    * Update a mail blast entry
    * @param $intBlastId
    * @param $strSubject
    * @param $strMessage
    * @return bool
    */
    function UpdateMailBlast($intBlastId,$strSubject,$strMessage){
      $strQuery = 'UPDATE PC_mailblast SET ';
      $strQuery .= 'mailsubject = "'.$this->safe($strSubject).'",';
      $strQuery .= 'mailmessage = "'.$this->safe($strMessage).'"';
      $strQuery .= ' WHERE mailblastid = "'.$this->safe($intBlastId).'"';
        //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
        if($this->WPDB->query($strQuery)){
           return TRUE;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

  #ENDREGION

  #REGION 404REDIRECTS

    /**
    * insert a 404 redirect
    * @param $str404Page
    * @param $str404Redirect
    * @return last insert ID
    */
    function Insert404Redirect($str404Page,$str404Redirect){
      $strQuery = 'INSERT INTO PC_404redirects(404page,
                                               404redirect,
                                               lastupdate
                                               ) VALUES ("'.$this->safe($str404Page).'",
                                                         "'.$this->safe($str404Redirect).'",
                                                         "'.$this->safe(time()).'")';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($this->WPDB->query($strQuery)){
           return $this->WPDB->insert_id;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * Update a 404 redirect
    * @param $intRedirectId
    * @param $str404Page
    * @param $str404Redirect
    * @return bool
    */
    function Update404Redirect($intRedirectId,$str404Page,$str404Redirect){
      $strQuery = 'UPDATE PC_404redirects SET ';
      $strQuery .= '404page = "'.$this->safe($str404Page).'",';
      $strQuery .= '404redirect = "'.$this->safe($str404Redirect).'",';
      $strQuery .= 'lastupdate = "'.$this->safe(time()).'"';
      $strQuery .= ' WHERE redirectid = "'.$this->safe($intRedirectId).'"';
        //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
        if($this->WPDB->query($strQuery)){
           return TRUE;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }


    /**
    * delete a 404 redirect by ID
    * @return bool
    */
    function Delete404Redirect($intRedirectId){
       $strQuery = 'DELETE FROM PC_404redirects WHERE redirectid = "'.$intRedirectId.'"';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      return $this->WPDB->query($strQuery);
    }

    /**
    * get the redirects for session storage, or by single ID
    * @param int $intRedirectId unique record id ( optional )
    * @return array
    */
    function Get404Redirects($intRedirectId=0){
       $strQuery = 'SELECT * FROM PC_404redirects ';
       if((int)$intRedirectId > 0)
        $strQuery .= ' WHERE redirectid = '.$intRedirectId;
      PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($resResource= $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $resResource;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }
  #ENDREGION

  #REGION FORMRESULTS
    /**
    * insert a form submission result
    * @param $intFormId
    * @param $strFormData
    * @param $intAdminGroup
    * @param $intUserId
    * @return last insert ID
    */
    function InsertFormResults($intFormId,$strFormData,$intAdminGroup,$intUserId){
      $strQuery = 'INSERT INTO PC_formresults(formid,
                                              formdata,
                                              admingroup,
                                              userid,
                                              edate
                                               ) VALUES ("'.$this->safe($intFormId).'",
                                                         "'.$this->safe($strFormData).'",
                                                         "'.$this->safe($intAdminGroup).'",
                                                         "'.$this->safe($intUserId).'",
                                                         "'.$this->safe(time()).'")';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($this->WPDB->query($strQuery)){
           return $this->WPDB->insert_id;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * get the form results from a form id
    * @param $intFormId
    * @param $intStartDate
    * @param $intEndDate
    * @return results
    */
    function GetFormSubmissionResults($intFormId,$intStartDate=0,$intEndDate=0){
      $strQuery = 'SELECT * FROM PC_formresults WHERE formid = "'.$intFormId.'"';
       if((int)$intStartDate > 0)
        $strQuery .= ' AND edate > '.$intStartDate;
       if((int)$intEndDate > 0)
        $strQuery .= ' AND edat < '.$intEndDate;
      if($resResource= $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $resResource;
       }
       else{
         PCMW_Logger::Debug('Query FAILED'."\r\n".' ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }
  #ENDREGION

  #REGION VIDEOACCESS

    /**
    * insert a new video
    * @param $objVideo
    * @return last insert ID
    */
    function InsertVideo($objVideo){
      $strQuery = 'INSERT INTO PC_videoaccess(videotitle,
                                              videosource,
                                              videotype,
                                              accesslevel,
                                              cssclass,
                                              videoheight,
                                              videowidth,
                                              showcontrols,
                                              hidesource,
                                              addwatermark,
                                              udate
                                               ) VALUES ("'.$this->safe($objVideo->strVideoTitle).'",
                                                         "'.$this->safe($objVideo->strVideoSource).'",
                                                         "'.$this->safe($objVideo->strVideoType).'",
                                                         "'.$this->safe($objVideo->intAccessLevel).'",
                                                         "'.$this->safe($objVideo->strCSSClass).'",
                                                         "'.$this->safe($objVideo->intVideoHeight).'",
                                                         "'.$this->safe($objVideo->intVideoWidth).'",
                                                         "'.$this->safe($objVideo->intShowControls).'",
                                                         "'.$this->safe($objVideo->intHideSource).'",
                                                         "'.$this->safe($objVideo->intAddWatermark).'",
                                                         "'.$this->safe(time()).'")';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($this->WPDB->query($strQuery)){
           return $this->WPDB->insert_id;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * Update a video
    * @param $objVideo
    * @return bool
    */
    function UpdateVideo($objVideo){
      $strQuery = 'UPDATE PC_videoaccess SET ';
      $strQuery .= 'videotitle = "'.$this->safe($objVideo->strVideoTitle).'",';
      $strQuery .= 'videosource = "'.$this->safe($objVideo->strVideoSource).'",';
      $strQuery .= 'videotype = "'.$this->safe($objVideo->strVideoType).'",';
      $strQuery .= 'accesslevel = "'.$this->safe($objVideo->intAccessLevel).'",';
      $strQuery .= 'cssclass = "'.$this->safe($objVideo->strCSSClass).'",';
      $strQuery .= 'videoheight = "'.$this->safe($objVideo->intVideoHeight).'",';
      $strQuery .= 'videowidth = "'.$this->safe($objVideo->intVideoWidth).'",';
      $strQuery .= 'showcontrols = "'.$this->safe($objVideo->intShowControls).'",';
      $strQuery .= 'hidesource = "'.$this->safe($objVideo->intHideSource).'",';
      $strQuery .= 'addwatermark = "'.$this->safe($objVideo->intAddWatermark).'",';
      $strQuery .= 'udate = "'.$this->safe(time()).'"';
      $strQuery .= ' WHERE videoid = "'.$this->safe($objVideo->intVideoId).'"';
        //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
        if($this->WPDB->query($strQuery)){
           return TRUE;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * get a video
    * @param $intVideoId
    * @param $intAdminGroup
    * @return results
    */
    function GetVideo($intVideoId=0,$intAdminGroup=0){
      $strQuery = 'SELECT * FROM PC_videoaccess';
       if((int)$intVideoId > 0){
        $strQuery .= ' WHERE videoid = '.$intVideoId;
         if((int)$intAdminGroup > 0)
          $strQuery .= ' AND accesslevel <= '.$intAdminGroup;
         else{
         PCMW_Logger::Debug('$intAdminGroup ['.$intAdminGroup.'] ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
            return FALSE;//no valid video
         }
       }
       else{
          if((int)$intAdminGroup < PCMW_MODERATOR)
            return FALSE;
       }
      if($resResource = $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $resResource;
       }
       else{
         PCMW_Logger::Debug('Query FAILED'."\r\n".' ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * delete a video
    * @param $intVideoId
    * @return bool
    */
    function DeleteVideo($intVideoId){
       $strQuery = 'DELETE FROM PC_videoaccess WHERE videoid = "'.$intVideoId.'"';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      return $this->WPDB->query($strQuery);
    }
  #ENDREGION

  #REGION CHAT

    /**
    * insert a new video
    * @param $objVideo
    * @return last insert ID
    */
    function InsertChatSession($objSession){
      $strQuery = 'INSERT INTO PC_chatsession(userid,
                                              ownerid,
                                              previousowner,
                                              chattype,
                                              chataccess,
                                              chatstatus,
                                              updatealert,
                                              chatmeta,
                                              startdate,
                                              lastupdate
                                               ) VALUES ("'.$this->safe($objSession->intUserId).'",
                                                         "'.$this->safe($objSession->intOwnerId).'",
                                                         "'.$this->safe($objSession->intPreviousOwnerId).'",
                                                         "'.$this->safe($objSession->strChatType).'",
                                                         "'.$this->safe($objSession->intChatAccess).'",
                                                         "'.$this->safe($objSession->intStatus).'",
                                                         "'.$this->safe($objSession->intUpdateAlert).'",
                                                         "'.$this->safe($objSession->strChatMeta).'",
                                                         "'.$this->safe(time()).'",
                                                         "'.$this->safe(time()).'")';
      //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($this->WPDB->query($strQuery)){
           return $this->WPDB->insert_id;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * Update a chat session
    * @param $objChatSession
    * @return bool
    */
    function UpdateChatSession($objChatSession){
      $strQuery = 'UPDATE PC_chatsession SET ';
      $strQuery .= 'ownerid = "'.$this->safe($objChatSession->intOwnerId).'",';
      $strQuery .= 'previousowner = "'.$this->safe($objChatSession->intPreviousOwnerId).'",';
      $strQuery .= 'chatstatus = "'.$this->safe($objChatSession->intStatus).'",';
      $strQuery .= 'updatealert = "'.$this->safe($objChatSession->intUpdateAlert).'",';
      $strQuery .= 'chatmeta = "'.$this->safe($objChatSession->strChatMeta).'",';
      $strQuery .= 'enddate = "'.$this->safe($objChatSession->intEndDate).'",';
      $strQuery .= 'lastupdate = "'.$this->safe(time()).'"';
      $strQuery .= ' WHERE sessionid = "'.$this->safe($objChatSession->intChatSessionId).'"';
        //PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
        if($this->WPDB->query($strQuery)){
           return TRUE;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * insert a new video
    * @param $objVideo
    * @return last insert ID
    */
    function InsertChatMessage($objMessage){
      $strQuery = 'INSERT INTO PC_chatmessage(message,
                                              userid,
                                              attachments,
                                              sessionid,
                                              edate
                                               ) VALUES ("'.$this->safe($objMessage->strMessage).'",
                                                         "'.$this->safe($objMessage->intUserId).'",
                                                         "'.$this->safe($objMessage->strAttachments).'",
                                                         "'.$this->safe($objMessage->intChatSessionId).'",
                                                         "'.$this->safe(time()).'")';
      PCMW_Logger::Debug('Query ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($this->WPDB->query($strQuery)){
           return $this->WPDB->insert_id;
       }
       else{
           PCMW_Logger::Debug('Query FAILED'."\r\n".'  ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * get a chat session by ID
    * @param $intSessionId
    * @return results
    */
    function GetChatSessionOnly($intSessionId){
      $strQuery = 'SELECT * FROM PC_chatsession WHERE sessionid = "'.$intSessionId.'"';
      if($resResource= $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $resResource;
       }
       else{
         PCMW_Logger::Debug('Query FAILED'."\r\n".' ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * get a chat session by ID
    * @param $intSessionId
    * @return results
    */
    function GetSessionChat($intSessionId){
      $strQuery = 'SELECT *,cs.sessionid,cm.userid FROM PC_chatsession AS cs LEFT JOIN PC_chatmessage AS cm on cs.sessionid = cm.sessionid WHERE cs.sessionid = "'.$intSessionId.'" ORDER BY cm.edate DESC';
      if($resResource= $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $resResource;
       }
       else{
         PCMW_Logger::Debug('Query FAILED'."\r\n".' ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * get a chat session by ID
    * @param $intUserId
    * @return results
    */
    function GetUserSessions($intUserId){
      $strQuery = 'SELECT * FROM PC_chatsession WHERE (ownerid = "'.$intUserId.'" OR ownerid = 0) AND chatstatus < '.PCMW_CLOSED.' ORDER BY chatstatus,lastupdate DESC';
      if($resResource= $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $resResource;
       }
       else{
         PCMW_Logger::Debug('Query FAILED'."\r\n".' ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }

    /**
    * get a chat session by ID
    * @param $intUserId
    * @return results
    */
    function GetUserChat($intUserId){
      $strQuery = 'SELECT *,cs.sessionid,cm.userid FROM PC_chatsession AS cs LEFT JOIN PC_chatmessage AS cm on cs.sessionid = cm.sessionid WHERE cs.userid = "'.$intUserId.'" AND cs.chatstatus != '.PCMW_CLOSED.' ORDER BY cm.edate DESC';
      //PCMW_Logger::Debug('Query FAILED'."\r\n".' ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
      if($resResource= $this->WPDB->get_results($strQuery,ARRAY_A)){
           return $resResource;
       }
       else{
         PCMW_Logger::Debug('Query FAILED'."\r\n".' ['.$strQuery.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);
           return FALSE;
       }
    }
  #ENDREGION
}//end class
?>