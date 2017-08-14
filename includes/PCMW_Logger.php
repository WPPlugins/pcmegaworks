<?php
/*error_reporting (E_ALL ^ E_WARNING ^ E_PARSE ^ E_COMPILE_ERROR ^ E_NOTICE);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Logs'.DIRECTORY_SEPARATOR.'error.log');*/
/**
* Debug class, basically for handling of exceptions and user-error messages.
*/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once(dirname(__FILE__)  .DIRECTORY_SEPARATOR.'PCMW_Constants.php');
class PCMW_Logger
{

  public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_Logger();
		return( $inst );
   }
  /**
   * Does nothing, intentionally.
   *
   * @access public
   * @param  void
   * @return void
   */
  public function __construct() {} // end __construct()

  /**
   * Does nothing, intentionally.
   *
   * @access public
   * @param  void
   * @return void
   */
  public function __destruct() {} // end __destruct()


  	/**
	 * Given an error message and a debug level, perform the action on the message
	 * as indicated by the level.
	 *
	 * Valid debug levels (NOT PCMW_DEBUG_ARG):
	 *   0 = off
	 *   1 = write to log
	 *   2 = write to log and email
	 *   3 = email only
	 *   4 = all
	 *   5 = print to screen
	 *
	 * @access public
	 * @param  string $strError  Error Message
	 * @param  int $intDebugLevel       Debug Level
	 * @return void
	 */
	public static function Debug($strError ,$intDebugLevel, $boolBacKTrace = FALSE,$boolShowObjectMembers = FALSE){
      $strBackTrace = '';
      $strObjectMembers = '';
      $strError.= self::GetMemoryUsage();
      if($boolBacKTrace)
          $strBackTrace = self::FormBackTrace(FALSE);
      if($boolShowObjectMembers)
          $strObjectMembers = self::LoadObjectVariables($boolShowObjectMembers,FALSE);
      if((int)$intDebugLevel > 0){
  		if(defined('PCMW_DEBUG_ARG') && is_numeric(PCMW_DEBUG_ARG) &&  (int)PCMW_DEBUG_ARG < 1){
  			return;//if debugging is explicitly turned off
        }
          //replace line breaks for HTML
	    $patterns = Array();
	    $replacements = Array();
	    $patterns[0] = "/ /i";
	    $patterns[1] = "/\r\n/i";
	    $replacements[0] = '&nbsp;';
	    $replacements[1] = '<br />' . "\r\n";
	    // format for web display
	    $htmlerr = preg_replace($patterns, $replacements, $strError);
        if($boolBacKTrace)
           $strBackTrace = self::FormBackTrace(TRUE);
        if($boolShowObjectMembers)
           $strObjectMembers = self::LoadObjectVariables($boolShowObjectMembers,TRUE);
        if((int)$intDebugLevel == 1 || (int)$intDebugLevel == 2  || (int)$intDebugLevel == 4 ){
            //log it
			self::Debug_log($strBackTrace.$strError.$strObjectMembers);
        }
        if((int)$intDebugLevel == 2 || (int)$intDebugLevel == 3  || (int)$intDebugLevel == 4 ){
            //email it
			self::Send_Mail(PCMW_ADMIN,PCMW_SITENAME." Error ",$strBackTrace.$strError.$strObjectMembers);
        }
  		if(((int)$intDebugLevel == 5 || (int)$intDebugLevel == 4)){
          //print to screen
	      echo "<div style='color:#fff;background-color:#000;width:100%;height:200px;overflow:scroll;'>";
          echo $strBackTrace.'<br />';
          echo $htmlerr.'<br />';
          echo $strObjectMembers.'</div>';
  		}
      }
	} // end Debug_er()


    /**
    * gather the memory usage for this moment and append it to the log
    * @return string
    */
    public static function GetMemoryUsage(){
     $strUsage = "\r\n";
     $intPHPMemory = memory_get_usage(TRUE);
     //add memory usage
      $strUsage .= "memory_get_usage [";
     if ($intPHPMemory < 1024)
      $strUsage .= $intPHPMemory." Bytes";
     elseif ($intPHPMemory < 1048576)
      $strUsage .= round($intPHPMemory/1024,2)." KB";
     else
      $strUsage .= round($intPHPMemory/1048576,2)." MB";
     $strUsage .= "]\r\n";
     $strUsage .= "memory_get_peak_usage [";
     //peak memory
     $intPHPPeakMemory = memory_get_peak_usage (TRUE);
     if ($intPHPPeakMemory < 1024)
       $strUsage .= $intPHPPeakMemory." Bytes";
     elseif ($intPHPPeakMemory < 1048576)
       $strUsage .= round($intPHPPeakMemory/1024,2)." KB";
     else
       $strUsage .= round($intPHPPeakMemory/1048576,2)." MB";
     $strUsage .= "]\r\n";
     return $strUsage;
    }

        //form the backtrace
  public static function FormBackTrace($boolForDisplay = FALSE){
    $arrBackTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $strBreak = "\r\n";
    $strArguments = '';
    $strBackTrace = '';
    if($boolForDisplay)
      $strBreak = '<br />';
    foreach($arrBackTrace as $ka=>$va){
      $strBackTraceFile = 'File ['.$va['file'].']'.$strBreak;
      if(array_key_exists('args',$va) && is_array($va['args'])){
        foreach($va['args'] as $kb=>$vb){
          if(!is_object($vb))
            $strArguments .= '<pre>'.$vb.'</pre>,';
          else{
            $strObjectVariables = var_export($vb,TRUE);
            $strArguments .= '[OBJECT]'.$strBreak.'<pre>'.$strObjectVariables.'</pre>,'.$strBreak;
          }
        }
      }
      $strBackTrace .= 'Line ['.$va['line'].'] '.$va['class'].'->'.$va['function'].'('.$strArguments.')'.$strBreak;
    }
    return $strBackTraceFile.$strBackTrace;

  }

   public static function LoadObjectVariables($objShowObjectMembers,$boolForDisplay = TRUE){
      $strReturn = '';
        $strObjectVariables = var_export($objShowObjectMembers,TRUE);
      if($boolForDisplay){
        $strReturn .= '<pre>'.$strObjectVariables.'</pre>';
      }
      else{
        $strReturn = $strObjectVariables;
      }
      return $strReturn;
    }

    
  /**
   * Given an error string and a debug level, attempt to open the debug log and
   * append the error string with a timestamp. If the log cannot be written to,
   * send an email to the administrator notifying him of the problem and the
   * error message that was supposed to be written.
   *
   * @access public
   * @param  string $strError   Error Message
   * @return void
   */
  public static function Debug_log($strError)
  {
    // switched from m_d_Y to Y_m_d to match string cardinality with date progression
    $handle = (dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Logs'.DIRECTORY_SEPARATOR.'LOG_'.date('Y_m_d',time()).'.txt');
    $intFunctionCall = 0;
    $arrLastError = error_get_last();
    $strLastError = '';
    if(sizeof($arrLastError) > 0)
      $strLastError = var_export($arrLastError,TRUE);
    if(is_file($handle))
    {
      $fh = fopen($handle,'a');
      if($fh)
      {
        fwrite($fh,"\r\n----------------------[".date('r')."]:----------------------\r\n ".
                   "[".$_SERVER['SCRIPT_NAME']."]: " . $strError . "\r\n".
                   '$strLastError ['.$strLastError.']'." \r\n");
        fclose($fh);
      }
      else
      {
        $error = "An error has occurred, and the log cannot be written to.";
        if(self::Send_Mail(PCMW_ADMIN,"Query Error ",($error."<br />".$strError.'<br />$strLastError ['.$strLastError.']'." \r\n".$handle)))
          ; // do nothing on success
        else
          echo "Failure on Level 3".$error."<br />".$strError.'<br />'.$handle;
      }
    }
    else
    {
      $handle = (dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Logs'.DIRECTORY_SEPARATOR.'LOG_'.date('Y_m_d',time()).'.txt');
      $fh = fopen($handle,'a+');
      if($fh)
      {
        fwrite($fh,"\r\n----------------------[".date('r')."]:----------------------\r\n ".
                   "[" . $_SERVER['SCRIPT_NAME']."]: " .  $strError . "\r\n".
                   '$strLastError ['.$strLastError.']'." \r\n");
        fclose($fh);
      }
      else
      {
        //throw new exception('$strError ['.$strError.'] $handle ['.$handle.']');
        $error = "An error has occurred on server [".PCMW_SITENAME."], and the log cannot be written to.";
        if(self::Send_Mail(PCMW_ADMIN,"Query Error ".$intFunctionCall,($error."<br />".$er.'<br />$strLastError ['.$strLastError.']'." \r\n".$handle)))
          ; // do nothing on success
        else
          echo "Failure on Level 3 ".'$strLastError ['.$strLastError.']'." \r\n".$error."<br />".$er.'<br />'.$handle;
      }
    }
  } // end Debug_log()


  /**
   * Trivial e-mail function.
   * @todo get rid of both this e-mail function and the one in DB2, make into a proper class
   * 
   * @access public
   * @param  string $to     E-mail address of recipient
   * @param  string $sub    Subject of the e-mail
   * @param  string $mess   Message body
   * @return bool
   */
  public static function Send_Mail($to,$sub,$mess)
  {
    $headers  = 'MIME-Version: 1.0' . "\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
    $headers .= 'To: '.$to.' <'.$to.'>' . "\n";
    $headers .= 'From: '.PCMW_SITENAME.'< '.PCMW_SUPPORT.' >' . "\n";
    if(mail($to,$sub,$mess,$headers))
      return true;
    else
      return false;
  } // end Send_Mail()

} // end class Debug
?>