<?php
#
# PDF Contact Sheet Functionality
#

foreach ($_POST as $key => $value) {$$key = stripslashes(utf8_decode(trim($value)));}

// create new PDF document
include('../../include/db.php');
include('../../include/general.php');
include('../../include/authenticate.php');
include('../../include/search_functions.php');
include('../../include/resource_functions.php');
include('../../include/collections_functions.php');
include('../../include/image_processing.php');

require_once('../../lib/tcpdf/tcpdf.php');
require_once('../../lib/fpdi/fpdi.php');

# Still making variables manually when not using Prototype: 
$collection=getvalescaped("c","");
$size=getvalescaped("size","");
$column=getvalescaped("columns","");
$order_by=getvalescaped("orderby","relevance");
$sort=getvalescaped("sort","asc");
$orientation=getvalescaped("orientation","");
$sheetstyle=getvalescaped("sheetstyle","thumbnails");

$logospace=0;
$footerspace=0;

$contactsheet_header=getvalescaped("includeheader",'');
if ($contactsheet_header==''){$contactsheet_header=$contact_sheet_include_header;}

if (getvalescaped("addlogo",$include_contactsheet_logo)=="true") {$add_contactsheet_logo=true;}else{$add_contactsheet_logo=false;}

$contact_sheet_add_link=getvalescaped("addlink",$contact_sheet_add_link);

if(getvalescaped("preview","")!=""){$preview=true;} else {$preview=false;}
if ($sheetstyle=="single"){$imgsize=getvalescaped("ressize","lpr");}
else{$imgsize="pre";}
$previewpage=getvalescaped("previewpage",1);

if ($preview==true){$imgsize="col";}if ($sheetstyle=="single" && $preview==true){$imgsize="pre";}
if ($size == "a4") {$width=210/25.4;$height=297/25.4;} // convert to inches
if ($size == "a3") {$width=297/25.4;$height=420/25.4;}

if ($size == "letter") {$width=8.5;$height=11;}
if ($size == "legal") {$width=8.5;$height=14;}
if ($size == "tabloid") {$width=11;$height=17;}

#configuring the sheet:
if ($orientation=="L"){
$pagewidth=$pagesize[1]=$height ;
$pageheight=$pagesize[0]=$width;
}else{
$pagewidth=$pagesize[0]=$width;
$pageheight=$pagesize[1]=$height;
}
$date= date("Y-m-d H:i:s");
$leading=2;

# back compatibility  
if (isset($print_contact_title)){
	if ($print_contact_title && empty($config_sheetthumb_fields)){$config_sheetthumb_fields=array(8);}
}

function contact_sheet_add_fields($resourcedata)
	{
	global $pdf, $n, $getfields, $sheetstyle, $imagesize, $refnumberfontsize, $leading, $csf, $pageheight, $currentx, $currenty, $topx, $topy, $bottomx, $bottomy, $logospace, $deltay,$width,$config_sheetsingle_include_ref,$contactsheet_header,$cellsize,$ref,$pagewidth; 
	//exit (print_r($getfields));

	if ($sheetstyle=="single" && $config_sheetsingle_include_ref=="true"){
		$pdf->SetY($bottomy);
		$pdf->MultiCell($pagewidth-2,0,'','','L',false,1);	
		$pdf->ln();
		$pdf->MultiCell($pagewidth-2,0,$ref,'','L',false,1);	
	}


	for($ff=0; $ff<count($getfields); $ff++){
		$value="";
		$value=str_replace("'","\'", $resourcedata['field'.$getfields[$ff]]);
			
		$plugin="../../plugins/value_filter_" . $csf[$ff]['name'] . ".php";
		if ($csf[$ff]['value_filter']!=""){
			eval($csf[$ff]['value_filter']);
			}
		else if (file_exists($plugin)) {include $plugin;}
		$value=TidyList($value);
	
		if ($sheetstyle=="thumbnails") 
			{
			$pdf->Cell($imagesize,(($refnumberfontsize+$leading)/72),$value,0,2,'L',0,'',1);
			//if ($ff==2){echo print_r($getfields) . " " . $pdf->GetY();exit();}
			
			$bottomy=$pdf->GetY();
			$bottomx=$pdf->GetX();
			}
		else if ($sheetstyle=="list")
			{
			
			$pdf->Text($pdf->GetX()+$imagesize+0.1,$pdf->GetY()+(0.2*($ff+$deltay)),$value);
			//$pdf->Text($pdf->GetX()+$imagesize+0.1,$pdf->GetY()+(0.2*($ff+2)),$value);	
			
			//$pdf->Text($pdf->GetX()+$imagesize+0.1,$pdf->GetY()+(0.2*($ff+2)+ 0.15),$value);					
			$pdf->SetXY($currentx,$currenty);
			}
		else if ($sheetstyle=="single")
			{		
			$pdf->MultiCell($pagewidth-2,0,$value,'','L',false,1);		
			}
			
		}
	}
	
function contact_sheet_add_image()
	{	
	global $pdf, $imgpath, $sheetstyle, $imagesize, $pageheight, $pagewidth, $imagewidth, $imageheight, $preview_extension, $baseurl, $contact_sheet_add_link, $ref, $extralines, $refnumberfontsize, $cellsize, $topx, $topy, $bottomy, $align,$thumbsize,$logospace,$width,$contactsheet_header; 
	$nextline="";
	if ($sheetstyle=="single")
		{
		# Centre on page
		//$posx="C";
		$align="C";		
		$nextline="N";
			
			$posx=((($width-2)/2)-($cellsize[0])/2);
			if($contactsheet_header=="true"){
				$posy=1.2 + $logospace;
			} else {
				$posy=0.8 + $logospace;
			}
		}
	elseif ($sheetstyle=="list")
		{
		$posx=$pdf->GetX();
		$posy=$pdf->GetY()+0.025;
		$align="";
		}
	elseif ($sheetstyle=="thumbnails")
		{
		if ($imagewidth==0)
			{$posx=$pdf->GetX()+ $cellsize[0]/2 - ($cellsize[1] * $thumbsize[0])/($thumbsize[1]*2);}
		else
			{$posx=$pdf->GetX();}
		if ($imageheight==0)
			{$posy=$pdf->GetY()+0.025 + $cellsize[1]/2 - ($cellsize[0] * $thumbsize[1])/($thumbsize[0]*2);}
		else
			{$posy=$pdf->GetY()+0.025;}
			$align="";
		}
		
	# Add the image
	if ($contact_sheet_add_link=="true")
		{$pdf->SetMargins(.7,1.2,.7);
		$imageinfo=$pdf->Image($imgpath,$posx,$posy,$imagewidth,$imageheight,$preview_extension,$baseurl. '/?r=' . $ref,$nextline,false,300,$align,false,false,0);
		$pdf->SetMargins(1,1.2,.7);
		}
	else
		{
		$pdf->Image($imgpath,$posx,$posy,$imagewidth,$imageheight,$preview_extension,'',$nextline,false,300,$align,false,false,0);
		}	
			
	$bottomy=$pdf->GetY();
	# Add spacing cell
	if ($sheetstyle=="list")
		{		
		$pdf->Cell($cellsize[0],$cellsize[1],'',0,0);		
		}
	/*else if ($sheetstyle=="single")
		{		
		$pdf->Setx($posx+$cellsize[0]);
		$pdf->Cell($cellsize[0],($bottomy-$topy)+$imagesize+.2,'',0,0);		
		}*/
	else if ($sheetstyle=="thumbnails")
		{			
		$pdf->Setx($topx);
		$pdf->Cell($cellsize[0],($bottomy-$topy)+$imagesize+.2,'',0,0);		
		}
	}

## Sizing calculations
function do_contactsheet_sizing_calculations(){
global $sheetstyle,$deltay,$add_contactsheet_logo,$pageheight,$pagewidth,$column,$config_sheetthumb_fields,$config_sheetthumb_include_ref,$leading,$refnumberfontsize,$imagesize,$columns,$rowsperpage,$cellsize,$logospace,$page,$rowsperpage,$contact_sheet_logo_resize,$contact_sheet_custom_footerhtml,$footerspace,$contactsheet_header,$config_sheetsingle_fields,$config_sheetsingle_include_ref,$orientation;


if ($sheetstyle=="thumbnails")
	{
	if ($add_contactsheet_logo && $contact_sheet_logo_resize)
	{$logospace=$pageheight/9;}

	$columns=$column;
	#calculating sizes of cells, images, and number of rows:
	$cellsize[0]=$cellsize[1]=($pagewidth-1.7)/$columns;
	$imagesize=$cellsize[0]-.3;
	# estimate rows per page based on config lines
	$extralines=(count($config_sheetthumb_fields)!=0)?count($config_sheetthumb_fields):0;
	if ($contact_sheet_custom_footerhtml!=''){$footerspace=$pageheight*.05;}
	if ($config_sheetthumb_include_ref){$extralines++;}
	$rowsperpage=($pageheight-.5-$logospace-$footerspace-($cellsize[1]+($extralines*(($refnumberfontsize+$leading)/72))))/($cellsize[1]+($extralines*(($refnumberfontsize+$leading)/72)));
	$page=1;	
	}
else if ($sheetstyle=="list")
	{ 
	if ($add_contactsheet_logo && $contact_sheet_logo_resize)
	{$logospace=$pageheight/9;}
	#calculating sizes of cells, images, and number of rows:
	$columns=1;
	$imagesize=1.0;
	$cellsize[0]=$pagewidth-1.7;
	$cellsize[1]=1.2;
	if ($contact_sheet_custom_footerhtml!=''){$footerspace=$pageheight*.05;}
	$rowsperpage=($pageheight-1.2-$logospace-$footerspace-$cellsize[1])/$cellsize[1];
	$page=1;
	}
else if ($sheetstyle=="single")
	{
	$extralines=(count($config_sheetsingle_fields)!=0)?count($config_sheetsingle_fields):0;
	if ($add_contactsheet_logo && $contact_sheet_logo_resize)
		{
		if ($orientation=="L"){$logospace=$pageheight/11;if ($contactsheet_header){$extralines=$extralines + 2;}} else {$logospace=$pageheight/9;}
		}
	$columns=$column;	
	if ($config_sheetsingle_include_ref){$extralines++;}
	
	# calculate size of single cell per page, allowing for extra lines. Needs to be smaller if landscape.
	if ($orientation=="L")
		{
		$cellsize[0]=$cellsize[1]=($pageheight*0.65)-($extralines*(($refnumberfontsize+$leading)/72));
		}
	else 
		{
		$cellsize[0]=$cellsize[1]=($pagewidth*0.8);
		}
	$imagesize=$cellsize[0]-0.3;
	$rowsperpage=1;
	$page=1;
	$columns=1;
	}
}
$deltay=1;
do_contactsheet_sizing_calculations();

#Get data
$collectiondata= get_collection($collection);
if (is_numeric($order_by)){ $order_by="field".$order_by;}
//debug("Contact Sheet Sort is $order_by $sort");
$result=do_search("!collection" . $collection,"",$order_by,0,-1,$sort);

if ($sheetstyle=="thumbnails"){$getfields=$config_sheetthumb_fields;}
else if ($sheetstyle=="list"){$getfields=$config_sheetlist_fields;}
else if ($sheetstyle=="single"){$getfields=$config_sheetsingle_fields;}
$csf="";
for ($m=0;$m<count($getfields);$m++)
	{
	$csf_data=sql_query("select name,value_filter, type from resource_type_field where ref='$getfields[$m]'");
	$csf[$m]['name']=$csf_data[0]['name'];
	$csf[$m]['value_filter']=$csf_data[0]['value_filter'];
	$csf[$m]['type']=$csf_data[0]['type'];
	}	

	
$user= get_user($collectiondata['user']);






          

    



	
class MYPDF extends FPDI {

	//Page header
	public function Header() {
		global $logowidth,$contactsheet_header,$contact_sheet_logo,$add_contactsheet_logo,$contact_sheet_font,$titlefontsize,$applicationname,$collectiondata,$date,$subsetting,$lang, $pagewidth,$pageheight, $logospace,$contact_sheet_logo_resize;
		
		if (isset($contact_sheet_logo) && $add_contactsheet_logo)
			{			
			if (file_exists("../../" . $contact_sheet_logo))
				{
				$extension=pathinfo($contact_sheet_logo);$extension=$extension['extension'];
				
				if ($extension=="pdf")
					{  //recommended as it works best with or without $contact_sheet_logo_resize
					$this->setSourceFile("../../" . $contact_sheet_logo);
					$this->_tplIdx = $this->importPage(1);
					$logosize=$this->getTemplateSize($this->_tplIdx );
					if (!$contact_sheet_logo_resize){	
						$logowidth=$logosize['w'];
						$logospace=$logosize['h'];
						do_contactsheet_sizing_calculations(); // run this code again with new logospace
					} 
					else {
						$logoratio=$logosize['w']/$logosize['h'];
						$logowidth=$pageheight/8 * $logoratio;
					}
					$this->useTemplate($this->_tplIdx,$pagewidth/2-($logowidth/2),.4,$logowidth);
				}
				/*else if ($extension=="svg")
					{
						$this->ImageSVG("../../" . $contact_sheet_logo, '', $pageheight/30, '', $pageheight/8,'','',"C");					
					}*/
				else
					{
					if (!$contact_sheet_logo_resize){	
						$logospace = getimagesize("../../" . $contact_sheet_logo);
						$logospace=$logospace[1]/300; 
						$this->Image("../../" . $contact_sheet_logo,'',.5,'',$logospace,$extension,false,'',true,'300','C', false, false, 0, false, false, false);
						do_contactsheet_sizing_calculations(); // run this code again with new logospace
					} else {
						$this->Image("../../" . $contact_sheet_logo,'',$pageheight/30,'',$pageheight/8,$extension,false,'',true,'300','C', false, false, 0, false, false, false);	
						
					}
					}
				}
				else 
				{
				exit("Contact sheet logo file not found at " . $contact_sheet_logo);
				}
			}
			if($contactsheet_header=="true")
			{
			$this->SetFont($contact_sheet_font,'',$titlefontsize,'',$subsetting);
			$title = $applicationname.' - '. i18n_get_collection_name($collectiondata).' - '.nicedate($date,true,true);
			$pagenumber=$this->getAliasNumPage(). " " . $lang["of"] . " " .$this->getAliasNbPages();
			$this->Text(1,$logospace+0.8,$title.'   '.$pagenumber);
			}
		}

	// Page footer
	 public function Footer() {
		 // custom footer avoids linerule
		 global $contact_sheet_custom_footerhtml,  $pageheight, $pagewidth,$refnumberfontsize;
			if ($contact_sheet_custom_footerhtml!='')
				{			
				$this->Line(0.5, $pageheight*.94,$pagewidth - 0.5,$pageheight*.94);
				$this->SetFontSize($refnumberfontsize);
				$this->writeHTMLCell('',$pageheight*0.04,0.5, $pageheight*.95, ($contact_sheet_custom_footerhtml), 0, 0, 0, true,'c', true);
				}
		}
	}

class rsPDF extends MYPDF {

    var $_tplIdx;
}


$pdf = new rsPDF($orientation , 'in', $size, true, 'UTF-8', false); 

$pdf->SetTitle(i18n_get_collection_name($collectiondata).' - '.nicedate($date, true, true));
$pdf->SetAuthor($user['fullname'].' '.$user['email']);
$pdf->SetSubject($applicationname . " - " . $lang["contactsheet"]);
$pdf->SetMargins(1,1.2,.7);
$pdf->SetAutoPageBreak(false);
$pdf->SetCellPadding(0); 
$pdf->AddPage(); 
$pdf->SetFont($contact_sheet_font,'','','',$subsetting);

//$pdf->ln();$pdf->ln();
$pdf->SetFontSize($refnumberfontsize);
if($contactsheet_header=="true"){
	$pdf->SetX(1);$pdf->SetY(1.2 + $logospace);
} else {
	$pdf->SetX(1);$pdf->SetY(0.8 + $logospace);
}

#Begin loop through resources, collecting Keywords too.
$i=0;
$j=0;


for ($n=0;$n<count($result);$n++){
	$ref=$result[$n]["ref"];
	$preview_extension=$result[$n]["preview_extension"];
	$resourcetitle="";
    $i++;
	$currentx=$pdf->GetX();
	$currenty=$pdf->GetY();
	if ($ref!==false){
		# Find image
		# Load access level
		$access=get_resource_access($result[$n]); // feed get_resource_access the resource array rather than the ref, since access is included.
		$use_watermark=check_use_watermark();
		$imgpath = get_resource_path($ref,true,$imgsize,false,$preview_extension,-1,1,$use_watermark);
		if (!file_exists($imgpath) && $preview_extension=="jpg" && $imgsize=='hpr'){$imgpath = get_resource_path($ref,true,'',false,$preview_extension,-1,1,$use_watermark);}
		if (!file_exists($imgpath) && $imgsize!='pre'){$imgpath = get_resource_path($ref,true,'pre',false,$preview_extension,-1,1,$use_watermark);}
		if (!file_exists($imgpath)){
			$imgpath="../../gfx/".get_nopreview_icon($result[$n]['resource_type'],$result[$n]['file_extension'],false,true); 
			$preview_extension=explode(".",$imgpath);
			if(count($preview_extension)>1){
				$preview_extension=trim(strtolower($preview_extension[count($preview_extension)-1]));
			} 
		}	
		if (file_exists($imgpath)){
			# cells are used for measurement purposes only
			# Two ways to size image, either by height or by width.
			$thumbsize=getimagesize($imgpath);			
			if ($thumbsize[0]>$thumbsize[1]){ ################# landscape image
				$imagewidth=$imagesize;
				$imageheight=0;
				if ($sheetstyle=="thumbnails"){
					$topy=$pdf->GetY();	$topx=$pdf->GetX();	
					if ($config_sheetthumb_include_ref){
						$pdf->Cell($imagesize,(($refnumberfontsize+$leading)/72),$ref,0,2,'L',0,'',1);
					}
					##render fields
					contact_sheet_add_fields($result[$n]);
					$bottomy=$pdf->GetY();	
					$bottomx=$pdf->GetX();	
					#Add image
					contact_sheet_add_image();
					$pdf->SetXY($topx,$topy);
					$pdf->Cell($cellsize[0],($bottomy-$topy)+$imagesize+.2,'',0,0);
					
					}				
				else if ($sheetstyle=="list")
					{					
					if ($config_sheetlist_include_ref){
					    $pdf->SetXY($currentx,$currenty);
					    $pdf->Text($pdf->GetX()+$imagesize+0.1,$pdf->GetY(),$ref);
						 $deltay=1;
						}
					$pdf->SetXY($currentx,$currenty);	
					#render fields				
					contact_sheet_add_fields($result[$n]);
					#Add image
					contact_sheet_add_image();					
					}
				else if ($sheetstyle=="single")
					{									
					#Add image
					contact_sheet_add_image();											
					contact_sheet_add_fields($result[$n]);
					}
				}
					
			else
				{ # portrait
				$imagewidth=0;
				$imageheight=$imagesize;
				
				if ($sheetstyle=="thumbnails")
					{
					$topy=$pdf->GetY();	
					$topx=$pdf->GetX();	
					if ($config_sheetthumb_include_ref){
						$pdf->Cell($imagesize,(($refnumberfontsize+$leading)/72),$ref,0,2,'L',0,'',1);
						}
					##render fields
					contact_sheet_add_fields($result[$n]);
					#Add image
					contact_sheet_add_image();
					$pdf->SetXY($topx,$topy);
					$pdf->Setx($topx);
					$pdf->Cell($cellsize[0],($bottomy-$topy)+$imagesize+.2,'',0,0);
					}
				else if ($sheetstyle=="list"){
					
					if ($config_sheetlist_include_ref){
					    $pdf->SetXY($currentx,$currenty);
					    $pdf->Text($pdf->GetX()+$imagesize+0.1,$pdf->GetY()+0.2,$ref);
						$deltay=2;
					}
					$pdf->SetXY($currentx,$currenty);		
					#render fields								
					contact_sheet_add_fields($result[$n]);
					#Add image
					contact_sheet_add_image();
					
					}
				
				else if ($sheetstyle=="single"){					
					#Add image
					contact_sheet_add_image();			
					#render fields	
					
					contact_sheet_add_fields($result[$n]);			
					
					}			
				}
			$n=$n++;
			if ($i == $columns){
					
				$pdf->ln();
				$i=0;$j++;	
				if ($j > $rowsperpage || $sheetstyle=="single"){
					$j=0; 
							
							
					if ($n<count($result)-1){ //avoid making an additional page if it will be empty							
						$pdf->AddPage();
						$pdf->SetX(1);$pdf->SetY(1.2 + $logospace);
					}
				}			
			}
		}
	}
}	

#Make AJAX preview?:
	if ($preview==true && isset($imagemagick_path)) 
		{
		if (file_exists(get_temp_dir() . "/contactsheetrip.jpg")){unlink(get_temp_dir() . "/contactsheetrip.jpg");}
		if (file_exists(get_temp_dir() . "/contactsheet.jpg")){unlink(get_temp_dir() . "/contactsheet.jpg");}
		if (file_exists(get_temp_dir() . "/contactsheet.pdf")){unlink(get_temp_dir() . "/contactsheet.pdf");}
		echo ($pdf->GetPage());
		$pdf->Output(get_temp_dir() . "/contactsheet.pdf","F");
		
		# Set up  
		putenv("MAGICK_HOME=" . $imagemagick_path); 
		putenv("PATH=" . $ghostscript_path . ":" . $imagemagick_path); # Path 

        $ghostscript_fullpath = get_utility_path("ghostscript");
        $command = $ghostscript_fullpath . " -sDEVICE=jpeg -dFirstPage=$previewpage -o -r100 -dLastPage=$previewpage -sOutputFile=" . escapeshellarg(get_temp_dir() . "/contactsheetrip.jpg") . " " . escapeshellarg(get_temp_dir() . "/contactsheet.pdf");
		run_command($command);

        $convert_fullpath = get_utility_path("im-convert");
        if ($convert_fullpath==false) {exit("Could not find ImageMagick 'convert' utility at location '$imagemagick_path'");}

        $command = $convert_fullpath . " -resize ".$contact_sheet_preview_size." -quality 90 -colorspace ".$imagemagick_colorspace." \"".get_temp_dir() . "/contactsheetrip.jpg\" \"".get_temp_dir() . "/contactsheet.jpg\"";
		run_command($command);
		exit();
		}

#check configs, decide whether PDF outputs to browser or to a new resource.
if ($contact_sheet_resource==true){
	$newresource=create_resource(1,0);

	update_field($newresource,8,i18n_get_collection_name($collectiondata)." ".$date);
	update_field($newresource,$filename_field,$newresource.".pdf");

#Relate all resources in collection to the new contact sheet resource
relate_to_collection($newresource,$collection);	

	#update file extension
	sql_query("update resource set file_extension='pdf' where ref='$newresource'");
	
	# Create the file in the new resource folder:
	$path=get_resource_path($newresource,true,"",true,"pdf");
	
	$pdf->Output($path,'F');

	#Create thumbnails and redirect browser to the new contact sheet resource
	create_previews($newresource,true,"pdf");
	redirect($baseurl_short."pages/view.php?ref=" .$newresource);
	}

else{ 
	$out1 = ob_get_contents();
	if ($out1!=""){
	ob_end_clean();
	}
$pdf->Output(i18n_get_collection_name($collectiondata).".pdf","D");
}
