<?php
if (php_sapi_name()!=="cli") {exit("This utility is command line only.");}

# Also tests copy_hitcount_to_live() and get_resource_data()

# Update the hit count
update_hitcount(1);
update_hitcount(1);

# Transfer hit count data to live.
copy_hitcount_to_live(1);

# Read the resource data.
$data=get_resource_data(1,false); # Fetch without caching.

# Should be a hit count of two now.
return ($data["hit_count"]==2);
