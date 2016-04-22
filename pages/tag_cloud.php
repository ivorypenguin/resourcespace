<?php
include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php";
$type=getvalescaped("type","Keyword usage");

function tag_cloud($year=-1,$type="Keyword usage")
	{
	$q="";if ($year!=-1) {$q="and daily_stat.year='$year'";}
	$tags=sql_query("select sum(count) c,keyword from daily_stat left join keyword on object_ref=keyword.ref where activity_type='$type' $q group by object_ref order by c desc limit 150;");
	$t=array();
	for ($n=0;$n<count($tags);$n++)
		{
		$keyword=$tags[$n]["keyword"];
		if (!is_numeric(substr($keyword,0,1))) {$t[$keyword]=$tags[$n]["c"];}
		}
	ksort($t);return ($t);
	}

include "../include/header.php";
?>


<div class="BasicsBox"> 
  <h1><?php echo $lang["tagcloud"]?></h1><p><?php echo $lang["tagcloudtext"]?></p>
</div>

<div class="RecordBox">
<div class="RecordPanel">  
<div class="RecordResouce">

<?php
$tags=tag_cloud(-1,$type);
$max=max($tags);$min=min($tags);$range=$max-$min;if ($range==0) {$range=1;}
foreach($tags as $tag=>$count)
	{
	$fs=10+floor((($count-$min)/$range)*35)
	?><span style="font-size:<?php echo $fs?>px;padding:1px;"><a href="<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode($tag)?>&resetrestypes=1"><?php echo str_replace(" ","&nbsp;",$tag)?></a></span> <?php
	}
?>
</div>
</div>
<div class="PanelShadow"></div>
</div>

<?php
include "../include/footer.php";
?>
