<?php

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";

if (!checkperm("a"))
	{
	exit ("Permission denied.");
	}

include "../../include/resource_functions.php";


$find=getvalescaped("find","");
$offset=getvalescaped("offset",0);
if (array_key_exists("find",$_POST)) {$offset=0;} # reset page counter when posting
    
    
$restypefilter=getvalescaped("restypefilter","",true);
$restypesfilter=($restypefilter!="")?array($restypefilter):"";
$field_order_by=getvalescaped("field_order_by","ref");
$field_sort=getvalescaped("field_sort","asc");

//$url_params="ref=" . $ref . "&find=" . $find . "&restypefilter=" . $restypefilter . "&field_order_by=" . $field_order_by . "&field_sort=" . $field_sort;
//$url=urlencode($baseurl . "/pages/admin/admin_resource_type_fields.php?" . $url_params);

$backurl=getvalescaped("backurl","");
if($backurl=="")
    {
    $backurl=$baseurl . "/pages/admin/admin_home.php";
    }

$allow_reorder=false;
// Allow sorting if we are ordering a single resource type, or if $use_order_by_tab_view is true (which means order_by values are across all resource types) and we can see all fields
if($field_order_by=="order_by" &&  (($use_order_by_tab_view && $restypefilter=="") || (!$use_order_by_tab_view && $restypefilter!=""))){$allow_reorder=true;}


include "../../include/header.php";


$url_params = array("restypefilter"=>$restypefilter,
		    "field_order_by"=>$field_order_by,
		    "field_sort"=>$field_sort,
		    "find" =>$find);
$url=generateURL($baseurl . "/pages/admin/admin_resource_type_fields.php",$url_params);


if (getval("newfield","")!="")
	{
	$newfieldrestype=getvalescaped("newfieldrestype",0,true);
	sql_query("insert into resource_type_field (title,resource_type) values('" . getvalescaped("newfield","") . "','$newfieldrestype')");
	$new=sql_insert_id();
	log_activity(null,LOG_CODE_CREATED,getvalescaped("newfield",""),'resource_type_field','title',$new,null,'');
	redirect($baseurl_short . 'pages/admin/admin_resource_type_field_edit.php?ref=' . $new);
	}
	
if(getval("deleted","")!="")
    {
    $error_text=$lang["admin_field_deleted"] . "# " . htmlspecialchars(getval("deleted",""));	
    }
    
	
	
function addColumnHeader($orderName, $labelKey)
    {
	global $baseurl, $group, $field_order_by, $field_sort, $find, $lang, $restypefilter, $url_params;

	if ($field_order_by == $orderName && $field_sort=="asc")
		$arrow = '<span class="DESC"></span>';
	else if ($field_order_by == $orderName && $field_sort=="desc")
		$arrow = '<span class="ASC"></span>';
	else
		$arrow = '';
		
	?>
	<td><a href="<?php echo $baseurl ?>/pages/admin/admin_resource_type_fields.php?restypefilter=<?php echo $restypefilter ?>&field_order_by=<?php echo $orderName ?>&field_sort=<?php echo ($field_sort=="desc" || $field_order_by=="order_by") ? 'asc' : 'desc';
		  ?>&find=<?php echo urlencode($find)?>" onClick="return CentralSpaceLoad(this);"><?php
		  echo $lang[$labelKey] . $arrow ?></a>
	</td>
	<?php
      }
      
?>

  
  <h1><?php echo $lang["admin_resource_type_fields"]?></h1>
  <?php
  $introtext=text("introtext");
  if ($introtext!="")
    {
    echo "<p>" . text("introtext") . "</p>";
    }
 
$fields=get_resource_type_fields($restypesfilter, $field_order_by, $field_sort, $find);
$resource_types=sql_query("select ref, name from resource_type");
$arr_restypes=array();
foreach($resource_types as $resource_type)
	{
	$arr_restypes[$resource_type["ref"]]=$resource_type["name"];
	}
$arr_restypes[0]=$lang["resourcetype-global_field"];
$arr_restypes[999]=$lang["resourcetype-archive_only"];

$results=count($fields);

?>


<div class="BasicsBox">



<div class="FormError" id="PageError"
  <?php
  if (!isset($error_text)) { ?> style="display:none;"> <?php }
  else { echo ">" . $error_text ; } ?>
</div>

<?php
if(!$allow_reorder && ($use_order_by_tab_view || $restypefilter!="" || $field_order_by!="order_by" ))
  {
 ?>
  <a href="<?php echo $baseurl . "/pages/admin/admin_resource_type_fields.php?restypefilter=" . (($use_order_by_tab_view)?"":$restypefilter) . "&field_order_by=order_by&field_sort=asc" ?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php if($use_order_by_tab_view){echo $lang["admin_resource_type_field_reorder_mode_all"];}else{echo $lang["admin_resource_type_field_reorder_mode"];}?></a></p>  
  <?php
  }
else
    {
    if($allow_reorder)
	{	
	echo "<p>" . $lang["admin_resource_type_field_reorder_information"] ."</p>";
	}
    else 
	{
	echo "<div id=\"PageInfo\"><p>" . $lang["admin_resource_type_field_reorder_select_restype"] ."</p></div>";
	}
    }
  ?>

<form method="post" id="AdminResourceTypeFieldForm" onSubmit="return CentralSpacePost(this,true);"  action="<?php echo generateURL($baseurl . "/pages/admin/admin_resource_type_fields.php",array("field_order_by"=>$field_order_by,"field_sort"=>$field_sort,"find" =>$find)) ?>" >
		
	<div class="Question">  
		<label for="restypefilter"><?php echo $lang["property-resource_type"]; ?></label>
		<div class="tickset">
		  <div class="Inline">
		  <select name="restypefilter" id="restypefilter" onChange="return CentralSpacePost(this.form,true);" >
			<option value=""<?php if ($restypefilter == "") { echo " selected"; } ?>><?php echo $lang["all"]; ?></option>
			<option value="0"<?php if ($restypefilter == "0") { echo " selected"; } ?>><?php echo $lang["resourcetype-global_field"]; ?></option>
			
			<?php
			  for($n=0;$n<count($resource_types);$n++){
			?>
			<option value="<?php echo $resource_types[$n]["ref"]; ?>"<?php if ($restypefilter == $resource_types[$n]["ref"]) { echo " selected"; } ?>><?php echo i18n_get_translated($resource_types[$n]["name"]); ?></option>
			<?php
			  }
			?>
			
			<option value="999"<?php if ($restypefilter == "999") { echo " selected"; } ?>><?php echo $lang["resourcetype-archive_only"]; ?></option>
			</select>
		  </div>
		</div>
		<div class="clearerleft"> </div>
	  </div>
</form>
	
	
<div class="Listview">
<table id="resource_type_field_table" border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
<tr class="ListviewTitleStyle">
<?php  

addColumnHeader('ref', 'property-reference');
addColumnHeader('title', 'property-title');
addColumnHeader('resource_type', 'property-resource_type');
addColumnHeader('name', 'property-shorthand_name');
addColumnHeader('type', 'property-field_type');
addColumnHeader('tab_name', 'property-tab_name');
?>
<td><div class="ListTools"><?php echo $lang["tools"]?></div></td>
</tr>

<tbody id="resource_type_field_table_body">
<?php


for ($n=0;$n<count($fields);$n++)
	{
	?>
	<tr class="resource_type_field_row" id="field_sort_<?php echo $fields[$n]["ref"];?>">
		<td>
			<?php echo str_highlight ($fields[$n]["ref"],$find,STR_HIGHLIGHT_SIMPLE);?>
		</td>	
		<td>
			<div class="ListTitle">
			      <a href="<?php echo $baseurl . "/pages/admin/admin_resource_type_field_edit.php?ref=" . $fields[$n]["ref"] . "&restype=" . $restypefilter . "&field_order_by=" . $field_order_by . "&field_sort=" . $field_sort . "&find=" . urlencode($find) . "&backurl=" . urlencode($url) ?>" onClick="jQuery('#resource_type_field_table_body').sortable('cancel');return CentralSpaceLoad(this,true);"><span><?php echo str_highlight (i18n_get_translated($fields[$n]["title"]),$find,STR_HIGHLIGHT_SIMPLE);?></span></a>
				
				
			</div>
		</td>
		<td>		
			<?php if(isset($arr_restypes[$fields[$n]["resource_type"]])){echo i18n_get_translated($arr_restypes[$fields[$n]["resource_type"]]);} else {echo $fields[$n]["resource_type"];}?>
		</td>	
		<td>		
			<?php echo str_highlight($fields[$n]["name"],$find,STR_HIGHLIGHT_SIMPLE);?>
		</td>	
		<td>		
			<?php echo ($fields[$n]["type"]!="")?$lang[$field_types[$fields[$n]["type"]]]:$lang[$field_types[0]];  // if no value it is treated as type 0 (single line text) ?>
		</td>
		<td>		
			<?php echo str_highlight(i18n_get_translated($fields[$n]["tab_name"]),$find,STR_HIGHLIGHT_SIMPLE);?>
		</td>
		
		<td>
			<div class="ListTools">
			  
			  <?php 
			if($field_order_by=="order_by")
				{
				?>		
				<a href="javascript:void(0)" class="movelink movedownlink" <?php if($n==count($fields)-1){ ?> disabled <?php } ?>>&gt;&nbsp;<?php echo $lang['action-move-down'] ?></a>
				<a href="javascript:void(0)" class="movelink moveuplink" <?php if($n==0){ ?> disabled <?php } ?>>&gt;&nbsp;<?php echo $lang['action-move-up'] ?></a>
				<?php
				}
				?>
			
			
				<a href="<?php echo $baseurl . "/pages/admin/admin_copy_field.php?ref=" . $fields[$n]["ref"] . "&backurl=" . $url?>" onClick="CentralSpaceLoad(this,true)" >&gt;&nbsp;<?php echo $lang["copy"] ?></a>
				<a href="<?php echo $baseurl . "/pages/admin/admin_resource_type_field_edit.php?ref=" . $fields[$n]["ref"] . "&backurl=" . $url?>" onClick="jQuery('#resource_type_field_table_body').sortable('cancel');return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["action-edit"]?> </a>
				<a href="<?php echo $baseurl . "/pages/admin/admin_resource_type_field_edit.php?ref=" . $fields[$n]["ref"] . "&delete=yes&backurl=" . $url?>" onClick="if(confirm('<?php echo $lang["confirm-deletion"] ?>')){CentralSpaceLoad(this,true);return false;}else{return false;}" >&gt;&nbsp;<?php echo $lang["action-delete"] ?></a>
				 
			</div>
		</td>
	</tr>
	<?php
	}
?>
</tbody>
</table>
</div>


<form method="post" id="AdminResourceTypeFieldForm2" onSubmit="return CentralSpacePost(this,true);"  action="<?php echo generateURL($baseurl . "/pages/admin/admin_resource_type_fields.php",array("field_order_by"=>$field_order_by,"field_sort"=>$field_sort,"restypefilter"=>$restypefilter)) ?>" >
		
		
	<div class="Question">
			<label for="find"><?php echo $lang["find"]?></label>
			<div class="tickset">
			 <div class="Inline"><input type=text name="find" id="find" value="<?php echo $find?>" maxlength="100" class="shrtwidth" /></div>
			 <div class="Inline"><input name="Submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["searchbutton"]?>&nbsp;&nbsp;" /></div>
			<?php
			if ($find!="")
			    {
			    ?>
			    <div class="Inline"><input name="resetform" class="resetform" type="submit" value="<?php echo $lang["clearbutton"]?>" onclick="CentralSpaceLoad('<?php echo generateURL($baseurl . "/pages/admin/admin_resource_type_fields.php",array("field_order_by"=>$field_order_by,"field_sort"=>$field_sort,"restypefilter"=>$restypefilter,"find"=>"")) ?>',false);return false;" /></div>
			    <?php
			    }
			?>
			</div>
			<div class="clearerleft"> </div>
		</div>
	
		<div class="Question">
			<label for="newfield"><?php echo $lang["admin_resource_type_field_create"]?></label>
			<div class="tickset">
			 <input type="hidden" name="newfieldrestype" value="<?php echo htmlspecialchars($restypefilter) ?>""/>   
			 <div class="Inline"><input type=text name="newfield" id="newtype" maxlength="100" class="shrtwidth" /></div>
			 <div class="Inline"><input name="Submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["create"] ?>&nbsp;&nbsp;" /></div>
			</div>
			<div class="clearerleft"> </div>
		</div>
	</form>


 
</div><!-- End of BasicsBox -->

  

<script>
  
function ReorderResourceTypeFields(idsInOrder)
	{
	//alert(idsInOrder);
	var newOrder = [];
	jQuery.each(idsInOrder, function() {
		newOrder.push(this.substring(11));
		}); 
	
	jQuery.ajax({
	  type: 'POST',
	  url: '<?php echo $baseurl_short?>pages/admin/ajax/update_resource_type_field_order.php?restypefilter=<?php echo $restypefilter ?>&reorder=true',
	  data: {order:JSON.stringify(newOrder)},
	  success: function() {
		
		//jQuery('.movelink').show();
	  	jQuery('.movedownlink:last').attr( "disabled", "true" );
		jQuery('.moveuplink:first').attr( "disabled", "true" );
	  	jQuery('.movedownlink:not(:last)').removeAttr( "disabled" )
		jQuery('.moveuplink:not(:first)').removeAttr( "disabled")
		//$( "input:not(:checked) + span" )
		//alert("SUCCESS");
		//var results = new RegExp('[\\?&amp;]' + 'search' + '=([^&amp;#]*)').exec(window.location.href);
		//var ref = new RegExp('[\\?&amp;]' + 'ref' + '=([^&amp;#]*)').exec(window.location.href);
		//if ((ref==null)&&(results!== null)&&('<?php echo urlencode("!collection" . $usercollection); ?>' === results[1])) CentralSpaceLoad('<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $usercollection); ?>',true);
	     }
	  });		
	}

function enableFieldsort(){
	var fixHelperModified = function(e, tr) {
		  var $originals = tr.children();
		  var $helper = tr.clone();
		  $helper.children().each(function(index)
		  {
			jQuery(this).width($originals.eq(index).width())
		  });
		  return $helper;
	  };

	  //jQuery('.resource_type_field_row').draggable({ axis: "y" });
	  //jQuery('.resource_type_field_row').droppable();
	  
	  jQuery('#resource_type_field_table_body').sortable({
			  items: "tr",
			  axis: "y",
			  cursor: 'move',
			  opacity: 0.6, 
			  distance: 5,
			  stop: function(event, ui) {
				  <?php
				  if($allow_reorder)
					{
					?>
					var idsInOrder = jQuery('#resource_type_field_table_body').sortable("toArray");
					//alert(idsInOrder);
					ReorderResourceTypeFields(idsInOrder);
					<?php
					}
				else
					{
					if($use_order_by_tab_view && $restypefilter!="")
						{
						$errormessage=$lang["admin_resource_type_field_reorder_information_tab_order"];
						}
					else if (!$use_order_by_tab_view && $restypefilter=="" && $field_order_by=="order_by" )
						{
						$errormessage=$lang["admin_resource_type_field_reorder_select_restype"];
						?>
						hideinfo=true;
						<?php						
						}
					else
						{
						$errormessage=$lang["admin_resource_type_field_reorder_information_normal_order"];
						} 
					?>
					
					jQuery('#PageError').html("<?php echo $errormessage ?>");
					jQuery('#PageError').show();
					if (hideinfo!==undefined)
					    {
					    jQuery('#PageInfo').hide();					   
					    }

					jQuery( "#resource_type_field_table_body" ).sortable( "cancel" );
					<?php
					}
					?>

				  
				  },
			  helper: fixHelperModified
			 
			}).disableSelection();
	}
	
enableFieldsort();

jQuery(".moveuplink").click(function() {
    if (jQuery(this).attr('disabled')) {
	      e.preventDefault();
	      e.stopImmediatePropagation();
	  }
      curvalue=parseInt(jQuery(this).parents(".resource_type_field_row").children('.order_by_value').html());
      parentvalue=parseInt(jQuery(this).parents(".resource_type_field_row").prev().children('.order_by_value').html());
      jQuery(this).parents(".resource_type_field_row").children('.order_by_value').html(curvalue-10);
      jQuery(this).parents(".resource_type_field_row").prev().children('.order_by_value').html(parentvalue+10);
      jQuery(this).parents(".resource_type_field_row").insertBefore(jQuery(this).parents(".resource_type_field_row").prev());
      var idsInOrder = jQuery('#resource_type_field_table_body').sortable("toArray");
      ReorderResourceTypeFields(idsInOrder);
		
    });
   
jQuery(".movedownlink").click(function() {
   if (jQuery(this).attr('disabled')) {
	      e.preventDefault();
	      e.stopImmediatePropagation();
	  }
      curvalue=parseInt(jQuery(this).parents(".resource_type_field_row").children('.order_by_value').html());
      childvalue=parseInt(jQuery(this).parents(".resource_type_field_row").next().children('.order_by_value').html());
      jQuery(this).parents(".resource_type_field_row").children('.order_by_value').html(curvalue+10);
      jQuery(this).parents(".resource_type_field_row").next().children('.order_by_value').html(childvalue-10);
      jQuery(this).parents(".resource_type_field_row").insertAfter(jQuery(this).parents(".resource_type_field_row").next());
      var idsInOrder = jQuery('#resource_type_field_table_body').sortable("toArray");
      ReorderResourceTypeFields(idsInOrder);
    });
	
</script>
	
<?php
include "../../include/footer.php";
?>
