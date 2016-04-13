<?php
include '../../include/db.php';
include_once '../../include/general.php';
include '../../include/authenticate.php'; if(!checkperm('a')) { exit('Permission denied.'); }

include "../../include/header.php";

# Baseline metrics (reported metrics are a percentage of these)
# Based on an Intel NUC with a quad core Intel Core i5-4250U CPU @ 1.30GHz, 8GB RAM and a Samsung SSD hard disk (Dan's desktop PC)
$mysql_baseline=85;
$cpu_baseline=970410;
$disk_baseline=20558;
?>
<div class="BasicsBox">
<p>&gt; <a href="admin_system_performance.php?reload=<?php echo time() ?>" onClick="return CentralSpaceLoad(this,false);"><?php echo $lang["reload"] ?></a></p>

<h1><?php echo $lang["system_performance"] ?></h1>

<?php
# Database read/write speed
sql_query("drop table if exists performance_test");
sql_query("create table performance_test (c int(11),d char(64))");
$timer=microtime(true);$counter=0;
while (microtime(true)<($timer+1)) // Run for one second
    {
    $d=escape_check(md5(microtime()));
    sql_query("insert into performance_test(c,d) values ('$counter','" . $d . "')");
    sql_query("select * from performance_test where c='$counter'");
    $counter++;
    }
sql_query("drop table if exists performance_test");
?>
<div class="Question">
<label><?php echo $lang["mysql_throughput"] ?></label><div class="Fixed"><?php echo round(($counter/$mysql_baseline) * 100,1) ?></div>
<div class="clearerleft"></div>
</div>


<?php
# CPU speed
$timer=microtime(true);$counter=0;
while (microtime(true)<($timer+1)) // Run for one second
    {
    $x=md5(microtime());
    $counter++;
    }
?>
<div class="Question">
<label><?php echo $lang["cpu_benchmark"] ?></label><div class="Fixed"><?php echo round(($counter/$cpu_baseline) * 100,1) ?></div>
<div class="clearerleft"></div>
</div>



<?php
# Disk write test
$tmp=get_temp_dir();
$timer=microtime(true);$counter=0;
$f=fopen($tmp . "/performance_test.txt", "w");
while (microtime(true)<($timer+1)) // Run for one second
    {
    fwrite($f,str_pad("",10000,"X"));
    $counter++;
    }
fclose($f);
unlink($tmp . "/performance_test.txt");
?>
<div class="Question">
<label><?php echo $lang["disk_write_speed"] ?></label><div class="Fixed"><?php echo round(($counter/$disk_baseline) * 100,1) ?></div>
<div class="clearerleft"></div>
</div>




</div>
<?php
include "../../include/footer.php";
?>