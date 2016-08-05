<?php

if (php_sapi_name()!=="cli") {exit("This utility is command line only.");}

$test_data=array
    (
    "this a line with a newline at the end\n" => 'this a line with a newline at the end\n',
    "this a line with a backslash-r at the end\r" => 'this a line with a backslash-r at the end\r',
    "this a line with a backslashes \\in the middle before the first in" => 'this a line with a backslashes in the middle before the first in',
    "this a line has too many backslashes before the backslash-n \\\\\\n I hope it resolves it" => 'this a line has too many backslashes before the backslash-n \n I hope it resolves it',
    "this a line has several single quotes '''  that are unescaped" => 'this a line has several single quotes \\\'\\\'\\\'  that are unescaped'
    );

foreach($test_data as $input_value=>$expected_value)
    {
    if(escape_check($input_value)!==$expected_value)
        {
        unset ($test_data);
        return false;
        }
    }

unset($test_data);
return true;
