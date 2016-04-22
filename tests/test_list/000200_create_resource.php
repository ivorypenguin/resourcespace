<?php
if (php_sapi_name()!=="cli") {exit("This utility is command line only.");}



create_resource(1);

# Did it work?
return (get_resource_data(1)!==false);
