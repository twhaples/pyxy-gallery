<?php pyxyGo(); /* <-- action dispatcher */ 
/*
	You should not edit this file. If you make changes to this file,
	and upgrade to a new version later, you will lose your changes.
	Instead, save a copy of the generated page to the file 'index.html'
	and edit that, instead. Pyxy will serve the contents of index.html
	automatically, if it is	available, instead of the default page.
	
 
 */

IfModSince(0); # If-Modified-Since
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<script type="text/javascript" src="moo.js"></script>
<script type="text/javascript" src="pyxy.js"></script>
<style type="text/css">
/* You should probably put this CSS in a separate file if you're editing this page. */
 body {
	/* background color */
	background: #232;
	color: #aca; 
	}
 #pyxy table, 
 #pyxy table caption,
 #pyxy table tbody tr th,
 #pyxy table tbody tr td { 
	/* table settings */
	background: #898; 
	text-align: center;
	font-family: sans-serif;
	} 

 #pyxy table { width: 700px;} 
 #pyxy td { 
	width: 25%;   /* change this if you use more/less than 4 rows */
	vertical-align: top;
	border: #565 1px solid;
	} 
	
 #pyxy * a:hover, #pyxy * a:active {
	  color: #cfc; /* eek ozone */
	  }
 #pyxy * a, 
 #pyxy * a:visited,
 #pyxylightCaption a,
 #pyxylightCaption a:visited {
    /* navigation links */
	color: #121;
	text-decoration: none;
	font-weight: bold;
	margin-left: 2pt;
	margin-right: 2pt;

	} 
 #pyxy * b { /* navigation 'current' */
	color: white;
	margin-left: 2pt;
	margin-right: 2pt;
	} 
 
 #pyxy * a img { border: 1px #121 solid; margin: 0px;}
 	
 #pyxy * div { margin: 0px; padding: 0px; } /* caption container */
 #pyxy * p,
 #pyxylightCaption p { /* captions */
	color: black;
	margin: 0px;
	padding: 0px;
	text-align: center;
	}
 #pyxylightCaption { text-align: center; }
	
 #pyxy table caption, #pyxy table { /* center these */
	 margin-left: auto; margin-right: auto;
	 }
 #pyxy th {
	 font-weight: normal;
	 border: 1px solid black;
	 }
 
 
 
 
 
 #pyxy * th .nav-prev { float: left; }
 #pyxy * th .nav-next { float: right; }

 /* the rest of the page */
 #top,#bottom { text-align: center; width: 600px; margin-left: auto; margin-right: auto;}
 a { color: #ccf; }
 
 
 #pyxylight {
	background-color:#898;
	}
 #overlay {
	background-color: #010;
	}
 
</style>
<title><?php echo($title);?></title>
</head>
<body>
  <div id="top">
	<h1><?php echo($title);?></h1>
	<p>BETA BETA BETA BETA</p>
  </div>
  <div id="pyxy">
		<noscript>
			<p> We're sorry. This gallery requires JavaScript.<br/>
				Please accept our humble apologies. (At least it's not
				Flash!)</p>
		</noscript>
  </div>
  <script>new pyxyGallery("pyxy");</script>
  <div id="bottom">
	<p>	This is a <a href="http://fennecfoxen.org/pyxy/gallery">Pyxy v2.0 BETA</a> gallery.
		If you want to customize this page, 
		save a copy to the file 'index.html' 
			in the same directory as this file,
		and edit it.</p>
  </div>
</body></html><?php
exit;

function pyxyGo(){
	if(!isset($_REQUEST['act'])){ # default action
		if(is_readable("index.html")){
			IfModSince(filemtime("index.html"));
			echo(readfile("index.html"));
			exit;
		}
		global $title;
		$title = getTitle();
		return;
	} elseif(($act=$_REQUEST['act']) == "json") {
		pyxyJson();
	} elseif($act == "resize"){
		pyxyResize();
	}
	exit;
}

function pyxyJson(){
	$dir = loadDir();
	IfModSince($dir['lastmod']);
	header("Content-type: text/plain; chaset=UTF-8");
	echo(getJSON($dir));
}


function loadDir($dir = '.'){
	$lastmod = 0;
	$dirs = Array();
	$imgs = Array();
	
	$dh = opendir($dir);
	while (($file = readdir($dh)) !== false) {
		if(is_dir($file)){ $dirs[] = $file; }
		elseif(is_readable($file)){
			$ext = getValidExtension($file);
			if($ext){
				$when = filemtime($file);
				$imgs[$file] = $when;
				if($when > $lastmod){ $lastmod = $when; }
			} # end if ft
		}# end elseif
	} #end while
	closedir($dh);
	
	return array("name" => $dir,
				"lastmod" => $lastmod,
				"images" => $imgs,
				"dirs" => $dirs);

} #end load_dir

function getValidExtension($file){
	foreach(array("jpg","png","gif") as $pft){
		$ext = strtolower(substr($file, - strlen($pft)));
		if($ext == $pft){
			return $ext;
		}
	}
	return 0;
}


	
function getJSON($dir){
	$is = Array();
	$imgs = $dir['images'];
	foreach($imgs as $i => $m){
		$comma = ', '; # readability
		$isize = GetImageSize($i,$info);
		$ipi = "{}";
		if (isset($info["APP13"])) {
   			$iptc = iptcparse($info["APP13"]);
   			$ipi = array();
   			foreach($iptc as $skey => $sval){
	  			$sipi = array();
	  			foreach($sval as $k => $v){
		  			$v = htmlentities($v);
		  			$v = addslashes($v);
		  			$v = str_replace("\n",'\n',$v);
		  			$v = str_replace("\r",'\r',$v);
	  				$sipi[] = "\"$k\": \"$v\"";
   				}
   				$ipi[] = "\"$skey\": { " . implode($comma,$sipi) . " }";
   			}
   			$ipi = '{' . implode($comma,$ipi) . '}';
		}
		
		
		$exifi = "{}";
		if(function_exists("exif_read_data") &&
			getValidExtension($i) == "jpg"){
			$exifdata = exif_read_data($i,'ANY_TAG',1);
			if($exifdata){
				$exifi = array();
				foreach($exifdata as $section => $secdata){
					$sres = array();
					foreach($secdata as $k => $v)
						$v = htmlentities($v);
			  			$v = addslashes($v);
			  			$v = str_replace("\n",'\n',$v);
			  			$v = str_replace("\r",'\r',$v);
						$sres[]= "\"$k\": \"$v\"";
					$exifi[] = "\"$section\": { " . implode($comma,$sres) ." }";
				}
				$exifi = "{" . implode($comma,$exifi) . "}";
			}			
		}
		
		$is[] = '{ "url": "' . $i .'", ' .
				'"date": ' . $m . $comma .
				'"width": ' . $isize[0] . $comma . 
				'"height": ' . $isize[1] . $comma .
				'"filesize": ' . filesize($i) . $comma .  
				'"iptc": ' . $ipi . $comma . 
				'"exif": ' . $exifi . 
				'}';
		}
	$json = '({'
	       . "\"name\" : \"" . $dir['name'] . "\", \n" 
			. "\"images\" : [\n" . implode(",\n",$is) . "\n]"
			. '})';
	return $json;
	}
	
	
function pyxyResize(){
	$filename = basename($_REQUEST['file']);
	$width = isset($_REQUEST['width']) ? $_REQUEST['width'] : 160;
	$height = isset($_REQUEST['height']) ? $_REQUEST['height'] : 160;
	$dim = Array($width,$height);
	
	doResize($filename,$dim);
}

function doResize($filename,$dimensions,$method="ImageCopyResized"){
	IfModSince(filemtime($filename));
	$olddimensions = GetImageSize($filename);
	$wide = $olddimensions[0];
	$high = $olddimensions[1];
	
	$dimensions = scaledim($olddimensions,$dimensions);
	$newW = $dimensions[0];
	$newH = $dimensions[1];
	
	if($wide < $newW && $high < $newH){
		header("Location: $filename");
		exit; # redirect to file if it's small enough already
	}
	if(function_exists("string_exif_thumbnail") && getValidExtension($file) == "jpg"){
		$thumb =  exif_thumbnail($filename, $xWidth,$xHeight,$xImageType);
		if($xWidth < $newW && $xHeight < $newH){
			header("Cache-control: public, max-age=86400");
			header("Content-type: " . image_type_to_mime_type($xImageType));
			echo($thumb);
		}
	}
	$img = loadImage($filename);
	$newimg = imageCreateTrueColor( $newW, $newH );
	
	$rrf = 4; # resize-resample factor
	if($wide > $newW * $rrf || $high > $newH * $rrf){
		$tempimg = imageCreateTrueColor( $newW * $rrf, $newH * $rrf );
		ImageCopyResized(  $tempimg, $img,    0, 0, 0, 0, $newW * $rrf, $newH * $rrf, $wide, $high);
		ImageCopyResampled($newimg, $tempimg, 0, 0, 0, 0, $newW, $newH, $newW * $rrf, $newH * $rrf);
		ImageDestroy($tempimg);
	} else {
		ImageCopyResampled($newimg, $img, 0, 0, 0, 0, $newW, $newH ,$wide, $high);
	}
	
	//$method($newimg, $img, 0, 0, 0, 0, $newW, $newH ,$wide, $high);
	ImageDestroy ($img);
	
	ob_start();
    ImageJpeg($newimg,'',60);
	$ImageData = ob_get_contents();
	$ImageDataLength = ob_get_length();
 	ob_end_clean();
 	ImageDestroy($newimg);
 	
 	header("Cache-control: public, max-age=86400");
	header('Content-Type: image/jpeg');
	header("Content-Length: " . $ImageDataLength);
	echo $ImageData;
	
}
function loadImage($filename){
	$ext = getValidExtension($filename);
	if($ext == "gif"){
		return ImageCreateFromGIF($filename);
	} elseif($ext == "jpg" || $ext == "jpeg"){
		return ImageCreateFromJPEG($filename);
	} elseif($ext == "png") {
		return ImageCreateFromPNG($filename);
	}
}


function scaledim($from,$to){
    $newW = $wide = $from[0];
	$newH = $high = $from[1];
	$maxW = $to[0];
	$maxH = $to[1];

	if($newH < $maxH && $newW < $maxW){
		return $from;
	}

	$wrat = $maxW / $wide;
	$hrat = $maxH / $high;


	if($newH > $maxH){
	   $newH = $high * $hrat;
	   $newW = $wide * $hrat;
	}
	if($newW > $maxW) { #check both dimensions
	   $newH = $high * $wrat;
	   $newW = $wide * $wrat;
		}
	$newH = floor($newH);
	$newW = floor($newW);
	
	$dim[0] = $newW;
	$dim[1] = $newH;
	return $dim;
}



function IfModSince($lastmod){
	if ($lastmod) { 
		$mself = filemtime($_SERVER['SCRIPT_FILENAME']) ;
		if($mself > $lastmod) $lastmod = $mself; 
/*		if(file_exists('pyxy.pref.inc')){
			$mpref = filemtime('pyxy.pref.inc');
			if($mpref > $lastmod) $lastmod = $mpref;
		} */
		$cond = isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : 0;
		if ($cond and $_SERVER['REQUEST_METHOD'] == 'GET' and strtotime($cond) >= $lastmod) {
			header('HTTP/1.0 304 Not Modified');
			exit;
		} #end if cond
		header('Last-Modified: ' . date('r',$lastmod));
	} #end if lastmod
} # end IfModSince




function getTitle(){	
	$x = explode("/",$_SERVER['SCRIPT_FILENAME']);
	$x = str_replace('_', ' ', $x);
	$x = $x[sizeof($x) - 2];
	$x[0] = strtoupper($x[0]);
	return $x;
}