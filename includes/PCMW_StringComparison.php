<?php
/**************************************************************************
* @ CLASS PCMW_StringComparison
* @brief given a string or array of values, compare it against an array of data
* @ REQUIRES:
*  None
*
**************************************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
class PCMW_StringComparison{

   //debugging
   var $boolDebugOn = FALSE;
   var $intDebugLevel = 1;
   var $strDebugMessage = '';

   public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_StringComparison();
		return( $inst );
  }

  function __construct(){
    //Start on instantiation
  }

  #REGION UTILITY METHODS
  //All general use functions specific to this class go here
  //general common use functions belong in PCMW_Database2.php

  /*
  @brief Get the methods available for debugging
  *keep this at the top of the class for easy access and update
  @return array()
  */
  function GetDebugMethods(){
    $arrDebugMethods = array();
    $arrDebugMethods['PCMW_StringComparison::MakeStringComparison'] = 1;
    return $arrDebugMethods;
  }

  #ENDREGION

  #REGION DATACALCULATION
  //All methods which handle specific data calculation and manipulation methods should go here
  /*
  @brief make the string comparisons based on divider data
    -- rules support || (or) + (&& (and)) as sub delimitation for multiple evaluations
    IE:  value=45||value<55||value!=35
  @param $strRule the string rule to evaluate (ie: $varParameter>=some_static_value)
  @param $varParameter value to be compared against in the rule as a string or an array
    -- if this is an array, the first value of the operator
       delimited string rule will be the array key name.
    -- if this is a string the first value of the operator delimited string is ignored.
  @param $boolBreakOnly return only the value to be evaluated
  @param $boolReturnOperator return the operator in the return value
  @return boolean
  */
  function MakeStringComparison($strRule,$varParameter,$boolBreakOnly=FALSE,$boolReturnOperator=FALSE){
     $boolRetVal = FALSE;
    //since 0 is evaluated as FALSE
        if($varParameter || $varParameter === 0){
            //we'll find out what the comparison is
            //return the result of the comparison
            if(strstr($strRule,"<") && !strstr($strRule,"<=")){
              if($boolBreakOnly){
                $arrParts = explode('<',$strRule);
                return $boolReturnOperator? '<'.$arrParts[1]:$arrParts[1];//'<'.$arrParts[1];
              }
              $boolRetVal = $this->CheckLessThanValue($strRule,$varParameter);
            }
            if(strstr($strRule,">") && !strstr($strRule,">=")){
              if($boolBreakOnly){
                $arrParts = explode('>',$strRule);
                return $boolReturnOperator? '>'.$arrParts[1]:$arrParts[1];//
              }
              $boolRetVal = $this->CheckGreaterThanValue($strRule,$varParameter);
            }
            if(strstr($strRule,"=") && !strstr($strRule,"!=") && !strstr($strRule,">=") && !strstr($strRule,"<=")){
              if($boolBreakOnly){
                $arrParts = explode('=',$strRule);
                return $boolReturnOperator? '='.$arrParts[1]:$arrParts[1];//'='.$arrParts[1];
              }
              $boolRetVal = $this->CheckEqualValue($strRule,$varParameter);
            }
            if(strstr($strRule,"~")){
              if($boolBreakOnly){
                $arrParts = explode('~',$strRule);
                return $boolReturnOperator? '~'.$arrParts[1]:$arrParts[1];//'~'.$arrParts[1];
              }
              $boolRetVal = $this->CheckStrStrValue($strRule,$varParameter);
            }
            if(strstr($strRule,"!=")){
              if($boolBreakOnly){
                $arrParts = explode('!=',$strRule);
                return $boolReturnOperator? '!='.$arrParts[1]:$arrParts[1];//'!='.$arrParts[1];
              }
              $boolRetVal = $this->CheckNotEqualValue($strRule,$varParameter);
            }
            if(strstr($strRule,">=")){
              if($boolBreakOnly){
                $arrParts = explode('>=',$strRule);
                return $boolReturnOperator? '>='.$arrParts[1]:$arrParts[1];//'>='.$arrParts[1];
              }
              $boolRetVal = $this->CheckEqualOrGreaterValue($strRule,$varParameter);
            }
            if(strstr($strRule,"<=")){
              if($boolBreakOnly){
                $arrParts = explode('<=',$strRule);
                return $boolReturnOperator? '<='.$arrParts[1]:$arrParts[1];//'<='.$arrParts[1];
              }
              $boolRetVal = $this->CheckEqualOrLessValue($strRule,$varParameter);
            }
        }
            //nothing matches, return FALSE
            return $boolRetVal;
  }


    //get the equal to "or"
    function  CheckEqualValue($strRule,$varValue){
      $boolReturn = FALSE;
      if(strstr($strRule,"||")){
        $strRuleOrs = explode("||",$strRule);
        foreach($strRuleOrs as $ka=>$va){
         $arrBreak = explode('=',$va);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''" || is_null($varValue[$arrBreak[0]]))? 'blank':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? 'blank':$arrBreak[1] ;
             if((string)$varValue[$arrBreak[0]] == (string)$arrBreak[1]){
               $boolReturn = TRUE;
             }
           }
           else{
             $varValue = ($varValue == "''" || is_null($varValue))? 'blank':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || is_null($varValue[$arrBreak[0]]))? 'blank':$arrBreak[1] ;
             if(trim((string)$varValue) == trim((string)$arrBreak[1])){
               $boolReturn = TRUE;
             }
           }
        }
      }
      else if(strstr($strRule,"+")){
        $strRuleOrs = explode("+",$strRule);
        $boolReturn = TRUE;
        foreach($strRuleOrs as $ka=>$va){
         $arrBreak = explode('=',$va);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''" || is_null($varValue[$arrBreak[0]]))? 'blank':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? 'blank':$arrBreak[1] ;
             if((string)$varValue[$arrBreak[0]] != (string)$arrBreak[1]){
               $boolReturn = FALSE;
             }
           }
           else{
             $varValue = ($varValue == "''" || is_null($varValue))? 'blank':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? 'blank':$arrBreak[1] ;
             if((string)$varValue != (string)$arrBreak[1]){
               $boolReturn = FALSE;
             }
           }
        }
      }
      else{
         $arrBreak = explode('=',trim($strRule));
           if(is_array($varValue)){
             $varValue[trim($arrBreak[0])] = (trim($varValue[trim($arrBreak[0])]) == "''" || is_null($varValue[$arrBreak[0]]))? 'blank':trim($varValue[trim($arrBreak[0])]) ;
             $arrBreak[1] = (trim($arrBreak[1]) == "''" || ($arrBreak[1] === 'null'))? 'blank':trim($arrBreak[1]) ;
//Debug::Debug_er('string)$varValue[$arrBreak[0]] = '.(string)$varValue[$arrBreak[0]].' (string)$arrBreak[1] = '.(string)$arrBreak[1] .' $arrBreak[0] ['.$arrBreak[0].'] METHOD ['.__METHOD__.'] '.__LINE__,1);
             if(trim((string)$varValue[trim($arrBreak[0])]) == trim((string)$arrBreak[1])){
               $boolReturn = TRUE;//$varValue;
             }
           }
           else{
             $varValue = ($varValue == "''" || is_null($varValue))? 'blank':trim($varValue) ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? 'blank':trim($arrBreak[1]) ;
             if((string)$varValue == (string)$arrBreak[1]){
               $boolReturn = TRUE;//$varValue;
             }
           }
      }
      return $boolReturn;
    }

    //get the less than to "or"
    function  CheckLessThanValue($strRule,$varValue){
      $boolReturn = FALSE;
      if(strstr($strRule,"||")){
        $strRuleOrs = explode("||",$strRule);
        foreach($strRuleOrs as $ka=>$va){
        $arrBreak = explode('<',$va);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
            if($varValue[$arrBreak[0]] < $arrBreak[1])
                 $boolReturn = TRUE;
           }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
            if($varValue < $arrBreak[1])
                 $boolReturn = TRUE;
           }
        }
      }else if(strstr($strRule,"+")){
        $strRuleOrs = explode("+",$strRule);
        $boolReturn = TRUE;
        foreach($strRuleOrs as $ka=>$va){
         $arrBreak = explode('<',$va);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue[$arrBreak[0]] >= $arrBreak[1]){
               $boolReturn = FALSE;
             }
           }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue >= $arrBreak[1]){
               $boolReturn = FALSE;
             }
           }
        }
      }
      else{
        $arrBreak = explode('<',$strRule);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
            if($varValue[$arrBreak[0]] < $arrBreak[1]){
               $boolReturn = TRUE;//$varValue;
            }
           }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
            if($varValue < $arrBreak[1]){
               $boolReturn = TRUE;//$varValue;
            }
           }
      }
      return $boolReturn;
    }

    //get the greater than to "or"
    function  CheckGreaterThanValue($strRule,$varValue){
      $boolReturn = FALSE;
      if(strstr($strRule,"||")){
        $strRuleOrs = explode("||",$strRule);
        foreach($strRuleOrs as $ka=>$va){
        $arrBreak = explode('>',$va);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
            if($varValue[$arrBreak[0]] > $arrBreak[1])
                 $boolReturn = TRUE;
            }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
            if($varValue > $arrBreak[1])
                 $boolReturn = TRUE;
           }
        }
      }else if(strstr($strRule,"+")){
        $strRuleOrs = explode("+",$strRule);
        $boolReturn = TRUE;
        foreach($strRuleOrs as $ka=>$va){
         $arrBreak = explode('>',$va);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue[$arrBreak[0]] <= $arrBreak[1]){
               $boolReturn = FALSE;
             }
           }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue <= $arrBreak[1]){
               $boolReturn = FALSE;
             }
           }
        }
      }
      else{
        $arrBreak = explode('>',$strRule);
           if(is_array($varValue)){
             @$varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
            if($varValue[$arrBreak[0]] > $arrBreak[1]){
              $boolReturn = TRUE;//$varValue;
            }
           }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
            if($varValue > $arrBreak[1]){
              $boolReturn = TRUE;//$varValue;
            }

           }
      }
      return $boolReturn;
    }

    //get the strstr to "or"
    function  CheckStrStrValue($strRule,$varValue){
      $boolReturn = FALSE;
      if(strstr($strRule,"||")){
        $strRuleOrs = explode("||",$strRule);
        foreach($strRuleOrs as $ka=>$va){
         $arrBreak = explode('~',$va);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if(stristr($arrBreak[1],$varValue[$arrBreak[0]]))
                 $boolReturn = TRUE;
           }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if(stristr($arrBreak[1],$varValue))
                 $boolReturn = TRUE;
           }
        }
      }else if(strstr($strRule,"+")){
        $strRuleOrs = explode("+",$strRule);
        $boolReturn = TRUE;
        foreach($strRuleOrs as $ka=>$va){
         $arrBreak = explode('~',$va);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if(!(stristr($arrBreak[1],$varValue[$arrBreak[0]]))){
               $boolReturn = FALSE;
             }
           }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if(!(stristr($arrBreak[1],$varValue))){
               $boolReturn = FALSE;
             }
           }
        }
      }
      else{
         $arrBreak = explode('~',$strRule);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if(stristr($arrBreak[1],$varValue[$arrBreak[0]])){
               $boolReturn = TRUE;//$varValue;
             }
           }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if(stristr($arrBreak[1],$varValue)){
               $boolReturn = TRUE;//$varValue;
             }
           }
      }
      return $boolReturn;
    }

    //get the not equal to "or"
    function  CheckNotEqualValue($strRule,$varValue){
      $boolReturn = FALSE;
      if(strstr($strRule,"||")){
        $strRuleOrs = explode("||",$strRule);
        foreach($strRuleOrs as $ka=>$va){
         $arrBreak = explode('!=',$va);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue[$arrBreak[0]] != $arrBreak[1])
                 $boolReturn = TRUE;
           }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue != $arrBreak[1])
                 $boolReturn = TRUE;
           }
        }
      }else if(strstr($strRule,"+")){
        $strRuleOrs = explode("+",$strRule);
        $boolReturn = TRUE;
        foreach($strRuleOrs as $ka=>$va){
         $arrBreak = explode('!=',$va);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue[$arrBreak[0]] == $arrBreak[1]){
               $boolReturn = FALSE;
             }
           }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue == $arrBreak[1]){
               $boolReturn = FALSE;
             }
           }
        }
      }
      else{
         $arrBreak = explode('!=',$strRule);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue[$arrBreak[0]] != $arrBreak[1]){
                $boolReturn = TRUE;//$varValue;
             }
           }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue != $arrBreak[1])
                $boolReturn = TRUE;//$varValue;
           }
      }
      return $boolReturn;
    }

    //get the equal or greater to "or"
    function  CheckEqualOrGreaterValue($strRule,$varValue){
      $boolReturn = FALSE;
      if(strstr($strRule,"||")){
        $strRuleOrs = explode("||",$strRule);
        foreach($strRuleOrs as $ka=>$va){
         $arrBreak = explode('>=',$va);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue[$arrBreak[0]] >= $arrBreak[1])
                 $boolReturn = TRUE;
            }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue >= $arrBreak[1])
                 $boolReturn = TRUE;
           }
        }
      }else if(strstr($strRule,"+")){
        $strRuleOrs = explode("+",$strRule);
        $boolReturn = TRUE;
        foreach($strRuleOrs as $ka=>$va){
         $arrBreak = explode('>=',$va);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue[$arrBreak[0]] < $arrBreak[1]){
               $boolReturn = FALSE;
             }
           }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue < $arrBreak[1]){
               $boolReturn = FALSE;
             }
           }
        }
      }
      else{
         $arrBreak = explode('>=',$strRule);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue[$arrBreak[0]] >= $arrBreak[1]){
                $boolReturn = TRUE;//$varValue;
             }
           }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue >= $arrBreak[1]){
                $boolReturn = TRUE;//$varValue;
             }
           }
      }
      return $boolReturn;
    }

    //get the equal to or less to "or"
    function  CheckEqualOrLessValue($strRule,$varValue){
      $boolReturn = FALSE;
      if(strstr($strRule,"||")){
        $strRuleOrs = explode("||",$strRule);
        foreach($strRuleOrs as $ka=>$va){
         $arrBreak = explode('<=',$va);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue[$arrBreak[0]] <= $arrBreak[1])
                 $boolReturn = TRUE;
           }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue <= $arrBreak[1]){
               $boolReturn = TRUE;
             }

           }
        }
      }else if(strstr($strRule,"+")){
        $strRuleOrs = explode("+",$strRule);
        $boolReturn = TRUE;
        foreach($strRuleOrs as $ka=>$va){
         $arrBreak = explode('<=',$va);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue[$arrBreak[0]] > $arrBreak[1]){
               $boolReturn = FALSE;
             }
           }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue > $arrBreak[1]){
               $boolReturn = FALSE;
             }
           }
        }
      }
      else{
         $arrBreak = explode('<=',$strRule);
           if(is_array($varValue)){
             $varValue[$arrBreak[0]] = ($varValue[$arrBreak[0]] == "''")? '':$varValue[$arrBreak[0]] ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue[$arrBreak[0]] <= $arrBreak[1]){
               $boolReturn = TRUE;
             }
           }
           else{
             $varValue = ($varValue == "''")? '':$varValue ;
             $arrBreak[1] = ($arrBreak[1] == "''" || ($arrBreak[1] === 'null'))? '':$arrBreak[1] ;
             if($varValue <= $arrBreak[1]){
               $boolReturn = TRUE;
             }
           }
      }
      return $boolReturn;
    }

  #ENDREGION


  #REGION DATABASECALLS
  //All functions which will interact with PCMW_Database.php should go in here
  #ENDREGION

  #REGION DEBUGGINGMETHODS

  /*
  @brief store and display debug information
  @param $strMessage,$boolLogNow
  @param boolean
  */
  function PCMW_LoadDebugLog($strMessage='',$boolLogNow=FALSE,$strMethod='',$boolShowBackTrace=FALSE){
  //$this->PCMW_LoadDebugLog('VALUE ['.$arrPOST .'-  LINE '.__LINE__."\r\n",FALSE,__METHOD__);
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