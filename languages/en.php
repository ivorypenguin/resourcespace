<?php
# English
# Language File for ResourceSpace
# -------
# Note: when translating to a new language, preserve the original case if possible.

# User group names (for the default user groups)
$lang["usergroup-administrators"]="Administrators";
$lang["usergroup-general_users"]="General Users";
$lang["usergroup-super_admin"]="Super Admin";
$lang["usergroup-archivists"]="Archivists";
$lang["usergroup-restricted_user_-_requests_emailed"]="Restricted User - Requests Emailed";
$lang["usergroup-restricted_user_-_requests_managed"]="Restricted User - Requests Managed";
$lang["usergroup-restricted_user_-_payment_immediate"]="Restricted User - Payment Immediate";
$lang["usergroup-restricted_user_-_payment_invoice"]="Restricted User - Payment Invoice";

# Resource type names (for the default resource types)
$lang["resourcetype-photo"]="Photo";
$lang["resourcetype-document"]="Document";
$lang["resourcetype-video"]="Video";
$lang["resourcetype-audio"]="Audio";
$lang["resourcetype-global_fields"]="Global Fields";
$lang["resourcetype-archive_only"]="Archive Only";
$lang["resourcetype-photo-2"]="Photos";
$lang["resourcetype-document-2"]="Documents";
$lang["resourcetype-video-2"]="Videos";
$lang["resourcetype-audio-2"]="Audio";

# Image size names (for the default image sizes)
$lang["imagesize-thumbnail"]="Thumbnail";
$lang["imagesize-preview"]="Preview";
$lang["imagesize-screen"]="Screen";
$lang["imagesize-low_resolution_print"]="Low resolution print";
$lang["imagesize-high_resolution_print"]="High resolution print";
$lang["imagesize-collection"]="Collection";

# Field titles (for the default fields)
$lang["fieldtitle-keywords"]="Keywords";
$lang["fieldtitle-country"]="Country";
$lang["fieldtitle-title"]="Title";
$lang["fieldtitle-story_extract"]=$lang["storyextract"]="Story Extract";
$lang["fieldtitle-credit"]="Credit";
$lang["fieldtitle-date"]=$lang["date"]="Date";
$lang["fieldtitle-expiry_date"]="Expiry date";
$lang["fieldtitle-caption"]="Caption";
$lang["fieldtitle-notes"]="Notes";
$lang["fieldtitle-named_persons"]="Named person(s)";
$lang["fieldtitle-camera_make_and_model"]="Camera make / model";
$lang["fieldtitle-original_filename"]="Original filename";
$lang["fieldtitle-video_contents_list"]="Video contents list";
$lang["fieldtitle-source"]="Source";
$lang["fieldtitle-website"]="Website";
$lang["fieldtitle-artist"]="Artist";
$lang["fieldtitle-album"]="Album";
$lang["fieldtitle-track"]="Track";
$lang["fieldtitle-year"]="Year";
$lang["fieldtitle-genre"]="Genre";
$lang["fieldtitle-duration"]="Duration";
$lang["fieldtitle-channel_mode"]="Channel mode";
$lang["fieldtitle-sample_rate"]="Sample rate";
$lang["fieldtitle-audio_bitrate"]="Audio bitrate";
$lang["fieldtitle-frame_rate"]="Frame rate";
$lang["fieldtitle-video_bitrate"]="Video bitrate";
$lang["fieldtitle-aspect_ratio"]="Aspect ratio";
$lang["fieldtitle-video_size"]="Video size";
$lang["fieldtitle-image_size"]="Image size";
$lang["fieldtitle-extracted_text"]="Extracted text";
$lang["fieldtitle-file_size"]=$lang["filesize"]="File size";
$lang["fieldtitle-category"]="Category";
$lang["fieldtitle-subject"]="Subject";
$lang["fieldtitle-author"]="Author";
$lang["fieldtitle-owner"]="Owner";

# Field types
$lang["fieldtype-text_box_single_line"]="Text box (single line)";
$lang["fieldtype-text_box_multi-line"]="Text box (multi-line)";
$lang["fieldtype-text_box_large_multi-line"]="Text box (large multi-line)";
$lang["fieldtype-text_box_formatted_and_ckeditor"]="Text box (formatted / CKeditor)";
$lang["fieldtype-check_box_list"]="Check box list";
$lang["fieldtype-drop_down_list"]="Drop down list";
$lang["fieldtype-date"]="Date";
$lang["fieldtype-date_and_optional_time"]="Date and optional time";
$lang["fieldtype-date_and_time"]="Date / time";
$lang["fieldtype-expiry_date"]="Expiry date";
$lang["fieldtype-category_tree"]="Category tree";
$lang["fieldtype-dynamic_keywords_list"]="Dynamic keywords list";
$lang["fieldtype-dynamic_tree_in_development"]="Dynamic tree (in development)";
$lang["fieldtype-radio_buttons"]="Radio buttons";
$lang["fieldtype-warning_message"]="Warning message";

# Property labels (for the default properties)
$lang["documentation-permissions"]="See <a href=../../documentation/permissions.txt target=_blank>the permissions help text file</a> for further information on permissions.";
$lang["property-reference"]="Reference";
$lang["property-name"]="Name";
$lang["property-permissions"]="Permissions";
$lang["information-permissions"]="NOTE: Global permissions from config may also be in effect";
$lang["property-fixed_theme"]="Fixed theme";
$lang["property-parent"]="Parent";
$lang["property-search_filter"]="Search filter";
$lang["property-edit_filter"]="Edit filter";
$lang["property-resource_defaults"]="Resource defaults";
$lang["property-override_config_options"]="Override config options";
$lang["property-email_welcome_message"]="Email welcome message";
$lang["information-ip_address_restriction"]="Wildcards are supported for IP address restrictions, e.g. 128.124.*";
$lang["property-ip_address_restriction"]="IP address restriction";
$lang["property-request_mode"]="Request mode";
$lang["property-allow_registration_selection"]="Allow registration selection";

$lang["property-resource_type_id"]="Resource type id";
$lang["information-allowed_extensions"]="If set, only files with the specified extensions are allowed upon upload to this type, e.g. jpg,gif";
$lang["property-allowed_extensions"]="Allowed extensions";
$lang["information-resource_type_config_override"]="Allows custom configuration values for each resource type, affecting search results, resource view and edit pages. Don't forget to revert any settings changed here in the config override for the other resource types.";

$lang["property-field_id"]="Field id";
$lang["property-title"]="Title";
$lang["property-resource_type"]="Resource type";
$lang["property-field_type"]="Field type";

$lang["property-options"]="Comma separated list of options. The first option will be the default option. If you do not wish to set a default value, use a starting comma to default to blank. E.g. <br />,Option1,Option2 - will default to blank.";
$lang['property-options_edit_link'] = 'Manage options';
$lang["property-required"]="Required";
$lang["property-order_by"]="Order by";
$lang["property-indexing"]="<b>Indexing</b>";
$lang["information-if_you_enable_indexing_below_and_the_field_already_contains_data-you_will_need_to_reindex_this_field"]="If you enable indexing below and the field already contains data, you will need to <a target=_blank href=../tools/reindex_field.php?field=%ref>reindex this field</a>"; # %ref will be replaced with the field id
$lang["property-index_this_field"]="Index this field";
$lang["information-enable_partial_indexing"]="Partial keyword indexing (prefix+infix indexing) should be used sparingly as it will significantly increase the index size. See the wiki for details.";
$lang["property-enable_partial_indexing"]="Enable partial indexing";
$lang["information-shorthand_name"]="Important: Shorthand name must be set for the field to be appear on the search bar, in Advanced search or to be used in search/edit filters. It must contain only lowercase alphabetical characters - no spaces, numbers or symbols.";
$lang["property-shorthand_name"]="Shorthand name";
$lang["property-display_field"]="Display field";
$lang["property-enable_advanced_search"]="Enable advanced search";
$lang["property-enable_simple_search"]="Enable simple search";
$lang["property-use_for_find_similar_searching"]="Use for find similar searching";
$lang["property-iptc_equiv"]="Iptc equiv";
$lang["property-display_template"]="Display template";
$lang["property-value_filter"]="Value filter";
$lang["property-regexp_filter"]="Regexp filter";
$lang["information-regexp_filter"]="Regular Expression filtering - e.g. '[A-Z]+' will ensure only upper case letters can be entered.";
$lang["information-regexp_fail"]="The entered value was not in the required format.";
$lang["property-tab_name"]="Tab name";
$lang["property-push_metadata"]="Push metadata";
$lang["property-smart_theme_name"]="Smart featured collection name";
$lang["property-exiftool_field"]="Exiftool field";
$lang["property-exiftool_filter"]="Exiftool filter";
$lang["property-help_text"]="Help text";
$lang["property-tooltip_text"]="Tooltip text";
$lang["information-tooltip_text"]="Tooltip text: Text that will appear in simple/advanced search when the cursor hovers over the field";
$lang["information-display_as_dropdown"]="Checkbox lists and dropdown boxes: display as a dropdown box on the advanced search? (the default is to display both as checkbox lists on the advanced search page to enable OR functionality)";
$lang["property-display_as_dropdown"]="Display as dropdown";
$lang["property-external_user_access"]="External user access";
$lang["property-autocomplete_macro"]="Autocomplete macro";
$lang["property-hide_when_uploading"]="Hide when uploading";
$lang["property-hide_when_restricted"]="Hide when restricted";
$lang["property-omit_when_copying"]="Omit when copying";
$lang["property-sync_with_field"]="Sync with field";
$lang["information-copy_field"]="<a href=field_copy.php?ref=%ref>Copy field</a>";
$lang["property-display_condition"]="Display condition";
$lang["information-display_condition"]="Display condition: this field will only display if the following conditions are met. Uses same format as group search filter i.e. shortname=value1|value2, shortnamea=validoptiona;shortnameb=validoptionb1|validoptionb2";
$lang["property-onchange_macro"]="On change macro";
$lang["information-onchange_macro"]="On change macro: code to be executed when field value is changed. CAUTION ADVISED";
$lang["information-derestrict_filter"]="Derestrict filter. Can be used in conjunction with g permission so that all resources are restricted unless metadata condition is met";
$lang["information-push_metadata"]="If set, the metadata for this resource will be displayed on the resource view page for any related resources. For example, you may relate several photos to a person resource. If this property is set on the person resource, then the person metadata will appear on all related photo resource records, avoiding duplication of data in the system.";

$lang["property-query"]="Query";

$lang["information-id"]="Note: 'Id' below MUST be set to a three character unique code.";
$lang["property-id"]="Id";
$lang["property-width"]="Width";
$lang["property-height"]="Height";
$lang["property-quality"]="Quality";
$lang["property-pad_to_size"]="Pad to size";
$lang["property-internal"]="Internal";
$lang["property-allow_preview"]="Allow preview";
$lang["property-allow_restricted_download"]="Allow restricted download";

$lang["property-total_resources"]="Total resources";
$lang["property-total_keywords"]="Total keywords";
$lang["property-resource_keyword_relationships"]="Resource keyword relationships";
$lang["property-total_collections"]="Total collections";
$lang["property-collection_resource_relationships"]="Collection resource relationships";
$lang["property-total_users"]="Total users";

# Used for activity log
$lang["property-resource-field"]="Resource field";
$lang["property-old_value"]="Old value";
$lang["property-new_value"]="New value";
$lang["property-table"]="Table";
$lang["property-column"]="Column";
$lang["property-table_reference"]="Table reference";
$lang["property-code"]="Code";
$lang["property-operation"]="Operation";

# Top navigation bar (also reused for page titles)
$lang["logout"]="Log out";
$lang["contactus"]="Contact us";
# next line
$lang["home"]="Home";
$lang["searchresults"]="Search results";
$lang["themes"]="Featured collections";
$lang["themeselector"]=&$lang["themes"];
$lang["mycollections"]="My collections";
$lang["myrequests"]="My requests";
$lang["collections"]="Collections";
$lang["mycontributions"]="My contributions";
$lang["researchrequest"]="Research request";
$lang["helpandadvice"]="Knowledge Base";
$lang["teamcentre"]="Admin";

# footer link
$lang["aboutus"]="About us";
$lang["interface"]="Interface";
$lang["changethemeto"] = "Change theme to";

# Search bar
$lang["simplesearch"]="Simple search";
$lang["searchbutton"]="Search";
$lang["clearbutton"]="Clear";
$lang["bycountry"]="By country";
$lang["bydate"]="By date";
$lang["anyyear"]="Any year";
$lang["anymonth"]="Any month";
$lang["anyday"]="Any day";
$lang["anycountry"]="Any country";
$lang["resultsdisplay"]="Results display";
$lang["xlthumbs"]="X-large";
$lang["xlthumbstitle"]="Extra Large Thumbnails";
$lang["largethumbs"]="Large";
$lang["largethumbstitle"]="Large Thumbnails";
$lang["smallthumbs"]="Small";
$lang["smallthumbstitle"]="Small Thumbnails";
$lang["list"]="List";
$lang["listtitle"]="List View";
$lang["perpage"]="per page";
$lang["on"]="On";
$lang["off"]="Off";
$lang["seconds"]="seconds";
$lang["reload"]="Reload";
$lang["pause"]="Pause";
$lang["filterbutton"]="Filter";
$lang["stopbutton"]="Stop";
$lang["loadmorebutton"]="Load more";

$lang["gotoadvancedsearch"]="Advanced search";
$lang["viewnewmaterial"]="View new material";
$lang["researchrequestservice"]="Research request service";

# Admin
$lang["manageresources"]="Manage resources";
$lang["overquota"]="Over disk space quota; cannot add resources";
$lang["managearchiveresources"]="Manage archive resources";
$lang["managethemes"]="Manage featured collections";
$lang["manageresearchrequests"]="Manage research requests";
$lang["manageusers"]="Manage users";
$lang["managecontent"]="Manage content";
$lang["viewstatistics"]="View statistics";
$lang["viewreports"]="View reports";
$lang["viewreport"]="View report";
$lang["treeobjecttype-report"]=$lang["report"]="Report";
$lang["sendbulkmail"]="Send bulk mail";
$lang["systemsetup"]="System";
$lang["systemlog"]="System log";
$lang["usersonline"]="Users currently online (idle time minutes)";
$lang["diskusage"]="Disk usage";
$lang["available"]="available";
$lang["used"]="used";
$lang["free"]="free";
$lang["editresearch"]="Edit research";
$lang["editproperties"]="Edit properties";
$lang["selectfiles"]="Select files";
$lang["searchcontent"]="Search content";
$lang["ticktodeletehelp"]="Tick to delete this section";
$lang["createnewhelp"]="Create a new help section";
$lang["searchcontenteg"]="(page, name, text)";
$lang["copyresource"]="Copy Resource";
$lang["resourceidnotfound"]="The resource ID was not found";
$lang["inclusive"]="(inclusive)";
$lang["pluginssetup"]="Manage plugins";
$lang["pluginmanager"]="Plugin manager";
$lang["users"]="Users";

# Admin - Bulk E-mails
$lang["emailrecipients"]="E-mail recipient(s)";
$lang["emailsubject"]="E-mail subject";
$lang["emailtext"]="E-mail text";
$lang["emailhtml"]="Enable HTML support - mail body must use HTML formatting";
$lang["send"]="Send";
$lang["emailsent"]="The e-mail has been sent.";
$lang["mustspecifyoneuser"]="You must specify at least one user";
$lang["couldnotmatchusers"]="Could not match all the usernames, or usernames were duplicated";

# Admin - User management
$lang["comments"]="Comments";

# Admin - Resource management
$lang["viewuserpending"]="View user contributed resources pending review";
$lang["userpending"]="User contributed resources pending review";
$lang["viewuserpendingsubmission"]="View user contributed resources pending submission";
$lang["userpendingsubmission"]="User contributed resources pending submission";
$lang["searcharchivedresources"]="Search archived resources";
$lang["viewresourcespendingarchive"]="View resources pending archive";
$lang["resourcespendingarchive"]="Resources pending archive";
$lang["uploadresourcebatch"]="Upload resource batch";
$lang["uploadinprogress"]="Upload and preview creation in progress";
$lang["donotmoveaway"]="IMPORTANT: Do not navigate away from this page until the upload has completed!";
$lang["pleaseselectfiles"]="Please select one or more files to upload.";
$lang["previewstatus"]="Created previews for resource %file% of %filestotal%."; # %file%, %filestotal% will be replaced, e.g. Created previews for resource 2 of 2.
$lang["uploadedstatus"]="Resource %file% of %filestotal% uploaded - %path%"; # %file%, %filestotal% and %path% will be replaced, e.g. Resource 2 of 2 uploaded - pub/pictures/astro-images/JUPITER9.JPG
$lang["upload_failed_for_path"]="Upload failed for %path%"; # %path% will be replaced, e.g. Upload failed for abc123.jpg
$lang["uploadcomplete"]="Upload complete";
$lang["upload_summary"]="Upload summary";
$lang["resources_uploaded-0"]="0 resources uploaded OK.";
$lang["resources_uploaded-1"]="1 resource uploaded OK.";
$lang["resources_uploaded-n"]="%done% resources uploaded OK."; # %done% will be replaced, e.g. 17 resources uploaded OK.
$lang["resources_failed-0"]="0 resources failed.";
$lang["resources_failed-1"]="1 resource failed.";
$lang["resources_failed-n"]="%done% resources failed."; # %failed% will be replaced, e.g. 2 resources failed.
$lang["specifyftpserver"]="Specify remote FTP server";
$lang["ftpserver"]="FTP server";
$lang["ftpusername"]="FTP username";
$lang["ftppassword"]="FTP password";
$lang["ftpfolder"]="FTP folder";
$lang["usesmtp"]="Use an SMTP Server for email sending / receiving";
$lang["smtpsecure"]="SMTP Secure";
$lang["smtphost"]="SMTP Host";
$lang["smtpport"]="SMTP Port";
$lang["smtpauth"]="Use Authentication for SMTP Server";
$lang["smtpusername"]="SMTP Username";
$lang["smtppassword"]="SMTP Password";


$lang["connect"]="Connect";
$lang["uselocalupload"]="OR: Use local 'upload' folder instead of remote FTP server";

# User contributions
$lang["contributenewresource"]="Contribute new resource";
$lang["viewcontributedps"]="View my contributions - pending submission";
$lang["viewcontributedpr"]="View my contributions - pending review";
$lang["viewcontributedsubittedl"]="View my contributions - active";
$lang["contributedps"]="My contributions - pending submission";
$lang["contributedpr"]="My contributions - pending review";
$lang["contributedsubittedl"]="My contributions - active";

# Collections
$lang["editcollection"]="Edit collection";
$lang["editcollectionresources"]="Edit collection previews";
$lang["access"]="Access";
$lang["private"]="Private";
$lang["public"]="Public";
$lang["attachedusers"]="Attached users";
$lang["themecategory"]="Featured collection category";
$lang["theme"]="Featured collection";
$lang["newcategoryname"]="OR: Enter a new featured collection category name...";
$lang["allowothersaddremove"]="Allow other users to add/remove resources";
$lang["resetarchivestatus"]="Reset archive status for all resources in collection";
$lang["editallresources"]="Edit all resources in collection";
$lang["editresources"]="Edit resources";
$lang["multieditnotallowed"]="Mult-edit not allowed - all the resources are not in the same status or of the same type.";
$lang["emailcollectiontitle"]="E-mail collection";
$lang["collectionname"]="Collection name";
$lang["collection-name"]="Collection: %collectionname%"; # %collectionname will be replaced, e.g. Collection: Cars
$lang["collectionid"]="Collection ID";
$lang["collectionidprefix"]="Col_ID";
$lang["_dupe"]="_dupe";
$lang["emailtousers"]="E-mail to users<br><br><b>For existing users</b> start typing the user's name to search, click the user when found and then click plus<br><br><b>For non-registered users</b> type the e-mail address then click plus";
$lang["emailtousers_internal"]="E-mail to users<br><br>Start typing the user's name to search, then click the required user when found";
$lang["removecollectionareyousure"]="Are you sure you wish to remove this collection from your list?";
$lang["managemycollections"]="Manage my collections";
$lang["createnewcollection"]="Create new collection";
$lang["findpubliccollection"]="Public collections";
$lang["searchpubliccollections"]="Search public collections";
$lang["addtomycollections"]="Add to my collections";
$lang["action-addtocollection"]="Add to collection";
$lang["action-removefromcollection"]="Remove from collection";
$lang["addtocollection"]="Add to collection";
$lang["cantmodifycollection"]="You can't modify this collection.";
$lang["currentcollection"]="Current collection";
$lang["viewcollection"]="View collection";
$lang['hiddencollections_hide']="Hide hidden collections";
$lang['hiddencollections_show']="Show hidden collections";
$lang['hide_collection']="Hide collection";
$lang["viewall"]="View all";
$lang['view_all_resources'] = 'View all resources';
$lang["action-editall"]="Edit all";
$lang['edit_all_resources'] = 'Edit all resources';
$lang["hidethumbnails"]="Hide thumbs";
$lang["showthumbnails"]="Show thumbs";
$lang["toggle"]="Toggle";
$lang["resize"]="Resize";
$lang["contactsheet"]="Contact sheet";
$lang["mycollection"]="My Collection";
$lang["editresearchrequests"]="Edit research requests";
$lang["research"]="Research";
$lang["savedsearch"]="Saved search";
$lang["mustspecifyoneusername"]="You must specify at least one username";
$lang["couldnotmatchallusernames"]="Could not match all the usernames";
$lang["emailcollectionmessage"]="has e-mailed you a collection of resources from $applicationname which has been added to your 'my collections' page."; # suffixed to user name e.g. "Fred has e-mailed you a collection..."
$lang["nomessage"]="No message";
$lang["emailcollectionmessageexternal"]="has e-mailed you a collection of resources from $applicationname."; # suffixed to user name e.g. "Fred has e-mailed you a collection..."
$lang["clicklinkviewcollection"]="Click the link below to view the collection.";
$lang["zippedcollectiontextfile"]="Include text file with resource/collection data.";
$lang["archivesettings"]="Archive settings";
$lang["archive-zip"]="ZIP";
$lang["archive-7z"]="7Z";
$lang["download-of-collections-not-enabled"]="Download of collections is not enabled.";
$lang["archiver-utility-not-found"]="Couldn't find the archiver utility.";
$lang["collection_download_settings-not-defined"]="\$collection_download_settings is not defined.";
$lang["collection_download_settings-not-an-array"]="\$collection_download_settings is not an array.";
$lang["listfile-argument-not-defined"]="\$archiver_listfile_argument is not defined.";
$lang["nothing_to_download"]="Nothing to download.";
$lang["copycollectionremoveall"]="Remove all resources before copying";
$lang["purgeanddelete"]="Purge";
$lang["purgecollectionareyousure"]="Are you sure you want to remove this collection AND DELETE all resources in it?";
$lang["collectionsdeleteempty"]="Delete empty collections";
$lang["collectionsdeleteemptyareyousure"]="Are you sure you want to delete all of your own empty collections?";
$lang["collectionsnothemeselected"]="You must select or enter a featured collection category name.";
$lang["downloaded"]="Downloaded";
$lang["contents"]="Contents";
$lang["forthispackage"]="for this package";
$lang["didnotinclude"]="Did not include";
$lang["selectcollection"]="Select collection";
$lang["total"]="Total";
$lang["lastmatching"]="Last matching";
$lang["ownedbyyou"]="owned by you";
$lang["edit_theme_category"]="Edit featured collection category";
$lang["emailthemecollectionmessageexternal"]="has e-mailed you collections of resources from $applicationname."; 
$lang["emailthememessage"]="has e-mailed you a selection of featured collections from $applicationname which have been added to your 'My collections' page.";
$lang["clicklinkviewthemes"]="Click the link below to view the featured collections.";
$lang["clicklinkviewcollections"]="Click the links below to view the collections.";

# Lightbox
$lang["lightbox-image"] = "Image";
$lang["lightbox-of"] = "of";

# Resource create / edit / view
$lang["createnewresource"]="Create new resource";
$lang["treeobjecttype-resource_type"]=$lang["resourcetype"]="Resource type";
$lang["resourcetypes"]="Resource types";
$lang["deleteresource"]="Delete resource";
$lang["downloadresource"]="Download resource";
$lang["rightclicktodownload"]="Right click this link and choose 'Save Target As' to download your resource..."; # For Opera/IE browsers only
$lang["downloadinprogress"]="Download in progress";
$lang["editmultipleresources"]="Edit Multiple Resources";
$lang["editresource"]="Edit resource";
$lang["resources_selected-1"]="1 resource selected"; # 1 resource selected
$lang["resources_selected-2"]="%number resources selected"; # e.g. 17 resources selected
$lang["image"]="Image";
$lang["previewimage"]="Preview image";
$lang["file"]="File";
$lang["upload"]="Upload";
$lang["action-upload"]="Upload";
$lang["action-upload-to-collection"]="Upload to this collection";
$lang["uploadafile"]="Upload a file";
$lang["replacefile"]="Replace file";
$lang["showwatermark"]="Show watermark";
$lang["hidewatermark"]="Hide watermark";
$lang["imagecorrection"]="Edit preview images";
$lang["previewthumbonly"]="(preview / thumbnail only)";
$lang["rotateclockwise"]="Rotate clockwise";
$lang["rotateanticlockwise"]="Rotate anti-clockwise";
$lang["increasegamma"]="Brighten previews";
$lang["decreasegamma"]="Darken previews";
$lang["restoreoriginal"]="Restore original";
$lang["recreatepreviews"]="Recreate previews";
$lang["retrypreviews"]="Retry preview creation";
$lang["specifydefaultcontent"]="Specify default content for new resources";
$lang["properties"]="Properties";
$lang["relatedresources"]="Related resources";
$lang["relatedresources-filename_extension"]="Related resources - %EXTENSION"; # Use %EXTENSION, %extension or %Extension as a placeholder. The placeholder will be replaced with the filename extension, using the same case. E.g. "Related resources - %EXTENSION" -> "Related resources - JPG"
$lang["relatedresources-id"]="Related resources for %id%"; # %id% will be replaced, e.g. Related Resources - ID57
$lang["relatedresources-restype"]="Related resources - %restype%"; # Use %RESTYPE%, %restype% or %Restype% as a placeholder. The placeholder will be replaced with the resource type in plural, using the same case. E.g. "Related resources - %restype%" -> "Related resources - photos"
$lang["relatedresources_onupload"]="Relate All Resources on Upload";
$lang["indexedsearchable"]="Indexed, searchable fields";
$lang["clearform"]="Clear form";
$lang["similarresources"]="similar resources"; # e.g. 17 similar resources
$lang["similarresource"]="similar resource"; # e.g. 1 similar resource
$lang["nosimilarresources"]="No similar resources";
$lang["emailresourcetitle"]="E-mail resource";
$lang["resourcetitle"]="Resource title";
$lang["requestresource"]="Request resource";
$lang["action-viewmatchingresources"]="View matching resources";
$lang["nomatchingresources"]="No matching resources";
$lang["matchingresources"]="matching resources"; # e.g. 17 matching resources
$lang["advancedsearch"]="Advanced search";
$lang["archiveonlysearch"]="Archived resources";
$lang["allfields"]="All fields";
$lang["typespecific"]="Type specific";
$lang["youfound"]="You found"; # e.g. you found 17 resources
$lang["youfoundresources"]="resources"; # e.g. you found 17 resources
$lang["youfoundresource"]="resource"; # e.g. you found 1 resource
$lang["youfoundresults"]="results"; # e.g. you found 17 resources
$lang["youfoundresult"]="result"; # e.g. you found 1 resource
$lang["display"]="Display"; # e.g. Display: thumbnails / list
$lang["sortorder"]="Sort order";
$lang['sortorder-asc']  = 'ASC';
$lang['sortorder-desc'] = 'DESC';
$lang["relevance"]="Relevance";
$lang["asadded"]="As added";
$lang["popularity"]="Popularity";
$lang["rating"]="Rating";
$lang["colour"]="Colour";
$lang["jumptopage"]="Jump to page";
$lang["jump"]="Jump";
$lang["titleandcountry"]="Title / country";
$lang["torefineyourresults"]="To refine your results, try";
$lang["verybestresources"]="The very best resources";
$lang["addtocurrentcollection"]="Add to current collection";
$lang["addresource"]="Add single resource";
$lang["addresourcebatch"]="Add resource batch";
$lang["fileupload"]="File upload";
$lang["clickbrowsetolocate"]="Click browse to locate a file";
$lang["resourcetools"]="Resource tools";
$lang["fileinformation"]="File information";
$lang["options"]="Options";
$lang["previousresult"]="Previous result";
$lang["viewallresults"]="View all results";
$lang["nextresult"]="Next result";
$lang["pixels"]="pixels";
$lang["download"]="Download";
$lang["preview"]="Preview";
$lang["fullscreenpreview"]="Full screen preview";
$lang["originalfileoftype"]="Original %EXTENSION File"; # Use %EXTENSION, %extension or %Extension as a placeholder. The placeholder will be replaced with the filename extension, using the same case. E.g. "Original %EXTENSION File" -> "Original PDF File"
$lang["fileoftype"]="? File"; # ? will be replaced, e.g. "MP4 File"
$lang["cell-fileoftype"]="%EXTENSION File"; # Use %EXTENSION, %extension or %Extension as a placeholder. The placeholder will be replaced with the filename extension, using the same case. E.g. "%EXTENSION File" -> "JPG File"
$lang["field-fileextension"]="%EXTENSION"; # Use %EXTENSION, %extension or %Extension as a placeholder. The placeholder will be replaced with the filename extension, using the same case. E.g. "%EXTENSION" -> "JPG"
$lang["fileextension-inside-brackets"]="[%EXTENSION]"; # Use %EXTENSION, %extension or %Extension as a placeholder. The placeholder will be replaced with the filename extension, using the same case. E.g. "[%EXTENSION]" -> "[JPG]"
$lang["fileextension"]="%EXTENSION"; # Use %EXTENSION, %extension or %Extension as a placeholder. The placeholder will be replaced with the filename extension, using the same case. E.g. "%EXTENSION" -> "JPG"
$lang["log"]="Log";
$lang["resourcedetails"]="Resource details";
$lang["offlineresource"]="Offline resource";
$lang["action-request"]="Request";
$lang["request"]="Request";
$lang["requestlog"]="Request log";
$lang["searchforsimilarresources"]="Search for similar resources";
$lang["clicktoviewasresultset"]="View these resources as a result set";
$lang["searchnomatches"]="Your search did not have any results.";
$lang["try"]="Try";
$lang["tryselectingallcountries"]="Try selecting <b>all</b> in the countries box, or";
$lang["tryselectinganyyear"]="Try selecting <b>any year</b> in the year box, or";
$lang["tryselectinganymonth"]="Try selecting <b>any month</b> in the month box, or";
$lang["trybeinglessspecific"]="Try being less specific by";
$lang["enteringfewerkeywords"]="entering fewer search keywords."; # Suffixed to any of the above 4 items e.g. "Try being less specific by entering fewer search keywords"
$lang["match"]="match";
$lang["matches"]="matches";
$lang["inthearchive"]="in the archive";
$lang["nomatchesinthearchive"]="No matches in the archive";
$lang["savethissearchtocollection"]="Save query to collection";
$lang["mustspecifyonekeyword"]="You must specify at least one search keyword.";
$lang["hasemailedyouaresource"]="has e-mailed you a resource."; # Suffixed to user name, e.g. Fred has e-mailed you a resource
$lang["clicktoviewresource"]="Click the link below to view the resource.";
$lang["statuscode"]="Status code";
$lang["unoconv_pdf"]="generated by Open Office";
$lang['calibre_pdf']="generated by Calibre";
$lang["resourcenotfound"]="Resource not found.";
$lang['remove_custom_access_users_groups'] = 'Users and groups with custom access';
$lang['remove_custom_access_no_users_found'] = 'No users or groups with custom access found.';
$lang['action-generate_pdf'] = 'Generate PDF';

# Resource log - actions
$lang["resourcelog"]="Resource log";
$lang["log-u"]="Uploaded file";
$lang["log-c"]="Created resource";
$lang["log-d"]="Downloaded file";
$lang["log-e"]="Edited resource field";
$lang["log-m"]="Edited resource field (multi-edit)";
$lang["log-E"]="Shared resource via e-mail to ";//  + notes field
$lang["log-v"]="Viewed resource";
$lang["log-x"]="Deleted resource";
$lang["log-l"]="Logged in"; # For user entries only.
$lang["log-t"]="Transformed file";
$lang["log-s"]="Change status";
$lang["log-a"]="Change access";
$lang["log-r"]="Reverted metadata";
$lang["log-b"]="Created alternate";
$lang["log-y"]="Deleted alternate";
$lang["log-missinglang"]="[type] (missing lang)"; # [type] will be replaced.
$lang['log-adminpermissionsrequired'] = 'Full admin permission required!';

/* Universal log codes (generic - not for example, resource specific) */
$lang["log_code_a"]="Access changed";
$lang["log_code_b"]=$lang["log-b"];
$lang["log_code_c"]="Created";
$lang["log_code_C"]="Copied";
$lang["log_code_d"]=$lang["log-d"];
$lang["log_code_e"]="Edited";
$lang["log_code_E"]="Emailed";
$lang["log_code_l"]=$lang["log-l"];
$lang["log_code_m"]="Multi-edited";
$lang["log_code_p"]="Payed";
$lang["log_code_r"]="Reverted or re-uploaded";
$lang["log_code_R"]="Reordered";
$lang["log_code_s"]=$lang["log-s"];
$lang["log_code_S"]="System";
$lang["log_code_t"]=$lang["log-t"];
$lang["log_code_u"]=$lang["log-u"];
$lang["log_code_U"]="Unspecified";
$lang["log_code_v"]="Viewed";
$lang["log_code_x"]="Deleted";

$lang["backtoresourceview"]="Back to resource view";
$lang["continuetoresourceview"]="Continue to resource view";

# Resource status
$lang["status"]="Status";
$lang["status-2"]="Pending submission";
$lang["status-1"]="Pending review";
$lang["status0"]="Active";
$lang["status1"]="Waiting to be archived";
$lang["status2"]="Archived";
$lang["status3"]="Deleted";

# Charts
$lang["activity"]="Activity";
$lang["summary"]="summary";
$lang["mostinaday"]="Most in a day";
$lang["totalfortheyear"]="Total for the year";
$lang["totalforthemonth"]="Total for the month";
$lang["dailyaverage"]="Daily average for active days";
$lang["nodata"]="No data for this period.";
$lang["max"]="Max"; # i.e. maximum
$lang["statisticsfor"]="Statistics for"; # e.g. Statistics for 2007
$lang["printallforyear"]="Print all statistics for this year";

# Log in / user account
$lang["nopassword"]="Click here to apply for an account";
$lang["forgottenpassword"]="Click here if you have forgotten your password";
$lang["keepmeloggedin"]="Keep me logged in at this workstation";
$lang["columnheader-username"]=$lang["username"]="Username";
$lang["password"]="Password";
$lang["login"]="Log in";
$lang["loginincorrect"]="Sorry, your login details were incorrect.<br /><br />If you have forgotten your password,<br />use the link above to request a new one.";
$lang["accountexpired"]="Your account has expired. Please contact the resources team.";
$lang["useralreadyexists"]="An account with that e-mail or username already exists, changes not saved";
$lang["useremailalreadyexists"]="An account with that e-mail already exists.";
$lang["ticktoemail"]="E-mail this user their username and new password";
$lang["ticktodelete"]="Tick to delete this user";
$lang["edituser"]="Edit user";
$lang["columnheader-full_name"]=$lang["fullname"]="Full name";
$lang["email"]="E-mail";
$lang["columnheader-e-mail_address"]=$lang["emailaddress"]="E-mail address";
$lang["suggest"]="Suggest";
$lang["accountexpiresoptional"]="Account expires (optional)";
$lang["lastactive"]="Last active";
$lang["lastbrowser"]="Last browser";
$lang["searchusers"]="Search users";
$lang["createuserwithusername"]="Create user with username...";
$lang["emailnotfound"]="The e-mail address specified could not be found";
$lang["yourname"]="Your full name";
$lang["youremailaddress"]="Your e-mail address";
$lang["sendreminder"]="Send reminder";
$lang["sendnewpassword"]="Send email";
$lang["requestuserlogin"]="Request user login";
$lang["accountlockedstatus"]="Account is locked";
$lang["accountunlock"]="Unlock";

# Research request
$lang["nameofproject"]="Name of project";
$lang["descriptionofproject"]="Description of project";
$lang["descriptionofprojecteg"]="(eg. Audience / style / subject / geographical focus)";
$lang["deadline"]="Deadline";
$lang["nodeadline"]="No deadline";
$lang["noprojectname"]="You must specify a project name";
$lang["noprojectdescription"]="You must specify a project description";
$lang["contacttelephone"]="Contact telephone";
$lang["finaluse"]="Final use";
$lang["finaluseeg"]="(eg. Powerpoint / leaflet / poster)";
$lang["noresourcesrequired"]="Number of resources required for final product?";
$lang["shaperequired"]="Shape of images required";
$lang["portrait"]="Portrait";
$lang["landscape"]="Landscape";
$lang["square"]="Square";
$lang["either"]="Either";
$lang["sendrequest"]="Send request";
$lang["editresearchrequest"]="Edit research request";
$lang["requeststatus0"]=$lang["unassigned"]="Unassigned";
$lang["requeststatus1"]="In progress";
$lang["requeststatus2"]="Complete";
$lang["copyexistingresources"]="Copy the resources in an existing collection to this research brief";
$lang["deletethisrequest"]="Tick to delete this request";
$lang["requestedby"]="Requested by";
$lang["requesteditems"]="Requested items";
$lang["assignedtoteammember"]="Assigned to team member";
$lang["typecollectionid"]="(Type collection ID below)";
$lang["researchid"]="Research ID";
$lang["assignedto"]="Assigned to";
$lang["createresearchforuser"]="Create research request for user";
$lang["searchresearchrequests"]="Search Research Requests";
$lang["requestasuser"]="Request as user";
$lang["haspostedresearchrequest"]="has posted a research request"; # username is suffixed to this
$lang["newresearchrequestwaiting"]="New research request waiting";
$lang["researchrequestassignedmessage"]="Your research request has been assigned to a member of the admin team. Once we've completed the research you'll receive an e-mail with a link to all the resources that we recommend.";
$lang["researchrequestassigned"]="Research request assigned";
$lang["researchrequestcompletemessage"]="Your research request is complete and has been added to your 'my collections' page.";
$lang["researchrequestcomplete"]="Research request completed";


# Misc / global
$lang["selectgroupuser"]="Select group/user...";
$lang["select"]="Select...";
$lang["selectloading"]="Select....";
$lang["add"]="Add";
$lang["create"]="Create";
$lang["treeobjecttype-group"]=$lang["group"]="Group";
$lang["groupsmart"]="Group (Smart)";
$lang["confirmaddgroup"]="Are you sure you want to add all the current members in this group?";
$lang["confirmaddgroupsmart"]="Are you sure you want to dynamically include members in this group?";
$lang["backtoteamhome"]="Back to admin home";
$lang["columnheader-resource_id"]=$lang["resourceid"]="Resource ID";
$lang["id"]="ID";
$lang["todate"]="To date";
$lang["fromdate"]="From date";
$lang["day"]="Day";
$lang["month"]="Month";
$lang["year"]="Year";
$lang["hour-abbreviated"]="HH";
$lang["minute-abbreviated"]="MM";
$lang["itemstitle"]="Items";
$lang["tools"]="Tools";
$lang["created"]="Created";
$lang["user"]="User";
$lang["owner"]="Owner";
$lang["message"]="Message";
$lang["name"]="Name";
$lang["action"]="Action";
$lang["treeobjecttype-field"]=$lang["field"]="Field";
$lang["save"]="Save";
$lang["revert"]="Revert";
$lang["cancel"]="Cancel";
$lang["view"]="View";
$lang["type"]="Type";
$lang["text"]="Text";
$lang["yes"]="Yes";
$lang["no"]="No";
$lang["key"]="Key:"; # e.g. explanation of icons on search page
$lang["default"]="Default";
$lang["languageselection"]="Language selection";
$lang["language"]="Language";
$lang["changeyourpassword"]="Change my password";
$lang["yourpassword"]="Your password";
$lang["currentpassword"]="Current password";
$lang["newpassword"]="New password";
$lang["newpasswordretype"]="New password (retype)";
$lang["passwordnotvalid"]="This is not a valid password";
$lang["passwordnotmatch"]="The entered passwords did not match";
$lang["wrongpassword"]="Incorrect password, please try again";
$lang["action-view"]="View";
$lang["action-preview"]="Preview";
$lang["action-expand"]="Expand";
$lang["action-select"]="Select";
$lang["action-download"]="Download";
$lang["action-email"]="E-mail";
$lang["action-edit"]="Edit";
$lang["action-delete"]="Delete";
$lang["action-deletecollection"]="Delete collection";
$lang["action-revertmetadata"]="Revert metadata";
$lang["confirm-revertmetadata"]="Are you sure you want to re-extract the original metadata from this file? This action will simulate a re-upload of the file, and you will lose any altered metadata.";
$lang["action-remove"]="Remove";
$lang['action-replace'] = 'Replace';
$lang["complete"]="Complete";
$lang["backtohome"]="Back to the home page";
$lang["continuetohome"]="Continue to the home page";
$lang["backtohelphome"]="Back to help home";
$lang["backtosearch"]="Back to my search results";
$lang["backtoview"]="Resource View";
$lang["backtoeditresource"]="Back to edit resource";
$lang["backtouser"]="Back to user login";
$lang["continuetouser"]="Continue to user login";
$lang["termsandconditions"]="Terms and conditions";
$lang["iaccept"]="I accept the terms";
$lang["mustaccept"]="You must tick the box to accept the terms before you can proceed";
$lang["proceed"]="Proceed";
$lang["contributedby"]="Contributed by";
$lang["format"]="Format";
$lang["notavailableshort"]="N/A";
$lang["allmonths"]="All months";
$lang["allgroups"]="All groups";
$lang["status-ok"]="OK";
$lang["status-fail"]="FAIL";
$lang["status-warning"]="WARNING";
$lang["status-notinstalled"]="Not installed";
$lang["status-never"]="Never";
$lang["softwareversion"]="? version"; # E.g. "PHP version"
$lang["softwarebuild"]="? Build"; # E.g. "ResourceSpace Build"
$lang["softwarenotfound"]="'?'  not found"; # ? will be replaced.
$lang["client-encoding"]="(client-encoding: %encoding)"; # %encoding will be replaced, e.g. client-encoding: utf8
$lang["browseruseragent"]="Browser user-agent";
$lang['serverplatform']="Server platform";
$lang["are_available-0"]="are available";
$lang["are_available-1"]="is available";
$lang["are_available-2"]="are available";
$lang["were_available-0"]="were available";
$lang["were_available-1"]="was available";
$lang["were_available-2"]="were available";
$lang["resource-0"]="resources";
$lang["resource-1"]="resource";
$lang["resource-2"]="resources";
$lang["status-note"]="NOTE";
$lang["action-changelanguage"]="Change language";
$lang["loading"]="Loading...";
$lang['disable_languages']='Disable language selection options';

# Pager
$lang["next"]="Next";
$lang["previous"]="Previous";
$lang["page"]="Page";
$lang["of"]="of"; # e.g. page 1 of 2
$lang["items"]="items"; # e.g. 17 items
$lang["item"]="item"; # e.g. 1 item

# Statistics
$lang["stat-addpubliccollection"]="Add public collection";
$lang["stat-addresourcetocollection"]="Add resources to collection";
$lang["stat-addsavedsearchtocollection"]="Add saved search to collection";
$lang["stat-addsavedsearchitemstocollection"]="Add saved search items to collection";
$lang["stat-advancedsearch"]="Advanced search";
$lang["stat-archivesearch"]="Archive search";
$lang["stat-assignedresearchrequest"]="Assigned research request";
$lang["stat-createresource"]="Create resource";
$lang["stat-e-mailedcollection"]="E-mailed collection";
$lang["stat-e-mailedresource"]="E-mailed resource";
$lang["stat-keywordaddedtoresource"]="Keyword added to resource";
$lang["stat-keywordusage"]="Keyword usage";
$lang["stat-newcollection"]="New collection";
$lang["stat-newresearchrequest"]="New research request";
$lang["stat-printstory"]="Print story";
$lang["stat-processedresearchrequest"]="Processed research request";
$lang["stat-resourcedownload"]="Resource download";
$lang["stat-resourceedit"]="Resource edit";
$lang["stat-resourceupload"]="Resource upload";
$lang["stat-resourceview"]="Resource view";
$lang["stat-search"]="Search";
$lang["stat-usersession"]="User session";
$lang["stat-addedsmartcollection"]="Added smart collection";

# Access
$lang["access0"]="Open";
$lang["access1"]="Restricted";
$lang["access2"]="Confidential";
$lang["access3"]="Custom";
$lang["statusandrelationships"]="Status and relationships";

# Lists
$lang["months"]=array("January","February","March","April","May","June","July","August","September","October","November","December");
$lang["false-true"]=array("False","True");

# Formatting
$lang["plugin_field_fmt"]="%A (%B)"; // %A and %B are replaced by content defined by individual plugins. See, e.e., config_db_single_select in /include/plugin_functions.php


#Sharing
$lang["share"]="Share";
$lang["sharecollection"]="Share collection";
$lang["sharecollection-name"]="Share collection - %collectionname"; # %collectionname will be replaced, e.g. Share Collection - Cars
$lang["share_theme_category"]="Share featured collection category";
$lang["share_theme_category_subcategories"]="Include featured collections in subcategories for external users?";
$lang["email_theme_category"]="E-mail featured collection category";
$lang["generateurl"]="Generate URL";
$lang["generateurls"]="Generate URLs";
$lang["generateexternalurl"]="Generate external URL";
$lang["generateexternalurls"]="Generate external URLs";
$lang["generateurlinternal"]="The below URL will work for existing users only.";
$lang["generateurlexternal"]="The below URL will work for everyone and does not require a login.";
$lang["generatethemeurlsexternal"]="The below URLs will work for everyone and do not require a login.";
$lang["showexistingthemeshares"]="Show existing shares for featured collections in this category";
$lang["internalusersharing"]="Internal user sharing";
$lang["externalusersharing"]="External user sharing";
$lang["externalusersharing-name"]="External user sharing - %collectionname%"; # %collectionname will be replaced, e.g. External User Sharing - Cars
$lang["accesskey"]="Access key";
$lang["sharedby"]="Shared by";
$lang["sharedwith"]="Shared with";
$lang["lastupdated"]="Last updated";
$lang["lastused"]="Last used";
$lang["noattachedusers"]="No attached users.";
$lang["confirmdeleteaccess"]="Are you sure you wish to delete this access key? Users that have been given access using this key will no longer be able to access this collection.";
$lang["confirmdeleteaccessresource"]="Are you sure you wish to delete this access key? Users that have been given access using this key will no longer be able to access this resource.";
$lang["editingexternalshare"]="Editing external share";
$lang["noexternalsharing"]="No external sharing.";
$lang["sharedcollectionaddwarning"]="Warning: This collection has been shared with external users. The resource you have added has now been made available to these users. Click 'share' to manage the external access for this collection.";
$lang["sharedcollectionaddwarningupload"]="Warning: The selected collection has been shared with external users. The resources you upload will be made available to these users. Click 'share' in the bottom bar to manage the external access for this collection.";

$lang["sharedcollectionaddblocked"]="You are not permitted to add resources to a collection that has been shared with external users.";
$lang["restrictedsharecollection"]="You have restricted access to one or more of the resources in this collection and therefore sharing is prohibited.";
$lang["selectgenerateurlexternal"]="To create a URL that will work for external users (people that do not have a login) please choose the access level you wish to grant to the resources.";
$lang["selectgenerateurlexternalthemecat"]="To create URLs that will allow access to external users (people that do not have a login) please choose the access level you wish to grant to the resources.";
$lang["externalselectresourceaccess"]="If you are e-mailing external users, please select the level of access you would like to grant to this resource.";
$lang["externalselectresourceexpires"]="If you are e-mailing external users, please select an expiry date for the generated URL.";
$lang["externalshareexpired"]="Sorry, this share has expired and is no longer available.";
$lang["notapprovedsharecollection"]="One or more resources in this collection are not active and therefore sharing is prohibited.";
$lang["notapprovedsharetheme"]="Sharing is prohibited for at least one collection, because one or more resources is not active.";
$lang["notapprovedresources"]="The following resources are not active and cannot be added to a shared collection: ";
$lang['error_generating_access_key'] = 'Could not generate an access key';


# New for 1.3
$lang["savesearchitemstocollection"]="Save results to collection";
$lang["removeallresourcesfromcollection"]="Remove all resources from this collection";
$lang['deleteallresourcesfromcollection'] = 'Delete all resources';
$lang["deleteallsure"]="Are you sure you wish to DELETE these resources? This will delete the resources themselves, not just remove them from this collection.";
$lang["batchdonotaddcollection"]="(do not add to a collection)";
$lang["collectionsthemes"]="Related featured and public collections";
$lang["recent"]="Recently added";
$lang["n_recent"]="%qty Recently added resources";
$lang["batchcopyfrom"]="Copy the data below from resource with ID";
$lang["copy"]="Copy";
$lang["zipall"]="Download";
$lang["downloadzip"]="Download collection as an archive";
$lang["downloadsize"]="Download size";
$lang["tagging"]="Tagging";
$lang["speedtagging"]="Speed tagging";
$lang["existingkeywords"]="Existing keywords:";
$lang["extrakeywords"]="Extra keywords";
$lang["leaderboard"]="Leaderboard";
$lang["confirmeditall"]="Are you sure you wish to save? This will overwrite the existing values(s) for the selected field(s) for all the resources in your current collection.";
$lang["confirmsubmitall"]="Are you sure you wish to submit all for review? This will overwrite the existing values(s) for the selected field(s) for all the resources in your current collection and submit them all for review.";
$lang["confirmunsubmitall"]="Are you sure you wish to unsubmit all from the review process? This will overwrite the existing values(s) for the selected field(s) for all the resources in your current collection and unsubmit them all from review.";
$lang["confirmpublishall"]="Are you sure you wish to publish? This will overwrite the existing values(s) for the selected field(s) for all the resources in your current collection and publish them all for public viewing";
$lang["confirmunpublishall"]="Are you sure you wish to unpublish these resources? This will overwrite the existing values(s) for the selected field(s) for all the resources in your current collection and remove them from public viewing";
$lang["collectiondeleteconfirm"]="Are you sure you wish to delete this collection?";
$lang["hidden"]="(hidden)";
$lang["requestnewpassword"]="Request new password";

# New for 1.4
$lang["reorderresources"]="Reorder resources within collection (hold and drag)";
$lang['resourcetypereordered']="Resource type position reordered";
$lang['resourcetypefieldreordered']="Resource type field position reordered";
$lang["addorviewcomments"]="Add or view comments";
$lang["collectioncomments"]="Collection comments";
$lang["collectioncommentsinfo"]="Add a comment to this collection for this resource. This will only apply to this collection.";
$lang["comment"]="Comment";
$lang["warningexpired"]="Resource expired";
$lang["warningexpiredtext"]="Warning! This resource has exceeded the expiry date. You must click the link below to enable the download functionality.";
$lang["warningexpiredok"]="&gt; Enable resource download";
$lang["userrequestcomment"]="Comment";
$lang["addresourcebatchbrowser"]="Upload resources";
$lang["addresourcebatchbrowserjava"]="Add resource batch - in browser  - Java (legacy) ";

$lang["addresourcebatchftp"]="Add resource batch - fetch from FTP server";
$lang["replaceresourcebatch"]="Replace resource batch";
$lang["editmode"]="Edit mode";
$lang["replacealltext"]="Replace all text / option(s)";
$lang["findandreplace"]="Find and replace";
$lang["prependtext"]="Prepend text";
$lang["appendtext"]="Append text / option(s)";
$lang["removetext"]="Remove text / option(s)";
$lang["find"]="Find";
$lang["andreplacewith"]="...and replace with...";
$lang["relateallresources"]="Relate all resources in this collection";

# New for 1.5
$lang["columns"]="Columns";
$lang["contactsheetconfiguration"]="Contact sheet configuration";
$lang["thumbnails"]="Thumbnails";
$lang["contactsheetintrotext"]="Please select the configuration options you'd like for your contact sheet PDF. The preview will update automatically when you change your options, unless you change the order of resources in the collection, in which case you'll need to press the \"Preview\" button to update.<br />When you're ready, press \"Create\" to generate and download your contact sheet PDF.";
$lang["size"]="Size";
$lang["orientation"]="Orientation";
$lang["requiredfield"]="This is a required field";
$lang["requiredfields"]="Please review the form and try again. The following fields were not completed:";
$lang["requiredfields-general"]="Please complete all required fields";
$lang["requiredantispam"]="The anti-spam code has not been entered correctly, please try again";
$lang["viewduplicates"]="View duplicate resources";
$lang["duplicateresources"]="Duplicate resources";
$lang["duplicateresourcesfor"]="Duplicate resources for ";
$lang["duplicateresourceupload"]="Upload failed. This file matches existing resources:";
$lang["noresourcesfound"]="No resources found";
$lang["userlog"]="User log";
$lang["ipaddressrestriction"]="IP address restriction (optional)";
$lang["searchfilteroverride"]="Search filter override";
$lang["wildcardpermittedeg"]="Wildcard permitted e.g.";

# New for 1.6
$lang["collection_download_original"]="Original file";
$lang["newflag"]="NEW!";
$lang["link"]="Link";
$lang["uploadpreview"]="Upload a preview image only";
$lang["starttypingusername"]="(start typing username / full name / group name)";
$lang["requestfeedback"]="Request feedback<br />(you will be e-mailed the response)";
$lang["sendfeedback"]="Send feedback";
$lang["feedbacknocomments"]="You have not left any comments for the resources in the collection.<br />Click the speech bubble next to each resource to add comments.";
$lang["collectionfeedback"]="Collection feedback";
$lang["collectionfeedbackemail"]="You have received the following feedback:";
$lang["feedbacksent"]="Your feedback has been sent.";
$lang["newarchiveresource"]="Add single archived resource";
$lang["nocategoriesselected"]="No categories selected";
$lang["showhidetree"]="Show/hide tree";
$lang["clearall"]="Clear all";
$lang["clearcategoriesareyousure"]="Are you sure you wish to clear all selected options?";

$lang["archive"]="Archive";
$lang["collectionviewhover"]="Click to see the resources in this collection";
$lang["collectioncontacthover"]="Create a contact sheet with the resources in this collection";
$lang["original"]="Original";

$lang["password_not_min_length"]="The password must be at least ? characters in length";
$lang["password_not_min_alpha"]="The password must have at least ? alphabetical (a-z, A-Z) characters";
$lang["password_not_min_uppercase"]="The password must have at least ? upper case (A-Z) characters";
$lang["password_not_min_numeric"]="The password must have at least ? numeric (0-9) characters";
$lang["password_not_min_special"]="The password must have at least ? non alpha-numeric characters (!@$%&* etc.)";
$lang["password_matches_existing"]="The entered password is the same as your existing password";
$lang["password_expired"]="Your password has expired and you must now enter a new password";
$lang["done__password_changed"]="Your password has been changed and you may now log in.";
$lang["max_login_attempts_exceeded"]="You have exceeded the maximum number of login attempts. You must now wait ? minutes before you can attempt to log in again.";

$lang["newlogindetails"]="Please find your new login details below."; # For new password mail
$lang["youraccountdetails"]="Your account details"; # Subject of mail sent to user on user details save

$lang["copyfromcollection"]="Copy from collection";
$lang["donotcopycollection"]="Do not copy from a collection";

$lang["resourcesincollection"]="resources in this collection"; # E.g. 3 resources in this collection
$lang["removefromcurrentcollection"]="Remove from current collection";
$lang["showtranslations"]="+ Show translations";
$lang["hidetranslations"]="- Hide translations";
$lang["archivedresource"]="Archived resource";

$lang["managerelatedkeywords"]="Manage related keywords";
$lang["keyword"]="Keyword";
$lang["relatedkeywords"]="Related Keywords";
$lang["matchingrelatedkeywords"]="Matching related keywords";
$lang["newkeywordrelationship"]="Create new relationship for keyword...";
$lang["searchkeyword"]="Search keyword";

$lang["exportdata"]="Export data";
$lang["exporttype"]="Export type";

$lang["managealternativefiles"]="Manage alternative files";
$lang["managealternativefilestitle"]="Manage alternative files";
$lang["alternativefiles"]="Alternative files";
$lang["filetype"]="File type";
$lang["filedeleteconfirm"]="Are you sure you wish to delete this file?";
$lang["addalternativefile"]="Add alternative file";
$lang["editalternativefile"]="Edit alternative file";
$lang["description"]="Description";
$lang["notuploaded"]="Not uploaded";
$lang["uploadreplacementfile"]="Upload replacement file";
$lang["backtomanagealternativefiles"]="Back to manage alternative files";


$lang["resourceistranscoding"]="Resource is currently being transcoded";
$lang["cantdeletewhiletranscoding"]="You can't delete resources while they are transcoding";

$lang["maxcollectionthumbsreached"]="There are too many resources in this collection to display thumbnails. Thumbnails will now be hidden.";

$lang["ratethisresource"]="How do you rate this resource?";
$lang["ratingthankyou"]="Thank you for your rating.";
$lang["ratings"]="ratings";
$lang["rating_lowercase"]="rating";
$lang["ratingremovehover"]="Click to remove your rating";
$lang["ratingremoved"]="Your rating has been removed.";

$lang["cannotemailpassword"]="You cannot e-mail the user their existing password as it is not stored (a cryptographic hash is stored instead).<br /><br />You must use the 'Suggest' button above which will generate a new password and enable the e-mail function.";

$lang["userrequestnotification1"]="The User Login Request form has been completed with the following details:";
$lang["userrequestnotification2"]="If this is a valid request, please visit the system at the URL below and create an account for this user.";
$lang["ipaddress"]="IP Address";
$lang["userresourcessubmitted"]="The following user contributed resources have been submitted for review:";
$lang["userresourcessubmittednotification"]="This user has submitted resources for review.";
$lang["userresourcesapproved"]="Your submitted resources have been approved:";
$lang["userresourcesunsubmitted"]="The following user contributed resources have been unsubmitted, and no longer require review:";
$lang["userresourcesunsubmittednotification"]="This user has changed the status of these resources so no longer require review.";
$lang["viewalluserpending"]="View all user contributed resources pending review:";

# New for 1.7
$lang["installationcheck"]="Installation check";
$lang["managefieldoptions"]="Manage field options";
$lang["matchingresourcesheading"]="Matching resources";
$lang["backtofieldlist"]="Back to field list";
$lang["rename"]="Rename";
$lang["showalllanguages"]="Show all languages";
$lang["hidealllanguages"]="Hide all languages";
$lang["clicktologinasthisuser"]="Click to log in as this user";
$lang["clicktoviewlog"]="Click to view log";
$lang["addkeyword"]="Add keyword";
$lang["selectedresources"]="Selected resources";
$lang["addresourcebatchlocalfolder"]="Add resource batch - fetch from local upload folder";
$lang["phpextensions"]="PHP extensions";

# Setup Script
$lang["setup-alreadyconfigured"]="Your ResourceSpace installation is already configured.  To reconfigure, you may delete <pre>include/config.php</pre> and point your browser to this page again.";
$lang["setup-successheader"]="Congratulations!";
$lang["setup-successdetails"]="Your initial ResourceSpace setup is complete.  Be sure to check out 'include/default.config.php' for more configuration options.";
$lang["setup-successnextsteps"]="Next steps:";
$lang["setup-successremovewrite"]="You can now remove write access to 'include/'.";
$lang["setup-visitwiki"]='Visit the <a target="_blank" href="http://wiki.resourcespace.org/index.php/?title=Main_Page">ResourceSpace Documentation Wiki</a> for more information about customizing your installation.';
$lang["php-config-file"]="PHP config: '%phpinifile'"; # %phpinifile will be replaced, e.g. PHP config: '/etc/php5/apache2/php.ini'
$lang["setup-checkconfigwrite"]="Write access to config directory:";
$lang["setup-checkstoragewrite"]="Write access to storage directory:";
$lang["setup-welcome"]="Welcome to ResourceSpace";
$lang["setup-introtext"]="Thanks for choosing ResourceSpace.  This configuration script will help you setup ResourceSpace.  This process only needs to be completed once.";
$lang["setup-checkerrors"]="Pre-configuration errors were detected.<br />  Please resolve these errors and return to this page to continue.";
$lang["setup-errorheader"]="There were errors detected in your configuration.  See below for detailed error messages.";
$lang["setup-warnheader"]="Some of your settings generated warning messages.  See below for details.  This doesn't necessarily mean there is a problem with your configuration.";
$lang["setup-basicsettings"]="Basic settings";
$lang["setup-basicsettingsdetails"]="These settings provide the basic setup for your ResourceSpace installation.  Required items are marked with a <strong>*</strong>";
$lang["setup-dbaseconfig"]="Database configuration";
$lang["setup-mysqlerror"]="There was an error with your MySQL settings:";
$lang["setup-mysqlerrorversion"]="MySQL version should be 5 or greater.";
$lang["setup-mysqlerrorserver"]="Unable to reach server.";
$lang["setup-mysqlerrorlogin"]="Login failed. (Check username and password.)";
$lang["setup-mysqlerrordbase"]="Unable to access database.";
$lang["setup-mysqlerrorperns"]="Check user permissions.  Unable to create tables.";
$lang["setup-mysqltestfailed"]="Test failed (unable to verify MySQL)";
$lang["setup-mysqlserver"]="MySQL server";
$lang["setup-mysqlusername"]="MySQL username";
$lang["setup-mysqlpassword"]="MySQL password";
$lang["setup-mysqldb"]="MySQL database";
$lang["setup-mysqlbinpath"]="MySQL binary path";
$lang["setup-generalsettings"]="General settings";
$lang["setup-baseurl"]="Base URL";
$lang['setup-admin_fullname'] = 'Admin full name';
$lang['setup-admin_email'] = 'Admin e-mail';
$lang['setup-admin_username'] = 'Admin username';
$lang['setup-admin_password'] = 'Admin password';

$lang["setup-emailfrom"]="Email from address";
$lang["setup-emailnotify"]="Email notify";
$lang["setup-spiderpassword"]="Spider password";
$lang["setup-scramblekey"]="Scramble key";
$lang["setup-apiscramblekey"]="API scramble key";
$lang["setup-paths"]="Paths";
$lang["setup-pathsdetail"]="For each path, enter the path without a trailing slash to each binary.  To disable a binary, leave the path blank.  Any auto-detected paths have already been filled in.";
$lang["setup-applicationname"]="Application name";
$lang["setup-basicsettingsfooter"]="NOTE: The only <strong>required</strong> settings are on this page.  If you're not interested in checking out the advanced options, you may click below to begin the installation process.";
$lang["setup-if_mysqlserver"]='The IP address or <abbr title="Fully Qualified Domain Name">FQDN</abbr> of your MySQL server installation.  If MySql is installed on the same server as your web server, use "localhost".';
$lang["setup-if_mysqlusername"]="The username used to connect to your MySQL server.  This user must have rights to create tables in the database named below.";
$lang["setup-if_mysqlpassword"]="The password for the MySQL username entered above.";
$lang["setup-if_mysqldb"]="The Name of the MySQL database RS will use. (This database must exist.)";
$lang["setup-if_mysqlbinpath"]="The path to the MySQL client binaries - e.g. mysqldump. NOTE: This is only needed if you plan to use the export tool.";
$lang["setup-if_baseurl"]="The 'base' web address for this installation.  NOTE: No trailing slash.";
$lang['setup-if_admin_username']='The username used to connect to ResourceSpace. This user will be the first user of the system.';
$lang['setup-if_admin_password']='The password for the Admin username entered above.';
$lang["setup-if_emailfrom"]="The address that emails from RS appear to come from.";
$lang["setup-if_emailnotify"]="The email address to which resource/user/research requests are sent.";
$lang["setup-if_spiderpassword"]="The spider password is a required field.";
$lang["setup-if_scramblekey"]="To enable scrambling, set the scramble key to be a hard-to-guess string (similar to a password).  If this is a public installation then this is a very wise idea.  Leave this field blank to disable resource path scrambling. This field has already been randomised for you, but you can change it to match an existing installation, if necessary.";
$lang["setup-if_apiscramblekey"]="Set the api scramble key to be a hard-to-guess string (similar to a password).  If you plan to use APIs then this is a very wise idea.";
$lang["setup-if_applicationname"]="The name of your implementation / installation (e.g. 'MyCompany Resource System').";
$lang["setup-err_mysqlbinpath"]="Unable to verify path.  Leave blank to disable.";
$lang["setup-err_baseurl"]="Base URL is a required field.";
$lang["setup-err_baseurlverify"]="Base URL does not seem to be correct (could not load license.txt).";
$lang["setup-err_spiderpassword"]="The password required for spider.php.  IMPORTANT: Randomise this for each new installation. Your resources will be readable by anyone that knows this password.  This field has already been randomised for you, but you can change it to match an existing installation, if necessary.";
$lang["setup-err_scramblekey"]="If this is a public installation, setting the scramble key is recommended.";
$lang["setup-err_apiscramblekey"]="If this is a public installation, setting the api scramble key is recommended.";
$lang["setup-err_path"]="Unable to verify location of";
$lang["setup-emailerr"]="Not a valid email address.";
$lang['setup-admin_fullname_error'] = "A user's full name cannot be an empty string.";
$lang["setup-rs_initial_configuration"]="ResourceSpace: Initial Configuration";
$lang["setup-include_not_writable"]="'/include' not writable. Only required during setup.";
$lang["setup-override_location_in_advanced"]="Override location in 'Advanced Settings'.";
$lang["setup-advancedsettings"]="Advanced Settings";
$lang["setup-binpath"]="%bin Path"; #%bin will be replaced, e.g. "Imagemagick Path"
$lang["setup-begin_installation"]="Begin installation!";
$lang["setup-generaloptions"]="General options";
$lang["setup-allow_password_change"]="Allow password change?";
$lang["setup-enable_remote_apis"]="Enable remote APIs?";
$lang["setup-if_allowpasswordchange"]="Allow end users to change their passwords.";
$lang["setup-if_enableremoteapis"]="Allow remote access to API plugins.";
$lang["setup-allow_account_requests"]="Allow users to request accounts?";
$lang["setup-display_research_request"]="Display the Research Request functionality?";
$lang["setup-if_displayresearchrequest"]="Allows users to request resources via a form, which is e-mailed.";
$lang["setup-themes_as_home"]="Use the featured collections page as the home page?";
$lang["setup-remote_storage_locations"]="Remote Storage Locations";
$lang["setup-use_remote_storage"]="Use remote storage?";
$lang["setup-if_useremotestorage"]="Check this box to configure remote storage locations for RS. (To use another server for filestore.)";
$lang["setup-storage_directory"]="Storage directory";
$lang["setup-if_storagedirectory"]="Where to put the media files. Can be absolute (/var/www/blah/blah) or relative to the installation. NOTE: No trailing slash.";
$lang["setup-storage_url"]="Storage URL";
$lang["setup-if_storageurl"]="Where the storagedir is available. Can be absolute (http://files.example.com) or relative to the installation. NOTE: No trailing slash.";
$lang["setup-ftp_settings"]="FTP settings";
$lang["setup-if_ftpserver"]="Only necessary if you plan to use the FTP upload feature.";
$lang["setup-smtp-settings"]="SMTP Settings";
$lang["setup-if-usesmtp"]="Use an external SMTP server for outgoing emails (e.g. Gmail). Uses PHPMailer";
$lang["setup-if-smtpsecure"]="'', 'tls' or 'ssl'. For Gmail, 'tls' or 'ssl' is required.";
$lang["setup-if-smtphost"]="Hostname, e.g. 'smtp.gmail.com'.";
$lang["setup-if-smtpport"]="Port number, e.g. 465 for Gmail using SSL.";
$lang["setup-if-smtpauth"]="Send credentials to SMTP server (false to use anonymous access)";
$lang["setup-if-smtpusername"]="Username (full email address).";
$lang["setup-if-smtppassword"]="Password";
$lang["design-options"]="Design Options";
$lang["use-slim-theme"]="Use the SlimHeader Design?";
$lang["setup-if_slimtheme"]="Use the SlimHeader design rather than the original design to display a thinner header bar with a linked logo by default.";
$lang["setup-login_to"]="Login to";
$lang["setup-configuration_file_output"]="Configuration file output";
$lang["more-information"]="More information";
$lang["setup-structuralplugins"]="System templates";
$lang["setup-headercolourstyleoverride"]="Custom header colour";

# Collection log - actions
$lang["collectionlog"]="Collection log";
$lang["collectionlogheader"]="Collection log - %collection"; # %collection will be replaced, e.g. Collection Log - My Collection
$lang["collectionlog-r"]="Removed resource";
$lang["collectionlog-R"]="Removed all resources";
$lang["collectionlog-D"]="Deleted all resources";
$lang["collectionlog-d"]="Deleted resource"; // this shows external deletion of any resources related to the collection.
$lang["collectionlog-a"]="Added resource";
$lang["collectionlog-c"]="Added resource (copied)";
$lang["collectionlog-m"]="Added resource comment";
$lang["collectionlog-*"]="Added resource rating";
$lang["collectionlog-S"]="Shared collection with "; //  + notes field
$lang["collectionlog-E"]="E-mailed collection to ";//  + notes field
$lang["collectionlog-s"]="Shared resource with ";//  + notes field
$lang["collectionlog-T"]="Stopped sharing collection with ";//  + notes field
$lang["collectionlog-t"]="Stopped access to resource by ";//  + notes field
$lang["collectionlog-X"]="Collection deleted";
$lang["collectionlog-b"]="Batch transformed";
$lang["collectionlog-A"]="Changed access to "; // +notes field
$lang["collectionlog-Z"]="Collection downloaded";

$lang["viewuncollectedresources"]="View resources not used in collections";

# Collection requesting
$lang["requestcollection"]="Request collection";

# Metadata report
$lang["metadata-report"]="Metadata report";

# Video Playlist
$lang["videoplaylist"]="Video playlist";

$lang["collection"]="Collection";
$lang["idecline"]="I decline"; # For terms and conditions

$lang["mycollection_notpublic"]="You cannot make your 'My Collection' into a featured or public collection. Please create a new collection for this purpose.";

$lang["resourcemetadata"]="Resource metadata";
$lang["columnheader-expires"]=$lang["expires"]="Expires";
$lang["expires-date"]="Expires: %date%"; # %date will be replaced, e.g. Expires: Never
$lang["never"]="Never";

$lang["approved"]="Approved";
$lang["notapproved"]="Not approved";

$lang["userrequestnotification3"]="If this is a valid request, click the link to review the details and approve the user account.";

$lang["ticktoapproveuser"]="You must tick the box to approve this user if you wish to enable this account";

$lang["managerequestsorders"]="Manage requests / orders";
$lang["editrequestorder"]="Edit request / order";
$lang["requestorderid"]="Request / order ID";
$lang["viewrequesturl"]="To view this request, click the link below:";
$lang["requestreason"]="Reason for request";

$lang["resourcerequeststatus0"]="Pending";
$lang["resourcerequeststatus1"]="Approved";
$lang["resourcerequeststatus2"]="Declined";

$lang["ppi"]="PPI"; # (Pixels Per Inch - used on the resource download options list).

$lang["useasthemethumbnail"]="Use this resource as a featured collection category thumbnail?";
$lang["sessionexpired"]="You have been automatically logged out because you were inactive for more than 30 minutes. Please enter your login details to continue.";

$lang["resourcenotinresults"]="The current resource is no longer within your active search results so next/previous navigation is not possible.";
$lang["publishstatus"]="Save with publish status:";
$lang["addnewcontent"]="New content (page, name)";
$lang["hitcount"]="Hit count";
$lang["downloads"]="Downloads";

$lang["addremove"]="Add/remove";

##  Translations for standard log entries
$lang["all_users"]="all users";
$lang["new_resource"]="new resource";

$lang["invalidextension_mustbe"]="Invalid extension, must be";
$lang["invalidextension_mustbe-extensions"]="Invalid extension, must be %EXTENSIONS."; # Use %EXTENSIONS, %extensions or %Extensions as a placeholder. The placeholder will be replaced with the filename extensions, using the same case. E.g. "Invalid extension, must be %EXTENSIONS" -> "Invalid extension, must be JPG"
$lang["allowedextensions"]="Allowed extensions";
$lang["allowedextensions-extensions"]="Allowed extensions: %EXTENSIONS"; # Use %EXTENSIONS, %extensions or %Extensions as a placeholder. The placeholder will be replaced with the filename extensions, using the same case. E.g. "Allowed Extensions: %EXTENSIONS" -> "Allowed Extensions: JPG, PNG"

$lang["alternativebatchupload"]="Upload alternative files";
$lang["alternativelocalupload"]="Upload alternative files - fetch from local upload folder";

$lang["confirmdeletefieldoption"]="Are you sure you wish to DELETE this field option?";

$lang["cannotshareemptycollection"]="This collection is empty and cannot be shared.";	
$lang["cannotshareemptythemecategory"]="This featured collection category contains no featured collections and cannot be shared.";

$lang["requestall"]="Request all";
$lang["requesttype-email_only"]=$lang["resourcerequesttype0"]="Email only";
$lang["requesttype-managed"]=$lang["resourcerequesttype1"]="Managed request";
$lang["requesttype-payment_-_immediate"]=$lang["resourcerequesttype2"]="Payment - immediate";
$lang["requesttype-payment_-_invoice"]=$lang["resourcerequesttype3"]="Payment - invoice";

$lang["requestsent"]="Your resource request has been submitted for approval ";
$lang["requestsenttext"]="Your resource request has been submitted for approval and will be looked at shortly.";
$lang["requestupdated"]="Your resource request has been updated ";
$lang["requestassignedtouser"]="Your resource request has been assigned to % for approval.";
$lang["requestapprovedmail"]="Your request has been approved. Click the link below to view and download the requested resources.";
$lang["requestdeclinedmail"]="Sorry, your request for the resources in the collection below has been declined.";

$lang["resourceexpirymail"]="The following resources have expired:";
$lang["resourceexpiry"]="Resource expiry";

$lang["requestapprovedexpires"]="Your access to these resources will expire on";

$lang["pleasewaitsmall"]="(please wait)";
$lang["removethisfilter"]="(remove this filter)";

$lang["no_exif"]="Do not import embedded EXIF/IPTC/XMP metadata for this upload";
$lang["difference"]="Difference";
$lang["viewdeletedresources"]="View deleted resources";
$lang["finaldeletion"]="This resource is already in the 'deleted' state. This action will completely remove the resource from the system.";
$lang["diskerror"]="Quota exceeded";

$lang["nocookies"]="A cookie could not be set correctly. Please make sure you have cookies enabled in your browser settings.";

$lang["selectedresourceslightroom"]="Selected resources (Lightroom compatible list):";

# Plugins Manager
$lang['plugins-noneinstalled'] = "No plugins currently activated.";
$lang['plugins-noneavailable'] = "No plugins currently available.";
$lang['plugins-availableheader'] = 'Available plugins';
$lang['plugins-installedheader'] = 'Currently activated plugins';
$lang['plugins-author'] = 'Author';
$lang['plugins-version'] = 'Version';
$lang['plugins-instversion'] = 'Installed version';
$lang['plugins-uploadheader'] = 'Upload plugin';
$lang['plugins-uploadtext'] = 'Select a .rsp file to install.';
$lang['plugins-deactivate'] = 'Deactivate';
$lang['plugins-moreinfo'] = 'More&nbsp;Info';
$lang['plugins-activate'] = 'Activate';
$lang['plugins-purge'] = 'Purge&nbsp;configuration';
$lang['plugins-rejmultpath'] = 'Archive contains multiple paths. (Security Risk)';
$lang['plugins-rejrootpath'] = 'Archive contains absolute paths. (Security Risk)';
$lang['plugins-rejparentpath'] = 'Archive contain parent paths (../). (Security Risk)';
$lang['plugins-rejmetadata'] = 'Archive description file not found.';
$lang['plugins-rejarchprob'] = 'There was a problem extracting the archive:';
$lang['plugins-rejfileprob'] = 'Uploaded plugin must be a .rsp file.';
$lang['plugins-rejremedy'] = 'If you trust this plugin you can install it manually by expanding the archive into your plugins directory.';
$lang['plugins-uploadsuccess'] = 'Plugin uploaded succesfully.';
$lang['plugins-headertext'] = 'Plugins extend the functionality of ResourceSpace.';
$lang['plugins-legacyinst'] = 'Activated via config.php';
$lang['plugins-uploadbutton'] = 'Upload plugin';
$lang['plugins-download'] = 'Download&nbsp;configuration';
$lang['plugins-upload-title'] = 'Get configuration from file';
$lang['plugins-upload'] = 'Upload configuration';
$lang['plugins-getrsc'] = 'File to use:';
$lang['plugins-saveconfig'] = 'Save configuration';
$lang['plugins-saveandexit'] = 'Save and exit';
$lang['plugins-didnotwork'] = 'Sorry, that didn\'t work. Choose a valid .rsc file for this plugin and then click \'Upload Configuration\' button.';
$lang['plugins-goodrsc'] = 'Configuration uploaded ok. Click \'Save Configuration\' button to save.';
$lang['plugins-badrsc'] = 'Sorry, that wasn\'t a valid .rsc file.';
$lang['plugins-wrongplugin'] = 'Sorry, that\'s an .rsc file for the %plugin plugin. Choose one for this plugin.'; // %plugin is replaced by the name of the plugin being configured.
$lang['plugins-configvar'] = 'Sets configuration variable: $%cvn'; //%cvn is replaced by the name of the config variable being set

#Location Data
$lang['location-title'] = 'Location data';
$lang['location-add'] = 'Add location';
$lang['location-edit'] = 'Edit location';
$lang['location-details'] = 'Use "Drag mode" to switch between pin positioning and panning. Use zoom controls to zoom in and out. Click Save to save pin position and zoom level.';
$lang['location-noneselected']="No location selected";
$lang['location'] = 'Location';
$lang['mapzoom'] = 'Map zoom';
$lang['openstreetmap'] = "OpenStreetMap";
$lang['google_terrain'] = "Google terrain";
$lang['google_default_map'] = "Google default map";
$lang['google_satellite'] = "Google satellite";
$lang["markers"] = "Markers";

$lang["publiccollections"]="Public collections";
$lang["viewmygroupsonly"]="View my groups only";
$lang["usemetadatatemplate"]="Use metadata template";
$lang["undometadatatemplate"]="(undo template selection)";

$lang["accountemailalreadyexists"]="An account with that e-mail address already exists";

$lang["backtothemes"]="Back to featured collections";
$lang["downloadreport"]="Download report";

#Bug Report Page
$lang['reportbug']="Prepare bug report for ResourceSpace team";
$lang['reportbug-detail']="The following information has been compiled for inclusion in the bug report.  You'll be able to change all values before submitting a report.";
$lang['reportbug-login']="NOTE: Click here to login to the bug tracker BEFORE clicking prepare.";
$lang['reportbug-preparebutton']="Prepare bug report";

$lang["enterantispamcode"]="<strong>Anti-Spam</strong> <sup>*</sup><br /> Please enter the following code:";

$lang["groupaccess"]="Group access";
$lang["plugin-groupsallaccess"]="This plugin is activated for all groups";
$lang["plugin-groupsspecific"]="This plugin is activated for the selected groups only";


$lang["associatedcollections"]="Associated collections";
$lang["emailfromuser"]="Send the e-mail from ";
$lang["emailfromsystem"]="If unchecked, email will be sent from the system address: ";



$lang["previewpage"]="Preview page";
$lang["nodownloads"]="No downloads";
$lang["uncollectedresources"]="Resources not used in collections";
$lang["nowritewillbeattempted"]="No write will be attempted";
$lang["notallfileformatsarewritable"]="Not all file formats are writable by exiftool";
$lang["filetypenotsupported"]="%EXTENSION filetype not supported"; # Use %EXTENSION, %extension or %Extension as a placeholder. The placeholder will be replaced with the filename extension, using the same case. E.g. "%EXTENSION filetype not supported" -> "JPG filetype not supported"
$lang["exiftoolprocessingdisabledforfiletype"]="Exiftool processing disabled for file type %EXTENSION"; # Use %EXTENSION, %extension or %Extension as a placeholder. The placeholder will be replaced with the filename extension, using the same case. E.g. "Exiftool processing disabled for file type %EXTENSION" -> "Exiftool processing disabled for file type JPG"
$lang["nometadatareport"]="No metadata report";
$lang["metadatawritewillbeattempted"]="Metadata write will be attempted.";
$lang["metadatatobewritten"]="Metadata which will be written";
$lang["embeddedvalue"]="Embedded value";
$lang["exiftooltag"]="Exiftool tag";
$lang["error"]="Error";
$lang["exiftoolnotfound"]="Could not find Exiftool";
$lang["existing_tags"]="Existing Exiftool tags";
$lang["new_tags"]="New Exiftool tags (which will be added upon download)";
$lang["date_of_download"]="[Date of download]";
$lang["field_ref_and_name"]="%ref% - %name%"; # %ref% and %name% will be replaced, e.g. 3 – Country

$lang["indicateusage"]="Please describe your planned use for this resource.";
$lang["usage"]="Usage";
$lang["usagecomments"]="Usage";
$lang["indicateusagemedium"]="Usage medium";
$lang["usageincorrect"]="You must describe the planned usage and select a medium";

$lang["savesearchassmartcollection"]="Save search as smart collection";
$lang["smartcollection"]="Smart collection";
$lang["dosavedsearch"]="Do saved search";


$lang["uploadertryjava"]="Use the legacy Java uploader.";
$lang["uploadertryplupload"]="<strong>NEW</strong> - Try out the new style uploader.";
$lang["getjava"]="To ensure that you have the latest Java software on your system, visit the Java website.";

$lang["all"]="All";
$lang["allresourcessearchbar"]="All resources";
$lang["allcollectionssearchbar"]="All collections";
$lang["backtoresults"]="Back to results";
$lang["continuetoresults"]="Continue to results";

$lang["preview_all"]="Preview all";

$lang["usagehistory"]="Usage history";
$lang["usagebreakdown"]="Usage breakdown";
$lang["usagetotal"]="Total downloads";
$lang["usagetotalno"]="Total number of downloads";
$lang["ok"]="OK";

$lang["random"]="Random";
$lang["userratingstatsforresource"]="User rating stats for resource";
$lang["average"]="Average";
$lang["popupblocked"]="The popup has been blocked by your browser.";
$lang["closethiswindow"]="Close this window";

$lang["requestaddedtocollection"]="This resource has been added to your current collection. You can request the items in your collection by clicking \'Request All\' on the collection bar below.";

# E-commerce text
$lang["buynow"]="Buy now";
$lang["yourbasket"]="Your basket";
$lang["addtobasket"]="Add to basket";
$lang["yourbasketisempty"]="Your basket is empty.";
$lang["yourbasketcontains-1"]="Your basket contains 1 item.";
$lang["yourbasketcontains-2"]="Your basket contains %qty items."; # %qty will be replaced, e.g. Your basket contains 3 items.
$lang["buy"]="Buy";
$lang["buyitemaddedtocollection"]="This resource has been added to your basket. You can purchase all the items in your basket by clicking \'Buy Now\' below.";
$lang["buynowintro"]="Please select the sizes you require.";
$lang["nodownloadsavailable"]="Sorry, there are no downloads available for this resource.";
$lang["proceedtocheckout"]="Proceed to checkout";
$lang["totalprice"]="Total price";
$lang["price"]="Price";
$lang["waitingforpaymentauthorisation"]="Sorry, we have not yet received the payment authorisation. Please wait a few moments then click 'reload' below.";
$lang["reload"]="Reload";
$lang["downloadpurchaseitems"]="Download purchased items";
$lang["downloadpurchaseitemsnow"]="Please use the links below to download your purchased items immediately.<br><br>Do not navigate away from this page until you have downloaded all the items.";
$lang["alternatetype"]="Alternative type";
$lang["viewpurchases"]="My purchases";
$lang["viewpurchasesintro"]="To access previously purchased resources please use the links below.";
$lang["orderdate"]="Order date";
$lang["removefrombasket"]="Remove from basket";
$lang["total-orders-0"] = "<strong>Total: 0</strong> orders";
$lang["total-orders-1"] = "<strong>Total: 1</strong> order";
$lang["total-orders-2"] = "<strong>Total: %number</strong> orders"; # %number will be replaced, e.g. Total: 5 Orders
$lang["purchase_complete_email_admin"] = "Notification of purchase";
$lang["purchase_complete_email_admin_body"] = "The following purchase has been completed.";
$lang["purchase_complete_email_user"] = "Confirmation of purchase";
$lang["purchase_complete_email_user_body"] = "Thanks for your purchase. Please use the link below to access your purchased items.";
$lang["purchase_email_address"] = "Please enter a valid email address if you wish to receive confirmation of your purchase";


$lang["subcategories"]="Subcategories";
$lang["subcategory"]="Subcategory";
$lang["back"]="Back";

$lang["pleasewait"]="Please wait...";

$lang["autorotate"]="Autorotate images?";

# Reports
# Report names (for the default reports)
$lang["report-keywords_used_in_resource_edits"]="Keywords used in resource edits";
$lang["report-keywords_used_in_searches"]="Keywords used in searches";
$lang["report-resource_download_summary"]="Resource download summary";
$lang["report-resource_views"]="Resource views";
$lang["report-resources_sent_via_e-mail"]="Resources sent via e-mail";
$lang["report-resources_added_to_collection"]="Resources added to collection";
$lang["report-resources_created"]="Resources created";
$lang["report-resources_with_zero_downloads"]="Resources with zero downloads";
$lang["report-resources_with_zero_views"]="Resources with zero views";
$lang["report-resource_downloads_by_group"]="Resource downloads by group";
$lang["report-resource_download_detail"]="Resource download detail";
$lang["report-user_details_including_group_allocation"]="User details including group allocation";
$lang["report-expired_resources"]="Expired resources";
$lang['report_delete_periodic_email_link'] = 'To delete this report, click the link below:';
$lang['report_periodic_email_delete_title'] = 'Delete periodic email';
$lang['report_periodic_email_delete_confirmation'] = 'Please confirm you want to delete it';
$lang['deleted'] = 'Deleted';
$lang['report_periodic_email_deletion_confirmed'] = 'Periodic report has been deleted';
$lang['report_periodic_email_option_me'] = 'me';
$lang['report_periodic_email_option_all_users'] = 'all users';
$lang['report_periodic_email_option_selected_user_groups'] = 'selected user group(s)';
$lang['report_periodic_email_unsubscribe_title'] = 'Unsubscribe from periodic emails';
$lang['report_periodic_email_unsubscribe_confirmation'] = 'Please confirm you would like to unsubscribe';


#Column headers (for the default reports)
$lang["columnheader-keyword"]="Keyword";
$lang["columnheader-entered_count"]="Entered count";
$lang["columnheader-searches"]="Searches";
$lang["columnheader-date_and_time"]="Date / time";
$lang["columnheader-downloaded_by_user"]="Downloaded by user";
$lang["columnheader-user_group"]="User group";
$lang["columnheader-resource_title"]="Resource title";
$lang["columnheader-title"]="Title";
$lang["columnheader-downloads"]="Downloads";
$lang["columnheader-group_name"]="Group name";
$lang["columnheader-resource_downloads"]="Resource downloads";
$lang["columnheader-views"]="Views";
$lang["columnheader-added"]="Added";
$lang["columnheader-creation_date"]="Creation date";
$lang["columnheader-sent"]="Sent";
$lang["columnheader-last_seen"]="Last seen";

$lang["period"]="Period";
$lang["lastndays"]="Last ? days"; # ? is replaced by the system with the number of days, for example "Last 100 days".
$lang["specificdays"]="Specific number of days";
$lang["specificdaterange"]="Specific date range";
$lang["to"]="to";

$lang["emailperiodically"]="Create new periodic e-mail";
$lang["emaileveryndays"]="E-mail this report every ? days to:";
$lang["newemailreportcreated"]="A new periodic e-mail has been created. You can cancel this using the link at the bottom of the e-mail.";
$lang["unsubscribereport"]="To unsubscribe from this report, click the link below:";
$lang["unsubscribed"]="Unsubscribed";
$lang["youhaveunsubscribedreport"]="You have been unsubscribed from the periodic report e-mail.";
$lang["sendingreportto"]="Sending report to";
$lang["reportempty"]="No matching data was found for the selected report and period.";

$lang["purchaseonaccount"]="Add to account";
$lang["areyousurepayaccount"]="Are you sure you wish to add this purchase to your account?";
$lang["accountholderpayment"]="Account Holder Payment";
$lang["subtotal"]="Subtotal";
$lang["discountsapplied"]="Discounts applied";
$lang["log-p"]="Purchased resource";
$lang["viauser"]="via user";
$lang["close"]="Close";

# Installation Check
$lang["repeatinstallationcheck"]="Repeat installation check";
$lang["shouldbeversion"]="should be ? or greater"; # E.g. "should be 4.4 or greater"
$lang["phpinivalue"]="PHP.INI value for '?'"; # E.g. "PHP.INI value for 'memory_limit'"
$lang["writeaccesstofilestore"]="Write access to $storagedir";
$lang["nowriteaccesstofilestore"]="$storagedir not writable";
$lang["writeaccesstoplugins"]="Write access to /plugins";
$lang["nowriteaccesstoplugins"]="/plugins folder not writable";
$lang["writeaccesstohomeanim"]="Write access to $homeanim_folder";
$lang["nowriteaccesstohomeanim"]="$homeanim_folder not writable. Open permissions to enable home animation cropping feature in the transform plugin.";
$lang["blockedbrowsingoffilestore"]="Blocked browsing of 'filestore' directory";
$lang["noblockedbrowsingoffilestore"]="filestore folder appears to be browseable; remove 'Indexes' from Apache 'Options' list.";
$lang["execution_failed"]="Unexpected output when executing %command command. Output was '%output'.";  # %command and %output will be replaced, e.g. Execution failed; unexpected output when executing convert command. Output was '[stdout]'.
$lang["exif_extension"]="EXIF extension";
$lang["archiver_utility"]="Archiver utility";
$lang["zipcommand_deprecated"]="Using \$zipcommand is deprecated and replaced by \$collection_download and \$collection_download_settings.";
$lang["zipcommand_overridden"]="But please note that \$zipcommand is defined and overridden.";
$lang["lastscheduledtaskexection"]="Last scheduled task execution (days)";
$lang["executecronphp"]="Relevance matching will not be effective and periodic e-mail reports will not be sent. Ensure <a href='../batch/cron.php'>batch/cron.php</a> is executed at least once daily via a cron job or similar.";
$lang["shouldbeormore"]="should be ? or greater"; # E.g. should be 200M or greater
$lang["config_file"]="(config: %file)"; # %file will be replaced, e.g. (config: /etc/php5/apache2/php.ini)
$lang['large_file_support_64_bit'] = 'Large file support (64 bit platform)';
$lang['large_file_warning_32_bit'] = 'WARNING: Running 32 bit PHP. Files larger than 2GB will not be supported.';

$lang["starsminsearch"]="Stars (minimum)";
$lang["anynumberofstars"]="Any number of stars";
$lang["star"]="Star";
$lang["stars"]="Stars";

$lang["noupload"]="No upload";

# System Setup
# System Setup Tree Nodes (for the default setup tree)
$lang["treenode-root"]="Root";
$lang["treenode-group_management"]="Group management";
$lang["treenode-new_group"]="New group";
$lang["treenode-new_subgroup"]="New subgroup";
$lang["treenode-resource_types_and_fields"]="Manage resource types";
$lang["treenode-new_resource_type"]="New resource type";
$lang["treenode-new_field"]="New field";
$lang["treenode-reports"]="Reports";
$lang["treenode-new_report"]="New report";
$lang["treenode-downloads_and_preview_sizes"]="Downloads / preview sizes";
$lang["treenode-new_download_and_preview_size"]="New download / preview size";
$lang["treenode-database_statistics"]="Database statistics";
$lang["treenode-permissions_search"]="Permissions search";
$lang["treenode-no_name"]="(no name)";

$lang["treeobjecttype-preview_size"]="Preview size";

$lang["permissions"]="Permissions";

# System Setup File Editor
$lang["configdefault-title"]="(copy and paste options from here)";
$lang["config-title"]="(BE CAREFUL not to make syntax errors. If you break this file, you must fix server-side!)";

# System Setup Properties Pane
$lang["file_too_large"]="File too large";
$lang["field_updated"]="Field updated";
$lang["zoom"]="Zoom";
$lang["deletion_instruction"]="Leave blank and save to delete the file";
$lang["upload_file"]="Upload file";
$lang["item_deleted"]="Item deleted";
$lang["viewing_version_created_by"]="Viewing version created by";
$lang["on_date"]="on";
$lang["launchpermissionsmanager"]="Launch permissions manager";
$lang["confirm-deletion"]="Are you sure you want to delete?";
$lang["accept_png_gif_only"]="Only .png or .gif extensions accepted";
$lang["ensure_file_extension_match"]="Ensure file and extension match";

# Permissions Manager
$lang["permissionsmanager"]="Permissions manager";
$lang["backtogroupmanagement"]="Back to group management";
$lang["searching_and_access"]="Searching / access";
$lang["metadatafields"]="Metadata fields";
$lang["resource_creation_and_management"]="Resource creation / management";
$lang["themes_and_collections"]="Collections";
$lang["administration"]="Administration";
$lang["other"]="Other";
$lang["custompermissions"]="Custom permissions";
$lang["searchcapability"]="Search capability";
$lang["access_to_restricted_and_confidential_resources"]="Can download restricted resources and view confidential resources<br>(normally admin only)";
$lang["restrict_access_to_all_available_resources"]="Restrict access to all available resources";
$lang["can_make_resource_requests"]="Can make resource requests";
$lang["show_watermarked_previews_and_thumbnails"]="Show watermarked previews/thumbnails";
$lang["can_see_all_fields"]="Can see all fields";
$lang["can_see_field"]="Can see field";
$lang["can_edit_all_fields"]="Can edit all fields<br>(for editable resources)";
$lang["can_edit_field"]="Can edit field";
$lang["can_see_resource_type"]="Can see resource type";
$lang["restricted_access_only_to_resource_type"]="Restricted access only to resource type";
$lang["restricted_upload_for_resource_of_type"]="Restricted upload for resource of type";
$lang["edit_access_to_workflow_state"]="Edit access to workflow state";
$lang["edit_access_to_access"]="Edit access to Access state";
$lang["can_create_resources_and_upload_files-admins"]="Can create resources / upload files<br>(admin users; resources go to 'Active' state)";
$lang["can_create_resources_and_upload_files-general_users"]="Can create resources / upload files<br>(normal users; resources go to 'Pending Submission' state via My Contributions)";
$lang["can_delete_resources"]="Can delete resources<br>(to which the user has write access)";
$lang["can_manage_archive_resources"]="Can manage archive resources";
$lang["can_manage_alternative_files"]="Can manage alternative files";
$lang["can_tag_resources_using_speed_tagging"]="Can tag resources using 'Speed Tagging'<br>(if enabled in the configuration)";
$lang["enable_bottom_collection_bar"]="Enable bottom collection bar ('Lightbox')";
$lang["can_publish_collections_as_themes"]="Can publish collections as featured collections";
$lang["can_see_all_theme_categories"]="Can see all featured collection categories";
$lang["can_see_theme_category"]="Can see featured collection category";
$lang["can_see_theme_sub_category"]="Can see featured collection subcategory";
$lang["display_only_resources_within_accessible_themes"]="When searching, display only resources that exist within featured collections to which the user has access";
$lang["can_access_team_centre"]="Can access the admin area";
$lang["can_manage_research_requests"]="Can manage research requests";
$lang["can_manage_resource_requests"]="Can manage resource requests";
$lang["can_manage_content"]="Can manage content (intro/help text)";
$lang["can_bulk-mail_users"]="Can bulk-mail users";
$lang["can_manage_users"]="Can manage users";
$lang["can_manage_keywords"]="Can manage keywords";
$lang["can_access_system_setup"]="Can access the System Setup area";
$lang["can_change_own_password"]="Can change own account password";
$lang["can_manage_users"]="Can manage users";
$lang["can_manage_users_in_children_groups"]="Can manage users in children groups to the user's group only";
$lang["can_email_resources_to_own_and_children_and_parent_groups"]="Can email resources to users in the user's own group, children groups and parent group only";

$lang["nodownloadcollection"]="You do not have access to download any of the resources in this collection.";

$lang["progress"]="Progress";
$lang["ticktodeletethisresearchrequest"]="Tick to delete this request";

$lang["done"]="Done.";

$lang["latlong"]="Lat / long";
$lang["geographicsearch"]="Geographic search";
$lang["geographicsearchresults"]="Geographic search results";

$lang["geographicsearch_help"]="Drag to select a search area.";

$lang["purge"]="Purge";
$lang["purgeuserstitle"]="Purge users";
$lang["purgeusers"]="Purge users";
$lang["purgeuserscommand"]="Delete user accounts that have been inactive for the last % months and were created prior to this period.";
$lang["purgeusersconfirm"]="This will delete % user accounts. Are you sure?";
$lang["pleaseenteravalidnumber"]="Please enter a valid number";
$lang["purgeusersnousers"]="There are no users to purge.";

$lang["editallresourcetypewarning"]="Warning: changing the resource type will delete any resource type specific metadata currently stored for the selected resources.";
$lang["editresourcetypewarning"]="Warning: changing the resource type will delete any resource type specific metadata currently stored for this resource.";

$lang["geodragmode"]="Drag mode";
$lang["geodragmodearea"]="position pin";
$lang["geodragmodeareaselect"]="select search area";
$lang["geodragmodepan"]="pan";

$lang["substituted_original"] = "substituted original";
$lang["use_original_if_size"] = "Use original if selected size is unavailable?";

$lang["originals-available-0"] = "available"; # 0 (originals) available
$lang["originals-available-1"] = "available"; # 1 (original) available
$lang["originals-available-2"] = "available"; # 2+ (originals) available

$lang["inch-short"] = "in";
$lang["centimetre-short"] = "cm";
$lang["megapixel-short"]="MP";
$lang["at-resolution"] = "@"; # E.g. 5.9 in x 4.4 in @ 144 PPI

$lang["deletedresource"] = "Deleted resource";
$lang["deletedresources"] = "Deleted resources";
$lang["nopreviewresources"]= "Resources without previews";
$lang["action-delete_permanently"] = "Delete permanently";

$lang["horizontal"] = "Horizontal";
$lang["vertical"] = "Vertical";

$lang["cc-emailaddress"] = "CC %emailaddress"; # %emailaddress will be replaced, e.g. CC [your email address]
$lang["list-recipients-label"] = "List all recipients in e-mail?";
$lang["list-recipients"] = "This message was sent to the following e-mail addresses:";

$lang["sort"] = "Sort";
$lang["sortcollection"] = "Sort collection";
$lang["emptycollection"] = "Remove resources";
$lang["deleteresources"] = "Delete resources";
$lang["emptycollectionareyousure"]="Are you sure you want to remove all resources from this collection?";

$lang["error-cannoteditemptycollection"]="You cannot edit an empty collection.";
$lang["error-permissiondenied"]="Permission denied.";
$lang["error-permissions-login"]="Please log in to access this page";
$lang["error-oldphp"] = "Requires PHP version %version or higher."; # %version will be replaced with, e.g., "5.2"
$lang["error-collectionnotfound"]="Collection not found.";


$lang["no-options-available"]="No options available";
$lang["header-upload-subtitle"] = "Step %number: %subtitle"; # %number, %subtitle will be replaced, e.g. Step 1: Specify Default Content For New Resources
$lang["local_upload_path"] = "Local upload folder";
$lang["ftp_upload_path"] = "FTP folder";
$lang["foldercontent"] = "Folder content";
$lang["intro-local_upload"] = "Select one or more files from the local upload folder and click  'Upload'. Once the files are uploaded they can be deleted from the upload folder.";
$lang["intro-ftp_upload"] = "Select one or more files from the FTP folder and click  'Upload'.";
$lang["intro-java_upload"] = "Click  'Browse' to locate one or more files and then click  'Upload'.";
$lang["intro-java_upload-replace_resource"] = "Click  'Browse' to locate a file and then click  'Upload'.";
$lang["intro-single_upload"] = "Click  'Browse' to locate a file and then click  'Upload'.";
$lang["intro-plupload"] = "Click 'Add files' to locate one or more files and then click 'Start upload'.";
$lang["intro-plupload_dragdrop"] = "Drag and drop or click 'Add files' to locate one or more files and then click 'Start upload'.";
$lang["intro-plupload_upload-replace_resource"] = "Click 'Add files' to locate a file and then click 'Start upload'.";
$lang["intro-batch_edit"] = "Please specify the default upload settings and the default values for the metadata of the resources you are about to upload.";
$lang["plupload-maxfilesize"] = "The maximum allowed upload file size is %s.";
$lang["pluploader_warning"]="Your browser may not support very large file uploads. If you are having problems please try either upgrading your browser or using the links below.";
$lang["getsilverlight"]="To ensure that you have the latest version of Silverlight on your system, visit the Microsoft Silverlight site.";
$lang["getbrowserplus"]="To get the latest BrowserPlus software, visit the Yahoo BrowserPlus website.";
$lang["pluploader_usejava"]="Use the legacy Java uploader.";

$lang["collections-1"] = "(<strong>1</strong> Collection)";
$lang["collections-2"] = "(<strong>%d</strong> Collections with <strong>%d</strong> items)"; # %number will be replaced, e.g. 3 Collections
$lang["total-collections-0"] = "<strong>Total: 0</strong> Collections";
$lang["total-collections-1"] = "<strong>Total: 1</strong> Collection";
$lang["total-collections-2"] = "<strong>Total: %number</strong> Collections"; # %number will be replaced, e.g. Total: 5 Collections
$lang["owned_by_you-0"] = "(<strong>0</strong> owned by you)";
$lang["owned_by_you-1"] = "(<strong>1</strong> owned by you)";
$lang["owned_by_you-2"] = "(<strong>%mynumber</strong> owned by you)"; # %mynumber will be replaced, e.g. (2 owned by you)

$lang["listresources"]= "Resources:";
$lang["action-log"]="View log";

$lang["saveuserlist"]="Save this list";
$lang["deleteuserlist"]="Delete this list";
$lang["typeauserlistname"]="Type a user list name...";
$lang["loadasaveduserlist"]="Load a saved user list";

$lang["searchbypage"]="Search page";
$lang["searchbyname"]="Search name";
$lang["searchbytext"]="Search text";
$lang["saveandreturntolist"]="Save and return to list";
$lang["backtomanagecontent"]="Back to manage content";
$lang["editcontent"]="Edit content";

$lang["confirmcollectiondownload"]="Please wait while we create the archive. This might take a while, depending on the total size of your resources.";
$lang["collectiondownloadinprogress"]='Please wait while we create the archive. This might take a while, depending on the total size of your resources.<br /><br />To continue working, you may  <a href=\"home.php\" target=\"_blank\">> Open a New Browser Window</a><br /><br />';
$lang["preparingzip"]="Preparing...";
$lang["filesaddedtozip"]="files copied";
$lang["fileaddedtozip"]="file copied";
$lang["zipping"]="Zipping";
$lang["zipcomplete"]="Your Zip file download should have started. You may leave this page.";

$lang["starttypingkeyword"]="Start typing keyword...";
$lang["createnewentryfor"]="Create new entry for";
$lang["confirmcreatenewentryfor"]="Are you sure you wish to create a new keyword list entry for '%%'?";
$lang["noentryexists"]="No entry exists for";

$lang["editresourcepreviews"]="Edit Resource Previews";
$lang["can_assign_resource_requests"]="Can assign resource requests to others";
$lang["can_be_assigned_resource_requests"]="Can be assigned resource requests (also; can only see resource requests assigned to them in the Manage Resource Requests area)";

$lang["declinereason"]="Reason for declining";
$lang["approvalreason"]="Reason for approving";

$lang["requestnotassignedtoyou"]="Sorry, this request is no longer assigned to you. It is now assigned to user %.";
$lang["requestassignedtoyou"]="Resource request assigned to you";
$lang["requestassignedtoyoumail"]="A resource request has been assigned to you for approval. Please use the link below to approve or deny the resource request.";

$lang["manageresources-overquota"]="Resource management disabled - you have exceeded your disk usage quota";
$lang["searchitemsdiskusage"]="Disk space used by results";
$lang['collection_disk_usage'] = 'Disk space used by all resources';
$lang["matchingresourceslabel"]="Matching resources";

# CSV Export of Search results
$lang['csvExportResultsMetadata'] = 'CSV Export - Results metadata';
$lang['csvAddMetadataCSVToArchive'] = 'Include metadata CSV file to the archive?';

$lang["saving"]="Saving...";
$lang["saved"]="Saved";
$lang["changessaved"]="Changes Saved";

$lang["resourceids"]="Resource ID(s)";

$lang["warningrequestapprovalfield"]="!!! Warning - resource ID % - please take note of the following before approving !!!";

$lang["yyyy-mm-dd"]="YYYY-MM-DD";

$lang["resources-with-requeststatus0-0"]="(0 pending)"; # 0 Pending
$lang["resources-with-requeststatus0-1"]="(1 pending)"; # 1 Pending
$lang["resources-with-requeststatus0-2"]="(%number pending)"; # %number will be replaced, e.g. 3 Pending
$lang["researches-with-requeststatus0-0"]="(0 unassigned)"; # 0 Unassigned
$lang["researches-with-requeststatus0-1"]="(1 unassigned)"; # 1 Unassigned
$lang["researches-with-requeststatus0-2"]="(%number unassigned)"; # %number will be replaced, e.g. 3 Unassigned

$lang["byte-symbol"]="B";
$lang["kilobyte-symbol"]="KB";
$lang["megabyte-symbol"]="MB";
$lang["gigabyte-symbol"]="GB";
$lang["terabyte-symbol"]="TB";

$lang["upload_files"]="Upload files";
$lang["upload_files-to_collection"]="Upload files (to the collection '%collection')"; # %collection will be replaced, e.g. Upload Files (to the collection 'My Collection')

$lang["ascending"] = "Ascending";
$lang["descending"] = "Descending";
$lang["sort-type"] = "Sort type";
$lang["collection-order"] = "Collection order";
$lang["save-error"]="!! Error auto saving - please save manually !!";

$lang["theme_home_promote"]="Promote on the home page?";
$lang["theme_home_page_text"]="Home page text";
$lang["theme_home_page_image"]="Home page image";
$lang["ref-title"] = "%ref - %title"; # %ref and %title will be replaced, e.g. 3 - Sunset

$lang["error-pageload"] = "Sorry, there has been an error loading this page. If you are performing a search please try refining your search query. If the problem persists please contact your system administrator";

$lang["copy-field"]="Copy field";
$lang["copy-to-resource-type"]="Copy to resource type";
$lang["synchronise-changes-with-this-field"]="Synchronise changes with this field";
$lang["copy-completed"]="Copy completed. New field has ID ?.";

$lang["nothing-to-display"]="Nothing to display.";
$lang["report-send-all-users"]="Send the report to all active users?";

$lang["contactsheet-single"]="1 per page";
$lang["contact_sheet-include_header_option"]="Include header?";
$lang["contact_sheet-add_link_option"]="Add clickable links to resource view page?";
$lang["contact_sheet-add_logo_option"]="Add logo to top of each page?";
$lang["contact_sheet-single_select_size"]="Image quality";

$lang["caps-lock-on"]="Warning! Caps Lock is on";
$lang["collectionnames"]="Collection names";
$lang["findcollectionthemes"]="Featured collections";
$lang["upload-options"]="Upload options";
$lang["user-preferences"]="My preferences";
$lang["allresources"]="All Resources";

$lang["smart_collection_result_limit"]="Smart collection: result count limit";

$lang["untaggedresources"]="Resources with no %field data";

$lang["secureyouradminaccount"]="Welcome! To secure your server, you are required to change the default password now.";
$lang["resources-all-types"]="Resources of all types";
$lang["search-mode"]="Search for...";
$lang["action-viewmatchingresults"]="View matching results";
$lang["nomatchingresults"]="No matching results";
$lang["matchingresults"]="matching results"; # e.g. 17 matching results=======
$lang["resources"]="Resources";
$lang["share-resource"]="Share resource";
$lang["scope"]="Scope";
$lang["downloadmetadata"]="Download Metadata";
$lang["downloadingmetadata"]="Downloading Metadata";
$lang["file-contains-metadata"]="The file you are now downloading contains all of the Metadata for this resource.";
$lang["metadata"]="Metadata";
$lang["textfile"]="Text File";
$lang['pdffile'] = 'PDF File';
$lang['metadata-pdf-title'] = 'Metadata download for resource';

# Comments field titles, prompts and default placeholders
$lang['comments_box-title']="Comments";
$lang['comments_box-policy']="Comments Policy";
$lang['comments_box-policy-placeholder']="Please add text to comments_policy entry in the site text";		# only shown if Admin User and no policy set
$lang['comments_in-response-to']="in response to";
$lang['comments_respond-to-this-comment']="Reply";
$lang['comments_in-response-to-on']="on";
$lang['comments_anonymous-user']="Anonymous";
$lang['comments_submit-button-label']="Submit";
$lang['comments_body-placeholder']="Add a comment...";
$lang['comments_fullname-placeholder']="Your Name (required)";
$lang['comments_email-placeholder']="Your E-mail (required)";
$lang['comments_website-url-placeholder']="Website";
$lang['comments_flag-this-comment']="Flag this comment";
$lang['comments_flag-has-been-flagged']="This comment has been flagged";
$lang['comments_flag-reason-placeholder']="Reason to flag comment";
$lang['comments_validation-fields-failed']="Please ensure all mandatory fields are correctly completed";
$lang['comments_block_comment_label']="block comment";
$lang['comments_flag-email-default-subject']="Notification of flagged comment";
$lang['comments_flag-email-default-body']="This comment has been flagged:";
$lang['comments_flag-email-flagged-by']="Flagged by:";
$lang['comments_flag-email-flagged-reason']="Reason for flagging:";
$lang['comments_hide-comment-text-link']="Remove Comment";
$lang['comments_hide-comment-text-confirm']="Are you sure that you want to remove the text for this comment?";

# testing updated request emails
$lang["request_id"]="Request ID:";
$lang["user_made_request"]="The following user has made a request:";

$lang["download_collection"]="Download Collection";

$lang["all-resourcetypes"] = "resources"; # Will be used as %resourcetypes% if all resourcetypes are searched.
$lang["all-collectiontypes"] = "collections"; # Will be used as %collectiontypes% if all collection types are searched.
$lang["resourcetypes-no_collections"] = "All %resourcetypes%"; # Use %RESOURCETYPES%, %resourcetypes% or %Resourcetypes% as a placeholder. The placeholder will be replaced with the resourcetype in plural (or $lang["all-resourcetypes"]), using the same case. E.g. "All %resourcetypes%" -> "All photos"
$lang["no_resourcetypes-collections"] = "All %collectiontypes%"; # Use %COLLECTIONTYPES%, %collectiontypes% or %Collectiontypes% as a placeholder. The placeholder will be replaced with the collectiontype (or $lang["all-collectiontypes"]), using the same case. E.g. "All %collectiontypes%" -> "All my collections"
$lang["resourcetypes-collections"] = "All %resourcetypes% and all %collectiontypes%"; # Please find the comments for $lang["resourcetypes-no_collections"] and $lang["no_resourcetypes-collections"]!
$lang["resourcetypes_separator"] = ", "; # The separator to be used when converting the array of searched resourcetype to a string. E.g. ", " -> "photos, documents"
$lang["collectiontypes_separator"] = ", "; # The separator to be used when converting the array of searched collections to a string. E.g. ", " -> "public collections, themes"
$lang["hide_view_access_to_workflow_state"]="Block access to workflow state";
$lang["collection_share_status_warning"]="Warning - this collection has resources in the following states, please check that these resources will be accessible to other users";
$lang["contactadmin"]="Contact administrator";
$lang["contactadminintro"]="Please enter your message and click 'Send'.";
$lang["contactadminemailtext"]=" has emailed you about a resource";
$lang["showgeolocationpanel"]="Show location information";
$lang["hidegeolocationpanel"]="Hide location information";
$lang["download_usage_option_blocked"]="This usage option is not available. Please check with your administrator";

$lang["tagcloudtext"]="With which metadata terms have people been tagging resources? The more a term has been used, the larger it appears in the cloud.<br /><br />You may also click on any term below to execute the search.";
$lang["tagcloud"]="Tag Cloud";

$lang["email_link_expires_never"]="This link will never expire.";
$lang['email_link_expires_date']="This link will expire on ";
$lang['email_link_expires_days']="Link expires: ";
$lang['expire_days']='days';
$lang['expire_day']='day';
$lang["collection_order_description"]="Collection order";
$lang["view_shared_collections"]="View shared collections";
$lang["shared_collections"]="Shared collections";
$lang["internal"]="Internal";
$lang["managecollectionslink"]="Manage collections";
$lang["showcollectionindropdown"]="Show in collection bar";
$lang["sharerelatedresources"]="Include related resources.<br>A new collection will be created and shared if any of these are selected";
$lang["sharerelatedresourcesaddremove"]="If sharing related resources, allow other users to add/remove resources from the new collection";
$lang["create_empty_resource"]="Skip upload and create a new resource with no associated file";
$lang["entercollectionname"]="Enter name, then press Return";
$lang["embedded_metadata"]="Embedded metadata";
$lang["embedded_metadata_extract_option"]="Extract";
$lang["embedded_metadata_donot_extract_option"]="Do not extract";
$lang["embedded_metadata_append_option"]="Append";
$lang["embedded_metadata_prepend_option"]="Prepend";
$lang["embedded_metadata_custom_option"]="Custom";
$lang["related_resource_confirm_delete"]="This will remove the relationship but will not delete the resource. ";
$lang["batch_replace_filename_intro"]="To replace a batch of resources you can upload files with names matching the unique resource IDs. Alternatively you can select a metadata field containing the file names and the system will look for a match with the uploaded file names to identify the file that needs to be replaced";
$lang["batch_replace_use_resourceid"]="Match filenames with resource IDs";
$lang["batch_replace_filename_field_select"]="Please select the field containing the file name.";
$lang["plupload_log_intro"] ="Upload summary - server time : ";
$lang["no_access_to_collection"]="Sorry, you don't have access to this collection.";
$lang["internal_share_grant_access"]="Grant open access to selected internal users?";
$lang["internal_share_grant_access_collection"]="Grant open access to internal users (for resources that you can edit)?";

# For merging filename with title functionality:
$lang['merge_filename_title_question'] = 'Use filename in title (if no embedded title is found)?';
$lang['merge_filename_title_do_not_use'] = 'Do not use';
$lang['merge_filename_title_replace'] = 'Replace';
$lang['merge_filename_title_prefix'] = 'Prefix';
$lang['merge_filename_title_suffix'] = 'Suffix';
$lang['merge_filename_title_include_extensions'] = 'Include extensions?';
$lang['merge_filename_title_spacer'] = 'Spacer';

# For sending a collection with all the resources uploaded at one time:
$lang['send_collection_to_admin_emailedcollectionname'] = 'E-mailed collection';
$lang['send_collection_to_admin_emailsubject'] = 'Collection uploaded by ';
$lang['send_collection_to_admin_usercontributedcollection'] = ' uploaded these resources as a whole collection';
$lang['send_collection_to_admin_additionalinformation'] = 'Additional information';
$lang['send_collection_to_admin_collectionname'] = 'Collection name: ';
$lang['send_collection_to_admin_numberofresources'] = 'Number of resources: ';

# User group management
$lang['page-title_user_group_management'] = "Manage user groups";
$lang['page-subtitle_user_group_management'] = "Use this section to add, remove and modify user groups.";
$lang['action-title_create_user_group_called'] = "Create user group called...";
$lang['action-title_filter_by_parent_group'] = "Parent user group filter";
$lang['action-title_filter_by_permissions'] = "Permissions filter";
$lang["fieldhelp-permissions_filter"]="You may enter a single permission or comma separated permissions.  Partial permission names and wildcards are not allowed.  Permissions are case sensitive.";

# User group management edit
$lang['page-title_user_group_management_edit'] = "Edit user group";
$lang['page-subtitle_user_group_management_edit'] = "Use this section to modify user group properties.";
$lang["action-title_remove_user_group_logo"]="Tick to remove user group logo";
$lang["action-title_see_wiki_for_advanced_options"]="Please refer to the <a href='http://wiki.resourcespace.org/index.php?title=Main_Page#System_Administrator.27s_Guide'>WIKI</a> for further help with Advanced options.";

# admin web edit
$lang['page-title_web_edit'] = "Edit file";
$lang['page-subtitle_web_edit'] = "Use this section to directly edit files - use with caution.";

# User group permissions
$lang['page-title_user_group_permissions_edit'] = "Edit user group permissions";
$lang['page-subtitle_user_group_permissions_edit'] = "Use this section to modify user group permissions.";

# Report management
$lang['page-title_report_management'] = "Manage reports";
$lang['page-subtitle_report_management'] = "Use this section to modify system reports.";
$lang['action-title_create_report_called'] = "Create report called...";

# Report management edit
$lang['page-title_report_management_edit'] = "Edit report";
$lang['page-subtitle_report_management_edit'] = "Use this section to modify report contents.";
$lang["fieldtitle-tick_to_delete_report"] = "Tick to delete this report";

# size management
$lang['page-title_size_management'] = "Manage sizes";
$lang['page-subtitle_size_management'] = "Use this section to modify sizes for downloads and previews.";
$lang['action-title_create_size_with_id'] = "Create size with " . $lang['property-id'] . "...";

# size management edit
$lang['page-title_size_management_edit'] = "Edit size";
$lang['page-subtitle_size_management_edit'] = "Use this section to modify size details.";
$lang["fieldtitle-tick_to_delete_size"] = "Tick to delete this size";

##########################################################################################
# Non page-specific items that need to be merged above when system admin project completed
##########################################################################################

$lang["admin_advanced_field_properties"]="Advanced field properties";
$lang["admin_delete_field_confirm"]="There are %%AFFECTEDRESOURCES%% resource(s) with data in this field. Some of the affected resource IDs are listed below. The metadata associated with this field will be lost if you continue. Are you sure you want to proceed with deletion?";
$lang["admin_resource_type_create"]="Create resource type called...";
$lang["admin_resource_type_field"]="Manage metadata field";
$lang["admin_resource_type_field_count"]="Metadata fields";
$lang["admin_resource_type_field_create"]="Create metadata field called...";
$lang["admin_resource_type_field_reorder_information"]="To amend the display order, rearrange the rows in the table by dragging and dropping.";
$lang["admin_resource_type_field_reorder_information_normal_order"]="Reordering is only available when viewing in display order and is set per resource type.";
$lang["admin_resource_type_field_reorder_information_tab_order"]="Reordering is only available when viewing all fields in display order.";
$lang["admin_resource_type_field_reorder_mode"]="Show in display order to enable reordering of fields using drag and drop";
$lang["admin_resource_type_field_reorder_mode_all"]="Show all fields in display order to enable reordering of fields using drag and drop";
$lang["admin_resource_type_field_reorder_select_restype"]="Select a single resource type or global fields in order to enable reordering ";
$lang["admin_resource_type_fields"]="Manage metadata fields";
$lang["admin_resource_type_reorder_information_tab_order"]="Reordering is only available when viewing resource types in display order.";
$lang["admin_resource_type_reorder_mode"]="Show in display order to enable reordering of resource types using drag and drop ";
$lang["admin_resource_type_tab_info"]="Setting a tab name means that related resources of this type will be displayed in that tab along with the metadata. The resource type will effectively be part of the \$related_type_show_with_data array). This setting requires that tabs are correctly set for all fields.";
$lang["admin_report_create"]= "Create report called...";
$lang["action_copy_report"]= "Copy report";
$lang["copy_of"]="Copy of";
$lang["fieldhelp-add_to_config_override"]="Select config override to view help";
$lang["fieldhelp-no_config_override_help"]="There is no help for this config item";
$lang["fieldhelp-tick_to_delete_group"]="You are not allowed to delete user groups that contain active users or are the parent of other groups";
$lang["fieldtitle-add_to_config_override"]="Add to config override";
$lang["fieldtitle-advanced_options"]="Advanced options";
$lang["fieldtitle-derestrict_filter"]="Derestrict filter";
$lang["fieldtitle-group_logo"]="Group specific logo";
$lang["fieldtitle-tick_to_delete_group"]="Tick to delete this group";
$lang["property-contains"]="Contains";
$lang["property-groups"]="Groups";
$lang["property-user_group"]="User group";
$lang["property-user_group_parent"]="Parent user group";
$lang["property-user_group_remove_parent"]="(remove parent)";
$lang["resource_type_delete_confirmation"]="There are %%RESOURCECOUNT%% resource(s) of this type.";
$lang["resource_type_delete_select_new"]="Please select the resource type that these will be converted to.";
$lang["resourcetype-global_field"]="Global";
$lang["resourcetype-global_field"]="Global";
$lang["search_title_hasdata"]="Resources with data in field";
$lang["show_resources"]="Show resources";
$lang["team_user_contributions"]="Contributions";
$lang["team_user_view_contributions"]="View contributions";
$lang['action-title_apply'] = "Apply";
$lang['property-allow_preview'] = "Allow preview";
$lang['property-allow_restricted_download'] = "Allow restricted download";
$lang['property-orphaned'] = "Orphaned";
$lang['property-pad_to_size'] = "Pad to size";
$lang['admin_field_deleted'] = "Deleted field";
$lang['action-move-up'] = 'Move up';
$lang['action-move-down'] = 'Move down';
$lang['delete_user_group_checkbox_alert_message'] = 'Please note this action will also delete all content text that was relevant to this user group. There are %%RECORDSCOUNT%% records found in Manage Content.';



$lang["about__about"]="Your about text goes here.";
$lang["all__comments_flag_notification_email_body"]="";
$lang["all__comments_flag_notification_email_subject"]="";
$lang["all__comments_policy"]="";
$lang["all__comments_removal_message"]="";
$lang["all__emailbulk"]="[img_gfx/titles/title.gif]<br /><br />\n[text]<br /><br />\n[text_footer]\n";
$lang["all__emailcollection"]="[img_gfx/titles/title.gif]<br />\n[fromusername] [lang_emailcollectionmessage] <br /><br /> \n[lang_message] : [message]<br /><br /> \n[lang_clicklinkviewcollection] [list]\n";
$lang["all__emailcollectionexternal"]="[img_gfx/titles/title.gif]<br />\n[fromusername] [lang_emailcollectionmessageexternal] <br /><br /> \n[lang_message] : [message]<br /><br /> \n[lang_clicklinkviewcollection] [list]\n";
$lang["all__emailcontactadmin"]="[img_../gfx/titles/title.gif]<br />[fromusername] ([emailfrom])[lang_contactadminemailtext]<br /><br />[message]<br /><br /><a href=\"[url]\">[embed_thumbnail]</a><br /><br />[text_footer]";
$lang["all__emaillogindetails"]="[img_gfx/titles/title.gif]<br />\n[welcome]<br /><br /> \n[lang_newlogindetails]<br /><br /> \n[lang_username] : [username] <br /><br />\n[lang_password] : [password]<br /><br />\n<a href=\"[url]\">[url]</a><br /><br />\n[text_footer]\n";
$lang["all__emailnewresearchrequestwaiting"]="[img_gfx/titles/title.gif]<br />\n[username] ([userfullname] - [useremail])\n[lang_haspostedresearchrequest]<br /><br />\n[lang_nameofproject]:[name]<br /><br />\n[lang_descriptionofproject]:[description]<br /><br />\n[lang_deadline]:[deadline]<br /><br />\n[lang_contacttelephone]:[contact]<br /><br />\n[lang_finaluse]: [finaluse]<br /><br />\n[lang_shaperequired]: [shape]<br /><br />\n[lang_noresourcesrequired]: [noresources]<br /><br />\n<a href=\"[url]\">[url]</a><br /><br />\n<a href=\"[teamresearchurl]\">[teamresearchurl]</a><br /><br />\n[text_footer]\n";
$lang["all__emailnotifyresourcesapproved"]="[img_gfx/titles/title.gif]<br />\n[lang_userresourcesapproved]\n[list] <br />\n[lang_viewcontributedsubittedl] <br /><br /> \n<a href=\"[url]\">[url]</a><br /><br />\n[text_footer]\n";
$lang["all__emailnotifyresourcessubmitted"]="[img_gfx/titles/title.gif]<br />\n[lang_userresourcessubmitted]\n[list] <br />\n[lang_viewalluserpending] <br /><br /> \n<a href=\"[url]\">[url]</a><br /><br />\n[text_footer]\n";
$lang["all__emailnotifyresourcesunsubmitted"]="[img_gfx/titles/title.gif]<br />\n[lang_userresourcesunsubmitted]\n[list] <br />\n[lang_viewalluserpending] <br /><br /> \n<a href=\"[url]\">[url]</a><br /><br />\n[text_footer]\n";
$lang["all__emailreminder"]="[img_gfx/titles/title.gif]<br />\n[lang_newlogindetails] <br /><br />\n[lang_username] : [username] <br /> \n[lang_password]  : [password] <br /><br />\n<a href=\"[url]\">[url]</a><br /><br />\n[text_footer]\n";
$lang["all__emailresearchrequestassigned"]="[img_gfx/titles/title.gif]<br />\n[lang_researchrequestassignedmessage]<br /><br />\n[text_footer]\n";
$lang["all__emailresearchrequestcomplete"]="[img_gfx/titles/title.gif]<br />\n[lang_researchrequestcompletemessage] <br /><br /> \n[lang_clicklinkviewcollection] <br /><br /> \n<a href=\"[url]\">[url]</a><br /><br />\n[text_footer]\n";
$lang["all__emailresource"]="[img_gfx/titles/title.gif]<br />\n[fromusername] [lang_hasemailedyouaresource]<br /><br />\n[message]<br /><br />\n<a href=\"[url]\">[embed_thumbnail]</a><br /><br />\n[lang_clicktoviewresource]<br /><a href=\"[url]\">[resourcename] - [url]</a><br /><br />\n[text_footer]\n";
$lang["all__emailresourcerequest"]="[img_gfx/titles/title.gif]<br />\n[lang_username] : [username] <br />\n[list] <br />\n[details]<br /><br />\n[lang_clicktoviewresource] <br /><br />\n<a href=\"[url]\">[url]</a>\n";
$lang["all__footer"]="Powered by <a target=\"_blank\" href=\"http://www.resourcespace.org/\">ResourceSpace</a>: Open Source Digital Asset Management";
$lang["all__researchrequest"]="Let our resources team find the resources you need.";
$lang["all__searchpanel"]="Search using descriptions, keywords and resource numbers";
$lang["change_language__introtext"]="Please select your language below.";
$lang["collection_edit__introtext"]="Organise and manage your work by grouping resources together. Create 'Collections' to suit your way of working.\n\n<br />\n\nAll the collections in your list appear in the 'My Collections' panel at the bottom of the screen\n\n<br /><br />\n\n<strong>Private Access</strong> allows only you and and selected users to see the collection. Ideal for grouping resources under projects that you are working on independently and share resources amongst a project team.\n\n<br /><br />\n\n<strong>Public Access</strong> allows all users of the system to search and see the collection. Useful if you wish to share collections of resources that you think others would benefit from using.\n\n<br /><br />\n\nYou can choose whether you allow other users (public or users you have added to your private collection) to add and remove resources or simply view them for reference.";
$lang["collection_email__introtext"]="Please complete the form below. The recipients will receive an email containing links to the collections rather than file attachments so they can choose and download the appropriate resources.";
$lang["collection_email__introtextthemeshare"]="Complete the form below to e-mail the featured collections in this featured collection category. The recipients will receive an email containing links to each of the featured collections.";
$lang["collection_manage__findpublic"]="Public collections are groups of resources made widely available by users of the system. Enter a collection ID, or all or part of a collection name or username to find public collections. Add them to your list of collections to access the resources.";
$lang["collection_manage__introtext"]="Organise and manage your work by grouping resources together. Create 'Collections' to suit your way of working. You may want to group resources under projects that you are working on independently, share resources amongst a project team or simply keep your favourite resources together in one place. All the collections in your list appear in the 'My Collections' panel at the bottom of the screen.";
$lang["collection_manage__newcollection"]="To create a new collection, enter a short name.";
$lang["collection_public__introtext"]="Public collections are created by other users.";
$lang["contact__contact"]="Your contact details here.";
$lang["contribute__introtext"]="You can contribute your own resources. When you initially create a resource it is in the \"Pending Submission\" status. When you have uploaded your file and edited the fields, set the status field to \"Pending Review\". It will then be reviewed by the resources team.";
$lang["delete__introtext"]="Please enter your password to confirm that you would like to delete this resource.";
$lang["done__collection_email"]="An email containing a link to the collection has been sent to the users you specified.";
$lang["done__deleted"]="The resource has been deleted.";
$lang["done__research_request"]="A member of the research team will be assigned to your request. We'll keep in contact via email throughout the process, and once we've completed the research you'll receive an email with a link to all the resources that we recommend.";
$lang["done__resource_email"]="An email containing a link to the resource has been sent to the users you specified.";
$lang["done__resource_request"]="Your request has been submitted and we will be in contact shortly.";
$lang["done__user_password"]="An e-mail containing your username and password has been sent.";
$lang["done__user_request"]="Your request for a user account has been sent. Your login details will be sent to you shortly.";
$lang["download_click__introtext"]="To download the resource file, right click the link below and choose \"Save As...\". You will then be asked where you would like to save the file. To open the file in your browser simply click the link.";
$lang["download_progress__introtext"]="Your download will start shortly. When your download completes, use the links below to continue.";
$lang["downloadfile_nofile"]="The file requested was not found.";
$lang["edit__batch"]="";
$lang["edit__multiple"]="Please select which fields you wish to overwrite. Fields you do not select will be left untouched.";
$lang["help__introtext"]='<iframe src="http://www.resourcespace.org/knowledge-base/?from_rs=true" style="width:1235px;height:600px;border:none;margin:-20px;"/>
';
$lang["home__help"]="Help and advice to get the most out of ResourceSpace.";
$lang["home__mycollections"]="Organise, collaborate & share your resources. Use these tools to help you work more effectively.";
$lang["home__restrictedtext"]="Please click on the link that you were e-mailed to access the resources selected for you.";
$lang["home__restrictedtitle"]="Welcome to ResourceSpace [ver]";
$lang["home__themes"]="The very best resources, hand picked and grouped.";
$lang["home__welcometext"]="The simple, fast, &amp; free way to organise your digital assets.";
$lang["home__welcometitle"]="Welcome to ResourceSpace [ver]";
$lang["login__welcomelogin"]="Welcome to ResourceSpace, please log in...";
$lang["research_request__introtext"]="Our professional researchers are here to assist you in finding the very best resources for your projects. Complete this form as thoroughly as possible so we're able to meet your criteria accurately. <br /><br />A member of the research team will be assigned to your request. We'll keep in contact via email throughout the process, and once we've completed the research you'll receive an email with a link to all the resources that we recommend.  ";
$lang["resource_email__introtext"]="Quickly share this resource with other users by email. A link is automatically sent out. You can also include any message as part of the email.";
$lang["resource_request__introtext"]="Your request is almost complete. Please include the reason for your request so we can respond efficiently.";
$lang["search_advanced__introtext"]="<strong>Search Tip</strong><br />Any section that you leave blank, or unticked will include ALL those terms in the search. For example, if you leave all the country boxes empty, the search will return results from all those countries. If you select only 'Africa' then the results will ONLY contain resources from 'Africa'. ";
$lang["tag__introtext"]="Help to improve search results by tagging resources. Say what you see, separated by spaces or commas... for example: dog, house, ball, birthday cake. Enter the full name of anyone visible in the photo and the location the photo was taken if known.";
$lang["team_archive__introtext"]="To edit individual archive resources, simply search for the resource, and click edit in the 'Resource Tool' panel on the resource screen. All resources that are ready to be archived are listed Resources Pending list. From this list it is possible to add further information and transfer the resource record into the archive. ";
$lang["team_batch__introtext"]="";
$lang["team_batch_select__introtext"]="";
$lang["team_batch_upload__introtext"]="";
$lang["team_content__introtext"]="";
$lang["team_copy__introtext"]="Enter the ID of the resource you would like to copy. Only the resource data will be copied - any uploaded file will not be copied.";
$lang["team_home__introtext"]="Welcome to the admin area. Use the links below to administer resources, respond to resource requests, manage featured collections and alter system settings.";
$lang["team_report__introtext"]="Please choose a report and a date range. The report can be opened in Microsoft Excel or similar spreadsheet application.";
$lang["team_research__introtext"]="Organise and manage 'Research Requests'. <br /><br />Choose 'edit research' to review the request details and assign the research to a team member. It is possible to base a research request on a previous collection by entering the collection ID in the 'edit' screen. <br /><br />Once the research request is assigned, choose 'edit collection' to add the research request to 'My collection' panel. Using the standard tools, it is then possible to add resources to the research. <br /><br />Once the research is complete, choose 'edit research',  change the status to complete and an email is automatically  sent to the user who requested the research. The email contains a link to the research and it is also automatically added to their 'My Collection' panel.";
$lang["team_resource__introtext"]="Add individual resources or batch upload resources. To edit individual resources, simply search for the resource, and click edit in the 'Resource Tool' panel on the resource screen.";
$lang["team_stats__introtext"]="Charts are generated on demand based on live data. Tick the box to print all charts for your selected year.";
$lang["team_user__introtext"]="Use this section to add, remove and modify users.";
$lang["terms__introtext"]="Before you proceed you must accept the terms and conditions.\n\n";
$lang["terms__terms"]="Your terms and conditions go here.";
$lang["terms and conditions__terms and conditions"]="Your terms and conditions go here.";
$lang["themes__findpublic"]="Public collections are collections of resources that have been shared by other users.";
$lang["themes__introtext"]="Featured collections are groups of resources that have been selected by the administrators to provide an example of the resources available in the system.";
$lang["themes__manage"]="Organise and edit the featured collections available online. Featured collections are specially promoted collections. <br /><br /> <strong>1 To create a new entry under a Featured collection -  build a collection</strong><br /> Choose <strong>My Collections</strong> from the main top menu and set up a brand new <strong>public</strong> collection. Remember to include a featured collection name during the setup. Use an existing featured collection name to group the collection under a current featured collection (make sure you type it exactly the same), or choose a new title to create a brand new featured collection. Never allow users to add/remove resources from featured collections. <br /> <br /><strong>2 To edit the content of an existing entry under a featured collection </strong><br /> Choose <strong>edit collection</strong>. The items in that collection will appear in the <strong>My Collections</strong> panel at the bottom of the screen. Use the standard tools to edit, remove or add resources. <br /> <br /><strong>3 To alter a featured collection name or move a collection to appear under a different featured collection</strong><br /> Choose <strong>edit properties</strong> and edit featured collection category or collection name. Use an existing featured collection name to group the collection under an current featured collection (make sure you type it exactly the same), or choose a new title to create a brand new featured collection. <br /> <br /><strong>4 To remove a collection from a featured collection </strong><br /> Choose <strong>edit properties</strong> and delete the words in the featured collection category box. ";
$lang["upload__introtext"]="";
$lang["upload_swf__introtext"]="";
$lang["user_password__introtext"]="Enter your e-mail address and your username and password will be sent to you.";
$lang["user_preferences__introtext"]="Enter a new password below to change your password.";
$lang["user_preferences__introtext_new"]="Please enter a password below.";
$lang["user_request__introtext"]="Please complete the form below to request a user account.";
$lang["view__storyextract"]="Story extract:";
$lang["notify_resource_change_email_subject"]="A resource has been modified";
$lang["notify_resource_change_email"]="A resource that you downloaded in the past [days] days has been modified . Click the link below to view the resource.<br /><br /><a href='[url]'>[url]</a>";
$lang["notify_resource_change_notification"]="A resource that you downloaded in the past [days] days has been modified.";
$lang["passwordresetemail"]="Please click on the link below to reset your password.";
$lang['password_reset_email_html'] = 'Username: [username]<br /><br />Please click on the link below to reset your password.<br /><br /><a href="[url]" target="_blank" >[url]</a>';
$lang["passwordnewemail"]="Please click on the link below to set a new password for your account.";
$lang["passwordlinkexpired"]="Password reset link has either expired or been used. Please log in or request a new link."; 
$lang["done__user_password_link_sent"]="If your email address was recognised as belonging to a valid account then an e-mail containing a link to reset your password has been sent.<br /><br /> If you do not receive an email and you believe your account is valid please check your email filters before contacting your system administrator.";
$lang["user_password__introtextreset"]="Enter your e-mail address and a link you can use to reset your password will be sent to you.";
$lang["ticktoemaillink"]="E-mail user a link so that they can reset their password";
$lang["resetpassword"]="Reset password";
$lang["customaccesspreventshare"]="You do not have permission to share one or more resources in this collection";

$lang["prevent_user_group_sharing_externally"]="Prevent users from sharing resources with external users";

$lang["allow_user_group_selection_for_access_when_sharing_externally"]="Allow user group select for determining access level when sharing externally";
$lang["share_using_permissions_from_user_group"]="Share using permissions from user group";
$lang["externalshare_using_permissions_from_user_group"]="If you are e-mailing external users, please select the user group to use for access";
$lang["delete__nopassword"]="Please confirm that you would like to delete this resource.";

$lang["collection_download_too_large"]="Sorry, this collection is too large to download as one file. Try reducing the size of the collection or selecting a smaller image size.";
$lang["all__passwordnewemailhtml"]="[img_gfx/titles/title.gif]<br /><br />Please click on the link below to set a new password for your account.<br /><br />URL: <a href=\"[url]\" target=\"_blank\" >[url]</a><br />username: [username]<br />[text_footer]" ;

$lang['disk_size_no_upload_heading']="Uploading temporarily unavailable";
$lang['disk_size_no_upload_explain']="Due to space constraints, uploading has been temporarily disabled. We apologize for any inconvenience.";

/*
 * Start - User Dash Strings
 */
$lang["savethissearchtodash"]="Save to dash tile";
$lang["createnewdashtile"]="Create new dash tile";
$lang["specialdashtiles"]="Special dash tiles";
$lang["editdashtile"]="Edit dash tile";
$lang["createdashtilefreetext"]="Create text only dash tile";
$lang["enterdefaultorderby"]="Enter default position number";
$lang["dashtiletitle"]="Title";
$lang["dashtiletext"]="Text";
$lang["dashtilelink"]="Tile target link";
$lang["nodashtilefound"]="No matching dash tile was found";
$lang["existingdashtilefound"]="The tile specified already exists on your Dash.";
$lang["invaliddashtile"]="Invalid dash tile reference";
$lang["dashtilestyle"]="Dash tile style";
$lang["returntopreviouspage"]="Return to previous page";
$lang["showresourcecount"]="Show resource count?";
$lang["tilebin"]="Remove";
$lang["last"]="Last";
$lang["managedefaultdash"]="Manage all user tiles";
$lang["dashtile"]="Dash tile";
$lang["manage_own_dash"]="My dash";
$lang["manage_all_dash_h"]="Manage default dash / all user tiles (Requires h permission)";
$lang["manage_all_dash"]="Manage default dash / all user tiles";
$lang["dashtiledeleteaction"]="What delete action would you like to take?";
$lang["confirmdashtiledelete"]="Delete tile from my dash";
$lang["dashtiledeleteusertile"]="This is a tile that you have created and will be permanently deleted if you continue with this action";
$lang["confirmdefaultdashtiledelete"]="Delete tile for all users";
$lang["dashtiledelete"]="Delete dash tile";
$lang["error-missingtileheightorwidth"]="Missing tile height or width";
$lang["dashtileimage"]="Dash tile image";
$lang["dashtilesmalldevice"]="Required drag functionality is not available on devices with a small screen";
$lang["dashtileshow"]="Show tile";
$lang["dasheditmodifytiles"]="Edit / modify dash tiles available";
$lang['confirmdeleteconfigtile']="This tile is controlled by configuration option(s). To permanently delete this tile, turn off the relevant options and then perform this action again.";
$lang["error-dashactionmissing"]="No action or invalid data was submitted to this page. No tile template available to build. <br />Please return to this page from a suitable creation / edit link";
$lang["dasheditchangeall_users"]="Turning off this setting will not remove this tile from all dashes, you must do this from manage all user tiles. However, new users will no longer receive this tile on their dash.";
$lang["dashtilevisitlink"]="Visit target link";
$lang["alluserprebuiltdashtiles"]="Create pre-constructed dash tiles (added for all users)";
$lang["manageowndashinto"]="Manage dash tiles available for your dash. You can add / remove or edit tiles on your dash if you have the permissions to do so.";

/* User group dash tiles */
$lang['manage_user_group_dash_tiles']  = 'Manage user group dash tiles';
$lang['who_should_see_dash_tile']      = 'Who should see this tile?';
$lang['dash_tile_audience_me']         = 'only me';
$lang['dash_tile_audience_all_users']  = 'all users';
$lang['dash_tile_audience_user_group'] = 'specific user group(s)';
/* End of user group dash tiles */

/* Create Config dash tile link descriptions (text) */
$lang["createdashtilependingsubmission"]="User contributions pending submission (hides if none in state)";
$lang["createdashtilependingreview"]="User contributions pending review (hides if none in state)";
$lang["createdashtilethemeselector"]="Featured collections with a selector for a specific collection category";
$lang["createdashtilethemes"]="Featured collections";
$lang["createdashtilemycollections"]="My collections (user specific)";
$lang["createdashtileadvancedsearch"]="Advanced search link";
$lang["createdashtilemycontributions"]="My contributions (user specific)";
$lang["createdashtilehelpandadvice"]="Help and advice link";
$lang["createdashtileuserupload"]="Upload tile (user specific)";
#Tile style strings
$lang["tile_thmbs"]="Single";
$lang["tile_multi"]="Multi";
$lang["tile_blank"]="Blank";
$lang["tile_ftxt"]="Text only";
/* * End - User Dash Strings * */

/* * Start - Plugin Category Strings * */
$lang["plugin_category_general"]="General";
$lang["plugin_category_api"]="API";
$lang["plugin_category_advanced"]="Advanced";
$lang["plugin_category_design"]="Design";
$lang["plugin_category_ecommerce"]="Ecommerce";
$lang["plugin_category_sharing"]="Content Sharing";
/* * End - Plugin Category Strings * */

/* System Down page strings */
$lang['system_down_title'] = 'Attention!';
$lang['system_down_message'] = 'System is down for maintenance. Thank you for your patience.';
/* End of System Down page strings */

/* System Console */
$lang["systemconsole"]="System console";
$lang["systemconsoledebuglog"]="Debug log";
$lang["systemconsolememorycpu"]="Memory &amp; CPU";
$lang["systemconsoledatabase"]="Database";
$lang["systemconsolesqllogtransactions"]="SQL Transaction Log";
$lang["systemconsoleactivitylog"]="Activity Log";
$lang["systemconsoleturnoffafter"]="Turn off after";
$lang["systemconsoleonpermallusers"]="On (permanently for all users)";
$lang["systemconsoleonallusers"]="On (all users)";
$lang["systemconsoleonfailedtopcommand"]="Failed to execute top command";
$lang["systemconsoleonfailedtasklistcommand"]="Failed to execute tasklist command";
$lang["systemconsoleondebuglognotsetorfound"]="\$debug_log_location not set, file not found or is not readable";
$lang["systemconsoleonsqllognotsetorfound"]="\$mysql_log_transactions not set or \$mysql_log_location file not found or is not readable";

/* Global Trash Bin */
$lang['trash_bin_title'] = 'Remove';
$lang['trash_bin_delete_dialog_title'] = 'Remove resource from current collection?';
/* End of Global Trash Bin strings */

/* My Account Strings */
$lang["managecontent_defaulttextused"]="!! The text for the default language (?) is currently being used. Edit the text below to create an alternative version for this language / user group selection !!";
$lang["myaccount"]="My account";
$lang["userpreferences"]="My preferences";
$lang["modifyuserpreferencesintro"]="Options on this page allow you to make changes to some of the functionality  and interface that are available to you.";

/* User preferences*/
$lang['userpreference_colourtheme'] = 'Colour theme';
$lang["userpreferencecolourtheme"]="Interface colour theme";
$lang['userpreference_user_interface'] = 'User interface';
$lang['userpreference_enable_option'] = 'Enable';
$lang['userpreference_disable_option'] = 'Disable';
$lang['userpreference_default_sort_label'] = 'Default sort';
$lang['userpreference_default_perpage_label'] = 'Default per page';
$lang['userpreference_default_display_label'] = 'Default display';
$lang['userpreference_use_checkboxes_for_selection_label'] = 'Use checkboxes to add to collection';
$lang['userpreference_resource_view_modal_label'] = 'Resource view modal';
$lang['userpreference_thumbs_default_label'] = 'Default collection bar display';
$lang['userpreference_basic_simple_search_label'] = 'Basic simple search';
$lang['userpreference_cc_me_label'] = 'CC me when sending resources and collections';
$lang['userpreference_email_me_label'] = 'Send me emails instead of system notifications where possible';
$lang['userpreference_email_digest_label'] = 'Send me a daily digest of notifications, instead of separate emails';
$lang['userpreference_system_management_notifications'] = "Send me messages about important system events e.g. low disk space.";
$lang['userpreference_user_management_notifications'] = "Send me user administration messages e.g. new user acount requests";
$lang['userpreference_resource_access_notifications'] = "Send me messages about resource access e.g. resource requests";
$lang['userpreference_resource_notifications'] = "Send me resource messages about resource management e.g. resource state changes, metadata changes";

/* System Config */
$lang['systemconfig'] = 'System configuration';
$lang['systemconfig_linkedheaderimgsrc_label'] = 'Application logo';
$lang['systemconfig_description'] = 'The options on this page are system wide and can change some of the functionality available to the users. Please note that any option that is also user specific will take precedence if set.';
$lang['systemconfig_multilingual'] = 'Multilingual';
$lang['systemconfig_default_language_label'] = 'Default language';
$lang['systemconfig_browser_language_label'] = 'Browser language detection';
$lang['systemconfig_display_resource_id_in_thumbnail_label'] = 'Display resource ID on the thumbnail';
$lang['systemconfig_advanced_search_contributed_by_label'] = 'Show "Contributed by" on Advanced Search';
$lang['systemconfig_advanced_search_media_section_label'] = 'Show Media section on Advanced Search';
$lang['systemconfig_navigation'] = 'Navigation';
$lang['systemconfig_help_link_label'] = 'Show "Help & advice" link';
$lang['systemconfig_recent_link_label'] = 'Show "Recently added" link';
$lang['systemconfig_mycollections_link_label'] = 'Show "My collections" link';
$lang['systemconfig_myrequests_link_label'] = 'Show "My requests" link';
$lang['systemconfig_research_link_label'] = 'Show "Research requests" link';
$lang['systemconfig_themes_navlink_label'] = 'Show "Featured collections" link';
$lang['systemconfig_use_theme_as_home_label'] = 'Use the "Featured collections" page as the home page?';
$lang['systemconfig_use_recent_as_home_label'] = 'Use the "Recently added" page as the home page?';
$lang['systemconfig_workflow'] = 'Workflow';
$lang['systemconfig_minyear_label'] = 'The year of the earliest resource record';
$lang['systemconfig_user_accounts'] = 'User accounts';
$lang['systemconfig_allow_account_request_label'] = 'Allow users to request accounts';
$lang['systemconfig_terms_download_label'] = 'Terms and conditions for download';
$lang['systemconfig_terms_login_label'] = 'Terms and conditions on first login';
$lang['systemconfig_user_rating_label'] = 'User rating of resources';
$lang['systemconfig_security'] = 'Security';
$lang['systemconfig_password_min_length_label'] = 'Minimum length of password';
$lang['systemconfig_password_min_alpha_label'] = 'Minimum number of alphabetical characters (a-z, A-Z)';
$lang['systemconfig_password_min_numeric_label'] = 'Minimum number of numeric characters (0-9)';
$lang['systemconfig_password_min_uppercase_label'] = 'Minimum number of upper case alphabetical characters (A-Z)';
$lang['systemconfig_password_min_special_label'] = 'Minimum number of non-alphanumeric characters (e.g. !@$%&)';
$lang['systemconfig_password_expiry_label'] = 'How often do passwords expire, in days';
$lang['systemconfig_max_login_attempts_per_ip_label'] = 'How many failed login attempts per IP address until a temporary ban is placed on this IP';
$lang['systemconfig_max_login_attempts_per_username_label'] = 'How many failed login attempts per username until a temporary ban is placed on this IP';
$lang['systemconfig_max_login_attempts_wait_minutes_label'] = 'Waiting time (in minutes) for temporary banned users due to failed login attempts';
$lang['systemconfig_password_brute_force_delay_label'] = 'Delay (in seconds) after failed attempts';
$lang['systemconfig_option_not_allowed_error'] = 'Option not allowed! Please contact system admin';

/* Error Messages */
$lang['error_check_html_first'] = 'Please Check HTML! The text used does not contain valid HTML.';


$lang["maximise"]="Maximise";

$lang["actions-select"]="";
$lang['actions'] = 'Actions';
$lang["submit_review_prompt"]="Submit for review?";
$lang["submit_dialog_text"]="The uploaded resources are now in the pending submission state. Submit the collection for review or continue editing?";
$lang["action_submit_review"]="Submit for review";
$lang["action_continue_editing"]="Continue editing";
$lang['action-addrow']="Add row";

/* Messaging */
$lang["seen"]="Seen";
$lang["from"]="From";
$lang["mymessages"]="My messages";
$lang["mymessages_markread"]="Mark read";
$lang["mymessages_markunread"]="Mark unread";
$lang["mymessages_markallread"]="Mark all read";
$lang["mymessages_youhavenomessages"]="You have no messages to show";
$lang["screen"]="Screen";
$lang["message_type"]="Message type";
$lang["message_url"]="Message URL";
$lang["sendbulkmessage"]="Send bulk message";
$lang["message_sent"]="Message sent";

$lang["confirm_remove_custom_usergroup_access"]="This will revoke all custom user group access. Are you sure?";
$lang["applogo_does_not_exists"]="The uploaded logo is no longer available";

/* Edit field options */
$lang["manage_metadata_field_options"] = "Manage metadata field options";
$lang["system_performance"]="System performance";
$lang["mysql_throughput"]="MySQL throughput";
$lang["cpu_benchmark"]="CPU benchmark";
$lang["disk_write_speed"]="Disk write speed";
$lang["metadata_option_change_warning"]="Please note that at present, updating these options will not automatically alter stored parameters. Batch editing must be used to migrate existing stored values.";
/* Manage slideshows */
$lang["manage_slideshow"] = "Manage slideshow";
$lang["action-add-new"] = "Add new";
$lang["action-submit-button-label"] = "Submit";
$lang["slideshow_use_static_image"] = "Use a single random image from the set (image will not change unless page is reloaded)";

$lang["emailcollectionrequest"] = "[img_gfx/titles/title.gif]<br />The following user has made a request:<br />Username: [username]<br />User email: [useremail]<br /><br />Reason for request: [requestreason]<br /><br />Click the link below to view the request.<br /><a href='[requesturl]'>[requesturl]</a>";
$lang["emailusercollectionrequest"] = "[img_gfx/titles/title.gif]<br />Your resource request has been submitted for approval and will be looked at shortly.:<br /><br />Reason for request: [requestreason]<br /><br />Click the link below to view the requested resources.<br /><a href='[url]'>[url]</a>";
$lang['user_pref_show_notifications'] = "Show me system notifications on screen as they are received. If disabled, the counter will still update to indicate the presence of new messages";
$lang['user_pref_daily_digest'] = "Send me a daily email with all unread notifications from the last 24 hours";
$lang['email_daily_digest_subject'] = "Notification summary";
$lang['email_daily_digest_text'] = "This is a summary of your unread messages from the last 24 hours";
$lang['user_pref_daily_digest_mark_read'] = "Mark messages as read once I have been sent the summary email";
$lang['mymessages_introtext'] = "You can configure which messages appear here by changing your user preferences";
$lang["login_slideshow_image_notes"] = "This image will be used for the login page background only";
$lang['media'] = "Media";
$lang["pixel_height"]="Pixel height";
$lang["pixel_width"]="Pixel width";
$lang["file_extension_label"]="File extension";
$lang["signin_required"]="You must be signed in to perform this action";
$lang["signin_required_request_account"]="You must be signed in to perform this action. If you do not have an account you can request one by clicking on the above link";
$lang["error_batch_edit_resources"] = "The following resources have not been updated";
# Job queue message strings
$lang["job_queue_manage"] = "Manage job queue";
$lang["job_queue_manage_job"] = "Manage job";
$lang["job_queue_type"] = "Job type";
$lang["job_queue_duplicate_message"] = "Job creation failed. There is already a matching job in the queue.";
$lang["alternative_file_created"] = "Alternative file successfully created.";
$lang["alternative_file_creation_failed"] = "Alternative file creation failed";
$lang["download_file_created"] = "Your file is ready for download.";
$lang["download_file_creation_failed"] = "Your file download request failed.";
$lang["replace_resource_preserve_original"]="Keep the existing file as an alternative?";
$lang["replace_resource_original_description"]= "Original %EXTENSION file"; // %EXTENSION  will be relaced by originl file extension

/* Manage external shares */
$lang['permission_manage_external_shares'] = 'Can manage external shares with expiry set to "Never"';
$lang['manage_external_shares'] = 'Manage external shares';
$lang['filter_label'] = 'Filter';
/* end of Manage external shares */