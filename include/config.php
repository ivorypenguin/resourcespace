<?php
###############################
## ResourceSpace
## Local Configuration Script
###############################

# All custom settings should be entered in this file.
# Options may be copied from config.default.php and configured here.

# MySQL database settings
$mysql_server = 'localhost';
$mysql_username = 'resourcespace';
$mysql_password = 'resourcespace';
$mysql_db = 'resourcespace';

$mysql_bin_path = '/usr/bin';

# Base URL of the installation
$baseurl = 'http://localhost/resourcespace';

# Email settings
$email_from = 'aureagle@gmail.com';
$email_notify = 'aureagle@gmail.com';

$spider_password = 'aPaRudALUrU3';
$scramble_key = 'u4AmuXyLA9ym';

$api_scramble_key = 'EJuqUSu9A3AN';

# Paths
$imagemagick_path = '/usr/bin';
$ghostscript_path = '/usr/bin';
$ffmpeg_path = '/usr/bin';
$exiftool_path = '/usr/bin';
$antiword_path = '/usr/bin';
$pdftotext_path = '/usr/bin';

$enable_remote_apis = true;

#Design Changes
$slimheader=true;



/*

New Installation Defaults
-------------------------

The following configuration options are set for new installations only.
This provides a mechanism for enabling new features for new installations without affecting existing installations (as would occur with changes to config.default.php)

*/
                                
$thumbs_display_fields = array(8,3);
$list_display_fields = array(8,3,12);
$sort_fields = array(12);

// Set imagemagick default for new installs to expect the newer version with the sRGB bug fixed.
$imagemagick_colorspace = "sRGB";

$slideshow_big=true;
$home_slideshow_width=1400;
$home_slideshow_height=900;

$homeanim_folder = 'filestore/system/slideshow';