<?php
#
# ResourceSpace Analytics - draw a graph
#
include '../../../include/db.php';
include '../../../include/authenticate.php';
include '../../../include/reporting_functions.php';

$report=getvalescaped("report","");
$activity_type=getvalescaped("activity_type","");
$period=getvalescaped("period",$reporting_periods_default[1]);
$period_init=$period;
if ($period==0)
	{
	# Specific number of days specified.
	$period=getvalescaped("period_days","");
	if (!is_numeric($period) || $period<1) {$period=1;} # Invalid period specified.
	}
if ($period==-1)
	{
	# Specific date range specified.
	$from_y = getvalescaped("from-y","");
	$from_m = getvalescaped("from-m","");
	$from_d = getvalescaped("from-d","");
	
	$to_y = getvalescaped("to-y","");
	$to_m = getvalescaped("to-m","");
	$to_d = getvalescaped("to-d","");
	}
else
	{
	# Work out the from and to range based on the provided period in days.
	$start=time()-(60*60*24*$period);

	$from_y = date("Y",$start);
	$from_m = date("m",$start);
	$from_d = date("d",$start);
		
	$to_y = date("Y");
	$to_m = date("m");
	$to_d = date("d");
	}
$groups=getvalescaped("groups","");
$collection=getvalescaped("collection","");
$external=getvalescaped("external","0");
$n=getvalescaped("n","");
$type=getvalescaped("type","");
$from_dash=getval("from_dash","")!="";

# Rendering a tile? Set "n" or the graph sequence number to the tile number, so all graph IDs are unique on the dash page.
$tile=getvalescaped("tile","");
if ($tile!="")
    {
    $n=$tile;
    }

$condition="where
activity_type='$activity_type' and 
(
d.year>$from_y
or 
(d.year=$from_y and d.month>$from_m)
or
(d.year=$from_y and d.month=$from_m and d.day>=$from_d)
)
and
(
d.year<$to_y
or 
(d.year=$to_y and d.month<$to_m)
or
(d.year=$to_y and d.month=$to_m and d.day<=$to_d)
)";
if ($groups!="") {$condition.=" and d.usergroup in ('" . join("','",explode(",",$groups)) . "')";}

$join="";
# Using a subquery has proven to be faster for collection limitation (at least with MySQL 5.5 and MyISAM)... left the original join method here in case that proves to be faster with MySQL 5.6 and/or a switch to InnoDB.
#if ($collection!="") {$join.=" join collection_resource cr on cr.collection='$collection' and d.object_ref=cr.resource ";}
if ($collection!="") {$condition.=" and d.object_ref in (select cr.resource from collection_resource cr where cr.collection='$collection')";}

# External conditions
# 0 = external shares are ignored
# 1 = external shares are combined with the user group of the sharing user
# 2 = external shares are reported as a separate user group
if ($external==0)
    {
    $condition.=" and external=0";
    }

    
if (!$from_dash)
    {
    $title=get_translated_activity_type($activity_type) . " " . $lang["report-graph-by-" . $type];
    ?>
    <h2><?php echo $title ?>
    
    <?php
    # Add to dash tile function
    $graph_params="activity_type=" . urlencode($activity_type) . "&groups=" . urlencode($groups) . "&from-y=" . $from_y . "&from-m=" . $from_m ."&from-d=" . $from_d . "&to-y=" . $to_y . "&to-m=" . $to_m ."&to-d=" . $to_d . "&period=" . getvalescaped("period","") . "&period_days=" . getvalescaped("period_days",""). "&collection=" . $collection . "&external=" . $external . "&type=" . urlencode($type) . "&from_dash=true";
    ?>
    &nbsp;&nbsp;<a style="white-space:nowrap;" class="ReportAddToDash" href="<?php echo $baseurl_short ?>pages/dash_tile.php?create=true&title=<?php echo urlencode($title) ?>&nostyleoptions=true&link=<?php echo urlencode("pages/team/team_analytics_edit.php?ref=" . $report)?>&url=<?php echo urlencode("pages/team/ajax/graph.php?" . $graph_params) ?>" onClick="return CentralSpaceLoad(this,true);"><i class="fa fa-plus-square"></i>&nbsp;<?php echo  $lang["report_add_to_dash"] ?></a>
    </h2>
    <?php
    }
else
    {
    # Dash 
    # Load title
    $title= getvalescaped("tltitle",sql_value("select title value from dash_tile where ref='$tile'",""));
    ?>
    <div style="padding:10px 15px">
    <h2 style="font-size:120%;margin:0;padding:0 0 8px 0;background:none;white-space: nowrap;overflow: hidden;
  text-overflow: ellipsis;"><?php echo $title ?></h2>
    <?php
    }
?>
    
    <?php if ($type!="summary") { ?><div id="placeholder<?php echo $type . $n ?>"
    
    <?php if ($from_dash) { ?>
    style="width:220px;height:120px;"
    <?php } else { ?>
    style="width:100%;height:80%;"
    <?php } ?>
    
    ></div><?php } ?>

    <?php if ($type=="pie") {
    
    if ($activity_type=="Keyword usage" || $activity_type=="Keyword added to resource")
        {
        $join_table="keyword";
        $join_display="keyword";
        }
    if ($activity_type=="User session")
        {
        $join_table="user";
        $join_display="fullname";
        }
        
    $data=sql_query("select d.object_ref,j." . $join_display . " name,sum(count) c from daily_stat d join $join_table j on d.object_ref=j.ref $join $condition group by object_ref,j." . $join_display . " order by c desc limit 50");
    # Work out total so we can add an "other" block.
    $total=sql_value("select sum(count) value from daily_stat d $join $condition",0);
    if (count($data)==0) { ?><p><?php echo $lang["report_no_data"] ?></p><script>jQuery("#placeholder<?php echo $type . $n ?>").hide();</script><?php exit();}
    ?>
    <script type="text/javascript"> 
    jQuery(function () {
	
	jQuery.plot('#placeholder<?php echo $type . $n ?>', [

                <?php
                $rt=0;
                foreach ($data as $row) { $rt+=$row["c"];?>{data:<?php echo $row["c"] ?>,label:"<?php echo $row["name"]  ?>"},<?php } ?>

                <?php if ($total>$rt)
                    {
                    # The total doesn't match, some rows were truncated, add an "Other".
                    ?>
                    {data:<?php echo $total-$rt ?>,label:"Other",color: "#999"}
                    <?php
                    }
                    ?>
                
		], {
	series: {
	    pie: {
		show: true,
		label: {
                show: false
		},
		stroke: { width: 0 }
	    }
	}
	,
	grid: {
	    hoverable: true,borderWidth:0
	},
	legend: {
        show: false
	},
    tooltip: {
        show: true,
        content: '%p.0%, %s',
        shifts: {
            x: 20,
            y: 0
        }
    }

    });
    });

    </script>
    <?php } ?>
    
    
    
    
    
    
    
    <?php if ($type=="piegroup") {

    # External conditions
    # 0 = external shares are ignored
    # 1 = external shares are combined with the user group of the sharing user
    # 2 = external shares are reported as a separate user group

    # External mode 2 support - return the usergroup as '-1' if externally shared
    $usergroup_resolve="d.usergroup";
    $name_resolve="ug.name";
    if ($external==2)
	{
	$usergroup_resolve="if(d.external=0,d.usergroup,-1)";
        $name_resolve="if(d.external=0,ug.name,'" .$lang["report_external_share"] . "')";
	}
    $data=sql_query("select $usergroup_resolve as usergroup,$name_resolve as `name`,sum(count) c from daily_stat d left outer join usergroup ug on d.usergroup=ug.ref $join $condition group by $usergroup_resolve, $name_resolve order by c desc");
    if (count($data)==0) { ?><p><?php echo $lang["report_no_data"] ?></p><script>jQuery("#placeholder<?php echo $type . $n ?>").hide();</script><?php exit(); }
    ?>
    <script type="text/javascript"> 
    jQuery(function () {
	
	jQuery.plot('#placeholder<?php echo $type . $n ?>', [

                <?php foreach ($data as $row) { ?>{data:<?php echo $row["c"] ?>,label:"<?php echo $row["name"]  ?>"},<?php } ?>

		], {
	series: {
	    pie: {
		show: true,
		label: {
                show: false
		},
		stroke: { width: 0 }
	    }
	}
	,
	grid: {
	    hoverable: true
	},
	legend: {
        show: false
	},
    tooltip: {
        show: true,
        content: '%p.0%, %s',
        shifts: {
            x: 20,
            y: 0
        }
    }

    });
    });

    </script>
    <?php } ?>
    
    
    
    
    
    
    

    
    <?php if ($type=="line")
        {
        $data=sql_query("select unix_timestamp(concat(year,'-',month,'-',day))*1000 t,sum(count) c from daily_stat d $join $condition group by year,month,day order by t");
        if (count($data)==0) { ?><p><?php echo $lang["report_no_data"] ?></p><script>jQuery("#placeholder<?php echo $type . $n ?>").hide();</script><?php exit(); }
        
	# Find zero days and fill in the gaps

	$day_ms=(60*60*24*1000); # One day in milliseconds.
	$last_t=(strtotime($from_y . "-" . $from_m . "-" . $from_d) * 1000) -$day_ms;
	$newdata=array();
	#$last_t=0;
	foreach ($data as $row)
	    {
	    if ($row["t"]>0)
		{
		if ($last_t!=0 && ($row["t"]-$last_t)>$day_ms)
		    {
		    for ($m=$last_t+$day_ms;$m<$row["t"];$m+=$day_ms)
			{
			$newdata[(string)$m]=0;
			}
		    }
		$newdata[$row["t"]]=$row["c"];
		$last_t=$row["t"];
		}
	    }
	?>
        <script type="text/javascript"> 
jQuery(function () {
                        
    jQuery.plot("#placeholder<?php echo $type . $n ?>", [
    
        
    	    {
        data: [
        <?php foreach ($newdata as $t=>$c) { ?>
        [<?php echo $t ?>,<?php echo $c ?>],
        <?php } ?>
         ],
        label: "<?php echo get_translated_activity_type($activity_type) ?>",
        lines: { show: true  },
        points: { show: false},
        shadowSize: 4,
        <?php if ($from_dash) { ?>color: "#fff"<?php } else { ?>color: "#0be"<?php } ?>
    },
           
    ],
    	{
	<?php if (!$from_dash) { ?>
    	xaxis: { mode: "time", timeformat: "%Y-%m-%d", ticks: 10,  minTickSize: [1, "day"],
        min: <?php echo strtotime($from_y . "-" . $from_m . "-" . $from_d) * 1000 ?>,
        max: <?php echo strtotime($to_y . "-" . $to_m . "-" . $to_d) * 1000 ?>
        },
	<?php } else { ?>
	xaxis: { show: false },
	<?php } ?>
	    legend: {show: false },
	    grid: { <?php if (!$from_dash) { ?>hoverable: true, clickable: true, backgroundColor: "#fff", <?php } ?> borderWidth: <?php echo $from_dash?0:2 ?>, autoHighlight: true }
    	}
    
    );
    <?php if (!$from_dash) { ?>
        jQuery("<div id='tooltip<?php echo $type . $n ?>'></div>").css({
	       position: "absolute",
	       display: "none",
	       border: "1px solid #fdd",
	       padding: "2px",
	       "background-color": "#fee",
	       opacity: 0.80
       }).appendTo("body");

       jQuery("#placeholder<?php echo $type . $n ?>").bind("plothover", function (event, pos, item) {

       jQuery("#UICenter").on("scroll",function () { jQuery("#tooltip<?php echo $type . $n ?>").hide();});

        
        
        if (item) {
                var x = item.datapoint[0], y = item.datapoint[1].toFixed(0);
                var d = new Date(x);
                
                jQuery("#tooltip<?php echo $type . $n ?>").html(d.toDateString() + " = " + y)
                        .css({top: item.pageY+5, left: item.pageX+5})
                        .fadeIn(200);
                        
        } else {
                jQuery("#tooltip<?php echo $type . $n ?>").hide();
        }
       }
    );
    <?php }  else  {
    
    # Specific from dash styling
    ?>    
    jQuery(".flot-text").css("color","#ddd");
    <?php } ?>
    
    });

</script> 
        <?php
        }
    ?>
    
    
<?php if ($type=="summary") {
$cells=3;
$cellwidth=100/$cells;
if ($from_dash)
    {
    # Define styles locally for dash display
    ?>
    <style>
    .ReportSummary td {padding:3px 0 3px 0;vertical-align:bottom;display:block;width:45%;}
    .ReportMetric {font-size:200%;padding-left:5px;}
    </style>
    <?php
    }

?>
<table style="width:100%;" class="ReportSummary">
<tr>
<td width="<?php echo $cellwidth ?>%"><?php echo $lang["report_total"]   ?> <span class="ReportMetric"><?php echo sql_value("select ifnull(format(sum(count),0),0) value from daily_stat d $join $condition",0); ?></span></td>
<td width="<?php echo $cellwidth ?>%"><?php echo $lang["report_average"] ?> <span class="ReportMetric"><?php echo sql_value("select ifnull(format(avg(c),1),0) value from (select year,month,day,sum(count) c from daily_stat d $join $condition group by year,month,day) intable",0); ?></span></td>
</table>    
<?php } ?>


<?php if ($from_dash) { ?>
</div>
<?php } ?>
