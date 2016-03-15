<?php
include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php"; 
include "../include/resource_functions.php";
include_once "../include/collections_functions.php";
include "../include/search_functions.php";
include "../include/image_processing.php";
include_once '../include/node_functions.php';

# Editing resource or collection of resources (multiple)?
$ref=getvalescaped("ref","",true);

# Fetch search details (for next/back browsing and forwarding of search params)
$search=getvalescaped("search","");
$order_by=getvalescaped("order_by","relevance");
$offset=getvalescaped("offset",0,true);
$restypes=getvalescaped("restypes","");
if (strpos($search,"!")!==false) {$restypes="";}
$default_sort="DESC";
if (substr($order_by,0,5)=="field"){$default_sort="ASC";}
$sort=getval("sort",$default_sort);
$modal=(getval("modal","")=="true");

$archive=getvalescaped("archive",0,true); // This is the archive state for searching, NOT the archive state to be set from the form POST which we get later
  
$uploadparams="";
$uploadparams.="&relateto=" . urlencode(getval("relateto",""));
$uploadparams.="&redirecturl=" . urlencode(getval("redirecturl",""));

$collection     = getvalescaped('collection', '', true);
$collection_add = getvalescaped('collection_add', '');

global $merge_filename_with_title;
if($merge_filename_with_title && $ref < 0) {

  $merge_filename_with_title_option = urlencode(getval('merge_filename_with_title_option', ''));
  $merge_filename_with_title_include_extensions = urlencode(getval('merge_filename_with_title_include_extensions', ''));
  $merge_filename_with_title_spacer = urlencode(getval('merge_filename_with_title_spacer', ''));

  if($merge_filename_with_title_option != '') {
    $uploadparams .= '&merge_filename_with_title_option=' . $merge_filename_with_title_option;
 }

 if($merge_filename_with_title_include_extensions != '') {
    $uploadparams .= '&merge_filename_with_title_include_extensions=' . $merge_filename_with_title_include_extensions;
 }

 if($merge_filename_with_title_spacer != '') {
    $uploadparams .= '&merge_filename_with_title_spacer=' . $merge_filename_with_title_spacer;
 }

}

global $tabs_on_edit;
$collapsible_sections=true;
if($tabs_on_edit){$collapsible_sections=false;}

$errors=array(); # The results of the save operation (e.g. required field messages)

# Disable auto save for upload forms - it's not appropriate.
if ($ref<0) { $edit_autosave=false; }

# next / previous resource browsing
$go=getval("go","");
if ($go!="")
{
    # Re-run the search and locate the next and previous records.
  $modified_result_set=hook("modifypagingresult"); 
  if ($modified_result_set){
    $result=$modified_result_set;
 } else {    
    $result=do_search($search,$restypes,$order_by,$archive,240+$offset+1,$sort);
 }
 if (is_array($result))
 {
        # Locate this resource
    $pos=-1;
    for ($n=0;$n<count($result);$n++)
    {
      if ($result[$n]["ref"]==$ref) {$pos=$n;}
   }
   if ($pos!=-1)
   {
      if (($go=="previous") && ($pos>0)) {$ref=$result[$pos-1]["ref"];}
            if (($go=="next") && ($pos<($n-1))) {$ref=$result[$pos+1]["ref"];if (($pos+1)>=($offset+72)) {$offset=$pos+1;}} # move to next page if we've advanced far enough
         }
         else
         {
            ?>
            <script type="text/javascript">
            alert("<?php echo $lang["resourcenotinresults"] ?>");
            </script>
            <?php
         }
      }
   }

   $collection=getvalescaped("collection","",true);
   if ($collection!="") 
   {
    # If editing multiple items, use the first resource as the template
     $multiple=true;
    $edit_autosave=false; # Do not allow auto saving for batch editing.
    $items=get_collection_resources($collection);
    if (count($items)==0) {
       $error=$lang['error-cannoteditemptycollection'];
       error_alert($error);
       exit();
    }
    
    # check editability
    if (!allow_multi_edit($collection)){
       $error=$lang['error-permissiondenied'];
       error_alert($error);
       exit();
    }
    $ref=$items[0];
 }
 else
 {
  $multiple=false;
}

# Fetch resource data.
$resource=get_resource_data($ref);

# Allow to specify resource type from url for new resources
$resource_type=getval("resource_type","");
if ($ref<0 && $resource_type!="" && $resource_type!=$resource["resource_type"] && !checkperm("XU{$resource_type}"))     // only if new resource specified and user has permission for that resource type
  {
  update_resource_type($ref,$resource_type);
  $resource["resource_type"]=$resource_type;
  }

$setarchivestate = getvalescaped('status', $resource["archive"], TRUE);

# Allow alternative configuration settings for this resource type.
resource_type_config_override($resource["resource_type"]);

# If upload template, check if the user has upload permission.
if ($ref<0 && !(checkperm("c") || checkperm("d")))
{
  $error=$lang['error-permissiondenied'];
  error_alert($error);
  exit();
}

# Check edit permission.
if (!get_edit_access($ref,$resource["archive"],false,$resource))
{
    # The user is not allowed to edit this resource or the resource doesn't exist.
  $error=$lang['error-permissiondenied'];
  error_alert($error,!$modal);
  exit();
}

if (getval("regen","")!="")
{
  sql_query("update resource set preview_attempts=0 WHERE ref='" . $ref . "'");
  create_previews($ref,false,$resource["file_extension"]);
}

if (getval("regenexif","")!="")
{
  extract_exif_comment($ref);
}

# Establish if this is a metadata template resource, so we can switch off certain unnecessary features
$is_template=(isset($metadata_template_resource_type) && $resource["resource_type"]==$metadata_template_resource_type);

# check for upload disabled due to space limitations...
if ($ref<0 && isset($disk_quota_limit_size_warning_noupload))
  {
	# check free space
	if (isset($disksize)){ # Use disk quota rather than real disk size
		$avail=$disksize*(1024*1024*1024);
		$used=get_total_disk_usage();
		$free=$avail-$used;
	}
	else{		
		$avail=disk_total_space($storagedir);
		$free=disk_free_space($storagedir);
		$used=$avail-$free;
	}
	
	# echo "free: ".$free."<br/>";
	# convert limit
	$limit=$disk_quota_limit_size_warning_noupload*1024*1024*1024;
	# echo "no_upload: ".$limit."<br/>";
	# compare against size setting
	if($free<=$limit){
		# shut down uploading by redirecting to explanation page
		$explain=$baseurl_short."pages/no_uploads.php";
		redirect($explain);
	}
  }

hook("editbeforeheader");

# -----------------------------------
#           PERFORM SAVE
# -----------------------------------

if ((getval("autosave","")!="") || (getval("tweak","")=="" && getval("submitted","")!="" && getval("resetform","")=="" && getval("copyfrom","")==""))
  {
  if(($embedded_data_user_select && getval("exif_option","")=="custom") || isset($embedded_data_user_select_fields))  
    {
    $exif_override=false;
    foreach($_POST as $postname=>$postvar)
      {
      if (strpos($postname,"exif_option_")!==false)
        {
        $uploadparams.="&" . urlencode($postname) . "=" . urlencode($postvar);
        $exif_override=true;
        }
      }
    if($exif_override)
      {
      $uploadparams.="&exif_override=true";
      }
    }

  hook("editbeforesave");         
  
  # save data
  if (!$multiple)
      {
      # When auto saving, pass forward the field so only this is saved.
      $autosave_field=getvalescaped("autosave_field","");
         
      # Upload template: Change resource type
      $resource_type=getvalescaped("resource_type","");
      if ($resource_type!="" && !checkperm("XU{$resource_type}") && $autosave_field=="")     // only if resource specified and user has permission for that resource type
        {
        update_resource_type($ref,$resource_type);
        $resource=get_resource_data($ref,false); # Reload resource data.
        }       
         
       # Perform the save
       $save_errors=save_resource_data($ref,$multiple,$autosave_field);
         
       if($embedded_data_user_select)
         {
         $no_exif=getval("exif_option","");
         }
       else
         {
         $no_exif=getval("no_exif","");
         }

       if($relate_on_upload && $enable_related_resources && getval("relateonupload","")!="")
          {
          $uploadparams.="&relateonupload=yes";
          }
        $autorotate = getval("autorotate","");

        if ($upload_collection_name_required)
            {
            if (getvalescaped("entercolname","")=="" && getval("collection_add","")==-1)
              { 
              if (!is_array($save_errors)){$save_errors=array();} 
              $save_errors['collectionname']=$lang["requiredfield"];
              }
           }       

        if (($save_errors===true || $is_template)&&(getval("tweak","")==""))
          {           
          if ($ref>0 && getval("save","")!="")
            {
            # Log this
            daily_stat("Resource edit",$ref);
            if (!hook('redirectaftersave') && !$modal)
              {
              redirect($baseurl_short."pages/view.php?ref=" . urlencode($ref) . "&search=" . urlencode($search) . "&offset=" . urlencode($offset) . "&order_by=" . urlencode($order_by) . "&sort=" . urlencode($sort) . "&archive=" . urlencode($archive) . "&refreshcollectionframe=true");
              }
            }
          else
            {
            if ((getval("uploader","")!="")&&(getval("uploader","")!="local"))
              {
              # Save button pressed? Move to next step.
              if (getval("save","")!="") {redirect($baseurl_short."pages/upload_" . getval("uploader","") . ".php?collection_add=" . getval("collection_add","")."&entercolname=".urlencode(getvalescaped("entercolname",""))."&resource_type=" . urlencode($resource_type) . "&status=" . $setarchivestate .  "&no_exif=" . urlencode($no_exif) . "&autorotate=" . urlencode($autorotate) . "&themestring=" . urlencode(getval('themestring','')) . "&public=" . urlencode(getval('public','')) . "&archive=" . urlencode($archive) . $uploadparams . hook("addtouploadurl"));}
              }
            elseif ((getval("local","")!="")||(getval("uploader","")=="local")) // Test if fetching resource from local upload folder.
              {
              # Save button pressed? Move to next step.
              if (getval("save","")!="") {redirect($baseurl_short."pages/team/team_batch_select.php?use_local=yes&collection_add=" . getval("collection_add","")."&entercolname=".urlencode(getvalescaped("entercolname",""))."&resource_type=". urlencode($resource_type) . "&status=" . $setarchivestate .  "&no_exif=" . $no_exif . "&autorotate=" . $autorotate . $uploadparams );}
              }
            elseif (getval("single","")!="") // Test if single upload (archived or not).
              {
              # Save button pressed? Move to next step. if noupload is set - create resource without uploading stage
              if ((getval("noupload","")!="")&&(getval("save","")!="")) {$ref=copy_resource(0-$userref);redirect($baseurl_short."pages/view.php?ref=". urlencode($ref));}
  
              if (getval("save","")!="") {redirect($baseurl_short."pages/upload.php?resource_type=". urlencode($resource_type) . "&status=" . $setarchivestate .  "&no_exif=" . $no_exif . "&autorotate=" . urlencode($autorotate) . "&archive=" . urlencode($archive) . $uploadparams );}
              }    
            else // Hence fetching from ftp.
              {
              # Save button pressed? Move to next step.
              if (getval("save","")!="") {redirect($baseurl_short."pages/team/team_batch.php?collection_add=" . getval("collection_add","")."&entercolname=".urlencode(getvalescaped("entercolname","")). "&resource_type=". urlencode($resource_type) . "&status=" . $setarchivestate .  "&no_exif=" . $no_exif . "&autorotate=" . urlencode($autorotate) . $uploadparams );}
              }
            }
          }
        elseif (getval("save","")!="")
          {           
          $show_error=true;
          }
      }
    else
      {
      # Save multiple resources
      $save_errors=save_resource_data_multi($collection);
      if(!is_array($save_errors) && !hook("redirectaftermultisave"))
        {
        redirect($baseurl_short."pages/search.php?refreshcollectionframe=true&search=!collection" . $collection);
        }      
      $show_error=true;
      }
  }

# If auto-saving, no need to continue as it will only add to bandwidth usage to send the whole edit page back to the client. Send a simple 'SAVED' message instead.
if (getval("autosave","")!="") {exit("SAVED");}


if (getval("tweak","")!="")
   {
   $tweak=getval("tweak","");
   switch($tweak)
      {
      case "rotateclock":
         tweak_preview_images($ref,270,0,$resource["preview_extension"]);
         break;
      case "rotateanti":
         tweak_preview_images($ref,90,0,$resource["preview_extension"]);
         break;
      case "gammaplus":
         tweak_preview_images($ref,0,1.3,$resource["preview_extension"]);
         break;
      case "gammaminus":
         tweak_preview_images($ref,0,0.7,$resource["preview_extension"]);
         break;
      case "restore":
         sql_query("update resource set preview_attempts=0 WHERE ref='" . $ref . "'");
         if ($enable_thumbnail_creation_on_upload)
            {
            create_previews($ref,false,$resource["file_extension"],false,false,-1,true);
            refresh_collection_frame();
            }
         else
            {
            sql_query("update resource set preview_attempts=0, has_image=0 where ref='$ref'");
            }
         break;
      }
   hook("moretweakingaction", "", array($tweak, $ref, $resource));
   # Reload resource data.
   $resource=get_resource_data($ref,false);
   }

# Simulate reupload (preserving filename and thumbs, but otherwise resetting metadata).
if (getval("exif","")!="")
   {
   upload_file($ref,$no_exif=false,true);
   resource_log($ref,"r","");
   }   

# If requested, refresh the collection frame (for redirects from saves)
if (getval("refreshcollectionframe","")!="")
   {
   refresh_collection_frame();
   }

include "../include/header.php";
?>


<script type="text/javascript">

registerCollapsibleSections();

jQuery(document).ready(function()
{
   <?php
   if($ctrls_to_save)
     {?>
        jQuery(document).bind('keydown',function (e)
        {
          if (!(e.which == 115 && (e.ctrlKey || e.metaKey)) && !(e.which == 83 && (e.ctrlKey || e.metaKey)) && !(e.which == 19) )
          {
            return true;
         }
         else
         {
            e.preventDefault();
            if(jQuery('#mainform'))
            {
               jQuery('.AutoSaveStatus').html('<?php echo $lang["saving"] ?>');
               jQuery('.AutoSaveStatus').show();
               jQuery.post(jQuery('#mainform').attr('action') + '&autosave=true',jQuery('#mainform').serialize(),

                  function(data)
                  {
                    if (data.trim()=="SAVED")
                    {
                     jQuery('.AutoSaveStatus').html('<?php echo $lang["saved"] ?>');
                     jQuery('.AutoSaveStatus').fadeOut('slow');
                  }
                  else
                  {
                     jQuery('.AutoSaveStatus').html('<?php echo $lang["save-error"] ?>' + data);
                  }
               });
            }
            return false;
         }
      });
<?php
}?>

});
<?php hook("editadditionaljs") ?>

function ShowHelp(field)
{
    // Show the help box if available.
    if (document.getElementById('help_' + field))
    {
       jQuery('#help_' + field).fadeIn();
    }
 }
 function HideHelp(field)
 {
    // Hide the help box if available.
    if (document.getElementById('help_' + field))
    {
       document.getElementById('help_' + field).style.display='none';
    }
 }

 jQuery(document).ready(function() {
    jQuery('#collection_add').change(function (){
      if(jQuery('#collection_add').val()==-1){
        jQuery('#collectioninfo').fadeIn();
     } 
     else {
        jQuery('#collectioninfo').fadeOut();
     }
  });
    jQuery('#collection_add').change();
 }); 

 <?php
# Function to automatically save the form on field changes, if configured.
 if ($edit_autosave) { ?>
    preventautosave=false;

// Disable autosave on enter keypress as form will be submitted by this keypress anyway which can result in duplicate data
jQuery(document).bind('keydown',function (e)
{               
  if (e.which == 13)
  {
    preventautosave=true;
 }
 else
 {       
    preventautosave=false;  
 }
})

function AutoSave(field)
{
  if (preventautosave) {   
    return false;
 }   
 jQuery('#AutoSaveStatus' + field).html('<?php echo $lang["saving"] ?>');
 jQuery('#AutoSaveStatus' + field).show();


 jQuery.post(jQuery('#mainform').attr('action') + '&autosave=true&autosave_field=' + field,jQuery('#mainform').serialize(),

  function(data)
  {
    if (data.trim()=="SAVED")
    {
     jQuery('#AutoSaveStatus' + field).html('<?php echo $lang["saved"] ?>');
     jQuery('#AutoSaveStatus' + field).fadeOut('slow');
  }
  else
  {
     jQuery('#AutoSaveStatus' + field).html('<?php echo $lang["save-error"] ?>' + data);             
  }
});
}
<?php } 

# Resource next / back browsing.
function EditNav() # Create a function so this can be repeated at the end of the form also.
{
  global $baseurl_short,$ref,$search,$offset,$order_by,$sort,$archive,$lang,$modal,$restypes;
  ?>
  <div class="backtoresults">
  <a class="prevLink" onClick="return <?php echo ($modal?"Modal":"CentralSpace") ?>Load(this,true);" href="<?php echo $baseurl_short?>pages/edit.php?ref=<?php echo urlencode($ref) ?>&amp;search=<?php echo urlencode($search)?>&amp;offset=<?php echo urlencode($offset) ?>&amp;order_by=<?php echo urlencode($order_by) ?>&amp;sort=<?php echo urlencode($sort) ?>&amp;archive=<?php echo urlencode($archive) ?>&amp;go=previous&amp;restypes=<?php echo $restypes; ?>"><?php echo $lang["previousresult"]?></a>
  
  <a class="upLink" onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/search.php<?php if (strpos($search,"!")!==false) {?>?search=<?php echo urlencode($search)?>&amp;offset=<?php echo urlencode($offset) ?>&amp;order_by=<?php echo urlencode($order_by) ?>&amp;archive=<?php echo urlencode($archive) ?>&amp;sort=<?php echo urlencode($sort) ?>&amp;restypes=<?php echo $restypes; ?><?php } ?>"><?php echo $lang["viewallresults"]?></a>
  
  <a class="nextLink" onClick="return <?php echo ($modal?"Modal":"CentralSpace") ?>Load(this,true);" href="<?php echo $baseurl_short?>pages/edit.php?ref=<?php echo urlencode($ref) ?>&amp;search=<?php echo urlencode($search)?>&amp;offset=<?php echo urlencode($offset) ?>&amp;order_by=<?php echo urlencode($order_by) ?>&amp;sort=<?php echo urlencode($sort) ?>&amp;archive=<?php echo urlencode($archive) ?>&amp;go=next&amp;restypes=<?php echo $restypes; ?>"><?php echo         $lang["nextresult"]?></a>
  
  <?php if ($modal) { ?>
&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo $baseurl_short?>pages/edit.php?ref=<?php echo urlencode($ref)?>&amp;search=<?php echo urlencode($search)?>&amp;offset=<?php echo urlencode($offset)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;sort=<?php echo urlencode($sort)?>&amp;archive=<?php echo urlencode($archive)?>&amp;restypes=<?php echo $restypes; ?>" onClick="return CentralSpaceLoad(this);"><?php echo $lang["maximise"]?></a>
&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onClick="ModalClose();"><?php echo $lang["close"] ?></a>
<?php } ?>
  </div>
  <?php
}
function SaveAndClearButtons($extraclass="")
   { 
   global $lang,$multiple,$ref,$clearbutton_on_edit;
   ?>
   <div class="QuestionSubmit <?php echo $extraclass ?>">
   <?php
   if($clearbutton_on_edit)
      { 
      ?>
      <input name="resetform" class="resetform" type="submit" value="<?php echo $lang["clearbutton"]?>" />&nbsp;
      <?php
      } ?>
      <input <?php if ($multiple) { ?>onclick="return confirm('<?php echo $lang["confirmeditall"]?>');"<?php } ?> name="save" class="editsave" type="submit" value="&nbsp;&nbsp;<?php echo ($ref>0)?$lang["save"]:$lang["next"]?>&nbsp;&nbsp;" /><br><br>
     <div class="clearerleft"> </div>
     </div>
   <?php 
   }

?>
</script>

<?php
$form_action = $baseurl_short . 'pages/edit.php?ref=' . urlencode($ref) . '&amp;uploader=' . urlencode(getvalescaped("uploader","")) . '&amp;single=' . urlencode(getvalescaped("single","")) . '&amp;local=' . urlencode(getvalescaped("local","")) . '&amp;search=' . urlencode($search) . '&amp;offset=' . urlencode($offset) . '&amp;order_by=' . urlencode($order_by) . '&amp;sort=' . urlencode($sort) . '&amp;archive=' . urlencode($archive) . '&amp;collection=' . $collection . '&amp;metadatatemplate=' . getval("metadatatemplate","")  . $uploadparams . '&modal=' . getval("modal","");
// If resource type is set as a data only, don't reach upload stage (step 2)
if(0 > $ref && in_array($resource['resource_type'], $data_only_resource_types))
    {
    $uploadparams .= '&single=true&noupload=true';
    $form_action = $baseurl_short . 'pages/edit.php?ref=' . urlencode($ref) . '&amp;local=' . urlencode(getvalescaped("local","")) . '&amp;search=' . urlencode($search) . '&amp;offset=' . urlencode($offset) . '&amp;order_by=' . urlencode($order_by) . '&amp;sort=' . urlencode($sort) . '&amp;archive=' . urlencode($archive) . '&amp;collection=' . $collection . '&amp;metadatatemplate=' . getval("metadatatemplate","")  . $uploadparams . '&modal=' . getval("modal","");
    }
?>

<form method="post" action="<?php echo $form_action; ?>" id="mainform" onsubmit="return <?php echo ($modal?"Modal":"CentralSpace") ?>Post(this,true);">

   <div class="BasicsBox">
      <input type="hidden" name="submitted" value="true">
   <?php 
   if ($multiple) 
      { ?>
      <h1 id="editmultipleresources"><?php echo $lang["editmultipleresources"]?></h1>
      <p style="padding-bottom:20px;"><?php $qty = count($items);
      echo ($qty==1 ? $lang["resources_selected-1"] : str_replace("%number", $qty, $lang["resources_selected-2"])) . ". ";
      # The script doesn't allow editing of empty collections, no need to handle that case here.
      echo text("multiple");
      ?> </p> <?php
      if ($edit_show_save_clear_buttons_at_top) {SaveAndClearButtons("NoPaddingSaveClear");}
      } 
   elseif ($ref>0)
      {
      if (!hook('replacebacklink') && !$modal) 
         {?>
         <p><a href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>" onClick="return CentralSpaceLoad(this,true);">&lt;&nbsp;<?php echo $lang["backtoresourceview"]?></a></p><?php
         }
      if (!hook("replaceeditheader")) 
         { ?>
         <div class="RecordHeader">
          <?php
         # Draw nav
         if (!$multiple&&!hook("dontshoweditnav")) { EditNav(); }
         ?>
         <h1 id="editresource"><?php echo $lang["editresource"]?></h1>
         
        
         </div><!-- end of RecordHeader -->
         <?php
         if ($edit_show_save_clear_buttons_at_top) { SaveAndClearButtons("NoPaddingSaveClear");}
         ?>
         <div class="Question" id="resource_ref_div" style="border-top:none;">
            <label><?php echo $lang["resourceid"]?></label>
            <div class="Fixed"><?php echo urlencode($ref) ?></div>
            <div class="clearerleft"> </div>
         </div>
         <?php 
         }
      hook("custompermshowfile");
      if ((!$is_template && !checkperm("F*"))||$custompermshowfile) 
         { ?>
         <div class="PreviewImageBlock RecordDownload">
         <div class="Question" id="question_file">
            <label><?php echo $lang["file"]?></label>
         <div class="Fixed" style="width:50%;">
         <?php
         if ($resource["has_image"]==1)
            { ?>
            <img id="preview" align="top" src="<?php echo get_resource_path($ref,false,($edit_large_preview && !$modal?"pre":"thm"),false,$resource["preview_extension"],-1,1,false)?>" class="ImageBorder" style="margin-right:10px;"/>
            <?php // check for watermarked version and show it if it exists
            if (checkperm("w"))
               {
               $wmpath=get_resource_path($ref,true,($edit_large_preview?"pre":"thm"),false,$resource["preview_extension"],-1,1,true);
               if (file_exists($wmpath))
                  { ?>
                  <img style="display:none;" id="wmpreview" align="top" src="<?php echo get_resource_path($ref,false,($edit_large_preview?"pre":"thm"),false,$resource["preview_extension"],-1,1,true)?>" class="ImageBorder"/>
                  <?php 
                  }
               } ?>
            <br />
            <?php
            }
         else
            {
            # Show the no-preview icon
              ?>
              <img src="<?php echo $baseurl_short ?>gfx/<?php echo get_nopreview_icon($resource["resource_type"],$resource["file_extension"],true)?>" />
              <br />
              <?php
            }
         if ($resource["file_extension"]!="") 
            { ?>           
            <strong>
            <?php 
            echo str_replace_formatted_placeholder("%extension", $resource["file_extension"], $lang["cell-fileoftype"]) . " (" . formatfilesize(@filesize_unlimited(get_resource_path($ref,true,"",false,$resource["file_extension"]))) . ")";
            ?>
            </strong>
            <?php 
            if (checkperm("w") && $resource["has_image"]==1 && file_exists($wmpath))
               { ?> 
               &nbsp;&nbsp;
               <a href="#" onclick="jQuery('#wmpreview').toggle();jQuery('#preview').toggle();if (jQuery(this).text()=='<?php echo $lang['showwatermark']?>'){jQuery(this).text('<?php echo $lang['hidewatermark']?>');} else {jQuery(this).text('<?php echo $lang['showwatermark']?>');}"><?php echo $lang['showwatermark']?></a>
               <?php 
               } ?>
            <br />
            <?php 
            }

        if($top_nav_upload_type == 'local')
            {
            $replace_upload_type = 'plupload';
            }
        else 
            {
            $replace_upload_type=$top_nav_upload_type;
            }

        // Allow to upload only if resource is not a data only type
        if(0 < $ref && !in_array($resource['resource_type'], $data_only_resource_types))
            {
            ?>
            <a href="<?php echo $baseurl_short?>pages/upload_<?php echo $replace_upload_type ?>.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>&replace_resource=<?php echo urlencode($ref)  ?>&resource_type=<?php echo $resource['resource_type']?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo (($resource["file_extension"]!="")?$lang["replacefile"]:$lang["uploadafile"]) ?></a>
            <?php
            }
         if ($resource["file_extension"]!="") 
            {hook("afterreplacefile");} 
         else 
            {hook("afteruploadfile");}
         if (! $disable_upload_preview) 
            { ?>
            <br />
     <a href="<?php echo $baseurl_short?>pages/upload_preview.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>" onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["uploadpreview"]?></a><?php } ?>
     <?php if (!$disable_alternative_files && !checkperm('A')) { ?><br />
     <a href="<?php echo $baseurl_short?>pages/alternative_files.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>"  onClick="return CentralSpaceLoad(this,true);">&gt;&nbsp;<?php echo $lang["managealternativefiles"]?></a><?php } ?>
     <?php if ($allow_metadata_revert){?><br />
     <a href="<?php echo $baseurl_short?>pages/edit.php?ref=<?php echo urlencode($ref) ?>&exif=true&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>" onClick="return confirm('<?php echo $lang["confirm-revertmetadata"]?>');">&gt; 
        <?php echo $lang["action-revertmetadata"]?></a><?php } ?>
        <?php hook("afterfileoptions"); ?>
     </div>
     <div class="clearerleft"> </div>
  </div>
  </div>
  <?php }
  hook("beforeimagecorrection");

  if (!checkperm("F*")) { ?>
  <div class="Question" id="question_imagecorrection">
   <label><?php echo $lang["imagecorrection"]?><br/><?php echo $lang["previewthumbonly"]?></label><select class="stdwidth" name="tweak" id="tweak" onChange="<?php echo ($modal?"Modal":"CentralSpace") ?>Post(document.getElementById('mainform'),true);">
   <option value=""><?php echo $lang["select"]?></option>
   <?php if ($resource["has_image"]==1) { ?>
   <?php
# On some PHP installations, the imagerotate() function is wrong and images are turned incorrectly.
# A local configuration setting allows this to be rectified
   if (!$image_rotate_reverse_options)
   {
     ?>
     <option value="rotateclock"><?php echo $lang["rotateclockwise"]?></option>
     <option value="rotateanti"><?php echo $lang["rotateanticlockwise"]?></option>
     <?php
  }
  else
  {
     ?>
     <option value="rotateanti"><?php echo $lang["rotateclockwise"]?></option>
     <option value="rotateclock"><?php echo $lang["rotateanticlockwise"]?></option>
     <?php
  }
  ?>
  <?php if ($tweak_allow_gamma){?>
  <option value="gammaplus"><?php echo $lang["increasegamma"]?></option>
  <option value="gammaminus"><?php echo $lang["decreasegamma"]?></option>
  <?php } ?>
  <option value="restore"><?php echo $lang["recreatepreviews"]?></option>
  <?php } else { ?>
  <option value="restore"><?php echo $lang["retrypreviews"]?></option>
  <?php } ?>
  <?php hook("moretweakingopt"); ?>
</select>
<div class="clearerleft"> </div>
</div>
<?php } ?>


<?php }
else
  { # Upload template: (writes to resource with ID [negative user ref])
   if (!hook("replaceeditheader"))
   {
    # Define the title h1:
    if (getval("uploader","")=="plupload") {$titleh1 = $lang["addresourcebatchbrowser"];} # Add Resource Batch - In Browser
    elseif (getval("uploader","")=="java") {$titleh1 = $lang["addresourcebatchbrowserjava"];} # Add Resource Batch - In Browser - Java (Legacy)
    elseif (getval("single","")!="")
    {
       if (getval("status","")=="2")
       {
            $titleh1 = $lang["newarchiveresource"]; # Add Single Archived Resource
         }
         else
         {
            $titleh1 = $lang["addresource"]; # Add Single Resource
         }
      }
    elseif ((getval("local","")!="")||(getval("uploader","")=="local")) {$titleh1 = $lang["addresourcebatchlocalfolder"];} # Add Resource Batch - Fetch from local upload folder
    else $titleh1 = $lang["addresourcebatchftp"]; # Add Resource Batch - Fetch from FTP server
    
    # Define the subtitle h2:
    $titleh2 = str_replace(array("%number","%subtitle"), array("1", $lang["specifydefaultcontent"]), $lang["header-upload-subtitle"]);
    
    ?>
    
    <h1><?php echo $titleh1 ?></h1>
    <h2><?php echo $titleh2 ?></h2>
    <p><?php echo $lang["intro-batch_edit"] ?></p>
    
    <?php
 }
// Upload template: Show the required fields note at the top of the form.
if(!$is_template && $show_required_field_label)
    {
    ?>
    <p class="greyText noPadding"><sup>*</sup> <?php echo $lang['requiredfield']; ?></p>
    <?php
    }

# Upload template: Show the save / clear buttons at the top too, to avoid unnecessary scrolling.
?>
<div class="QuestionSubmit">
   <?php
   global $clearbutton_on_upload;
   if(($clearbutton_on_upload && $ref<0 && !$multiple) || ($ref>0 && $clearbutton_on_edit)) 
     { ?>
  <input name="resetform" class="resetform" type="submit" value="<?php echo $lang["clearbutton"]?>" />&nbsp;
  <?php
    }

    $save_btn_value = $lang['next'];
    if(0 > $ref && in_array($resource['resource_type'], $data_only_resource_types))
        {
        $save_btn_value = $lang['create'];
        }
?>
<input name="save" class="editsave" type="submit" value="&nbsp;&nbsp;<?php echo $save_btn_value; ?>&nbsp;&nbsp;" /><br />
<div class="clearerleft"> </div>
</div>

<?php } ?>

<?php hook("editbefresmetadata"); ?>
<?php if (!hook("replaceedittype")) { ?>
<?php
if(!$multiple)
    {
    ?>
    <div class="Question" id="question_resourcetype">
        <label for="resourcetype"><?php echo $lang["resourcetype"]?></label>
        <select name="resource_type" id="resourcetype" class="stdwidth" 
                onChange="<?php if ($ref>0) { ?>if (confirm('<?php echo $lang["editresourcetypewarning"]; ?>')){<?php } ?><?php echo ($modal?"Modal":"CentralSpace") ?>Post(document.getElementById('mainform'),true);<?php if ($ref>0) { ?>}else {return}<?php } ?>">
        <?php
        $types = get_resource_types();
        for($n = 0; $n < count($types); $n++)
            {
            // skip showing a resource type that we do not to have permission to change to (unless it is currently set to that). Applies to upload only
            if(0 > $ref && (checkperm("XU{$types[$n]['ref']}") || in_array($types[$n]['ref'], $hide_resource_types)))
                {
                continue;
                }
                ?>
            <option value="<?php echo $types[$n]["ref"]?>" <?php if ($resource["resource_type"]==$types[$n]["ref"]) {?>selected<?php } ?>><?php echo htmlspecialchars($types[$n]["name"])?></option><?php
            }
            ?>
        </select>
        <div class="clearerleft"></div>
    </div>
    <?php
    }
else
    {
    # Multiple method of changing resource type.
    ?>
    <h2 <?php echo ($collapsible_sections)?"class=\"CollapsibleSectionHead\"":""?>><?php echo $lang["resourcetype"] ?></h2>
    <div <?php echo ($collapsible_sections)?"class=\"CollapsibleSection\"":""?> id="ResourceTypeSection<?php if ($ref==-1) echo "Upload"; ?>"><input name="editresourcetype" id="editresourcetype" type="checkbox" value="yes" onClick="var q=document.getElementById('editresourcetype_question');if (this.checked) {q.style.display='block';alert('<?php echo $lang["editallresourcetypewarning"] ?>');} else {q.style.display='none';}">&nbsp;<label for="editresourcetype"><?php echo $lang["resourcetype"] ?></label>
    <div class="Question" style="display:none;" id="editresourcetype_question">
        <label for="resourcetype"><?php echo $lang["resourcetype"]?></label>
        <select name="resource_type" id="resourcetype" class="stdwidth">
            <?php
            $types = get_resource_types();
            for($n = 0; $n < count($types); $n++)
                {
                if(in_array($types[$n]['ref'], $hide_resource_types))
                    {
                    continue;
                    }
                ?>
                <option value="<?php echo $types[$n]["ref"]?>" <?php if ($resource["resource_type"]==$types[$n]["ref"]) {?>selected<?php } ?>><?php echo htmlspecialchars($types[$n]["name"])?></option>
                <?php
                }
            ?>
        </select>
        <div class="clearerleft"></div>
    </div>
    <?php
    }
} # end hook("replaceedittype")

$lastrt=-1;

if (isset($metadata_template_resource_type) && !$multiple && !checkperm("F*"))
{
    # Show metadata templates here
  ?>
  <div class="Question" id="question_metadatatemplate">
     <label for="metadatatemplate"><?php echo $lang["usemetadatatemplate"]?></label>
     <select name="metadatatemplate" class="medwidth">
        <option value=""><?php echo (getval("metadatatemplate","")=="")?$lang["select"]:$lang["undometadatatemplate"] ?></option>
        <?php
        $templates=get_metadata_templates();
        foreach ($templates as $template)
        {
          ?>
          <option value="<?php echo $template["ref"] ?>"><?php echo htmlspecialchars($template["field$metadata_template_title_field"]) ?></option>
          <?php   
       }
       ?>
    </select>
    <input type="submit" class="medcomplementwidth" name="copyfromsubmit" value="<?php echo $lang["action-select"]?>">
 </div><!-- end of question_metadatatemplate --> 
 <?php
}

if($embedded_data_user_select && $ref<0 && !$multiple)
 {?>
<div class="Question" id="question_exif">
 <label for="exif_option"><?php echo $lang["embedded_metadata"]?></label>
 <table id="" cellpadding="3" cellspacing="3" style="display: block;">                    
   <tbody>
     <tr>        
       <td width="10" valign="middle">
         <input type="radio" id="exif_extract" name="exif_option" value="extract" onClick="jQuery('.ExifOptions').hide();" <?php if($metadata_read_default) echo "checked" ?>>
      </td>
      <td align="left" valign="middle">
         <label class="customFieldLabel" for="exif_extract"><?php echo $lang["embedded_metadata_extract_option"] ?></label>
      </td>


      <td width="10" valign="middle">
         <input type="radio" id="no_exif" name="exif_option" value="yes" onClick="jQuery('.ExifOptions').hide();" <?php if(!$metadata_read_default) echo "checked" ?>>
      </td>
      <td align="left" valign="middle">
         <label class="customFieldLabel" for="no_exif"><?php echo $lang["embedded_metadata_donot_extract_option"] ?></label>
      </td>


      <td width="10" valign="middle">
         <input type="radio" id="exif_append" name="exif_option" value="append" onClick="jQuery('.ExifOptions').hide();">
      </td>
      <td align="left" valign="middle">
         <label class="customFieldLabel" for="exif_append"><?php echo $lang["embedded_metadata_append_option"] ?></label>
      </td>


      <td width="10" valign="middle">
         <input type="radio" id="exif_prepend" name="exif_option" value="prepend" onClick="jQuery('.ExifOptions').hide();">
      </td>
      <td align="left" valign="middle">
         <label class="customFieldLabel" for="exif_prepend"><?php echo $lang["embedded_metadata_prepend_option"] ?></label>
      </td>

      <td width="10" valign="middle">
         <input type="radio" id="exif_custom" name="exif_option" value="custom" onClick="jQuery('.ExifOptions').show();">
      </td>
      <td align="left" valign="middle">
         <label class="customFieldLabel" for="exif_custom"><?php echo $lang["embedded_metadata_custom_option"] ?></label>
      </td>

   </tr>
</tbody>
</table>



<div class="clearerleft"> </div>
</div>
<?php   
}

if ($edit_upload_options_at_top){include '../include/edit_upload_options.php';}


$use=$ref;

# Resource aliasing.
# 'Copy from' or 'Metadata template' been supplied? Load data from this resource instead.
$originalref=$use;

if (getval("copyfrom","")!="")
{
    # Copy from function
  $copyfrom=getvalescaped("copyfrom","");
  $copyfrom_access=get_resource_access($copyfrom);

    # Check access level
    if ($copyfrom_access!=2) # Do not allow confidential resources (or at least, confidential to that user) to be copied from
    {
       $use=$copyfrom;
       $original_fields=get_resource_field_data($ref,$multiple,true,-1,"",$tabs_on_edit);
    }
 }

 if (getval("metadatatemplate","")!="")
 {
  $use=getvalescaped("metadatatemplate","");
  $original_fields=get_resource_field_data($ref,$multiple,true,-1,"",$tabs_on_edit);
}

# Load resource data
$fields=get_resource_field_data($use,$multiple,!hook("customgetresourceperms"),$originalref,"",$tabs_on_edit);

# if this is a metadata template, set the metadata template title field at the top
if (isset($metadata_template_resource_type)&&(isset($metadata_template_title_field)) && $resource["resource_type"]==$metadata_template_resource_type){
    # recreate fields array, first with metadata template field
  $x=0;
  for ($n=0;$n<count($fields);$n++){
    if ($fields[$n]["resource_type"]==$metadata_template_resource_type){
      $newfields[$x]=$fields[$n];
      $x++;
   }
}
    # then add the others
for ($n=0;$n<count($fields);$n++){
 if ($fields[$n]["resource_type"]!=$metadata_template_resource_type){
   $newfields[$x]=$fields[$n];
   $x++;
}
}
$fields=$newfields;
}

$required_fields_exempt=array(); # new array to contain required fields that have not met the display condition

function is_field_displayed($field)
{
  global $ref, $resource;

    # Field is an archive only field
  return !(($resource["archive"]==0 && $field["resource_type"]==999)
    # Field has write access denied
    || (checkperm("F*") && !checkperm("F-" . $field["ref"])
     && !($ref < 0 && checkperm("P" . $field["ref"])))
    || checkperm("F" . $field["ref"])
    # Upload only field
    || ($ref < 0 && $field["hide_when_uploading"] && $field["required"]==0)
    || hook('edithidefield', '', array('field' => $field))
    || hook('edithidefield2', '', array('field' => $field)));
}

function check_display_condition($n, $field)
{
  global $fields, $scriptconditions, $required_fields_exempt, $blank_edit_template, $ref, $use;

  $displaycondition=true;
  $s=explode(";",$field["display_condition"]);
  $condref=0;
    foreach ($s as $condition) # Check each condition
    {
       $displayconditioncheck=false;
       $s=explode("=",$condition);
        for ($cf=0;$cf<count($fields);$cf++) # Check each field to see if needs to be checked
        {
            node_field_options_override($fields[$cf]);
            if ($s[0]==$fields[$cf]["name"]) # this field needs to be checked
            {
                $scriptconditions[$condref]["field"] = $fields[$cf]["ref"];  # add new jQuery code to check value
                $scriptconditions[$condref]['type'] = $fields[$cf]['type'];
                $scriptconditions[$condref]['options'] = (in_array($fields[$cf]['type'],array(2, 3, 7, 9, 12))?implode(",",$fields[$cf]['node_options']):$fields[$cf]['options']);

                $checkvalues=$s[1];
                $validvalues=explode("|",mb_strtoupper($checkvalues));
                $scriptconditions[$condref]["valid"]= "\"";
                $scriptconditions[$condref]["valid"].= implode("\",\"",$validvalues);
                $scriptconditions[$condref]["valid"].= "\"";
                $v=trim_array(explode(",",mb_strtoupper($fields[$cf]["value"])));

                // If blank edit template is used, on upload form the dependent fields should be hidden
                if($blank_edit_template && $ref < 0 && $use === '-1') {
                   $v = array();
                }
                
                foreach ($validvalues as $validvalue)
                {
                    if (in_array($validvalue,$v)) {$displayconditioncheck=true;} # this is  a valid value
                 }
                 if (!$displayconditioncheck) {$displaycondition=false;$required_fields_exempt[]=$field["ref"];}
                #add jQuery code to update on changes
                    if ($fields[$cf]["type"]==2) # add onchange event to each checkbox field
                    {
                        # construct the value from the ticked boxes
                        # Note: it seems wrong to start with a comma, but this ensures it is treated as a comma separated list by split_keywords(), so if just one item is selected it still does individual word adding, so 'South Asia' is split to 'South Asia','South','Asia'.
                     $options=trim_array($fields[$cf]["node_options"]);
                     ?><script type="text/javascript">
                     jQuery(document).ready(function() {<?php
                       for ($m=0;$m<count($options);$m++)
                       {
                         $checkname=$fields[$cf]["ref"] . "_" . md5($options[$m]);
                         echo "
                         jQuery('input[name=\"" . $checkname . "\"]').change(function (){
                           checkDisplayCondition" . $field["ref"] . "();
                        });";
                  }
                  ?>
               });
                     </script><?php
                  }
                        # add onChange event to each radio button
                  else if($fields[$cf]['type'] == 12) {

                    $options = $fields[$cf]['node_options'];?>
					
                    <script type="text/javascript">
                    jQuery(document).ready(function() {

                       <?php
                       foreach ($options as $option) {
                         $element_id = 'field_' . $fields[$cf]['ref'] . '_' . sha1($option);
                         $jquery = sprintf('
                          jQuery("#%s").change(function() {
                            checkDisplayCondition%s();
                         });
                         ',
                         $element_id,
                         $field["ref"]
                         );
                         echo $jquery;
                      } ?>

                   });
                    </script>

                    <?php
                 }
                 else
                 {
                  ?>
                  <script type="text/javascript">
                  jQuery(document).ready(function() {
                    jQuery('#field_<?php echo $fields[$cf]["ref"];?>').change(function (){

                       checkDisplayCondition<?php echo $field["ref"];?>();

                    });
                 });
                  </script>
                  <?php
               }
            }

            } # see if next field needs to be checked

            $condref++;
        } # check next condition

        ?>
        <script type="text/javascript">
        function checkDisplayCondition<?php echo $field["ref"];?>()
			{
			field<?php echo $field["ref"]?>status=jQuery('#question_<?php echo $n ?>').css('display');
			newfield<?php echo $field["ref"]?>status='none';
			newfield<?php echo $field["ref"]?>provisional=true;
			
			<?php
			foreach ($scriptconditions as $scriptcondition)
				{
				?>
				newfield<?php echo $field["ref"]?>provisionaltest=false;
				if (jQuery('#field_<?php echo $scriptcondition["field"]?>').length!=0)
					{
					<?php
					if($scriptcondition['type'] == 12) {

						$scriptcondition["options"] = explode(',', $scriptcondition["options"]);

						foreach ($scriptcondition["options"] as $key => $value) 
							{
							$scriptcondition["options"][$key] = sha1($value);
							}

						$scriptcondition["options"] = implode(',', $scriptcondition["options"]);
						?>
						
						var options_string = '<?php echo $scriptcondition["options"]; ?>';
						var field<?php echo $scriptcondition["field"]; ?>_options = options_string.split(',');
						var checked = null;
						
						for(var i=0; i < field<?php echo $scriptcondition["field"]; ?>_options.length; i++)
							{
							if(jQuery('#field_<?php echo $scriptcondition["field"]; ?>_' + field<?php echo $scriptcondition["field"]; ?>_options[i]).is(':checked')) 
								{
								checked = jQuery('#field_<?php echo $scriptcondition["field"]; ?>_' + field<?php echo $scriptcondition["field"]; ?>_options[i] + ':checked').val();
								checked = checked.toUpperCase();
								}
							}
						
						fieldvalues<?php echo $scriptcondition["field"]?>=checked.split(',');
						fieldokvalues<?php echo $scriptcondition["field"]; ?> = [<?php echo $scriptcondition["valid"]; ?>];

						if(checked !== null && jQuery.inArray(checked, fieldokvalues<?php echo $scriptcondition["field"]; ?>) > -1) 
							{
							newfield<?php echo $field["ref"]; ?>provisionaltest = true;
							}
						<?php
						}
					else
						{
						?>
						fieldcheck<?php echo $scriptcondition["field"]?>=jQuery('#field_<?php echo $scriptcondition["field"]?>').val().toUpperCase();
						fieldvalues<?php echo $scriptcondition["field"]?>=fieldcheck<?php echo $scriptcondition["field"]?>.split(',');
						//alert(fieldvalues<?php echo $scriptcondition["field"]?>);
						<?php
						}
					?>
					}
				else
					{
					<?php

					# Handle Radio Buttons type: not sure if this is needed here anymore
					if($scriptcondition['type'] == 12) {

						$scriptcondition["options"] = explode(',', $scriptcondition["options"]);

						foreach ($scriptcondition["options"] as $key => $value) 
							{
							$scriptcondition["options"][$key] = sha1($value);
							}

						$scriptcondition["options"] = implode(',', $scriptcondition["options"]);
						?>
						
						var options_string = '<?php echo $scriptcondition["options"]; ?>';
						var field<?php echo $scriptcondition["field"]; ?>_options = options_string.split(',');
						var checked = null;
						
						for(var i=0; i < field<?php echo $scriptcondition["field"]; ?>_options.length; i++)
							{
							if(jQuery('#field_<?php echo $scriptcondition["field"]; ?>_' + field<?php echo $scriptcondition["field"]; ?>_options[i]).is(':checked')) 
								{
								checked = jQuery('#field_<?php echo $scriptcondition["field"]; ?>_' + field<?php echo $scriptcondition["field"]; ?>_options[i] + ':checked').val();
								checked = checked.toUpperCase();
								}
							}

						fieldokvalues<?php echo $scriptcondition["field"]; ?> = [<?php echo $scriptcondition["valid"]; ?>];

						if(checked !== null && jQuery.inArray(checked, fieldokvalues<?php echo $scriptcondition["field"]; ?>) > -1) 
							{
							newfield<?php echo $field["ref"]; ?>provisionaltest = true;
							}
						<?php
						}
					?>
					fieldvalues<?php echo $scriptcondition["field"]?>=new Array();
					checkedvals<?php echo $scriptcondition["field"]?>=jQuery('input[name^=<?php echo $scriptcondition["field"]?>_]');
      
					jQuery.each(checkedvals<?php echo $scriptcondition["field"]?>,function()
						{
						if (jQuery(this).is(':checked'))
							{
							checktext<?php echo $scriptcondition["field"]?>=jQuery(this).parent().next().text().toUpperCase();
							checktext<?php echo $scriptcondition["field"]?> = jQuery.trim(checktext<?php echo $scriptcondition["field"]?>);
							fieldvalues<?php echo $scriptcondition["field"]?>.push(checktext<?php echo $scriptcondition["field"]?>);
							//alert(fieldvalues<?php echo $scriptcondition["field"]?>);
							}
						});
					}
		
				fieldokvalues<?php echo $scriptcondition["field"]?>=new Array();
				fieldokvalues<?php echo $scriptcondition["field"]?>=[<?php echo $scriptcondition["valid"]?>];
		
				jQuery.each(fieldvalues<?php echo $scriptcondition["field"]?>,function(f,v)
					{
					//alert("checking value " + fieldvalues<?php echo $scriptcondition["field"]?> + " against " + fieldokvalues<?php echo $scriptcondition["field"]?>);
					//alert(jQuery.inArray(fieldvalues<?php echo $scriptcondition["field"]?>,fieldokvalues<?php echo $scriptcondition["field"]?>));
					if ((jQuery.inArray(v,fieldokvalues<?php echo $scriptcondition["field"]?>))>-1 || (fieldvalues<?php echo $scriptcondition["field"]?> ==fieldokvalues<?php echo  $scriptcondition["field"]?>))
						{
						newfield<?php echo $field["ref"]?>provisionaltest=true;
						}
					});

				if (newfield<?php echo $field["ref"]?>provisionaltest==false)
					{
					newfield<?php echo $field["ref"]?>provisional=false;
					}
				<?php
				}
			?>
			exemptfieldsval=jQuery('#exemptfields').val();
			exemptfieldsarr=exemptfieldsval.split(',');
			
			if (newfield<?php echo $field["ref"]?>provisional==true)
				{
				if (jQuery.inArray(<?php echo $field["ref"]?>,exemptfieldsarr))
					{
					exemptfieldsarr.splice(jQuery.inArray(<?php echo $field["ref"]?>, exemptfieldsarr), 1 );
					}
				newfield<?php echo $field["ref"]?>status='block';
				}
			else
				{
				if ((jQuery.inArray(<?php echo $field["ref"]?>,exemptfieldsarr))==-1)
					{
					exemptfieldsarr.push(<?php echo $field["ref"]?>);
					}
				}
			jQuery('#exemptfields').val(exemptfieldsarr.join(","));

			if (newfield<?php echo $field["ref"]?>status!=field<?php echo $field["ref"]?>status)
				{
				jQuery('#question_<?php echo $n ?>').slideToggle();
				if (jQuery('#question_<?php echo $n ?>').css('display')=='block')
					{
					jQuery('#question_<?php echo $n ?>').css('border-top','');
					}
				else
					{
					jQuery('#question_<?php echo $n ?>').css('border-top','none');
					}
				}
			}
		</script>
		<?php
return $displaycondition;
}

# Allows language alternatives to be entered for free text metadata fields.
function display_multilingual_text_field($n, $field, $translations)
  {
  global $language, $languages, $lang;
  ?>
  <p><a href="#" class="OptionToggle" onClick="l=document.getElementById('LanguageEntry_<?php echo $n?>');if (l.style.display=='block') {l.style.display='none';this.innerHTML='<?php echo $lang["showtranslations"]?>';} else {l.style.display='block';this.innerHTML='<?php echo $lang["hidetranslations"]?>';} return false;"><?php echo $lang["showtranslations"]?></a></p>
  <table class="OptionTable" style="display:none;" id="LanguageEntry_<?php echo $n?>">
     <?php
     reset($languages);
     foreach ($languages as $langkey => $langname)
     {
       if ($language!=$langkey)
       {
         if (array_key_exists($langkey,$translations)) {$transval=$translations[$langkey];} else {$transval="";}
         ?>
         <tr>
            <td nowrap valign="top"><?php echo htmlspecialchars($langname)?>&nbsp;&nbsp;</td>

            <?php
            if ($field["type"]==0)
            {
              ?>
              <td><input type="text" class="stdwidth" name="multilingual_<?php echo $n?>_<?php echo $langkey?>" value="<?php echo htmlspecialchars($transval)?>"></td>
              <?php
           }
           else
           {
              ?>
              <td><textarea rows=6 cols=50 name="multilingual_<?php echo $n?>_<?php echo $langkey?>"><?php echo htmlspecialchars($transval)?></textarea></td>
              <?php
           }
           ?>
        </tr>
        <?php
     }
  }
  ?></table><?php
  }

function display_field($n, $field, $newtab=false)
  {
  global $use, $ref, $original_fields, $multilingual_text_fields, $multiple, $lastrt,$is_template, $language, $lang,
  $blank_edit_template, $edit_autosave, $errors, $tabs_on_edit, $collapsible_sections, $ctrls_to_save,
  $embedded_data_user_select, $embedded_data_user_select_fields, $show_error, $save_errors, $baseurl;

  $name="field_" . $field["ref"];
  $value=$field["value"];
  $value=trim($value);

  if ($field["omit_when_copying"] && $use!=$ref)
    {
    # Omit when copying - return this field back to the value it was originally, instead of using the current value which has been fetched from the new resource.
    reset($original_fields);
    foreach ($original_fields as $original_field)
      {
      if ($original_field["ref"]==$field["ref"]) {$value=$original_field["value"];}
      }
    }

  $displaycondition=true;
  if ($field["display_condition"]!="")
    {
    #Check if field has a display condition set
    $displaycondition=check_display_condition($n,$field);
    }

  if ($multilingual_text_fields)
    {
    # Multilingual text fields - find all translations and display the translation for the current language.
    $translations=i18n_get_translations($value);
    if (array_key_exists($language,$translations)) {$value=$translations[$language];} else {$value="";}
    }

  if ($multiple && (getval("copyfrom","")=="" || str_replace(array(" ",","),"",$value)=="")) {$value="";} # Blank the value for multi-edits  unless copying data from resource.

  if ($field["resource_type"]!=$lastrt && $lastrt!=-1 && $collapsible_sections)
      {
      ?></div><h2 class="CollapsibleSectionHead" id="resource_type_properties"><?php echo htmlspecialchars(get_resource_type_name($field["resource_type"]))?> <?php echo $lang["properties"]?></h2><div class="CollapsibleSection" id="ResourceProperties<?php if ($ref==-1) echo "Upload"; ?><?php echo $field["resource_type"]; ?>Section"><?php
      }
    $lastrt=$field["resource_type"];

    # Blank form if 'reset form' has been clicked.
    if (getval("resetform","")!="") {$value="";}

    # If config option $blank_edit_template is set, always show a blank form for user edit templates.
    if ($ref<0 && $blank_edit_template && getval("submitted","")=="") {$value="";}

    /****************************** Errors on saving ***************************************/
    $field_save_error = FALSE;
    if (isset($show_error) && isset($save_errors))
      {
      if(array_key_exists($field['ref'], $save_errors))
        {
        $field_save_error = TRUE;
        }
      }
     
    if ($multiple && !hook("replace_edit_all_checkbox","",array($field["ref"])))
      {
      # Multiple items, a toggle checkbox appears which activates the question
      ?>
      <div class="edit_multi_checkbox"><input name="editthis_<?php echo htmlspecialchars($name) ?>" id="editthis_<?php echo $n?>" type="checkbox" value="yes"<?php if($field_save_error){?> checked<?php }?> onClick="var q=document.getElementById('question_<?php echo $n?>');var m=document.getElementById('modeselect_<?php echo $n?>');var f=document.getElementById('findreplace_<?php echo $n?>');if (this.checked) {q.style.display='block';m.style.display='block';} else {q.style.display='none';m.style.display='none';f.style.display='none';document.getElementById('modeselectinput_<?php echo $n?>').selectedIndex=0;}" <?php if(getval("copyfrom","")!="" && $value!=""){echo " checked" ;} ?>>&nbsp;<label for="editthis<?php echo $n?>"><?php echo htmlspecialchars($field["title"]) ?></label></div><!-- End of edit_multi_checkbox -->
      <?php
      }
      
  if ($multiple && !hook("replace_edit_all_mode_select","",array($field["ref"])))
      {
      # When editing multiple, give option to select Replace All Text or Find and Replace
      ?>
      <div class="Question" id="modeselect_<?php echo $n?>" style="<?php if($value=="" && !$field_save_error ){echo "display:none;";} ?>padding-bottom:0;margin-bottom:0;">
      <label for="modeselectinput"><?php echo $lang["editmode"]?></label>
      <select id="modeselectinput_<?php echo $n?>" name="modeselect_<?php echo $field["ref"]?>" class="stdwidth" onChange="var fr=document.getElementById('findreplace_<?php echo $n?>');var q=document.getElementById('question_<?php echo $n?>');<?php if ($field["type"]==7){?>if (this.value=='RM'){branch_limit_field['field_<?php echo $field["ref"]?>']=1;}else{branch_limit_field['field_<?php echo $field["ref"]?>']=0;}<?php } ?>if (this.value=='FR') {fr.style.display='block';q.style.display='none';} else {fr.style.display='none';q.style.display='block';}<?php hook ("edit_all_mode_js"); ?>">
      <option value="RT"><?php echo $lang["replacealltext"]?></option>
      <?php
      if (in_array($field["type"], array("0","1","5","8")))
        {
        # Find and replace appies to text boxes only.
        ?>
        <option value="FR" <?php if(getval("modeselect_" . $field["ref"],"")=="FR"){?> selected<?php } ?>><?php echo $lang["findandreplace"]?></option>
        <?php
        }
      if (in_array($field["type"], array("0","1","5","8")))
        {
        # Prepend applies to text boxes only.
        ?>
        <option value="PP"<?php if(getval("modeselect_" . $field["ref"],"")=="PP"){?> selected<?php } ?>><?php echo $lang["prependtext"]?></option>
        <?php
        }
      if (in_array($field["type"], array("0","1","2","3","5","7","8","9")))
        {
        # Append applies to text boxes, checkboxes ,category tree and dropdowns only.
        ?>
        <option value="AP"<?php if(getval("modeselect_" . $field["ref"],"")=="AP"){?> selected<?php } ?>><?php echo $lang["appendtext"]?></option>
        <?php
        }
      if (in_array($field["type"], array("0","1","2","3","5","7","9")))
        {
        # Remove applies to text boxes, checkboxes, dropdowns, category trees and dynamic keywords only. 
        ?>
        <option value="RM"<?php if(getval("modeselect_" . $field["ref"],"")=="RM"){?> selected<?php } ?>><?php echo $lang["removetext"]?></option>
        <?php
        }
        hook ("edit_all_extra_modes");
        ?>
        </select>
      </div><!-- End of modeselect_<?php echo $n?> -->

      <div class="Question" id="findreplace_<?php echo $n?>" style="display:none;border-top:none;">
        <label>&nbsp;</label>
        <?php echo $lang["find"]?> <input type="text" name="find_<?php echo $field["ref"]?>" class="shrtwidth">
        <?php echo $lang["andreplacewith"]?> <input type="text" name="replace_<?php echo $field["ref"]?>" class="shrtwidth">
      </div><!-- End of findreplace_<?php echo $n?> -->

      <?php hook ("edit_all_after_findreplace","",array($field,$n)); 
      }
      ?>

      <div class="Question <?php if($field_save_error) { echo 'FieldSaveError'; } ?>" id="question_<?php echo $n?>" <?php
      if (($multiple && !$field_save_error) || !$displaycondition || $newtab)
        {?>style="border-top:none;<?php 
        if (($multiple && $value=="") || !$displaycondition) # Hide this
        {
        ?>
        display:none;
        <?php
        }
        ?>"<?php
        }
     ?>>
     <?php 
     $labelname = $name;

     // Add _selector to label so it will keep working:
     if($field['type'] == 9)
      {
      $labelname .= '_selector';
      }

      // Add -d to label so it will keep working
     if($field['type'] == 4)
        {
        $labelname .= '-d';
        }
        ?>
     <label for="<?php echo htmlspecialchars($labelname)?>" ><?php if (!$multiple) {?><?php echo htmlspecialchars($field["title"])?> <?php if (!$is_template && $field["required"]==1) { ?><sup>*</sup><?php } ?><?php } ?></label>

     <?php
    # Autosave display
     if ($edit_autosave || $ctrls_to_save)
      {
      ?>
      <div class="AutoSaveStatus">
      <span id="AutoSaveStatus<?php echo $field["ref"] ?>" style="display:none;"></span>
      </div>
      <?php
      } 
    # Define some Javascript for help actions (applies to all fields)
     $help_js="onBlur=\"HideHelp(" . $field["ref"] . ");return false;\" onFocus=\"ShowHelp(" . $field["ref"] . ");return false;\"";

    #hook to modify field type in special case. Returning zero (to get a standard text box) doesn't work, so return 1 for type 0, 2 for type 1, etc.
     $modified_field_type="";
     $modified_field_type=(hook("modifyfieldtype"));
     if ($modified_field_type){$field["type"]=$modified_field_type-1;}

     hook("addfieldextras");
    # ----------------------------  Show field -----------------------------------
     $type=$field["type"];
    if ($type=="") {$type=0;} # Default to text type.
    if (!hook("replacefield","",array($field["type"],$field["ref"],$n)))
    	{
		global $auto_order_checkbox,$auto_order_checkbox_case_insensitive;
		include "edit_fields/" . $type . ".php";
		}

    # ----------------------------------------------------------------------------

    # Display any error messages from previous save
    if (array_key_exists($field["ref"],$errors))
      {
       ?>
       <div class="FormError">!! <?php echo $errors[$field["ref"]]?> !!</div>
       <?php
      }

    if (trim($field["help_text"]!=""))
     {
        # Show inline help for this field.
        # For certain field types that have no obvious focus, the help always appears.
       ?>
       <div class="FormHelp" style="padding:0;<?php if (!in_array($field["type"],array(2,4,6,7,10))) { ?> display:none;<?php } else { ?> clear:left;<?php } ?>" id="help_<?php echo $field["ref"]?>"><div class="FormHelpInner"><?php echo nl2br(trim(i18n_get_translated($field["help_text"],false)))?></div></div>
       <?php
     }

    # If enabled, include code to produce extra fields to allow multilingual free text to be entered.
    if ($multilingual_text_fields && ($field["type"]==0 || $field["type"]==1 || $field["type"]==5))
      {
       display_multilingual_text_field($n, $field, $translations);
      }
    
    if(($embedded_data_user_select || (isset($embedded_data_user_select_fields) && in_array($field["ref"],$embedded_data_user_select_fields))) && ($ref<0 && !$multiple))
    {
      ?>
      <table id="exif_<?php echo $field["ref"] ?>" class="ExifOptions" cellpadding="3" cellspacing="3" <?php if ($embedded_data_user_select){?> style="display: none;" <?php } ?>>                    
         <tbody>
           <tr>        
             <td>
                <?php echo "&nbsp;&nbsp;" . $lang["embeddedvalue"] . ": " ?>
             </td>
             <td width="10" valign="middle">
               <input type="radio" id="exif_extract_<?php echo $field["ref"] ?>" name="exif_option_<?php echo $field["ref"] ?>" value="yes" checked>
            </td>
            <td align="left" valign="middle">
               <label class="customFieldLabel" for="exif_extract_<?php echo $field["ref"] ?>"><?php echo $lang["embedded_metadata_extract_option"] ?></label>
            </td>


            <td width="10" valign="middle">
               <input type="radio" id="no_exif_<?php echo $field["ref"] ?>" name="exif_option_<?php echo $field["ref"] ?>" value="no">
            </td>
            <td align="left" valign="middle">
               <label class="customFieldLabel" for="no_exif_<?php echo $field["ref"] ?>"><?php echo $lang["embedded_metadata_donot_extract_option"] ?></label>
            </td>


            <td width="10" valign="middle">
               <input type="radio" id="exif_append_<?php echo $field["ref"] ?>" name="exif_option_<?php echo $field["ref"] ?>" value="append">
            </td>
            <td align="left" valign="middle">
               <label class="customFieldLabel" for="exif_append_<?php echo $field["ref"] ?>"><?php echo $lang["embedded_metadata_append_option"] ?></label>
            </td>


            <td width="10" valign="middle">
               <input type="radio" id="exif_prepend_<?php echo $field["ref"] ?>" name="exif_option_<?php echo $field["ref"] ?>" value="prepend">
            </td>
            <td align="left" valign="middle">
               <label class="customFieldLabel" for="exif_prepend_<?php echo $field["ref"] ?>"><?php echo $lang["embedded_metadata_prepend_option"] ?></label>
            </td>

         </tr>
      </tbody>
   </table>        
   <?php
  }
  ?>
  <div class="clearerleft"> </div>
  </div><!-- end of question_<?php echo $n?> div -->
  <?php     
  
  hook('afterfielddisplay', '', array($n, $field));
  }

?>
</div>
<?php hook('editbeforesectionhead');

global $collapsible_sections;
if($collapsible_sections)
{
  ?>
  <div id="CollapsibleSections">
     <?php
  }

  $display_any_fields=false;
  $fieldcount=0;
  $tabname="";
  $tabcount=0;
  for ($n=0;$n<count($fields);$n++)
  {
     if (is_field_displayed($fields[$n]))
     {
       $display_any_fields=true;
       break;
    }
 }
 if ($display_any_fields)
 {
    # "copy data from" feature
  if ($enable_copy_data_from && !checkperm("F*"))
    { ?>
 <div class="Question" id="question_copyfrom">
    <label for="copyfrom"><?php echo $lang["batchcopyfrom"]?></label>
    <input class="stdwidth" type="text" name="copyfrom" id="copyfrom" value="" style="width:80px;">
    <input type="submit" id="copyfromsubmit" name="copyfromsubmit" value="<?php echo $lang["copy"]?>" onClick="event.preventDefault();CentralSpacePost(document.getElementById('mainform'),true);">
    <input type="submit" name="save" value="Save">
 </div><!-- end of question_copyfrom -->
 <?php
}

?><h2  <?php if($collapsible_sections){echo'class="CollapsibleSectionHead"';}?> id="ResourceMetadataSectionHead"><?php echo $lang["resourcemetadata"]?></h2><?php
?><div <?php if($collapsible_sections){echo'class="CollapsibleSection"';}?> id="ResourceMetadataSection<?php if ($ref<0) echo "Upload"; ?>"><?php
}

if($tabs_on_edit)
{
    #  -----------------------------  Draw tabs ---------------------------
  $tabname="";
  $tabcount=0;
  if (count($fields)>0 && $fields[0]["tab_name"]!="")
  { 
    ?>

    <?php
    $extra="";
    $tabname="";
    $tabcount=0;
    $tabtophtml="";
    for ($n=0;$n<count($fields);$n++)
    {   
      $value=$fields[$n]["value"];

            # draw new tab?
      if ($tabname!=$fields[$n]["tab_name"] && is_field_displayed($fields[$n]))
      {
        if($tabcount==0){$tabtophtml.="<div class=\"BasicsBox\" id=\"BasicsBoxTabs\"><div class=\"TabBar\">";}
        $tabtophtml.="<div id=\"tabswitch" . $tabcount . "\" class=\"Tab";
        if($tabcount==0){$tabtophtml.=" TabSelected ";}
        $tabtophtml.="\"><a href=\"#\" onclick=\"SelectTab(" . $tabcount . ");return false;\">" .  i18n_get_translated($fields[$n]["tab_name"]) . "</a></div>";
        $tabcount++;
        $tabname=$fields[$n]["tab_name"];
     }
  }

  if ($tabcount>1)
  {
   echo $tabtophtml;
   echo "</div><!-- end of TabBar -->";
}

if ($tabcount>1)
   {?>
<script type="text/javascript">
function SelectTab(tab)
{
                // Deselect all tabs
                <?php for ($n=0;$n<$tabcount;$n++) { ?>
                 document.getElementById("tab<?php echo $n?>").style.display="none";
                 document.getElementById("tabswitch<?php echo $n?>").className="Tab";
                 <?php } ?>
                 document.getElementById("tab" + tab).style.display="block";
                 document.getElementById("tabswitch" + tab).className="Tab TabSelected";
              }
              </script>
              <?php
           }
        }


        if ($tabcount>1)
        {
          ?>
          <div id="tab0" class="TabbedPanel<?php if ($tabcount>0) { ?> StyledTabbedPanel<?php } ?>">
             <div class="clearerleft"> </div>
             <div class="TabPanelInner">

                <?php
             }
          }


          $tabname="";
          $tabcount=0;    
          for ($n=0;$n<count($fields);$n++)
          {
    # Should this field be displayed?
           if (is_field_displayed($fields[$n]))
           {
            if(in_array($fields[$n]['resource_type'], $hide_resource_types)) { continue; }
            $newtab=false;  
            if($n==0 && $tabs_on_edit){$newtab=true;}
        # draw new tab panel?
            if ($tabs_on_edit && ($tabname!=$fields[$n]["tab_name"]) && ($fieldcount>0))
            {
               $tabcount++;
            # Also display the custom formatted data $extra at the bottom of this tab panel.
               ?><div class="clearerleft"> </div><?php if(isset($extra)){echo $extra;} ?></div><!-- end of TabPanelInner --></div><!-- end of TabbedPanel --><div class="TabbedPanel StyledTabbedPanel" style="display:none;" id="tab<?php echo $tabcount?>"><div class="TabPanelInner"><?php  
               $extra="";
               $newtab=true;
            }
            $tabname=$fields[$n]["tab_name"];
            $fieldcount++;
            
			node_field_options_override($fields[$n]);
            display_field($n, $fields[$n], $newtab);
         }
      }



      if ($tabs_on_edit && $tabcount>0)
      {
        ?>
        <div class="clearerleft"> </div>
     </div><!-- end of TabPanelInner -->
  </div><!-- end of TabbedPanel -->
</div><!-- end of Tabs BasicsBox -->
<?php
}


# Add required_fields_exempt so it is submitted with POST
echo " <input type=hidden name=\"exemptfields\" id=\"exemptfields\" value=\"" . implode(",",$required_fields_exempt) . "\">";   

# Work out the correct archive status.
if ($ref<0) # Upload template.
   {
   global $override_status_default;
   $modified_defaultstatus = hook("modifydefaultstatusmode");
   if ($setarchivestate==2)
      {
      if (checkperm("e2")) {$setarchivestate = 2;} # Set status to Archived - if the user has the required permission.
      elseif ($modified_defaultstatus) {$setarchivestate = $modified_defaultstatus;}  # Set the modified default status - if set.
      elseif (checkperm("e" . $resource["archive"])) {$setarchivestate = $resource["archive"];} # Else, set status to the status stored in the user template - if the user has the required permission.
      elseif (checkperm("c")) {$setarchivestate = 0;} # Else, set status to Active - if the user has the required permission.
      elseif (checkperm("d")) {$setarchivestate = -2;} # Else, set status to Pending Submission.
      }
   else
      {
      if ($modified_defaultstatus) {$setarchivestate = $modified_defaultstatus;}  # Set the modified default status - if set.
      elseif ($override_status_default!==false) {$setarchivestate = $override_status_default;}
      elseif ($resource["archive"]!=2 && checkperm("e" . $resource["archive"])) {$setarchivestate = $resource["archive"];} # Set status to the status stored in the user template - if the status is not Archived and if the user has the required permission.
      elseif (checkperm("c")) {$setarchivestate = 0;} # Else, set status to Active - if the user has the required permission.
      elseif (checkperm("d") && !checkperm('e-2') && checkperm('e-1')) {$setarchivestate = -1;} # Else, set status to Pending Review if the user has only edit access to Pending review
      elseif (checkperm("d")) {$setarchivestate = -2;} # Else, set status to Pending Submission.   
      }
   if ($show_status_and_access_on_upload==false)
      {
      # Hide the dropdown, and set the default status.
      ?>
      <input type=hidden name="status" id="status" value="<?php echo htmlspecialchars($setarchivestate)?>"><?php
      }
   }
else # Edit Resource(s).
   {
   $setarchivestate = $resource["archive"];
   }

# Status / Access / Related Resources

if (eval($show_status_and_access_on_upload_perm) && !hook("editstatushide")) # Only display Status / Access / Related Resources if permissions match.
{
  if(!hook("replacestatusandrelationshipsheader"))
  {
    if ($ref>0 || $show_status_and_access_on_upload===true || $show_access_on_upload===true)
    {
            if ($enable_related_resources && ($multiple || $ref>0)) # Showing relationships
            {
              ?></div><!-- end of ResourceMetadataSection --><h2 <?php echo ($collapsible_sections)?"class=\"CollapsibleSectionHead\"":""?> id="StatusRelationshipsSectionHead"><?php echo $lang["statusandrelationships"]?></h2><div <?php echo ($collapsible_sections)?"class=\"CollapsibleSection\"":""?> id="StatusRelationshipsSection<?php if ($ref==-1) echo "Upload"; ?>"><?php
           }
           else
           {
                ?></div><!-- end of ResourceMetadataSection --><h2 <?php echo ($collapsible_sections)?"class=\"CollapsibleSectionHead\"":""?>><?php echo $lang["status"]?></h2><div <?php echo ($collapsible_sections)?"class=\"CollapsibleSection\"":""?> id="StatusSection<?php if ($ref==-1) echo "Upload"; ?>"><?php # Not showing relationships
             }
          }

       } /* end hook replacestatusandrelationshipsheader */

       hook("statreladdtopfields");

# Status
if ($ref>0 || $show_status_and_access_on_upload===true)
   {
   if(!hook("replacestatusselector"))
      {
      if ($multiple)
         { ?>
         <div id="editmultiple_status"><input name="editthis_status" id="editthis_status" value="yes" type="checkbox" onClick="var q=document.getElementById('question_status');if (q.style.display!='block') {q.style.display='block';} else {q.style.display='none';}">&nbsp;<label id="editthis_status_label" for="editthis<?php echo $n?>"><?php echo $lang["status"]?></label></div>
         <?php
         } ?>
      <div class="Question" id="question_status" <?php if ($multiple) {?>style="display:none;"<?php } ?>>
         <label for="status"><?php echo $lang["status"]?></label><?php

         # Autosave display
         if ($edit_autosave || $ctrls_to_save)
            { ?>
            <div class="AutoSaveStatus" id="AutoSaveStatusStatus" style="display:none;"></div>
            <?php
            } ?>
         <select class="stdwidth" name="status" id="status" <?php if ($edit_autosave) {?>onChange="AutoSave('Status');"<?php } ?>><?php
         for ($n=-2;$n<=3;$n++)
            {
            if (checkperm("e" . $n)) { ?><option value="<?php echo $n?>" <?php if ($setarchivestate==$n) { ?>selected<?php } ?>><?php echo $lang["status" . $n]?></option><?php }
            }
         foreach ($additional_archive_states as $additional_archive_state)
            {
            if (checkperm("e" . $additional_archive_state)) { ?><option value="<?php echo $additional_archive_state?>" <?php if ($setarchivestate==$additional_archive_state) { ?>selected<?php } ?>><?php echo isset($lang["status" . $additional_archive_state])?$lang["status" . $additional_archive_state]:$additional_archive_state ?></option><?php }
            }?>
         </select>
         <div class="clearerleft"> </div>
      </div><?php
      } /* end hook replacestatusselector */
   }

    # Access
hook("beforeaccessselector");
if (!hook("replaceaccessselector"))
{
 if($ref<0 && $override_access_default!==false)
 {
   $resource["access"]=$override_access_default;
}

if ($ref<0 && (($show_status_and_access_on_upload== false && $show_access_on_upload == false) || ($show_access_on_upload == false || ($show_access_on_upload == true && !eval($show_access_on_upload_perm)))))            # Upload template and the status and access fields are configured to be hidden on uploads.
   {?>
   <input type=hidden name="access" value="<?php echo htmlspecialchars($resource["access"])?>"><?php
}
else
{
   if ($multiple) { ?><div><input name="editthis_access" id="editthis_access" value="yes" type="checkbox" onClick="var q=document.getElementById('question_access');if (q.style.display!='block') {q.style.display='block';} else {q.style.display='none';}">&nbsp;<label for="editthis<?php echo $n?>"><?php echo $lang["access"]?></label></div><?php } ?>

   <div class="Question" id="question_access" <?php if ($multiple) {?>style="display:none;"<?php } ?>>
      <label for="access"><?php echo $lang["access"]?></label><?php

            # Autosave display
      if ($edit_autosave || $ctrls_to_save) { ?><div class="AutoSaveStatus" id="AutoSaveStatusAccess" style="display:none;"></div><?php }

      $ea0=!checkperm('ea0');
      $ea1=!checkperm('ea1');
      $ea2=checkperm("v")?(!checkperm('ea2')?true:false):false;
      $ea3=$custom_access?!checkperm('ea3'):false;
      if(($ea0 && $resource["access"]==0) || ($ea1 && $resource["access"]==1) || ($ea2 && $resource["access"]==2) || ($ea3 && $resource["access"]==3))
      {
        ?>
        <select class="stdwidth" name="access" id="access" onChange="var c=document.getElementById('custom_access');<?php if ($resource["access"]==3) { ?>if (!confirm('<?php echo $lang["confirm_remove_custom_usergroup_access"] ?>')) {this.value=<?php echo $resource["access"] ?>;return false;}<?php } ?>if (this.value==3) {c.style.display='block';} else {c.style.display='none';}<?php if ($edit_autosave) {?>AutoSave('Access');<?php } ?>">
          <?php
                    if($ea0)    //0 - open
                    {$n=0;?><option value="<?php echo $n?>" <?php if ($resource["access"]==$n) { ?>selected<?php } ?>><?php echo $lang["access" . $n]?></option><?php }
                    if($ea1)    //1 - restricted
                    {$n=1;?><option value="<?php echo $n?>" <?php if ($resource["access"]==$n) { ?>selected<?php } ?>><?php echo $lang["access" . $n]?></option><?php }
                    if($ea2)    //2 - confidential
                    {$n=2;?><option value="<?php echo $n?>" <?php if ($resource["access"]==$n) { ?>selected<?php } ?>><?php echo $lang["access" . $n]?></option><?php }
                    if($ea3)    //3 - custom
                    {$n=3;?><option value="<?php echo $n?>" <?php if ($resource["access"]==$n) { ?>selected<?php } ?>><?php echo $lang["access" . $n]?></option><?php }
                    ?>
                 </select>
                 <?php
              }
              else
              {
                 ?>
                 <label class="stdwidth" id="access"><?php echo $lang["access" .$resource["access"]];?></label>
                 <?php
              }
              ?>
              <div class="clearerleft"> </div>
              <?php
              if($ea3 || $resource["access"]==3)
              {
                 ?>
                 <table id="custom_access" cellpadding=3 cellspacing=3 style="padding-left:150px;<?php if ($resource["access"]!=3) { ?>display:none;<?php } ?>"><?php
                 global $default_customaccess;
                 $groups=get_resource_custom_access($ref);
                 for ($n=0;$n<count($groups);$n++)
                 {
                   $access=$default_customaccess;
                   $editable= (!$ea3)?false:true;
                   if ($groups[$n]["access"]!="") {$access=$groups[$n]["access"];}
                   $perms=explode(",",$groups[$n]["permissions"]);
                   if (in_array("v",$perms)) {$access=0;$editable=false;} ?>
                   <tr>
                      <td valign=middle nowrap><?php echo htmlspecialchars($groups[$n]["name"])?>&nbsp;&nbsp;</td>

                      <td width=10 valign=middle><input type=radio name="custom_<?php echo $groups[$n]["ref"]?>" value="0" <?php if (!$editable) { ?>disabled<?php } ?> <?php if ($access==0) { ?>checked <?php }
                      if ($edit_autosave) {?> onChange="AutoSave('Access');"<?php } ?>></td>

                      <td align=left valign=middle><?php echo $lang["access0"]?></td>

                      <td width=10 valign=middle><input type=radio name="custom_<?php echo $groups[$n]["ref"]?>" value="1" <?php if (!$editable) { ?>disabled<?php } ?> <?php if ($access==1) { ?>checked <?php }
                      if ($edit_autosave) {?> onChange="AutoSave('Access');"<?php } ?>></td>

                      <td align=left valign=middle><?php echo $lang["access1"]?></td><?php

                      if (checkperm("v"))
                        { ?>
                     <td width=10 valign=middle><input type=radio name="custom_<?php echo $groups[$n]["ref"]?>" value="2" <?php if (!$editable) { ?>disabled<?php } ?> <?php if ($access==2) { ?>checked <?php }
                     if ($edit_autosave) {?> onChange="AutoSave('Access');"<?php } ?>></td>

                     <td align=left valign=middle><?php echo $lang["access2"]?></td><?php
                  } ?>
                  </tr><?php
               } ?>
            </table>
            <div class="clearerleft"> </div>
            <?php
         }
         ?>
         </div><?php
      }
   } /* end hook replaceaccessselector */

    # Related Resources
    if ($enable_related_resources && ($multiple || $ref>0)) # Not when uploading
    {
       if ($multiple) { ?><div><input name="editthis_related" id="editthis_related" value="yes" type="checkbox" onClick="var q=document.getElementById('question_related');if (q.style.display!='block') {q.style.display='block';} else {q.style.display='none';}">&nbsp;<label for="editthis<?php echo $n?>"><?php echo $lang["relatedresources"]?></label></div><?php } ?>

       <div class="Question" id="question_related" <?php if ($multiple) {?>style="display:none;"<?php } ?>>
          <label for="related"><?php echo $lang["relatedresources"]?></label><?php

        # Autosave display
          if ($edit_autosave  || $ctrls_to_save) { ?><div class="AutoSaveStatus" id="AutoSaveStatusRelated" style="display:none;"></div><?php } ?>

          <textarea class="stdwidth" rows=3 cols=50 name="related" id="related"<?php
          if ($edit_autosave) {?>onChange="AutoSave('Related');"<?php } ?>><?php

          echo ((getval("resetform","")!="")?"":join(", ",get_related_resources($ref)))?></textarea>

          <div class="clearerleft"> </div>
          </div><?php
       } 
    }


    if($ref > 0 && $delete_resource_custom_access)
    {
       $query = sprintf('
          SELECT rca.user AS user_ref,
          IF(u.fullname IS NOT NULL, u.fullname, u.username) AS user
          FROM resource_custom_access AS rca
          INNER JOIN user AS u ON rca.user = u.ref
          WHERE resource = "%s";
          ',
          $ref
          );
       $rca_users = sql_query($query);
       
       $group_query = sprintf('
          SELECT rca.usergroup AS usergroup_ref,
          u.name AS name
          FROM resource_custom_access AS rca
          INNER JOIN usergroup AS u ON rca.usergroup = u.ref
          WHERE resource = "%s";
          ',
          $ref
          );
       $rca_usergroups = sql_query($group_query);

       ?>
    </div> <!-- end of previous collapsible section -->
    <h2 id="resource_custom_access" <?php echo ($collapsible_sections) ? ' class="CollapsibleSectionHead"' : ''; ?>>Resource custom access</h2>
    <div  id="ResourceCustomAccessSection" <?php echo ($collapsible_sections) ? 'class="CollapsibleSection"' : ''; ?>>
       <script type="text/javascript">
       function removeCustomAccess(ref,type) {
		console.log('<?php echo $baseurl_short; ?>pages/ajax/remove_custom_access.php?resource=<?php echo $ref?>&ref='+ref+'&type='+type);
        jQuery.ajax({
          type: 'POST',
          url: '<?php echo $baseurl_short; ?>pages/ajax/remove_custom_access.php',
          data: {
            ajax: 'true',
            resource: <?php echo $ref; ?>,
            ref: ref,
            type: type
         },
         success: function() {
			 if(type=='user')
				{
				jQuery('#rca_user_' + ref).remove();
				}
			else if (type=='usergroup')
				{
				jQuery('#rca_usergroup_' + ref).remove();	
				}
         }
      });
     }
     </script>
     <div class="Question" id="question_resource_custom_access">
      <label for="res_custom_access"><?php echo $lang['remove_custom_access_users_groups']?></label>
      <!-- table here -->
      <table id="res_custom_access" cellpadding="3" cellspacing="3">
        <tbody>
          <?php
          foreach ($rca_users as $rca_user_info)
			{
             ?>
             <tr id="rca_user_<?php echo $rca_user_info['user_ref'] ?>">
               <td valign="middle" nowrap=""><?php echo $rca_user_info['user']; ?></td>
               <td valign="middle" nowrap="">&nbsp;</td>
               <td width="10" valign="middle">
                 <input type="hidden" name="remove_access_user_ref" value="<?php echo $rca_user_info['user_ref'] ?>">
                 <input type="submit" name="remove_access" value="Remove access" onClick="removeCustomAccess(<?php echo $rca_user_info['user_ref']; ?>,'user'); return false;">
              </td>
           </tr>
           <?php
			}
        foreach ($rca_usergroups as $rca_usergroup_info)
			{
             ?>
             <tr id="rca_group_<?php echo $rca_usergroup_info['usergroup_ref'] ?>">
               <td valign="middle" nowrap=""><?php echo $rca_usergroup_info['name']." (".$lang['group'].")"?></td>
               <td valign="middle" nowrap="">&nbsp;</td>
               <td width="10" valign="middle">
                 <input type="hidden" name="remove_access_usergroup_ref" value="<?php echo $rca_usergroup_info['usergroup_ref'] ?>">
                 <input type="submit" name="remove_access_group" value="Remove access" onClick="removeCustomAccess(<?php echo $rca_usergroup_info['usergroup_ref']; ?>,'usergroup'); return false;">
              </td>
           </tr>
           <?php
        }

                    // Add a default message if no users are attached
        if(count($rca_users) == 0 && count($rca_usergroups) == 0)
        {
          ?>
          <tr>
            <td><?php echo $lang['remove_custom_access_no_users_found']; ?></td>
         </tr>
         <?php
      }
      ?>
   </tbody>
</table>
<!-- end of table -->
<div class="clearerleft"> </div>
</div>
<?php
}

if ($multiple && !$disable_geocoding)
{
    # Multiple method of changing location.
 ?>
</div><h2 <?php echo ($collapsible_sections)?" class=\"CollapsibleSectionHead\"":""?> id="location_title"><?php echo $lang["location-title"] ?></h2><div <?php echo ($collapsible_sections)?"class=\"CollapsibleSection\"":""?> id="LocationSection<?php if ($ref==-1) echo "Upload"; ?>">
<div><input name="editlocation" id="editlocation" type="checkbox" value="yes" onClick="var q=document.getElementById('editlocation_question');if (this.checked) {q.style.display='block';} else {q.style.display='none';}">&nbsp;<label for="editlocation"><?php echo $lang["location"] ?></label></div>
<div class="Question" style="display:none;" id="editlocation_question">
  <label for="location"><?php echo $lang["latlong"]?></label>
  <input type="text" name="location" id="location" class="stdwidth">
  <div class="clearerleft"> </div>
</div>
<div><input name="editmapzoom" id="editmapzoom" type="checkbox" value="yes" onClick="var q=document.getElementById('editmapzoom_question');if (this.checked) {q.style.display='block';} else {q.style.display='none';}">&nbsp;<label for="editmapzoom"><?php echo $lang["mapzoom"] ?></label></div>
<div class="Question" style="display:none;" id="editmapzoom_question">
  <label for="mapzoom"><?php echo $lang["mapzoom"]?></label>
  <select name="mapzoom" id="mapzoom">
    <option value=""><?php echo $lang["select"]?></option>
    <option value="2">2</option>
    <option value="3">3</option>
    <option value="4">4</option>
    <option value="5">5</option>
    <option value="6">6</option>
    <option value="7">7</option>
    <option value="8">8</option>
    <option value="9">9</option>
    <option value="10">10</option>
    <option value="11">11</option>
    <option value="12">12</option>
    <option value="13">13</option>
    <option value="14">14</option>
    <option value="15">15</option>
    <option value="16">16</option>
    <option value="17">17</option>
    <option value="18">18</option>
 </select>
</div>
<div class="clearerleft"> </div>


<?php
hook("locationextras");
} 
?>

<?php

if (!$edit_upload_options_at_top){include '../include/edit_upload_options.php';}
?>


</div>
<?php
if(!hook('replacesubmitbuttons'))
    {
    ?>
    <div class="QuestionSubmit">
    <?php
    global $clearbutton_on_upload;
    if(($clearbutton_on_upload && $ref < 0 && !$multiple) || ($ref > 0 && $clearbutton_on_edit))
        {
        ?>
        <input name="resetform" class="resetform" type="submit" value="<?php echo $lang["clearbutton"]?>" />&nbsp;
        <?php
        }

        $save_btn_value = (0 < $ref) ? $lang['save'] : $lang['next'];
        if(0 > $ref && in_array($resource['resource_type'], $data_only_resource_types))
            {
            $save_btn_value = $lang['create'];
            }
        ?>
        <input <?php if ($multiple) { ?>onclick="return confirm('<?php echo $lang["confirmeditall"]?>');"<?php } ?> name="save" class="editsave" type="submit" value="&nbsp;&nbsp;<?php echo $save_btn_value; ?>&nbsp;&nbsp;" /><br><br>
        <div class="clearerleft"> </div>
    </div>
    <?php 
    }
   
# Duplicate navigation
if (!$multiple && !$modal && $ref>0 &&!hook("dontshoweditnav")) {EditNav();}

if(!$is_template && $show_required_field_label)
    {
    ?>
    <p><sup>*</sup> <?php echo $lang['requiredfield']; ?></p>
    <?php
    } 

if($collapsible_sections)
{
  ?>
</div><!-- end of collapsible section -->
<?php
}
if($multiple){echo "</div>";} ?>
</form>

<script>
// Helper script to assist with AJAX - when 'save' and 'reset' buttons are pressed, add a hidden value so the 'save'/'resetform' values are passed forward just as if those buttons had been clicked. jQuery doesn't do this for us.
 jQuery(".editsave").click(function(){
                jQuery("#mainform").append(
                    jQuery("<input type='hidden'>").attr( { 
                        name: "save", 
                        value: "true" }));}
                );
  jQuery(".resetform").click(function(){
                jQuery("#mainform").append(
                    jQuery("<input type='hidden'>").attr( { 
                        name: "resetform", 
                        value: "true" }));}
                );
   jQuery("#copyfromsubmit").click(function(){
                jQuery("#mainform").append(
                    jQuery("<input type='hidden'>").attr( { 
                        name: "copyfromsubmit", 
                        value: "true" }));}
                );
</script>

<?php
if (isset($show_error) && isset($save_errors) && !hook('replacesaveerror'))
  {
  ?>
  <script type="text/javascript">

  // Find the first field that triggered the error:
  var error_fields;

  error_fields = document.getElementsByClassName('FieldSaveError');
  window.location.hash = error_fields[0].id;

  </script>
  <?php
  foreach ($save_errors as $save_error_field=>$save_error_message)
    {
      ?>
      <script type="text/javascript">
      alert('<?php echo htmlspecialchars($save_error_message) ?>');
      </script><?php
    }
 }

hook("autolivejs");

include "../include/footer.php";
