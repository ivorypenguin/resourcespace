<?php
include "../../include/db.php";
include_once "../../include/general.php";
include "../../include/authenticate.php";

#handle posts
if (array_key_exists("userfile",$_FILES))
    {
    #file uploads
    $filename=strtolower(str_replace(" ","_",$_FILES['userfile']['name']));
    $filepath="../assets/" . $filename;
    if ($filename!="")
    	{
	    $result=move_uploaded_file($_FILES['userfile']['tmp_name'], $filepath);
    	if ($result==false)
       	 	{
       	 echo "<div class=propbox>File upload error. File too large?<br><br><pre>";
       	 print_r($_FILES);
       	 echo "</pre></div><br><br>";
       	 	}
     	else
     		{
     		?>
     		<script type="text/javascript">
     		top.right.document.getElementById("<?php echo getval("callback","")?>").value="<?php echo $filename?>";
     		</script>
			<?php
    	 	}
    	}
    }
?>

<html><body bgcolor="#dddddd">
<form enctype="multipart/form-data" method="post">
<input type="hidden" name="MAX_FILE_SIZE" value="2000000">
<label for="uploader">Upload file</label>
<input id="uploader" name="userfile" type="file" size=30>
<div align=right style="margin-top:10px;margin-bottom:0px;"><input type="submit" name="upload" value="Upload File"></div>
</form>

</body>
</html>
