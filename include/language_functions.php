<?php
# Language functions
# Functions for the translation of the application

if (!function_exists("lang_or_i18n_get_translated")) {
function lang_or_i18n_get_translated($text, $mixedprefix, $suffix = "")
    {
    # Translates field names / values using two methods:
    # First it checks if $text exists in the current $lang (after $text is sanitized and $mixedprefix - one by one if an array - and $suffix are added).
    # If not found in the $lang, it tries to translate $text using the i18n_get_translated function.

    $text=trim($text);
    global $lang;

    if (is_array($mixedprefix)) {$prefix = $mixedprefix;}
    else {$prefix = array($mixedprefix);}
    for ($n = 0;$n<count($prefix);$n++) {
        $langindex = $prefix[$n] . strip_tags(strtolower(str_replace(array(", ", " ", "\t", "/", "(", ")"), array("-", "_", "_", "and", "", ""), $text))) . $suffix;

        # Checks if there is a $lang (should be defined for all standard field names / values).
        if (isset($lang[$langindex])) {
            $return = $lang[$langindex];
            break;
        }
    }    
        if (isset($return)) {return $return;}
        else {return i18n_get_translated($text);} # Performs an i18n translation (of probably a custom field name / value).
    }
}

if (!function_exists("i18n_get_translated")) {
function i18n_get_translated($text,$i18n_split_keywords=true)
    {
    # For field names / values using the i18n syntax, return the version in the current user's language
    # Format is ~en:Somename~es:Someothername
    $text=trim($text);
    
    # For multiple keywords, parse each keyword.
    if ($i18n_split_keywords && (strpos($text,",")!==false) && (strpos($text,"~")!==false)) {$s=explode(",",$text);$out="";for ($n=0;$n<count($s);$n++) {if ($n>0) {$out.=",";}; $out.=i18n_get_translated(trim($s[$n]));};return $out;}
    
    global $language,$defaultlanguage;
	$asdefaultlanguage=$defaultlanguage;
	if (!isset($asdefaultlanguage))
		$asdefaultlanguage='en';
    
    # Split
    $s=explode("~",$text);

    # Not a translatable field?
    if (count($s)<2) {return $text;}

    # Find the current language and return it
    $default="";
    for ($n=1;$n<count($s);$n++)
        {
        # Not a translated string, return as-is
        if (substr($s[$n],2,1)!=":" && substr($s[$n],5,1)!=":" && substr($s[$n],0,1)!=":") {return $text;}
        
        # Support both 2 character and 5 character language codes (for example en, en-US).
        $p=strpos($s[$n],':');
		$textLanguage=substr($s[$n],0,$p);
        if ($textLanguage==$language) {return substr($s[$n],$p+1);}
        
        if ($textLanguage==$asdefaultlanguage || $p==0 || $n==1) {$default=substr($s[$n],$p+1);}
        }    
    
    # Translation not found? Return default language
    # No default language entry? Then consider this a broken language string and return the string unprocessed.
    if ($default!="") {return $default;} else {return $text;}
    }
}

function i18n_get_collection_name($mixedcollection, $index="name")
    {
    # Translates collection names

    global $lang;

    # The function handles both strings and arrays.
    if (!is_array($mixedcollection))
        {
        $name_untranslated = $mixedcollection;
        }
    else
        {
        $name_untranslated = $mixedcollection[$index];

        # Check if it is a Smart Collection
        if (isset($mixedcollection['savedsearch']) && ($mixedcollection['savedsearch']!=null))
            {
            return htmlspecialchars($lang['smartcollection'] . ": " . $name_untranslated);
            }
        }

    # Check if it is a My Collection (n)
    $name_translated = preg_replace('/(^My Collection)(|(\s\d+))$/', $lang["mycollection"] . '$2', $name_untranslated, -1, $translated);
    if ($translated==1) {return htmlspecialchars($name_translated);}

    # Check if it is a Upload YYMMDDHHMMSS
    $upload_date = preg_replace('/(^Upload)\s(\d{12})$/', '$2', $name_untranslated, -1, $translated);
	if ($translated!=1)
		$upload_date = preg_replace('/(^Upload)\s(\d{14})$/', '$2', $name_untranslated, -1, $translated);
    if ($translated==1)
		{
		# Translate date into MySQL ISO format to be able to use nicedate()
		if (strlen($upload_date)==14)
			{
			$year = substr($upload_date, 0, 4);
			$upload_date=substr($upload_date, 2);
			}
		else
			{
			$year = substr($upload_date, 0, 2);
			if ((int)$year > (int)date('y'))
				$year = ((int)substr(date('Y'), 0, 2)-1) . $year;
			else
				$year = substr(date('Y'), 0, 2) . $year;
			}
		$month = substr($upload_date, 2, 2);
		$day = substr($upload_date, 4, 2);
		$hour = substr($upload_date, 6, 2);
		$minute = substr($upload_date, 8, 2);
		$second = substr($upload_date, 10, 2);
		$date = nicedate("$year-$month-$day $hour:$minute:$second", true);
		return htmlspecialchars($lang['upload'] . ' ' . $date);
		}

    # Check if it is a Research: [..]
    if (substr($name_untranslated,0,9)=="Research:"){
	return $lang["research"].": ".i18n_get_translated(substr($name_untranslated,9));
    }
    //$name_translated = preg_replace_callback('/(^Research:)(\s.*)/', function ($matches){return i18n_get_translated($matches[2]);}, $name_untranslated, -1, $translated);
    //if ($translated==1) {return htmlspecialchars($lang["research"] . ": " . $name_translated);}

    # Ordinary collection - translate with i18n_get_translated
    return htmlspecialchars(i18n_get_translated($name_untranslated));
    }

if (!function_exists("i18n_get_indexable")) {
function i18n_get_indexable($text)
    {
    # For field names / values using the i18n syntax, return all language versions, as necessary for indexing.
    $text=trim($text);
    $text=str_replace("<br />"," ",$text); // make sure keywords don't get squashed together
    $text=strip_tags($text);
    $text=preg_replace('/~(.*?):/',',',$text);// remove i18n strings, which shouldn't be in the keywords
    //echo $text;die();
    # For multiple keywords, parse each keyword.
    if (substr($text,0,1)!="," && (strpos($text,",")!==false) && (strpos($text,"~")!==false)) {
        $s=explode(",",$text);
        $out="";
        for ($n=0;$n<count($s);$n++) {
        if ($n>0) {$out.=",";} 
        $out.=i18n_get_indexable(trim($s[$n]));
        }
        return $out;
    }

    # Split
    $s=explode("~",$text);

    # Not a translatable field?
    if (count($s)<2) {return $text;}

    $out="";
    for ($n=1;$n<count($s);$n++)
        {
        if (substr($s[$n],2,1)!=":") {return $text;}
        if ($out!="") {$out.=",";}
        $out.=substr($s[$n],3);
        }    
    return $out;
    }
}

if (!function_exists("i18n_get_translations")) {
function i18n_get_translations($value)
    {
    # For a string in the language format, return all translations as an associative array
    # E.g. "en"->"English translation";
    # "fr"->"French translation"
    global $defaultlanguage;
    if (strpos($value,"~")===false) {return array($defaultlanguage=>$value);}
    $s=explode("~",$value);
    $return=array();
    for ($n=1;$n<count($s);$n++)
    {
    $e=explode(":",$s[$n]);
    if (count($e)==2) {$return[$e[0]]=$e[1];}
    }
    return $return;
    }
}

function str_replace_formatted_placeholder($mixedplaceholder, $mixedreplace, $subject, $question_mark = false, $separator = ", ")
    {
    # Returns a string with all occurrences of the $mixedplaceholder in $subject replaced with the $mixedreplace. If $mixedplaceholder is a string but $mixedreplace is an array, the $mixedreplace is imploded to a string using $separator.
    # The replace values are formatted according to the formatting of the placeholders.
    # The placeholders may be written in UPPERCASE, lowercase or Uppercasefirst.
    # Each placeholder will be replaced by the replace value,
    # written with the same case as the placeholder.
    # It's possible to also include "?" as a placeholder for legacy reasons.

    # Example #1:
    # str_replace_formatted_placeholder("%extension", $resource["file_extension"], $lang["originalfileoftype"], true)
    # will search for the three words "%EXTENSION", "%extension" and "%Extension" and also the char "?"
    # in the string $lang["originalfileoftype"]. If the found placeholder is %extension
    # it will be replaced by the value of $resource["file_extension"],
    # written in lowercase. If the found placeholder instead would have been "?" the value
    # would have been written in UPPERCASE.
    #
    # Example #2:
    # str_replace_formatted_placeholder("%resourcetypes%", $searched_resource_types_names_array, $lang["resourcetypes-collections"], false, $lang["resourcetypes_separator"])
    # will search for the three words "%RESOURCETYPES%", "%resourcetypes%" and "%Resourcetypes%"
    # in the string $lang["resourcetypes-collections"]. If the found placeholder is %resourcetypes%
    # all elements in $searched_resource_types_names_array will be written in lowercase and separated by $lang["resourcetypes_separator"] before the resulting string will replace the placeholder.

    # Creates a multi-dimensional array of the placeholders written in different case styles.
    $array_placeholder = array();
    if (is_array($mixedplaceholder)) {$placeholder = $mixedplaceholder;}
    else {$placeholder = array($mixedplaceholder);}
    for ($n = 0;$n<count($placeholder);$n++)
        {
        $array_placeholder[$n] = array(strtoupper($placeholder[$n]), strtolower($placeholder[$n]), ucfirstletter($placeholder[$n]));
        }

    # Creates a multi-dimensional array of the replace values written in different case styles.
    if (is_array($mixedreplace)) {$replace = $mixedreplace;}
    else {$replace = array($mixedreplace);}
    for ($n = 0;$n<count($replace);$n++)
        {
        $array_replace[$n] = array(strtoupper($replace[$n]), strtolower($replace[$n]), ucfirst(strtolower($replace[$n])));
        }

    # Adds "?" to the arrays if required.
    if ($question_mark)
        {
        $array_placeholder[] = "?";
        $array_replace[] = strtoupper($replace[0]);
        }

    # Replaces the placeholders with the replace values and returns the new string.
    $result = $subject;
    if (count($placeholder)==1 && count($replace)>1)
        {
        # The placeholder shall be replaced by an imploded array.
        $array_replace_strings = array(implode($separator, array_map(create_function('$column','return $column[0];'), $array_replace)), implode($separator, array_map(create_function('$column','return $column[1];'), $array_replace)), implode($separator, array_map(create_function('$column','return $column[2];'), $array_replace)));
        $result = str_replace($array_placeholder[0], $array_replace_strings, $result);
        }
    else
        {
        for ($n=0;$n<count($placeholder);$n++)
            {
            if (!isset($array_replace[$n][0])) {break;}
            else
                {
                $result = str_replace($array_placeholder[$n], $array_replace[$n], $result);
                }
            }
        }
    return $result;
    }

function ucfirstletter($string)
    {
    # Returns a string with the first LETTER of $string capitalized.
    # Compare with ucfirst($string) which returns a string with first CHAR of $string capitalized:
    # ucfirstletter("abc") / ucfirstletter("%abc") returns "Abc" / "%Abc"
    # ucfirst("abc") / ucfirst("%abc") returns "Abc" / "%abc"

    # Search for the first letter ([a-zA-Z]), which may or may not be followed by other characters (.*).
    # Replaces the found substring ('$0') with the same substring but now with the first character capitalized, using ucfirst().
    # Note the /e modifier: If this modifier is set, preg_replace() does normal substitution of backreferences in the replacement string, evaluates it as PHP code, and uses the result for replacing the search string.  
    return preg_replace_callback("/[a-zA-Z].*/", "ucfirstletter_callback", $string);

    }
function ucfirstletter_callback($matches){
	return ucfirst($matches[0]);
}
