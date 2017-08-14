<?php
/**************************************************************************
* @CLASS PCMapRender
* @brief USE THIS TO CREATE NEW CLASSES FOR THE INCLUDES DIRECTORY.
* @REQUIRES:
*  -PCMW_Database.php
*
**************************************************************************/
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_GoogleMapAPIJSMin.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_GoogleMapAPICore.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'PCMW_GoogleMapHelper.php');
class PCMapRender{

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMapRender();
		return( $inst );
  }

  /**
  * Load and retunr a map
  * @param $strAddress
  * @param $strZoom
  * @return strfing (HTML)
  */
  function RenderMap($arrAddressParams){
    PCMW_GoogleMapHelper::Get()->boolLoadDirections = TRUE;
    PCMW_GoogleMapHelper::Get()->boolLoadElevationDirections = FALSE;
    PCMW_GoogleMapHelper::Get()->boolAddBikingDirections = FALSE;
    PCMW_GoogleMapHelper::Get()->boolAddSideBar = FALSE;
    PCMW_GoogleMapHelper::Get()->boolAddTrafficOverlay = FALSE;
    PCMW_GoogleMapHelper::Get()->boolUseClustering = FALSE;
    PCMW_GoogleMapHelper::Get()->boolUseCustomOverlay = FALSE;
    PCMW_GoogleMapHelper::Get()->intZoom = 16;//0 = zoomed out 100% 0,4, 8, 12, 16, 19
    PCMW_GoogleMapHelper::Get()->strWidth = '100%';
    PCMW_GoogleMapHelper::Get()->strHeight = '400px';
    PCMW_GoogleMapHelper::Get()->FillMarkerArray($arrAddressParams);
   /*
   * start loading our config options here
   */
    PCMW_GoogleMapHelper::Get()->strMapName = 'CustomMap';//name the map
    PCMW_GoogleMapHelper::Get()->strMapSideBarId = 'MyMapApp';
    PCMW_GoogleMapHelper::Get()->strDirectionsContainer = 'map_directions';
    PCMW_GoogleMapHelper::Get()->strSideBarType = 'BOX';
    PCMW_GoogleMapHelper::Get()->strMapControlsType = 'DEFAULT';
    PCMW_GoogleMapHelper::Get()->MakePreDefinedMap();
    /*
    * give back our HTML here
    */
    $strMap = PCMW_GoogleMapHelper::Get()->strHeadScript1;
    $strMap .= PCMW_GoogleMapHelper::Get()->strHeadScript2;
    $strMap .= PCMW_GoogleMapHelper::Get()->strBodyScript;
    $strMap .= PCMW_GoogleMapHelper::Get()->strBodyScript2;
    $strMap .= PCMW_GoogleMapHelper::Get()->strBodyScript3;
    $strMap .= '<div id="'.PCMW_GoogleMapHelper::Get()->strDirectionsContainer.'" ></div>';
    $strMap .= PCMW_GoogleMapHelper::Get()->strBodyScript1;
    return $strMap;
  }
}//end class
?>