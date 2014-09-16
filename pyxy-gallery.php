<? /*
pyxy-gallery version 1.01
http://fennecfoxen.org/pyxy/gallery
Copyright (C) 7 May 2006 Thomas Whaples <tom@eh.net>

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

*/


function doMain(){
global $pref;
# put your website template here
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><? echo($pref['title']); ?></title>
	<script language="JavaScript1.5" type="text/javascript" src="prototype.js"></script>
	<script language="JavaScript1.5" type="text/javascript"><!--
		/* auto-loaded preference stuff */
		<? $jspref = Array(	'uri',
							'maxH','maxW',
						  	'max_per_page', 'pics_per_row',
							'show_pic', 'show_url', 'show_date', 'show_size','show_res',
							'lightbox_path'
							);
			foreach ($jspref as $p){ jsPref($p); } ?>
		/* data for script */
		var pictures;
		var g;
		var start = -1;
		var end = -1;
		var fragid = -1;
		
		var months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
		
		function startGallery(){
			g = $("gallery");
			var myAjax = new Ajax.Request(uri, {method: 'get', parameters: "act=json", onComplete: loadGallery });
		}
		function loadGallery(res){
			result = eval(res.responseText);
			pictures = result.data;
			if(pictures == undefined) pictures = result;
			installNavigation();
			//loadFragment();
			autofrag();
		}
		function loadFragment(){
			var s = document.location.href.split('#');
			if(s.length == 1)
				loadFrom(0);
			else if(s[1] == "")
				loadFrom(0);
			else
				loadFrom(s[1]);
		}
		function refrag(){
			autofrag();
			//window.setTimeout(loadFragment, 10);
			}
		function autofrag(){
			window.clearTimeout(fragid);
			fragid = window.setTimeout(autofrag, 50);
			loadFragment();
			}
		
		function loadFrom(frag){
			frag = parseInt(frag);
			if(frag == Math.NaN) frag = 0;
			if(frag == start) return;
			var max = pictures.length;
			var c = 0;
			start =  frag;
			end = frag + max_per_page;
			var htm = "";
			for(x = frag; c < max_per_page; x++){
				c++;
				if(x < max){
					var pic = pictures[x];
					var d = new Date();
						d.setTime(pic.mtime * 1000);
					var da = d.getDate() + " " + months[d.getMonth()] + " " + d.getFullYear();
					htm += '<td class="thumb"><a href="' + pic.url +  // 'onclick="showLightbox(this); return false;' +
							'">';
					if(show_pic) htm += getThumb(pic);
					if(show_url) htm += '<br/>' +  pic.url;
					htm += '<\/a>';
					if(show_date) htm += '<br/>' + da;
					if(show_size) htm += '<br/>' + getsize(pic.filesize);
					if(show_res) htm += '<br/>' + pic.width + 'x' + pic.height;
					htm += '</td>';
				} else { 
					htm += '<td class="thumb"></td>';
				}
				if(!(c % pics_per_row))
					htm += "<\/tr><tr>";
			}
			htm = "<table><tr>" + htm + "<\/tr><\/table>";
			g.innerHTML = htm;
			updateNav();
		}
		function getThumb(pic){
			return '<img src="' + uri + '?act=resize&amp;pic=' + pic.url + '" ' +
					' width="' + pic.twidth + '" height="' + pic.theight + '"/>';
		}

		function getsize(size){			
			if(size < 1000)
				return size + " bytes";
			if(size < 1000000)
				return (Math.round(size / 100) / 10) + " KB";
			if(size < 10000000)
				return (Math.round(size / 100000) / 10) + " MB";
			return (Math.round(size / 100000000) / 10) + " GB"; // eek
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
			prev.innerHTML = "prev";
			prev.onclick = refrag;
	
			next = document.createElement('a');
			next.className = "navNext";
			next.innerHTML = "next";
			next.onclick = refrag;

			last = document.createElement('a');
			last.className = "navLast";
			last.innerHTML = "last";
			last.onclick = refrag;
			lasti = (pictures.length - (pictures.length %  max_per_page));
			if(lasti == pictures.length) lasti -= max_per_page;
			last.href = "#" + lasti;

		
			first = document.createElement('a');
			first.className = "navFirst";
			first.innerHTML = "first";
			first.onclick = refrag;
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
				num.onclick = refrag;
				el.appendChild(num);
			}
		}
	// -->
	</script>
	<style type="text/css">
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
			width: 776px;
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
	</style>
</head>
<body onload="startGallery();" onfocus="autofrag();"><div id="all">
<div id="head">
  <h1><? echo($pref['pagetitle']); ?></h1>
</div>
<div id="main">
    <div class="navNP"><!-- the JavaScript above will populate this--></div>
	<div id="gallery">loading...
	   <noscript><? doNoScript(); ?></noscript></div>
	<div class="nav123"></div>
</div>
<div id="foot">
	<a href="http://fennecfoxen.org/pyxy/gallery">Pyxy Gallery v1.01</a> by
	<a href="http://fennecfoxen.org">Thomas Whaples</a>.
</div>
</div>
<? 
if(file_exists("gallery_footer.inc")){ include("gallery_footer.inc"); }
?>
</body>
</html>

<? } # end function doMain();


# A little boring stuff, then prefrences.
$title = getTitle();
$titlec = $title;
$titlec[0] = strtoupper($titlec[0]);

# default preference section
# you probably don't want to edit these
# you're better off editing a file pref.inc

$pref = Array();
$pref['uri'] = $_SERVER['PHP_SELF'];
$pref['filetypes'] = Array('jpg','jpeg','gif','png');

$pref['tmpdir'] = '/tmp/'; # a / at the end please
						   # This isn't used yet.

$pref['title'] = "Gallery: $titlec";
$pref['pagetitle'] = "Gallery: $titlec";
$pref['max_per_page'] = 8;
$pref['pics_per_row'] = 4;
$pref['show_pic']	  = 1; # hey, someone might not want thumbnails
$pref['show_url']     = 1; # but you really should have one or the other
$pref['show_date']    = 1;
$pref['show_size']    = 1;
$pref['show_res']     = 1;

$pref['autoinstall'] = 1;
$pref['lightbox_path'] = ""; # lightbox.js location: relative URL
							 # This doesn't work yet.

$pref['maxH'] = 128;
$pref['maxW'] = 170;
$pref['quality'] = 80;

#override preferences in pref.inc
if(file_exists("pref.inc")){
	include("pref.inc");
	}
#boring stuff follows
function getTitle(){	
	$x = explode("/",$_SERVER['SCRIPT_FILENAME']);
	$x = str_replace('_', ' ', $x);
	return $x[sizeof($x) - 2];
	return 'Gallery';
}

function jsPref($prefname){
	global $pref;
	$val = $pref[$prefname];
	if(is_string($val) == "string"){
		$val = "\"$val\"";
		}
	echo("var $prefname = $val;\n"); 
	}

#okay, fun stuff time.
$dirs = Array();
$imgs = Array();

# MAIN PAGE HANDLER

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
	header('Content-type: text/html; charset=utf-8');
	header("Content-Length: ".$dataLength);
	echo $data;	
} elseif($_REQUEST['act'] == 'json'){
	$lastmod = load_dir();
	ifmodsince($lastmod);
	$res = get_imgs_json();
	header('Content-type: text/javascript');
	header('Content-length: ' . strlen($res));
	echo($res);
	exit;
} elseif($_REQUEST['act'] == 'resize'){
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
	    $ft = filetype($file);
	
		if($ft == 'dir'){ $dirs[] = $file; }
		elseif($ft == 'file'){
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
	
	if($wide < $pref['maxW'] && $wide < $pref['maxH']){
		return $img; # already small enough
	}

	$newimg = imageCreateTrueColor( $newW, $newH );
	ImageCopyResampled($newimg, $img, 0, 0, 0, 0, $newW, $newH ,$wide, $high);
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

function doNoScript(){ global $pref; ?>
	<p>Sorry, this gallery requires JavaScript. However, you may view this image listing:</p>
	<p><a rel="alternate" href="<? echo($pref['uri']); ?>?act=noscript">View directory</a></p>
	<?
	
	?>
	
<?
}

function get_imgs_noscript(){
	global $imgs;
	global $pref;
	$res =  "<html><head><title> " . $pref['title'] . "</title></head><body>";
	$res .= "<h1>Directory for <a href=\"" . $pref['uri'] . "\"> " . 
		$pref['title'] . "</a></h1>";
	$res .= "<ul>";
	foreach($imgs as $i=>$d){
		$res .= "<li><a href=\"$i\">$i</a></li>";
		}
	$res .= "</ul></head></html>";
	return $res;
}

function autoinstall(){
	$pjs = fopen("prototype.js","x");
	$pjsurl = "http://fennecfoxen.org/misc/prototype.js";
	$pjsurl = "http://prototype.conio.net/dist/prototype-1.4.0.js";
	if($pjs){
		$prototypejs = file_get_contents($pjsurl);
		if($prototypejs){
			fwrite($pjs, $prototypejs);
			fclose($pjs);
		}
		else {
			echo("<h2>prototype.js not available</h2>");
			echo("<p><b>Could not auto-install prototype.js</b> - please <a href='$pjsurl'>download it</a> and install manually.</b>");
			unlink("prototype.js");
		}
	}
}


?>
