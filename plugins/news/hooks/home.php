<?php
function HookNewsHomeSearchbarbottomtoolbar()
	{
	global $lang,$site_text,$baseurl;
	include_once dirname(__FILE__)."/../inc/news_functions.php";
	$recent = 3;
	$findtext = "";
	$news = get_news_headlines("",$recent,"");
	$results=count($news);
   	?>


	<div id="SearchBoxPanel" style="margin-top:10px;">
	<div id="HomeSiteText">
        
		
		
	<h1><?php echo $lang['title']; ?></h1>
       
	<?php
		if($results > 0)
			{
			for($n = 0; ($n < $results); $n++)
				{
				?>
				<p>&gt;<a href="<?php echo $baseurl; ?>/plugins/news/pages/news.php?ref=<?php echo $news[$n]['ref']; ?>"><?php echo $news[$n]['title']; ?></a></p>
				<?php
				}
			}
		else
			{
			echo $lang['news_nonewmessages'];
			}
			?>
		</div>
	</div>
	<?php
	}

