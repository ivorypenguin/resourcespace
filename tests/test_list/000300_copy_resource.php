<?php
if (php_sapi_name()!=="cli") {exit("This utility is command line only.");}



$new=copy_resource(1);



# Did it work?
if (get_resource_data($new)===false) {return false;}

# Was the title field we set on the original resource copied?
return (get_data_by_field($new,8)=="Test title");
