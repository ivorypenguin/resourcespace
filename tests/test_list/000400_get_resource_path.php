<?php
if (php_sapi_name()!=="cli") {exit("This utility is command line only.");}


$path=get_resource_path(1,true,"");

# Copy the default slideshow image to this location, for future tests to use
copy(dirname(__FILE__) . "/../../gfx/homeanim/1.jpg",$path);

return (strlen($path)>0);

