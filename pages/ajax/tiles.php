<?php
include_once "../../include/general.php";

# from
# http://wiki.openstreetmap.org/wiki/ProxySimplePHP
# The main benefit is for SSL sites which don't want to be making HTTP calls which result in content warnings


    $tilecache=get_temp_dir()."/tiles";

    $ttl = 86400; //cache timeout in seconds

    $x = intval($_GET['x']);
    $y = intval($_GET['y']);
    $z = intval($_GET['z']);
    $r = strip_tags($_GET['r']);

    $file = $tilecache."/${z}_${x}_$y.png";

    if (!is_file($file) || filemtime($file)<time()-(86400*30))
    {
      $server = array();
      switch ($r)
      {
        case 'mapnik':
          $server[] = 'a.tile.openstreetmap.org';
          $server[] = 'b.tile.openstreetmap.org';
          $server[] = 'c.tile.openstreetmap.org';

          $url = 'http://'.$server[array_rand($server)];
          $url .= "/".$z."/".$x."/".$y.".png";
          break;

      }
      $ch = curl_init($url);
      $fp = fopen($file, "w");
      curl_setopt($ch, CURLOPT_FILE, $fp);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      $wait=curl_exec($ch);
      curl_close($ch);
      fflush($fp);    // need to insert this line for proper output when tile is first requestet
      fclose($fp);
		//debug("CURL ". $url);
    }

    $exp_gmt = gmdate("D, d M Y H:i:s", time() + $ttl * 60) ." GMT";
    $mod_gmt = gmdate("D, d M Y H:i:s", filemtime($file)) ." GMT";
    header("Expires: " . $exp_gmt);
    header("Last-Modified: " . $mod_gmt);
    header("Cache-Control: public, max-age=" . $ttl * 60);
    // for MSIE 5
    header("Cache-Control: pre-check=" . $ttl * 60, FALSE);  
    header ('Content-Type: image/png');
    readfile($file);

	exit();
