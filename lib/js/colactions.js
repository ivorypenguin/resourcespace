<script type="text/javascript">

function basename(path) {
    return path.replace(/\\/g,'/').replace( /.*\//, '' );
}

<?php 
// value for each option provides the action to perform in detail:

// ref - for multiselector pages, colactionselect needs to have a collection number suffixed. Always include
// confirmation [0 or string] - accept or reject the action (should be a valid lang)
// actionpage - [0 or string] - this page will be executed via ajax (optional, only if you need a background action)
// redirect - [0 or string] - redirect to this page after completion of the action
// frame [main or collections] - which frame to redirect to (main or collections)
// refresh frames [collections,main,both or false]
if (checkperm("b")||$frameless_collections||$ajax_collections){$window="";$colwindow="";} else {$window="top.main.";$colwindow="top.collections.";}

?>

function colAction(value){
	//console.log(value);
	if (value==""){return false;} // spacers
	var value=value.split("|");
	if (value[0]=="custom"){eval(value[1]);return false;}/*override this system and use custom js */
	var confirmaction=value[1]; 
	var confirmed=true;
	var ajaxrequest=value[2]; 

	if (value[1]!="0"){
		if (!confirm(confirmaction)){ 
			<?php if ($pagename!="collection_manage" && $pagename!="collection_public" && $pagename!="themes"){?>colactions.<?php } ?>colactionselect.value='';confirmed=false;return false;
		}
		else {
			confirmed=true;
		}
	}

	if (confirmed){
		if (value[2]!="0"){
			var wait= jQuery.ajax(value[2],{async:false});
		}
		if (value[5]=='collections'){ 
			<?php if (!checkperm("b")&& !$ajax_collections){?>
			<?php echo $colwindow?>location.replace("<?php echo $baseurl?>/pages/collections.php?ref="+value[0]);
			<?php } ?>
		}
		if (value[5]=='main'){ 
			<?php echo $window?>location.reload();
		}
		if (value[5]=='both'){ 
			<?php if (!checkperm("b") && !$ajax_collections){?>
			<?php echo $colwindow?>location.replace("<?php echo $baseurl?>/pages/collections.php?ref="+value[0]);
			<?php } ?>
			<?php echo $window?>location.reload();
		}
		if (value[3]!="0" && value[4]=="main"){
			// exceptions to CentralSpaceLoad
			if (
			basename(<?php echo $window?>location.href).substr(0,11)!="preview.php" 
			&& 
			basename(<?php echo $window?>location.href).substr(0,15)!="preview_all.php" 
			&& 
			basename(<?php echo $window?>location.href).substr(0,9)!="index.php" 
			&&
			basename(value[3]).substr(0,11)!="preview.php"
			&& 
			basename(value[3]).substr(0,15)!="preview_all.php"
			)
			{
				CentralSpaceLoad(value[3],true);
			} 
				
			else {
				<?php echo $window?>location.href=value[3];
				}
			}
		else {
			<?php if (!checkperm("b") && !$frameless_collections  && !$ajax_collections){?>
			<?php echo $colwindow?>location.href=value[3];	
			<?php } else { ?>
			CollectionDivLoad(value[3]);	
			<?php
			} 	?>
		}
	}
}			
</script>
