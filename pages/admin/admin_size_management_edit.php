<?php

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";

if (!checkperm("a"))
	{
	exit ("Permission denied.");
	}


$find=getval("find","");
$order_by=getval("orderby","");
$url_params= ($order_by ? "&orderby={$order_by}" : "") . ($find ? "&find={$find}" : "");

# create new record from callback
$new_size_id=getvalescaped("newsizeid","");
if ($new_size_id!="")
	{
	sql_query("insert into preview_size(id,name,internal,width,height) values('" . strtolower($new_size_id) . "','{$new_size_id}',0,0,0)");
	$ref=sql_insert_id();
	log_activity(null,LOG_CODE_CREATED,$new_size_id,'preview_size','id',$ref,null,'');
	redirect("{$baseurl_short}pages/admin/admin_size_management_edit.php?ref={$ref}{$url_params}");	// redirect to prevent repost and expose form data
	exit;
	}

$ref = getvalescaped('ref', '');

if (!sql_value("select ref as value from preview_size where ref='{$ref}' and internal<>'1'",false) && !$internal_preview_sizes_editable)		// note that you are not allowed to edit internal sizes without $internal_preview_sizes_editable=true
	{
	redirect("{$baseurl_short}pages/admin/admin_size_management.php?{$url_params}");		// fail safe by returning to the size management page if duff ref passed
	exit;
	}

if (getval("deleteme",false))
	{
	sql_query("delete from preview_size where ref='{$ref}'");
	log_activity(null,LOG_CODE_DELETED,null,'preview_size',null,$ref);
	redirect("{$baseurl_short}pages/admin/admin_size_management.php?{$url_params}");		// return to the size management page
	exit;
	}

if (getval("save",false))
	{
	$cols=array();

	$name=getvalescaped("name","");
	if ($name!="") $cols["name"]=$name;

	$width=getvalescaped("width",-1,true);
	if ($width>=0) $cols["width"]=$width;

	$height=getvalescaped("height",-1,true);
	if ($height>=0) $cols["height"]=$height;
	
	if($preview_quality_unique)
		{
		$quality=getvalescaped("quality",0,true);
		if($quality>0 && $quality<=100)
			{
			$cols["quality"]=$quality;
			}
		else
			{
			$cols["quality"]=$imagemagick_quality;	
			}
		}

	$cols["padtosize"]=(getval('padtosize',false) ? "1" : "0");
	$cols["allow_preview"]=(getval('allowpreview',false) ? "1" : "0");
	$cols["allow_restricted"]=(getval('allowrestricted',false) ? "1" : "0");

	foreach ($cols as $col=>$val)
		{
		if (isset($sql_columns))
			{
			$sql_columns.=",";
			}
		else
			{
			$sql_columns="";
			}
		$sql_columns.="{$col}='{$val}'";
		log_activity(null,LOG_CODE_EDITED,$val,'preview_size',$col,$ref);
		}

	if (isset($sql_columns)) sql_query("update preview_size set {$sql_columns} where ref={$ref}");
	redirect("{$baseurl_short}pages/admin/admin_size_management.php?{$url_params}");		// return to the size management page
	exit;
	}

$record = sql_query("select * from preview_size where ref={$ref}");
$record = $record[0];

include "../../include/header.php";

?><form method="post" enctype="multipart/form-data" action="<?php echo $baseurl_short; ?>pages/admin/admin_size_management_edit.php?ref=<?php echo $ref . $url_params ?>" id="mainform"
	onSubmit="return CentralSpacePost(this,true);" >

	<div class="BasicsBox">

	<p>
		<a href="" onclick="return CentralSpaceLoad('<?php echo $baseurl_short; ?>pages/admin/admin_size_management.php?<?php echo $url_params; ?>',true);">&lt;&nbsp;<?php echo $lang['page-title_size_management']; ?></a>
	</p>

	<h1><?php echo $lang['page-title_size_management_edit']; ?></h1>
	<p><?php echo $lang['page-subtitle_size_management_edit']; ?></p>

		<input type="hidden" name="save" value="1">

		<div class="Question">
			<label for="reference"><?php echo $lang["property-id"]; ?></label>
			<div class="Fixed"><?php echo $record['id']; ?></div>
			<div class="clearerleft"></div>
		</div>

		<div class="Question">
			<label for="name"><?php echo $lang["property-name"]; ?></label>
			<input name="name" type="text" class="stdwidth" value="<?php echo $record['name']; ?>">	
			<div class="clearerleft"></div>
		</div>

		<div class="Question">
			<label for="name"><?php echo $lang["property-width"]; ?></label>
			<input name="width" type="text" class="shrtwidth" value="<?php echo $record['width']; ?>">
			<div class="clearerleft"></div>
		</div>

		<div class="Question">
			<label for="name"><?php echo $lang["property-height"]; ?></label>
			<input name="height" type="text" class="shrtwidth" value="<?php echo $record['height']; ?>">
			<div class="clearerleft"></div>
		</div>
		
		<?php
		if($preview_quality_unique)
			{
			?>
			<div class="Question">
				<label><?php echo $lang["property-quality"]; ?></label>
				<input name="quality" type="text" class="shrtwidth" value="<?php echo($record['quality']!=''?$record['quality']:$imagemagick_quality)?>">
				<div class="clearerleft"></div>
			</div>
			<?php
			}
		?>

		<div class="Question">
			<label><?php echo $lang['property-pad_to_size']; ?></label>
			<input name="padtosize" type="checkbox" value="1"<?php if($record['padtosize']) {?> checked="checked"<?php }?>>
			<div class="clearerleft"></div>
		</div>

		<div class="Question">
			<label><?php echo $lang['property-allow_preview']; ?></label>
			<input name="allowpreview" type="checkbox" value="1"<?php if($record['allow_preview']) {?> checked="checked"<?php }?>>
			<div class="clearerleft"></div>
		</div>

		<div class="Question">
			<label><?php echo $lang['property-allow_restricted_download']; ?></label>
			<input name="allowrestricted" type="checkbox" value="1"<?php if($record['allow_restricted']) {?> checked="checked"<?php }?>>
			<div class="clearerleft"></div>
		</div>
		
		<?php
		if(!$record['internal'])
			{
			?>
			<div class="Question">
				<label><?php echo $lang["fieldtitle-tick_to_delete_size"]?></label>
				<input name="deleteme" type="checkbox" value="1">
				<div class="clearerleft"></div>
			</div>
			<?php
			}
		?>

		<div class="QuestionSubmit">
			<label for="buttonsave"></label>
			<input name="buttonsave" type="submit" value="&nbsp;&nbsp;<?php echo $lang["save"]; ?>&nbsp;&nbsp;">
		</div>

	</div>

</form>

<?php
include "../../include/footer.php";
