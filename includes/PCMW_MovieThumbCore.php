<?php
/**************************************************************************
* @CLASS MovieThumb
* @brief USE THIS TO CREATE NEW CLASSES FOR THE INCLUDES DIRECTORY.
* @REQUIRES:
*  -WatermarkCore.php
*
**************************************************************************/
class MovieThumb EXTENDS Watermark{
   //debugging
   var $boolDebugOn = TRUE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';
   var $boolMakeWatermark = TRUE;
   var $strFileName = '';

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new MovieThumb();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
    parent::__construct();
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
    $arrDebugMethods['MovieThumb::createMovieThumb'] = 1;
    $arrDebugMethods['Watermark::MakeWaterMark'] = 1;
    return $arrDebugMethods;
  }

  #ENDREGION

  #REGION DATACALCULATION
  //All methods which handle specific data calculation and manipulation methods should go here
    //copied from
    function createMovieThumb($srcFile, $destFile = "GreatNews.jpg")
    {
        $output = array();
        $cmd = sprintf('%sffmpeg.exe -i file:"%s" -an -ss 00:00:15 -r 1 -vframes 1 -y file:"%s"',FFMPEGPATH, $srcFile, $destFile.'/'.$this->strFileName);
        if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN'))
            $cmd = str_replace('/', DIRECTORY_SEPARATOR, $cmd);
        else
            $cmd = str_replace('\\', DIRECTORY_SEPARATOR, $cmd);
        $varReults = exec($cmd, $output, $retval);
        //$this->LoadDebugLog('$cmd ['.$cmd.']-  LINE '.__LINE__."\r\n",TRUE,__METHOD__);
        if ($retval){
        $strLastError = var_export(error_get_last(),TRUE);
        $this->LoadDebugLog('$retval ['.$retval.'] $strLastError ['.$strLastError.'] $varReults ['.$varReults.']-  LINE '.__LINE__."\r\n",TRUE,__METHOD__);
            return false;
        }
        if($this->boolMakeWatermark){
            $this->strImageSource = $destFile.'/'.$this->strFileName;
            $this->strNewImage = $destFile.'/'.$this->strFileName;
            $this->LoadDebugLog('$this->strImageSource ['.$this->strImageSource.']-  LINE '.__LINE__."\r\n",TRUE,__METHOD__);
            $this->MakeWaterMark();
        }
        else{
         $this->LoadDebugLog('Cannot make watermark ['.$destFile.'/'.$this->strFileName.']-  LINE '.__LINE__."\r\n",TRUE,__METHOD__);
        }
        return $this->strImageSource;
    }
  #ENDREGION


  #REGION DATABASECALLS
  //All functions which will interact with Database.php should go in here
  #ENDREGION

  #REGION DEBUGGINGMETHODS

  /*
  @brief store and display debug information
  @param $strMessage,$boolLogNow
  @param boolean
  */
  function LoadDebugLog($strMessage='',$boolLogNow=FALSE,$strMethod='',$boolShowBackTrace=FALSE){
  //$this->LoadDebugLog('VALUE ['.$arrPOST .']-  LINE '.__LINE__."\r\n",FALSE,__METHOD__);
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