<?php

# Use temporary tables to improve performance/reliability of certain query types?
# This is recommended if your server supports it, as it will provide more reliable search results
# for wildcard searches
$use_temp_tables = false; // general temp-table based improvements (wildcard matching, duplicate search)
$use_temp_tables_for_keyword_joins = true; // specifically to omit the use of temp tables for keyword joins when $use_temp_tables=true. Index modification works better in some cases, but default is true so $use_temp_tables behaves the same by default. 
