<?php
/**
* @file PCMW_BaseClass.php
* @brief This file contains the PCMW_BaseClass class.
* @author Logan Boyd
* @package MedAdherence
*/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) || ! defined( 'ABSPATH' ) )
	die;
require_once(dirname(__FILE__)  .DIRECTORY_SEPARATOR.'PCMW_Constants.php');
require_once(dirname(__FILE__)  .DIRECTORY_SEPARATOR.'PCMW_Logger.php');
require_once(dirname(__FILE__)  .DIRECTORY_SEPARATOR.'PCMW_isemail.php'); // supposedly 100% accurate email syntax validator
/**
 * @class PCMW_BaseClass
 * @brief This class contains all functions common to almost all other classes.
 */
class PCMW_BaseClass
{
  protected $var_meta_arr;

  function __construct()
  {
    $this->var_meta_arr = array();
  } // end class constructor

  function __destruct() { }


  /**
   * Unserializes the object specified by $objname from session.
   *
   * If what was in session was empty or not an object, return false.
   *
   * @todo add debug error msgs to getObject()
   * @param string $objname   The name of the object as it was serialized
   * @return object|bool
   */
  static function getObject($objname)
  {
    $object = NULL;
    if(isset($_SESSION[$objname]) && !empty($_SESSION[$objname]))
    {
      $object = unserialize($_SESSION[$objname]);
      if(is_object($object))
        return $object;
      else
        return FALSE;
    }
    else
    {
      return FALSE; // return false on fail for now; should this attempt to create an object?
    }
  }

  /**
   * Given an object and a name string, serialize the object in session as a
   * session variable named $objname. If what was passed wasn't an object,
   * return false. Otherwise, session var is assumed to now contain the object.
   *
   * @todo add debug msgs to storeObject()
   * @param object $object    The object to serialize
   * @param string $objname   The name of the session variable to serialize as.
   * @return bool
   */
  static function storeObject($object, $objname)
  {
    if(isset($object) && is_object($object))
    {
      $_SESSION[$objname] = serialize($object);
      return TRUE;
    }
    else
    {
      return FALSE;
    }
  }

  /**
   * Given a field name and its value, check whether the value is a valid DATE
   * string. If the field is valid, return true. Otherwise, return an associative
   * array containing the invalid parts and their expected values.
   *
   * @param string $field_name
   * @param string $date
   * @return array
   */
  static function checkDate($field_name, $date)
  {
    $err_array = Array();
    $regex = '/^(19[0-9]{2}|2[0-9]{3})-(0[1-9]{1}|1[0-2]{1})-(0[1-9]{1}|[1-2]{1}[0-9]{1}|3[0-1]{1})$/i';

    $str_ck = self::checkString($field_name, $date, DATE_LENGTH, DATE_LENGTH);
    if($str_ck !== TRUE)
    {
      $err_array = array_merge($err_array, $str_ck);
    }
    if(!empty($date) && ($date == DATE_NULL || !preg_match($regex, $date)))
    {
      $err_array[$field_name]['expected_value'] = 'valid date string of format YYYY-MM-DD';
      $err_array[$field_name]['value'] = $date;
    }
    if(is_array($err_array))
      return $err_array;
    else
      return TRUE;
  } // end checkDate()

  /**
   * Given a field name and its value, check whether the value is a valid
   * DATETIME string. If the field is valid, return true. Otherwise, return an
   * associative array containing the invalid parts and their expected values.
   *
   * @param string $field_name
   * @param string $datetime
   * @return array
   * @todo make this work with other valid date/time inputs
   */
  static function checkDateTime($field_name, $datetime)
  {
    $err_array = Array();

    // should match dates in range of 1900-01-01 to 2999-12-31 and times in range of 00:00:00 to 23:59:59
    $regex = '/^(19[0-9]{2}|2[0-9]{3})-(0[1-9]{1}|1[0-2]{1})-(0[1-9]{1}|[1-2]{1}[0-9]{1}|3[0-1]{1}) ([0-1]{1}[0-9]{1}|2[0-3]{1}):([0-5]{1}[0-9]{1}):([0-5]{1}[0-9]{1})$/i';

    $str_ck = self::checkString($field_name, $datetime, DATETIME_LENGTH, DATETIME_LENGTH);
    if($str_ck !== TRUE)
    {
      $err_array = array_merge($err_array, $str_ck);
    }
    if(!empty($datetime) && ($datetime == DATETIME_NULL || !preg_match($regex, $datetime)))
    {
      $err_array[$field_name]['expected_value'] = 'valid datetime string of format YYYY-MM-DD HH:MM:SS';
      $err_array[$field_name]['value'] = $datetime;
    }
    if(is_array($err_array))
      return $err_array;
    else
      return TRUE;
  } // end checkDateTime()

  /**
   * Given a field name and its value, check whether the value is a valid
   * e-mail address. If the field is valid, return true. Otherwise, return an
   * associative array containing the invalid parts and their expected values.
   *
   * @param string $field_name
   * @param string $datetime
   * @todo use that guy's BSD-licensed regex instead of filter_var() since filter_var() doesn't correctly return valid/invalid in many cases (as per the RFCs)
   * @return array
   */
  static function checkEmail($field_name, $email)
  {
    $err_array = Array();
    //$regex = '';

    $str_ck = self::checkString($field_name, $email, 100);
    if($str_ck !== TRUE)
    {
      $err_array = array_merge($err_array, $str_ck);
    }
    if(!empty($email) && !pcmw_isemail($email))
    {
      $err_array[$field_name]['expected_value'] = 'valid e-mail address matching RFC1123, RFC3696, RFC4291, RFC5321, and RFC5322.';
      $err_array[$field_name]['value'] = $email;
    }
    if(is_array($err_array))
      return $err_array;
    else
      return TRUE;
  }

  /**
   * Given a field name and its value, check whether the value is a valid
   * floating point number. If the field is valid, return true. Otherwise,
   * return an associative array containing the invalid parts and their
   * expected values.
   *
   * @param string $field_name
   * @param string $float
   * @return array
   */
  static function checkFloat($field_name, $float, $allow_empty = false)
  {
    $err_array = Array();
    if(!is_float($float) && !is_numeric($float))
    {
      $err_array[$field_name]['expected_type'] = 'float';
      $err_array[$field_name]['type'] = gettype($float);
    }

    if(!empty($err_array))
      return $err_array;
    else
      return TRUE;
  } // end checkFloat()

  /**
   * Given a field name and its value, check whether the value is a valid
   * integer. If the field is valid, return true. Otherwise, return an
   * associative array containing the invalid parts and their expected values.
   *
   * @param string $field_name
   * @param int $int
   * @return array
   */
  static function checkInt($field_name, $int, $allow_empty = false)
  {
    $err_array = Array();
    if(!is_int($int) && !is_numeric($int))
    {
      $err_array[$field_name]['expected_type'] = 'integer';
      $err_array[$field_name]['type'] = gettype($int);
    }
    if(!empty($err_array))
      return $err_array;
    else
      return TRUE;
  } // end checkInt()

  /**
   * Given a field name and its value, check whether the value is a valid
   * integer for a database key. If the field is valid, return true. Otherwise,
   * return an associative array containing the invalid parts and their expected
   * values.
   *
   * @param string $field_name
   * @param int $key
   * @return array
   */
  static function checkKey($field_name, $key, $allow_empty = false)
  {
    $err_array = Array();

    $check = self::checkInt($field_name, $key);
    if($check !== TRUE)
    {
      $err_array = array_merge($err_array, $check);
    }

    if(intval($key) < 0)
    {
      $err_array[$field_name]['expected_value'] = 'integer >= 0';
      $err_array[$field_name]['value'] = intval($key);
    }
    if(!empty($err_array))
      return $err_array;
    else
      return TRUE;
  } // end checkKey()

  /**
   * Given a field name and its value, check whether the value is a valid string
   * with a length within the minimum and maximum values. If the field is valid,
   * return true. Otherwise, return an associative array containing the invalid
   * parts and their expected values.
   *
   * @param string $field_name  The name of the variable being checked
   * @param string $string      The variable being checked
   * @param int $max            Optional maximum length for the string
   * @param int $min            Optional minimum length for the string
   * @return array
   */
  static function checkString($field_name, $string, $max = 255, $min = 0)
  {
    $err_array = Array();
      //PCMW_Logger::Debug('Errors: ['.(int)is_string($string).'] LINE ['.__LINE__.'] METHOD ['.__METHOD__.']',1);
    if(!is_string($string))
    {
      $err_array[$field_name]['expected_type'] = 'string';
      $err_array[$field_name]['type'] = gettype($string);
    }
    if(!empty($string) && strlen($string) < $min || strlen($string) > $max)
    {
      if($min != $max)
      {
        $err_array[$field_name]['expected_length'] = '<= ' . $max . ' and >= ' .  $min;
      }
      else
      {
        $err_array[$field_name]['expected_length'] = 'exactly ' . $max;
      }
      $err_array[$field_name]['length'] = strlen($string);
    }
    if(!empty($err_array))
      return $err_array;
    else
      return TRUE;
  } // end checkString()

  /**
   * Given an associative array of variables, their types and min/max lengths
   * (if applicable), and an array of vars to ignore, validate all class
   * variables not in the ignore array. If the class variable isn't in the
   * passed associative array, skip those as well.
   *
   * @todo Make $var_arr just a part of the class itself?
   *
   * For each variable that does not pass validation, add it to an associative
   * array describing the problem, then return the array.
   *
   * @param array $var_arr      An array of variables, their 'types', mins and maxes
   * @param array $ignore_arr   The array of variables to ignore
   * @return array
   */
  function checkTypes($var_arr, $ignore_arr)
  {
    $err_array = Array();
    $check = null;//Array();
    //var_dump($this);
    $arrValues = $this->LoadArrayWithObject();
    foreach($arrValues as $key => $value)
    {
      //PCMW_Logger::Debug('$key ['.$key.'] $value ['.$value.'] array_key_exists($key, $ignore_arr) ['.(int)array_key_exists($key, $ignore_arr).']['.__METHOD__.'] on LINE ['.__LINE__.']',1);
      // skip the check if we're supposed to ignore this variable
      if(is_array($key) || (is_array($ignore_arr) && array_key_exists($key, $ignore_arr)))
        continue 1;
        //PCMW_Logger::Debug('$key ['.$key.'] $value ['.$value.'] array_key_exists($key, $ignore_arr) ['.(int)array_key_exists($key, $ignore_arr).']['.__METHOD__.'] on LINE ['.__LINE__.']',1);

      // note that if a class variable is not represented in $var_arr, it won't get checked
      switch($var_arr[$key]['type'])
      {
        case 'key':
          $check = self::checkKey($key, $value);
          break;
        case 'string':
          if(!empty($var_arr[$key]['min']) && !empty($var_arr[$key]['max']))
          {
            $check = self::checkString($key, $value, $var_arr[$key]['max'], $var_arr[$key]['min']);
          }
          elseif(!empty($var_arr[$key]['max']))
          {
            $check = self::checkString($key, $value, $var_arr[$key]['max']);
          }
          else
          {
            $check = self::checkString($key, $value);
          }
          break;
        case 'date':
          $check = self::checkDate($key, $value);
          break;
        case 'datetime':
          $check = self::checkDateTime($key, $value);
          break;
        case 'email':
          $check = self::checkEmail($key, $value);
          break;
        case 'int':
          $check = self::checkInt($key, $value);
          break;
        case 'float':
          $check = self::checkFloat($key, $value);
          break;
        default:
          break;
      }
      if($check !== TRUE)
      {
        $err_array = array_merge($err_array, $check);
      }
    }
    if(!empty($err_array))
      return $err_array;
    else
      return TRUE;
  } // end checkTypes() 
}