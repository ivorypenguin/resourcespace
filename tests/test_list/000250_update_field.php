<?php
if (php_sapi_name()!=="cli") {exit("This utility is command line only.");}



update_field(1,8,"Test title");


# Was it set?
return (get_data_by_field(1,8)=="Test title");

