<?php
include "../../../include/db.php";
include_once "../../../include/general.php";
include "../../../include/authenticate.php"; if (!checkperm("a")) {exit("Access denied.");}
include "../../../include/header.php";

?>
<div class="BasicsBox"> 
	<h1><?php echo $lang["svncheck"]?></h1>
	<p class="tight"><?php echo $lang["svnintrotext"]?></p><br>
	
	<?php
	include "../../../include/version.php";

	// initialize vars
	$files_modified=false;
	$clean=false; 

	// first, check whether this is an SVN checkout, if not, exit
	if ($productversion!="SVN")
		{ 
		echo "<br/><br/><b>Your installation is not an SVN checkout. If you install ResourceSpace as an SVN checkout, updates to the software would be much easier.</b>";
		}
	else
		{
		// check svn version	
		$svnrevision=shell_exec("svnversion ". $storagedir."/..");	
		$svnrevision=explode(":",$svnrevision);

		if (isset($svnrevision[1]))
			{
			$svnrevision=$svnrevision[1];
			}
		else
			{
			$svnrevision=$svnrevision[0];
			}

		// parse string to check if revision is modified
		if (substr(trim($svnrevision),-1)=="M")
			{
			$files_modified=true;
			}

		// echo versions	
		echo "Your SVN client version: ".svn_client_version();
		echo "<br/>Your ResourceSpace SVN version: ".$svnrevision;

		// next, analyze an svn status report to see if the installation is clean and can be updated using this tool.
		if ($files_modified)
			{
			$svn_status=svn_status($storagedir."/../");
			?>
			<br/>
			<br/>
			<table class="InfoTable">
				<?php
				for ($n=1;$n<count($svn_status);$n++)
					{
					if ($svn_status[$n]["text_status"]==8)
						{
						?>
						<tr>
							<td><?php echo $svn_status[$n]["path"]?></td>
							<td><b><?php echo "modified"?></b></td>
						</tr>
						<?php	
						}
					}
				?>
			</table>
			<?php
			echo "<br/><br/><b>Files in your working copy have been modified as shown above. Updates are disabled to prevent conflicts. It is recommended that you implement your modifications as plugins to enable web-based updates.</b>";
			}
		else
			{
			$clean=true;
			echo "<br/><br/><b>Your working copy is clean at revision $svnrevision. Congratulations! ";

			// flush now as svn_log can take time.
			flush();

			// grab a log to see if updates are available
			$svnlog=svn_log("http://svn.resourcespace.org/svn/resourcespace", SVN_REVISION_HEAD, $svnrevision+0);

			if (count($svnlog)>1)
				{
				echo "<br/><br/>The following Updates are available";
				?>
				<br/>
				<br/>
				<table class="InfoTable">
					<?php

					for ($n=0;$n<count($svnlog);$n++)
						{
						if (isset($svnlog[$n]))
							{
							?>
							<tr>
								<td><a href="svnup.php?rev=<?php echo $svnlog[$n]["rev"]?>">&gt;&nbsp;<?php echo $lang["updatetothisrevision"]?></a></td>
								<td><?php echo $svnlog[$n]["rev"]?></td>
								<td><?php echo $svnlog[$n]["author"]?></td>
								<td><b><?php foreach($svnlog[$n]["paths"]as $svnpath){echo $svnpath["path"];}?></b></td>
								<td nowrap><b><?php echo date("d-m-Y",strtotime($svnlog[$n]["date"]))?></b></td>
								<td><b><?php echo $svnlog[$n]["msg"]?></b></td>
							</tr>
							<?php	
							}
						}	
					?>
				</table>
				<?php
				}
			else
				{
				echo "<br/><br/>You are fully up to date";
				}
			}
		}
	?>
</div>
<?php
include "../../../include/footer.php";
