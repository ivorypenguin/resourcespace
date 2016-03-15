<?php
/**
 * Ensures the filename cannot leave the directory set.
 *
 * @param string $name
 * @return string
 */
function safe_file_name($name)
    {
    // Returns a file name stipped of all non alphanumeric values
    // Spaces are replaced with underscores
    $alphanum = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
    $name = str_replace(' ', '_', $name);
    $newname = '';

    for($n = 0; $n < strlen($name); $n++)
        {
        $c = substr($name, $n, 1);
        if(strpos($alphanum, $c) !== false)
            {
            $newname .= $c;
            }
        }

    $newname = substr($newname, 0, 30);

    return $newname;
    }