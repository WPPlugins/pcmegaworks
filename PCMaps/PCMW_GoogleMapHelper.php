<?php
/***********************************************
* Google maps api helper class.
* @brief wrapper class to switch between maps and styles
* @requires
*   -PCMW_GoogleMapAPICore.php
*   -PCMW_GoogleMapAPIJSMin.php
*   -PCMW_Database.php
*   -PCMW_Abstraction.php
******************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
class PCMW_GoogleMapHelper{
  var $boolUseAddresses = FALSE;
  var $strHeadScript1 = '';
  var $strHeadScript2 = '';
  var $strBodyScript = '';
  var $strBodyScript1 = '';
  var $strBodyScript2 = '';
  var $strBodyScript3 = '';
  var $arrLocations = NULL;//hold the locations we are plotting
  var $strMapName = 'CustomMap';//name the map
  var $strMapSideBarId = 'MyMapApp';
  var $strDirectionsContainer = 'map_directions';//directions HTML element name
  var $strMapControlsType = 'DEFAULT';//accordingly - sets default option for type controls(DEFAULT, HORIZONTAL_BAR, DROPDOWN_MENU)
  var $strSideBarType = 'BOX';//LIST,BOX,CUSTOM - how to display the list
  var $strServerAddress = '';//where are we
  var $strDefaultMapIcon = 'http://progressivecoding.net/favicon.ico';//what is the defaut map icon?
  var $boolLoadDirections = FALSE;//load the directions at all?
  var $boolLoadElevationDirections = FALSE;//load the elevation directions?
  var $boolAddBikingDirections = FALSE;
  var $boolAddSideBar = FALSE;//turn sidebar on and off
  var $boolAddTrafficOverlay = FALSE;
  var $boolUseClustering = FALSE;//use clustering or just get nuts
  var $boolUseCustomOverlay = FALSE;
  var $intZoom = 3;
  var $intSize = "null";
  var $arrStyle = array();
  var $strWidth = '500px';
  var $strHeight = '500px';
  var $strCustomLayOverImage = "http://progressivecoding.net/images/flag.jpg";
  //cluster map, or multiple maps
  var $strDisplayType = 'single';//cluster or single

  //lets load the map objects
  var $objMapObject = null;
  public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_GoogleMapHelper();
		return( $inst );
  }


  function __construct(){
  //something
  if(defined (PCMW_LOGO))
    $this->strDefaultMapIcon = PCMW_LOGO;
  }

  //initialize the map object
  function InitializeMap(){
    $this->objMapObject = new GoogleMapAPI($this->strMapName,$this->strMapSideBarId);
    $this->objMapObject->_minify_js = isset($_REQUEST["min"])?FALSE:TRUE;
    //update the API defaults
    $this->objMapObject->width = $this->strWidth;
    $this->objMapObject->height = $this->strHeight;
    //do we show the sidebar?
    if(!$this->boolAddSideBar)
       $this->objMapObject->disableSidebar();
    //Show directions or just marker.
    if($this->boolLoadDirections){
    //load the directions based on what was posted
        $this->LoadDirections($this->strDirectionsContainer);
        //do we add biking directions
        if($this->boolAddBikingDirections)
          $this->objMapObject->enableBikingDirections();
        //do we want to add eleveation directions?
        if($this->boolLoadElevationDirections)
          $this->objMapObject->enableElevationDirections();
    }
    $this->objMapObject->type_controls_style = $this->strMapControlsType;
    if($this->boolAddTrafficOverlay)
        $this->objMapObject->enableTrafficOverlay();
    if(defined('DEFAULTMAPICON'))
       $this->strDefaultMapIcon = DEFAULTMAPICON;
    if(defined('PCMW_PLUGINADDRESS'))
       $this->strServerAddress = get_site_url().'/'.PCMW_PLUGINADDRESS;
    if($this->boolUseClustering){
      //Enable Marker Clustering
      $this->objMapObject->enableClustering();
      //Set options (passing nothing to set defaults, just demonstrating usage
      if($this->arrStyle != "null")
          $this->arrStyle = json_encode($this->arrStyle);
      $this->objMapObject->setClusterOptions($this->intZoom,$this->intSize,$this->arrStyle);
      $this->objMapObject->setClusterLocation($this->strServerAddress."js/markerclusterer_compiled.js");
    }
    return TRUE;
  }



function MakePreDefinedMap(){
    if(!$this->InitializeMap()){
        PCMW_Abstraction::Get()->ReportToUser(0,'Unable to load Map locations. Exiting. LINE ['.__LINE__.']');
        return FALSE;
    }
    if(!$this->AddMarkersBasedOnLocation()){
        PCMW_Abstraction::Get()->ReportToUser(0,'Unable to load Map locations. Exiting. LINE ['.__LINE__.']');
        return FALSE;
    }
    //Enable Marker Clustering
    if($this->boolUseCustomOverlay)
      $this->CustomOverlay();
      $this->strHeadScript1 = $this->objMapObject->getHeaderJS();
      if(!$this->strHeadScript2 = $this->objMapObject->getMapJS())
        PCMW_Abstraction::Get()->ReportToUser(0,'Unable to load Map Javascript. Exiting. LINE ['.__LINE__.']');
      $this->strBodyScript1 = $this->objMapObject->printOnLoad();
      $this->strBodyScript2 = $this->objMapObject->printMap();
      //$this->strBodyScript3 = $this->objMapObject->getSidebar();
      if($this->boolAddSideBar && $this->strSideBarType == 'LIST'){
         $this->PrepareSideBarStyle();
         $this->strBodyScript3 .= $this->objMapObject->getSidebar();
      }
      return TRUE;
}

function CustomOverlay(){
    //Show directions or just marker.
    $floatBaseLatitude = $this->arrLocations[0]['Lat'];
    $floatOverlayLatitude = ($floatBaseLatitude * ($this->intZoom / 40));
    $floatBaseLongitude = $this->arrLocations[0]['Lon'];
    $floatOverlayLongitude = ($floatBaseLongitude * ($this->intZoom / 150));
    $this->objMapObject->addOverlay(($floatBaseLatitude - $floatOverlayLatitude),
                                    ($floatBaseLongitude + $floatOverlayLongitude),
                                    ($floatBaseLatitude + $floatOverlayLatitude),
                                    ($floatBaseLongitude - $floatOverlayLongitude),
                                    $this->strCustomLayOverImage,
                                    25);
    $this->objMapObject->setCenterCoords($floatBaseLongitude,$floatBaseLatitude);
    $this->objMapObject->setZoomLevel($this->intZoom);
    return TRUE;
}


//we may need to load directions
function LoadDirections($strContainerId){
  //Get posted variables
      $ADDRESS = isset($_REQUEST["address"])?$_REQUEST["address"]:"";
      $TOFROM = isset($_REQUEST["tofrom"])?$_REQUEST["tofrom"]:"";
      $MARKER_LAT = isset($_REQUEST["lat"])?$_REQUEST["lat"]:0.0;
      $MARKER_LNG = isset($_REQUEST["lon"])?$_REQUEST["lon"]:0.0;
      if( $ADDRESS != "" ){
          if($TOFROM == "to"){
              $START_ADDRESS = $ADDRESS;
              $DESTINATION_ADDRESS = $MARKER_LAT.",".$MARKER_LNG;
          }else{
              $DESTINATION_ADDRESS = $ADDRESS;
              $START_ADDRESS = $MARKER_LAT.",".$MARKER_LNG;
          }

          $this->objMapObject->addDirections($START_ADDRESS, $DESTINATION_ADDRESS, $this->strDirectionsContainer,TRUE);
          $this->strBodyScript3 .= '<h2><a href=\''.$_SERVER['SCRIPT_SELF'].'\'>Remove directions</a></h2><br />';
      }
}


/**
 * Get the Style data to match our sidebar
 * @return bool
 */
 function PrepareSideBarStyle(){
    //load the map
    if($this->strSideBarType == 'CUSTOM')
        return FALSE;
      $this->strBodyScript3 .= '<style type="text/css">
        .mapsidebar_'.$this->strMapName.', #'.$this->strMapName.'{
            float:left;
        }
        .mapsidebar_'.$this->strMapName.'{
            width:100%;
            border:1px #000 solid;
            margin-left:5px;
        }
        .map_directions{
         color:#EC8C29;
         font-size:20px;
         line-height:28px;
         /*font-family: Georgia, "Times New Roman", Times, serif;*/
         font-weight:bold;
         padding:10px;
        }
        .map_directions ul{
         list-style:none;
        }
        .map_directions a{
         color:#FFFFFF;
         /*text-decoration:none;*/
        }

        .map_directions p{
         background-color:#0D0D0D;
         border:1px solid #070707;
        }
      </style>';
    return TRUE;
 }


 /**
 * given an address, get the GEO coordinates for a map
 * @param $strAddress
 * @return array(lat,lon)
 *   //$arrGeoCoords['lat'];
 *   //$arrGeoCoords['lon'];
 */
 function GetGeoCoordinates($strAddress){
  return $this->objMapObject->geoGetCoords($strAddress);
 }

 /**
 * Fill our markers with the loccations we loaded
 *@return bool
 */
 function AddMarkersBasedOnLocation(){
  if($this->boolAddSideBar){
    $this->PrepareSideBarStyle();
    $this->strBodyScript3 .= '<div class="mapsidebar_'.$this->strMapName.'">';
    $this->strBodyScript3 .= '<div class="'.$this->strDirectionsContainer.'"><ul style="display:inline;">';
  }
  foreach($this->arrLocations as $arrLocation){
    //lets make the html
    $strHTML = '';
   /* foreach($this->arrSchema['html']['column'] as $ka=>$va){
      if($ka == 'DateTime')
        $strHTML .= $va[0].date($va[1],$arrLocation[$va[2]]).'<br />';
      else
        $strHTML .= $ka.$arrLocation[$va].'<br />';
    } */
    if($this->boolLoadDirections)
        $strHTML .= $this->MakeEndPointDirections($arrLocation);
    //load our icon
    $arrLocation['vendoricon'] = (trim($arrLocation['vendoricon']) == '')? $this->strDefaultMapIcon : stripslashes($arrLocation['vendoricon']);
    if($this->boolUseAddresses || (float)$arrLocation['longitude'] == 0){
        //PCMW_Abstraction::Get()->AddUserMSG('['.trim($arrLocation['longitude']).']. Exiting. LINE ['.__LINE__.']',1);
      $strMarkerId = $this->objMapObject->addMarkerByAddress($arrLocation['vendoraddress'].' '.$arrLocation['vendorcity'].','.$arrLocation['vendorstate'].' '.$arrLocation['vendorzip'],
                                      $arrLocation['vendorname'],
                                      $strHTML,
                                      $arrLocation['vendordescription'],
                                      $arrLocation['vendoricon'],
                                      $arrLocation['vendoricon']);
    }
    else{
        //PCMW_Abstraction::Get()->AddUserMSG('[-'.(float)$arrLocation['longitude'].'-]. Exiting. LINE ['.__LINE__.']',1);
      $strMarkerId = $this->objMapObject->addMarkerByCoords($arrLocation['longitude'],
                                     $arrLocation['latitude'],
                                     $arrLocation['vendorname'],
                                     $strHTML,
                                     $arrLocation['vendordescription'],
                                     $arrLocation['vendoricon'],
                                     $arrLocation['vendoricon']);
    }
    if($this->boolAddSideBar)
        $this->AddChosenSideBar($strMarkerId,$arrLocation);

  }
  //close up the sidebar
  if($this->boolAddSideBar)
    $this->strBodyScript3 .= '</ul></div></div>';
  return TRUE;
 }


//add directions to our maps
function MakeEndPointDirections($arrLocation){
    $MARKER_HTML = $arrLocation['vendorname']."<br />";
    //do already have the coordinates?
    if(is_array($arrLocation) && sizeof($arrLocation) > 0 && !empty($arrLocation['latitude']) && !empty($arrLocation['longitude'])){
      $MARKER_LAT = $arrLocation['latitude'];
      $MARKER_LNG = $arrLocation['longitude'];
    }
    else if(array_key_exists('vendoraddress',$arrLocation) && !empty($arrLocation['vendoraddress'])){
      $arrGeoCoords = $this->objMapObject->geoGetCoords($arrLocation['vendoraddress'].' '.$arrLocation['vendorcity'].','.$arrLocation['vendorstate'].' '.$arrLocation['vendorzip']);
      $MARKER_LAT = $arrGeoCoords['lat'];
      $MARKER_LNG = $arrGeoCoords['lon'];
      $MARKER_HTML .= $arrLocation['vendoraddress'].'<br />'.$arrLocation['vendorcity'].','.$arrLocation['vendorstate'].' '.$arrLocation['vendorzip']."<br />";
    }
    else
        return FALSE;
      $MARKER_HTML .= "Need directions?";
      $MARKER_HTML .= "<form action='' method='post'>";
      $MARKER_HTML .= "Get directions";
      $MARKER_HTML .= "<select name='tofrom'>";
      $MARKER_HTML .= "<option value='to'>to here</option>";
      $MARKER_HTML .= "<option value='from'>from here</option>";
      $MARKER_HTML .= "</select><br />";
      $MARKER_HTML .= "Address:";
      $MARKER_HTML .= "<input type='hidden' name='lat' value='".$MARKER_LAT."' /><br />";
      $MARKER_HTML .= "<input type='hidden' name='lon' value='".$MARKER_LNG."' /><br />";
      $MARKER_HTML .= "<input type='text' name='address' /><br />";
      $MARKER_HTML .= "<input type='submit' value='Get Directions' />";
      $MARKER_HTML .= "</form>";
      return $MARKER_HTML;
}


/**
 * Load the sidebar based on type
  *@return boolean
 */
 function AddChosenSideBar($strMarkerId,$arrLocation){
   if($this->strSideBarType == 'BOX'){
     $this->strBodyScript3 .= $this->AddSideBarData($strMarkerId,$arrLocation);
   }
   if($this->strSideBarType == 'CUSTOM'){
   //our schema should be filled and have an HTML container to build on.
   //it will also need a STYLE declaration
     //$this->arrSideBarSchema
   }
   return TRUE;
 }


 /**
 * Load the side bar bullet list
 * @param $strMarkerId (string) the id of the marker we will refer to on the map for linking
 * @param $arrLocation (array) the location data to fill
 * @return bool
 */

function AddSideBarData($strMarkerId,$arrLocation){
    //create an id to be used for the marker opener <a>
    $opener_id = "opener_".$strMarkerId;
    //append <li> item to sidebar html
    $SIDEBAR_HTML = "
        <li id='".$opener_id."'>
            <a id=".str_replace(' ','',$arrLocation['vendorname']).$arrLocation['vendorid']."></a>
            <a href='#'>".$arrLocation['vendorname']."</a>
            <p>
                ".$arrLocation['vendoraddress'].' '.$arrLocation['vendorcity'].','.$arrLocation['vendorstate'].' '.$arrLocation['vendorzip'].'<br /><b>'.$arrLocation['vendordescription'].'</b>'.'<br />'.$arrLocation['vendortelephone']."
            </p>
        </li>
    ";
    //add marker opener id to map object
    $this->objMapObject->addMarkerOpener($strMarkerId, $opener_id);
    return $SIDEBAR_HTML;
}

//WE NEED TO GET ALL OF THE LOCATIONS
function FillMarkerArray($arrAddress = ''){
    if(defined('PCMW_VERSION')){
      if(array_key_exists('maptype',$arrAddress) && $arrAddress['maptype'] == 'all'){
        $this->arrLocations = PCMW_Database::Get()->GetAllVendors(TRUE);
        //$strResult = var_export($this->arrLocations,TRUE);
   //PCMW_Logger::Debug('$this->items ['.$strResult.'] METHOD ['.__METHOD__.'] LINE['.__LINE__.']',1);

        if($this->arrLocations && sizeof($this->arrLocations) > 0)
          return TRUE;
        PCMW_Abstraction::Get()->AddUserMSG('Unable to load Map Javascript. Exiting. LINE ['.__LINE__.']',1);
        return  FALSE;
     }
     else if(array_key_exists('groupid',$arrAddress)){
        $this->arrLocations = PCMW_Database::Get()->GetVendorsByGroup($arrAddress['groupid'],TRUE);                                
        //now we set the map defaults
        $arrGroupData = PCMW_Database::Get()->GetMapGroupById($arrAddress['groupid']);
        $arrSettings = PCMW_Utility::Get()->JSONDecode($arrGroupData[0]['groupsettings']);
        //load our settings
        $this->boolLoadDirections = $arrSettings['directions'];
        $this->boolLoadElevationDirections = $arrSettings['elevation'];
        $this->boolAddBikingDirections = $arrSettings['bikingdirections'];
        $this->boolAddSideBar = $arrSettings['showaddress'];
        $this->boolAddTrafficOverlay = $arrSettings['trafficoverlay'];
        $this->boolUseClustering = $arrSettings['clusterlocations'];
        $this->intZoom = $arrSettings['zoomlevel'];//0 = zoomed out 100% 0,4, 8, 12, 16, 19
        $this->strWidth = $arrSettings['width'].'px';
        $this->strHeight = $arrSettings['height'].'px';
     }
     else if(array_key_exists('maptype',$arrAddress) && $arrAddress['maptype'] == 'single'){
       $this->arrLocations = $this->BuildAddressArray($arrAddress);
       return TRUE;
     }
     else{
        PCMW_Abstraction::Get()->AddUserMSG('Unable to load Map Javascript. Exiting. LINE ['.__LINE__.']',1);
        return FALSE;
     }
   }
}

/**
*
build the address array
*/
  function BuildAddressArray($arrRawAddress){
      //echo '<pre>';
      //print_r($arrRawAddresses);
      //echo '</pre>';
    $arrCleanedAddresses = array();
      //we need to make this fit for the locator logic
       $arrLocatorPair = array();
       $arrLocatorPair['vendorname'] = stripslashes($arrRawAddress['title']);
       $arrLocatorPair['vendoraddress'] = stripslashes($arrRawAddress['address']).' ';
       $arrLocatorPair['vendoraddress'] .= stripslashes($arrRawAddress['city']).' ';
       $arrLocatorPair['vendoraddress'] .= stripslashes($arrRawAddress['state']).' ';
       $arrLocatorPair['vendoraddress'] .= stripslashes($arrRawAddress['zip']);
       $arrLocatorPair['vendorcity'] = stripslashes($arrRawAddress['city']).' ';
       $arrLocatorPair['vendorstate'] = stripslashes($arrRawAddress['state']).' ';
       $arrLocatorPair['vendorzip'] = stripslashes($arrRawAddress['zip']);
       $arrLocatorPair['vendoricon'] = (trim($arrRawAddress['vendoricon']) == '')? $this->strDefaultMapIcon : stripslashes($arrRawAddress['vendoricon']);
       $arrLocatorPair['vendordescription'] = stripslashes($arrRawAddress['description']);
       $arrLocatorPair['vendorwebsite'] = stripslashes($arrRawAddress['website']);
       $arrLocatorPair['vendortelephone'] = stripslashes($arrRawAddress['telephone']);
       $arrLocatorPair['latitude'] = stripslashes($arrRawAddress['latitude']);
       $arrLocatorPair['longitude'] = stripslashes($arrRawAddress['longitude']);
       $arrCleanedAddresses[] = $arrLocatorPair;
    return $arrCleanedAddresses;
  }

}//ends class
?>