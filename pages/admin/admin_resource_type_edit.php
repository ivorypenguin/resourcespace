<?php
/**
 * User edit form display page (part of Team Center)
 * 
 * @package ResourceSpace
 * @subpackage Pages_Team
 */
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php"; 

if (!checkperm("a"))
	{
	exit ("Permission denied.");
	}
        
        

$ref=getvalescaped("ref","",true);
$name=getvalescaped("name","");
$config_options=getvalescaped("config_options","");
$allowed_extensions=getvalescaped("allowed_extensions","");
$tab=getvalescaped("tab","");
$confirm_delete=false;

$restype_order_by=getvalescaped("restype_order_by","rt");
$restype_sort=getvalescaped("restype_sort","asc");

$url_params = array("ref"=>$ref,
		    "restype_order_by"=>$restype_order_by,
		    "restype_sort"=>$restype_sort);
$url=generateURL($baseurl . "/pages/admin/admin_resource_type_edit.php",$url_params);

$backurl=getvalescaped("backurl","");
if($backurl=="")
    {
    $backurl=$baseurl . "/pages/admin/admin_resource_types.php?ref=" . $ref;
    }

if (getval("save","")!="")
	{
	# Save resource type data

	log_activity(null,LOG_CODE_EDITED,$name,'resource_type','name',$ref);
	log_activity(null,LOG_CODE_EDITED,$config_options,'resource_type','config_options',$ref);
	log_activity(null,LOG_CODE_EDITED,$allowed_extensions,'resource_type','allowed_extensions',$ref);
	log_activity(null,LOG_CODE_EDITED,$tab,'resource_type','tab_name',$ref);

        if ($execution_lockout) {$config_options="";} # Not allowed to save PHP if execution_lockout set.
        
	sql_query("update resource_type set name='" . $name . "',config_options='" . $config_options . "', allowed_extensions='" . $allowed_extensions . "',tab_name='" . $tab . "' where ref='$ref'");
	
	redirect(generateURL($baseurl_short . "pages/admin/admin_resource_types.php",$url_params));
	}
	
if (getval("delete","")!="")
	{
	
	$targettype=getvalescaped("targettype","");
	# Check for resources of this  type
	$affectedresources=sql_array("select ref value from resource where resource_type='$ref' and ref>0",0);
	if(count($affectedresources)>0 && $targettype=="")
	    {
	    //User needs to confirm a new resource type
	    $confirm_delete=true;
	    }
        else
	    {
	    //If we have a target type, move the current resources to the new resource type
	    if($targettype!="" && $targettype!=$ref)
		{
		include "../../include/resource_functions.php"; 
		foreach($affectedresources as $affectedresource)
		    {update_resource_type($affectedresource,$targettype);}
		}
	    // Delete the resource type
	    sql_query("delete from resource_type where ref='$ref'");
		redirect(generateURL($baseurl_short . "pages/admin/admin_resource_types.php",$url_params));
	    }
	
	
	}

# Fetch  data
$restypedata=sql_query ("
	select 
		ref,
		name,
		order_by,
		config_options,
		allowed_extensions,
		tab_name
        from
		resource_type
	where
            ref='$ref'
	order by name"
);
$restypedata=$restypedata[0];


include "../../include/header.php";


?>
<div class="BasicsBox">
<p>    
<a href="<?php echo $backurl ?>" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["back"]?></a>
</p>
<h1><?php echo i18n_get_translated($restypedata["name"]) ?></h1>
<?php if (isset($error_text)) { ?><div class="FormError"><?php echo $error_text?></div><?php } ?>
<?php if (isset($saved_text)) { ?><div class="PageInfoMessage"><?php echo $saved_text?></div><?php } ?>

<form method=post action="<?php echo $baseurl_short?>pages/admin/admin_resource_type_edit.php?ref=<?php echo urlencode($ref) ?>&backurl=<?php echo urlencode ($url) ?>">


<?php if($confirm_delete)
    {
    ?>
    <div class="PageInfoMessage">
    <?php
    echo str_replace("%%RESOURCECOUNT%%",count($affectedresources),$lang["resource_type_delete_confirmation"]) . "<br>";	
        
    echo $lang["resource_type_delete_select_new"];
    ?>
    </div>
    <?php
    
    $destrestypes=$resource_types=sql_query ("
	select 
		ref,
		name
        from
		resource_type
	where
	    ref<>'$ref'
	order by name asc
	"
    );
    
    ?>
    <div class="Question">  
    <label for="targettype"><?php echo $lang["resourcetype"]; ?></label>    
    <div class="tickset">
      <div class="Inline"><select name="targettype" id="targettype" >
        <option value="" selected ><?php echo $lang["select"]; ?></option>
	<?php
	  for($n=0;$n<count($destrestypes);$n++){
	?>
		<option value="<?php echo $destrestypes[$n]["ref"]; ?>"><?php echo i18n_get_translated($destrestypes[$n]["name"]); ?></option>
	<?php
	  }
	?>
        </select>
      </div>
    </div>
	<div class="clearerleft"> </div>
    </div>
    
     
    <div class="QuestionSubmit">
    <label for="buttons"> </label>			
    <input name="cancel" type="submit" value="&nbsp;&nbsp;<?php echo $lang["cancel"]?>&nbsp;&nbsp;" />
    <input name="delete" type="submit" value="&nbsp;&nbsp;<?php echo $lang["action-delete"]?>&nbsp;&nbsp;" onClick="return confirm('<?php echo $lang["confirm-deletion"]?>');"/>
    </div>
    
    <?php   
    
    exit();	
    }
else
    {
?> 
    
    
    <input type=hidden name=ref value="<?php echo urlencode($ref) ?>">
    
    <div class="Question"><label><?php echo $lang["property-reference"]?></label>
	<div class="Fixed"><?php echo  $restypedata["ref"] ?></div>
	<div class="clearerleft"> </div>
    </div>
    
    
    <div class="Question">
	<label><?php echo $lang["property-name"]?></label>
	<input name="name" type="text" class="stdwidth" value="<?php echo htmlspecialchars($restypedata["name"])?>">
	<div class="clearerleft"> </div>
    </div>
    
    <div class="Question">
	<label><?php echo $lang["property-allowed_extensions"]?></label>
	<input name="allowed_extensions" type="text" class="stdwidth" value="<?php echo htmlspecialchars($restypedata["allowed_extensions"])?>">
	
	<div class="FormHelp" style="padding:0;clear:left;" >
	    <div class="FormHelpInner"><?php echo $lang["information-allowed_extensions"] ?>
	    </div>
	</div>    
	<div class="clearerleft"> </div>    
    </div>
    
    
    
    <?php if (!$execution_lockout) { ?>
    <div class="Question">
	<label><?php echo $lang["property-override_config_options"] ?></label>
	<textarea name="config_options" class="stdwidth" rows=5 cols=50><?php echo htmlspecialchars($restypedata["config_options"])?></textarea>
	<div class="FormHelp" style="padding:0;clear:left;" >
	    <div class="FormHelpInner"><?php echo $lang["information-resource_type_config_override"] ?>
	    </div>
	</div>
	<div class="clearerleft"> </div>
    </div>
    <?php } ?>

    <div class="Question">
	<label><?php echo $lang["property-tab_name"]?></label>
	<input name="tab" type="text" class="stdwidth" value="<?php echo htmlspecialchars($restypedata["tab_name"])?>">
	<div class="FormHelp" style="padding:0;clear:left;" >
	    <div class="FormHelpInner"><?php echo $lang["admin_resource_type_tab_info"] ?>
	    </div>
	</div>
	<div class="clearerleft"> </div>
    </div>
    
    
    <div class="QuestionSubmit">
    <label for="buttons"> </label>			
    <input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["save"]?>&nbsp;&nbsp;" />
    <input name="delete" type="submit" value="&nbsp;&nbsp;<?php echo $lang["action-delete"]?>&nbsp;&nbsp;" onClick="return confirm('<?php echo $lang["confirm-deletion"]?>');"/>
    </div>
    <?php
    } // End of normal page (not confirm deletion)
    ?>

</form>
</div><!-- End of Basics Box -->

<?php


include "../../include/footer.php";
?>
