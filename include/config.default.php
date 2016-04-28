<?php
/**
 * This file contains the default configuration settings.
 * 
 * **** DO NOT ALTER THIS FILE! ****
 * 
 * If you need to change any of the below values, copy
 * them to config.php and change them there.
 * 
 * This file will be overwritten when you upgrade and
 * ensures that any new configuration options are set to
 * a sensible default value.
 * 
 * @package ResourceSpace
 * @subpackage Configuration
 */


/* ---------------------------------------------------
BASIC PARAMETERS
------------------------------------------------------ */
$mysql_server="localhost";	# Use 'localhost' if MySQL is installed on the same server as your web server.
$mysql_username="root";		# MySQL username
$mysql_password="";			# MySQL password
$mysql_db="resourcespace";			# MySQL database name
# $mysql_charset="utf8"; # MySQL database connection charset, uncomment to use.

# The path to the MySQL client binaries - e.g. mysqldump
# (only needed if you plan to use the export tool)
$mysql_bin_path="/usr/bin"; # Note: no trailing slash

# Force MySQL Strict Mode? (regardless of existing setting) - This is useful for developers so that errors that might only occur when Strict Mode is enabled are caught. Strict Mode is enabled by default with some versions of MySQL. The typical error caused is when the empty string ('') is inserted into a numeric column when NULL should be inserted instead. With Strict Mode turned off, MySQL inserts NULL without complaining. With Strict Mode turned on, a warning/error is generated.
$mysql_force_strict_mode=false;

# If true, it does not remove the backslash from DB queries, and doesn't do any special processing.
# to them. Unless you need to store '\' in your fields, you can safely keep the default.
$mysql_verbatim_queries=false;

# Ability to record important DB transactions (e.g. INSERT, UPDATE, DELETE) in a sql file to allow replaying of changes since DB was last backed.
# You may schedule cron jobs to delete this sql log file and perform a mysqldump of the database at the same time.
# Note that there is no built in database backup, you need to take care of this yourself!
#
# WARNING!! Ensure the location defined by $mysql_log_location is not in a web accessible directory -it is advisable to either block access in the web server configuration or make the file write only by the web service account
$mysql_log_transactions=false;
#$mysql_log_location="/var/resourcespace_backups/sql_log.sql";

$baseurl="http://my.site/resourcespace"; # The 'base' web address for this installation. Note: no trailing slash
$email_from="resourcespace@my.site"; # Where system e-mails appear to come from
$email_notify="resourcespace@my.site"; # Where resource/research/user requests are sent
$email_notify_usergroups=array(); # Use of email_notify is deprecated as system notifications are now sent to the appropriate users based on permissions and user preferences. This variable can be set to an array of usergroup references and will take precedence.

# Indicates which users can update very low level configuration options for example debug_log.
$system_architect_user_names = array('admin');		// Warning: this is for experienced technical users, typically ResourceSpace providers.

$spider_password="TBTT6FD"; # The password required for spider.php - IMPORTANT - randomise this for each new installation. Your resources will be readable by anyone that knows this password.
$spider_usergroup=2; # The user group that will be used to access the resource list for the spider index.
$spider_access=array(0,1); # Which access level(s) are required when producing the index (0=Open, 1=Restricted, 2=Confidential/Hidden).

$email_from_user=true; #enable user-to-user emails to come from user's address by default (for better reply-to), with the user-level option of reverting to the system address

# Scramble resource paths? If this is a public installation then this is a very wise idea.
# Set the scramble key to be a hard-to-guess string (similar to a password).
# To disable, set to the empty string ("").
$scramble_key="abcdef123";

# If you agree to send occasional statistics to Montala, leave this set to 'yes'.
# The following two numeric metrics alone will be sent every 7 days:
# - Number of resources
# - Number of users
# The information will only be used to provide totals on the Montala site, e.g 
# global number of installations, users and resources.
$send_statistics=true;

# Enable work-arounds required when installed on Microsoft Windows systems
$config_windows=false;

# Server charset (needed when dealing with filenames in some situations, e.g. at collection download).
#$server_charset = ''; # E.g. 'UTF-8', 'ISO-8859-1' or 'Windows-1252'.

# ---- Paths to various external utilities ----

# If using ImageMagick/GraphicsMagick, uncomment and set next 2 lines
# $imagemagick_path='/sw/bin';
# $ghostscript_path='/sw/bin';
$ghostscript_executable='gs';

# If using FFMpeg to generate video thumbs and previews, uncomment and set next line.
# $ffmpeg_path='/usr/bin';

# Install Exiftool and set this path to enable metadata-writing when resources are downloaded
# $exiftool_path='/usr/local/bin';

# Path to Antiword - for text extraction / indexing of Microsoft Word Document (.doc) files
# $antiword_path='/usr/bin';

# Path to pdftotext - part of the XPDF project, see http://www.foolabs.com/xpdf/
# Enables extraction of text from PDF files
# $pdftotext_path='/usr/bin';

# Path to blender
# $blender_path='/usr/bin/';

# Path to an archiver utility - uncomment and set the lines below if download of collections is enabled ($collection_download = true)
# Example given for Linux with the zip utility:
# $archiver_path = '/usr/bin';
# $archiver_executable = 'zip';
# $archiver_listfile_argument = "-@ <";

# Example given for Linux with the 7z utility:
# $archiver_path = '/usr/bin';
# $archiver_executable = '7z';
# $archiver_listfile_argument = "@";

# Example given for Windows with the 7z utility:
# $archiver_path = 'C:\Program\7-Zip';
# $archiver_executable = '7z.exe';
# $archiver_listfile_argument = "@";

$use_zip_extension=false; //use php-zip extension instead of $archiver or $zipcommand


/* ---------------------------------------------------
OTHER PARAMETERS

The below options customise your installation. 
You do not need to review these items immediately
but may want to review them once everything is up 
and running.
------------------------------------------------------ */


# Uncomment and set next two lines to configure storage locations (to use another server for file storage)
#
# Note - these are really only useful on Windows systems where mapping filestore to a remote drive or other location is not trivial.
# On Unix based systems it's usually much easier just to make '/filestore' a symbolic link to another location.
#
#$storagedir="/path/to/filestore"; # Where to put the media files. Can be absolute (/var/www/blah/blah) or relative to the installation. Note: no trailing slash
#$storageurl="http://my.storage.server/filestore"; # Where the storagedir is available. Can be absolute (http://files.example.com) or relative to the installation. Note: no trailing slash

# Store original files separately from RS previews? If this setting is adjusted with resources in the system you'll need to run ../pages/tools/filestore_separation.php.
$originals_separate_storage=false;

include "version.php";

$applicationname="ResourceSpace"; # The name of your implementation / installation (e.g. 'MyCompany Resource System')
$applicationdesc=""; # Subtitle (i18n translated) if $header_text_title=true;
$header_favicon="gfx/interface/favicon.png";

#replace header logo with text, application name and description
$header_text_title=false;

#If using the old background method, create a clickable area of the resourcespace logo graphic. Defaults to Homepage
$header_link=true;
#If $slimheader is off you must set the link height and width to match the size of the logo graphic in pixels (Legacy support)
#$header_link_height=;
#$header_link_width=;

###### SLIM HEADER DESIGN ######
#In order to maintain backwards compatibility you must do the following to turn on the Slim Header Design
#1. Set #slimheader=true;
#2. (If you want a custom Logo) Set a source image location for the header logo with $linkedheaderimgsrc="/your/location.png";
#3. (If you want to see the optional slim themes) Enable slim themes (See Below)

## Slim Themes ##
# The Slim Charcoal theme can be added to the available themes like so: 
# $available_themes=array("multi", "whitegry","greyblu","black","slimcharcoal");

## Defaults ##
#This uses an img tag to display the header and will automatically include a link to the homepage. 
$slimheader=false;
# Custom source location for the header image (includes baseurl, requires leading "/"). Will default to the resourcespace logo if left blank. Recommended image size: 350px(X) x 80px(Y)

# Set this to true in order for the top bar to remain present when scrolling down the page
$slimheader_fixed_position=false;

$linkedheaderimgsrc="";
###### END SLIM HEADER #######

# Change the Header Logo link to another address by uncommenting and setting the variable below
# $header_link_url=http://my-alternative-header-link

# Include ResourceSpace version header in View Source
$include_rs_header_info=true;

# Used for specifying custom colour for header background
$header_colour_style_override='';

# Available languages
# If $defaultlanguage is not set, the brower's default language will be used instead
$defaultlanguage="en"; # default language, uses ISO 639-1 language codes ( en, es etc.)
$languages["en"]="British English";
$languages["en-US"]="American English";
$languages["ar"]="العربية";
$languages["id"]="Bahasa Indonesia"; # Indonesian
$languages["ca"]="Català"; # Catalan
$languages["zh-CN"]="简体字"; # Simplified Chinese
$languages["da"]="Dansk"; # Danish
$languages["de"]="Deutsch"; # German
$languages["el"]="Ελληνικά"; # Greek
$languages["es"]="Español"; # Spanish
$languages["es-AR"]="Español (Argentina)";
$languages["fr"]="Français"; # French
$languages["hr"]="Hrvatski"; # Croatian
$languages["it"]="Italiano"; # Italian
$languages["jp"]="日本語"; # Japanese
$languages["nl"]="Nederlands"; # Dutch
$languages["no"]="Norsk"; # Norwegian
$languages["pl"]="Polski"; # Polish
$languages["pt"]="Português"; # Portuguese
$languages["pt-BR"]="Português do Brasil"; # Brazilian Portuguese
$languages["ru"]="Русский язык"; # Russian
$languages["fi"]="Suomi"; # Finnish
$languages["sv"]="Svenska"; # Swedish


# Disable language selection options (Includes Browser Detection for language)
$disable_languages=false;

# Show the language chooser on the bottom of each page
$show_language_chooser=true;

# Allow Browser Language Detection
$browser_language=true;

# FTP settings for batch upload
# Only necessary if you plan to use the FTP upload feature.
$ftp_server="my.ftp.server";
$ftp_username="my_username";
$ftp_password="my_password";
$ftp_defaultfolder="temp/";

# Can users change passwords?
$allow_password_change=true;

# search params
# Common keywords to ignore both when searching and when indexing.
# Copy this block to config.php and uncomment the languages you would like to use.

$noadd=array();

# English stop words
$noadd=array_merge($noadd, array("", "a","the","this","then","another","is","with","in","and","where","how","on","of","to", "from", "at", "for", "-", "by", "be"));

# Swedish stop words (copied from http://snowball.tartarus.org/algorithms/swedish/stop.txt 20101124)
#$noadd=array_merge($noadd, array("och", "det", "att", "i", "en", "jag", "hon", "som", "han", "på", "den", "med", "var", "sig", "för", "så", "till", "är", "men", "ett", "om", "hade", "de", "av", "icke", "mig", "du", "henne", "då", "sin", "nu", "har", "inte", "hans", "honom", "skulle", "hennes", "där", "min", "man", "ej", "vid", "kunde", "något", "från", "ut", "när", "efter", "upp", "vi", "dem", "vara", "vad", "över", "än", "dig", "kan", "sina", "här", "ha", "mot", "alla", "under", "någon", "eller", "allt", "mycket", "sedan", "ju", "denna", "själv", "detta", "åt", "utan", "varit", "hur", "ingen", "mitt", "ni", "bli", "blev", "oss", "din", "dessa", "några", "deras", "blir", "mina", "samma", "vilken", "er", "sådan", "vår", "blivit", "dess", "inom", "mellan", "sånt", "varför", "varje", "vilka", "ditt", "vem", "vilket", "sitta", "sådana", "vart", "dina", "vars", "vårt", "våra", "ert", "era", "vilkas"));


# How many results trigger the 'suggestion' feature, -1 disables the feature
# WARNING - there is a significant performance penalty for enabling this feature as it attempts to find the most popular keywords for the entire result set.
# It is not recommended for large systems.
$suggest_threshold=-1; 


$max_results=200000;
$minyear=1980; # The year of the earliest resource record, used for the date selector on the search form. Unless you are adding existing resources to the system, probably best to set this to the current year at the time of installation.

# Set folder for home images. Ex: "gfx/homeanim/mine/" 
# Files should be numbered sequentially, and will be auto-counted.
$homeanim_folder="gfx/homeanim/gfx";

# Set different size for slideshow images (value  in pixels). This is honoured by transform plugin so still allows easy replacement of images. 	
# Can be used as config override in conjunction with $homeanim_folder as above (for large images you may also want to set $home_themeheaders, $home_themes, $home_mycollections and $home_helpadvice to false).
# $home_slideshow_width=517;
# $home_slideshow_height=350;

# Small slideshow mode (old slideshow)
$small_slideshow = true;

# Big slideshow mode (Fullscreen slideshow)
# ----------------------------------
# You will need to configure much bigger slideshow images with $home_slideshow_width and $home_slideshow_height, and regenerate
# your slideshow images using the transform plugin. This is recommended to be used along with the slim header.
$slideshow_big=false;

# Number of seconds for slideshow to wait before changing image (must be greater than 1)
$slideshow_photo_delay = 5;


/** Dash Config Options **/
# Enable home dash functionality (on by default, recommended)
$home_dash = true;
# Define the available styles per type.
$tile_styles['srch'] = array('thmbs', 'multi', 'blank');
$tile_styles['ftxt'] = array('ftxt');
$tile_styles['conf'] = array('blank');
# Place the default dash (tiles set for all_users) on the home page for anonymous users with none of the drag 'n' drop functionality.
$anonymous_default_dash=true;
# use shadows on all tile content (Built in support for transparent tiles)
$dash_tile_shadows=false;
# All user permissions for the dash are revoked and the dash admin can manage a single dash for all users. 
# Only those with admin privileges can modify the dash and this must be done from the Team Centre > Manage all user dash tiles (One dash for all)
$managed_home_dash = false;
# Allows Dash Administrators to have their own dash whilst all other users have the managed dash ($managed_home_dash must be on)
$unmanaged_home_dash_admins = false;

/*
* Dash tile color picker/ selector
* If $dash_tile_colour = true and there are no colour options, a colour picker (jsColor) will be used instead
* Example of colour options array:
* $dash_tile_colour_options = array('0A8A0E' => 'green', '0C118A' => 'blue');
*/
$dash_tile_colour         = true;
$dash_tile_colour_options = array();
/* End Dash Config Options */

/*
 * Legacy Tile options 
 * The home_dash option and functionality has replaced these config options 
 */

	# Options to show/hide the tiles on the home page
	$home_themeheaders=false;
	$home_themes=true;
	$home_mycollections=true;
	$home_helpadvice=true;
	$home_advancedsearch=false;
	$home_mycontributions=false;
	#
	# Custom panels for the home page.
	# You can add as many panels as you like. They must be numbered sequentially starting from zero (0,1,2,3 etc.)
	#
	# You may want to turn off $home_themes etc. above if you want ONLY your own custom panels to appear on the home page.
	#
	# The below are examples.
	#
	# $custom_home_panels[0]["title"]="Custom Panel A";
	# $custom_home_panels[0]["text"]="Custom Panel Text A";
	# $custom_home_panels[0]["link"]="search.php?search=example";
	#
	# You can add additional code to a link like this:
	# $custom_home_panels[0]["additional"]="target='_blank'";
	#
	# $custom_home_panels[1]["title"]="Custom Panel B";
	# $custom_home_panels[1]["text"]="Custom Panel Text B";
	# $custom_home_panels[1]["link"]="search.php?search=example";
	#
	# $custom_home_panels[2]["title"]="Custom Panel C";
	# $custom_home_panels[2]["text"]="Custom Panel Text C";
	# $custom_home_panels[2]["link"]="search.php?search=example";

/*
 * End of Legacy Tile Config
 */ 


# Optional 'quota size' for allocation of a set amount of disk space to this application. Value is in GB.
# Note: Unix systems only.
# $disksize=150;

# Disk Usage Warnings - require running check_disk_usage.php
# Percentage of disk space used before notification is sent out. The number should be between 1 and 100.
#$disk_quota_notification_limit_percent_warning=90;
# interval in hours to wait before sending another percent warning 
#$disk_quota_notification_interval=24;
$disk_quota_notification_email='';

# GB of disk space left before uploads are disabled.
# This causes disk space to be checked before each upload attempt
# $disk_quota_limit_size_warning_noupload=10;

# Set your time zone below (default GMT)
if (function_exists("date_default_timezone_set")) {date_default_timezone_set("GMT");}

# IPTC header - Character encoding auto-detection
# If using IPTC headers, specify any non-ascii characters used in your local language
# to aid with character encoding auto-detection.
# Several encodings will be attempted and if a character in this string is returned then this is considered
# a match.
# For English, there is no need to specify anything here (i.e. just an empty string) - this will disable auto-detection and assume UTF-8
# The example below is for Norwegian.
# $iptc_expectedchars="æøåÆØÅ";
$iptc_expectedchars="";

# Which field do we drop the EXIF data in to? (when NOT using exiftool)
# Comment out these lines to disable basic EXIF reading.
# See exiftool for more advanced EXIF/IPTC/XMP extraction.
$exif_comment=18;
$exif_model=52;
$exif_date=12;

# If exiftool is installed, you can optionally enable the metadata report available on the View page. 
# You may want to enable it on the usergroup level by overriding this config option in System Setup.

$metadata_report=false;

# Allow a link to re-extract metadata per-resource (on the View Page) to users who have edit abilities.
$allow_metadata_revert=false;

# Use Exiftool to attempt to extract specified resolution and unit information from files (ex. Adobe files) upon upload.
$exiftool_resolution_calc=false;

# Set to true to strip out existing EXIF,IPTC,XMP metadata when adding metadata to resources using exiftool.
$exiftool_remove_existing=false; 

# If Exiftool path is set, write metadata to files upon download if possible.
$exiftool_write=true;
# Omit conversion to utf8 when exiftool writes (this happens when $mysql_charset is not set, or $mysql_charset!="utf8")
$exiftool_write_omit_utf8_conversion=false;

/*
These two options allow the user to choose whether they want to write metadata on downloaded files.

$force_exiftool_write_metadata should be used by system admins to force writing or not writing metadata on a file on download
$exiftool_write_option will be used on both resource and collection download. On collection download, an extra option (check box)
will be available so the user can specify whether they want to write metadata on the downloaded files
example use:
$force_exiftool_write_metadata = false; $exiftool_write_option = true; means ResourceSpace will write to the files
$force_exiftool_write_metadata = true; $exiftool_write_option = false; means ResourceSpace will force users to not write metadata to the files

Note: this honours $exiftool_write so if that option is false, this will not work
*/
$force_exiftool_write_metadata = false;
$exiftool_write_option         = false;

# Set metadata_read to false to omit the option to extract metadata.
$metadata_read=true;

# If metadata_read is true, set whether the default setting on the edit/upload page is to extract metadata (true means the metadata will be extracted)
$metadata_read_default=true;

# If Exiftool path is set, do NOT send files with the following extensions to exiftool for processing
# For example: $exiftool_no_process=array("eps","png");
$exiftool_no_process=array();

# Which field do we drop the original filename in to?
$filename_field=51;

# If using imagemagick, should colour profiles be preserved? (for larger sizes only - above 'scr')
$imagemagick_preserve_profiles=false;
$imagemagick_quality=90; # JPEG quality (0=worst quality/lowest filesize, 100=best quality/highest filesize)

# Allow unique quality settings for each preview size. This will use $imagemagick_quality as a default setting.
# If you want to adjust the quality settings for internal previews you must also set $internal_preview_sizes_editable=true
$preview_quality_unique=false;

# Allow editing of internal sizes? This will require additional updates to css settings!
$internal_preview_sizes_editable=false;

# Colorspace usage
# Use "RGB" for ImageMagick versions before 6.7.6-4
# Use "RGB" for GraphicsMagick
# Use "sRGB" for ImageMagick version 6.7.6-4 and newer
$imagemagick_colorspace="RGB";

# Default color profile
# This is going to be used for all rendered files (or just thumbnails if $imagemagick_preserve_profiles
# is set
#$default_icc_file='my-profile.icc';

# To use the Ghostscript command -dUseCIEColor or not (generally true but added in some cases where scripts might want to turn it off).
$dUseCIEColor=true;

# Some files can take a long time to preview, or take too long (PSD) or involve too many sofware dependencies (RAW). 
# If this is a problem, these options allow EXIFTOOL to attempt to grab a preview embedded in the file.
# (Files must be saved with Previews). If a preview image can't be extracted, RS will revert to ImageMagick.
$photoshop_thumb_extract=false;
$cr2_thumb_extract=false; 
$nef_thumb_extract=false;
$dng_thumb_extract=false;
$rw2_thumb_extract=true;
$raf_thumb_extract=false;

# Turn on creation of a miff file for Photoshop EPS.
# Off by default because it is 4x slower than just ripping with ghostscript, and bloats filestore.
$photoshop_eps_miff=false;

# Attempt to resolve a height and width of the ImageMagick file formats at view time
# (enabling may cause a slowdown on viewing resources when large files are used)
$imagemagick_calculate_sizes=false;

# If using imagemagick for PDF, EPS and PS files, up to how many pages should be extracted for the previews?
# If this is set to more than one the user will be able to page through the PDF file.
$pdf_pages=30;

# When uploading PDF files, split each page to a separate resource file?
$pdf_split_pages_to_resources=false;

# Use VideoJS for video playback (as opposed to FlashPlayer, which we are deprecating)
$videojs=true;

# Create a preview video for ffmpeg compatible files? A FLV (Flash Video) file will automatically be produced for supported file types (most video types - AVI, MOV, MPEG etc.)
/* Examples of preview options to convert to different types (don't forget to set the extension as well):
* MP4: $ffmpeg_preview_options = '-f mp4 -ar 22050 -b 650k -ab 32k -ac 1';
*/
$ffmpeg_preview=true; 
$ffmpeg_preview_seconds=120; # how many seconds to preview
$ffmpeg_preview_extension="flv";
$ffmpeg_preview_min_width=32;
$ffmpeg_preview_min_height=18;
$ffmpeg_preview_max_width=480;
$ffmpeg_preview_max_height=270;
$ffmpeg_preview_options="-f flv -ar 22050 -b 650k -ab 32k -ac 1";
# ffmpeg_global_options: options to be applied to every ffmpeg command. 
#$ffmpeg_global_options = "-loglevel panic"; # can be used for recent versions of ffmpeg when verbose output prevents run_command completing
#$ffmpeg_global_options = "-v panic"; # use for older versions of ffmpeg  as above
$ffmpeg_global_options = "";
#$ffmpeg_snapshot_fraction=0.1; # Set this to specify a point in the video at which snapshot image is taken. Expressed as a proportion of the video duration so must be set between 0 and 1. Only valid if duration is greater than 10 seconds.
#$ffmpeg_snapshot_seconds=10;  # Set this to specify the number of seconds into the video at which snapshot should be taken, overrides the $ffmpeg_snapshot_fraction setting

# $ffmpeg_command_prefix - Ability to add prefix to command when calling ffmpeg 
# Example for use on Linux using nice to avoid slowing down the server
# $ffmpeg_command_prefix = "nice - n 10";

# If uploaded file is in the preview format already, should we transcode it anyway?
# Note this is now ON by default as of switching to MP4 previews, because it's likely that uploaded MP4 files will need a lower bitrate preview and
# were not intended to be the actual preview themselves.
$ffmpeg_preview_force=false;

# Option to always try and play the original file instead of preview - useful if recent change to $ffmpeg_preview_force doesn't suit e.g. if all users are
# on internal network and want to see HQ video
$video_preview_original=false;

# Encode preview asynchronous?
$ffmpeg_preview_async=false;

# Find out and obey the Pixel Aspect Ratio
$ffmpeg_get_par=false;

# Use New qscale to maintain quality (else uses -sameq)
$ffmpeg_use_qscale = true;

# FFMPEG - generation of alternative video file sizes/formats
# It is possible to automatically generate different file sizes and have them attached as alternative files.
# See below for examples.
# The blocks must be numbered sequentially (0, 1, 2).
# Ensure the formats you are specifiying with vcodec and acodec are supported by checking 'ffmpeg -formats'.
# "lines_min" refers to the minimum number of lines (vertical pixels / height) needed in the source file before this alternative video file will be created. It prevents the creation of alternative files that are larger than the source in the event that alternative files are being used for creating downscaled copies (e.g. for web use).
#
# Params examples for different cases:
# Converting .mov to .avi use "-g 60 -vcodec msmpeg4v2 -acodec pcm_u8 -f avi";
#
# $ffmpeg_alternatives[0]["name"]="QuickTime H.264 WVGA";
# $ffmpeg_alternatives[0]["filename"]="quicktime_h264";
# $ffmpeg_alternatives[0]["extension"]="mov";
# $ffmpeg_alternatives[0]["params"]="-vcodec h264 -s wvga -aspect 16:9 -b 2500k -deinterlace -ab 160k -acodec mp3 -ac 2";
# $ffmpeg_alternatives[0]["lines_min"]=480;
# $ffmpeg_alternatives[0]["alt_type"]='mywebversion';

# $ffmpeg_alternatives[1]["name"]="Larger FLV";
# $ffmpeg_alternatives[1]["filename"]="flash";
# $ffmpeg_alternatives[1]["extension"]="FLV";
# $ffmpeg_alternatives[1]["params"]="-s wvga -aspect 16:9 -b 2500k -deinterlace -ab 160k -acodec mp3 -ac 2";
# $ffmpeg_alternatives[1]["lines_min"]=480;
# $ffmpeg_alternatives[1]["alt_type"]='mywebversion';

# To be able to run certain actions asyncronus (eg. preview transcoding), define the path to php:
# $php_path="/usr/bin";

# Use qt-faststart to make mp4 previews start faster
# $qtfaststart_path="/usr/bin";
# $qtfaststart_extensions=array("mp4","m4v","mov");

# Allow users to request accounts?
$allow_account_request=true;

# Should the system allow users to request new passwords via the login screen?
$allow_password_reset=true;

# Highlight search keywords when displaying results and resources?
$highlightkeywords=true;

# Search on day in addition to month/year?
$searchbyday=false;

# Allow download of original file for resources with "Restricted" access.
# For the tailor made preview sizes / downloads, this value is set per preview size in the system setup.
$restricted_full_download=false;

# Instead of showing a download size as "Restricted", the download size is hidden - ONLY IF the user has not got the ability to request ("q" permission).
$hide_restricted_download_sizes=false;

# Also search the archive and display a count with every search? (performance penalty)
$archive_search=false;

# Display the Research Request functionality?
# Allows users to request resources via a form, which is e-mailed.
$research_request=false;

# Country search in the right nav? (requires a field with the short name 'country')
$country_search=false;

# Resource ID search blank in right nav? (probably only needed if $config_search_for_number is set to true) 
$resourceid_simple_search=false;

# Enable date option on simple search bar
$simple_search_date=true;

# Enable sorting resources in other ways:
$colour_sort=true;
$popularity_sort=true;
$random_sort=false;
$title_sort=false; // deprecated, based on resource table column
$country_sort=false; // deprecated, based on resource table column
$original_filename_sort=false; // deprecated, based on resource table column

# What is the default sort order?
# Options are date, colour, relevance, popularity, country
$default_sort="relevance";

# What is the default sort order when viewing collection resources?
# Options are date, colour, relevance, popularity, country
$default_collection_sort="relevance";

# Enable themes (promoted collections intended for showcasing selected resources)
$enable_themes=true;

# Use the themes page as the home page?
$use_theme_as_home=false;

# Use the recent page as the home page?
$use_recent_as_home=false;


# Show images along with theme category headers (image selected is the most popular within the theme category)
$theme_images=true;
$theme_images_number=1; # How many to auto-select (if none chosen manually)
$theme_images_align_right=false; # Align theme images to the right on the themes page? (particularly useful when there are multiple theme images)
$show_theme_collection_stats=false; # Show count of themes and resources in theme category

# How many levels of theme category to show.
# If this is set to more than one, a dropdown box will appear to allow browsing of theme sub-levels
$theme_category_levels=1;

# Theme direct jump mode
# If set, sub category levels DO NOT appear and must be directly linked to using custom home panels or top navigation items (or similar).
# $theme_category_levels must be greater than 1.
$theme_direct_jump=false;

#Force Collections lists on the Themes page to be in Descending order.
$descthemesorder=false;

##  Advanced Search Options
##  Defaults (all false) shows advanced search in the search bar but not the home page or top navigation.
##  To disable advanced search altogether, set 
##      $advancedsearch_disabled = true;
##      $home_advancedsearch=false;
##      $advanced_search_nav=false;

#Hide advanced search on search bar
$advancedsearch_disabled = false;

# Display the advanced search as a 'search' link in the top navigation
$advanced_search_nav=false;

# Show Contributed by on Advanced Search (ability to search for resources contributed by a specific user)
$advanced_search_contributed_by = true;

# Show Media section on Advanced Search
$advanced_search_media_section = true;

# Do not display 'search results' link in the top navigation
$disable_searchresults = false;

# Display a 'Recent' link in the top navigation
$recent_link=true;
# Display 'View New Material' link in the quick search bar (same as 'Recent')
$view_new_material=false;
# For recent_link and view_new_material, and use_recent_as_home, the quantity of resources to return.
$recent_search_quantity=1000;

# Display Help and Advice link in the top navigation
$help_link=true;

# Display Search Results link in top navigation
$search_results_link=true;

# Display a 'My Collections' link in the top navigation
# Note that permission 'b' is needed for collection_manage.php to be displayed
$mycollections_link=false;

# Display a 'My Requests' link in the top navigation
$myrequests_link=false;

# Display a 'Research Request' link in the top navigation
$research_link=true;

# Display a Themes link in Top Navigation if Themes is enabled
$themes_navlink = true;

# display an alert icon next to the team centre link 
# and the relevant team centre item when there are requests that need managing
# only affects users with permissions to do this.
$team_centre_alert_icon = false;

# Hide mycontributions link from regular users
$mycontributions_userlink=true;
# Display a 'My Contributions' link in the top navigation for admin (permission C)
$mycontributions_link = false;

# Require terms for download?
$terms_download=false;

# Require terms on first login?
$terms_login=false;

##  Thumbnails options

# In the collection frame, show or hide thumbnails by default? ("hide" is better if collections are not going to be heavily used).
$thumbs_default="show";
#  Automatically show thumbs when you change collection (only if default is show)
$autoshow_thumbs = false;
# How many thumbnails to show in the collections panel until a 'View All...' link appears, linking to a search in the main window.
$max_collection_thumbs=150;
# Show an Empty Collection link which will empty the collection of resources (not delete them)
$emptycollection = false;

# Options for number of results to display per page:
$results_display_array=array(24,48,72,120,240);
# How many results per page? (default)
$default_perpage=48;
# Options for number of results to display for lists (user admin, public collections, manage collections)
$list_display_array=array(15,30,60);
# How many results per page? (default)
$default_perpage_list=15;


# Group based upload folders? (separate local upload folders for each group)
$groupuploadfolders=false;
# Username based upload folders? (separate local upload folders for each user based on username)
$useruploadfolders=false;

# Enable order by rating? (require rating field updating to rating column)
$orderbyrating=false;

# Zip command to use to create zip archive (uncomment to enable download of collections as a zip file)
# $zipcommand =
# This setting is deprecated and replaced by $collection_download and $collection_download_settings.

# Set $collection_download to true to enable download of collections as archives (e.g. zip files).
# The setting below overrides - if true - the $zipcommand.
# You also have to uncomment and set $collection_download_settings for it to work.
# (And don't forget to set $archiver_path etc. in the path section.)
$collection_download = false;

# The total size, in bytes, of the collection download possible PRIOR to zipping. Prevents users attempting very large downloads.
$collection_download_max_size = 1024 * 1024 * 1024; # default 1GB.

# Example given for Linux with the zip utility:
# $collection_download_settings[0]["name"] = 'ZIP';
# $collection_download_settings[0]["extension"] = 'zip';
# $collection_download_settings[0]["arguments"] = '-j';
# $collection_download_settings[0]["mime"] = 'application/zip';

# Example given for Linux with the 7z utility:
# $collection_download_settings[0]["name"] = 'ZIP';
# $collection_download_settings[0]["extension"] = 'zip';
# $collection_download_settings[0]["arguments"] = 'a -tzip';
# $collection_download_settings[0]["mime"] = 'application/zip';
# $collection_download_settings[1]["name"] = '7Z';
# $collection_download_settings[1]["extension"] = '7z';
# $collection_download_settings[1]["arguments"] = 'a -t7z';
# $collection_download_settings[1]["mime"] = 'application/x-7z-compressed';

# Example given for Linux with tar (saves time if large compressed resources):
# $collection_download_settings[0]["name"] = 'tar file';
# $collection_download_settings[0]["extension"] = 'tar';
# $collection_download_settings[0]["arguments"] = '-cf ';
# $collection_download_settings[0]["mime"] = 'application/tar';
# $archiver_path = '/bin';
# $archiver_executable = 'tar';
# $archiver_listfile_argument = " -T ";


# Example given for Windows with the 7z utility:
# $collection_download_settings[0]["name"] = 'ZIP';
# $collection_download_settings[0]["extension"] = 'zip';
# $collection_download_settings[0]["arguments"] = 'a -scsWIN -tzip';
# $collection_download_settings[0]["mime"] = 'application/zip';
# ...

# Option to write a text file into zipped collections containing resource data
$zipped_collection_textfile=false;
# Set default option for text file download to "no"
$zipped_collection_textfile_default_no=false;

# Enable speed tagging feature? (development)
$speedtagging=false;
$speedtaggingfield=1;
# To set speed tagging field by resource type, you can set $speedtagging_by_type[resource_type]=resource_type_field; 
# default will be $speedtaggingfield
# example to add speed tags for Photo type(1) to the Caption(18) field:
# $speedtagging_by_type[1]=18; 


# A list of types which get the extra video icon in the search results
$videotypes=array(3);
# add icons for resource types - add style IconResourceType<resourcetyperef> and 
# IconResourceTypeLarge<resourcetyperef> similar to videotypes, this option overrides $videtypes option
$resource_type_icons=false;


# Sets the default colour theme (defaults to white)
$defaulttheme="";


/** DEPRECATED **/

	# Theme chips available. This makes it possible to add new themes and chips using the same structure.
	# To create a new theme, you need a chip in gfx/interface, a graphics folder called gfx/<themename>,
	# and a css file called css/Col-<themename>.css
	# this is a basic way of adding general custom themes that do not affect SVN checkouts, 
	# though css can also be added in plugins as usual.
	 
	$available_themes=array("multi", "whitegry","greyblu","black");

	# NOTE: Do not add custom themes to $available_themes_by_default.
	# This is being used to know which themes are custom
	$available_themes_by_default = array("multi", "whitegry","greyblu","black","slimcharcoal");

	# Uncomment and set the next line to lock to one specific colour scheme (e.g. greyblu/whitegry).
	# $userfixedtheme="whitegry";

/** END OF DEPRECATED **/

/** USER PREFERENCES **/
$user_preferences = true;

/* Should the "purge users" function be available? */
$user_purge=true;

# List of active plugins.
# Note that multiple plugins must be specified within array() as follows:
# $plugins=array("loader","rss","messaging","googledisplay"); 
$plugins = array('transform', 'rse_version');

# Uncomment and set the next line to allow anonymous access. 
# You must set this to the USERNAME of the USER who will represent all your anonymous users
# Note that collections will be shared among all anonymous users - it's therefore usually best to turn off all collections functionality for the anonymous user.
#$anonymous_login="guest";

# Domain Linked Anonymous Access
# Uncomment and set to allow different anonymous access USERS for different domains. 
# The usernames are the same rules for just a single anonymous account but you must match them against the full domain $Baseurl that they will be using.
# Note that collections will be shared among all anonymous users for each domain - it's therefore usually best to turn off all collections functionality for the anonymous user.
/* $anonymous_login = array(
		"http://example.com" => "guest",
		"http://test.com" => "guest2"
		); */

# When anonymous access is on, show login in a modal.
$anon_login_modal=false;

$anonymous_user_session_collection=true;

# Enable captioning and ranking of collections (deprecated - use $collection_commenting instead)
$collection_reorder_caption=false; 

# Enable collection commenting and ranking
$collection_commenting = false;

# Add the collections footer
$collections_footer = true;

# Footer text applied to all e-mails (blank by default)
$email_footer="";

# Contact Sheet feature, and whether contact sheet becomes resource.
# Requires ImageMagick/Ghostscript.
$contact_sheet=true;
# Produce a separate resource file when creating contact sheets?
$contact_sheet_resource=false; 
# Ajax previews in contact sheet configuration. 
$contact_sheet_previews=true;
# Ajax previews in contact sheet, preview image size in pixels. 
$contact_sheet_preview_size="250x250";
# Select a contact sheet font. Default choices are 
# helvetica,times,courier (standard) and dejavusanscondensed for more Unicode support (but embedding/subsetting makes it slower).
# There are also several other fonts included in the tcpdf lib (but not ResourceSpace), which provide unicode support
# To embed more elaborate fonts, acquire the files from the TCPDF distribution or create your own using TCPDF utilities, and install them in the lib/tcpdf/fonts folder.
$contact_sheet_font="helvetica";
# if using a custom tcpdf font, subsetting is available, but can be turned off
$subsetting=true; 
# allow unicode filenames? (stripped out by default in tcpdf but since collection names may 
# have special characters, probably want to try this on.)
$contact_sheet_unicode_filenames=true;
# Set font sizes for contactsheet
$titlefontsize=10; // Contact Sheet Title
$refnumberfontsize=8; // This includes field text, not just ref number
# If making a contact sheet with list sheet style, use these fields in contact sheet:
$config_sheetlist_fields = array(8);
$config_sheetlist_include_ref=true;
# If making a contact sheet with thumbnail sheet style, use these fields in contact sheet:
$config_sheetthumb_fields = array();
$config_sheetthumb_include_ref=true;
# If making a contact sheet with one resource per page sheet style, use these fields in contact sheet:
$config_sheetsingle_fields = array(8);
$config_sheetsingle_include_ref=true;
# experimental sorting (doesn't include ASC/DESC yet).
$contactsheet_sorting=false;

# Add header text to contact page?
$contact_sheet_include_header=true;
# Give user option to add header text to contact page?
$contact_sheet_include_header_option=false;

# Add logo image to contact page? set contact_sheet_logo if set to true
$include_contactsheet_logo=false;
#$contact_sheet_logo="gfx/contactsheetheader.png"; // can be a png/gif/jpg or PDF file

# if $contact_sheet_logo_resize==false, the image is sized at 300ppi or the PDF retains it's original dimensions.
# if true, the logo is scaled to a hardcoded percentage of the page size.
$contact_sheet_logo_resize=true; 

# Give user option to add/remove logo?
#$contact_sheet_logo_option=true;

# Optional example footer html to include on contact sheet
#$contact_sheet_custom_footerhtml='<div style="text-align: center" >XXX MAIN STREET, CITY, ABC 123 - TEL: (111) 000-8888 - FAX: (000) 111-9999</div><table style="width:100%;margin:auto;"><tr><td style="width:50%;text-align: center" >resourcespace.org</td><td style="width:50%;text-align: center" >&#0169; ReourceSpace. All Rights Reserved.</td></tr></table>';

# Make images in contactsheet links to the resource view page?
$contact_sheet_add_link=true;
# Give user option to enable links?
$contact_sheet_add_link_option=false;

$contact_sheet_single_select_size=false;

# Set this to FALSE in order to remove the link from the collection bar
$contact_sheet_link_on_collection_bar = true;

##  Contact Print settings - paper size options
$papersize_select = '
<option value="a4">A4 - 210mm x 297mm</option>
<option value="a3">A3 - 297mm x 420mm</option>
<option value="letter">US Letter - 8.5" x 11"</option>
<option value="legal">US Legal - 8.5" x 14"</option>
<option value="tabloid">US Tabloid - 11" x 17"</option>';

## Columns options (May want to limit options if you are adding text fields to the Thumbnail style contact sheet).
$columns_select = '
<option value=2>2</option>
<option value=3>3</option>
<option value=4 selected>4</option>
<option value=5>5</option>
<option value=6>6</option>
<option value=7>7</option>';

# Show related themes and public collections panel on Resource View page.
$show_related_themes=true;

# Multi-lingual support for e-mails. Try switching this to true if e-mail links aren't working and ASCII characters alone are required (e.g. in the US).
$disable_quoted_printable_enc=false;

# Watermarking - generate watermark images for 'internal' (thumb/preview) images.
# Groups with the 'w' permission will see these watermarks when access is 'restricted'.
# Uncomment and set to the location of a watermark graphic.
# NOTE: only available when ImageMagick is installed.
# NOTE: if set, you must be sure watermarks are generated for all images; This can be done using pages/tools/update_previews.php?previewbased=true
# NOTE: also, if set, restricted external emails will recieve watermarked versions. Restricted mails inherit the permissions of the sender, but
# if watermarks are enabled, we must assume restricted access requires the equivalent of the "w" permission
# $watermark="gfx/watermark.png";

# Set to true to watermark thumb/preview for groups with the 'w' permission even when access is 'open'.
# This makes sense if $terms_download is active.
$watermark_open=false;

# Set to true to extend $watermark_open to the search page. $watermark_open must be set to true.
$watermark_open_search=false; 

# Simple search even more simple
# Set to 'true' to make the simple search bar more basic, with just the single search box.
$basic_simple_search=false;

# include an "all" toggle checkbox for Resource Types in Search bar
$searchbar_selectall=false;

# move search and clear buttons to bottom of searchbar
$searchbar_buttons_at_bottom=true;

# Hide the main simple search field in the searchbar (if using only simple search fields for the searchbar)
$hide_main_simple_search=false;

# Custom top navigation links.
# You can add as many panels as you like. They must be numbered sequentially starting from zero (0,1,2,3 etc.)
# URL should be absolute, or include $baseurl as below, because a relative URL will not work from the Team Center.
# Since configuration is prior to $lang availability, use a special syntax prefixing the string "(lang)" to access $lang['mytitle']:
# ex:
# $custom_top_nav[0]["title"]="(lang)mytitle";

# $custom_top_nav[0]["title"]="Example Link A";
# $custom_top_nav[0]["link"]="$baseurl/pages/search.php?search=a";
#
# $custom_top_nav[1]["title"]="Example Link B";
# $custom_top_nav[1]["link"]="$baseurl/pages/search.php?search=b";


# Use original filename when downloading a file?
$original_filenames_when_downloading=true;

# Should the download filename have the size appended to it?
$download_filenames_without_size = false;

# When $original_filenames_when_downloading, should the original filename be prefixed with the resource ID?
# This ensures unique filenames when downloading multiple files.
# WARNING: if switching this off, be aware that when downloading a collection as a zip file, a file with the same name as another file in the collection will overwrite that existing file. It is therefore advisiable to leave this set to 'true'.
$prefix_resource_id_to_filename=true;

# When using $prefix_resource_id_to_filename above, what string should be used prior to the resource ID?
# This is useful to establish that a resource was downloaded from ResourceSpace and that the following number
# is a ResourceSpace resource ID.
$prefix_filename_string="RS";

# Display a 'new' flag next to new themes (by default themes created < 2 weeks ago)
# Note: the age take days as parameter. Anything less than that would mean that a theme becomes old after a few hours which is highly unlikely.
$flag_new_themes     = true;
$flag_new_themes_age = 14;

# Create file checksums?
$file_checksums=false;

# Calculate checksums on first 50k and size if true or on the full file if false
$file_checksums_50k = true;

# Block duplicate files based on checksums? (has performance impact). May not work reliably with $file_checksums_offline=true unless checksum script is run frequently. 
$file_upload_block_duplicates=false;

# checksums will not be generated in realtime; a background cron job must be used
# recommended if files are large, since the checksums can take time
$file_checksums_offline = true;

# Default group when adding new users;
$default_group=2;

# Enable 'custom' access level?
# Allows fine-grained control over access to resources.
# You may wish to disable this if you are using metadata based access control (search filter on the user group)
$custom_access=true;

# Set the Default Level for Custom Access. 
# This will only work for resources that haven't been set to custom previously, otherwise they will show their previously set values.
/*
	0 - Open
	1 - Restricted
	2 - Confidential
*/
$default_customaccess=2;

# How are numeric searches handled?
#
# If true:
# 		If the search keyword is numeric then the resource with the matching ID will be shown
# If false:
#		The search for the number provided will be performed as with any keyword. However, if a resource with a matching ID number if found then this will be shown first.
$config_search_for_number=false;

# Display the download as a 'save as' link instead of redirecting the browser to the download (which sometimes causes a security warning).
# For the Opera and Internet Explorer 7 browsers this will always be enabled regardless of the below setting as these browsers block automatic downloads by default.
$save_as=false;

# Allow resources to be e-mailed / shared (internally and externally)
$allow_share=true;
$enable_theme_category_sharing=false;

# Always create a collection when sharing an individual resource via email
$share_resource_as_collection=false;

# Use a custom stylesheet when sharing externally.
# Note: $custom_stylesheet_external_share_path can be set anywhere inside websites' root folder.
# eg.: '/plugins/your plugin name/css/external_shares.css'
$custom_stylesheet_external_share = false;
$custom_stylesheet_external_share_path = '';

# Hide display of internal URLs when sharing collections. Intended to prevent inadvertently sending external users invalid URLs
$hide_internal_sharing_url=false;

# Allow theme names to be batch edited in the Themes page.
$enable_theme_category_edit=true;

# Should those with 'restricted' access to a resource be able to share the resource?
$restricted_share=false;

# Should those that have been granted open access to an otherwise restricted resource be able to share the resource?
$allow_custom_access_share=false;

# Should a user that has contributed a resource always have open access to it?
$open_access_for_contributor=false;

# Should a user that has contributed a resource always have edit access to it? (even if the resource is live)
$edit_access_for_contributor=false;

# Prevent granting of open access if a user has edit permissions. Setting to true will allow group permissions ('e*' and 'ea*') to determine editability.
$prevent_open_access_on_edit_for_active=false;

# Auto-completion of search (quick search only)
$autocomplete_search=true;
$autocomplete_search_items=15;
$autocomplete_search_min_hitcount=10; # The minimum number of times a keyword appears in metadata before it qualifies for inclusion in auto-complete. Helps to hide spurious values.

# Automatically order checkbox lists (alphabetically)
$auto_order_checkbox=true;

# Use a case insensitive sort when automatically order checkbox lists (alphabetically)
$auto_order_checkbox_case_insensitive=false;

# Order checkbox lists vertically (as opposed to horizontally, as HTML tables normally work)
$checkbox_ordered_vertically=true;

# When batch uploading, show the 'add resources to collection' selection box
$enable_add_collection_on_upload=true;

# When batch uploading, allow users to set collection public as part of upload process
# also allows assignment to themes for users who have appropriate privileges
$enable_public_collection_on_upload=false;

# Batch Uploads, default is "Add to New Collection". Turn off to default to "Do not Add to Collection"
$upload_add_to_new_collection=true;
# Batch Uploads, enables the "Add to New Collection" option.
$upload_add_to_new_collection_opt=true;
# Batch Uploads, enables the "Do Not Add to New Collection" option, set to false to force upload to a collection.
$upload_do_not_add_to_new_collection_opt=true;
# Batch Uploads, require that a collection name is entered, to override the Upload<timestamp> default behavior
$upload_collection_name_required=false;
#Batch uploads - always upload to My Collection
$upload_force_mycollection=false;
#Batch Uploads, do not display hidden collections
$hidden_collections_hide_on_upload=false;
#Batch Uploads, include show/hide hidden collection toggle. Must have $hidden_collections_hide_on_upload=true;
$hidden_collections_upload_toggle=false;

# When batch uploading, enable the 'copy resource data from existing resource' feature
$enable_copy_data_from=true;

# Show clear button on the upload page
$clearbutton_on_upload=true;

# Show clear button on the edit page
$clearbutton_on_edit=true;

# Store Resource Refs when uploading, this is useful for other developer tools to hook into the upload.
$store_uploadedrefs=false;

# Always record the name of the resource creator for new records.
# If false, will only record when a resource is submitted into a provisional status.
$always_record_resource_creator = true;

# Enable the 'related resources' field when editing resources.
$enable_related_resources=true;

# Adds an option to the upload page which allows Resources Uploaded together to all be related 
/* requires $enable_related_resources=true */
/* $php_path MUST BE SET */
$relate_on_upload=false;

# Option to make relating all resources at upload the default option if $relate_on_upload is set
$relate_on_upload_default=false;

#Size of the related resource previews on the resource page. Usually requires some restyling (#RelatedResources .CollectionPanelShell)
#Takes the preview code such as "col","thm"
$related_resource_preview_size="col";

# Enable the 'keep me logged in at this workstation' option at the login form
# If the user then selects this, a 100 day expiry time is set on the cookie.
$allow_keep_logged_in=true;
#Remember Me Checked By Default
$remember_me_checked = true;

# Show the link to 'user contributed assets' on the My Contributions page
# Allows non-admin users to see the assets they have contributed
$show_user_contributed_resources=true;

# Show the contact us link?
$contact_link=true;
$nav2contact_link = false;

# Show the about us link?
$about_link=true;

# When uploading resources (batch upload) and editing the template, should the date be reset to today's date?
# If set to false, the previously entered date is used.
$reset_date_upload_template=true;
$reset_date_field=12; # Which date field to reset? (if using multiple date fields)

# When uploading resources (batch upload) and editing the template, should all values be reset to blank or the default value every time?
$blank_edit_template=false;

# Show expiry warning when expiry date has been passed
$show_expiry_warning=true;

# Make selection box in collection edit menu that allows you to select another accessible collection to base the current one upon.
# It is helpful if you would like to make variations on collections that are heavily commented upon or re-ordered.
$enable_collection_copy=true;

# Default resource types to use for searching (leave empty for all)
$default_res_types="";

# Show the Resource ID on the resource view page.
$show_resourceid=true;

# Show the resource type on the resource view page.
$show_resource_type=false;

# Show the access on the resource view page.
$show_access_field=true;

# Show the 'contributed by' on the resource view page.
$show_contributed_by=true;

# Show the extension after the truncated text in the search results.
$show_extension_in_search=false;

# Should the category tree field (if one exists) default to being open instead of closed?
$category_tree_open=false;

# Should the category tree status window be shown?
$category_tree_show_status_window=true;

# Should searches using the category tree use AND for heirarchical keys?
$category_tree_search_use_and=false;

# Length of a user session. This is used for statistics (user sessions per day) and also for auto-log out if $session_autologout is set.
$session_length=30;

# Automatically log a user out at the end of a session (a period of idleness equal to $session_length above).
$session_autologout=false;

# Randomised session hash?
# Setting to 'true' means each new session is completely unique each login. This may be more secure as the hash is less easy to guess but means that only one user can use a given user account at any one time.
$randomised_session_hash=false;

# Allow browsers to save the login information on the login form.
$login_autocomplete=true;

# Option to ignore case when validating username at login. 
$case_insensitive_username=false;

# Password standards - these must be met when a user or admin creates a new password.
$password_min_length=7; # Minimum length of password
$password_min_alpha=1; # Minimum number of alphabetical characters (a-z, A-Z) in any case
$password_min_numeric=1; # Minimum number of numeric characters (0-9)
$password_min_uppercase=0; # Minimum number of upper case alphabetical characters (A-Z)
$password_min_special=0; # Minimum number of 'special' i.e. non alphanumeric characters (!@$%& etc.)

# How often do passwords expire, in days? (set to zero for no expiry).
$password_expiry=0;

# How many failed login attempts per IP address until a temporary ban is placed on this IP
# This helps to prevent dictionary attacks.
$max_login_attempts_per_ip=20;

# How many failed login attempts per username until a temporary ban is placed on this IP
$max_login_attempts_per_username=5;

# How long the user must wait after failing the login $max_login_attempts_per_ip or $max_login_attempts_per_username times.
$max_login_attempts_wait_minutes=10;

# How long to wait (in seconds) before returning a 'password incorrect' message (for logins) or 'e-mail not found' message (for the request new password page)
# This can help to deter 'brute force' attacks, trying to find user's passwords or e-mail addresses in use.
$password_brute_force_delay=4;

# Use imperial instead of metric for the download size guidelines
$imperial_measurements=false;

# Use day-month-year format? If set to false format will be month-day-year.
$date_d_m_y=true;

# What is the default resource type to use for batch upload templates?
$default_resource_type=1;

# If ResourceSpace is behind a proxy, enabling this will mean the "X-Forwarded-For" Apache header is used
# for the IP address. Do not enable this if you are not using such a proxy as it will mean IP addresses can be
# easily faked.
$ip_forwarded_for=false;

# When extracting text from documents (e.g. HTML, DOC, TXT, PDF) which field is used for the actual content?
# Comment out the line to prevent extraction of text content
$extracted_text_field=72;

# Should the resources that are in the archive state "User Contributed - Pending Review" (-1) be
# visible in the main searches (as with resources in the active state)?
# The resources will not be downloadable, except to the contributer and those with edit capability to the resource.
$pending_review_visible_to_all=false;

# Should the resources that are in the archive state "User Contributed - Pending submission" (-2) be
# searchable (otherwise users can search only for their own resources pending submission
$pending_submission_searchable_to_all=false;

# Enable user rating of resources
# Users can rate resources using a star ratings system on the resource view page.
# Average ratings are automatically calculated and used for the 'popularity' search ordering.
$user_rating=false;

# Enable public collections
# Public collections are collections that have been set as public by users and are searchable at the bottom
# of the themes page. Note that, if turned off, it will still be possible for administrators to set collections
# as public as this is how themes are published.
$enable_public_collections=true;

# Hide owner in list of public collections
$collection_public_hide_owner=true;

# Custom User Registration Fields
# -------------------------------
# Additional custom fields that are collected and e-mailed when new users apply for an account
# Uncomment the next line and set the field names, comma separated
#$custom_registration_fields="Phone Number,Department";
# Which of the custom fields are required?
# $custom_registration_required="Phone Number";
# You can also set that particular fields are displayed in different ways as follows:
# $custom_registration_types["Department"]=1;
# Types are as follows:
# 	1: Normal text box (default)
# 	2: Large text box
#   3: Drop down box (set options using $custom_registration_options["Field Name"]=array("Option 1","Option 2","Option 3");
#   4: HTML block, e.g. help text paragraph (set HTML using $custom_registration_html["Field Name"]="<b>Some HTML</b>";
#      Optionally, you can add the language to this, ie. $custom_registration_html["Field Name"]["en"]=...
#   5: Checkbox, set options using $custom_registration_options["Field Name"]=array("0:Option 1","1:Option 2","Option 3");
#      where 0: and 1: are unchecked and checked(respectively) by default, if not specified then assumed unchecked.  Example:
#      $custom_registration_options["Department"]=array("0:Human Resources","1:Marketing","1:Sales","IT");
#      Note that if this field is listed in $custom_registration_required, then the user will be forced to check at least one option.


# Allow user group to be selected as part of user registration?
# User groups available for user selection must be specified using the 'Allow registration selection' option on each user group
# in System Setup.
# Only useful when $user_account_auto_creation=true;
$registration_group_select=false;

# Custom Resource/Collection Request Fields
# -----------------------------------------
# Additional custom fields that are collected and e-mailed when new resources or collections are requested.
# Uncomment the next line and set the field names, comma separated
#$custom_request_fields="Phone Number,Department";
# Which of the custom fields are required?
# $custom_request_required="Phone Number";
# You can also set that particular fields are displayed in different ways as follows:
# $custom_request_types["Department"]=1;
# Types are as follows:
# 	1: Normal text box (default)
# 	2: Large text box
#   3: Drop down box (set options using $custom_request_options["Field Name"]=array("Option 1","Option 2","Option 3");
#   4: HTML block, e.g. help text paragraph (set HTML usign $custom_request_html="<b>Some HTML</b>";


# Send an e-mail to the address set at $email_notify above when user contributed
# resources are submitted (status changes from "User Contributed - Pending Submission" to "User Contributed - Pending Review").
$notify_user_contributed_submitted=true;
$notify_user_contributed_unsubmitted=false;

# When requesting feedback, allow the user to select resources (e.g. pick preferred photos from a photo shoot).
$feedback_resource_select=false;
# When requesting feedback, display the contents of the specified field (if available) instead of the resource ID. 
#$collection_feedback_display_field=51;


# Uncomment and set the below value to set the maximum size of uploaded file that thumbnail/preview images will be created for.
# This is useful when dealing with very large files that may place a drain on system resources - for example 100MB+ Adobe Photoshop files will take a great deal of cpu/memory for ImageMagick to process and it may be better to skip the automatic preview in this case and add a preview JPEG manually using the "Upload a preview image" function on the resource edit page.
# The value is in MB.
# $preview_generate_max_file_size=100;

# Prevent previews from creating versions that result in the same size?
# If true pre, thm, and col sizes will not be considered.
$lean_preview_generation=false;

# Should resource views be logged for reporting purposes?
# Note that general daily statistics for each resource are logged anyway for the statistics graphs
# - this option relates to specific user tracking for the more detailed report.
$log_resource_views=false;

# A list of file extentions of file types that cannot be uploaded for security reasons.
# For example; uploading a PHP file may allow arbirtary execution of code, depending on server security settings.
$banned_extensions=array("php","cgi","pl","exe","asp","jsp", 'sh', 'bash');

#Set a default access value for the upload page. This will override the default resource template value.
#Change the value of this option to the access id number
$override_access_default=false;
#Set a default status value for the upload page. This will override the default resource template value.
#Change the value of this option to the status id number
$override_status_default=false;

# When adding resource(s), in the upload template by the status and access fields are hidden.
# Set the below option to 'true' to enable these options during this process.
$show_status_and_access_on_upload=false;

# Set Permission required to show "access" and "status" fields on upload, evaluates PHP code so must be preceded with 'return' and end with a semicolon. False = No permission required.
$show_status_and_access_on_upload_perm = "return !checkperm('F*');"; # Stack permissions= " return !checkperm('e0') && !checkperm('c')";

#Access will be shown if this value is set to true. This option acts as an override for the status and access flag.
# Show Status and Access = true && Show Access = true   - Status and Access Shown
# Show Status and Access = false && Show Access = true  - Only Access Shown
# Show Status and Access = true && Show Access = false - Only Status Shown
# Show Status and Access = false && Show Access = false - Neither Shown
# DEFAULT VALUE: = $show_status_and_access_on_upload;
$show_access_on_upload = &$show_status_and_access_on_upload;

# Permission required to show "access" field on upload, this evaluates PHP code so must be preceded with 'return'. True = No permission required. 
# Example below ensures they have permissions to edit active resources.
# $show_access_on_upload_perm = "return checkperm('e0')"; #Stack permissions= "return checkperm('e0') && checkperm('c');";
$show_access_on_upload_perm = "return true;";


# Mime types by extensions.
# used by pages/download.php to detect the mime type of the file proposed to download.
$mime_type_by_extension = array(
    'mov'   => 'video/quicktime',
    '3gp'   => 'video/3gpp',
    'mpg'   => 'video/mpeg',
    'mp4'   => 'video/mp4',
    'avi'   => 'video/msvideo',
    'mp3'   => 'audio/mpeg',
    'wav'   => 'audio/x-wav',
    'jpg'   => 'image/jpeg',
    'jpeg'  => 'image/jpeg',
    'gif'   => 'image/gif',
    'png'   => 'image/png',
    'odt' => 'application/vnd.oasis.opendocument.text',
    'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    'odp' => 'application/vnd.oasis.opendocument.presentation'
  );

# PHP execution time limit
# Default is 5 minutes.
$php_time_limit=300;

# Should the automatically produced FLV file be available as a separate download?
$flv_preview_downloadable=false;

# What is the default value for the user select box, for example when e-mailing resources?
$default_user_select="";

# When multiple dropdowns are used on the simple search box, should selecting something from one or more dropdowns
# limit the options available in the other dropdowns automatically? This adds a performance penalty so is off by default.
$simple_search_dropdown_filtering=false;


# When searching, also include themes/public collections at the top?
$search_includes_themes=false;
$search_includes_public_collections=false;
$search_includes_user_collections=false;
$search_includes_resources=true;

# Should the Clear button leave collection searches off by default?
$clear_button_unchecks_collections=true;

# include keywords from collection titles when indexing collections
$index_collection_titles = true;
$index_collection_creator = true; 

# Default home page (when not using themes as the home page).
# You can set other pages, for example search results, as the home page e.g.
# $default_home_page="search.php?search=example";
$default_home_page="home.php";

# Configures separators to use when splitting keywords (in other words - characters to treat as white space)
# You must reindex after altering this if you have existing data in the system (via pages/tools/reindex.php)
# 'Space' is included by default and does not need to be specified below.
$config_separators=array("/","_",".","; ","-","(",")","'","\"","\\", "?");

# trim characters - will be removed from the beginning or end of the string, but not the middle
# when indexing. Format for this argument is as described in PHP trim() documentation.
# leave blank for no extra trimming.
$config_trimchars="";

# Resource field verbatim keyword regex
# Using the index value of [resource field], specifies regex criteria for adding verbatim strings to keywords.
# It solves the problem, for example, indexing an entire "nnn.nnn.nnn" string value when '.' are used in $config_separators.
# $resource_field_verbatim_keyword_regex[1] = '/\d+\.\d+\w\d+\.\d+/';		// this example would add 994.1a9.93 to indexed keywords for field 1.  This can be found using quoted search.

# Global permissions
# Permissions that will be prefixed to all user group permissions
# Handy for setting global options, e.g. for fields
$global_permissions="";

# Global permissions
# Permissions that will be removed from all user group permissions
# Useful for temporarily disabling permissions globally, e.g. to make the system readonly during maintenance.
# Suggested setting for a 'read only' mode: $global_permissions_mask="a,t,c,d,e0,e1,e2,e-1,e-2,i,n,h";
$global_permissions_mask="";

# User account application - auto creation
# By default this is switched off and applications for new user accounts will be sent as e-mails
# Enabling this option means user accounts will be created but will need to be approved by an administrator
# before the user can log in.
$user_account_auto_creation=false;
$user_account_auto_creation_usergroup=2; # which user group for auto-created accounts? (see also $registration_group_select - allows users to select the group themselves).

# Automatically approve ALL account requests (created via $user_account_auto_creation above)?
$auto_approve_accounts=false;

# Automatically approve accounts that have e-mails ending in given domain names.
# E.g. $auto_approve_domains=array("mycompany.com","othercompany.org");
#
# NOTE - only used if $user_account_auto_creation=true above.
# Do not use with $auto_approve_accounts above as it will override this parameter and approve all accounts regardless of e-mail domain.
#
# Optional additional feature... place users in groups depending on email domain. Use syntax:
# $auto_approve_domains=array("mycompany.com"=>2,"othercompany.org"=>3);
# Where 2 and 3 are the ID numbers for the respective user groups.

$auto_approve_domains=array();

# Allows for usernames to be created based on full name (eg. John Mac -> John_Mac)
# Note: user_account_auto_creation needs to be true.
$user_account_fullname_create=false;

# Display a larger preview image on the edit page?
$edit_large_preview=true;

# Allow sorting by resource ID
$order_by_resource_id=false;

# Enable find similar search?
$enable_find_similar=true;

##  The URL that goes in the bottom of the 'emaillogindetails' / 'emailreminder' email templates (save_user function in general.php)
##  If blank, uses $baseurl 
$email_url_save_user = ""; //emaillogindetails
$email_url_remind_user = ""; //emailreminder

# edit.php - disable links to upload preview and manage alternative files
$disable_upload_preview = false;
$disable_alternative_files = false;

#collection_public.php - hide 'access' column
$hide_access_column_public = false;
#collection_manage.php - hide 'access' column
$hide_access_column = false;

# Enable the 'edit all' function in both the collection bar and My Collections
$show_edit_all_link = true;

#Bypass share.php and go straight to e-mail
$bypass_share_screen = false;

# add a prefix to all collection refs, to distinguish them from resource refs
$collection_prefix = "";

# Allow multiple collections to be e-mailed at once
$email_multi_collections = false;

#  Link back to collections from log page - if "" then link is ignored.
#  suggest 
# $back_to_collections_link = "&lt;&lt;-- Back to My Collections &lt;&lt;--";
$back_to_collections_link = "";

# For fields with partial keyword indexing enabled, this determines the minimum infix length
$partial_index_min_word_length=3;

# ---------------------
# Search Display 

# Thumbs Display Fields: array of fields to display on the large thumbnail view.
$thumbs_display_fields=array(8,3);
# array of defined thumbs_display_fields to apply CSS modifications to (via $search_results_title_wordwrap, $search_results_title_height, $search_results_title_trim)
$thumbs_display_extended_fields=array();
	# $search_result_title_height=26;
	$search_results_title_trim=30;
	$search_results_title_wordwrap=100; // Force breaking up of very large titles so they wrap to multiple lines (useful when using multi line titles with $search_result_title_height). By default this is set very high so that breaking doesn't occur. If you use titles that have large unbroken words (e.g. filenames with no spaces) then it may be useful to set this value to something lower, e.g. 20
	
# Enable extra large thumbnails option for search screen
$xlthumbs=true;
# Extra Large Display Fields:  array of fields to display on the xlarge thumbnail view.
$xl_thumbs_display_fields=array(8,3);
# array of defined xl_thumbs_display_fields to apply CSS modifications to (via $xl_search_results_title_wordwrap, $xl_search_results_title_height, $xl_search_results_title_trim)
$xl_thumbs_display_extended_fields=array();
	# $xl_search_result_title_height=26;
	$xl_search_results_title_trim=60;
	$xl_search_results_title_wordwrap=100;
	
# Enable small thumbnails option for search screen
$smallthumbs=true;	
# Small Thumbs Display Fields: array of fields to display on the small thumbnail view.
$small_thumbs_display_fields=array();
# array of defined small_thumbs_display_fields to apply CSS modifications to ($small_search_results_title_wordwrap, $small_search_results_title_height, $small_search_results_title_trim)
$small_thumbs_display_extended_fields=array();
	# $small_search_result_title_height=26;
	$small_search_results_title_trim=30;
	$small_search_results_title_wordwrap=100;

# Enable list view option for search screen
$searchlist=true;
# List Display Fields: array of fields to display on the list view
$list_display_fields=array(8,3,12);
$list_search_results_title_trim=25;

# When returning to search results from the view page via "all" link, bring user to result location of viewed resource?
$search_anchors=true;

# Highlight last viewed result when using $search_anchors
$search_anchors_highlight=false;

# Related Resource title trim: set to 0 to disable
$related_resources_title_trim=15;
	
# SORT Fields: display fields to be added to the sort links in large,small, and xlarge thumbnail views
$sort_fields=array(12);

# TITLE field that should be used as title on the View and Collections pages.
$view_title_field=8; 

# Searchable Date Field:
$date_field=12; 

# Data Joins -- Developer's tool to allow adding additional resource field data to the resource table for use in search displays.
# ex. $data_joins=array(13); to add the expiry date to the general search query result.  
$data_joins=array();

# List View Default Columns
$id_column=true;
$resource_type_column=true;
$date_column=false; // based on creation_date which is a deprecated mapping. The new system distinguishes creation_date (the date the resource record was created) from the date metadata field. creation_date is updated with the date field.
# ---------------------------



# On some PHP installations, the imagerotate() function is wrong and images are rotated in the opposite direction
# to that specified in the dropdown on the edit page.
# Set this option to 'true' to rectify this.
$image_rotate_reverse_options=false;

# Once collections have been published as themes by default they are removed from the user's My Collections. These option leaves them in place.
$themes_in_my_collections=false;

# Show an upload link in the top navigation? (if 't' and 'c' permissions for the current user)
$top_nav_upload=true;
# Show an upload link in the top navigation in addition to 'my contributions' for standard user? (if 'd' permission for the current user)
$top_nav_upload_user=false;
$top_nav_upload_type="plupload"; # The upload type. Options are plupload, ftp, local

# Configure the maximum upload file size; this directly translates into plupload's max_file_size if set
# $plupload_max_file_size = '50M';

# You can set the following line to ''  to disable chunking. May resolve issues with flash uploader.
$plupload_chunk_size='5mb';

# Use the JQuery UI Widget instead of the Queue interface (includes a stop button and optional thumbnail mode
$plupload_widget=false;
$plupload_widget_thumbnails=true;

# Allow users to delete resources?
# (Can also be controlled on a more granular level with the "D" restrictive permission.)
$allow_resource_deletion = true;

# Resource deletion state
# When resources are deleted, the variable below can be set to move the resources into an alternative state instead of removing the resource and its files from the system entirely.
# 
# The resource will still be removed from any collections it has been added to.
#
# Possible options are:
#
# -2	User Contributed Pending Submission (not useful unless deleting user-contributed resources)
# -1	User Contributed Pending Review (not useful unless deleting user-contributed resources) 
# 1		Waiting to be archived
# 2 	Archived
# 3		Deleted (recommended)
$resource_deletion_state=3;

# Does deleting resources require password entry? (single resource delete)
# Off by default as resources are no longer really deleted by default, they are simply moved to a deleted state which is less dangerous - see $resource_deletion_state above.
$delete_requires_password=false;

# Offline processes (e.g. staticsync and create_previews.php) - for process locking, how old does a lock have to be before it is ignored?
$process_locks_max_seconds=60*60*4; # 4 hours default.

# Zip files - the contents of the zip file can be imported to a text field on upload.
# Requires 'unzip' on the command path.
# If the below is not set, but unzip is available, the archive contents will be written to $extracted_text_field
#
# $zip_contents_field=18;
$zip_contents_field_crop=1; # The number of lines to remove from the top of the zip contents output (in order to remove the filename field and other unwanted header information).

# List of extensions that can be processed by ffmpeg.
# Mostly video files.
# @see http://en.wikipedia.org/wiki/List_of_file_formats#Video
$ffmpeg_supported_extensions = array(
		'aaf',
		'3gp',
		'asf',
		'avchd',
		'avi',
		'cam',
		'dat',
		'dsh',
		'flv',
		'm1v',
		'm2v',
		'mkv',
		'wrap',
		'mov',
		'mpeg',
		'mpg',
		'mpe',
		'mp4',
		'mxf',
		'nsv',
		'ogm',
		'ogv',
		'rm',
		'ram',
		'svi',
		'smi',
		'webm',
		'wmv',
		'divx',
		'xvid',
		'm4v',
	);

# A list of extensions which will be ported to mp3 format for preview.
# Note that if an mp3 file is uploaded, the original mp3 file will be used for preview.
$ffmpeg_audio_extensions = array(
    'wav',
    'ogg',
    'aif',
    'aiff',
    'au',
    'cdda',
    'm4a',
    'wma',
    'mp2',
    'aac',
    'ra',
    'rm',
    'gsm'
    );
	
# The audio settings for mp3 previews
$ffmpeg_audio_params = "-acodec libmp3lame -ab 64k -ac 1"; # Default to 64Kbps mono

# A list of file extensions for files which will not have previews automatically generated. This is to work around a problem with colour profiles whereby an image file is produced but is not a valid file format.
$no_preview_extensions=array("icm","icc");

# If set, send a notification when resources expire to this e-mail address.
# This requires batch/expiry_notification.php to be executed periodically via a cron job or similar.
# If this is not set and the script is executed notifications will be sent to resource admins, or users in groups specified in $email_notify_usergroups 
# $expiry_notification_mail="myaddress@mydomain.example";

# What is the default display mode for search results? (smallthumbs/thumbs/list)
$default_display="thumbs";

# Generate thumbs/previews for alternative files?
$alternative_file_previews=true;
$alternative_file_previews_batch=true;


# Permission to show the replace file, preview image only and alternative files options on the resource edit page.
# Overrides required permission of F*
$custompermshowfile=false;

# Display resource title on alternative file management page
$alternative_file_resource_title=true;
# Display resource title on replace file page
$replace_file_resource_title=true;

# enable support for storing an alternative type for each alternate file
# to activate, enter the array of support types below. Note that the 
# first value will be the default
# EXAMPLE: 
# $alt_types=array("","Print","Web","Online Store","Detail");
$alt_types=array("");
# organize View page display according to alt_type
$alt_types_organize=false;

# Display col-size image of resource on alternative file management page
$alternative_file_resource_preview=true;
# Display col-size image of resource on replace file page
$replace_file_resource_preview=true;

# For alternative file previews... enable a thumbnail mouseover to see the preview image?
$alternative_file_previews_mouseover=false;

# Confine public collections display to the collections posted by the user's own group, sibling groups, parent group and children groups.
# All collections can be accessed via a new 'view all' link.
$public_collections_confine_group=false;

# Show public collections in the top nav?
$public_collections_top_nav=false;

# Themes simple view - option to show featured collection categories and featured collections (themes) as basic tiles wih no images.
# Can be tested or used for custom link by adding querystring parameter simpleview=true to themes.php e.g. pages/themes.php?simpleview=true
# NOTE: only works with $themes_category_split_pages=true;
$themes_simple_view=false;
# Option to show images on featured collection and featured collection category tiles if $themes_simple_view is enabled
$themes_simple_images=true;
# Display theme categories as links, and themes on separate pages?
$themes_category_split_pages=false;
# Display breadcrumb-style theme parent links instead of "Subcategories"
$themes_category_split_pages_parents=false;
# Include "Themes" root node before theme level crumbs to add context and link to themes.php
$themes_category_split_pages_parents_root_node=true;
# Navigate to deeper levels in theme category trees? Set to false to link to matching resources directly.
$themes_category_navigate_levels=false;
# If a theme header contains a single collection, allow the title to be a direct link to the collection.
# Drilling down is still possible via the >Expand tool, which replaces >Select when a deeper level exists
$themes_single_collection_shortcut=false;
# Show only collections that have resources the current user can see?
$themes_with_resources_only=false;

# optional columns in themes collection lists
$themes_column_sorting=false; // only works with themes_category_split_pages
$themes_date_column=false;
$themes_ref_column=false;

# Ask the user the intended usage when downloading
$download_usage=false;
$download_usage_options=array("Press","Print","Web","TV","Other");
# Option to block download (hide the button) if user selects specific option(s). Only used as a guide for the user e.g. to indicate that permission should be sought.
#$download_usage_prevent_options=array("Press");

# Should public collections exclude themes
# I.e. once a public collection has been given a theme category, should it be removed from the public collections search results?
$public_collections_exclude_themes=true;

# Show a download summary on the resource view page.
$download_summary=false;

# Ability to alter collection frame height/width
$collection_frame_divider_height=3;
$collection_frame_height=153;

# Ability to hide error messages
$show_error_messages=true;

# Ability to set that the 'request' button on resources adds the item to the current collection (which then can be requested) instead of starting a request process for this individual item.
$request_adds_to_collection=false;

# Option to change the FFMPEG download name from the default ("FLV File" - in the used language) to a custom string.
# $ffmpeg_preview_download_name = "Flash web preview";

# Option to change the original download filename (Use %EXTENSION, %extension or %Extension as a placeholder. Using ? is now DEPRECATED. The placeholder will be replaced with the filename extension, using the same case. E.g. "Original %EXTENSION file" -> "Original WMV file")
# $original_download_name="Original %EXTENSION file";


# Generation of alternative image file sizes/formats using ImageMagick/GraphicMagick
# It is possible to automatically generate different file sizes and have them attached as alternative files.
# This works in a similar way to video file alternatives.
# See below for examples.
# The blocks must be numbered sequentially (0, 1, 2).
# 'params' are any extra parameters to pass to ImageMagick for example DPI
# 'source_extensions' is a comma-separated list of the files that will be processed, e.g. "eps,png,gif" (note no spaces).
# 'source_params' are parameters for the source file (e.g. -density 1200)
#
# Example - automatically create a PNG file alternative when an EPS file is uploaded.
# $image_alternatives[0]["name"]="PNG File";
# $image_alternatives[0]["source_extensions"]="eps";
# $image_alternatives[0]["source_params"]="";
# $image_alternatives[0]["filename"]="alternative_png";
# $image_alternatives[0]["target_extension"]="png";
# $image_alternatives[0]["params"]="-density 300"; # 300 dpi
# $image_alternatives[0]["icc"]=false;

# $image_alternatives[1]["name"]="CMYK JPEG";
# $image_alternatives[1]["source_extensions"]="jpg,tif";
# $image_alternatives[1]["source_params"]="";
# $image_alternatives[1]["filename"]="cmyk";
# $image_alternatives[1]["target_extension"]="jpg";
# $image_alternatives[1]["params"]="-quality 100 -flatten $icc_preview_options -profile ".dirname(__FILE__) . "/../iccprofiles/name_of_cmyk_profile.icc"; # Quality 100 JPEG with specific CMYK ICC Profile
# $image_alternatives[1]["icc"]=true; # use source ICC profile in command

# Example - automatically create a JPG2000 file alternative when an TIF file is uploaded
# $image_alternatives[2]['name']              = 'JPG2000 File';
# $image_alternatives[2]['source_extensions'] = 'tif';
# $image_alternatives[2]["source_params"]="";
# $image_alternatives[2]['filename']          = 'New JP2 Alternative';
# $image_alternatives[2]['target_extension']  = 'jp2';
# $image_alternatives[2]['params']            = '';
# $image_alternatives[2]['icc']               = false;


# For reports, the list of default reporting periods
$reporting_periods_default=array(7,30,100,365);


# For checkbox list searching, perform logical AND instead of OR when ticking multiple boxes.
$checkbox_and=false;

# Option to show resource ID in the thumbnail, next to the action icons.
$display_resource_id_in_thumbnail=false;

# Show "Save" and "Clear" buttons at the top of the resource edit form as well as at the bottom
$edit_show_save_clear_buttons_at_top=false;

# Allow empty collections to be shared?
$collection_allow_empty_share=false;

# Allow collections containing resources that are not active to be shared?
$collection_allow_not_approved_share=false;

#Allow the smartsearch to override $access rules when searching
$smartsearch_accessoverride=true;

# Image preview zoom using jQuery.zoom (hover over the preview image to zoom in on the resource view page)
$image_preview_zoom=false;

# How many characters from the fields are 'mirrored' on to the resource table. This is used for field displays in search results.
# This is the varchar length of the 'field' columns on the resource table.
# The value can be increased if titles (etc.) are being truncated in search results, but the field column lengths must be altered also.
$resource_field_column_limit=200;

# Resource access filter
# If set, filter searches to resources uploaded by users with the specified user IDs only. '-1' is an alias to the current user.
# For example, to filter search results to only include resources uploaded by the current user themselves and the admin user (by default user ID 1) set:
# $resource_created_by_filter=array(-1,1);
# This is used for the ResourceSpace demo installation.
#
# $resource_created_by_filter=array();

# Tell the browser to load the Ubuntu font from Google, used by the new styling.
# This can be set to 'false' to improve loading times if you are using custom styling
# that does not use this font.
$load_ubuntu_font=true;


#
# ------------------------ eCommerce Settings -----------------------------
#
# Pricing information for the e-commerce / basket request mode.
# Pricing is size based, so that the user can select the download size they require.
$pricing["scr"]=10;
$pricing["lpr"]=20;
$pricing["hpr"]=30; # (hpr is usually the original file download)
$currency_symbol="&pound;";
$payment_address="payment.address@goes.here"; // you must enable Instant Payment Notifications in your Paypal Account Settings.
$payment_currency="GBP";
# Should the "Add to basket" function appear on the download sizes, so the size of the file required is selected earlier and stored in the basket? This means the total price can appear in the basket.
$basket_stores_size=true; 
$paypal_url="https://www.paypal.com/cgi-bin/webscr";

# Ability to set a field which will store 'Portrait' or 'Landscape' depending on image dimensions
# $portrait_landscape_field=1;


# ------------------------------------------------------------------------------------------------------------------
# StaticSync (staticsync.php)
# The ability to synchronise ResourceSpace with a separate and stand-alone filestore.
# ------------------------------------------------------------------------------------------------------------------
$syncdir="/var/www/r2000/accounted"; # The sync folder
$nogo="[folder1]"; # A list of folders to ignore within the sign folder.
# Maximum number of files to process per execution of staticsync.php
$staticsync_max_files = 10000;
$staticsync_autotheme=true; # Automatically create themes based on the first and second levels of the sync folder structure.
# Allow unlimited theme levels to be created based on the folder structure. 
# Script will output a new $theme_category_levels number which must then be updated in config.php
$staticsync_folder_structure=false;
# Mapping extensions to resource types for sync'd files
# Format: staticsync_extension_mapping[resource_type]=array("extension 1","extension 2");
$staticsync_extension_mapping_default=1;
$staticsync_extension_mapping[3]=array("mov","3gp","avi","mpg","mp4","flv"); # Video
$staticsync_extension_mapping[4]=array("flv");
# Uncomment and set the next line to specify a category tree field to use to store the retieved path information for each file. The tree structure will be automatically modified as necessary to match the folder strucutre within the sync folder (performance penalty).
# $staticsync_mapped_category_tree=50;
# Uncomment and set the next line to specify a text field to store the retrieved path information for each file. This is a time saving alternative to the option above.
# $staticsync_filepath_to_field=100;
# Append multiple mapped values instead of overwritting? This will use the same appending methods used when editing fields. Not used on dropdown, date, categroy tree, datetime, or radio buttons
$staticsync_extension_mapping_append_values=true;
# Should the generated resource title include the sync folder path?
$staticsync_title_includes_path=true;
# Should the sync'd resource files be 'ingested' i.e. moved into ResourceSpace's own filestore structure?
# In this scenario, the sync'd folder merely acts as an upload mechanism. If path to metadata mapping is used then this allows metadata to be extracted based on the file's location.
$staticsync_ingest=false;
# Try to rotate images automatically when not ingesting resources? If set to TRUE you must also set $imagemagick_preserve_profiles=true;
$autorotate_no_ingest=false;
# Try to rotate images automatically when ingesting resources? If set to TRUE you must also set $imagemagick_preserve_profiles=true;
$autorotate_ingest=false;
# The default workflow state for imported files (-2 = pending submission, -1 = pending review, etc.)
$staticsync_defaultstate=0;
# Archive state to set for resources where files have been deleted/moved from syncdir
$staticsync_deleted_state=2;

# Uncomment and set to the ref of the user account that the staticsync resources will be 'created by' 
# $staticsync_userref=-1;

#
# StaticSync Path to metadata mapping
# ------------------------
# It is possible to take path information and map selected parts of the path to metadata fields.
# For example, if you added a mapping for '/projects/' and specified that the second level should be 'extracted' means that 'ABC' would be extracted as metadata into the specified field if you added a file to '/projects/ABC/'
# Hence meaningful metadata can be specified by placing the resource files at suitable positions within the static
# folder heirarchy.
# Use the line below as an example. Repeat this for every mapping you wish to set up
#	$staticsync_mapfolders[]=array
#		(
#		"match"=>"/projects/",
#		"field"=>10,
#		"level"=>2
#		);
#
# You can also now enter "access" in "field" to set the access level for the resource. The value must match the name of the access level
# in the default local language. Note that custom access levels are not supported. For example, the mapping below would set anything in 
# the projects/restricted folder to have a "Restricted" access level.
#	$staticsync_mapfolders[]=array
#		(
#		"match"=>"/projects/restricted",
#		"field"=>"access",
#		"level"=>2
#		);
#
# You can enter "archive" in "field" to set the archive state for the resource. You must include "archive" to the array and its value must match either a default level or a custom archive level. The mapped folder level does not need to match the name of the archive level. Note that this will override $staticsync_defaultstate. For example, the mapping below would set anything in the restricted folder to have an "Archived" archive level.
#   $staticsync_mapfolders[]=array
#		(
#		"match"=>"/projects/restricted",
#		"field"=>"archive",
#		"level"=>2,
#		"archive"=>2
#		);
#
# Suffix to use for alternative files folder
# If staticsync finds a folder in the same directory as a file with the same name as a file but with this suffix appended, then files in the folder will be treated as alternative files for the give file.
# For example a folder/file structure might look like:
# /staticsync_folder/myfile.jpg
# /staticsync_folder/myfile.jpg_alternatives/alternative1.jpg
# /staticsync_folder/myfile.jpg_alternatives/alternative2.jpg
# /staticsync_folder/myfile.jpg_alternatives/alternative3.jpg
# NOTE: Alternative file processing only works when $staticsync_ingest is set to 'true'.
$staticsync_alternatives_suffix="_alternatives";

# Option to have alternative files located in same directory as primary files but identified by a defined string. As with staticsync_alternatives_suffix this only works when $staticsync_ingest is set to 'true'.
#$staticsync_alternative_file_text="_alt_";

# if false, the system will always synthesize a title from the filename and path, even
# if an embedded title is found in the file. If true, the embedded title will be used.
$staticsync_prefer_embedded_title = true;

# Do we allow deletion of files located in $syncdir through the UI?
$staticsync_allow_syncdir_deletion=false;

# End of StaticSync settings


# Show tabs on the edit/upload page. Disables collapsible sections
$tabs_on_edit=false;

# Show additional clear and 'show results' buttons at top of advanced search page
$advanced_search_buttons_top=false;


# Enable multi-lingual free text fields
# By default, only the checkbox list/dropdown fields can be multilingual by using the special syntax when defining
# the options. However, setting the below to true means that free text fields can also be multi-lingual. Several text boxes appear when entering data so that translations can be entered.
$multilingual_text_fields=false;

# Allow to selectively disable upload methods.
# Controls are :
# - single_upload            : Enable / disable "Add Single Resource"
# - in_browser_upload        : Enable / disable "Add Resource Batch - In Browser"
# - fetch_from_ftp           : Enable / disable "Add Resource Batch - Fetch from FTP server"
# - fetch_from_local_folder  : Enable / disable "Add Resource Batch - Fetch from local upload folder"
$upload_methods = array(
		'single_upload' => true,
		'in_browser_upload' => true,
		'fetch_from_ftp' => false,
		'fetch_from_local_folder' => false,
	);

# Allow to change the location of the upload folder, so that it is not in the
# web visible path. Relative and abolute paths are allowed.
$local_ftp_upload_folder = 'upload/';

# Use a file tree display for local folder upload
$local_upload_file_tree=false;

# Hide links to other uploader
$hide_uploadertryother = false;

# Set path to Unoconv (a python-based bridge to OpenOffice) to allow document conversion to PDF.
## $unoconv_path="/usr/bin";
# Files with these extensions will be passed to unoconv (if enabled above) for conversion to PDF and auto thumb-preview generation.
# Default list taken from http://svn.rpmforge.net/svn/trunk/tools/unoconv/docs/formats.txt
$unoconv_extensions=array("ods","xls","doc","docx","odt","odp","html","rtf","txt","ppt","pptx","sxw","sdw","html","psw","rtf","sdw","pdb","bib","txt","ltx","sdd","sda","odg","sdc","potx","key");

# Uncomment to set a point in time where collections are considered 'active' and appear in the drop-down. 
# This is based on creation date for now. Older collections are effectively 'archived', but accessible through Manage My Collections.
# You can use any English-language strings supported by php's strtotime() function.
# $active_collections="-3 months";

# Set this to true to separate related resource results into separate sections (ie. PDF, JPG)
$sort_relations_by_filetype=false;

# Set this to true to separate related resource results into separate sections by resource type (ie. Document, Photo)
$sort_relations_by_restype=false;

# When using the "View these resources as a result set" link, show the original resource in search result?
$related_search_show_self=false;

# Select the field to display in searchcrumbs for a related search (defaults to filename)
# If this is set to a different field and the value is empty fallback to filename
$related_search_searchcrumb_field=51;

# Allow the addition of 'saved searches' to collections. 
$allow_save_search=true;

# Use the collection name in the downloaded zip filename when downloading collections as a zip file?
$use_collection_name_in_zip_name=false;

# Default DPI setting for the view page if no resolution is stored in the db.
$view_default_dpi=300;

# PDF/EPS base ripping quality in DPI. Note, higher values might greatly increase the resource usage
# on preview generation (see $pdf_dynamic_rip on how to avoid that)
$pdf_resolution=150;

# PDF/EPS dynamic ripping
# Use pdfinfo (pdfs) or identify (eps) to extract document size in order to calculate an efficient ripping resolution 
# Useful mainly if you have odd sized pdfs, as you might in the printing industry; 
# ex: you have very large PDFs, such as 50 to 200 in (will greatly decrease ripping time and avoid overload) 
# or very small, such as PDFs < 5 in (will improve quality of the scr image)
$pdf_dynamic_rip=false;

# Allow for the creation of new site text entries from Manage Content
# note: this is intended for developers who create custom pages or hooks and need to have more manageable content,
$site_text_custom_create=false;

# use hit count functionality to track downloads rather than resource views.
$resource_hit_count_on_downloads=false;
$show_hitcount=false;

# Use checkboxes for selecting resources 
$use_checkboxes_for_selection=false;

# allow player for mp3 files
# player docs at http://flash-mp3-player.net/players/maxi/
# Updated October 2015 so will use VideoJS if enabled ($videojs=true;)
$mp3_player=true;

# Show the performance metrics in the footer (for debug)
$config_show_performance_footer=false;

$use_phpmailer=false;

# Allow to disable thumbnail generation during batch resource upload from FTP or local folder.
# In addition to this option, a multi-thread thumbnail generation script is available in the batch
# folder (create_previews.php). You can use it as a cron job, or manually.
# Note: this also works for normal uploads (through web browser)
$enable_thumbnail_creation_on_upload = true;

# Create XML metadata dump files in the resource folder?
# This ensures that your metadata is kept in a readable format next to each resource file and may help
# to avoid data obsolescence / future migration. Also, potentially a useful additional backup.
$xml_metadump=true;

# Configures mapping between metadata and Dublin Core fields, which are used in the XML metadata dump instead if a match is found.
$xml_metadump_dc_map=array
	(
	"title" => "title",
	"caption" => "description",
	"date" => "date"
	);
	
# Use Plugins Manager
$use_plugins_manager = true;

# Allow Plugin Upload
$enable_plugin_upload = true;


# ------------- Geocoding / geolocation -------------
# Note that a Google Maps API key is no longer required.
#Disable geocoding features?
$disable_geocoding = false;

# OpenLayers: The default center and zoom for the map view when searching or selecting a new location. This is a world view.
# For example, to specify the USA use: #$geolocation_default_bounds="-10494743.596017,4508852.6025659,4";
# For example, to specify Utah, use $geolocation_default_bounds="-12328577.96607,4828961.5663655,6";
$geolocation_default_bounds="-3.058839178216e-9,2690583.3951564,2";

# The layers to make available. The first is the default.
$geo_layers="osm";
# To enable Google layers, use:
# $geo_layers="osm, gmap, gsat, gphy";

# Height of map in pixels on resource view page
$view_mapheight=200;

# Cache openstreetmap tiles on your server. This is slower when loading, but eliminates non-ssl content warnings if your site is SSL (requires curl)
$geo_tile_caching=false;

# A list of upper/lower long/lat bounds, defining areas that will be excluded from geographical search results.
# Areas are defined using values in the following sequence: southwest lat, southwest long, northeast lat, northeast long
$geo_search_restrict=array
	(	
	# array(50,-3,54,3) # Example omission zone
	# ,array(-10,-20,-8,-18) # Example omission zone 2
	# ,array(1,1,2,2) # Example omission zone 3
	);


# QuickLook previews (Mac Only)
# If configured, attempt to produce a preview for files using Mac OS-X's built in QuickLook preview system which support multiple files.
# This requires AT LEAST VERSION 0.2 of 'qlpreview', available from http://www.hamsoftengineering.com/codeSharing/qlpreview/qlpreview.html
#
# $qlpreview_path="/usr/bin";
#
# A list of extensions that QLPreview should NOT be used for.
$qlpreview_exclude_extensions=array("tif","tiff");



# Log developer debug information to the debug log (filestore/tmp/debug.txt)?
# As the default location is world-readable it is recommended for live systems to change the location to somewhere outside of the web directory by setting $debug_log_location below
$debug_log=false;

# Debug log location. Optional. Used to specify a full path to debug file. Ensure folder permissions allow write access by web service account
#$debug_log_location="d:/logs/resourcespace.log";
#$debug_log_location="/var/log/resourcespace/resourcespace.log";

# Enable Metadata Templates. This should be set to the ID of the resource type that you intend to use for metadata templates.
# Metadata templates can be selected on the resource edit screen to pre-fill fields.
# The intention is that you will create a new resource type named "Metadata Template" and enter its ID below.
# This resource type can be hidden from view if necessary, using the restrictive resource type permission.
#
# Metadata template resources act a little differently in that they have editable fields for all resource types. This is so they can be used with any 
# resource type, e.g. if you complete the photo fields then these will be copied when using this template for a photo resource.
# 
# $metadata_template_resource_type=5;
#
# The ability to set that a different field should be used for 'title' for metadata templates, so that the original title field can still be used for template data
# $metadata_template_title_field=10;

# enable a list of collections that a resource belongs to, on the view page
$view_resource_collections=false;

# enable titles on the search page that help describe the current context
$search_titles=false;
# whether all/additional keywords should be displayed in search titles (ex. "Recent 1000 / pdf")
$search_titles_searchcrumbs=false;
# whether field-specific keywords should include their shortnames in searchcrumbs (if $search_titles_searchcrumbs=true;) ex. "originalfilename:pdf"
$search_titles_shortnames=false;

# if using $collections_compact_style, you may want to remove the contact sheet link from the Manage Collections page
$manage_collections_contact_sheet_link=true;
# Other collections management link switches:
$manage_collections_remove_link=true;
$manage_collections_share_link=true;

# Tool at the bottom of the Collection Manager list which allows users to delete any empty collections that they own. 
$collections_delete_empty=false;

# Allow saving searches as 'smart collections' which self-update based on a saved search. 
$allow_smart_collections=false;
# Run Smart collections asynchronously (faster smart collection searches, with the tradeoff that they are updated AFTER the search.
# This may not be appropriate for usergroups that depend on live updates in workflows based on smart collections.
$smart_collections_async=false;

# Allow a Preview page for entire collections (for more side to side comparison ability, works with collection_reorder_caption)
$preview_all=false;
# Minimize collections frame when visiting preview_all.php
$preview_all_hide_collections=true;

# Don't display the link to toggle thumbnails in collection frame
$disable_collection_toggle=false;

# Display User Rating Stars in search views (a popularity column in list view)
$display_user_rating_stars=false;
# Allow each user only one rating per resource (can be edited). Note this will remove all accumlated ratings/weighting on newly rated items.
$user_rating_only_once = true;
# if user_rating_only_once, allow a log view of user's ratings (link is in the rating count on the View page):
$user_rating_stats = true;
# Allow user to remove their rating.
$user_rating_remove=true;

# Allow a user to CC oneself when sending resources or collections.
$cc_me=false;

# Allow listing of all recipients when sending resources or collection.
$list_recipients=false;

# How many keywords should be included in the search when a single keyword expands via a wildcard. Setting this too high may cause performance issues.
$wildcard_expand_limit=50;

# Should *all* manually entered keywords (e.g. basic search and 'all fields' search on advanced search) be treated as wildcards?
# E.g. "cat" will always match "catch", "catalogue", "catagory" with no need for an asterisk.
# WARNING - this option could cause search performance issues due to the hugely expanded searches that will be performed.
# It will also cause some other features to be disabled: related keywords and quoted string support
$wildcard_always_applied=false;

# Set to true if wildcard should also be prepended to the keyword
$wildcard_always_applied_leading = false;



# "U" permission allows management of users in the current group as well as children groups. TO test stricter adherence to the idea of "children only", set this to true. 
$U_perm_strict=false;

# enable remote apis (if using API, RSS2, or other plugins that allow remote authentication via an api key)
$enable_remote_apis=false;
$api_scramble_key="abcdef123";

# Allow users capable of deleting a full collection (of resources) to do so from the Collection Manage page.
$collection_purge=false;

# Set cookies at root (for now, this is implemented for the colourcss cookie to preserve selection between pages/ team/ and plugin pages)
# probably requires the user to clear cookies.
$global_cookies=false;

# Iframe-based direct download from the view page (to avoid going to download.php)
# note this is incompatible with $terms_download and the $download_usage features, and is overridden by $save_as
$direct_download=false;
$debug_direct_download=false; // set to true to see the download iframe for debugging purposes.
$direct_download_allow_ie7=false; // ie7 blocks initial downloads but after allowing once, it seems to work, so this option is available (no guarantees).
$direct_download_allow_ie8=false; // ie7 blocks initial downloads but after allowing once, it seems to work, so this option is available (no guarantees).
$direct_download_allow_opera=false; // opera can also allow popups, but this is recommended off as well since by default it won't work for most users.

# web-based config.php editing, using CodeMirror for highlighting. 
# must make config.php writable. 
# note that caution must be used not to break syntax, or else you must edit the file server side to fix the site.
$web_config_edit=false;

# enable option to autorotate new images based on embedded camera orientation data
# requires ImageMagick to work.
$camera_autorotation = false;
$camera_autorotation_checked = true;
$camera_autorotation_ext = array('jpg','jpeg','tif','tiff','png'); // only try to autorotate these formats
$camera_autorotation_gm = false;

# display swf in full on the view page (note that jpg previews aren't created yet)
$display_swf=false;
# if gnash_dump (gnash w/o gui) is compiled, previews are possible:
# Note: gnash-dump must be compiled on the server. http://www.xmission.com/~ink/gnash/gnash-dump/README.txt
# Ubuntu: ./configure --prefix=/usr/local/gnash-dump --enable-renderer=agg \
# --enable-gui=gtk,dump --disable-kparts --disable-nsapi --disable-menus
# several dependencies will also be necessary, according to ./configure
# $dump_gnash_path="/usr/local/gnash-dump/bin";

# show the title of the resource being viewed in the browser title bar
$show_resource_title_in_titlebar = false;
# When displaying title of the resource, set the following to true if you want to show Upload resources or Edit resource when on edit page:
$distinguish_uploads_from_edits=false;

# add direct link to original file for each image size
$direct_link_previews = false;

# SECURITY WARNING: The next two options will  effectively allow anyone
# to download any resource without logging in. Be careful!!!!
// allow direct resource downloads without authentication
$direct_download_noauth = false;
// make preview direct links go directly to filestore rather than through download.php
// (note that filestore must be served through the web server for this to work.)
$direct_link_previews_filestore = false;

$psd_transparency_checkerboard=false;
// checkerboard for gif and png with transparency
$transparency_background = "gfx/images/transparency.gif";

# Search for a minimum number of stars in Simple search/Advanaced Search (requires $$display_user_rating_stars)
$star_search=false;

# Omit archived resources from get_smart_themes (so if all resources are archived, the header won't show)
# Generally it's not possible to check for the existence of results based on permissions,
# but in the case of archived files, an extra join can help narrow the smart theme results to active resources.
$smart_themes_omit_archived=false;

# Remove archived resources from collections results unless user has e2 permission (admins).
$collections_omit_archived=false;

# Set to false to omit results for public collections on numeric searches.
$search_public_collections_ref=true;

# Set path to Calibre to allow ebook conversion to PDF.
# $calibre_path="/usr/bin";
# Files with these extensions will be passed to calibre (if enabled above) for conversion to PDF and auto thumb-preview generation.
# Set path to Calibre to allow ebook conversion to PDF.
# $calibre_path="/usr/bin";
# Files with these extensions will be passed to calibre (if enabled above) for conversion to PDF and auto thumb-preview generation.
$calibre_extensions=array("epub","mobi","lrf","pdb","chm","cbr","cbz");




# ICC Color Management Features (Experimental)
# Note that ImageMagick must be installed and configured with LCMS support
# for this to work

# Enable extraction and use of ICC profiles from original images
$icc_extraction = false;

# target color profile for preview generation
# the file must be located in the /iccprofiles folder
# this target preview will be used for the conversion
# but will not be embedded
$icc_preview_profile = 'sRGB_IEC61966-2-1_black_scaled.icc';

# embed the target preview profile?
$icc_preview_profile_embed=false;

# additional options for profile conversion during preview generation
$icc_preview_options = '-intent perceptual -black-point-compensation';

# add user and access information to collection results in the collections panel dropdown
# this extends the width of the dropdown and is intended to be used with $collections_compact_style
# but should also be compatible with the traditional collections tools menu.
$collection_dropdown_user_access_mode=false;

# show mp3 player in xlarge thumbs view (if $mp3_player=true)
$mp3_player_xlarge_view=true;
# show flv player in xlarge thumbs view 
$flv_player_xlarge_view=false;
# show embedded swfs in xlarge thumbs view 
$display_swf_xlarge_view=false;

# show mp3 player in thumbs view (if $mp3_player=true)
$mp3_player_thumbs_view=false;
# show flv player in thumbs view 
$video_player_thumbs_view=false;
# show flv player in small thumbs view 
$video_player_small_thumbs_view=false;

# use an ffmpeg alternative for search preview playback
$video_player_thumbs_view_alt=false;
#$video_player_thumbs_view_alt_name='searchprev';

# play videos/audio on hover instead of on click
$video_search_play_hover=false; // search.php
$video_view_play_hover=false; // view.php
$video_preview_play_hover=false; // preview.php and preview_all.php

# hotkeys for video playback
$keyboard_navigation_video_search=false;
$keyboard_navigation_video_view=false;
$keyboard_navigation_video_preview=false;
# play backwards (in development) - default 'j'
$video_playback_backwards=false;
$keyboard_navigation_video_search_backwards=74;
# play/pause - default 'k'
$keyboard_navigation_video_search_play_pause=75;
# play forwards - default 'l'
$keyboard_navigation_video_search_forwards=76;

# pager dropdown
$pager_dropdown=false;

# Use an external SMTP server for outgoing emails (e.g. Gmail).
# Requires $use_phpmailer.
$use_smtp=false;
# SMTP settings:
$smtp_secure=''; # '', 'tls' or 'ssl'. For Gmail, 'tls' or 'ssl' is required.
$smtp_host=''; # Hostname, e.g. 'smtp.gmail.com'.
$smtp_port=25; # Port number, e.g. 465 for Gmail using SSL.
$smtp_auth=true; # Send credentials to SMTP server (false to use anonymous access)
$smtp_username=''; # Username (full email address).
$smtp_password=''; # Password.

$sharing_userlists=false; // enable users to save/select predefined lists of users/groups when sharing collections and resources.

$attach_user_smart_groups=true; //enable user attach to include 'smart group option', different from the default "users in group" method (which will still be available)
$public_collections_header_only=false; // show public collections page in header, omit from Themes and Manage Collections

$ckeditor_toolbars="'Styles', 'Bold', 'Italic', 'Underline','FontSize', 'RemoveFormat', 'TextColor','BGColor'";
$ckeditor_content_toolbars="
	{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','RemoveFormat' ] },
	{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','-','Undo','Redo' ] },
	{ name: 'styles', items : [ 'Format' ] },
	{ name: 'paragraph', items : [ 'NumberedList','BulletedList' ] },
	{ name: 'links', items : [ 'Link','Unlink' ] },
	{ name: 'insert', items : [ 'Image','HorizontalRule'] },
	{ name: 'tools', items : [ 'Source', 'Maximize' ] }
";

# Automatically save the edit form after making changes?
$edit_autosave=true;

# use_refine_searchstring can improve search string parsing. disabled by Dan due to an issue I was unable to replicate. (tom)  
$use_refine_searchstring=false;

# By default, keyword relationships are two-way 
# (if "tiger" has a related keyword "cat", then a search for "cat" also includes "tiger" matches).
# $keyword_relationships_one_way=true means that if "tiger" has a related keyword "cat",
# then a search for "tiger" includes "tiger", but does not include "cat" matches.
$keyword_relationships_one_way=false;

$show_searchitemsdiskusage=true;

# If set, which field will cause warnings to appear when approving requests containing these resources?
#$warn_field_request_approval=115;

# Normally, image tweaks are only applied to scr size and lower. 
# If using Magictouch, you may want tweaks like rotation to be applied to the larger images as well.
# This could require recreating previews to sync up the various image rotations.
$tweak_all_images=false;
$tweak_allow_gamma=true;

# experimental email notification of php errors to $email_notify. 
$email_errors=false;
$email_errors_address="";

# Use php-mysqli extension for interfacing with the mysql database
# Only enable if the extension is present.
$use_mysqli=function_exists("mysqli_connect");

# Experimental performance enhancement - two pass mode for search results.
# The first query returns only the necessary number of results for the current search results display
# The second query is the same but returns only a count of the full result set, which is used to pad the result array to the correct size (so counts display correctly).
# This means that large volumes of resource data are not passed around unnecessarily, which can significantly improve performance on systems with large data sets.
$search_sql_double_pass_mode=true;

# Experimental performance enhancement - only search for fields with matching keywords that are supposed to be indexed.
$search_sql_force_field_index_check = false;

# Use the new tab ordering system. This will sort the tabs by the order by value set in System Setup
$use_order_by_tab_view=false;

# Allows for themes with a taller header than standard to still be fully visible in System Setup. 
$admin_header_height=120;

# Remove the line that separates collections panel menu from resources
$remove_collections_vertical_line=false;

# Make dropdown selectors for Display and Results Display menus
$display_selector_dropdowns=false;

# Option that specifically allows the per-page dropdown without needing $display_selector_dropdown=true. This is useful if you'd like to use the display selector icons with per-page dropdowns.
$perpage_dropdown = true;

# Display link to request log on view page
$display_request_log_link=false;

# Show friendly error to user instead of 403 if IP not in permitted range.
$iprestrict_friendlyerror=false;

# Make search filter strict (prevents direct access to view/preview page)
# Set to 2 in order to emulate single resource behaviour in search (EXPERIMENTAL). Prevents search results that are not accessible from showing up. Slight performance penalty on larger search results.
$search_filter_strict=true;

# Plupload settings
# Specify the supported runtimes and priority
$plupload_runtimes = 'html5,gears,silverlight,browserplus,flash,html4';

# Start uploads as soon as files are added to the queue?
$plupload_autostart=false;

# Clear the queue after uploads have completed
$plupload_clearqueue=true;

# Keep failed uploads in the queue after uploads have completed
$plupload_show_failed=true;

# Maximum number of attempts to upload a file chunk before erroring
$plupload_max_retries=5;

# Send confirmation emails to user when request sent or assigned
$request_senduserupdates=true;

# Allow users to create new collections. Set to false to prevent creation of new collections.
$collection_allow_creation=true;

# Allow Dates to be set within Date Ranges: Ensure to allow By Date to be used in Advanced Search if required.
$daterange_search=false;

# Keyboard navigation allows using left and right arrows to browse through resources in view/search/preview modes
$keyboard_navigation=true;

# Keyboard control codes
# Previous/next resource, default: left/right arrows
$keyboard_navigation_prev=37;
$keyboard_navigation_next=39;
$keyboard_navigation_pages_use_alt=false;
# add resource to collection, default 'a'
$keyboard_navigation_add_resource=65;
# remove resource from collection, default 'r'
$keyboard_navigation_remove_resource=82;
# previous page in document preview, default ','
$keyboard_navigation_prev_page=188;
# next page in document preview, default '.'
$keyboard_navigation_next_page=190;
# view all results, default '/'
$keyboard_navigation_all_results=191;
# toggle thumbnails in collections frame, default 't'
$keyboard_navigation_toggle_thumbnails=84;
# view all resources from current collection, default 'v'
$keyboard_navigation_view_all=86;
# zoom to/from preview, default 'z'
$keyboard_navigation_zoom=90;
# close modal, defaut escape
$keyboard_navigation_close=27;
# with $keyboard_scroll_jump on arrow keys jump from picture to picture in preview_all mode (horizontal only)
$keyboard_scroll_jump=false;

# How long until the Loading popup appears during an ajax request (milliseconds)
$ajax_loading_timer=1500;

#Option for downloaded filename to be just <resource id>.extension, without indicating size or whether an alternative file. Will override $original_filenames_when_downloading which is set as default
$download_filename_id_only = false;

# Append the size to the filename when downloading
# Required: $download_filename_id_only = true;
$download_id_only_with_size = false;

# Index the 'contributed by' field?
$index_contributed_by=false;

# Index the resource type, so searching for the resource type string will work (e.g. if you have a resource of type "photo" then "cat photo" will match even if the resource metadata itself doesn't contain the word 'photo')
$index_resource_type=true;

# Use CKEditor for site content?
$site_text_use_ckeditor=false;

# Preview All default orientation ("v" for vertical or "h" for horizontal)
$preview_all_default_orientation="h";

# Allow sorting by resource_type on thumbnail views
$order_by_resource_type=false;

# Upload Options at top of Edit page (Collection, import metadata checkbox) at top of edit page, rather than the bottom (default).
$edit_upload_options_at_top=false;

# Option to select metadata field that will be used for downloaded filename (do not include file extension)
#$download_filename_field=8;

# option to always send emails from the logged in user
$always_email_from_user=false;

# option to always cc admin on emails from the logged in user
$always_email_copy_admin=false;

# Option to limit recent search to resources uploaded in the last X days
$recent_search_period_select=false;
$recent_search_period_array=array(1,7,14,60);

#Option for recent link to use recent X days instead of recent X resources
$recent_search_by_days=false;
$recent_search_by_days_default=60;

$simple_search_reset_after_search=false;

#download_chunk_size - for resource downloads. This can be amended to suit local setup. For instance try changing this to 4096 if experiencing slow downloads
$download_chunk_size=(2 << 20); 

#what to search for in advanced search by default - "Global", "Collections" or resource type id (e.g. 1 for photo in default installation, can be comma separated to enable multiple selections
$default_advanced_search_mode="Global";

#Option to turn on metadata download in view.php.
$metadata_download=false;

# Custom logo to use when downloading metadata in PDF format
$metadata_download_header_title = 'ResourceSpace';
#$metadata_download_pdf_logo     = '/path/to/logo/location/logo.png';
$metadata_download_footer_text  = '';

# settings for commenting on resources - currently not enabled by default

# $comments_collection_enable=false; 			# reserved for future use
$comments_resource_enable=false;				# allow users to make comments on resources
$comments_flat_view=false;						# by default, show in a threaded (indented view)
$comments_responses_max_level=10 ;				# maximum number of nested comments / threads
$comments_max_characters=200;					# maximum number of characters for a comment
$comments_email_notification_address="";		# email address to use for flagged comment notifications
$comments_show_anonymous_email_address=false;	# by default keep anonymous commenter's email address private
$comments_policy_external_url="";				# if specified, will popup a new window fulfilled by URL (when clicking on "comment policy" link)
$comments_view_panel_show_marker=true;			# show an astrisk by the comment view panel title if comments exist

# show the login panel for anonymous users
$show_anonymous_login_panel=true;

# force single branch selection in category tree selection 
$cat_tree_singlebranch=false;

$regex_email = "[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}";	# currently exclusively used for comments functionality - checking of valid (anonymous) email addresses entered in JS and in back-end PHP

$do_not_add_to_new_collection_default=false;  # will set "do not add to a collection" as the default option for upload option
$no_metadata_read_default=false; // If set to true and $metadata_read is false then metadata will be imported by default
$removenever=false; # Remove 'never' option for resource request access expiration and sets default expiry date to 7 days
$hide_resource_share_link=false; // Configurable option to hide the "Share" link on the resource view page.

# Option to email the contributor when their resources have been approved (moved from pending submission to active)
$user_resources_approved_email=false; 

# Set to true to move the Search button before the Clear button
$swap_clear_and_search_buttons=false;

# Option to have default date left blank, instead of current date.
$blank_date_upload_template=false;

# Option to show dynamic dropdows as normal dropdowns on the simple search. If set to false, a standard text box is shown instead.
$simple_search_show_dynamic_as_dropdown=true;

# Option to allow users to see all resources that they uploaded, irrespective of 'z' permissions
$uploader_view_override=true;

# Allow user to select archive state in advanced search
$advanced_search_archive_select=true;

# Additional archive states - option to add workflow states to the default list of -2 (pending submission), -1 (Pending review), 0 (Active), 1 (Awaiting archive), 2 (archived) and 3 (deleted)
# Can be used in conjunction with 'z' permissions to restrict access to workflow states.
# Note that for any state you need to create a corresponding language entry e.g.if you had the following additonal states set
# additional_archive_states=array(4,5);
# you would need the following language entries to be set to an appropriate description e.g.
# $lang['status4']="Pending media team review";
# $lang['status5']="Embargoed";

$additional_archive_states=array();

# Option to use CTRL + S on edit page to save data
$ctrls_to_save=false;

# Option to separate some resource types in searchbar selection boxes
$separate_resource_types_in_searchbar=Array();

$team_centre_bug_report=true;

# Option to show resource archive status in search results list view
$list_view_status_column=false;

# Removes textbox on the download usage page.
$remove_usage_textbox=false;

# Moves textbox below dropdown on the download usage page.
$usage_textbox_below=false;

# Option to replace text descriptions of search views (x-large, large, small, list) with icons
$iconthumbs=true;

# Option to make filling in usage text box a non-requirement.
$usage_comment_blank=false;

# Option to add a link to the resource view page that allows a user to email the $email_notify address about the resource
$resource_contact_link=false;

# Hide geolocation panel by default (a link to show it will be displayed instead)
$hide_geolocation_panel=false;

# Option to move the welcome text into the Home Picture Panel. Stops text from falling behind other panels.
$welcome_text_picturepanel=false;
# Hide Welcome Text
$no_welcometext = false;

#Show a specified metadata field below the resource preview image on the view page. Useful for phoo captions. 
#$display_field_below_preview=18;

# Optional setting to override the default $email_notify address for resource request email notifications, applies to specified resource types
# e.g. for photo (resource type 1 by default)
# $resource_type_request_emails[1]="imageadministrator@my.site"; 
# e.g. for documents (resource type 2 by default)
# $resource_type_request_emails[2]="documentadministrator@my.site"; 

# $rating_field. A legacy option that allows for selection of a metadata field that contains administrator ratings (not user ratings) that will be displayed in search list view. Field must be plain text and have numeric only numeric values.
# $rating_field=121;

# Set this to true to prevent possible issues with IE and download.php. Found an issue with a stray pragma: no-cache header that seemed to be added by SAML SSO solution.
$download_no_session_cache_limiter=false;


# Specifies that searching will search all workflow states
# NOTE - does not work with $advanced_search_archive_select=true (advanced search status searching) as the below option removes the workflow selection altogether.
# IMPORTANT - this feature gets disabled when requests ask for a specific archive state (e.g. View deleted resources or View resources in pending review)
$search_all_workflow_states=false;

# Require email address to be entered when users are submitting collecion feedback
$feedback_email_required=true;

# Option to show only existing shares that have been shared by the user when sharing resources (not collections)
$resource_share_filter_collections=false;

# Do not create any new snapshots when recreating FFMPEG previews. (This is to aid in migration to mp4 when custom previews have been uploaded)
$ffmpeg_no_new_snapshots=false;

# Set the following to false to disable permission checking before showing edit_all link in collection bar and on Manage my collections page, useful as this can be a performance hit if there are many resources in collections
$edit_all_checkperms=false;

# Force fields with display templates to obey "order by" numbering.
$force_display_template_order_by=false;

# Option to turn off email sharing.
$email_sharing=true;

# Hide "Generate URL" from the collection_share.php page?
$hide_collection_share_generate_url=false;

# Hide "Generate URL" from the resource_share.php page?
$hide_resource_share_generate_url=false;

#Resource Share Expiry Controls
$resource_share_expire_days=150; #Maximum number of days allowed for the share 
$resource_share_expire_never=true; #Allow the 'Never' option.

#Collections Share Expiry Controls
$collection_share_expire_days=150; #Maximum number of days allowed for the share 
$collection_share_expire_never=true; #Allow the 'Never' option.

# Pop-out Collection Bar Upon Collection Interaction such as "Select Collection"
$collection_bar_popout=false;

# Add option to include related resources when sharing single resource (creates a new collection)
$share_resource_include_related=false;

# Allow users to skip upload and create resources with no attached file
$upload_no_file=false;

# Normalize keywords when indexing and searching? Having this set to true means that various character encodings of e.g. diacritics will be standardised when indexing and searching. Requires internationalization functions (PHP versions >5.3). For example, there are several different ways of encoding "é" (e acute) and this will ensure that a standard form of "é" will always be used.
$normalize_keywords=true;

# Having keywords_remove_diacritics set to true means that diacritics will be removed for indexing e.g. 'zwälf' is indexed as 'zwalf', 'café' is indexed as 'cafe'.
# The actual data is not changed, this only affects searching and indexing
$keywords_remove_diacritics=false;

# Index the unnormalized keyword in addition to the normalized version, also applies to keywords with diacritics removed. Quoted search can then be used to find matches for original unnormalized keyword.
$unnormalized_index=false;

# Show tabbed panels in view. Metadata, Location, Comments are grouped in tabs, Related Collection, Related Galleries and Related Resources, Search for Similar are grouped too
$view_panels=false;

# Allow user to select to import or append embedded metadata on a field by field basis
$embedded_data_user_select=false;

# Always display the option to override the import or appending/prepending of embedded metadata for the fields specified in the array
# $embedded_data_user_select_fields=array(1,8);

# Option to show related resources of specified resource types in a table alongside resource data. Thes resource types will not then be shown in the usual related resources area.
# $related_type_show_with_data=array(3,4);
# Additonal option to show a link for those with edit access allowing upload of new related resources. The resource type will then be automatically selected for the upload
$related_type_upload_link=true;

# Array of preview sizes to always create. This is especially helpful if your preview size is small than the "thm" size.
$always_make_previews=array();

# Option to display an upload log in the browser on the upload page (note that this is not stored or saved)
$show_upload_log=true;

#Display User Ref on the User Edit Page in the header? Example Output: Edit User 12
$display_useredit_ref=false;

# Basic option to visually hide resource types when searching and uploading
# Note: these resource types will still be available (subject to filtering)
$hide_resource_types = array();

# Ability (when uploading new resources) to include a user selectable option to use the embedded filename to generate the title
# Note: you can set a default option by using one of the following values: do_not_use, replace, prefix, suffix
$merge_filename_with_title = FALSE;
$merge_filename_with_title_default = 'do_not_use';

# Add collection link to email when user submits a collection of resources for review (upload stage only)
# Note: this will send a collection containing only the newly uploaded resources
$send_collection_to_admin = FALSE;

# Set to true if you want to share internally a collection which is not private
$ignore_collection_access = FALSE;

# Show/ hide "Remove resources" link from collection bar:
$remove_resources_link_on_collection_bar = TRUE;

# Show group filter and user search at top of team_user.php
$team_user_filter_top=false;

# Stemming support - at this stage, experimental. Indexes stems of words only, so plural / singular (etc) forms of keywords are indexed as if they are equivalent. Requires a full reindex.
$stemming=false;

# Show the > symbol in the resource tools
$resourcetoolsGT=true;

# Initialize array for classes to be added to <body> element
$body_classes = array();

# Manage requests automatically using $manage_request_admin[resource type ID] = user ID;
# IMPORTANT: the admin user needs to have permissions R and Rb set otherwise this will not work.
// $manage_request_admin[1] = 1; // Photo
// $manage_request_admin[2] = 1; // Document
// $manage_request_admin[3] = 1; // Video
// $manage_request_admin[4] = 1; // Audio

# Notify on resource change. If the primary resource file is replaced or an alternative file is added, users who have 
# downloaded the resource in the last X days will be sent an email notifying them that there has been a change with a link to the resource view page
# Set to 0 to disable this functionality;
$notify_on_resource_change_days=0;

# Allow passwords to be emailed directly to users. Settign this to true is a security risk so should be used with caution.
$allow_password_email=false;

# Do not show any notification text if a password reset attempt fails to find a valid user. Setting this to false means potential hackers can discover valid email addresses
$hide_failed_reset_text=true;

# Show and allow to remove custom access for users when editing a resource
$delete_resource_custom_access = false;

# Enable this option to display a system down message to all users
$system_down_redirect = false;

# Option for the system to empty the configured temp folder of old files when it is creating new temporary files there.
# Set to 0 (off) by default.
# Please use with care e.g. make sure your IIS/Apache service account doesn't have write access to the whole server
$purge_temp_folder_age=0;

# Set how many extra days a reset password link should be valid for. Default is 1 day 
# Note: this is based on server time. The link will always be valid for the remainder of the current server day. 
# If it is set to 0 the link will be valid only on the same day - i.e. until midnight from the time the link is generated
# If it is set to 1 the link will also be valid all the next day
$password_reset_link_expiry =1;

# Show the resource view in a modal when accessed from search results.
$resource_view_modal=true;

# Show geographical search results in a modal
$geo_search_modal_results = true;

# Show an edit icon/link in the search results.
$search_results_edit_icon=true;

# Option to show a popup to users that upload resources to pending submission status. Prompts user to either submit for review or continue editing.
$pending_submission_prompt_review=true;

# Experimental. Always use 'download.php' to send thumbs and previews. Improved security as 'filestore' web access can be disabled in theory.
$thumbs_previews_via_download=false;

# Frequency at which the page header will poll for new messages for the user.  Set to 0 (zero) to disable.
$message_polling_interval_seconds = 10;

# How many times must a keyword be used before it is considered eligable for suggesting, when a matching keyword is not found?
# Set to zero to suggest any known keyword regardless of usage.
# Set to a higher value to ensure only popular keywords are suggested.
$soundex_suggest_limit=10;

# Option for custom access to override search filters.
# For this resource, if custom access has been granted for the user or group, nullify the filter for this particular 
$custom_access_overrides_search_filter=false;

# When requesting a resource or resources, is the "reason for request" field mandatory?
$resource_request_reason_required=true;

# Use the 'chosen' library for rendering dropdowns (improved display and search capability for large dropdowns)
$chosen_dropdowns=false;

# Allow ResourceSpace to upload multiple times the same file in a row
# Set to true only if you want RS to create duplicates when client is losing
# connection with the server and tries again to send the last chunk
$plupload_allow_duplicates_in_a_row = false;

# Show header and footer on resource preview page
$preview_header_footer=false;

# Create all preview sizes at the full target size if image is smaller (except for HPR as this would result in massive images)
$previews_allow_enlarge=false;

# Option to use a random static image from the available slideshow images. Requires slideshow_big=true;
$static_slideshow_image=false;

# User preference - user_pref_resource_notifications. Option to receive notifications about resource management e.g. archive state changes 
$user_pref_resource_notifications=true;
# User preference - user_pref_resource_access_notifications. Option to receive notifications about resource access e.g. resource requests
$user_pref_resource_access_notifications=true;

# Administrator default for receiving notifications about resource access e.g. resource requests. Can't use user_pref_resource_access_notifications since this will pick up setting of requesting user
$admin_resource_access_notifications=true;

# User preference - user_pref_user_management_notifications (user admins only). Option to receive notifications about user management changes e.g. account requests
$user_pref_user_management_notifications=true;
# User preference - user_pref_system_management_notifications (System admins only). Option to receive notifications about system events e.g. low disk space
$user_pref_system_management_notifications=true;

# User preference - email_user_notifications. Option to receive emails instead of new style system notifications where appropriate. 
$email_user_notifications=false;

# User preference - email_and_user_notifications. Option to receive emails instead of new style system notifications where appropriate. 
$email_and_user_notifications=false;

# Execution lockout mode - prevents entry of PHP even to admin users (e.g. config overrides and upload of new plugins) - useful on shared / multi-tennant systems.
$execution_lockout=false;

# Load help page in a modal?
$help_modal=true;

// maximum number of words shown before more/less link is shown (used in resource log)
$max_words_before_more=30;

# User preference - if set to false, hide the notification popups for new messages
$user_pref_show_notifications=true;

# User preference - daily digest. Sets the default setting for a daily email digest of unread system notifications.
$user_pref_daily_digest=false; 
# Option to set the messages as read once the email is sent
$user_pref_daily_digest_mark_read=false;

# login_background. If enabled this uses first slideshow image as a background for the login screen. This image will not then be used in the slideshow. If not using the manage slideshow tool this will look for a file named 1.jpg in the $homeanim_folder.
$login_background=false;

/*
Resource types that cannot upload files. They are only being used to store information. Use resource type ID as values for this array.
By default the preview will default to "No preview" icon. In order to get a resource type specific one, make sure you add it to gfx/no_preview/resource_type/
Note: its intended use is with $pdf_resource_type_templates
*/
$data_only_resource_types = array();

/*
Resource type templates are stored in /filestore/system/pdf_templates
A resource type can have more than one template. When generating PDFs, if there is no request for a specific template,
the first one will be used so make sure the the most generic template is the first one.

IMPORTANT: you cannot use <html>, <head>, <body> tags in these templates as they are supposed
           to work with HTML2PDF library. For more information, please visit: http://html2pdf.fr/en/default
           You also cannot have an empty array of templates for a resource type.

Setup example:
$pdf_resource_type_templates = array(
    2 => array('case_studies', 'admins_case_studies')
);
*/
$pdf_resource_type_templates = array();

#Option to display year in a four digit format
$date_yyyy = false;

# Option to display external shares in standard internal collection view when accessed by a logged in user
$external_share_view_as_internal=false;

/*When sharing externally as a specific user group (permission x), limit the user groups shown only if
they are allowed*/
$allowed_external_share_groups = array();

// CSV Download - add original URL column
$csv_export_add_original_size_url_column = false;

// Show required field legend on upload
$show_required_field_label = true;

// Show extra home / about / contact us links in the page footer?
$bottom_links_bar=false;

# Prevent users without accounts from requesting resources when accessing external shares. If true, external users requesting access will be redirected to the login screen so only recommended if account requests are allowed.
$prevent_external_requests=false;

/*
Display watermark without repeating it
Possible values for position: NorthWest, North, NorthEast, West, Center, East, SouthWest, South, SouthEast

IMPORTANT: the watermark used will need to have an aspect ratio of 1 for this to work as expected. A different aspect ratio
           will return unexpected results

$watermark_single_image = array(
    'scale'    => 40,
    'position' => 'Center',
);
*/

# $offline_job_queue. Enable the job_queue functionality that runs resource heavy tasks to be run offline and send notifications once complete. Initially used by video_tracks plugin 
# If set to true a frequent cron job or scheduled task should be added to run pages/tools/offline_jobs.php 
$offline_job_queue=false;
# Delete completed jobs from the queue?
$offline_job_delete_completed=false;

# Default lifetime in days of a temporary download file created by the job queue. After this time it will be deleted by another job
$download_file_lifetime=14;

# $replace_resource_preserve_option - Option to keep original resource files as alternatives when replacing resource
$replace_resource_preserve_option=false;
# $replace_resource_preserve_default - if $replace_resource_preserve_option is enabled, should the option be checked by default?
$replace_resource_preserve_default=false;

# When searching collections, return results based on the metadata of the resources inside also
$collection_search_includes_resource_metadata=false;

# Specify field references for fields that you do not wish the blank default entry to appear for, so the first keyword node is selected by default.
# e.g. array(3,12);
$default_to_first_node_for_fields=array();

# A list of groups for which the knowledge base will launch on login, until dismissed.
$launch_kb_on_login_for_groups=array();

