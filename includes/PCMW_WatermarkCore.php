<?php
/**************************************************************************
* @CLASS Watermark
* @brief USE THIS TO CREATE NEW CLASSES FOR THE INCLUDES DIRECTORY.
* @REQUIRES:
*  -Database.php
*
**************************************************************************/
class Watermark{
   //debugging
   var $boolDebugOn = TRUE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';
   var $strImageSource = FALSE;
   var $strNewImage = FALSE;
   var $strImageType = 'png';//png,gif,jpg
   var $strWatermarkFileName = '';
   var $strWatermarkExtension = 'png';

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new Watermark();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
    $this->strWatermarkFileName = SERVERPATH.'\images\play-icon-614x460.png';
  }

  #REGION UTILITY METHODS
  //All general use functions specific to this class go here
  /**
  * verify we have the components needed
  * @return bool
  */
  function VerifyFileExists(){
    if(!$this->strNewImage || !$this->strImageSource){
       throw new exception('$this->strNewImage ['.$this->strNewImage.'] || $this->strImageSource ['.$this->strImageSource.'] cannot be FALSE in verification ['.__METHOD__.']');
        return FALSE;
    }
    else{
      if(!is_file($this->strImageSource)){
       throw new exception('$this->strImageSource ['.$this->strImageSource.'] is not a valid file. ['.__METHOD__.']');
        return FALSE;
      }
      else if(!is_file($this->strWatermarkFileName)){
       throw new exception('$this->strWatermarkFileName ['.$this->strWatermarkFileName.'] is not a valid file. ['.__METHOD__.']');
        return FALSE;
      }
      else return TRUE;
    }
    return TRUE;
  }

  /*
  @brief Get the methods available for debugging
  *keep this at the top of the class for easy access and update
  @return array()
  */
  function GetDebugMethods(){
    $arrDebugMethods = array();
    $arrDebugMethods['Watermark::MakeWaterMark'] = 1;
    return $arrDebugMethods;
  }

  #ENDREGION

  #REGION DATACALCULATION
  //All methods which handle specific data calculation and manipulation methods should go here
  /**
  * given an image file, add a watermark
  * @return bool
  */
  function MakeWaterMark($intHeight=400,$intWidth=450){
   $strThis = var_export($this,TRUE);
   $this->LoadDebugLog('$strThis ['.$strThis.']-  LINE '.__LINE__."\r\n",TRUE,__METHOD__);
   if(!$this->VerifyFileExists()){
    throw new exception('Cannot copy image ['.$this->strNewImage.'] ['.__LINE__.']. ['.__METHOD__.']');
    return FALSE;
   }
   // Load the stamp and the photo to apply the watermark to
    //make the watermark file
   if($this->strWatermarkExtension == 'jpg')
    $stamp = imagecreatefromjpeg($this->strWatermarkFileName);
   if($this->strWatermarkExtension == 'png')
    $stamp = imagecreatefrompng($this->strWatermarkFileName);
   if($this->strWatermarkExtension == 'gif')
    $stamp = imagecreatefromgif($this->strWatermarkFileName);
    $objImage = $this->MakeTransparentImage($intHeight,$intWidth);//imagecreatefrompng($this->strImageSource);
   if(!$stamp || !$objImage){
       throw new exception('$stamp or $objImage  is not a valid file. ['.__METHOD__.']');
        return FALSE;
      }
    // Set the margins for the stamp and get the height/width of the stamp image
    $dest_sx = imagesx($objImage);
    $dest_sy = imagesy($objImage);
    $src_sx = imagesx($stamp);
    $src_sy = imagesy($stamp);
    $marge_right = round((($dest_sx-$src_sx) / 2));
    $marge_bottom = round((($dest_sy-$src_sy) / 2));

    if(!imagecopy($objImage, $stamp, $marge_right, $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp))){
       $strError = var_export(error_get_last(),TRUE);
       throw new exception('Cannot copy image [<pre>'.$strError .'</pre>] ['.__LINE__.']. ['.__METHOD__.']');
        return FALSE;
    }

    // Save the image to file and free memory
    if(!imagepng($objImage, $this->strNewImage)){
       throw new exception('cannot create ['.$this->strNewImage.'] ['.__LINE__.'] ['.__METHOD__.']');
        return FALSE;
    }
    imagedestroy($objImage);
    return TRUE;
  }

  /**
  * make the image transparent
  * @return object
  */
  function MakeTransparentImage($intHeight=400,$intWidth=450){
   if($this->strImageType == 'jpg')
    $objImage = imagecreatefromjpeg($this->strImageSource);
   if($this->strImageType == 'png')
    $objImage = imagecreatefrompng($this->strImageSource);
   if($this->strImageType == 'gif')
    $objImage = imagecreatefromgif($this->strImageSource);
    $transparent_index = imagecolortransparent($objImage);
    if($transparent_index!=(-1))
        $transparent_color = imagecolorsforindex($objImage,$transparent_index);
        //(the new width $nw and height $nh must be defined before)
    $img_resized = imagecreatetruecolor( $intWidth, $intHeight );
    //simple check to find wether transparent color was set or not
    if(!empty($transparent_color)) {
        $transparent_new = imagecolorallocate( $img_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue'] );
        $transparent_new_index = imagecolortransparent( $img_resized, $transparent_new );
        //don't forget to fill the new image with the transparent color
        imagefill( $img_resized, 0,0, $transparent_new_index );
    }
    //resized copying and replacing the original image
    if( imagecopyresized( $img_resized, $objImage, 0,0, 0,0, $intWidth,$intHeight, imagesx($objImage),imagesy($objImage) ) ){
        imagedestroy($objImage);
        $objImage = $img_resized;
    }
    imagepng($objImage, $this->strImageSource);
    return imagecreatefrompng($this->strImageSource);
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