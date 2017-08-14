<?php
/*******************************************************
* @ brief PCMW_Utility class for all Basic reused functions
*  @param: takes no parameters
*  @Requires: None
*
****************************************************/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
class PCMW_Utility{
  public static function Get(){
		//==== instantiate or retrieve singleton ====
		static $inst = NULL;
		if( $inst == NULL )
			$inst = new PCMW_Utility();
		return( $inst );
   }

   public function __construct(){
    // construct here
   }


//make the states array
public static function MakeStatesArray(){
    $arrStates = array('AL'=>"Alabama",
			'AK'=>"Alaska",
			'AZ'=>"Arizona",
			'AR'=>"Arkansas",
			'CA'=>"California",
			'CO'=>"Colorado",
			'CT'=>"Connecticut",
			'DE'=>"Delaware",
			'DC'=>"District Of Columbia",
			'FL'=>"Florida",
			'GA'=>"Georgia",
			'HI'=>"Hawaii",
			'ID'=>"Idaho",
			'IL'=>"Illinois",
			'IN'=>"Indiana",
			'IA'=>"Iowa",
			'KS'=>"Kansas",
			'KY'=>"Kentucky",
			'LA'=>"Louisiana",
			'ME'=>"Maine",
			'MD'=>"Maryland",
			'MA'=>"Massachusetts",
			'MI'=>"Michigan",
			'MN'=>"Minnesota",
			'MS'=>"Mississippi",
			'MO'=>"Missouri",
			'MT'=>"Montana",
			'NE'=>"Nebraska",
			'NV'=>"Nevada",
			'NH'=>"New Hampshire",
			'NJ'=>"New Jersey",
			'NM'=>"New Mexico",
			'NY'=>"New York",
			'NC'=>"North Carolina",
			'ND'=>"North Dakota",
			'OH'=>"Ohio",
			'OK'=>"Oklahoma",
			'OR'=>"Oregon",
			'PA'=>"Pennsylvania",
			'RI'=>"Rhode Island",
			'SC'=>"South Carolina",
			'SD'=>"South Dakota",
			'TN'=>"Tennessee",
			'TX'=>"Texas",
			'UT'=>"Utah",
			'VT'=>"Vermont",
			'VA'=>"Virginia",
			'WA'=>"Washington",
			'WV'=>"West Virginia",
			'WI'=>"Wisconsin",
			'WY'=>"Wyoming");
    return $arrStates;
}

public static function MakeCountryArray(){
    //get countries http://snippets.dzone.com/posts/show/6623
    $arrCountries = array("GB" => "United Kingdom",
                      "US" => "United States",
                      "AF" => "Afghanistan",
                      "AL" => "Albania",
                      "DZ" => "Algeria",
                      "AS" => "American Samoa",
                      "AD" => "Andorra",
                      "AO" => "Angola",
                      "AI" => "Anguilla",
                      "AQ" => "Antarctica",
                      "AG" => "Antigua And Barbuda",
                      "AR" => "Argentina",
                      "AM" => "Armenia",
                      "AW" => "Aruba",
                      "AU" => "Australia",
                      "AT" => "Austria",
                      "AZ" => "Azerbaijan",
                      "BS" => "Bahamas",
                      "BH" => "Bahrain",
                      "BD" => "Bangladesh",
                      "BB" => "Barbados",
                      "BY" => "Belarus",
                      "BE" => "Belgium",
                      "BZ" => "Belize",
                      "BJ" => "Benin",
                      "BM" => "Bermuda",
                      "BT" => "Bhutan",
                      "BO" => "Bolivia",
                      "BA" => "Bosnia And Herzegowina",
                      "BW" => "Botswana",
                      "BV" => "Bouvet Island",
                      "BR" => "Brazil",
                      "IO" => "British Indian Ocean Territory",
                      "BN" => "Brunei Darussalam",
                      "BG" => "Bulgaria",
                      "BF" => "Burkina Faso",
                      "BI" => "Burundi",
                      "KH" => "Cambodia",
                      "CM" => "Cameroon",
                      "CA" => "Canada",
                      "CV" => "Cape Verde",
                      "KY" => "Cayman Islands",
                      "CF" => "Central African Republic",
                      "TD" => "Chad",
                      "CL" => "Chile",
                      "CN" => "China",
                      "CX" => "Christmas Island",
                      "CC" => "Cocos (Keeling) Islands",
                      "CO" => "Colombia",
                      "KM" => "Comoros",
                      "CG" => "Congo",
                      "CD" => "Congo, The Democratic Republic Of The",
                      "CK" => "Cook Islands",
                      "CR" => "Costa Rica",
                      "CI" => "Cote D'Ivoire",
                      "HR" => "Croatia (Local Name: Hrvatska)",
                      "CU" => "Cuba",
                      "CY" => "Cyprus",
                      "CZ" => "Czech Republic",
                      "DK" => "Denmark",
                      "DJ" => "Djibouti",
                      "DM" => "Dominica",
                      "DO" => "Dominican Republic",
                      "TP" => "East Timor",
                      "EC" => "Ecuador",
                      "EG" => "Egypt",
                      "SV" => "El Salvador",
                      "GQ" => "Equatorial Guinea",
                      "ER" => "Eritrea",
                      "EE" => "Estonia",
                      "ET" => "Ethiopia",
                      "FK" => "Falkland Islands (Malvinas)",
                      "FO" => "Faroe Islands",
                      "FJ" => "Fiji",
                      "FI" => "Finland",
                      "FR" => "France",
                      "FX" => "France, Metropolitan",
                      "GF" => "French Guiana",
                      "PF" => "French Polynesia",
                      "TF" => "French Southern Territories",
                      "GA" => "Gabon",
                      "GM" => "Gambia",
                      "GE" => "Georgia",
                      "DE" => "Germany",
                      "GH" => "Ghana",
                      "GI" => "Gibraltar",
                      "GR" => "Greece",
                      "GL" => "Greenland",
                      "GD" => "Grenada",
                      "GP" => "Guadeloupe",
                      "GU" => "Guam",
                      "GT" => "Guatemala",
                      "GN" => "Guinea",
                      "GW" => "Guinea-Bissau",
                      "GY" => "Guyana",
                      "HT" => "Haiti",
                      "HM" => "Heard And Mc Donald Islands",
                      "VA" => "Holy See (Vatican City State)",
                      "HN" => "Honduras",
                      "HK" => "Hong Kong",
                      "HU" => "Hungary",
                      "IS" => "Iceland",
                      "IN" => "India",
                      "ID" => "Indonesia",
                      "IR" => "Iran (Islamic Republic Of)",
                      "IQ" => "Iraq",
                      "IE" => "Ireland",
                      "IL" => "Israel",
                      "IT" => "Italy",
                      "JM" => "Jamaica",
                      "JP" => "Japan",
                      "JO" => "Jordan",
                      "KZ" => "Kazakhstan",
                      "KE" => "Kenya",
                      "KI" => "Kiribati",
                      "KP" => "Korea, Democratic People's Republic Of",
                      "KR" => "Korea, Republic Of",
                      "KW" => "Kuwait",
                      "KG" => "Kyrgyzstan",
                      "LA" => "Lao People's Democratic Republic",
                      "LV" => "Latvia",
                      "LB" => "Lebanon",
                      "LS" => "Lesotho",
                      "LR" => "Liberia",
                      "LY" => "Libyan Arab Jamahiriya",
                      "LI" => "Liechtenstein",
                      "LT" => "Lithuania",
                      "LU" => "Luxembourg",
                      "MO" => "Macau",
                      "MK" => "Macedonia, Former Yugoslav Republic Of",
                      "MG" => "Madagascar",
                      "MW" => "Malawi",
                      "MY" => "Malaysia",
                      "MV" => "Maldives",
                      "ML" => "Mali",
                      "MT" => "Malta",
                      "MH" => "Marshall Islands",
                      "MQ" => "Martinique",
                      "MR" => "Mauritania",
                      "MU" => "Mauritius",
                      "YT" => "Mayotte",
                      "MX" => "Mexico",
                      "FM" => "Micronesia, Federated States Of",
                      "MD" => "Moldova, Republic Of",
                      "MC" => "Monaco",
                      "MN" => "Mongolia",
                      "MS" => "Montserrat",
                      "MA" => "Morocco",
                      "MZ" => "Mozambique",
                      "MM" => "Myanmar",
                      "NA" => "Namibia",
                      "NR" => "Nauru",
                      "NP" => "Nepal",
                      "NL" => "Netherlands",
                      "AN" => "Netherlands Antilles",
                      "NC" => "New Caledonia",
                      "NZ" => "New Zealand",
                      "NI" => "Nicaragua",
                      "NE" => "Niger",
                      "NG" => "Nigeria",
                      "NU" => "Niue",
                      "NF" => "Norfolk Island",
                      "MP" => "Northern Mariana Islands",
                      "NO" => "Norway",
                      "OM" => "Oman",
                      "PK" => "Pakistan",
                      "PW" => "Palau",
                      "PA" => "Panama",
                      "PG" => "Papua New Guinea",
                      "PY" => "Paraguay",
                      "PE" => "Peru",
                      "PH" => "Philippines",
                      "PN" => "Pitcairn",
                      "PL" => "Poland",
                      "PT" => "Portugal",
                      "PR" => "Puerto Rico",
                      "QA" => "Qatar",
                      "RE" => "Reunion",
                      "RO" => "Romania",
                      "RU" => "Russian Federation",
                      "RW" => "Rwanda",
                      "KN" => "Saint Kitts And Nevis",
                      "LC" => "Saint Lucia",
                      "VC" => "Saint Vincent And The Grenadines",
                      "WS" => "Samoa",
                      "SM" => "San Marino",
                      "ST" => "Sao Tome And Principe",
                      "SA" => "Saudi Arabia",
                      "SN" => "Senegal",
                      "SC" => "Seychelles",
                      "SL" => "Sierra Leone",
                      "SG" => "Singapore",
                      "SK" => "Slovakia (Slovak Republic)",
                      "SI" => "Slovenia",
                      "SB" => "Solomon Islands",
                      "SO" => "Somalia",
                      "ZA" => "South Africa",
                      "GS" => "South Georgia, South Sandwich Islands",
                      "ES" => "Spain",
                      "LK" => "Sri Lanka",
                      "SH" => "St. Helena",
                      "PM" => "St. Pierre And Miquelon",
                      "SD" => "Sudan",
                      "SR" => "Suriname",
                      "SJ" => "Svalbard And Jan Mayen Islands",
                      "SZ" => "Swaziland",
                      "SE" => "Sweden",
                      "CH" => "Switzerland",
                      "SY" => "Syrian Arab Republic",
                      "TW" => "Taiwan",
                      "TJ" => "Tajikistan",
                      "TZ" => "Tanzania, United Republic Of",
                      "TH" => "Thailand",
                      "TG" => "Togo",
                      "TK" => "Tokelau",
                      "TO" => "Tonga",
                      "TT" => "Trinidad And Tobago",
                      "TN" => "Tunisia",
                      "TR" => "Turkey",
                      "TM" => "Turkmenistan",
                      "TC" => "Turks And Caicos Islands",
                      "TV" => "Tuvalu",
                      "UG" => "Uganda",
                      "UA" => "Ukraine",
                      "AE" => "United Arab Emirates",
                      "UM" => "United States Minor Outlying Islands",
                      "UY" => "Uruguay",
                      "UZ" => "Uzbekistan",
                      "VU" => "Vanuatu",
                      "VE" => "Venezuela",
                      "VN" => "Viet Nam",
                      "VG" => "Virgin Islands (British)",
                      "VI" => "Virgin Islands (U.S.)",
                      "WF" => "Wallis And Futuna Islands",
                      "EH" => "Western Sahara",
                      "YE" => "Yemen",
                      "YU" => "Yugoslavia",
                      "ZM" => "Zambia",
                      "ZW" => "Zimbabwe");
                      return $arrCountries;
}


   /**
   * Given an element name, $_POST, option values and optional attributes build a simple select box
   * @param $arrOptionValues
   * @param $arrPOST
   * @param $arrAttributes
   */
   function MakeSimpleSelectBox($arrOptionValues,$arrPOST,$arrAttributes){
     $strSelect = '<select name="'.$arrAttributes['selectname'].'" id="'.$arrAttributes['selectname'].'" onchange="'.@$arrAttributes['onchange'].'" class="'.@$arrAttributes['class'].'">';
     foreach($arrOptionValues as $varKey=>$varValue){
       //echo '$varKey ['.$varKey.'] $varValue ['.$varValue.']<br />';
       $strSelected = ($arrPOST[$arrAttributes['selectname']] == $varKey)? ' SELECTED ': '' ;
       $strSelect .= '<option '.$strSelected .' value="'.$varKey.'">'.$varValue.'</option>';
     }
     $strSelect .= '</select>';
     return $strSelect;
   }

   //Make a userlist html
   function MakeAlphabeticSelectionFilter($strPage,$strSelectedLetter='a',$strGet = 'letter',$intIsGet=0){
    $strLetterLinks = '';
    $strLetters = $this->LoadAlphabeticString();
    for($i=0;$i<strlen($strLetters);$i++){
      $strSelected = '';
      if($strLetters[$i] == $strSelectedLetter)
        $strSelected = ' class="selectedletter" ';
       if((int)$intIsGet === 0)
          $strLetterLinks ='<a href="javascript:this.form.'.$strGet.'.value='.$strLetters[$i].';this.form.submit();">'.$strLetters[$i].'</a> ';
       else if((int)$intIsGet == 1)//onclick function for letter
          $strLetterLinks ='<a href="javascript:'.$strGet.'(\''.$strSelectedLetter.'\',this.form);">'.$strLetters[$i].'</a> ';
       else
          $strLetterLinks = '<a href="'.$strPage.$strLetters[$i].'?'.$strGet.'='.$strLetters[$i].' '.$strSelected.'>'.$strLetters[$i].'</a> ';
    }
    return $strLetterLinks;
   }

   //get the directorys files, exclude subdirectory's
   function GetDirectoryContents($strDirectory,$boolExcludeSubDirectorys = TRUE,$boolDrill = FALSE){
     $arrFiles = array();
     if(is_dir($strDirectory)){
       $objDirectoryHandle = opendir($strDirectory);
       while (FALSE !== ($objFile = readdir($objDirectoryHandle))) {
        if ($objFile != "." && $objFile != ".."){
           if(is_dir($objFile) && !$boolExcludeSubDirectorys){
           //should we get recursive on this bitch?
             if($boolDrill){
               $arrSubDirectory = $this->GetDirectoryContents($objFile,$boolExcludeSubDirectorys,$boolDrill);
               //should we correct the path names?
               $arrFiles = array_merge($arrFiles,$arrSubDirectory);
             }
             //nah, lets just list is being there.
             else
                $arrFiles[$objFile] = $objFile;
           }
           //screwit, nothing but files.
           else{
             $arrFiles[$objFile] = $objFile;
           }
        }
       }
     }
     return $arrFiles;
   }

   //lets make a list of links from an array
   function MakeHyperlinksFromArray($arrSource,$strOrientation='horizontal',$boolUseKeyIndex=TRUE,$strTarget = '_self',$strPath=''){
      $strLinks = '<ul class="arraylinkscontainer">';
      if($strOrientation == 'horizontal')
        $strLinks .= '<li class="arraylinksparent">';
      foreach($arrSource as $ka=>$va){
        $strAddress = ($boolUseKeyIndex)? $ka: $va ;
        if($strOrientation == 'horizontal'){
            $strLinks .= '<a href="'.$strPath.$strAddress.'" target="'.$strTarget.'" class="arraylinks">'.$va.'</a>';
        }
        else{
            $strLinks .= '<li class="arraylinksparent">';
            $strLinks .= '<a href="'.$strPath.$strAddress.'" target="'.$strTarget.'"  class="arraylinks">';
            $strLinks .= $va;
            $strLinks .= '</a>';
            $strLinks .= '</li>';
        }
      }
      if($strOrientation == 'horizontal')
        $strLinks .= '</li>';
      $strLinks .= '</ul>';
      return $strLinks;
   }

   //load the week array
    function LoadWeekArray($boolAllowAll = FALSE){
       $arrWeeks = array();
       if($boolAllowAll)
        $arrWeeks[52]='All';
       for($i=0;$i<52;$i++)
        $arrWeeks[$i] = $i;
       return $arrWeeks;
    }


    //load the day array
    function LoadDayArray($boolAllowAll = FALSE){
       $arrDays = array();
       if($boolAllowAll)
        $arrDays[7]='All';
        $intTime =  strtotime(date('D',time()));
       for($i=0;$i<7;$i++){
          $arrDays[$i] = strftime('%A',$intTime);
          $intTime = strtotime('+1 day',$intTime);
       }
       return $arrDays;
    }

    //load the months array
    function LoadMonthArray($boolAllowAll = FALSE){
       $arrMonths = array();
          if($boolAllowAll)
        $arrMonths[12]='All';
        $intTime =  strtotime('January');
        for($i=0;$i<12;$i++){
          $arrMonths[$i] = strftime('%B',$intTime);
          $intTime = strtotime('+1 month',$intTime);
        }
        return $arrMonths;
    }

    //load the hours array
    static function LoadHourArray($boolHourly = FALSE,$intValidTime=1){
       $arrHours = array();
       if($boolHourly)
        $arrHours['hour'] = 'Hourly';
       for($rf=$intValidTime;$rf<13;$rf++){
          $at = ($rf < 10)? '0'.$rf:$rf;
          $arrHours[$at] = $at;
        }
       return $arrHours;
    }

    //load the minutes array
    static function LoadMinuteArray($boolAllowAll = FALSE){
       $arrMinutes = array();
        if($boolAllowAll){
          for($rp=0;$rp<60;$rp++){
            $at = ($rp < 10)? '0'.$rp:$rp;
            $arrMinutes[$at] = $at;
          }
        }
        else{
          for($rp=0;$rp<4;$rp++){
            $at = (($rp * 15) < 10)? '0'.($rp * 15):($rp * 15);
            $arrMinutes[(string)$at] = $at;
          }
        }
       return $arrMinutes;
    }

    //load the meridian array
    static function LoadMeridianArray(){
      $arrMeridian = array();
      $arrMeridian['AM'] = 'A.M.';
      $arrMeridian['PM'] = 'P.M.';
      return $arrMeridian;
    }

    //maker a singular value array
    function MakeSingularIndexArray($arrData){
       $arrReturnOptions = '';
       foreach($arrData as $ka=>$va){
          $arrReturnOptions[$ka]= $ka;
       }
       return $arrReturnOptions;
    }

    //filter an array BY an array
    function FilterArrayByArrayIndexes($arrNeedles,$arrHaystack){
       $arrFilteredArray = array();
       foreach($arrHaystack as $ka=>$va){
         if(array_key_exists($ka,$arrNeedles))
            $arrFilteredArray[$ka] = $ka;
       }
       return $arrFilteredArray;
    }

    //we need to remove redundant entires from an array
    function RemoveRedundantData($arrExistingArray, $arrDifferentialArray){
      $arrResultArray = array();
      foreach($arrDifferentialArray as $ka=>$va){
        if(!array_key_exists($ka, $arrExistingArray))
            $arrResultArray[$ka] = $va;
      }
      return $arrResultArray;
    }


    //we need to make an name indexed value array for select boxes
    public static function MakeNameValueArray($strValueColumnName,$strTextColumnName,$arrSource,$arrFirstOption=array()){
       $arrValues = $arrFirstOption;
       if(sizeof($arrSource) > 0){
        foreach($arrSource as $ka=>$va){
           $arrValues[$va[$strValueColumnName]] = $va[$strTextColumnName];
        }
       }
       return $arrValues;
    }


    //we need to make an name indexed value array for select boxes
    //this one contains multiple return values
    function MakeThreeNameValueArray($strValueColumnName,$strTextColumnName1,$strTextColumnName2,$strTextColumnName3,$arrSource, $boolAll = FALSE){
       $arrValues = array();
       if($boolAll){
         if(is_array($boolAll))
          $arrValues[$boolAll[0]] = $boolAll[1];
         else
          $arrValues['all'] = 'All';
       }
       if(sizeof($arrSource) > 0){
        foreach($arrSource as $ka=>$va){
           if($boolAll && $va[$strValueColumnName] < 1)
            $arrValues['all'] = 'All';
           else
           $arrValues[$va[$strValueColumnName]] = $va[$strTextColumnName1].' '.
                                                  $va[$strTextColumnName2].' '.
                                                  $va[$strTextColumnName3];
        }
       }
       return $arrValues;
    }

    /**
    * given a reference array and a data array find the keys in the references
    * array to extract the values in the data array
    * @param array $arrReferenceArray array of key to filter the data array with
    * @param array $arrDataArray array of data elements to extract
    * @param array $arrInitialArray in the event we ahve a starting array
    * @return array
    */
    function BuildArrayFromReference($arrReferenceArray,$arrDataArray,$arrInitialArray=array()){
      foreach($arrReferenceArray as $ka=>$va){
        if(array_key_exists($ka,$arrDataArray))
          $arrInitialArray[$ka] = $arrDataArray[$ka];
      }
      return $arrInitialArray;
    }

    //make an array from a larger array.
    //used to pick one column or index for use with select box creation.
    function Striparray($index,$parentarray){
       $retarray = array();//return the filled array
       foreach($parentarray as $ka=>$va){
         foreach($va as $kb=>$vb){
           if(trim($kb) == trim($index))
              $retarray[] = $vb;
           }
       }
       return $retarray;
    }

    //we need to arrange a special array with one value and multiple keys
    function MakeSpecialArray($arrSourceArray, $strColumnValueName,$strFormat, $boolMatchAll = FALSE){
     $arrReturnArray = array();
     foreach($arrSourceArray as $ka=>$va){
       //create a duplicate for modification
       $strTempFormat = $this->SwapVariablePlaceKeepers($strFormat, $va, $boolMatchAll);
       $strTempValue = $this->SwapVariablePlaceKeepers($strColumnValueName, $va, $boolMatchAll);
       //fill the array accordingly
       if($strTempValue != "")
         $arrReturnArray[$strTempFormat] = $strTempValue;
       else{
       }
     }
     return $arrReturnArray;
    }

    //we need to make a swap of values in a string
    function SwapVariablePlaceKeepers($strFormat, $arrValues, $boolMatchAll = FALSE){
      //remoave all odd chars so it can be exploded and iterated through
       $strCleanFormat = preg_replace("/[^a-zA-Z0-9\s]/", "-", $strFormat );
       $arrMatchValues = explode("-",$strCleanFormat);
       //go through the keys and replace them with the correct values
       if(sizeof($arrMatchValues) > 1){
         foreach($arrMatchValues as $ka=>$va){
           if($boolMatchAll && $arrValues[$va] == "")
            return '';
           else
           $strFormat = str_replace($va,$arrValues[$va],$strFormat);
         }
       }
       else{
          if($boolMatchAll && $arrValues[$strFormat] == "")
            return '';
           else
          $strFormat = str_replace($strFormat,$arrValues[$strFormat],$strFormat);
       }
       return $strFormat;
    }


    //claen special characters from a string indexed array
    function RemoveCharsFromArray($arrArray, $strIndex, $arrCharacter, $strReplace = ""){
      $arrReturn = $arrArray;
      foreach($arrArray as $ka=>$va){
        $strFixed = $va[$strIndex];
        foreach($arrCharacter as $kb=>$vb)
            $strFixed = str_replace($vb, $strReplace, $strFixed);
        $arrReturn[$ka][$strIndex] = $strFixed;
      }
      return $arrReturn;
    }


    //clean special characters from an intger indexed array
    function RemoveAnonymousCharsFromArray($arrArray, $arrCharacter, $strReplace = ""){
      $arrReturn = $arrArray;
      foreach($arrArray as $ka=>$va){
        foreach($arrCharacter as $kb=>$vb){
            $strFixedValue = str_replace($vb, $strReplace, $va);
            $strFixedText = str_replace($vb, $strReplace, $ka);
        }
        $arrReturn[$strIndex] = $strFixed;
      }
      return $arrReturn;
    }

    //we need to remove empty entries from an array
    function RemoveEmptyEntries($arrEntryArray,$varArrayColumn){
      $arrCleanedArray = array();
      foreach($arrEntryArray as $ka=>$va){
        if(trim($va[$varArrayColumn]) != "")
            $arrCleanedArray[] = $va;
      }
      return $arrCleanedArray;
    }

     //need to find an array match based on a key within the array
    //return an array
    function GetArrayMatch($arrSource, $varKey, $varValue,$boolToLower=FALSE,$boolGetAll=FALSE){
      $arrMatch = array();
        if(sizeof($arrSource) > 0){
           foreach($arrSource as $ka=>$va){
             if($boolToLower)
               $va[$varKey] = mb_strtolower($va[$varKey]);//changed to mb_strtolower to allow for char comparisons
               //PCMW_Logger::Debug('$va[$varKey] = ['.$va[$varKey].'] $varValue = ['.$varValue.']',1);
             if($va[$varKey] == $varValue){
               if($boolGetAll)
                $arrMatch[] = $va;
               else
                return $va;
             }
           }
        }
        return $arrMatch;
    }

  //get a single specific array value from an array
  //in this case we know the value we expect to get back
  function GetSpecificValue($arrArray,$mixKey,$mixValue,$boolGetLast = FALSE){
   $arrMatch = array();
    foreach($arrArray as $ka=>$va){
      foreach($va as $kb=>$vb){
        if($mixKey == $kb && $mixValue == $vb){
           if(!$boolGetLast)
             return $va;
           $arrMatch = $va;
        }
      }
    }
    return $arrMatch;
  }

  //we need the value based on a key match
  function GetSpecificKeyMatch($arrArray,$mixKey,$boolGetLast = FALSE){
   $arrMatch = array();
    foreach($arrArray as $ka=>$va){
      foreach($va as $kb=>$vb){
        if($mixKey == $kb){
           if(!$boolGetLast)
             return $va;
             //let's get a truthy deliniation
           else if($boolGetLast == 2)
             $arrMatch[] = $va;
           else
             $arrMatch = $va;
        }
      }
    }
    return $arrMatch;
  }

  //create my own array search function since in_fucking_array doesn't work on forwardslash dates
  function IsInArray(&$arrArray, $varValue){
     foreach($arrArray as $ka=>$va){
        if(trim($va) == trim($varValue))
            return TRUE;
     }
     return FALSE;
  }

  //trim the length of a single length array
  function TrimStringFat($arrArray,$intLength = 85,$intOffset = 0){
    $arrReformedArray = array();
    foreach($arrArray as $ka=>$va){
      $arrReformedArray[$ka] = substr($va,$intOffset,$intLength);
    }
    return $arrReformedArray;
  }


  //replace a substring with "X" amountof unkowns in front or behind it
  function ReplaceSubString($strInitialString, $strPlaceThis, $strWithThis=''){
  //find it
    $intSubStringStart = (strpos($strInitialString,$strPlaceThis));
    $strSubString = substr($strInitialString,$intSubStringStart,strlen($strWithThis));
    //replace it with this
    $strInitialString = str_replace($strSubString,$strWithThis,$strInitialString);
    return $strInitialString;
  }

  //other merge methods screw the pooch, lets just do this instead
  function CombineTwoMatricedArrays($arrArray1,$arrArray2){
    foreach($arrArray2 as $ka=>$va)
        $arrArray1[] = $va;
  return $arrArray1;
  }

   //other merge methods screw the pooch, lets just do this instead
  function MergeArrays($arrArray1,$arrArray2,$boolOverRide = TRUE){
    foreach($arrArray2 as $ka=>$va){
        if((array_key_exists($ka,$arrArray1) && $boolOverRide) || !array_key_exists($ka,$arrArray1))
            $arrArray1[$ka] = $va;
    }
  return $arrArray1;
  }

  //lets fix a calendar date//not sure if this exists somewhere else, but for now this will have to do.
  function FixCalendarDateForInsert($strDate,$intTimeZone=0,$boolShowTime=TRUE,$boolTwelveHour=FALSE,$boolUseSeconds=FALSE){
     $strTimeZone = 'UTC';
     if($intTimeZone > 0){
      $strTimeZone = $this->GetTimeZoneData($intTimeZone,TRUE);
     }
     $objTimeZone = new DateTimeZone($strTimeZone);
     //
     //$objDate = DateTime::createFromFormat('m-d-Y h:i:s A', $strDate,$objTimeZone);
     if($boolShowTime){
       if($boolTwelveHour){
        $strSeconds = ($boolUseSeconds)? ':s':'' ;
        $objDate = DateTime::createFromFormat('m-d-Y h:i'.$strSeconds.' A', $strDate,$objTimeZone);
        return date_format($objDate,'Y-m-d h:i'.$strSeconds.' A');
       }
       else{
        $strSeconds = ($boolUseSeconds)? ':s':'' ;
        $objDate = DateTime::createFromFormat('m-d-Y H:i'.$strSeconds, $strDate,$objTimeZone);
        return date_format($objDate,'Y-m-d H:i'.$strSeconds);
       }
     }
     else{
        $objDate = DateTime::createFromFormat('m-d-Y', $strDate,$objTimeZone);
        return date_format($objDate,'Y-m-d');
     }
  }

 function ConvertStringDateToInteger($strDate){
    $strDateHolder = preg_replace("/[^0-9]/", "/", $strDate );
    $arrDateParts = explode("/",$strDateHolder);
    //last number is the year
    if(sizeof($arrDateParts) > 1 && (strlen($arrDateParts[2]) > 2 || $arrDateParts[2] > 12)){
       return (int)(strtotime($arrDateParts[2].'/'.$arrDateParts[0].'/'.$arrDateParts[1]));
    }
    else if(is_int($strDate)){
        return (int)$strDate;
    }
    else{
        return (int)strtotime($strDate);
    }
  }


        //make the CURL string
    function MakeCurlString(&$arrParameters,$arrCurlExcludes = array(),$boolIgnoreCommas=FALSE){
      $arrOutPutPost = array();
      foreach($arrParameters as $ka=>$va){
        if($ka !="" && $ka != "PHPSESSID"){
          //if this is an array of sorts
          if(stristr($va,',') && !$boolIgnoreCommas){
            $arrTemp = explode(',',$va);
            foreach($arrTemp as $kb=>$vb){
                  $arrOutPutPost[$ka.$kb] = trim($vb);
            }
          }
          //this is an array and needs to be stored as such
          else if(is_array($va)){
            foreach($va as $kc=>$vc){
              $arrOutPutPost[] = trim($ka).'='.trim($vc);
            }
          }
          else{
              if(!in_array($ka,$arrCurlExcludes))
                  $arrOutPutPost[] = trim($ka).'='.trim($va);
          }
        }
      }
      //last step prior to posting , make the string
      $strPostString = implode ('&', $arrOutPutPost);
      return $strPostString;
    }


    //lets decompose the curls tring
    function DecomposeCurlString($strCurlString){
      $arrCurlOptions = explode("&",$strCurlString);
      $arrReturnCurl = array();
      //$arrExceptions = explode(',',$strExceptions);
      foreach($arrCurlOptions as $ka=>$va){
        //if(in_array(,$arrExceptions))
        $arrValueNamePair = explode("=",$va,2);
        //lets check to see if one of these are an array each time through
        if(array_key_exists($arrValueNamePair[0],$arrReturnCurl) && (!is_array($arrReturnCurl[$arrValueNamePair[0]]))){
            //store the original value
            $strTempContainer = $arrReturnCurl[$arrValueNamePair[0]];
            //turn the index into an array
            $arrReturnCurl[$arrValueNamePair[0]] = array($strTempContainer);
            //load the new value as well
            $arrReturnCurl[$arrValueNamePair[0]][] = $arrValueNamePair[1];
        }
        //an array already exists, add it to the existing one
        else if(array_key_exists($arrValueNamePair[0],$arrReturnCurl) && is_array($arrReturnCurl[$arrValueNamePair[0]]))
            $arrReturnCurl[$arrValueNamePair[0]][] = $arrValueNamePair[1];

        else{
            if(array_key_exists(1,$arrValueNamePair))
                $arrReturnCurl[$arrValueNamePair[0]] = $arrValueNamePair[1];
            else
                $arrReturnCurl[$arrValueNamePair[0]] = '';
        }
      }
      return $arrReturnCurl;
    }


    //lets update a curl string
   function UpdateCurlString($strCurlString,$varNewKey,$varNewValue='',$boolRemoveKey = FALSE,$boolOverwrite=TRUE,$boolCreateNested=TRUE){
      $arrCurlComponents = $this->DecomposeCurlString($strCurlString);
      if($boolRemoveKey){
         $arrTempArray = array();
         foreach($arrCurlComponents as $ka=>$va){
           if(is_array($va)){//we're not removing an entire key, just a subkey
             if($varNewValue != ""){
                unset($va[array_search($varNewValue,$va)]);
             }
             $arrTempArray[$ka] = $va;
           }
           else{
             if($ka != $varNewKey)
                $arrTempArray[$ka] = $va;
           }
         }
         $arrCurlComponents = $arrTempArray;
      }
      else{
        if(is_array($arrCurlComponents[$varNewKey])){
          if(!in_array($varNewValue,$arrCurlComponents[$varNewKey]))
            $arrCurlComponents[$varNewKey][] = $varNewValue;
        }
        else{
            if($boolOverwrite)
                $arrCurlComponents[$varNewKey] = $varNewValue;
            else{
              if(array_key_exists($varNewKey,$arrCurlComponents) && $varNewValue != $arrCurlComponents[$varNewKey] && $boolCreateNested){
                $arrCurlComponents[$varNewKey] = array($arrCurlComponents[$varNewKey],$varNewValue);
              }
            }
        }
      }
      return $this->MakeCurlString($arrCurlComponents,array(),TRUE);
   }

   //find a specific value in a curl string
   function FindCurlKeyValue($strCurlString,$varKey){
     $arrCurlComponents = $this->DecomposeCurlString($strCurlString);
     if(array_key_exists($varKey,$arrCurlComponents))
        return $arrCurlComponents[$varKey];
     return '';
   }

  /**
  * Get the timezones available, or convert an existing timezone to an offset
  * @param $intTimeZoneIdentifier (int) list identifier
  * @return string/int
  */
   public static function GetTimeZoneData($intTimeZoneIdentifier=0,$boolDateString=FALSE){
    $arrTimeZones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
    if((int)$intTimeZoneIdentifier === 0)
        return PCMW_Utility::GetTimeZoneList($arrTimeZones);
    //load UTC for comparison
    $intUTCTime = new DateTime('now', new DateTimeZone('UTC'));
    foreach($arrTimeZones as $ka=>$va){
      //load the object by timezone country/city designation
      //it matches our chosen TZ
      if((int)$intTimeZoneIdentifier > 0 && $intTimeZoneIdentifier == $ka){
        if($boolDateString)
            return $va;
        $objZoneTime = new DateTimeZone($va);
        //give back the offset in seconds
        return $objZoneTime->getOffset($intUTCTime);
      }
    }
  }

  /**
  * given an array of timezones, narrow down the list to something reasonable
  * @param $arrTimeZones
  * @return array
  */
  public static function GetTimeZoneList($arrTimeZones){
    //get all of the countries available
    $arrCountries = PCMW_Utility::MakeCountryArray();
    $arrTimeZoneList = array();
    $arrUsedTimeZones = array();
    foreach($arrCountries as $strCode=>$strName){
      $arrCountryZones = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $strCode);
      //GET UNIQUE VALUES ONLY
      foreach ($arrCountryZones as $strTimeZoneName) {
          $objDateTimeZone = new DateTimeZone($strTimeZoneName);
          $objDateTime = new DateTime("now", $objDateTimeZone);
          $intOffset = $objDateTime->getOffset();
       if(!in_array($intOffset,$arrUsedTimeZones)){
         $arrUsedTimeZones[] = $intOffset;
		 $strMeridian = $objDateTime->format('H') > 12 ? ' ('. $objDateTime->format('g:i a'). ')' : '';
         $arrTimeZoneList[array_search($strTimeZoneName,$arrTimeZones)] = trim($strTimeZoneName).' - '.$objDateTime->format('H:i').$strMeridian;
       }
      }
    }
    //order them sanely
    ksort($arrTimeZoneList);
    //give it back
    return $arrTimeZoneList;
  }


  /*
  @brief get the name of the page we landed on
  @return string (pagename, with extension)
  */
  public static function GetScriptSelf($strURL='',$intFolderCount=1){
    if($strURL == '')
        $strURL = $_SERVER["SCRIPT_NAME"];
    $strURL = ltrim($strURL,'/');//remove leading slashes
    $strURL = rtrim($strURL,'/');//remoe trailing slashes
    $arrFileParts = explode('/', $strURL);
    return $arrFileParts[count($arrFileParts) - $intFolderCount];
  }

  /*
  @brief get the URL of the page we landed on
  @return string (pagename, with extension)
  */
  public static function GetURLDepth($strURL='',$intFolderCount=1){
    if($strURL == '')
        $strURL = get_site_url().'/'.$_SERVER["SCRIPT_NAME"];
    $strURL = ltrim($strURL,'/');//remove leading slashes
    $strURL = rtrim($strURL,'/');//remoe trailing slashes
    for($i=0; $i<$intFolderCount; $i++) {
        $strURL = dirname($strURL);
    }
    return $strURL;
  }



     /**
  * given a string, remove any special encoding
  * @param $strText
  * @param $strToEncodingType some documents provide different formatting
  * @param $strFromEncodingType some documents provide different formatting
  * @return string
  */
  function RemoveSpecialFormatting($strText,$strToEncodingType='UTF-8',$strFromEncodingType='UTF-8'){
    $strText = mb_convert_encoding($strText, $strToEncodingType, $strFromEncodingType);
    $strText = html_entity_decode($strText, ENT_QUOTES, $strToEncodingType);
    $strText = iconv($strToEncodingType, 'ASCII//TRANSLIT//IGNORE', $strText);
    $find = array();
    $find[] = '“'; // left side double smart quote
    $find[] = '”'; // right side double smart quote
    $find[] = '‘'; // left side single smart quote
    $find[] = '’'; // right side single smart quote
    $find[] = '…'; // elipsis
    $find[] = '—'; // em dash
    $find[] = '–'; // en dash
    $replace = array();
    $replace[] = '"';
    $replace[] = '"';
    $replace[] = "'";
    $replace[] = "'";
    $replace[] = "...";
    $replace[] = "-";
    $replace[] = "-";

    $strText = str_replace($find, $replace, $strText);
    return trim(htmlspecialchars($strText));
  }

  /**
  * go through each member of an array and clean the keys and values
  * @param $arrData
  * @return bool
  */
  function CleanTableData(&$arrData){
    $strTableData = '';
    if(!is_array($arrData))
        return $arrData;
    foreach($arrData as $ka=>$va){
      if(is_string($ka))
        $ka = $this->RemoveSpecialFormatting($ka);
      if(is_string($va))
        $va = $this->RemoveSpecialFormatting($va);
      if(is_array($va))
        $va = $this->CleanTableData($va);//recursive call for nested arrays
      $arrData[$ka] = $va;
    }
    return $arrData;
  }

    /**
    * given a phone number remove or add formatting
    * @param $strPhoneNumber number to be cleaned or formated
    * @param bool $boolForInsert prepare it for display or insert?
    * @return bool
    */
    function PreparePhoneNumber($strPhoneNumber,$boolForInsert=TRUE,$intIsphoneInternational=FALSE){
     if($boolForInsert)
        return preg_replace("/[^0-9]/", "", $strPhoneNumber );
     else{
     //we got this from the database, so should be without formatting
       if(strlen($strPhoneNumber) === 10){
          return '('.substr($strPhoneNumber,0,3).') '.substr($strPhoneNumber,3,3).'-'.substr($strPhoneNumber,6,4);
       }
       else if(strlen($strPhoneNumber) > 10){
           if($strPhoneNumber[0] == "1"){
              $strPhoneNumber = substr($strPhoneNumber,-10);
              return '('.substr($strPhoneNumber,0,3).') '.substr($strPhoneNumber,3,3).'-'.substr($strPhoneNumber,6,4);
          }
          else
            return $strPhoneNumber;
       }
       else
        return $strPhoneNumber;
     }
    }

  /**
  * make a random hexadecimal color
  * @return string hexadecimal color value
  */
  function MakeRandomColor(){
    $arrHexValues = array(1, 2, 3, 4, 5, 6, 7, 8, 9, "A", "B", "C", "D", "E", "F" );
	shuffle($arrHexValues);
	$strColor = "#";
	for($i=1;$i<=6;$i++){
		$strColor .= $arrHexValues[rand(0,14)];
	}
	return $strColor;
  }

  function adjustBrightness($hex, $steps) {
      // Steps should be between -255 and 255. Negative = darker, positive = lighter
      $steps = max(-255, min(255, $steps));

      // Format the hex color string
      $hex = str_replace('#', '', $hex);
      if (strlen($hex) == 3) {
          $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
      }

      // Get decimal values
      $r = hexdec(substr($hex,0,2));
      $g = hexdec(substr($hex,2,2));
      $b = hexdec(substr($hex,4,2));

      // Adjust number of steps and keep it inside 0 to 255
      $r = max(0,min(255,$r + $steps));
      $g = max(0,min(255,$g + $steps));
      $b = max(0,min(255,$b + $steps));

      $r_hex = str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
      $g_hex = str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
      $b_hex = str_pad(dechex($b), 2, '0', STR_PAD_LEFT);

      return '#'.$r_hex.$g_hex.$b_hex;
  }

  function IsColorDark($strColor){
    // returns brightness value from 0 to 255

    // strip off any leading #
    $strColor = str_replace('#', '', $strColor);

    $c_r = hexdec(substr($strColor, 0, 2));
    $c_g = hexdec(substr($strColor, 2, 2));
    $c_b = hexdec(substr($strColor, 4, 2));
    if (((($c_r * 299) + ($c_g * 587) + ($c_b * 114)) / 1000) > 130)
        return TRUE;
    else
        return FALSE;
  }


  /**
  * make a random color combo for styles
  * @return string
  */
  function MakeRandomColorCombo(){
    $strRandomColor = PCMW_Utility::Get()->MakeRandomColor();
    $intSteps = 200;
    //now let's see if the color is light or dark
    if(PCMW_Utility::Get()->IsColorDark($strRandomColor))
      $intSteps = ($intSteps * -1);
    //now lets get the lighter or darker version of the color for the text
    $strTextColor = PCMW_Utility::Get()->adjustBrightness($strRandomColor, $intSteps);
    //now load the fields with data
    return 'background-color:'.$strRandomColor.';color:'.$strTextColor.';';
  }


  /**
  * encode a PHP array into json
  * @param array $arrValues values to be encoded
  * @param $objEncodeType
  * -JSON_HEX_QUOT
  * -JSON_HEX_TAG
  * -JSON_HEX_AMP
  * -JSON_HEX_APOS
  * -JSON_NUMERIC_CHECK
  * -JSON_PRETTY_PRINT
  * -JSON_UNESCAPED_SLASHES
  * -JSON_FORCE_OBJECT
  * -JSON_PRESERVE_ZERO_FRACTION
  * -JSON_UNESCAPED_UNICODE
  * -JSON_PARTIAL_OUTPUT_ON_ERROR
  * @return string
  */
  public static function JSONEncode($arrValues,$objEncodeType = JSON_FORCE_OBJECT){//JSON_FORCE_OBJECT
     //try to encode it now
     if($strJsonData = json_encode($arrValues,$objEncodeType))
        return $strJsonData;
     switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $strError = ' - No errors';
        break;
        case JSON_ERROR_DEPTH:
            $strError = ' - Maximum stack depth exceeded';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            $strError = ' - Underflow or the modes mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            $strError = ' - Unexpected control character found';
        break;
        case JSON_ERROR_SYNTAX:
            $strError = ' - Syntax error, malformed JSON';
        break;
        case JSON_ERROR_UTF8:
            $strError =' - Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
        default:
            $strError = ' - Unknown error';
        break;
    }
     PCMW_Logger::Debug('JSONENCODE failed ['.$strError.'] LINE ['.__LINE__.'] ',1);
  }

  /**
  * decode a json array string into a php array
  * @param string $strValues values to be decoded
  * @param bool $boolAssociatve return an associative array or numerically index array
  * @return array
  */
  public static function JSONDecode($strValues, $boolAssociatve=TRUE){
     return json_decode($strValues, $boolAssociatve);
  }

  /**
  * set a browser cookie
  * @param $strCookieName
  * @param $intDays default to a year ( 365 )
  * @param $strCookieValue curl string of values
  * @return bool
  */
  function SetPCCookie($strCookieName,$arrCookieValue=array(),$intDays=365){
    $strCookieValue = http_build_query($arrCookieValue);
    @setcookie($strCookieName, $strCookieValue, (time() + ($intDays * 86400)), "/");
    return isset($_COOKIE[$strCookieName]);
  }

  /**
  * get and parse the cookie data
  * @param $strCookieName
  * @return array || bool
  */
  function GetPCCookie($strCookieName){
    return @$this->DecomposeCurlString($_COOKIE[$strCookieName]);
  }

  /**
  * given a status code, return the human readable description
  * @param $intCode
  * @return string ( description )
  */
  function GetHTTPResponse($intCode){
    $arrDescription = array();
    if ($intCode !== NULL) {
      switch ($intCode) {
          case 100: $arrDescription[0] = 'Continue';
            $arrDescription[1] = '#E6E312';
            break;
          case 101: $arrDescription[0] = 'Switching Protocols';
            $arrDescription[1] = '#E6E312';
            break;
          case 200: $arrDescription[0] = 'OK';
            $arrDescription[1] = '#089131';
            break;
          case 201: $arrDescription[0] = 'Created';
            $arrDescription[1] = '#E6E312';
            break;
          case 202: $arrDescription[0] = 'Accepted';
            $arrDescription[1] = '#089131';
            break;
          case 203: $arrDescription[0] = 'Non-Authoritative Information';
            $arrDescription[1] = '';
            break;
          case 204: $arrDescription[0] = 'No Content';
            $arrDescription[1] = '#CC8F0B';
            break;
          case 205: $arrDescription[0] = 'Reset Content';
            $arrDescription[1] = '#CC0000';
            break;
          case 206: $arrDescription[0] = 'Partial Content';
            $arrDescription[1] = '#CC0000';
            break;
          case 300: $arrDescription[0] = 'Multiple Choices';
            $arrDescription[1] = '#CC8F0B';
            break;
          case 301: $arrDescription[0] = 'Moved Permanently';
            $arrDescription[1] = '#CC8F0B';
            break;
          case 302: $arrDescription[0] = 'Moved Temporarily';
            $arrDescription[1] = '#CC8F0B';
            break;
          case 303: $arrDescription[0] = 'See Other';
            $arrDescription[1] = '#CC8F0B';
            break;
          case 304: $arrDescription[0] = 'Not Modified';
            $arrDescription[1] = '#CC8F0B';
            break;
          case 305: $arrDescription[0] = 'Use Proxy';
            $arrDescription[1] = '#CC8F0B';
            break;
          case 400: $arrDescription[0] = 'Bad Request';
            $arrDescription[1] = '#CC0000';
            break;
          case 401: $arrDescription[0] = 'Unauthorized';
            $arrDescription[1] = '#CC0000';
            break;
          case 402: $arrDescription[0] = 'Payment Required';
            $arrDescription[1] = '#CC0000';
            break;
          case 403: $arrDescription[0] = 'Forbidden';
            $arrDescription[1] = '#CC0000';
            break;
          case 404: $arrDescription[0] = 'Not Found';
            $arrDescription[1] = '#CC0000';
            break;
          case 405: $arrDescription[0] = 'Method Not Allowed';
            $arrDescription[1] = '#CC0000';
            break;
          case 406: $arrDescription[0] = 'Not Acceptable';
            $arrDescription[1] = '#CC0000';
            break;
          case 407: $arrDescription[0] = 'Proxy Authentication Required';
            $arrDescription[1] = '#CC0000';
            break;
          case 408: $arrDescription[0] = 'Request Time-out';
            $arrDescription[1] = '#CC0000';
            break;
          case 409: $arrDescription[0] = 'Conflict';
            $arrDescription[1] = '#CC0000';
            break;
          case 410: $arrDescription[0] = 'Gone';
            $arrDescription[1] = '#CC0000';
            break;
          case 411: $arrDescription[0] = 'Length Required';
            $arrDescription[1] = '#CC0000';
            break;
          case 412: $arrDescription[0] = 'Precondition Failed';
            $arrDescription[1] = '#CC0000';
            break;
          case 413: $arrDescription[0] = 'Request Entity Too Large';
            $arrDescription[1] = '#CC0000';
            break;
          case 414: $arrDescription[0] = 'Request-URI Too Large';
            $arrDescription[1] = '#CC0000';
            break;
          case 415: $arrDescription[0] = 'Unsupported Media Type';
            $arrDescription[1] = '#CC0000';
            break;
          case 500: $arrDescription[0] = 'Internal Server Error';
            $arrDescription[1] = '#CC0000';
            break;
          case 501: $arrDescription[0] = 'Not Implemented';
            $arrDescription[1] = '#CC0000';
            break;
          case 502: $arrDescription[0] = 'Bad Gateway';
            $arrDescription[1] = '#CC0000';
            break;
          case 503: $arrDescription[0] = 'Service Unavailable';
            $arrDescription[1] = '#CC0000';
            break;
          case 504: $arrDescription[0] = 'Gateway Time-out';
            $arrDescription[1] = '#CC0000';
            break;
          case 505: $arrDescription[0] = 'HTTP Version not supported';
            $arrDescription[1] = '#CC0000';
            break;
          default:
            $arrDescription[0] = 'Unknown http status code "' . htmlentities($intCode) . '"';
            $arrDescription[1] = '#CC0000';
          break;
      }
    }
    $arrDescription[2] = $intCode;
    return $arrDescription;
  }

  /**
  * given  URL and filename get the header HTTP code
  * @param $strURL
  * @return int ( response code ) || bool
  */
  function GetURLHeaderHTTP($strURL,$boolDescribe=FALSE){
    $strHeaders = get_headers($strURL);
    $strResult =  substr($strHeaders[0], 9, 3);
    if($boolDescribe)
        return $this->GetHTTPResponse($strResult);
    //give back our boolean truth
    if((int)$strResult !== 200){
      return FALSE;
    }
    return TRUE;
  }

  /**
  * given a url, send a CURL request
  * @param $strURL
  * @return array ( $varResponse, $arrHeaders)
  */
  function MakeQuickCURL($strURL){
    $arrResponse = array();
    $objCURL = curl_init();
    curl_setopt($objCURL, CURLOPT_URL, $strURL);
    curl_setopt($objCURL, CURLOPT_TIMEOUT, 30);
    curl_setopt($objCURL, CURLOPT_RETURNTRANSFER,1);
    $arrResponse['result'] = curl_exec ($objCURL);
    $arrResponse['headers'] = curl_getinfo($objCURL);
    curl_close ($objCURL);
    return $arrResponse;
  }

  /**
  * get and load a DOM document
  * @param $strPage
  * @return object ( DOM ) || FALSE
  */
  function LoadDOMObject($strPage){
    if(!($strHTML = file_get_contents($strPage)))
      return FALSE;
    $objDom = new DOMDocument;
    libxml_use_internal_errors(true);
    $objDom->loadHTML($strHTML);
    libxml_clear_errors();
    $objDom->preserveWhiteSpace = false;
    return $objDom;
  }

  /**
  * given a string and delimiter, break it into an array
  * @param $strSubject
  * @param $charDelimiter
  * @return array || false
  */
  function MakeSubjectArray($strSubject,$charDelimiter){
    if(strpos($strSubject,$charDelimiter) !== FALSE)
      return explode($charDelimiter,trim($strSubject));
    return FALSE;
  }

  /**
  * given a string replace brackets with tags
  * @param $strContent
  * @return bool
  */
  function CleanPrePostHTML(&$strContent){
   $arrPreReplace = array('[',']');
   $arrPostReplace = array('<','>');
   $strContent = str_replace($arrPreReplace,$arrPostReplace,$strContent);                                                                
   return TRUE;
  }

}//end class
?>