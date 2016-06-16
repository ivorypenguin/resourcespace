<?php

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";

if (!checkperm("a"))
	{
	exit ("Permission denied.");
	}


$restype_order_by=getvalescaped("restype_order_by","rt");
$restype_sort=getvalescaped("restype_sort","asc");

if(!in_array($restype_order_by,array("rt","name","tab_name","order_by","fieldcount"))){$restype_order_by="rt";}

$url_params = array("restype_order_by"=>$restype_order_by,"restype_sort"=>$restype_sort);
$url=generateURL($baseurl . "/pages/admin/admin_resource_types.php",$url_params);


$backurl=getvalescaped("backurl","");
if($backurl=="")
    {
    $backurl=$baseurl . "/pages/admin/admin_home.php";
    }
    
if (getval("newtype","")!="")
	{
	sql_query("insert into resource_type (name) values ('" . getvalescaped("newtype","") . "')");
	$new=sql_insert_id();
	redirect($baseurl_short."pages/admin/admin_resource_type_edit.php?ref=" . $new);
	}

$resource_types=sql_query ("
	select * from 
		(
		select if(rt is null, rt.ref, rt) rt,
		name,
		if(rt=0,'0',if(rt=999,'999999999',order_by)) order_by,
		config_options,
		allowed_extensions,
		tab_name,
		fieldcount
        from
		resource_type rt
	left join 
	      (select ref, resource_type rt, count(*) fieldcount from resource_type_field group by resource_type) f 
	on rt.ref=f.rt
	
	union 
	
	select 
		rt,
		if (rt=0,'" . $lang["resourcetype-global_field"] . "',if(rt=999,'" . $lang["resourcetype-archive_only"] . "',name)) name,
		if(rt=0,'0',if(rt=999,'999999999',order_by)) order_by,
		config_options,
		allowed_extensions,
		tab_name,
		fieldcount
        from
		resource_type rt
	right join 
	      (select resource_type rt, count(*) fieldcount from resource_type_field group by resource_type) f 
	on rt.ref=f.rt
	)
	
	restypes
	
	order by $restype_order_by
	$restype_sort
	"
);

include "../../include/header.php";


function addColumnHeader($orderName, $labelKey)
    {
	global $baseurl, $url, $group, $restype_order_by, $restype_sort, $find, $lang;

	if ($restype_order_by == $orderName && $restype_sort=="asc")
		$arrow = '<span class="DESC"></span>';
	else if ($restype_order_by == $orderName && $restype_sort=="desc")
		$arrow = '<span class="ASC"></span>';
	else
		$arrow = '';

	?><td><a href="<?php echo $baseurl ?>/pages/admin/admin_resource_types.php?restype_order_by=<?php echo $orderName ?>&restype_sort=<?php echo ($restype_sort=="asc") ? 'desc' : 'asc';
			?>&find=<?php echo urlencode($find)?>&backurl=<?php echo urlencode($url) ?>" onClick="return CentralSpaceLoad(this);"><?php
			echo $lang[$labelKey] . $arrow ?></a></td>
	
      <?php

      }
?>	

<div class="BasicsBox">
   
  <h1><?php echo $lang["treenode-resource_types_and_fields"]?></h1>
  
  <?php
  $introtext=text("introtext");
  if($introtext!=""){ echo "<p>" . text("introtext") . "</p>";}
  
$allow_reorder=false;
// Allow sorting if we are ordering a single resource type, or if $use_order_by_tab_view is true (which means order_by values are across all resource types) and we can see all fields
if($restype_order_by=="order_by"){$allow_reorder=true;}

if(!$allow_reorder)
  {?>
  <a href="<?php echo $baseurl . "/pages/admin/admin_resource_types.php?restype_order_by=order_by&restype_sort=asc" ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET ?><?php echo $lang["admin_resource_type_reorder_mode"]?></a></p>  
  <?php
  }
  ?>

<div class="FormError" id="PageError"
  <?php
  if (!isset($error_text)) { ?> style="display:none;"> <?php }
  else { echo ">" . $error_text ; } ?>
</div>

<div class="Listview ListviewTight">
<table border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
<tr class="ListviewTitleStyle">

<?php
addColumnHeader('rt', 'property-reference');
addColumnHeader('name', 'property-name');
addColumnHeader('fieldcount', 'admin_resource_type_field_count');
?>

<td><div class="ListTools"><?php echo $lang["tools"]?></div></td>
</tr>
<tbody id="resource_type_table_body">
<?php



for ($n=0;$n<count($resource_types);$n++)
	{
	?>
	<tr <?php if (!in_array($resource_types[$n]["rt"],array(0,999))){?> class="resource_type_row" id="restype_sort_<?php echo $resource_types[$n]["rt"];?>" <?php }?> >
		<td>
			<?php echo $resource_types[$n]["rt"];?>
		</td>	
		<td>
			<div class="ListTitle">
				<?php
				if($resource_types[$n]["name"]!="" && !in_array($resource_types[$n]["rt"],array(0,999)))
				    {
				    ?>
				    <a href="<?php echo $baseurl_short?>pages/admin/admin_resource_type_edit.php?ref=<?php echo $resource_types[$n]["rt"]?>&backurl=<?php echo urlencode($url) ?>" onClick="return CentralSpaceLoad(this,true);">
				    <?php echo i18n_get_translated($resource_types[$n]["name"]);?>
				    </a>
				    <?php
				    }
				elseif ($resource_types[$n]["rt"]==999)
				    {
				    echo $lang["resourcetype-archive_only"];
				    }
				elseif ($resource_types[$n]["rt"]==0)
				    {
				    echo $lang["resourcetype-global_field"];
				    }?>			    
				
				</a>
			</div>
		</td>
		<td>
			<div class="ListTitle">
				<?php
				if($resource_types[$n]["fieldcount"]!="")
				    {
				    ?>
				    <a href="<?php echo $baseurl_short?>pages/admin/admin_resource_type_fields.php?restypefilter=<?php echo $resource_types[$n]["rt"] . "&backurl=" . urlencode($url) ?>" onClick="return CentralSpaceLoad(this,true);">
				    <?php echo $resource_types[$n]["fieldcount"] ?>
				    </a>
				    <?php
				    }
				else
				    {
				    echo "0";  
				    }?>
			</div>
		</td>
		
		<td>
			<div class="ListTools">
				<?php 
				if($restype_order_by=="order_by" && !in_array($resource_types[$n]["rt"],array(0,999)))
				     {
				     ?>		
				     <a href="javascript:void(0)" class="movelink movedownlink" <?php if($n==count($resource_types)-1){ ?> disabled <?php } ?>><?php echo LINK_CARET ?>Move down</a>
				     <a href="javascript:void(0)" class="movelink moveuplink" <?php if($n==0){ ?> disabled <?php } ?>><?php echo LINK_CARET ?>Move up	</a>
				     <?php
				     }
				    ?>
				<?php
				if(!in_array($resource_types[$n]["rt"],array(0,999)))
				  {
				  ?>
				  <a href="<?php echo $baseurl ?>/pages/admin/admin_resource_type_edit.php?ref=<?php echo $resource_types[$n]["rt"]?>&backurl=<?php echo urlencode($url) ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET ?><?php echo $lang["action-edit"]?> </a>
				  <?php
				  }
				  ?>
				  <a href="<?php echo $baseurl ?>/pages/admin/admin_resource_type_fields.php?restypefilter=<?php echo $resource_types[$n]["rt"] . "&backurl=" . urlencode($url) ?>" onClick="return CentralSpaceLoad(this,true);"><?php echo LINK_CARET ?><?php echo $lang["metadatafields"]?> </a>
				
			</div>
		</td>
	</tr>
	<?php
	}
?>
</tbody>
</table>
</div>
</div>




<div class="BasicsBox">
    <form method="post" action="<?php echo $baseurl_short?>pages/admin/admin_resource_types.php"  onSubmit="return CentralSpacePost(this,true);" >
		<div class="Question">
			<label for="newtype"><?php echo $lang["admin_resource_type_create"]?></label>
			<div class="tickset">
			 <div class="Inline"><input type=text name="newtype" id="newtype" maxlength="100" class="shrtwidth" /></div>
			 <div class="Inline"><input name="Submit" type="submit" value="&nbsp;&nbsp;<?php echo $lang["create"]?>&nbsp;&nbsp;" /></div>
			</div>
			<div class="clearerleft"> </div>
		</div>
		<input type="hidden" name="save" id="save" value="yes"/>
	</form>
</div>

<script>
  
function ReorderResourceTypes(idsInOrder)
	{
	//alert(idsInOrder);
	var newOrder = [];
	jQuery.each(idsInOrder, function() {
		newOrder.push(this.substring(13));
		}); 
	
	jQuery.ajax({
	  type: 'POST',
	  url: '<?php echo $baseurl_short?>pages/admin/ajax/update_resource_type_order.php?reorder=true',
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

function enableRestypesort(){
	var fixHelperModified = function(e, tr) {
		  var $originals = tr.children();
		  var $helper = tr.clone();
		  $helper.children().each(function(index)
		  {
			jQuery(this).width($originals.eq(index).width())
		  });
		  return $helper;
	  };

	  //jQuery('.resource_type_row').draggable({ axis: "y" });
	  //jQuery('.resource_type_row').droppable();
	  
	  jQuery('#resource_type_table_body').sortable({
			  items: ".resource_type_row",
			  axis: "y",
			  cursor: 'move',
			  opacity: 0.6, 
			  stop: function(event, ui) {
			  		//alert("HERE");
				  <?php
				  if($allow_reorder)
					{
					?>
					var idsInOrder = jQuery('#resource_type_table_body').sortable("toArray");
					//alert(idsInOrder);
					ReorderResourceTypes(idsInOrder);
					<?php
					}
				else
					{
					$errormessage=$lang["admin_resource_type_reorder_information_tab_order"];
					?>
					jQuery('#PageError').html("<?php echo $errormessage ?>").show();
					jQuery( "#resource_type_table_body" ).sortable( "cancel" );
					<?php
					}
					?>

				  
				  },
			  helper: fixHelperModified
			 
			}).disableSelection();
	}
	
enableRestypesort();

jQuery(".moveuplink").click(function(e) {
    if (jQuery(this).attr('disabled')) {
	      e.preventDefault();
	      e.stopImmediatePropagation();
	  }
      jQuery(this).parents(".resource_type_row").insertBefore(jQuery(this).parents(".resource_type_row").prev());
      var idsInOrder = jQuery('#resource_type_table_body').sortable("toArray");
      ReorderResourceTypes(idsInOrder);
		
    });
   
jQuery(".movedownlink").click(function(e) {
   if (jQuery(this).attr('disabled')) {
	      e.preventDefault();
	      e.stopImmediatePropagation();
	  }
      jQuery(this).parents(".resource_type_row").insertAfter(jQuery(this).parents(".resource_type_row").next());
      var idsInOrder = jQuery('#resource_type_table_body').sortable("toArray");
      ReorderResourceTypes(idsInOrder);
    });
	
</script>

<?php
include "../../include/footer.php";
?>
