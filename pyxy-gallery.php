<?php  $GPL = "/*
pyxy-gallery version " . ($version = '1.12') .  "
http://fennecfoxen.org/pyxy/gallery
See:
 http://fennecfoxen.org/pyxy/gallery/docs
for installation instructions.
Copyright (C) 2006 Thomas Whaples <tom@eh.net>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

The GNU General Public License is available at:
  http://www.gnu.org/copyleft/gpl.html
Or, write to:
  Free Software Foundation, Inc, 
  51 Franklin Street, Fifth Floor
  Boston, MA  02110-1301, USA

*/";

function doMain(){
global $pref; global $i8n; global $version;
# put your website template here
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<!-- this is pyxy-gallery by Thomas Whaples. -->
	<title><?php echo($pref['title']); ?></title>
	<link rel="stylesheet" href="<?php echo(getAct('css')); ?>" />
	<script type="text/javascript" src="<?php echo($pref['prototype_path']); ?>"></script>
	<?php if($pref['lightbox_path']) { ?>
		<script type="text/javascript" src="<?php echo($pref['lightbox_path'] . "lightbox.js"); ?>"></script>
		<link rel='stylesheet' href="<?php echo($pref['lightbox_path'] . "lightbox.css"); ?>"/>
	<?php } ?>
	<script type="text/javascript" src="<?php echo(getAct('js')); ?>"></script>
	</head>
<body onload="startGallery();"><div id="all">
<div id="head">
  <h1><?php echo($pref['pagetitle']); ?></h1>
  <?php if($pref['caption']){  echo('<div id="caption">' . $pref['caption'] . '</div>');  	} ?>
</div>
<div id="main">
    <div class="navNP"><!-- the JavaScript above will populate this--></div>
	<div id="gallery">loading...
	   <noscript><?php doNoScript(); ?></noscript>
	</div>
	<div class="nav123"></div>
</div>
<div id="foot"><?php
if(file_exists("pyxy.footer.inc")){ include("pyxy.footer.inc"); }
?><div id="credits">
   <a href="http://fennecfoxen.org/pyxy/gallery">Pyxy Gallery v<?php echo($version); ?></a>
   by
  <a href="http://fennecfoxen.org">Thomas Whaples</a>.
  <?php if($pref['lightbox_path']) { ?><br/>with <a href="http://www.huddletogether.com/projects/lightbox/">lightbox.js</a>
   by Lokesh Dhakar.<?php } ?>
   </div>
  </div>
 </div>
 </body>
</html>

<?php } # end function doMain();


# A little boring stuff, then prefrences.
$title = getTitle();
$titlec = $title;
$titlec[0] = strtoupper($titlec[0]);

# default preference section
# you probably don't want to edit these
# you're better off editing a file pyxy.pref.inc

$pref = Array();
$pref['debug'] = 0;


$pref['dir'] = './'; # I'm afraid I must insist on a relative URL
$pref['uri'] = $_SERVER['PHP_SELF'];
$pref['filetypes'] = Array('jpg','jpeg','gif','png');

$pref['tmpdir'] = '/tmp/'; # a / at the end please
						   # This isn't used yet.

$pref['title'] = "Gallery: $titlec";
$pref['pagetitle'] = "Gallery: $titlec";
$pref['caption']	  = "(this gallery has no description)";

$pref['max_per_page'] = 8;
$pref['pics_per_row'] = 4;
$pref['show_pic']	  = 1; # hey, someone might not want thumbnails or URLs
$pref['show_url']     = 1; # but you really should have one or the other
$pref['show_date']    = 1;
$pref['show_size']    = 1;
$pref['show_res']     = 1;

$pref['autoinstall'] = 1;
$pref['prototype_path'] =  "prototype.js"; # might want to turn off autoinstall if this is set
$pref['lightbox_path'] = ""; # lightbox.js location: relative URL
$pref['lightbox_ver'] = 0;

$pref['resize_method'] = 'resize-resample';
$pref['rr-factor'] = 4;
$pref['rr-threshhold'] = 6;
$pref['maxH'] = 128;
$pref['maxW'] = 170;
$pref['quality'] = 80;
$pref['sort'] = 'url';

$i8n = Array();
$i8n['months'] = '["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];';
$i8n['empty_gallery'] = '<br/><br/>There are no pictures in this directory.<br/><br/><br/>';
$i8n['first'] = 'first';
$i8n['last'] = 'last';
$i8n['prev'] = 'prev';
$i8n['next'] = 'next';
$i8n['Image'] = 'Image';
$i8n['bytes'] = ' bytes';
$i8n['kilobytes'] = ' KB';
$i8n['megabytes'] = ' MB';
$i8n['gigabytes'] = ' GB'; # eek


#override preferences in pyxy.pref.inc
if(file_exists('pyxy.pref.inc')){
	require('pyxy.pref.inc');
	}
	
#boring stuff follows
function getTitle(){	
	$x = explode("/",$_SERVER['SCRIPT_FILENAME']);
	$x = str_replace('_', ' ', $x);
	return $x[sizeof($x) - 2];
}

function jsPref($prefname){
	global $pref;
	$val = $pref[$prefname];
	if(is_string($val) == "string"){
		$val = "\"$val\"";
		}
	echo("var $prefname = $val;\n"); 
	}
function getAct($actname){
	global $pref;
	return $pref['uri'] . '?act=' . $actname;
	}

#okay, fun stuff time.
$dirs = Array();
$imgs = Array();

# MAIN PAGE HANDLER

if($pref['debug']){
	# Debug mode keeps anything from being cached
	header("Vary: *");
	header("Cache-control: must-revalidate");
}

if(!isset($_REQUEST['act'])){
	if($pref['autoinstall']){
		if(!file_exists('prototype.js')){
			autoinstall();
		}
	}
	ob_start();
	doMain();
	$data = ob_get_contents();
	$dataLength = ob_get_length();
	ob_end_clean();
	$pref['debug'] || header("Cache-control: public, max-age=86400");
	header('Content-type: text/html; charset=utf-8');
	header("Content-Length: ".$dataLength);
	echo $data;
} elseif($_REQUEST['act'] == 'css' || $_REQUEST['act'] == 'js'){
	$lastmod = filemtime($_SERVER['SCRIPT_FILENAME']);
	ifmodsince($lastmod);
	ob_start();
	if($_REQUEST['act'] == 'css') doCSS();
	elseif($_REQUEST['act'] == 'js') doJS();
	$data = ob_get_contents();
	$dataLength = ob_get_length();
	ob_end_clean();
	$pref['debug'] || header("Cache-control: public, max-age=86400");
	header("Content-Length: ".$dataLength);
	echo $data;

} elseif($_REQUEST['act'] == 'json'){
	chdir($pref['dir']);
	$lastmod = load_dir();
	ifmodsince($lastmod);
	$res = get_imgs_json();
	header('Content-type: text/javascript');
	header('Content-length: ' . strlen($res));
	echo($res);
	exit;
} elseif($_REQUEST['act'] == 'resize'){
    chdir($pref['dir']);
	if(isset($_REQUEST['pic'])){
		$pic = $_REQUEST['pic'];
		ifmodsince(filemtime($pic));
		$im = doresize($pic);

		ob_start();
        ImageJpeg($im,'',$pref['quality']);
		$ImageData = ob_get_contents();
		$ImageDataLength = ob_get_length();
 		ob_end_clean();
		
		header('Content-type: image/jpeg');
		header("Content-Length: ".$ImageDataLength);
		echo $ImageData;
		ImageDestroy($im);
	}
} elseif($_REQUEST['act'] == 'noscript'){
    chdir($pref['dir']);
	$lastmod = load_dir();
	ifmodsince($lastmod);
	$res = get_imgs_noscript();
	header('Content-type: text/html; charset=utf-8');
	header('Content-length: ' . strlen($res));
	echo($res);
	exit;
} else { # default action
	header("HTTP/1.1 403 Forbidden");
	header("Content-type: text/plain");
	echo("Unknown action parameter.");
}






function load_dir(){
	global $dirs;
	global $imgs;
	global $pref;
	$lastmod = 0;
	$dh = opendir('.');
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
	return $lastmod;
#	sort($imgs);
} #end load_dir

function getValidExtension($file){
	global $pref;
	foreach($pref['filetypes'] as $pft){
		$ext = strtolower(substr($file, - strlen($pft)));
		if($ext == $pft){
			return $ext;
		}
	}
	return '';
}


function get_imgs_json(){
	global $imgs;
	$is = Array();
	
	foreach($imgs as $i => $m){
		$comma = ', '; # readability
		$isize = GetImageSize($i);
		$tsize = scaledim($isize);
		$is[] = '{ "url": "' . $i .'", ' .
				'"mtime": ' . $m . $comma .
				'"width": ' . $isize[0] . $comma . 
				'"height": ' . $isize[1] . $comma .
				'"twidth": ' . $tsize[0] . $comma .
				'"theight": ' . $tsize[1] . $comma .
				'"filesize": ' . filesize($i) . 
				'}';
		
		}
	$json = '({"data" : [' . implode(",\n",$is) . ']})';
	return $json;
	}

function ifmodsince($lastmod){
	if ($lastmod) {
		$mself = filemtime($_SERVER['SCRIPT_FILENAME']) ;
		if($mself > $lastmod) $lastmod = $mself;
		if(file_exists('pyxy.pref.inc')){
			$mpref = filemtime('pyxy.pref.inc');
			if($mpref > $lastmod) $lastmod = $mpref;
		}
		$cond = isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : 0;
		if ($cond and $_SERVER['REQUEST_METHOD'] == 'GET' and strtotime($cond) >= $lastmod) {
			header('HTTP/1.0 304 Not Modified');
			exit;
		} #end if cond
		header('Last-Modified: ' . date('r',$lastmod));
	} #end if lastmod
} # end ifmodsince


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


function doresize($filename){
	global $pref;
	$img = loadImage($filename);

	$dimensions = GetImageSize($filename);
	$wide = $dimensions[0];
	$high = $dimensions[1];
	$dimensions = scaledim($dimensions);
	$newW = $dimensions[0];
	$newH = $dimensions[1];
	
	if($wide < $pref['maxW'] && $high < $pref['maxH']){
		return $img; # already small enough
	}

	$newimg = imageCreateTrueColor( $newW, $newH );
	if($pref['resize_method'] == 'resample'){
		ImageCopyResampled($newimg, $img, 0, 0, 0, 0, $newW, $newH ,$wide, $high);
	} elseif($pref['resize_method'] == 'resize'){
		ImageCopyResized($newimg, $img, 0, 0, 0, 0, $newW, $newH ,$wide, $high);
	} elseif($pref['resize_method'] == 'resize-resample'){
		if($wide > $newW * $pref['rr-threshold']){
			$rrf = $pref['rr-factor'];
			$tempimg = imageCreateTrueColor( $newW * $rrf, $newH * $rrf );
			ImageCopyResized($tempimg, $img, 0, 0, 0, 0,
							 $newW * $rrf, $newH * $rrf, $wide, $high);
			ImageCopyResampled($newimg, $tempimg, 0, 0, 0, 0, $newW, $newH,
							   $newW * $rrf, $newH * $rrf);
			ImageDestroy($tempimg);
		} else {
			ImageCopyResampled($newimg, $img, 0, 0, 0, 0, $newW, $newH ,$wide, $high);
		}
	}
	ImageDestroy ($img);
	return $newimg;
}


function scaledim($dim){
	global $pref;
    $newW = $wide = $dim[0];
	$newH = $high = $dim[1];

	if($newH < $pref['maxH'] && $newW < $pref['maxW']){
		return $dim;
	}

	$wrat = $pref['maxW'] / $wide;
	$hrat = $pref['maxH'] / $high;


	if($newH > $pref['maxH']){
	    $newH = $high * $hrat;
	    $newW = $wide * $hrat;
	}
	if($newW > $pref['maxW']) { #check both dimensions
	    $newH = $high * $wrat;
	    $newW = $wide * $wrat;
		}
	$newH = floor($newH);
	$newW = floor($newW);
	
	$dim[0] = $newW;
	$dim[1] = $newH;
	return $dim;
}

function doNoScript(){ global $pref; global $i8n; ?>
	<p>Image gallery by Pyxy-Gallery.<br/>
	This gallery requires JavaScript. However, you may view this image listing:</p>
    <h3><a rel="alternate" href="<?php echo(getAct('noscript')); ?>">View directory for</a></h3>
	<h2><a rel="alternate" href="<?php echo(getAct('noscript')); ?>"><?echo($pref['pagetitle']);?></a></h2>
		
	<?php
	
	?>
<?php
}

function get_imgs_noscript(){
	global $imgs;
	global $pref;
	$res = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	$res .=  "\n<html><head><title> " . $pref['title'] . "</title>";
	$res .= "\n<link rel='stylesheet' href='" . getAct('css') . "'/></head><body>";
	$res .= "<h1>Directory for <a href=\"" . $pref['uri'] . "\"> " . $pref['title'] . "</a></h1>";
    if($pref['caption']){  $res .= '<div id="caption">' . $pref['caption'] . '</div>'; }
  
	$res .= "<ul>";
	foreach($imgs as $i=>$d){
		$ih = $pref['dir'] . $i;
		$res .= "<li><a href=\"$ih\">$i</a></li>";
		}
	$res .= "</ul></head></html>";
	return $res;
}

function autoinstall(){
	#$pjsurl = "http://fennecfoxen.org/misc/prototype.js";
	#  well- theoretically I could abuse that URL
	#  best leave it with one on an official site.
	$pjsurl = "http://prototype.conio.net/dist/prototype-1.4.0.js";
	$docs = "http://fennecfoxen.org/pyxy/gallery/docs";

	if(ini_get('allow_url_fopen'))
		$pjs = fopen('prototype.js',"x");
	else {
		echo("<h2>allow_url_fopen disabled</h2>");
		echo("<p>Cannot autoinstall prototype.js: allow_url_fopen is disabled on your system. Please <a href='$pjsurl'>download prototype.js</a> and install it manually. See the <a href='$docs'>Pyxy-Gallery documentation</a> for further details.</p>");
	}
	if($pjs){
		$prototypejs = file_get_contents($pjsurl);
		if($prototypejs){
			fwrite($pjs, $prototypejs);
			fclose($pjs);
		}
		else {
			echo("<h2>prototype.js not available</h2>");
			echo("<p><b>Could not auto-install prototype.js</b> - could not download prototype.js over the network. Please <a href='$pjsurl'>download it</a> and install manually: see <a href='$docs'>the Pyxy-Gallery documentation</a> for details.</b>");
			unlink("prototype.js");
		}
	} else { # fopen failed 
		echo("<h2>prototype.js installation failed</h2>");
		echo("<p><b>Count not auto-install prototype.hs</b> - could not open file for writing. Please install <a href='$pjsurl'>prototype.js</a> manually: see <a href='$docs'>the Pyxy-Gallery documentation</a> for details.</p>");
	}
}

function doCSS(){ 
header("Content-type: text/css");
?>
/* <style> */
body { background: #333; color: #aaa;}
h1 {
	text-align: center;
	color: white;
	}
div.navNP, div.nav123,
div#gallery {
	border: thin black solid;
	/* width: 776px; */
	margin-left: auto;
	margin-right: auto;
	text-align: center;
	color: white;
}
div#gallery table {
	text-align: center;
	width: 776px; /* perfect for default settings */
	margin-left: auto;
	margin-right: auto;
	}
div.navNP a, div.nav123 a {
	padding-right: 6pt;
	padding-left: 6pt;
}
.thumb {
	/* width: 190px; height: 190px; */
	border: 1px solid black;
	padding: 1px;
	background: #555;
	font-size: small;
	color: black;
	vertical-align: bottom;
	}
div.thumb { float: left; }
a {
	text-decoration: none;
	color: #88c;
}
a:hover { color: #ccf; }
a strong { color: white; }	
a img { border: thin black solid; }
div#credits { text-align: center; margin-top: 10pt;}
div#caption {
	margin: 5px auto 5px auto;
	width: 776px;
}

/* </style> this tag is here to keep vi autohilighting stuff */
<?php
}

function doJS(){
	global $pref; global $i8n;
	header("Content-type: text/javascript");
?>
/*	<script type="text/javascript">  that tag is there for vi hilighting -- tom*/
/* Pyxy-gallery image-loading script!
   Copyright (c) 2005 by Thomas Whaples
   along with the rest of Pyxy-gallery.

   See the PHP source for details, or visit
   http://fennecfoxen.org/pyxy/gallery
*/
		/* auto-loaded preference stuff */
		<?php $jspref = Array(	'uri', 'dir',
							'maxH','maxW',
						  	'max_per_page', 'pics_per_row',
							'show_pic', 'show_url', 'show_date', 'show_size','show_res',
							'lightbox_path', 'lightbox_ver',
							'sort'
							);
			foreach ($jspref as $p){ jsPref($p); } ?>
		/* data for script */
		var pictures;
		var g;
		var start = -1;
		var end = -1;
		var fragid = -1;
		var myAjax;
		var months = <?php echo($i8n['months']); ?>;
		var oldHideLightbox = function(){};
		function startGallery(){
			if(lightbox_ver)
				initLightbox(); /* it needs to create <div>s and such */
			if(lightbox_ver == 1) {
				oldHideLightbox = hideLightbox; /* this lets us restore the fragment ID after Lightbox closes */
				hideLightbox = function(){ oldHideLightbox(); restoreFragment(); }
				}
			else if(lightbox_ver == 2) {
				oldHideLightbox = function(){ myLightbox.oldEnd() } ;
				myLightbox.oldEnd = myLightbox.end;
				myLightbox.end = function(){ this.oldEnd();	restoreFragment(); }
				}
			g = $("gallery");
			myAjax = new Ajax.Request(uri, {method: 'post', parameters: "act=json", onComplete: loadGallery });
		}
		function loadGallery(res){
			result = eval(res.responseText);
			pictures = result.data;
			if(pictures == undefined) pictures = result;
			if(sort) pictures.sort(sortfunc);
			installNavigation();
			autofrag();
		}
		var lastH;
		function loadFragment(){
			var H = document.location.href;
			if(H == lastH) return;
			lastH = H;
			var s = H.split('#');
			if(s.length == 1)
				loadFrom(0);
			else if(s[1] == "")
				loadFrom(0);
			else
				loadFrom(s[1]);

		}
		function autofrag(){
			window.clearTimeout(fragid);
			if(pictures && pictures.length){
				fragid = window.setTimeout(autofrag, 50);
				}
			loadFragment();
		}

		function restoreFragment(){
			var s = document.location.href.split('#');
			var base = s[0];
			var lastpic = s[1] ? s[1].substr(4) : "";
			var spot = -1;
			for(var x = 0; x < pictures.length; x++)
				if(pictures[x].url == lastpic){
					spot = x;
					break;
				}
			spot = spot - (spot % max_per_page);
			if(spot > 0)
				document.location.href = base + "#" + spot;
			else
				 document.location.href = base + "#";
		}
		function runLightbox(pid){
		/* we may get a fragment ID for a picture from a bookmark.
		   We need to pass Lightbox a fake link. */
			pic = document.createElement("a");
			pic.href = dir + pid;
			if(lightbox_ver == 1){
				showLightbox(pic);
				}
			else if(lightbox_ver == 2){myLightbox.start(pic);}
		}
		
		function loadFrom(frag){
			/* launch lightbox if appropriate */
			if(frag && frag.substr(0,4) == "show")
				return runLightbox(frag.substr(4));
			
			/* close lightbox if necessary */
			oldHideLightbox();
			frag = parseInt(frag);
			if(frag == Math.NaN) frag = 0;
			if(frag == start) return;
			if(!pictures) return;
			if(pictures.length == 0){
				g.innerHTML = "<?php echo($i8n['empty_gallery']);?>";
				return;		
			}
			var max = pictures.length;
			var c = 0;
			start =  frag;
			end = frag + max_per_page;
			var htm = "";
			var row = 0;
			for(x = frag; c < max_per_page; x++){
				c++;
				if(x < max){
					var pic = pictures[x];
					var d = new Date();
						d.setTime(pic.mtime * 1000);
					var da = d.getDate() + " " + months[d.getMonth()] + " " + d.getFullYear();
					pic.da = da;
					htm += '<td class="thumb"><a href="' + 
						((lightbox_ver)? ("#show" + pic.url) : (dir + pic.url)) +
							'" title="' + getTitle(pic) +  ' id="pic' + pic.url + '">';
					if(show_pic) htm += getThumb(pic);
					if(show_url) htm += '<br/>' +  pic.url;
					htm += '<\/a>';
					if(show_date) htm += '<br/>' + da;
					if(show_size) htm += '<br/>' + getsize(pic.filesize);
					if(show_res) htm += '<br/>' + pic.width + 'x' + pic.height;
					htm += '</td>';
				} else if(row) { 
					htm += '<td class="thumb"></td>';
				}
				if(!(c % pics_per_row))
					if(x < max){
						htm += "<\/tr><tr>";
						row++;
					} else break;
			}
			htm = "<table><tr>" + htm + "<\/tr><\/table>";
			g.innerHTML = htm;
			updateNav();
		}
		function getThumb(pic){
			return '<img src="' + uri + '?act=resize&amp;pic=' + pic.url + '" ' +
					' width="' + pic.twidth + '" height="' + pic.theight + '" ' + 
					' title="' + getTitle(pic) + '" alt="' + pic.url + '"/>';
		}
		function getTitle(pic){
			return "<?php echo($i8n['Image']); ?>: " + pic.url + " " +
				(pic.da ? "(" + pic.da  + ") " : "") + 
				"[" + pic.width + 'x' + pic.height + ", " + getsize(pic.filesize) + ']';
		}
		function getsize(size){			
			if(size < 1000)
				return size + "<?php echo($i8n['bytes']); ?>";
			if(size < 1000000)
				return (Math.round(size / 100) / 10) + "<?php echo($i8n['kilobytes']); ?>";
			if(size < 10000000)
				return (Math.round(size / 100000) / 10) + "<?php echo($i8n['megabytes']); ?>";
			return (Math.round(size / 100000000) / 10) + "<?php echo($i8n['gigabytes']); ?>"; // eek
		}
		
		function installNavigation(){
			var ns = document.getElementsByClassName('navNP');
			var i;
			for(i = 0; i < ns.length; i++){	makeNavNP(ns[i]);}
			ns = document.getElementsByClassName('nav123');
			for(i = 0; i < ns.length; i++){ makeNav123(ns[i]);}
		}

		function makeNavNP(el){
			prev = document.createElement('a');
			prev.className = "navPrev";
			prev.innerHTML = "<?php echo($i8n['prev']); ?>";
			prev.onclick = autofrag;
	
			next = document.createElement('a');
			next.className = "navNext";
			next.innerHTML = "<?php echo($i8n['next']); ?>";
			next.onclick = autofrag;

			last = document.createElement('a');
			last.className = "navLast";
			last.innerHTML = "<?php echo($i8n['last']); ?>";
			last.onclick = autofrag;
			lasti = (pictures.length - (pictures.length %  max_per_page));
			if(lasti == pictures.length) lasti -= max_per_page;
			last.href = "#" + lasti;

		
			first = document.createElement('a');
			first.className = "navFirst";
			first.innerHTML = "<?php echo($i8n['first']); ?>";
			first.onclick = autofrag;
			first.href = "#";
			
			el.innerHTML = "";
			el.appendChild(first);
			el.appendChild(prev);
			el.appendChild(next);
			el.appendChild(last);
			//updateNav(); // not ready to update yet
		}
		function updateNav(){
			var i;
			nexti = end;
			if(nexti > pictures.length) nexti -= max_per_page;
			previ = start - max_per_page;
			if(previ < 0) previ = 0;

			var nexts = document.getElementsByClassName('navNext');
			for(i = 0; i < nexts.length; i++){
				nexts[i].href = "#" + nexti;
			}
			
			var prevs = document.getElementsByClassName('navPrev');
			for(i = 0; i < prevs.length; i++){
				prevs[i].href = "#" + previ;
			}
			
			var nums =  document.getElementsByClassName('navNum');
			for(i = 0; i < nums.length; i++){
				if(nums[i].hid == start){
					nums[i].innerHTML = "<strong>" + nums[i].saveHTML + "</strong>";
				} else {
					nums[i].innerHTML = nums[i].saveHTML;
				}
			}
			
		}

		function makeNav123(el){
			var c;
			var n = 1;
			for(c = 0; c < pictures.length; c += max_per_page){
				var num = document.createElement('a');
				num.className = "navNum";
				num.innerHTML = num.saveHTML =  '['+(n++)+']';
				num.href = "#" + c;
				num.hid = c;
				num.onclick = autofrag;
				el.appendChild(num);
				el.appendChild(document.createElement('wbr')); // for spacing for linebreaks
			}
		}

	function sortfunc(a,b){
		if(a[sort] == b[sort]) return 0;
		return (a[sort] < b[sort]) ? -1 : 1;
	}
	// -->
//	</script>
<?php }
?>
