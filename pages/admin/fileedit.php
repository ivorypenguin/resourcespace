<?php
include "../../include/db.php";
include_once "../../include/general.php";?>
<?php include "../../include/authenticate.php"; if (!checkperm("a")) {exit("Permission denied");}?>
<?php

if (!$web_config_edit)
	exit('Feature not available');

if (get_magic_quotes_gpc()) {
    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
}


$folder=getval("folder","default");

$file=getval("file","");

$error="";
# Chars to translate on save/load
$from=array("&","<",">");
$to=array("&AMP;","&LT;","&GT;");

# Save value?
if (getval("submit","")!="")
	{
	$value=getval("value","");
	$value=str_replace($to,$from,$value);
	$f=fopen($file,"w");fwrite($f,$value);fclose($f);
	}

if (getval("delete","")!="")
	{
	unlink($file);
	?>
	<script language="Javascript">
	top.main.left.EmptyNode(<?php echo getval("parent","")?>);
	top.main.left.ReloadNode(<?php echo getval("parent","")?>);
	</script>
	<?php
	exit("File deleted.");
	}

# Fetch value

$value=join("",file($file));
$value=str_replace($from,$to,$value);

//include "include/header.php";
?><html>
<head>
<link rel="stylesheet" type="text/css" href="<?php echo $baseurl?>/pages/admin/muse.css" />
<script src="../../lib/CodeMirror/js/codemirror.js" type="text/javascript"></script>
<script type="text/javascript" src="../../lib/js/jquery-1.7.2.min.js"></script>
<style type="text/css">
    .CodeMirror-line-numbers {
        width: 2.5em;
        color: #aaa;
        background-color: #eee;
        text-align: right;
        padding-right: .3em;
        font-size: 10pt;
        font-family: monospace;
        padding-top: .4em;
    }
</style>
<link href="<?php echo $baseurl?>/css/Col-<?php echo (isset($userfixedtheme) && $userfixedtheme!="")?$userfixedtheme:getval("colourcss",$defaulttheme)?>.css" rel="stylesheet" type="text/css" media="screen,projection,print" id="colourcss" />
<?php
# Include CSS files for for each of the plugins too (if provided)
for ($n=0;$n<count($plugins);$n++)
    {
    $csspath=dirname(__FILE__)."/../../../plugins/" . $plugins[$n] . "/css/style.css";
    if (file_exists($csspath))
        {
        ?>
        <link href="<?php echo $baseurl?>/plugins/<?php echo $plugins[$n]?>/css/style.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" type="text/css" media="screen,projection,print"  />
        <?php
        }
    $theme=((isset($userfixedtheme) && $userfixedtheme!=""))?$userfixedtheme:getval("colourcss",$defaulttheme);
    $csspath=dirname(__FILE__)."/../../../plugins/" . $plugins[$n] . "/css/Col-".$theme.".css";
    if (file_exists($csspath))
        {
        ?>
        <link href="<?php echo $baseurl?>/plugins/<?php echo $plugins[$n]?>/css/Col-<?php echo $theme?>.css?css_reload_key=<?php echo $css_reload_key?>" rel="stylesheet" type="text/css" media="screen,projection,print" id="<?php echo $plugins[$n]?>css" />
        <?php
        }
    }
?>
<title>Administration</title>

</head>

<body style="background-position:0px -85px;margin:0;padding:10px;">

<style type="text/css">

.CodeMirror-line-numbers {font-size:9pt; }

</style>

<?php if ($error!="") { ?>
<div class=propbox style="font-weight:bold;color:red;"><?php echo $error?></div><br><br>
<?php } ?>

<div class="proptitle"><?php echo $lang["file"] . ": "  . str_replace("../","",$file); if (basename($file)=="config.default.php"){echo "<br />" . $lang["configdefault-title"];}
if (basename($file)=="config.php"){echo "<br />" . $lang["config-title"];}?></div>
<div class="propbox">

<form method=post>
<textarea style="height:100%;" id="code" name="value"><?php echo $value?></textarea>
<input type=hidden name="file" value="<?php echo $file?>">

<table width="100%">
<tr>
<?php 
$filename=basename($file); 
if ($filename!="config.php" && $filename!="config.default.php"){
	?>
	<td align=left><input type="submit" name="delete" value="<?php echo $lang["action-delete"] ?>" style="width:100px;" onclick="return confirm('<?php echo $lang["filedeleteconfirm"] ?>');"></td>
<?php } ?>

<?php if ($filename!="config.default.php"){
?>
<td align=right><input style="margin-top:20px;" type="submit" name="submit" value="<?php echo $lang["save"] ?>" style="width:150px;" onclick="this.value='<?php echo $lang["pleasewait"] ?>';"></td></tr>
<?php } ?>
</table>
</form>

</div>
<?php $pathinfo=pathinfo($file);
if ($pathinfo['extension']=="css"){$parserfile='"parsecss.js"';} 
else {$parserfile='"parsexml.js", "tokenizejavascript.js", "parsejavascript.js",
                     "../contrib/php/js/tokenizephp.js", "../contrib/php/js/parsephp.js",
                     "../contrib/php/js/parsephphtmlmixed.js"';}?>
<script type="text/javascript">
    var editor = CodeMirror.fromTextArea('code', {
    height: "80%",
    parserfile: [<?php echo $parserfile?>],
	stylesheet: ["../../lib/CodeMirror/css/xmlcolors.css", "../../lib/CodeMirror/css/jscolors.css", "../../lib/CodeMirror/css/csscolors.css", "../../lib/CodeMirror/contrib/php/css/phpcolors.css"],
    path: "../../lib/CodeMirror/js/",
    continuousScanning: 500,
		
	lineNumbers: true

   });
</script>


</body>
</html>
