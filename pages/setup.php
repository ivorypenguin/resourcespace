<?php
/**
 * Initial setup page.
 * 
 * @package ResourceSpace
 * @subpackage Pages_Misc
 */
include_once '../include/file_functions.php';
include_once '../include/general.php';
include_once '../include/collections_functions.php';
if (!function_exists('filter_var')){  //If running on PHP without filter_var, define a do-fer function, otherwise use php's filter_var (PHP > 5.2.0)
echo "!!!";
    define(FILTER_SANITIZE_STRING, 1);
    define(FILTER_SANITIZE_EMAIL, 2);
    define(FILTER_VALIDATE_EMAIL, 3);
    define(FILTER_VALIDATE_URL, 4);
    /****
     * Ad Hoc Function to replace filter_var on version of PHP prior to 5.2.0
     * 
     * @param string $data Data to sanitize.
     * @param int $filter Constant indicating filter type.
     * @return string Filtered data.
     */
    function filter_var($data, $filter){
        switch ($filter){
        case FILTER_VALIDATE_EMAIL:
            if(preg_match('/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/',$data, $output)>0)
                return true;
            else return false;
            break;
        case FILTER_SANITIZE_STRING:
            return addslashes($data); //Just do an escape quotes.  We're not doing anything too dangerous here after all
            break;
        case FILTER_VALIDATE_URL:       
            //Rely on checking the license.txt file to validate URL.  
            //This leaves a minor risk of the script being used to do bad things to other hosts if it is left available (i.e. RS is installed, but never configured)
            return true;
            break;
        }
    }
}
/**
 * Ad Hoc Function to support setup page
 * 
 * Copied from includes/db.php
 * 
 * @param string $value String representaion of byte size (including K, M, or G
 * @return int Number of KB
 */
function ResolveKB($value) { //Copied from includes/db.php
    $value=trim(strtoupper($value));
    if (substr($value,-1,1)=="K")
        {
        return substr($value,0,strlen($value)-1);
        }
    if (substr($value,-1,1)=="M")
        {
        return substr($value,0,strlen($value)-1) * 1024;
        }
    if (substr($value,-1,1)=="G")
        {
        return substr($value,0,strlen($value)-1) * 1024 * 1024;
        }
    return $value;
}
/**
 * Generates a random string of requested length.
 * 
 * Used to generate initial spider and scramble keys.
 * 
 * @param int $length Optional, default=12
 * @return string Random character string.
 */
function generatePassword($length=12) { 
    $vowels = 'aeuyAEUY';
    $consonants = 'bdghjmnpqrstvzBDGHJLMNPQRSTVWXZ23456789';
    $password = '';
    $alt = time() % 2;
    for ($i = 0; $i < $length; $i++) {
        if ($alt == 1) {
            $password .= $consonants[(rand() % strlen($consonants))];
            $alt = 0;
        } else {
            $password .= $vowels[(rand() % strlen($vowels))];
            $alt = 1;
        }
    }
    return $password;
}
/**
 * Santitizes input from a given request key.
 * 
 * Uses filter_var if available, or uses an ad hoc version if filter_var is
 * not available. (PHP 4)
 * 
 * @param string $key _REQUEST key to sanitize and return
 * @return string Santized _REQUEST key.
 **/
function get_post($key){ 
    return filter_var(@$_REQUEST[$key], FILTER_SANITIZE_STRING);
}
/**
 * Returns true if a given $_REQUEST key is set.
 * 
 * @param string $key _REQUEST key to test.
 * @return bool
 */

function get_post_bool($key){ 
    if (isset($_REQUEST[$key]))
        return true;
    else
        return false;
}
/**
 * Trims whitespace and trailing slash.
 * 
 * @param string $data
 * @return string
 */

function sslash($data){ 
    $stripped = rtrim($data);
    $stripped = rtrim($data, '/');
    return $stripped;
}
/**
 * Opens an HTTP request to a host to determine if the url is reachable.
 * 
 * Returns true if url is reachable.
 * 
 * @param string $url
 * @return bool
 */

function url_exists($url) 
{
    $parsed_url = parse_url($url);
    $host = @$parsed_url['host'];
    $path = @$parsed_url['path'];
    $port = @$parsed_url['port'];
    if (empty($path)) $path = "/";
    if (!isset($port)) {$port=0;}
    if ($port==0) {$port=80;}
    // Build HTTP 1.1 request header.
    $headers =  "GET $path HTTP/1.1\r\n" .
                "Host: $host\r\n" .
                "User-Agent: RS-Installation/1.0\r\n\r\n";
    $fp = fsockopen($host, $port, $errno, $errmsg, 5); //5 second timeout.  Assume that if we can't open the socket connection quickly the host or port are probably wrong.
    if (!$fp) {
        return false;
    }
    fwrite($fp, $headers);
    while(!feof($fp)) {
        $resp = fgets($fp, 4096);
        if(strstr($resp, 'HTTP/1.')){
            fclose($fp);
            $tmp = explode(' ',$resp);
            $response_code = $tmp[1];
            if ($response_code == 200)
                return true;
            else
                return false;
        }
    }
    fclose($fp);
    return false;
}   

/**
 * Sets the language to be used.
 *
 * @param string $defaultlanguage
 * @return array 
 */

function set_language($defaultlanguage)
{
	global $languages;
	global $storagedir, $applicationname, $homeanim_folder; # Used in the language files.
	$defaultlanguage = safe_file_name($defaultlanguage);
	if (file_exists("../languages/en.php")) {include "../languages/en.php";}
	if ($defaultlanguage==''){ 
		//See if we can auto-detect the most likely language.  The user can override this.
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			$httplanguage = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
			if(array_key_exists($httplanguage[0],$languages)){
				$defaultlanguage = $httplanguage[0];
			}
		}
	}
	if ($defaultlanguage!='en'){
		if (file_exists("../languages/".$defaultlanguage.".php")){

			include "../languages/".$defaultlanguage.".php";
		}
	}
	return $lang;
}


//Development Mode:  Set to true to change the config.php check to devel.config.php and output to devel.config.php instead.  Also displays the config file output in a div at the bottom of the page.
$develmode = false;
if ($develmode)
	$outputfile = '../include/devel.config.php';
else
	$outputfile = '../include/config.php';

// Define some vars to prevent warnings (quick fix)
$configstoragelocations=false;	
$storageurl="";
$storagedir=""; # This variable is used in the language files.

include '../include/config.default.php';
$defaultlanguage = get_post('defaultlanguage');
$lang = set_language($defaultlanguage);


/* Process AJAX request to check password */
if(get_post_bool('ajax'))
    {
    $response['success'] = false;
    $response['error']   = '';

    $admin_password             = get_post('admin_password');
    $password_validation_result = check_password($admin_password);

    if('' !== $admin_password && true === $password_validation_result)
        {
        $response['success'] = true;
        }
    else if('' !== $admin_password && is_string($password_validation_result) && '' !== $password_validation_result)
        {
        $response['error'] = $password_validation_result;
        }

    echo json_encode($response);
    exit();
    }
?>
<html>
<head>
<title><?php echo $lang["setup-rs_initial_configuration"];?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="../css/global.css?csr=5" rel="stylesheet" type="text/css" /> 
<link href="../css/colour.css?csr=5" rel="stylesheet" type="text/css" /> 
<link href="../css/Col-multi.css" rel="stylesheet" type="text/css" id="colourcss" /> 
<script type="text/javascript" src="../lib/js/jquery-1.7.2.min.js"></script> 

<script type="text/javascript"> 
 
$(document).ready(function(){
$('p.iteminfo').hide();
$('.starthidden').hide();
$('#tabs div.tabs').hide();
$('#tabs div:first').show();
$('#tabs ul li:first').addClass('active');
$('#tabs ul li a').click(function(){
	$('#tabs ul li').removeClass('active'); 
	$(this).parent().addClass('active'); 
	var currentTab = $(this).attr('href');
	$("#tabs div.tabs:visible").slideUp("slow",function(){
		$(currentTab).slideDown("slow"); 
	});
	return false;		
});
$('#configstoragelocations').each(function(){
	if (this.checked != true){
		$('#storageurl').attr("disabled",true);
		$('#storagedir').attr("disabled",true);
	}
	else {
		$('#remstorageoptions').show();
	}
});
$('#configstoragelocations').click(function(){
	if (this.checked == true) {
		$('#storageurl').removeAttr("disabled");
		$('#storagedir').removeAttr('disabled');
		$('#remstorageoptions').slideDown("slow");
	}
	else{
		$('#storageurl').attr("disabled",true);
		$('#storagedir').attr("disabled",true);
		$('#remstorageoptions').slideUp("slow");
	}
});
$('p.iteminfo').click(function(){
	$('p.iteminfo').hide("slow");
	});
$('.mysqlconn').keyup(function(){
	$('#al-testconn').fadeIn("fast",function(){
		$.ajax({
			url: "dbtest.php",
			async: true,
			dataType: "text",
			data: { mysqlserver: $('#mysqlserver').val(), mysqlusername: $('#mysqlusername').val(), mysqlpassword: $('#mysqlpassword').val(),mysqldb: $('#mysqldb').val() },
			success: function(data,type){
				if (data==200) {
					$('#mysqlserver').removeClass('warn');
					$('#mysqlusername').removeClass('warn');
					$('#mysqlpassword').removeClass('warn');
					$('#mysqldb').addClass('ok');
					$('#mysqlserver').addClass('ok');
					$('#mysqlusername').addClass('ok');
					$('#mysqlpassword').addClass('ok');
					$('#mysqldb').removeClass('warn');
				}
				else if (data==201) {
					$('#mysqlserver').removeClass('warn');
					$('#mysqlusername').removeClass('ok');
					$('#mysqlpassword').removeClass('ok');
					$('#mysqldb').removeClass('ok');
					$('#mysqldb').removeClass('warn');
					$('#mysqlserver').addClass('ok');
					$('#mysqlusername').addClass('warn');
					$('#mysqlpassword').addClass('warn');
				}
				else if (data==203) {
					$('#mysqlserver').removeClass('warn');
					$('#mysqlusername').removeClass('warn');
					$('#mysqlpassword').removeClass('warn');
					$('#mysqldb').removeClass('ok');
					$('#mysqldb').addClass('warn');
					$('#mysqlserver').addClass('ok');
					$('#mysqlusername').addClass('ok');
					$('#mysqlpassword').addClass('ok');
				}
				else{
					$('#mysqlserver').removeClass('ok');
					$('#mysqlusername').removeClass('ok');
					$('#mysqlpassword').removeClass('ok');
					$('#mysqldb').removeClass('ok');
					$('#mysqldb').removeClass('warn');
					$('#mysqlserver').addClass('warn');
					$('#mysqlusername').removeClass('warn');
					$('#mysqlpassword').removeClass('warn');
				}
				$('#al-testconn').hide();
			},
			error: function(){
				alert('<?php echo $lang["setup-mysqltestfailed"] ?>');
				$('#mysqlserver').addClass('warn');
				$('#mysqlusername').addClass('warn');
				$('#mysqlpassword').addClass('warn');
				$('#al-testconn').hide();
		}});
	});
});

// Check admin password security requirements/ policy -- client side
$('#admin_password').keyup(function() {
    $('#admin_test').fadeIn('slow', function() {
        var post_url  = 'setup.php';
        var post_data = {
            ajax: true,
            admin_password: $('#admin_password').val()
        };

        $.post(post_url, post_data, function(response) {

            $('#admin_password').removeClass('ok');
            $('#admin_password').addClass('warn');

            if(response.success === true)
                {
                $('#admin_password').addClass('ok');
                $('#admin_password_error').hide();
                }
            else if(response.success === false && response.error !== '')
                {
                $('#admin_password_error').text(response.error);
                $('#admin_password_error').show();
                }

            $('#admin_test').hide();

        }, 'json');
    });
});

$('a.iflink	').click(function(){
	$('p.iteminfo').hide("slow");
	var currentItemInfo = $(this).attr('href');
	$(currentItemInfo).show("fast");
	return false;
});
$('#mysqlserver').keyup();

});
</script> 
 
<style type="text/css"> 
 
<!--
#wrapper{ margin:0 auto;width:600px; }
 #intro {  margin-bottom: 40px; font-size:100%; background: #F7F7F7; text-align: left; padding: 40px; }
#introbottom { padding: 10px; clear: both; text-align:center;}
#preconfig {  float: right;background: #F1F1F1; padding: 25px;border-radius: 10px;box-shadow: #d7d7d7 1px 1px 9px;}
#preconfig h2 { border-bottom: 1px solid #ccc;	width: 100%;}
#preconfig p { font-size:110%; padding:0; margin:0; margin-top: 5px;}
#preconfig p.failure{ color: #f00; font-weight: bold; }

#structural_plugins {margin-bottom: 40px;font-size: 100%;background: #F7F7F7;text-align: left;padding: 20px;}
#structural_plugins h2{margin-bottom: 10px;}
.templateitem{padding:10px; padding-left:40px;}
.templateitem label{width: 220px;display: block;float: left;}
.templateitem input{display: block;float: left;}
.templateitem .desc{padding:0 10px;display: block;float: left;clear:left; padding-left:28px;}
.templateitem a.moreinfo{color:#72A939;padding-left: 0;}
.structurepluginradio{margin-right:10px;}

#tabs {font-size: 100%;}
#tabs > ul {float: left;margin: 0;padding: 0;border-bottom: 5px solid #F7F7F7;}
#tabs > ul >li { margin: 0; padding:0; margin-left: 8px; list-style: none; background: #777777; }
* html #tabs li { display: inline; /* ie6 double float margin bug */ }
#tabs > ul > li, #tabs  > ul > li a { float: left; border-top-left-radius: 10px; border-top-right-radius: 10px;}
#tabs > ul > li a { text-decoration: none; padding: 8px; color: #CCCCCC; font-weight: bold; }
#tabs > ul > li.active { background: #72A939; }
#tabs > ul > li.active a { color: #FFF }
#tabs div.tabs { background: #F7F7F7; clear: both; padding: 20px; text-align: left;}

p.iteminfo{ background: #e3fefa; width: 60%; color: #000; padding: 4px; margin: 10px; clear:both; }
strong { padding:0 5px; color: #F00; font-weight: bold; }
a.iflink { color: #F00; padding: 2px; border: 1px solid #444; margin-left: 4px; } 
#defaultlanguage{font-size: 1em;padding: 1px;}

input.warn { border: 2px solid #f00; }
input.ok{ border:2px solid #0f0; }
input#submit { margin: 30px; font-size:120%; }

div.configitem { padding-top:10px; padding-left:40px; padding-bottom: 5px; border-bottom: 1px solid #555555; clear:left;}
label { padding-right: 10px; width: 30%; font-weight: bold; }

div.configitem label { width:400px; display:block; float:left;}

div.advsection{ margin-bottom: 20px; }
.ajloadicon { padding-left:4px; }
h2#dbaseconfig{  min-height: 32px;}

.erroritem{ background: #fcc; border: 2px solid #f00; color: #000; padding: 10px; margin: 7px; font-weight:bold;}
.erroritem.p { margin: 0; padding:0px;padding-bottom: 5px;}
.warnitem{ background: #FFFFB3; border: 2px solid #FFFF33; color: #000; padding: 10px; margin: 7px; font-weight:bold;}
.warnitem.p { margin: 0; padding:0px;padding-bottom: 5px;}
#errorheader { font-size: 110%; margin-bottom: 20px; background: #fcc; border: 1px solid #f00; color: #000; padding: 10px; font-weight: bold; }
#configoutput { background: #777; color: #fff; text-align: left; padding: 20px; }
#warnheader { font-size: 110%; margin-bottom: 20px; background: #FFFFB3; border: 1px solid #FFFF33; color: #000; padding: 10px; font-weight: bold; }
.language {clear:both; text-align:center; padding:20px;}

--> 
 
</style> 
</head>
<body class="SlimHeader">
<div id="Header" class="slimheader_darken" style="height: 40px;margin-bottom: 20px;">
    <a href="#" onclick="return CentralSpaceLoad(this,true);" class="HeaderImgLink">
    	<img src="../gfx/titles/title.png" id="HeaderImg" />
    </a>
    <div id="HeaderNav1" class="HorizontalNav "><ul></ul></div>
	<div id="HeaderNav2" class="HorizontalNav HorizontalWhiteNav"><ul></ul></div> 
	<div class="clearer"></div>
</div>
<?php
	//Check if config file already exists and die with an error if it does.
	if (file_exists($outputfile))
	{
	?>
	<div id="errorheader"><?php echo $lang["setup-alreadyconfigured"];?></div> 
	</body>
	</html>
	<?php
	die(0);
	}
	if (!(isset($_REQUEST['submit']))){ //No Form Submission, lets setup some defaults
		if (!isset($storagedir) | $storagedir=="")
			{
			$storagedir = dirname(__FILE__)."/../filestore";
			$lang = set_language($defaultlanguage); # Updates $lang with $storagedir which is used in some strings.
			}
		if (isset($_SERVER['HTTP_HOST']))
			$baseurl = 'http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'],0,strlen($_SERVER['PHP_SELF'])-16);
		else
			$baseurl = 'http://'.php_uname('n'); //Set the baseurl to the machine hostname. 

                // Setup search paths (Currently only Linux/Mac OS X)
		$os=php_uname('s');
		if($os=='Linux' || $os=="Darwin"){
			$search_paths[]='/usr/bin';
			$search_paths[]='/sw/bin';
			$search_paths[]='/usr/share/bin';
			$search_paths[]='/usr/local/bin';
			$search_paths[]='/opt/local/bin';
		}
		//Check if we're on windows and set config_windows if we are.
		elseif(stristr($os,'windows')){
			$config_windows = true;
		}
		if (isset($search_paths)){
			foreach($search_paths as $path){
				if (file_exists($path.'/convert'))
					$imagemagick_path = $path;
				if (file_exists($path.'/gs'))
					$ghostscript_path = $path;
				if (file_exists($path.'/ffmpeg') || file_exists($path.'/avconv'))
					$ffmpeg_path = $path;
				if (file_exists($path.'/exiftool'))
					$exiftool_path = $path;
				if (file_exists($path.'/antiword'))
					$antiword_path= $path;
				if (file_exists($path.'/pdftotext'))
					$pdftotext_path = $path;
			}
		}

        $admin_fullname = 'Admin user';
        $admin_email    = '';
        $admin_username = 'admin';
        $admin_password = '';

	}
	else { //Form was submitted, lets do it!
		//Generate config.php Header
		//Note: The opening php tag is missing and is added when the file is written.
		//This allows the config to be displayed in the bottom div when in development mode.
		$config_windows = get_post_bool('config_windows');
		$exe_ext = $config_windows==true?'.exe':'';
		$config_output="";
		$config_output .= "###############################\r\n";
		$config_output .= "## ResourceSpace\r\n";
		$config_output .= "## Local Configuration Script\r\n";
		$config_output .= "###############################\r\n\r\n";
		$config_output .= "# All custom settings should be entered in this file.\r\n";  
		$config_output .= "# Options may be copied from config.default.php and configured here.\r\n\r\n";
		
		// Structural plugin
        $structural_plugin = get_post('structureplugin');
        if(!empty($structural_plugin))
        	{
        	$config_output.= "\r\n# Initial Structural Plugin used: ".$structural_plugin."\r\n\r\n\r\n";
        	}

		//Grab MySQL settings
		$mysql_server = get_post('mysql_server');
		$mysql_username = get_post('mysql_username');
		$mysql_password = get_post('mysql_password');
		$mysql_db = get_post('mysql_db');
		//Make a connection to the database using the supplied credentials and see if we can create and drop a table.
		if (@mysql_connect($mysql_server, $mysql_username, $mysql_password)){
			$mysqlversion=mysql_get_server_info();
		
			if ($mysqlversion<'5') 
				{
				$errors['databaseversion'] = true;
				}
			else 
				{
				if (@mysql_select_db($mysql_db))
					{
					if (@mysql_query("CREATE table configtest(test varchar(30))"))
						{
						@mysql_query("DROP table configtest");
						}
					else 
						{
						$errors['databaseperms'] = true;
						}
					}
				else 
					{$errors['databasedb'] = true;}
			}
		}
		else 
			{
			switch (mysql_errno())
				{
				case 1045:  //User login failure.
					$errors['databaselogin'] = true;
					break;
				default: //Must be a server problem.
					$errors['databaseserver'] = true;
					break;
				}
			}
		if (isset($errors))
			{
			$errors['database'] = mysql_error();
			}
		else 
			{
			//Test passed: Output MySQL config section
			$config_output .= "# MySQL database settings\r\n";
			$config_output .= "\$mysql_server = '$mysql_server';\r\n";
			$config_output .= "\$mysql_username = '$mysql_username';\r\n";
			$config_output .= "\$mysql_password = '$mysql_password';\r\n";
			$config_output .= "\$mysql_db = '$mysql_db';\r\n";
			$config_output .= "\r\n";
			}
		
		//Check MySQL bin path (not required)
		$mysql_bin_path = sslash(get_post('mysql_bin_path'));
		if ((isset($mysql_bin_path)) && ($mysql_bin_path!='')){
			if (!file_exists($mysql_bin_path.'/mysqldump'.$exe_ext))
				$errors['mysqlbinpath'] = true;
			else $config_output .="\$mysql_bin_path = '$mysql_bin_path';\r\n\r\n";
		}

		//Check baseurl (required)
		$baseurl = sslash(get_post('baseurl'));
		# In certain PHP versions there is a bug in filter_var using FILTER_VALIDATE_URL causing correct URLs containing a hyphen to fail.
		if (filter_var("http://www.filter-test.com", FILTER_VALIDATE_URL))
			{
			# The filter is working.
			$filterresult = filter_var($baseurl, FILTER_VALIDATE_URL);
			}
		else
			{
			# The filter is not working, use the hostname of the $baseurl and replace the problematic characters.
			$testbaseurl = str_replace(
				parse_url($baseurl,PHP_URL_HOST),
				str_replace(
					array("_", "-"),
					array("^", "x"), # _ is not allowed for hostname, - is allowed
					parse_url($baseurl,PHP_URL_HOST)),
				$baseurl);
			$filterresult = filter_var($testbaseurl, FILTER_VALIDATE_URL);
			}
		if ((isset($baseurl)) && ($baseurl!='') && ($baseurl!='http://my.site/resourcespace') && ($filterresult)){
			//Check that the base url seems correct by attempting to fetch the license file
			if (url_exists($baseurl.'/license.txt')){
				$config_output .= "# Base URL of the installation\r\n";
				$config_output .= "\$baseurl = '$baseurl';\r\n\r\n";
			}
			else { //Under certain circumstances this test may fail, but the URL is still correct, so warn the user.
				$warnings['baseurlverify']= true;
			}
		}
		else {
			$errors['baseurl'] = true;
		}

        $admin_fullname = get_post('admin_fullname');
        $admin_email    = get_post('admin_email');
        $admin_username = get_post('admin_username');
        $admin_password = get_post('admin_password');

        if('' === trim($admin_fullname))
            {
            $errors['admin_fullname'] = true;
            }


        if('' === trim($admin_email) || ('' !== trim($admin_email) && !filter_var($admin_email, FILTER_VALIDATE_EMAIL)))
            {
            $errors['admin_email'] = true;
            }
        else
            {
            // Email_notify is not used much now so we default it to the admin e-mail address.
            $config_output .= "# Email settings\r\n";
            $config_output .= "\$email_notify = '$admin_email';\r\n";
            }

        // Check password
        $password_validation_result = check_password($admin_password);
        if('' === $admin_password)
            {
            $errors['admin_password'] = 'Super Admin password cannot be empty!';
            }
        else if('' !== $admin_password && is_string($password_validation_result) && '' !== $password_validation_result)
            {
            $errors['admin_password'] = $password_validation_result;
            }

		//Verify email addresses are valid


		$email_from = get_post('email_from');
        if('' != $email_from)
            {
            if(filter_var($email_from, FILTER_VALIDATE_EMAIL) && ('resourcespace@my.site' !== $email_from))
                {
                $config_output .= "\$email_from = '$email_from';\r\n";
                }
            else
                {
                $errors['email_from'] = true;
                }
            }
        else
            {
            $errors['email_from'] = true;
            }

		// Set random keys. These used to be requested on the setup form but there was no reason to ask the user for these.
                $config_output .= "# Secure keys\r\n";
                $config_output .= "\$spider_password = '" . generatePassword() . "';\r\n";
                $config_output .= "\$scramble_key = '" . generatePassword() . "';\r\n";
                $config_output .= "\$api_scramble_key = '" . generatePassword() . "';\r\n\r\n";
		
		$config_output .= "# Paths\r\n";
		//Verify paths actually point to a useable binary
		$imagemagick_path = sslash(get_post('imagemagick_path'));
		$ghostscript_path = sslash(get_post('ghostscript_path'));
		$ffmpeg_path = sslash(get_post('ffmpeg_path'));
		$exiftool_path = sslash(get_post('exiftool_path'));
		$antiword_path = sslash(get_post('antiword_path'));
		$pdftotext_path = sslash(get_post('pdftotext_path'));
		if ($imagemagick_path!='')
			if (!file_exists($imagemagick_path.'/convert'.$exe_ext))
				$errors['imagemagick_path'] = true;
			else $config_output .= "\$imagemagick_path = '$imagemagick_path';\r\n";
		if ($ghostscript_path!='')
			if (!file_exists($ghostscript_path.'/gs'.$exe_ext))
				$errors['ghostscript_path'] = true;
			else $config_output .= "\$ghostscript_path = '$ghostscript_path';\r\n";
		if ($ffmpeg_path!='')
			if (!file_exists($ffmpeg_path.'/ffmpeg'.$exe_ext) && !file_exists($ffmpeg_path.'/avconv'.$exe_ext))
				$errors['ffmpeg_path'] = true;
			else $config_output .= "\$ffmpeg_path = '$ffmpeg_path';\r\n";
		if ($exiftool_path!='')
			if (!file_exists($exiftool_path.'/exiftool'.$exe_ext))
				$errors['exiftool_path'] = true;
			else $config_output .= "\$exiftool_path = '$exiftool_path';\r\n";
		if ($antiword_path!='')
			if (!file_exists($antiword_path.'/antiword'.$exe_ext))
				$errors['antiword_path'] = true;
			else $config_output .= "\$antiword_path = '$antiword_path';\r\n";
		if ($pdftotext_path!='')
			if (!file_exists($pdftotext_path.'/pdftotext'.$exe_ext))
				$errors['pdftotext_path'] = true;
			else $config_output .= "\$pdftotext_path = '$pdftotext_path';\r\n\r\n";
		
		//Deal with some checkboxes
		if (!$allow_account_request = get_post_bool('allow_account_request'))
			$config_output .= "\$allow_account_request = false;\r\n";
        if ($enable_remote_apis = get_post_bool('enable_remote_apis'))
			$config_output .= "\$enable_remote_apis = true;\r\n";    
		if (!$allow_password_change = get_post_bool('allow_password_change'))
			$config_output .= "\$allow_password_change = false;\r\n";
		if ($research_request = get_post_bool('research_request'))
			$config_output .= "\$research_request = true;\r\n";
		if ($use_theme_as_home = get_post_bool('use_theme_as_home'))
			$config_output .= "\$use_theme_as_home = true;\r\n";
		if ($disable_languages = get_post_bool('disable_languages'))
			$config_output .= "\$disable_languages = true;\r\n";
		if ($config_windows)
			$config_output .= "\$config_windows = true;\r\n";
		if ($defaultlanguage!='en')
			$config_output .= "\$defaultlanguage = '$defaultlanguage';\r\n";
		
		//Advanced Settings
		if ($_REQUEST['applicationname']!=$applicationname){
			$applicationname = get_post('applicationname');
			$config_output .= "\$applicationname = '$applicationname';\r\n";
		}
		if (get_post_bool('configstoragelocations')){
			$configstoragelocations = true;
			$storagedir = get_post('storagedir');
			$storageurl = get_post('storageurl');
			$config_output .= "\$storagedir = '$storagedir';\r\n";
			$config_output .= "\$storageurl = '$storageurl';\r\n";
		}
		else {
			$storagedir = dirname(__FILE__)."/../filestore";
			$configstoragelocations = false;
		}
		/*
		$ftp_server = get_post('ftp_server');
		$ftp_username = get_post('ftp_username');
		$ftp_password = get_post('ftp_password');
		$ftp_defaultfolder = get_post('ftp_defaultfolder');
		$config_output .= "\$ftp_server = '$ftp_server';\r\n";
		$config_output .= "\$ftp_username = '$ftp_username';\r\n";
		$config_output .= "\$ftp_password = '$ftp_password';\r\n";
		$config_output .= "\$ftp_defaultfolder = '$ftp_defaultfolder';\r\n";
		*/
		$use_smtp=get_post('use_smtp');
		if($use_smtp)
			{
			$smtp_secure= get_post('smtp_secure');
			$smtp_host= get_post('smtp_host');
			$smtp_port= get_post('smtp_port');
			$smtp_auth= get_post('smtp_auth');
			$smtp_username= get_post('smtp_username');
			$smtp_password= get_post('smtp_password');
			$config_output .= "#SMTP settings\r\n";
			$config_output .= "\$use_smtp = true;\r\n";
			$config_output .= "\$use_phpmailer = true;\r\n";
			$config_output .= "\$smtp_secure = '$smtp_secure';\r\n";
			$config_output .= "\$smtp_host = '$smtp_host';\r\n";
			$config_output .= "\$smtp_port = $smtp_port;\r\n";
			if($smtp_auth)
				{
				$config_output .= "\$smtp_auth = true;\r\n";
				$config_output .= "\$smtp_username = '$smtp_username';\r\n";
				$config_output .= "\$smtp_password = '$smtp_password';\r\n";
				}
			$config_output .= " \r\n";
			}



        //Design Configuration
        $slimtheme=get_post_bool('slim-theme');
        if($slimtheme)
        	{ 
        	$config_output.= "\r\n#Design Changes\r\n\$slimheader=true;\r\n";
        	#$config_output.= "\$available_themes=array('slimcharcoal', 'multi', 'whitegry','greyblu','black');\r\n\$defaulttheme='slimcharcoal';\r\n";
        	}
                
        # Append defaults for new systems.
        $config_output.=file_get_contents(dirname(__FILE__) . "/../include/config.new_installs.php");
	}
?>
<?php //Output Section

if ((isset($_REQUEST['submit'])) && (!isset($errors)))
	{
	//Form submission was a success.  Output the config file and refrain from redisplaying the form.
	$fhandle = fopen($outputfile, 'w') or die ("Error opening output file.  (This should never happen, we should have caught this before we got here)");
	fwrite($fhandle, "<?php\r\n".$config_output); //NOTE: php opening tag is prepended to the output.
	fclose($fhandle);

    // Check database structure now
    $suppress_headers = true;
	include_once '../include/db.php';
	check_db_structs();

	if(!empty($structural_plugin) && !$develmode)
		{
		$suppress_headers=true;
		include_once "../include/db.php";
		//BUILD Data from plugin
		global $mysql_db, $resource_field_column_limit;
	
		# Check for path
		$path="../plugins/".$structural_plugin."/dbstruct/";
		
		# Tables first.
		# Load existing tables list
		$ts=sql_query("show tables",false,-1,false);
		$tables=array();
		for ($n=0;$n<count($ts);$n++)
			{
			$tables[]=$ts[$n]["Tables_in_" . $mysql_db];
			}
		$dh=opendir($path);
		while (($file = readdir($dh)) !== false)
			{
			if (substr($file,0,5)=="data_")
				{
				$table=str_replace(".txt","",substr($file,5));
				sql_query("TRUNCATE $table");
				# Add initial data
				$data=$file;
				if (file_exists($path . "/" . $data))
					{
					$f=fopen($path . "/" . $data,"r");
					while (($row = fgetcsv($f,5000)) !== false)
						{
						# Escape values
						for ($n=0;$n<count($row);$n++)
							{
							$row[$n]=escape_check($row[$n]);
							$row[$n]="'" . $row[$n] . "'";
							if ($row[$n]=="''") {$row[$n]="null";}
							}
						sql_query("insert into $table values (" . join (",",$row) . ")",false,-1,false);
						}
					}

				# Check all indices exist
				# Load existing indexes
				$existing=sql_query("show index from $table",false,-1,false);
						
				$file=str_replace("data_","index_",$file);
				if (file_exists($path . "/" . $file))
					{
					$done=array(); # List of indices already processed.
					$f=fopen($path . "/" . $file,"r");
					while (($col = fgetcsv($f,5000)) !== false)
						{
						# Look for this index in the existing indices.
						$found=false;
						for ($n=0;$n<count($existing);$n++)
							{
							if ($existing[$n]["Key_name"]==$col[2]) {$found=true;}
							}
						if (!$found && !in_array($col[2],$done))
							{
							# Add this index.
							
							# Fetch list of columns for this index
							$cols=array();
							$f2=fopen($path . "/" . $file,"r");
							while (($col2 = fgetcsv($f2,5000)) !== false)
								{
								if ($col2[2]==$col[2]) {$cols[]=$col2[4];}
								}
							$sql="create index " . $col[2] . " on $table (" . join(",",$cols) . ")";
							sql_query($sql,false,-1,false);
							$done[]=$col[2];
							}
						}
					}
				}
			}
		}


    // Copy slideshow images under filestore in order to avoid
    // overwriting them when doing svn update
    $homeanim_folder = 'gfx/homeanim';
    $slideshow_files = get_slideshow_files_data();

    foreach($slideshow_files as $id => $file_info)
        {
        $to_folder = $storagedir . '/system/slideshow';
        $to_file   = $to_folder . '/' . basename($file_info['file_path']);

        // Make sure there is a target location
        if(!(file_exists($to_folder) && is_dir($to_folder)))
            {
            mkdir($to_folder, 0777, true);
            }

        if(file_exists($file_info['file_path']) && copy($file_info['file_path'], $to_file))
            {
            debug("AT SETUP: Copied slideshow image {$id} from \"{$file_info['file_path']}\" to \"{$to_file}\".");
            }
        }

    // Create user
    $credentials_username = '';
    $credentials_password = '';

    $user_count = sql_value("SELECT count(*) value FROM user WHERE username = '{$admin_username}'", 0);
    if(0 == $user_count)
        {
        $password_hash = hash('sha256', md5('RS' . $admin_username . $admin_password));

        // Note: First user should always be part of Super Admin, hence user group is set to 3
        $sql_query = "INSERT INTO user(username, password, fullname, email, usergroup) VALUES('" . escape_check($admin_username) . "', '" . $password_hash . "', '" . escape_check($admin_fullname) . "', '" . escape_check($admin_email) . "', 3)";

        $credentials_username = $admin_username;
        $credentials_password = $admin_password;

        sql_query($sql_query);
        }

    // We got so far but if somehow we didn't create a user so far, trigger error now and let user be aware of it
    if('' == $credentials_username)
        {
        trigger_error('Unexpected error! ResourceSpace could not set your credentials or username doesn\'t have a value!');
        }
        ?>
	<div id="intro">
		<h1><?php echo $lang["setup-successheader"]; ?></h1>
		<p><?php echo $lang["setup-successdetails"]; ?></p>
		<p><?php echo $lang["setup-successnextsteps"]; ?></p>
		<ul>
			<li><?php echo $lang["setup-successremovewrite"]; ?></li>
			<li><?php echo $lang["setup-visitwiki"]; ?></li>
			<li><a href="<?php echo $baseurl;?>/login.php"><?php echo $lang["setup-login_to"] . " " . $applicationname; ?></a>
				<ul>
					<li><?php echo $lang["username"] . ': ' . $credentials_username; ?></li>
					<li><?php echo $lang["password"] . ': ' . $credentials_password; ?></li>
				</ul>
			</li>
		</ul>
    <?php
    if('' == $credentials_password)
        {
        ?>
        <p>Warning! for some unknown reason your password is being seen as not having a value. Please log in and change the password!</p>
        <?php
        }
    ?>
	</div>
	<?php
	}
else
{
?>
<form action="setup.php" method="POST">
<?php echo $config_windows==true?'<input type="hidden" name="config_windows" value="true"/>':'' ?>
	<div id="intro">
			<div id="preconfig">
				<h2><?php echo $lang["installationcheck"]; ?></h2>
				<?php 
					$continue = true;
					$phpversion = PHP_VERSION;
					if(version_compare($phpversion, '5.3.0', '<='))
						{
						$result   = $lang["status-fail"] . ": " . str_replace('?', '5.3.0', $lang['shouldbeversion']);
						$pass     = false;
						$continue = false;
						} 
					else
						{
						$result = $lang["status-ok"];
						$pass = true;
						}
				?>
				<p class="<?php echo ($pass==true?'':'failure'); ?>"><?php echo str_replace("?", "PHP", $lang["softwareversion"]) . ": " . $phpversion . ($pass==false?'<br>':' ') . "(" . $result . ")"; ?></p>
				<p><?php echo str_replace("%phpinifile", php_ini_loaded_file(), $lang["php-config-file"]); ?></p>
				<?php
					if(function_exists('gd_info'))
                        {
						$gdinfo = gd_info();

    					if (is_array($gdinfo))
    						{
    						$version = $gdinfo["GD Version"];
    						$result = $lang["status-ok"];
    						$pass = true;
    						}
                        }
					else
						{
						$version = $lang["status-notinstalled"];
						$result = $lang["status-fail"];
						$pass = false;
						$continue = false;
						}
				?>
				<p class="<?php echo ($pass==true?'':'failure'); ?>"><?php echo str_replace("?", "GD", $lang["softwareversion"]) . ": " . $version . ($pass!=true?'<br>':' ') . "(" . $result . ")"; ?></p>
				<?php
					$memory_limit=ini_get("memory_limit");
					if (ResolveKB($memory_limit)<(200*1024))
						{
						$result = $lang["status-warning"] . ": " . str_replace("?", "200M", $lang["shouldbeormore"]);
						$pass = false;
						}
					else
						{
						$result = $lang["status-ok"];
						$pass = true;
						}
				?>
				<p class="<?php echo ($pass==true?'':'failure'); ?>"><?php echo str_replace("?", "memory_limit", $lang["phpinivalue"]) . ": " . $memory_limit . ($pass==false?'<br>':' ') . "(" . $result . ")"; ?></p>
				<?php
					$post_max_size = ini_get("post_max_size");
					if (ResolveKB($post_max_size)<(100*1024))
						{
						$result = $lang["status-warning"] . ": " . str_replace("?", "100M", $lang["shouldbeormore"]);
						$pass = false;
						}
					else
						{
						$result = $lang["status-ok"];
						$pass = true;
						}
				?>
				<p class="<?php echo ($pass==true?'':'failure'); ?>"><?php echo str_replace("?", "post_max_size", $lang["phpinivalue"]) . ": " . $post_max_size . ($pass==false?'<br>':' ') . "(" . $result . ")"; ?></p>
				<?php
					$upload_max_filesize = ini_get("upload_max_filesize");
					if (ResolveKB($upload_max_filesize)<(100*1024))
						{
						$result = $lang["status-warning"] . ": " . str_replace("?", "100M", $lang["shouldbeormore"]);
						$pass = false;
						}
					else
						{
						$result = $lang["status-ok"];
						$pass = true;
						}
				?>
				<p class="<?php echo ($pass==true?'':'failure'); ?>"><?php echo str_replace("?", "upload_max_filesize", $lang["phpinivalue"]) . ": " . $upload_max_filesize . ($pass==false?'<br>':' ') . "(" . $result . ")"; ?></p>
				<?php
					$success = is_writable('../include');
					if ($success===false)
						{
						$result = $lang["status-fail"] . ": " . $lang["setup-include_not_writable"];
						$pass = false;
						$continue = false;
						}	
					else
						{
						$result = $lang["status-ok"];
						$pass = true;
						}
				?>
					<p class="<?php echo ($pass==true?'':'failure');?>"><?php echo $lang["setup-checkconfigwrite"] . ($pass==false?'<br>':' ') . "(" . $result . ")"; ?></p>
				<?php
					if (!file_exists($storagedir)) {@mkdir ($storagedir,0777);}
					$success = is_writable($storagedir);
					if ($success===false)
						{
						$result = $lang["status-warning"] . ": " . $lang["nowriteaccesstofilestore"] . "<br/>" . $lang["setup-override_location_in_advanced"];
						$pass = false;
						}
					else
						{
						$result = $lang["status-ok"];
						$pass = true;
						}
				?>
					<p class="<?php echo ($pass==true?'':'failure'); ?>"><?php echo $lang["setup-checkstoragewrite"] . ($pass==false?'<br>':' ') . "(" . $result . ")"; ?></p>
			</div>
			<h1><?php echo $lang["setup-welcome"];?></h1>
			<p><?php echo $lang["setup-introtext"];?></p>
			<p><?php echo $lang["setup-visitwiki"];?></p>
			<div class="language" style="clear: none;text-align: left;padding: 0px;">
					<label for="defaultlanguage"><?php echo $lang["language"];?>:</label><select id="defaultlanguage" name="defaultlanguage">
						<?php
							foreach($languages as $code => $text){
								echo "<option value=\"$code\"";
								if ($code == $defaultlanguage)
									echo ' selected';
								echo ">$text</option>";
							}
						?>
					</select>
					<input type="submit" id="changelanguage" name="changelanguage" value="<?php echo $lang["action-changelanguage"]; ?>"/>
				</div>
			<div id="introbottom">
			<?php if ($continue===false) { ?>
			<strong><?php echo $lang["setup-checkerrors"];?></strong>
			<?php } else { ?>
			<script type="text/javascript">
			$(document).ready(function(){
				$('#tabs').show();
			});
			</script>
			<?php } ?>
			</div>
	</div>
	<?php
	include "../include/plugin_functions.php";
	$plugins_dir = dirname(__FILE__)."/../plugins/";
	# Build an array of available plugins.
	$dirh = opendir($plugins_dir);
	$plugins_avail = array();

	while (false !== ($file = readdir($dirh))) 
	   {
	   if (is_dir($plugins_dir.$file)&&$file[0]!='.')
	      	{
	        # Look for a <pluginname>.yaml file.
	        $plugin_yaml = get_plugin_yaml($plugins_dir.$file.'/'.$file.'.yaml', false);
	        if(isset($plugin_yaml["category"]) 
	         		&& $plugin_yaml["category"]=="structural"
	         		&& isset($plugin_yaml["info_url"])
	         		&& isset($plugin_yaml["setup_desc"])
	         		&& isset($plugin_yaml["name"])
	         	)
	         	{
	         	foreach ($plugin_yaml as $key=>$value)
		            {
		            $plugins_avail[$file][$key] = $value ;
		            }
	         	}    
	      	}
	   }
	closedir($dirh);
	if(!empty($plugins_avail))
		{
		ksort ($plugins_avail);
		?>
		<div id="structural_plugins">
			<h2><?php echo $lang["setup-structuralplugins"]; ?></h2>
			<?php
			$default= (isset($structural_plugin) && !empty($structural_plugin)) ? $structural_plugin : "general_structure";
			foreach($plugins_avail as $plugin)
				{ ?>
				<div class="templateitem">
					<input 
						class="structurepluginradio" 
						type="radio" 
						id="structureplugin-<?php echo $plugin["name"]; ?>" 
						name="structureplugin" 
						<?php
						if($plugin["name"]==$default){echo "checked";}
						?>
						value="<?php echo $plugin["name"];?>"
					/>
					<label for="structureplugin-<?php echo $plugin["name"]; ?>"><?php echo ucfirst(preg_replace("/(_|-)/"," ",$plugin["name"]));?></label>
					<span class="desc">
						<?php 
						echo $plugin["setup_desc"];
						if(substr($plugin["setup_desc"], -1)!="."){echo ".";}
						if(isset($plugin["info_url"]) && !empty($plugin["info_url"]))
						{ ?>
						<a 
							class="moreinfo" 
							target="_blank" 
							href="<?php echo $plugin["info_url"]?>"
						>
							<?php echo $lang["more-information"]."..."; ?>
						</a>
						<?php
						} ?>
					</span>					
					<div style="clear:both;"></div>		
				</div>
				<?php
				} ?>
			<div style="clear:both;"></div>
		</div>
		<?php
		}
	?>
	
	<?php if (isset($errors)){ ?>	
		<div id="errorheader"><?php echo $lang["setup-errorheader"];?></div>
	<?php } ?>	
	<?php if (isset($warnings)){ ?>	
		<div id="warnheader"><?php echo $lang["setup-warnheader"];?></div>
	<?php } ?>	
	<div id="tabs" class="starthidden" style="width:90%;margin:0 auto 0 auto;">
		<ul>
			<li><a href="#tab-1"><?php echo $lang["setup-basicsettings"];?></a></li>
			<li><a href="#tab-2"><?php echo $lang["setup-advancedsettings"];?></a></li>
		</ul>
		<div class="tabs" id="tab-1">
			<h1><?php echo $lang["setup-basicsettings"];?></h1>
			<p><?php echo $lang["setup-basicsettingsdetails"];?></p>
				<h2 id="dbaseconfig"><?php echo $lang["setup-dbaseconfig"];?><img class="starthidden ajloadicon" id="al-testconn" src="../gfx/ajax-loader.gif"/></h2>
				<?php if(isset($errors['database'])){?>
					<div class="erroritem"><?php echo $lang["setup-mysqlerror"];?>
						<?php 
						if(isset($errors['databaseversion'])) 
							{echo $lang["setup-mysqlerrorversion"];}
						if(isset($errors['databaseserver']))
							{echo $lang["setup-mysqlerrorserver"];} 
						if(isset($errors['databaselogin']))
							{echo $lang["setup-mysqlerrorlogin"];}
						if(isset($errors['databasedb']))
							{echo $lang["setup-mysqlerrordbase"];}
						if(isset($errors['databaseperms']))
							{echo $lang["setup-mysqlerrorperms"];} 
						?>
						
						<p><?php echo $errors['database'];?></p>
					</div>
				<?php } ?>
						
				<div class="configitem">
					<label for="mysqlserver"><?php echo $lang["setup-mysqlserver"];?></label><input class="mysqlconn" type="text" id="mysqlserver" name="mysql_server" value="<?php echo $mysql_server;?>"/><strong>*</strong><a class="iflink" href="#if-mysql-server">?</a>
					<p class="iteminfo" id="if-mysql-server"><?php echo $lang["setup-if_mysqlserver"];?></p>
				</div>
				<div class="configitem">
					<label for="mysqlusername"><?php echo $lang["setup-mysqlusername"];?></label><input class="mysqlconn" type="text" id="mysqlusername" name="mysql_username" value="<?php echo $mysql_username;?>"/><strong>*</strong><a class="iflink" href="#if-mysql-username">?</a>
					<p class="iteminfo" id="if-mysql-username"><?php echo $lang["setup-if_mysqlusername"];?></p>		
				</div>
				<div class="configitem">
					<label for="mysqlpassword"><?php echo $lang["setup-mysqlpassword"];?></label><input class="mysqlconn" type="password" id="mysqlpassword" name="mysql_password" value="<?php echo $mysql_password;?>"/><strong>*</strong><a class="iflink" href="#if-mysql-password">?</a>
					<p class="iteminfo" id="if-mysql-password"><?php echo $lang["setup-if_mysqlpassword"];?></p>
				</div>
				<div class="configitem">
					<label for="mysqldb"><?php echo $lang["setup-mysqldb"];?></label><input id="mysqldb" class="mysqlconn" type="text" name="mysql_db" value="<?php echo $mysql_db;?>"/><strong>*</strong><a class="iflink" href="#if-mysql-db">?</a>
					<p class="iteminfo" id="if-mysql-db"><?php echo $lang["setup-if_mysqldb"];?></p>
				</div>
				
				<div class="configitem">
					<?php if(isset($errors['mysqlbinpath'])){?>
						<div class="erroritem"><?php echo $lang["setup-err_mysqlbinpath"];?></div>
					<?php } ?>
					<label for="mysqlbinpath"><?php echo $lang["setup-mysqlbinpath"];?></label><input id="mysqlbinpath" type="text" name="mysql_bin_path" value="<?php echo $mysql_bin_path;?>"/><a class="iflink" href="#if-mysql-bin-path">?</a>
					<p class="iteminfo" id="if-mysql-bin-path"><?php echo $lang["setup-if_mysqlbinpath"];?></p>
				</div>
			</p>
			<p class="configsection">
				<h2><?php echo $lang["setup-generalsettings"];?><img id="admin_test" class="starthidden ajloadicon" src="../gfx/ajax-loader.gif"/></h2>
				<div class="configitem">
					<label for="applicationname"><?php echo $lang["setup-applicationname"];?></label><input id="applicationname" type="text" name="applicationname" value="<?php echo $applicationname;?>"/><a class="iflink" href="#if-applicationname">?</a>
					<p class="iteminfo" id="if-applicationname"><?php echo $lang["setup-if_applicationname"];?></p>
				</div>
				<div class="configitem">
					<?php if(isset($errors['baseurl'])){?>
						<div class="erroritem"><?php echo $lang["setup-err_baseurl"];?></div>
					<?php } ?>
					<?php if(isset($warnings['baseurlverify'])){?>
						<div class="warnitem"><?php echo $lang["setup-err_baseurlverify"];?></div>
					<?php } ?>
					<label for="baseurl"><?php echo $lang["setup-baseurl"];?></label><input id="baseurl" type="text" name="baseurl" value="<?php echo $baseurl;?>"/><strong>*</strong><a class="iflink" href="#if-baseurl">?</a>
					<p class="iteminfo" id="if-baseurl"><?php echo $lang["setup-if_baseurl"];?></p>
				</div>
                <div class="configitem">
                    <?php
                if(isset($errors['admin_fullname']))
                    {
                    ?>
                    <div class="erroritem"><?php echo $lang['setup-admin_fullname_error']; ?></div>
                    <?php
                    }
                    ?>
                    <label for="admin_fullname"><?php echo $lang['setup-admin_fullname']; ?></label>
                    <input id="admin_fullname" class="admin_credentials" type="text" name="admin_fullname" value="<?php echo $admin_fullname; ?>"/>
                </div>
                <div class="configitem">
                <?php
                if(isset($errors['admin_email']))
                    {
                    ?>
                    <div class="erroritem"><?php echo $lang['setup-emailerr']; ?></div>
                    <?php
                    }
                    ?>
                    <label for="admin_email"><?php echo $lang['setup-admin_email']; ?></label>
                    <input id="admin_email" class="admin_credentials" type="text" name="admin_email" value="<?php echo $admin_email; ?>"/><strong>*</strong>
                </div>
                <div class="configitem">
                    <label for="admin_username"><?php echo $lang['setup-admin_username']; ?></label>
                    <input id="admin_username" class="admin_credentials" type="text" name="admin_username" value="<?php echo $admin_username; ?>"/><strong>*</strong><a class="iflink" href="#if-admin-username">?</a>
                    <p id="if-admin-username" class="iteminfo"><?php echo $lang['setup-if_admin_username']; ?></p>
                </div>
                <div class="configitem">
                    <div id="admin_password_error" class="erroritem" <?php echo !isset($errors['admin_password']) ? 'style="display: none;"' : ''; ?>><?php echo isset($errors['admin_password']) ? $errors['admin_password'] : ''; ?></div>
                    <label for="admin_password"><?php echo $lang['setup-admin_password']; ?></label>
                    <input id="admin_password" class="admin_credentials" type="password" name="admin_password" value="<?php echo $admin_password; ?>"/><strong>*</strong><a class="iflink" href="#if-admin-password">?</a>
                    <p id="if-admin-password" class="iteminfo"><?php echo $lang['setup-if_admin_password']; ?></p>
                </div>
				<div class="configitem">
                <?php
                if(isset($errors['email_from']))
                    {
                    ?>
                    <div class="erroritem"><?php echo $lang["setup-emailerr"];?></div>
                    <?php
                    }
                    ?>
					<label for="emailfrom"><?php echo $lang["setup-emailfrom"];?></label><input id="emailfrom" type="text" name="email_from" value="<?php echo $email_from;?>"/><strong>*</strong><a class="iflink" href="#if-emailfrom">?</a>
					<p id="if-emailfrom" class="iteminfo"><?php echo $lang["setup-if_emailfrom"];?></p>
				</div>

			</p>
			<p class="configsection">
				<h2><?php echo $lang["setup-paths"];?></h2>
				<p><?php echo $lang["setup-pathsdetail"];?></p>
				<div class="configitem">
					<?php if(isset($errors['imagemagick_path'])){?>
						<div class="erroritem"><?php echo $lang["setup-err_path"];?> 'convert'.</div>
					<?php } ?>
					<label for="imagemagickpath"><?php echo str_replace("%bin", "ImageMagick/GraphicsMagick", $lang["setup-binpath"]) . ":"; ?></label><input id="imagemagickpath" type="text" name="imagemagick_path" value="<?php echo @$imagemagick_path; ?>"/>
				</div>
				<div class="configitem">
					<?php if(isset($errors['ghostscript_path'])){?>
						<div class="erroritem"><?php echo $lang["setup-err_path"];?> 'gs'.</div>
					<?php } ?>
					<label for="ghostscriptpath"><?php echo str_replace("%bin", "Ghostscript", $lang["setup-binpath"]) . ":"; ?></label><input id="ghostscriptpath" type="text" name="ghostscript_path" value="<?php echo @$ghostscript_path; ?>"/>
				</div>
				<div class="configitem">
					<?php if(isset($errors['ffmpeg_path'])){?>
						<div class="erroritem"><?php echo $lang["setup-err_path"];?> 'ffmpeg'.</div>
					<?php } ?>
					<label for="ffmpegpath"><?php echo str_replace("%bin", "FFMpeg/libav", $lang["setup-binpath"]) . ":"; ?></label><input id="ffmpegpath" type="text" name="ffmpeg_path" value="<?php echo @$ffmpeg_path; ?>"/>
				</div>
				<div class="configitem">
					<?php if(isset($errors['exiftool_path'])){?>
						<div class="erroritem"><?php echo $lang["setup-err_path"];?> 'exiftool'.</div>
					<?php } ?>
					<label for="exiftoolpath"><?php echo str_replace("%bin", "Exiftool", $lang["setup-binpath"]) . ":"; ?></label><input id="exiftoolpath" type="text" name="exiftool_path" value="<?php echo @$exiftool_path; ?>"/>
				</div>
				<div class="configitem">
				<?php if(isset($errors['antiword_path'])){?>
						<div class="erroritem"><?php echo $lang["setup-err_path"];?> 'AntiWord'.</div>
					<?php } ?>
					<label for="antiwordpath"><?php echo str_replace("%bin", "AntiWord", $lang["setup-binpath"]) . ":"; ?></label><input id="antiwordpath" type="text" name="antiword_path" value="<?php echo @$antiword_path; ?>"/>
				</div>
				
				<div class="configitem">
					<?php if(isset($errors['pdftotext_path'])){?>
						<div class="erroritem"><?php echo @$lang["setup-err_path"];?> 'pdftotext'.</div>
					<?php } ?>
					<label for="pdftotextpath"><?php echo str_replace("%bin", "PDFtotext", $lang["setup-binpath"]) . ":"; ?></label><input id="pdftotextpath" type="text" name="pdftotext_path" value="<?php echo @$pdftotext_path; ?>"/>
				</div>
			</p>
			<p><?php echo $lang["setup-basicsettingsfooter"];?></p>
		</div>
		<div class="tabs" id="tab-2">
			<h1><?php echo $lang["setup-advancedsettings"];?></h2>
			<h2><?php echo $lang["setup-generaloptions"];?></h2>
			<div class="advsection" id="generaloptions">
				<div class="configitem">
					<label for="allow_password_change"><?php echo $lang["setup-allow_password_change"];?></label><input id="allow_password_change" type="checkbox" name="allow_password_change" <?php echo ($allow_password_change==true?'checked':'');?>/><a class="iflink" href="#if-allow_password_change">?</a>
					<p class="iteminfo" id="if-allow_password_change"><?php echo $lang["setup-if_allowpasswordchange"];?></p>
				</div>
				<div class="configitem">
					<label for="allow_account_request"><?php echo $lang["setup-allow_account_requests"];?></label><input id="allow_account_request" type="checkbox" name="allow_account_request" <?php echo ($allow_account_request==true?'checked':'');?>/>
				</div>
				<div class="configitem">
					<label for="research_request"><?php echo $lang["setup-display_research_request"];?></label><input id="research_request" type="checkbox" name="research_request" <?php echo ($research_request==true?'checked':'');?>/><a class="iflink" href="#if-research_request">?</a>
					<p class="iteminfo" id="if-research_request"><?php echo $lang["setup-if_displayresearchrequest"];?></p>
				</div>
				<div class="configitem">
					<label for="use_theme_as_home"><?php echo $lang["setup-themes_as_home"];?></label><input id="use_theme_as_home" type="checkbox" name="use_theme_as_home" <?php echo ($use_theme_as_home==true?'checked':'');?>/>
				</div>
                <div class="configitem">
					<label for="enable_remote_apis"><?php echo $lang["setup-enable_remote_apis"];?></label><input id="enable_remote_apis" type="checkbox" name="enable_remote_apis" <?php echo ($enable_remote_apis==true?'checked':'');?>/><a class="iflink" href="#if-enable_remote_apis">?</a>
					<p class="iteminfo" id="if-enable_remote_apis"><?php echo $lang["setup-if_enableremoteapis"];?></p>
				</div>
				
			</div>	
			<h2><?php echo $lang["setup-remote_storage_locations"];?></h2>
			<div class="advsection" id="storagelocations">
				<div class="configitem">
					<label for="configstoragelocations"><?php echo $lang["setup-use_remote_storage"];?></label><input id="configstoragelocations" type="checkbox" name="configstoragelocations" value="true" <?php echo ($configstoragelocations==true?'checked':'');?>/><a class="iflink" href="#if-remstorage">?</a>
					<p class="iteminfo" id="if-remstorage"><?php echo $lang["setup-if_useremotestorage"];?></p>
				</div>
				<div id="remstorageoptions" class="starthidden">
					<div class="configitem">
						<label for="storagedir"><?php echo $lang["setup-storage_directory"] . ":"; ?></label><input id="storagedir" type="text" name="storagedir" value="<?php echo $storagedir;?>"/><a class="iflink" href="#if-storagedir">?</a>
						<p class="iteminfo" id="if-storagedir"><?php echo $lang["setup-if_storagedirectory"];?></p>
					</div>
					<div class="configitem">
						<label for="storageurl"><?php echo $lang["setup-storage_url"] . ":"; ?></label><input id="storageurl" type="text" name="storageurl" value="<?php echo $storageurl;?>"/><a class="iflink" href="#if-storageurl">?</a>
						<p class="iteminfo" id="if-storageurl"><?php echo $lang["setup-if_storageurl"];?></p>
					</div>
				</div>
			</div>
			<?php
			/* Remove FTP Settings
			<h2><?php echo $lang["setup-ftp_settings"];?></h2>
			<div class="advsection" id="ftpsettings">
				<div class="configitem">
					<label for="ftp_server"><?php echo $lang["ftpserver"] . ":"; ?></label><input id="ftp_server" name="ftp_server" type="text" value="<?php echo $ftp_server;?>"/><a class="iflink" href="#if-ftpserver">?</a>
					<p class="iteminfo" id="if-ftpserver"><?php echo $lang["setup-if_ftpserver"];?></p>
				</div>
				<div class="configitem">
					<label for="ftp_username"><?php echo $lang["ftpusername"] . ":"; ?></label><input id="ftp_username" name="ftp_username" type="text" value="<?php echo $ftp_username;?>"/>
				</div>
				<div class="configitem">
					<label for="ftp_password"><?php echo $lang["ftppassword"] . ":"; ?></label><input id="ftp_password" name="ftp_password" type="text" value="<?php echo $ftp_password;?>"/>
				</div>
				<div class="configitem">
					<label for="ftp_defaultfolder"><?php echo $lang["ftpfolder"] . ":"; ?></label><input id="ftp_defaultfolder" name="ftp_defaultfolder" type="text" value="<?php echo $ftp_defaultfolder;?>"/>
				</div>
			</div>
			*/?>

			<h2><?php echo $lang["setup-smtp-settings"]; ?></h2>
			<div class="advsection" id="smtpsettings">
				<div class="configitem">
					<label for="use_smtp"><?php echo $lang["usesmtp"] . ":"; ?></label>
					<input id="use_smtp" name="use_smtp" type="checkbox"  <?php echo $use_smtp?"checked":"";?>/>
					<a class="iflink" href="#if-usesmtp">?</a>
					<p class="iteminfo" id="if-usesmtp"><?php echo $lang["setup-if-usesmtp"];?></p>
				</div>
				<div id="use-SMTP-settings">
					<div class="configitem">
						<label for="smtp_secure"><?php echo $lang["smtpsecure"] . ":"; ?></label>
						<input id="smtp_secure" name="smtp_secure" type="text" value="<?php echo $smtp_secure;?>" />
						<a class="iflink" href="#if-smtpsecure">?</a>
						<p class="iteminfo" id="if-smtpsecure"><?php echo $lang["setup-if-smtpsecure"];?></p>
					</div>
					<div class="configitem">
						<label for="smtp_host"><?php echo $lang["smtphost"] . ":"; ?></label>
						<input id="smtp_host" name="smtp_host" type="text" value="<?php echo $smtp_host;?>"/>
						<a class="iflink" href="#if-smtphost">?</a>
						<p class="iteminfo" id="if-smtphost"><?php echo $lang["setup-if-smtphost"];?></p>
					</div>
					<div class="configitem">
						<label for="smtp_port"><?php echo $lang["smtpport"] . ":"; ?></label>
						<input id="smtp_port" name="smtp_port" type="text" value="<?php echo $smtp_port;?>"/>
						<a class="iflink" href="#if-smtpport">?</a>
						<p class="iteminfo" id="if-smtpport"><?php echo $lang["setup-if-smtpport"];?></p>
					</div>
					<div class="configitem">
						<label for="smtp_auth"><?php echo $lang["smtpauth"] . ":"; ?></label>
						<input id="smtp_auth" name="smtp_auth" type="checkbox" checked />
						<a class="iflink" href="#if-smtpauth">?</a>
						<p class="iteminfo" id="if-smtpauth"><?php echo $lang["setup-if-smtpauth"];?></p>
					</div>
					<div class="configitem">
						<label for="smtp_username"><?php echo $lang["smtpusername"] . ":"; ?></label>
						<input id="smtp_username" name="smtp_username" type="text" value="<?php echo $smtp_username;?>"/>
						<a class="iflink" href="#if-smtpusername">?</a>
						<p class="iteminfo" id="if-smtpusername"><?php echo $lang["setup-if-smtpusername"];?></p>
					</div>
					<div class="configitem">
						<label for="smtp_password"><?php echo $lang["smtppassword"] . ":"; ?></label>
						<input id="smtp_password" name="smtp_password" type="password" value="<?php echo $smtp_password;?>"/>
						<a class="iflink" href="#if-smtppassword">?</a>
						<p class="iteminfo" id="if-smtppassword"><?php echo $lang["setup-if-smtppassword"];?></p>
					</div>
				</div>
			</div>

			<h2><?php echo $lang["design-options"];?></h2>
			<div class="advsection" id="designsettings">
				<div class="configitem">
					<label for="slim-theme"><?php echo $lang["use-slim-theme"] . ":"; ?></label>
					<input id="slim-theme" name="slim-theme" type="checkbox" checked /><a class="iflink" href="#if-slimtheme">?</a>
					<p class="iteminfo" id="if-slimtheme"><?php echo $lang["setup-if_slimtheme"];?></p>
				</div>
			</div>
		</div>
		<input type="submit" id="submit" name="submit" value="<?php echo $lang["setup-begin_installation"];?>"/>
	</div>
</form>
<script>
	jQuery("#use_smtp").click(function(){
		if(jQuery(this).prop("checked")) {
			jQuery("#use-SMTP-settings").show(300);
		} else {
			jQuery("#use-SMTP-settings").hide(300);
		}
	});
	if(!jQuery("#use_smtp").prop("checked")) {
		jQuery("#use-SMTP-settings").hide();
	}
</script>
<?php }
if (($develmode)&& isset($config_output))
	{ ?>
	<div id="configoutput">
		<h1><?php echo $lang["setup-configuration_file_output"] . ":"; ?></h1>
		<pre><?php echo $config_output; ?></pre>
	</div>
	<?php 
	} ?>
</body>
</html>	
