<?php
#
# Setup page for the auto_group plugin
#

// Do the include and authorization checking ritual
include '../../../include/db.php';
include '../../../include/general.php';
include '../../../include/authenticate.php'; if (!checkperm('a')) {exit ($lang['error-permissiondenied']);}

$auto_group_templates_array=array();
// Specify the name of this plugin and the heading to display for the page.
$plugin_name='auto_group';
$page_heading=$lang['auto_group_heading'];
$page_intro='';

// Build configuration variable descriptions
$page_def[]=config_add_section_header("General","");
$page_def[]=config_add_single_ftype_select("auto_group_field",$lang['auto_group_field'],300,0);
$page_def[]=config_add_multi_group_select("auto_group_templates",$lang['auto_group_templates']);

# take the templates select and use it to create a default selector
$auto_group_templates_array=array();
foreach($auto_group_templates as $key => $ref){
	$ref_name=sql_value("select name value from usergroup where ref=$ref",'');
	$auto_group_templates_array[$key]=$ref_name;
}
$page_def[]=config_add_single_select("auto_group_template_default",$lang['auto_group_template_default'],$auto_group_templates_array,false);
$page_def[]=config_add_single_group_select("auto_group_parent",$lang['auto_group_parent']);


// Do the page generation ritual
$upload_status=config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $page_heading, $page_intro);
include '../../../include/footer.php';

?><script type="text/javascript">
	
	jQuery(document).ready(function () {
		jQuery("#group_field").change(function () {
			if(jQuery("#group_field_error").length){
				jQuery("#group_field_error").remove();
			}
			var field=jQuery("#group_field").val();

			jQuery.ajax({
				type: 'POST',
				async: false,
				data: {data:field},
				url:  '<?php echo $baseurl_short?>plugins/auto_group/pages/check_group_field.php',
			}).done(function(data){
				data=data.replace(/<!--(.*?)-->/gm, "");
				if(data!='true'){
					jQuery("#group_field").after("<div class='FormError' id='group_field_error'><br/><br/>"+data+"</div>");
				}
			});
		});
	});
</script>
