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

$lang["property-field_id"]="Field id";
$lang["property-title"]="Title";
$lang["property-resource_type"]="Resource type";
$lang["property-field_type"]="Field type";

$lang["property-options"]="Options";
$lang["property-required"]="Required";
$lang["property-order_by"]="Order by";
$lang["property-indexing"]="<b>Indexing</b>";
$lang["information-if_you_enable_indexing_below_and_the_field_already_contains_data-you_will_need_to_reindex_this_field"]="If you enable indexing below and the field already contains data, you will need to <a target=_blank href=../tools/reindex_field.php?field=%ref>reindex this field</a>"; # %ref will be replaced with the field id
$lang["property-index_this_field"]="Index this field";
$lang["information-enable_partial_indexing"]="Partial keyword indexing (prefix+infix indexing) should be used sparingly as it will significantly increase the index size. See the wiki for details.";
$lang["property-enable_partial_indexing"]="Enable partial indexing";
$lang["information-shorthand_name"]="Important: Shorthand name must be set for the field to appear on Advanced Search. It must contain only lowercase alphabetical characters - no spaces, numbers or symbols.";
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
$lang["property-smart_theme_name"]="Smart theme name";
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

$lang["property-query"]="Query";

$lang["information-id"]="Note: 'Id' below MUST be set to a three character unique code.";
$lang["property-id"]="Id";
$lang["property-width"]="Width";
$lang["property-height"]="Height";
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


# Top navigation bar (also reused for page titles)
$lang["logout"]="Log out";
$lang["contactus"]="Contact us";
# next line
$lang["home"]="Home";
$lang["searchresults"]="Search results";
$lang["themes"]="Themes";
$lang["mycollections"]="My collections";
$lang["myrequests"]="My requests";
$lang["collections"]="Collections";
$lang["mycontributions"]="My contributions";
$lang["researchrequest"]="Research request";
$lang["helpandadvice"]="Help & advice";
$lang["teamcentre"]="Team Centre";
# footer link
$lang["aboutus"]="About us";
$lang["interface"]="Interface";

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
$lang["largethumbs"]="Large";
$lang["smallthumbs"]="Small";
$lang["list"]="List";
$lang["perpage"]="per page";

$lang["gotoadvancedsearch"]="Advanced search";
$lang["viewnewmaterial"]="View new material";
$lang["researchrequestservice"]="Research request service";

# Team Centre
$lang["manageresources"]="Manage resources";
$lang["overquota"]="Over disk space quota; cannot add resources";
$lang["managearchiveresources"]="Manage archive resources";
$lang["managethemes"]="Manage themes";
$lang["manageresearchrequests"]="Manage research requests";
$lang["manageusers"]="Manage users";
$lang["managecontent"]="Manage content";
$lang["viewstatistics"]="View statistics";
$lang["viewreports"]="View reports";
$lang["viewreport"]="View report";
$lang["treeobjecttype-report"]=$lang["report"]="Report";
$lang["sendbulkmail"]="Send bulk mail";
$lang["systemsetup"]="System setup";
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
$lang["users"]="users";


# Team Centre - Bulk E-mails
$lang["emailrecipients"]="E-mail recipient(s)";
$lang["emailsubject"]="E-mail subject";
$lang["emailtext"]="E-mail text";
$lang["emailhtml"]="Enable HTML support - mail body must use HTML formatting";
$lang["send"]="Send";
$lang["emailsent"]="The e-mail has been sent.";
$lang["mustspecifyoneuser"]="You must specify at least one user";
$lang["couldnotmatchusers"]="Could not match all the usernames, or usernames were duplicated";

# Team Centre - User management
$lang["comments"]="Comments";

# Team Centre - Resource management
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
$lang["themecategory"]="Theme category";
$lang["theme"]="Theme";
$lang["newcategoryname"]="OR: Enter a new theme category name...";
$lang["allowothersaddremove"]="Allow other users to add/remove resources";
$lang["resetarchivestatus"]="Reset archive status for all resources in collection";
$lang["editallresources"]="Edit all resources in collection";
$lang["editresources"]="Edit resources";
$lang["multieditnotallowed"]="Mult-edit not allowed - all the resources are not in the same status or of the same type.";
$lang["emailcollection"]="E-mail collection";
$lang["collectionname"]="Collection name";
$lang["collection-name"]="Collection: %collectionname%"; # %collectionname will be replaced, e.g. Collection: Cars
$lang["collectionid"]="Collection ID";
$lang["collectionidprefix"]="Col_ID";
$lang["_dupe"]="_dupe";
$lang["emailtousers"]="E-mail to users<br><br><b>For existing users</b> start typing the user's name to search, click the user when found and then click plus<br><br><b>For non-registered users</b> type the e-mail address then click plus";
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
$lang["viewall"]="View all";
$lang["action-editall"]="Edit all";
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
$lang["collectionsnothemeselected"]="You must select or enter a theme category name.";
$lang["downloaded"]="Downloaded";
$lang["contents"]="Contents";
$lang["forthispackage"]="for this package";
$lang["didnotinclude"]="Did not include";
$lang["selectcollection"]="Select collection";
$lang["total"]="Total";
$lang["ownedbyyou"]="owned by you";
$lang["edit_theme_category"]="Edit theme category";
$lang["emailthemecollectionmessageexternal"]="has e-mailed you collections of resources from $applicationname."; 
$lang["emailthememessage"]="has e-mailed you a selection of themes from $applicationname which have been added to your 'My collections' page.";
$lang["clicklinkviewthemes"]="Click the link below to view the themes.";
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
$lang["relatedresources-id"]="Related resources - ID%id%"; # %id% will be replaced, e.g. Related Resources - ID57
$lang["relatedresources-restype"]="Related resources - %restype%"; # Use %RESTYPE%, %restype% or %Restype% as a placeholder. The placeholder will be replaced with the resource type in plural, using the same case. E.g. "Related resources - %restype%" -> "Related resources - photos"
$lang["indexedsearchable"]="Indexed, searchable fields";
$lang["clearform"]="Clear form";
$lang["similarresources"]="similar resources"; # e.g. 17 similar resources
$lang["similarresource"]="similar resource"; # e.g. 1 similar resource
$lang["nosimilarresources"]="No similar resources";
$lang["emailresource"]="E-mail";
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
$lang["log-missinglang"]="[type] (missing lang)"; # [type] will be replaced.

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
$lang["sendnewpassword"]="Send new password";
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
$lang["researchrequestassignedmessage"]="Your research request has been assigned to a member of the team. Once we've completed the research you'll receive an e-mail with a link to all the resources that we recommend.";
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
$lang["confirmaddgroup"]="Are you sure you want to add all the members in this group?";
$lang["backtoteamhome"]="Back to team centre home";
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
$lang["languageselection"]="Language selection";
$lang["language"]="Language";
$lang["changeyourpassword"]="Change your password";
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
$lang["iaccept"]="I Accept";
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
$lang["share_theme_category"]="Share theme category";
$lang["share_theme_category_subcategories"]="Include themes in subcategories for external users?";
$lang["email_theme_category"]="E-mail theme category";
$lang["generateurl"]="Generate URL";
$lang["generateurls"]="Generate URLs";
$lang["generateexternalurl"]="Generate external URL";
$lang["generateexternalurls"]="Generate external URLs";
$lang["generateurlinternal"]="The below URL will work for existing users only.";
$lang["generateurlexternal"]="The below URL will work for everyone and does not require a login.";
$lang["generatethemeurlsexternal"]="The below URLs will work for everyone and do not require a login.";
$lang["showexistingthemeshares"]="Show existing shares for themes in this category";
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
$lang["noexternalsharing"]="No external sharing.";
$lang["sharedcollectionaddwarning"]="Warning: This collection has been shared with external users. The resource you have added has now been made available to these users. Click 'share' to manage the external access for this collection.";
$lang["restrictedsharecollection"]="You have restricted access to one or more of the resources in this collection and therefore sharing is prohibited.";
$lang["selectgenerateurlexternal"]="To create a URL that will work for external users (people that do not have a login) please choose the access level you wish to grant to the resources.";
$lang["selectgenerateurlexternalthemecat"]="To create URLs that will allow access to external users (people that do not have a login) please choose the access level you wish to grant to the resources.";
$lang["externalselectresourceaccess"]="If you are e-mailing external users, please select the level of access you would like to grant to this resource.";
$lang["externalselectresourceexpires"]="If you are e-mailing external users, please select an expiry date for the generated URL.";
$lang["externalshareexpired"]="Sorry, this share has expired and is no longer available.";
$lang["notapprovedsharecollection"]="One or more resources in this collection are not active and therefore sharing is prohibited.";
$lang["notapprovedsharetheme"]="Sharing is prohibited for at least one collection, because one or more resources is not active.";
$lang["notapprovedresources"]="The following resources are not active and cannot be added to a shared collection: ";


# New for 1.3
$lang["savesearchitemstocollection"]="Save results to collection";
$lang["removeallresourcesfromcollection"]="Remove all resources from this collection";
$lang["deleteallresourcesfromcollection"]="Delete all resources in this collection";
$lang["deleteallsure"]="Are you sure you wish to DELETE these resources? This will delete the resources themselves, not just remove them from this collection.";
$lang["batchdonotaddcollection"]="(do not add to a collection)";
$lang["collectionsthemes"]="Related themes and public collections";
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
$lang["requiredfields"]="Some required fields were not completed. Please review the form and try again";
$lang["viewduplicates"]="View duplicate resources";
$lang["duplicateresources"]="Duplicate resources";
$lang["userlog"]="User log";
$lang["ipaddressrestriction"]="IP address restriction (optional)";
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
$lang["userresourcesapproved"]="Your submitted resources have been approved:";
$lang["userresourcesunsubmitted"]="The following user contributed resources have been unsubmitted, and no longer require review:";
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
$lang["setup-visitwiki"]='Visit the <a target="_blank" href="http://wiki.resourcespace.org/index.php/Main_Page">ResourceSpace Documentation Wiki</a> for more information about customizing your installation.';
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
$lang["setup-mysqlserver"]="MySQL server:";
$lang["setup-mysqlusername"]="MySQL username:";
$lang["setup-mysqlpassword"]="MySQL password:";
$lang["setup-mysqldb"]="MySQL database:";
$lang["setup-mysqlbinpath"]="MySQL binary path:";
$lang["setup-generalsettings"]="General settings";
$lang["setup-baseurl"]="Base URL:";
$lang["setup-emailfrom"]="Email from address:";
$lang["setup-emailnotify"]="Email notify:";
$lang["setup-spiderpassword"]="Spider password:";
$lang["setup-scramblekey"]="Scramble key:";
$lang["setup-apiscramblekey"]="API scramble key:";
$lang["setup-paths"]="Paths";
$lang["setup-pathsdetail"]="For each path, enter the path without a trailing slash to each binary.  To disable a binary, leave the path blank.  Any auto-detected paths have already been filled in.";
$lang["setup-applicationname"]="Application name:";
$lang["setup-basicsettingsfooter"]="NOTE: The only <strong>required</strong> settings are on this page.  If you're not interested in checking out the advanced options, you may click below to begin the installation process.";
$lang["setup-if_mysqlserver"]='The IP address or <abbr title="Fully Qualified Domain Name">FQDN</abbr> of your MySQL server installation.  If MySql is installed on the same server as your web server, use "localhost".';
$lang["setup-if_mysqlusername"]="The username used to connect to your MySQL server.  This user must have rights to create tables in the database named below.";
$lang["setup-if_mysqlpassword"]="The password for the MySQL username entered above.";
$lang["setup-if_mysqldb"]="The Name of the MySQL database RS will use. (This database must exist.)";
$lang["setup-if_mysqlbinpath"]="The path to the MySQL client binaries - e.g. mysqldump. NOTE: This is only needed if you plan to use the export tool.";
$lang["setup-if_baseurl"]="The 'base' web address for this installation.  NOTE: No trailing slash.";
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
$lang["setup-themes_as_home"]="Use the themes page as the home page?";
$lang["setup-remote_storage_locations"]="Remote Storage Locations";
$lang["setup-use_remote_storage"]="Use remote storage?";
$lang["setup-if_useremotestorage"]="Check this box to configure remote storage locations for RS. (To use another server for filestore.)";
$lang["setup-storage_directory"]="Storage directory";
$lang["setup-if_storagedirectory"]="Where to put the media files. Can be absolute (/var/www/blah/blah) or relative to the installation. NOTE: No trailing slash.";
$lang["setup-storage_url"]="Storage URL";
$lang["setup-if_storageurl"]="Where the storagedir is available. Can be absolute (http://files.example.com) or relative to the installation. NOTE: No trailing slash.";
$lang["setup-ftp_settings"]="FTP settings";
$lang["setup-if_ftpserver"]="Only necessary if you plan to use the FTP upload feature.";
$lang["setup-login_to"]="Login to";
$lang["setup-configuration_file_output"]="Configuration file output";

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

$lang["mycollection_notpublic"]="You cannot make your 'My Collection' into a public collection or theme. Please create a new collection for this purpose.";

$lang["resourcemetadata"]="Resource metadata";
$lang["columnheader-expires"]=$lang["expires"]="Expires";
$lang["expires-date"]="Expires: %date%"; # %date will be replaced, e.g. Expires: Never
$lang["never"]="Never";

$lang["approved"]="Approved";
$lang["notapproved"]="Not approved";

$lang["userrequestnotification3"]="If this is a valid request, click the link below to review the details and approve the user account.";

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

$lang["useasthemethumbnail"]="Use this resource as a theme category thumbnail?";
$lang["sessionexpired"]="You have been automatically logged out because you were inactive for more than 30 minutes. Please enter your login details to continue.";

$lang["resourcenotinresults"]="The current resource is no longer within your active search results so next/previous navigation is not possible.";
$lang["publishstatus"]="Save with publish status:";
$lang["addnewcontent"]="New content (page, name)";
$lang["hitcount"]="Hit count";
$lang["downloads"]="Downloads";

$lang["addremove"]="";

##  Translations for standard log entries
$lang["all_users"]="all users";
$lang["new_resource"]="new resource";

$lang["invalidextension_mustbe"]="Invalid extension, must be";
$lang["invalidextension_mustbe-extensions"]="Invalid extension, must be %EXTENSIONS."; # Use %EXTENSIONS, %extensions or %Extensions as a placeholder. The placeholder will be replaced with the filename extensions, using the same case. E.g. "Invalid extension, must be %EXTENSIONS" -> "Invalid extension, must be JPG"
$lang["allowedextensions"]="Allowed extensions";
$lang["allowedextensions-extensions"]="Allowed extensions: %EXTENSIONS"; # Use %EXTENSIONS, %extensions or %Extensions as a placeholder. The placeholder will be replaced with the filename extensions, using the same case. E.g. "Allowed Extensions: %EXTENSIONS" -> "Allowed Extensions: JPG, PNG"

$lang["alternativebatchupload"]="Batch upload alternative files";

$lang["confirmdeletefieldoption"]="Are you sure you wish to DELETE this field option?";

$lang["cannotshareemptycollection"]="This collection is empty and cannot be shared.";	
$lang["cannotshareemptythemecategory"]="This theme category contains no themes and cannot be shared.";

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

$lang["backtothemes"]="Back to themes";
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
$lang["emaileveryndays"]="E-mail me this report every ? days";
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
$lang["treenode-resource_types_and_fields"]="Resource types / fields";
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

# Permissions Manager
$lang["permissionsmanager"]="Permissions manager";
$lang["backtogroupmanagement"]="Back to group management";
$lang["searching_and_access"]="Searching / access";
$lang["metadatafields"]="Metadata fields";
$lang["resource_creation_and_management"]="Resource creation / management";
$lang["themes_and_collections"]="Themes / collections";
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
$lang["can_create_resources_and_upload_files-admins"]="Can create resources / upload files<br>(admin users; resources go to 'Active' state)";
$lang["can_create_resources_and_upload_files-general_users"]="Can create resources / upload files<br>(normal users; resources go to 'Pending Submission' state via My Contributions)";
$lang["can_delete_resources"]="Can delete resources<br>(to which the user has write access)";
$lang["can_manage_archive_resources"]="Can manage archive resources";
$lang["can_manage_alternative_files"]="Can manage alternative files";
$lang["can_tag_resources_using_speed_tagging"]="Can tag resources using 'Speed Tagging'<br>(if enabled in the configuration)";
$lang["enable_bottom_collection_bar"]="Enable bottom collection bar ('Lightbox')";
$lang["can_publish_collections_as_themes"]="Can publish collections as themes";
$lang["can_see_all_theme_categories"]="Can see all theme categories";
$lang["can_see_theme_category"]="Can see theme category";
$lang["can_see_theme_sub_category"]="Can see theme subcategory";
$lang["display_only_resources_within_accessible_themes"]="When searching, display only resources that exist within themes to which the user has access";
$lang["can_access_team_centre"]="Can access the Team Centre area";
$lang["can_manage_research_requests"]="Can manage research requests";
$lang["can_manage_resource_requests"]="Can manage resource requests";
$lang["can_manage_content"]="Can manage content (intro/help text)";
$lang["can_bulk-mail_users"]="Can bulk-mail users";
$lang["can_manage_users"]="Can manage users";
$lang["can_manage_keywords"]="Can manage keywords";
$lang["can_access_system_setup"]="Can access the System Setup area";
$lang["can_change_own_password"]="Can change own account password";
$lang["can_manage_users_in_children_groups"]="Can manage users in children groups to the user's group only";
$lang["can_email_resources_to_own_and_children_and_parent_groups"]="Can email resources to users in the user's own group, children groups and parent group only";

$lang["nodownloadcollection"]="You do not have access to download any of the resources in this collection.";

$lang["progress"]="Progress";
$lang["ticktodeletethisresearchrequest"]="Tick to delete this request";

$lang["done"]="Done.";

$lang["latlong"]="Lat / long";
$lang["geographicsearch"]="Geographic search";

$lang["geographicsearch_help"]="Drag to select a search area.";

$lang["purge"]="Purge";
$lang["purgeuserstitle"]="Purge users";
$lang["purgeusers"]="Purge users";
$lang["purgeuserscommand"]="Delete users accounts that have not been active in the last % months, but were created before this period.";
$lang["purgeusersconfirm"]="This will delete % user accounts. Are you sure?";
$lang["pleaseenteravalidnumber"]="Please enter a valid number";
$lang["purgeusersnousers"]="There are no users to purge.";

$lang["editallresourcetypewarning"]="Warning: changing the resource type will delete any resource type specific metadata currently stored for the selected resources.";
$lang["editresourcetypewarning"]="Warning: changing the resource type will delete any resource type specific metadata currently stored for this resource.";

$lang["geodragmode"]="Drag mode";
$lang["geodragmodearea"]="position pin";
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

$lang["header-upload-subtitle"] = "Step %number: %subtitle"; # %number, %subtitle will be replaced, e.g. Step 1: Specify Default Content For New Resources
$lang["local_upload_path"] = "Local upload folder";
$lang["ftp_upload_path"] = "FTP folder";
$lang["foldercontent"] = "Folder content";
$lang["intro-local_upload"] = "Select one or more files from the local upload folder and click  \'Upload\'. Once the files are uploaded they can be deleted from the upload folder.";
$lang["intro-ftp_upload"] = "Select one or more files from the FTP folder and click  \'Upload\'.";
$lang["intro-java_upload"] = "Click  \'Browse\' to locate one or more files and then click  \'Upload\'.";
$lang["intro-java_upload-replace_resource"] = "Click  \'Browse\' to locate a file and then click  \'Upload\'.";
$lang["intro-single_upload"] = "Click  \'Browse\' to locate a file and then click  \'Upload\'.";
$lang["intro-plupload"] = "Click \'Add files\' to locate one or more files and then click \'Start upload\'.";
$lang["intro-plupload_dragdrop"] = "Drag and drop or click \'Add files\' to locate one or more files and then click \'Start upload\'.";
$lang["intro-plupload_upload-replace_resource"] = "Click \'Add files\' to locate a file and then click \'Start upload\'.";
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
$lang["matchingresourceslabel"]="Matching resources";

$lang["saving"]="Saving...";
$lang["saved"]="Saved";

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
$lang["findcollectionthemes"]="Themes";
$lang["upload-options"]="Upload options";
$lang["user-preferences"]="User preferences";
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
$lang["share-resource"]="Share Resource";
$lang["scope"]="Scope";
$lang["downloadmetadata"]="Download Metadata";
$lang["downloadingmetadata"]="Downloading Metadata";
$lang["file-contains-metadata"]="The file you are now downloading contains all of the Metadata for this resource.";
$lang["metadata"]="Metadata";
$lang["textfile"]="Text File";

# Comments field titles, prompts and default placeholders
$lang['comments_box-title']="Comments";
$lang['comments_box-policy']="Comments Policy";
$lang['comments_box-policy-placeholder']="Please add text to comments_policy entry in the site text";		# only shown if Admin User and no policy set
$lang['comments_in-response-to']="in response to";
$lang['comments_respond-to-this-comment']="Reply";
$lang['comments_in-response-to-on']="on";
$lang['comments_anonymous-user']="Anonymous";
$lang['comments_submit-button-label']="Submit";
$lang['comments_body-placeholder']="Add a comment";
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
