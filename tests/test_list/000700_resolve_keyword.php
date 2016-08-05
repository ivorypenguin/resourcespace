<?php
if (php_sapi_name()!=="cli") {exit("This utility is command line only.");}


# Resolve a known keyword
$key1=resolve_keyword("Test",false);

# Resolve an unknown keyword and have it create it.
$key2=resolve_keyword("Unknown",true);

# To do - more testing around resolving different words, non-ASCII characters, etc.

# Everything as expected?
return (is_numeric($key1) && is_numeric($key2) && $key1!=$key2);
