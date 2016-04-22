<?php
include_once '../include/db.php';
include_once '../include/general.php';
# External access support (authenticate only if no key was provided)
if(getvalescaped('k', '') == '')
    {
    include_once '../include/authenticate.php';
    }
include_once '../include/search_functions.php';
include_once '../include/collections_functions.php';
include_once '../include/csv_export_functions.php';

$search     = getvalescaped('search', '');
$restypes   = getvalescaped('restypes', '');
$order_by   = getvalescaped('order_by', '');
$archive    = getvalescaped('archive', '');
$sort       = getvalescaped('sort', '');
$starsearch = getvalescaped('starsearch', '');

// Do the search again to get the results back
$search_results = do_search($search, $restypes, $order_by, $archive, -1, $sort, false, $starsearch);

log_activity($lang['csvExportResultsMetadata'],LOG_CODE_DOWNLOADED,$search . ($restypes == '' ? '' : ' (' . $restypes . ')'));

header("Content-type: application/octet-stream");
header("Content-disposition: attachment; filename=search_results_metadata.csv");

echo generateResourcesMetadataCSV($search_results);

ob_flush();
exit();