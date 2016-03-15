<?php function HookSvnCheckAddinstallationcheck(){

# Check SVN function
if (function_exists('svn_status')) 
	{
	$result="<a href='../plugins/svn/pages/svn.php'>&gt; Check for Updates</a>";
	}
else
	{
	$result="FAIL";
	}
?><tr><td colspan="2">SVN PHP extension installed (php5-svn)</td><td><b><?php echo $result?></b></td></tr>
<?php
}
