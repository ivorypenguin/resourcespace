<?php
# English
# Language File for the csv_upload Plugin
# -------
# Note: when translating to a new language, preserve the original case if possible.

$lang["csv_upload_nav_link"]="CSV upload";
$lang["csv_upload_intro"]="<p>This plugin allows you to create resources by uploading a CSV file. The format of the CSV is important and must follow a defined format.</p>";
$lang["csv_upload_encoding_notice"]="<p>Make sure the CSV file is encoded using <b>UTF-8</b>.</p>";

$lang["csv_upload_condition1"]="<li>The CSV must have a header row</li>";
$lang["csv_upload_condition2"]="<li>To create resources of different resource types there must be a column named 'resource_type' containing the reference ID of the resource type (e.g. 1 is Photo)</li>";
$lang["csv_upload_condition3"]="<li>To assign different archive states to the resources there must be a column named 'status' containing the status ID." . " -2=Pending submission, -1=Pending review, 0=Active, 1=Waiting to be archived, 2=Archived</li>";
$lang["csv_upload_condition4"]="<li>To assign different access levels (open,restricted, confidential) states to the resources there must be a column named 'access' with access values: 0=Open, 1=Restricted, 2=Confidential</li>";
$lang["csv_upload_condition5"]="<li>To be able to upload resource files later using batch replace functionality there should be a column named 'Original filename' and each file should have a unique filename</li>";
$lang["csv_upload_condition6"]="<li>All other column headers must correspond to the full name of a resource metadata field</li>";
$lang["csv_upload_condition7"]="<li>All mandatory fields for the created resource types must be present</li>";
$lang["csv_upload_condition8"]="<li>Column(s) that will have values containing <b>commas( , )</b>, make sure you format it as type <b>text</b> so you don't have to add quotes (\"\"). When saving as a csv file, make sure to check the option of quoting text type cells</li>";
$lang["csv_upload_condition9"]='<li>You can download a CSV file example by clicking on <a href="../downloads/csv_upload_example.csv">csv-upload-example.csv</a></li>';
$lang["csv_upload_error_no_permission"]="You do not have the correct permissions to upload a CSV file";
$lang["check_line_count"]="At least two rows found in CSV file";
$lang["check_header_names"]="Header names match those in the meta fields";
$lang["csv_upload_file"]="Select file";
$lang["csv_upload_default"]="Default";
$lang["csv_upload_unspecified"]="When unspecified";
$lang["csv_upload_filter"]="Filter";

$lang["csv_upload_automatic"]="Automatic";
$lang["csv_upload_override"]="Override";
$lang["csv_upload_automatic_notes"]='"Automatic" requires a numeric field in the CSV file called "resource_type" that indicates the resource type for each line.';
$lang["csv_upload_filter_notes"]='"Filter" only processes CSV line where the "resource_type" field matches the specified value.';
$lang["csv_upload_override_notes"]='"Override" will treat every CSV line as the specified resource type, regardless of "resource_type" existence or value.';