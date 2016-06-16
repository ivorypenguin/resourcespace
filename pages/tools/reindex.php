<?php
#
# Reindex.php
#
#
# Reindexes the resource metadata. This should be unnecessary unless the resource_keyword table has been corrupted.
#

include "../../include/db.php";
if (!(PHP_SAPI == 'cli')) {include "../../include/authenticate.php"; if (!checkperm("a")) {exit("Permission denied");}}
include_once "../../include/general.php";
include "../../include/resource_functions.php";
include "../../include/image_processing.php";

$sql = '';
if('' != getval('ref', ''))
    {
    $sql = "WHERE r.ref = '" . getvalescaped('ref', '', true) . "'";
    }

set_time_limit(60*60*10);
echo "<pre>";

$start = getvalescaped('start', '0');
if(!is_numeric($start))
    {
    $start = 0;
    }

$resources = sql_query("SELECT r.ref, u.username, u.fullname FROM resource AS r LEFT OUTER JOIN user AS u ON r.created_by = u.ref {$sql} ORDER BY ref");

$time_start = microtime(true);

for($n = $start; $n < count($resources); $n++)
    {
    $ref = $resources[$n]['ref'];

    reindex_resource($ref);

    $words = sql_value("SELECT count(*) `value` FROM resource_keyword WHERE resource = '{$ref}'", 0);

    echo "Done {$ref} ({$n}/" . count($resources) . ") - $words words<br />\n";

    @flush();
    @ob_flush();
    }

$time_end = microtime(true);
$time     = $time_end - $time_start;

echo "Reindex took $time seconds\n";