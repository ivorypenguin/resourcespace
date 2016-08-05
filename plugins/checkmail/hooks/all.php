<?php 

function HookCheckmailAllAdditional_title_pages_array(){
        return array("upload");
}
function HookCheckmailAllAdditional_title_pages(){
        global $pagename,$lang,$applicationname;
        switch($pagename){
			case "upload":
				$url=explode("/",$_SERVER['REQUEST_URI']);
				if($url[1]=="plugins" && $url[2]=="checkmail"){
					$pagetitle=$lang['uploadviaemail'];
				}
                break;
		}
        if(isset($pagetitle)){
                echo "<script language='javascript'>\n";
                echo "document.title = \"$applicationname - $pagetitle\";\n";
                echo "</script>";
        }
}
