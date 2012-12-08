<?php

function HookResourceofthedayHomeReplaceslideshow ()
	{
	include_once dirname(__FILE__)."/../inc/rotd_functions.php";

	global $baseurl, $view_title_field;

	$rotd=get_resource_of_the_day();
	if ($rotd===false) {return false;} # No ROTD, return false to disable hook and display standard slide show.

    # Get preview width
    $sizes = get_image_sizes($rotd, true);
    foreach ($sizes as $size)
        {
        if ($size["id"]=="pre")
            {
            $width = $size["width"];
            break;
            }
        }


    # Fetch title
    $title = sql_value("select value from resource_data where resource='$rotd' and resource_type_field=$view_title_field","");

	# Fetch caption
	$caption=sql_value("select value from resource_data where resource='$rotd' and resource_type_field=18","");

	# Show resource!
	$pre=get_resource_path($rotd,false,"pre",false,"jpg");
	?>
	<div class="HomePicturePanel RecordPanel" style="width: <?php echo $width ?>px; padding-left: 3px;">
	<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl?>/pages/view.php?ref=<?php echo $rotd ?>"><img class="ImageBorder" style="margin-bottom: 10px;" src="<?php echo $pre ?>" /></a>
	<br />
	<h2 ><?php echo i18n_get_translated(htmlspecialchars($title)) ?></h2>
	<?php echo $caption ?>
	</div>
	<?php
	
	return true;
	}


?>