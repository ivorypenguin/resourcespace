<?php
#
# Script to update resource_dimensions table for all resources.

include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php"; if (!checkperm("a")) {exit("Permission denied");}

set_time_limit(0);

if(!$exiftool_resolution_calc){
	die("Please turn on the exiftool resolution calculator in your config.php file.");
}
else{
	$exiftool_fullpath = get_utility_path("exiftool");
	if($exiftool_fullpath==false){
		die("Could not find exiftool. Aborting...");
	}
	else{
		# get all resources in the DB
		$resources=sql_query("select ref,field".$view_title_field.",file_extension from resource where ref>0 order by ref");

		foreach($resources as $resource){
			$resource_path=get_resource_path($resource['ref'],true,"",false,$resource['file_extension']);
			if (file_exists($resource_path) && !in_array($resource['file_extension'],$exiftool_no_process)){
				$resource=get_resource_data($resource['ref']);
				$command = $exiftool_fullpath . " -s -s -s -t -composite:imagesize -xresolution -resolutionunit " . escapeshellarg($resource_path);
				$dimensions_resolution_unit=explode("\t",run_command($command));
				
				# if anything was extracted, update the database.
				if (count($dimensions_resolution_unit)>=1 && $dimensions_resolution_unit[0]!=''){
					# check db for existing record
					$delete=sql_query("delete from resource_dimensions where resource=".$resource['ref']);
					# break down the width and height
					$wh=explode("x",$dimensions_resolution_unit[0]);
					if(count($wh)>1){
						$width=$wh[0];
						$height=$wh[1];
						$filesize=filesize_unlimited($resource_path);
						$sql_insert="insert into resource_dimensions (resource,width,height,file_size";
						$sql_values=" values('".$resource['ref']."','$width','$height','$filesize'";
					
					
						if(count($dimensions_resolution_unit)>=2){
							$resolution=$dimensions_resolution_unit[1];
							$sql_insert.=",resolution";
							$sql_values.=",'$resolution'";
							
							if(count($dimensions_resolution_unit)>=3){
								$unit=$dimensions_resolution_unit[2];
								$sql_insert.=",unit";
								$sql_values.=",'$unit'";
							}
						}
						
						$sql_insert.=")";
						$sql_values.=")";
						$sql=$sql_insert.$sql_values;
						$wait=sql_query($sql);
						
						echo "Ref: ".$resource['ref']." - ".$resource['field'.$view_title_field]." - updating resource_dimensions record.<br/>";
					}
				}
			}
		}
	}
 }
