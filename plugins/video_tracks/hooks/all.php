<?php
    
function autoload_captioning($class)
    {
    // project-specific namespace prefix
    $prefix = 'Captioning\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/../lib/Captioning/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file))
        {
        require $file;
        }
    }

spl_autoload_register('autoload_captioning');
    
use Captioning\Format\SubripFile;
use Captioning\Format\WebvttFile;
        
function HookVideo_tracksAllStaticsync_after_alt ($resource, $altfile="")
    {
    if(!is_array($altfile))
        {
        return false;
        }
    global $lang;
    if (mb_strtolower($altfile["extension"])=="srt")  
        {
		$newvtt["name"]=trim($altfile["name"])==""?str_replace("?", "VTT", $lang["fileoftype"]):str_ireplace("SRT", "VTT",$altfile["name"]);
		$newvtt["ref"] = add_alternative_file($resource, $newvtt["name"], $altfile["altdescription"], $altfile["basefilename"] . ".vtt", "vtt", $altfile["file_size"]);
		$newvtt["path"] = get_resource_path($resource, true, '', true, "vtt", -1, 1, false, '',  $newvtt["ref"]);
        
        try {
            $srt = new SubripFile($altfile["path"]);
            $srt->convertTo('webvtt')->save($newvtt["path"]);
            }
        catch(Exception $e)
            {
            echo "Error: ".$e->getMessage()."\n";
            }       
        }
    return true;        
    }
	
