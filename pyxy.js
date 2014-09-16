/* 
  You /probably/ should not edit this file. If you edit this file
  and the later upgrade, you'll lose your edits. Instead, create
  a new class, and inherit the pyxy class. =)
*/

var pyxyGallery = new Class({
	initialize: function(elem,preferences){
		this.ename = elem;   // elem is a our parent container's name-id-thing
		this.el  = $(elem);  // el is the element itself
		this.pref = preferences;
		this.fill_pref();
		
		this.start = 0;
		this.historyCount = -1;
		
		new Ajax(this.pref.url, 
			{method: 'get', onComplete: this.load,instance: this }
			).request();
			
		this.rePage = new RegExp('^'+elem+'-page-(\\d+)', '');
		this.reView = new RegExp('^'+elem+'-view-(.+)','');
		this.lightbox = new pyxyLight();
	},
	fill_pref: function(){
		this.pref = this.pref || {};
		var t = this;
		var dP = function(str,dfault){
			// sets up a default preference.
			// trivia: 'default' is a reserved word
			t.pref[str] = t.pref[str] || dfault;
		};
		dP("base","./");						// directory to call...
		dP("url",this.pref.base + "?act=json"); // URL to get images from
		dP("resizeParam", "");					// parameters to the 'resize' URL
		dP("resizeURL", this.pref.base + "?act=resize&" + this.pref.resizeParam + "&file=");

		dP("rows",2);	//    rows in table
		dP("cols",4);	// columns in table
		dP("fillBlankCols",1);	// Fills the last row on the last page
		dP("fillBlankRows", 0);
			// Fills all the rows on the last page
			// if you set this one, set a row height or something
			// else it looks stupid
		
		dP("showDate",1);	// show the date
		dP("showSize",1);	// show the file size
		dP("showResolution",1); //  the resolution
	},
	
	
	load : function(text,xml){
		instance = this.options.instance;
		if(text) {
			var res = eval(text);
			instance.loadObject(res);
			instance.autofrag();
		} else {
			alert(t['invalid-response'] + this.aj.url);
		}
	},
	
	loadObject: function(ob){
		var cells = new Array();
		var t = this;
		ob.images.each(function(pic){
			cells.extend([t.formatCell(pic)]);
		});
		this.cells = cells;

		var table = new Element('table');
		var tbody = new Element('tbody');
		table.adopt(tbody);
		this.table = table;
		this.tbody = tbody;
		
		this.el.setHTML("");
		this.el.adopt(table);
	},
	clearTbody: function(){
		this.cells.each(function(td){
			td.hide();
		});
		
		this.tbody.remove();
		this.tbody = new Element('tbody');
		this.table.adopt(this.tbody);
	},
	
	formatCell: function(pic){
		pic = this.fillData(pic);
		var td = new Element('td');
		var img = new Element('img');
		
		if(pic.thumbWidth) img.width = pic.thumbWidth;
		if(pic.thumbHeight) img.height = pic.thumbHeight;
		img.alt = img.title = pic.title;
		
		td.show = function(){ img.src = pic.thumbURL; }
		/* if we set .src early this will will munch loading time 
		   and interfere when the user wants to see a pic
		*/
		td.hide = function(){ img.src = ""; }
		
		if(pic.href){
			td.href = pic.href;
			var aImg = new Element('a');
			aImg.href = pic.href;
			aImg.rel = "prefetch";
			aImg.adopt(img);
			td.adopt(aImg);	
			td.showImage = (function(){

				var cap = this.formatLightboxCaption(pic);
				this.lightbox.show(pic.href,cap,this._restore.bind(this));
				return false;
			}).bind(this);
			td.restore = aImg.onclick = (function(){
				document.location.href =
				document.location.href.split('#')[0] 
					+ '#' + this.ename + "-view-" + escape(pic.href);
				return false;
				}).bind(this);
			// rely on fragment processing to show lightbox
		} else {
			td.adopt(img);
		}
		
	
		var caption = this.formatCaption(pic);
		if(caption) td.adopt(caption);
		return td;
	},
	formatBlankCell: function(){
		var td = new Element('td');
		td.className = "blank";
		return td;
	},
	formatCaption: function(pic){
		var caption = new Element('div');
		if(pic.href){
			var aTitle = new Element('a');
			aTitle.href = pic.href;
			aTitle.appendText(pic.title);
			caption.adopt(aTitle)
		} else {
			caption.adopt(new Element('p').appendText(pic.title));
		}
		if(this.pref.showDate)
			caption.adopt(new Element('p').appendText(this.formatDate(pic.date)));
		if(this.pref.showResolution)
			caption.adopt(new Element('p').appendText(this.formatResolution(pic.width,pic.height)));
		if(this.pref.showSize)
			caption.adopt(new Element('p').appendText(this.formatFileSize(pic.filesize)));
		return caption;
	},
	formatLightboxCaption: function(pic){
		var div = this.formatCaption(pic);
		div.setAttribute('id','pyxylightCaption');
		return div;
	},
	
	fillData: function(pic){
		// turns partial image metadata into more complete version
		pic.thumbURL = pic.thumbURL || this.pref.resizeURL + pic.url;
		pic.thumbWidth = pic.thumbHeight || 160;
		pic.thumbHeight = pic.thumbHeight || 120;
		pic.title = pic.title || pic.url;
		pic.href = pic.href || pic.url;
		pic.alt = pic.alt || pic.title;
		return pic;
	},

	
	formatFileSize: function(size){	// in bytes - POSITIVE size, please
		if(size < 1000)
			return size + " bytes";
		if(size < 1000000) // round down to one decimal place
			return (Math.round(size / 100) / 10) + " KB";
		if(size < 10000000)
			return (Math.round(size / 100000) / 10) + " MB";
		return (Math.round(size / 100000000) / 10) + " GB"; // eek
	},
	
	formatNavigation: function(isTop){
		var thiz = this;
		var t = this.t;
		var tr = new Element('tr');
		var th = new Element('th');
		tr.adopt(th);
		th.colSpan = this.pref.cols;
		
		var cp = this.getCurPage();
		var np = this.getPageCount();
		
		if(isTop){
			var prev = new Element('a');
			prev.className = "nav nav-prev";
			prev.appendText(t["prev"]);
			//prev.onclick = function(){ thiz.showPrev(); return false; };
			
			var next = new Element('a');
			next.className = "nav nav-next";
			next.appendText(t["next"]);
			//next.onclick = function(){ thiz.showNext(); return false; };
		

			if(cp != 1) prev.href = this.formatPageFrag(cp - 1);
			if(cp != np) next.href = this.formatPageFrag(cp + 1);
			
			th.adopt(prev);
			th.adopt(next);
			return tr;
		} else if(np > 1) {
			var pages = new Element('span');
			pages.className = "nav-pages";
			for(var i = 1; i <= np; i++){
				var a = new Element(i == cp ? 'b' : 'a');
				if(i != cp)
					a.href = this.formatPageFrag(i);
				a.appendText("[" + i + "]");
				th.adopt(a);
			}
			th.adopt(pages);
			return tr;
		}
	},
	formatPageFrag: function(n){
		return "#" + this.ename + "-page-" + n;
	},
	
	formatDate: function(mtime){ // UNIX timestamp
		var months = this.t['months'];
		var d = new Date();
		d.setTime(mtime * 1000); // milliseconds
		return d.getDate() + " " + months[d.getMonth()] + " " + d.getFullYear();
	},
	formatResolution: function(x,y){
		if(x && y)
			return '[' + x + 'x' + y + ']';
		return undefined;
	},
	
	loadCells: function(start){
		this.loadedOnce = 1;
		var i = this.start = start;
		this.clearTbody();
		
		var nTop = this.formatNavigation(1);
		if(nTop) this.tbody.adopt(nTop);
		this.tbody.appendText("\n");
		for(var r = 0; r < this.pref.rows; r++){
			if(i < this.cells.length || this.pref.fillBlankRows){
				var tr = new Element('tr');
				for(var c = 0; c < this.pref.cols; c++){
					if(i < this.cells.length){
						var td = this.cells[i++];
						td.show();
						tr.adopt(td);
						tr.appendText("\n");
					} else if(r || this.pref.fillBlankRows || this.pref.fillBlankCols) {
						tr.adopt(this.formatBlankCell());
					} // end else if
				} // end for: cols
				this.tbody.adopt(tr);
			} else {
				// blank row;
			}
		} // end for: rows
		
		var nBot = this.formatNavigation(0);
		if(nBot)  this.tbody.adopt(nBot);
	},
	getDisplayCount: function(){ return this.pref.rows * this.pref.cols;},
	getPageCount:    function(){ return parseInt( this.cells.length / this.getDisplayCount())+ 1;},
	getNextStart: function(){
		var s = this.start + this.getDisplayCount();
		return (s > this.cells.length) ? this.start : s;
	},
	getPrevStart: function(){
		var s = this.start - this.getDisplayCount();
		return (s < 0) ? 0 : s;
	},
	getPageStart: function(p){
		var s = (p-1) * this.getDisplayCount();
		return (p < 0 || p > this.cells.length) ? undefined : parseInt(s);
	},
	getCurPage: function(){
		return this.start / this.getDisplayCount() + 1;
	},
	getPageOf: function(x){
		return x / this.getDisplayCount() + 1;
	},
/*	showNext: function(){ this.loadCells(this.getNextStart()); },
	showPrev: function(){ this.loadCells(this.getPrevStart()); }, */
	_restore: function(){
		document.location.href =
		document.location.href.split('#')[0] 
			+ '#' + this.ename + "-page-" + this.getCurPage();
		this.autofrag();
	},
	
	showPage: function(p){ this.loadCells(this.getPageStart(parseInt(p))); },

	setFragID: function(id){
		var s = document.location.href.split('#');
		var base = s[0];
		document.location.href = base + "#" + id;
	},

		
	loadFragment: function(){
		var H = document.location.href;
		if(this.lastHref == H)
			return;
		this.lastHref = H;
		
		var s = H.split('#');
		if(s.length == 1 || s[1] == ""){// if we don't have a specific page to go to, and we
			if(!this.loadedOnce)		// haven't loaded anything yet, load from start
				this.loadCells(0);
			return;
		}
		var res;
		if(res = this.rePage.exec(s[1])){
			this.showPage(res[1]);
			if(this.lightbox.visible){
				this.lightbox._oncomplete = Class.empty;
				this.lightbox.hide();
			}
		} 
		if(res = this.reView.exec(s[1])){
			var id = undefined;
			for(i = 0; (id == undefined) && i < this.cells.length; i++){
				if(this.cells[i].href == res[1])
					id = i;
			}
			if(id != undefined){
				if(0 && this.lightbox.visible){
					this.lightbox._oncomplete = (function(){
						this.cells[id].showImage.delay(1000,this);
					}).bind(this);
					this.lightbox.hide();
				} else {
					this.cells[id].showImage();
				}
			}
			this.showPage(this.getPageOf(id));
		}
		
		if(!this.loadedOnce){
			this.loadCells(0); // load from start!
		}

	},

	autofrag: function(){
		if(this.fragID) 
			window.clearTimeout(this.fragID);
		if(this.cells && this.cells.length){
			this.fragID = window.setTimeout(this.autofrag.pass(), 100);
			}
		this.loadFragment();
	},
	
		
	t : { /* translation */
		'prev': 'prev',
		'next': 'next',
		'months':  ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
		'invalid-response': "Invalid response from URL: "
	}
	
});



/*
	Lightbox JS: Fullsize Image Overlays  -- Pyxy Gallery variant!
	Original  version - Lokesh Dhakar - http://www.huddletogether.com
	Modified  version - Sean McBride ( http://www.alwaysbeta.com )  -- with MooFX effects
	pyxyLight version - Thomas Whaples (http://fennecfoxen.org)

	For more information on this script, visit:
		http://huddletogether.com/projects/lightbox/
	and get the official Lightbox code.
		
	This code was licensed under the Creative Commons Attribution license.
	
*/


var pyxyLight = new Class({
	// If you would like to use a loading image, point to it in the next line, otherwise leave as-is.
	loadingImage: '/wp-content/themes/alwaysBetaTheme/images/loading.gif',
	customOpacity: 		.6,	// overlay opacity
	transitionDuration: 400, // fade in/out and wipe
	extraSpaceHeight: 100,
	extraSpaceWidth: 10,
	
	// getPageSize()
	// Returns array with page width, height and window width, height
	// Original core code from - quirksmode.org
	// Rewrite to use MooTools, be terse: Thomas Whaples
	getPageSize: function(){
		var xScroll = Window.getScrollWidth();
		var yScroll = Window.getScrollHeight();
		var windowHeight = Window.getHeight();
		var windowWidth = Window.getWidth();
		// for small pages with total height/width less the viewport size
		var pageHeight = (yScroll < windowHeight)
			? windowHeight
			: yScroll;
		var pageWidth = (xScroll < windowWidth)
			? windowWidth
			: xScroll;
		return new Array(pageWidth,pageHeight,windowWidth,windowHeight);
	},


	// showLightbox()
	// Preloads images. Pleaces new image in lightbox then centers and displays.
	showLightbox: function(objLink){
		this.show(objLink.href,this._makeCaption(objLink.title));
	},
	show: function(href,caption,onComplete){
		this._onComplete = onComplete || Class.empty;
		// prep objects
		var objOverlay		= this.objOverlay;
		var objLoadingImage	= this.objLoadingImage;
		var arrayPageSize = this.getPageSize();
		
		caption = caption || this._makeCaption("test");
		
		// center loadingImage if it exists
		if (objLoadingImage) {
			var yScroll = Window.getScrollTop();
			objLoadingImage.style.top = (yScroll + ((arrayPageSize[3] - this.extraSpaceHeight - objLoadingImage.height) / 2) + 'px');
			objLoadingImage.style.left = (((arrayPageSize[0] - this.extraSpaceWidth - objLoadingImage.width) / 2) + 'px');
		}	
	
		// set height of Overlay to take up whole page and show
		objOverlay.style.height = (arrayPageSize[1] + 'px');
		objOverlay.anim.custom(0,this.customOpacity);
		//objOverlay.style.display = 'block';
		
		// preload image
		imgPreload = new Image();
		imgPreload.onload= this._reveal.pass([imgPreload,href,caption],this);
		imgPreload.src = href;
		
		return false;	
	},

	_reveal: function(imgPreload,href,caption){
			this.visibility = 1;
			this.objImage.src = href;
	
			// center lightbox and make sure that the top and left values are not negative
			// and the image placed outside the viewport
			var yScroll = Window.getScrollTop();
			var arrayPageSize = this.getPageSize();
			
			var lightboxTop = yScroll + ((arrayPageSize[3] - this.extraSpaceHeight - imgPreload.height) / 2);
			var lightboxLeft = ((arrayPageSize[0] -  this.extraSpaceWidth - imgPreload.width) / 2);

			this.objLightbox.style.top = (lightboxTop < 0) ? "0px" : lightboxTop + "px";
			this.objLightbox.style.left = (lightboxLeft < 0) ? "0px" : lightboxLeft + "px";
			
			if(caption){
				this.objCaption = caption;
				this.objLightbox.appendChild(caption);
			}
			
			/* delay showing for a short time to keep IE happy and avoid blinks */
			this.objLightbox.anim.toggle.delay(100,this.objLightbox.anim);
	},
	_makeCaption: function(title){
		// create caption
		var objCaption = this.objCaption = new Element("div");
		objCaption.setAttribute('id','pyxylightCaption');
		objCaption.style.display = 'none';
		objCaption.style.display = 'block';
		objCaption.innerHTML = title;
		return objCaption;
	},
	
	_makeSpinner: function(src){
		var link = new Element("a");
		link.setAttribute('href','#');
		link.onclick = hide;
		var img = new Element("img");
		img.src = src;
		img.style.position = 'absolute';
		img.style.zIndex = '150';
		img.alt = 'Loading...';
		link.adopt(objLoadingImage);
		return link;
	},
	
	hide: function(){
		if(this.objCaption){
			this.objCaption.remove();
			this.objCaption = undefined;
		}
		if(this.visibility){
			this.visibility = 0;

			this.objOverlay.anim.custom(this.customOpacity,0);
			this.objLightbox.anim.toggle.delay(100,this.objLightbox.anim);
		}
	},




		/* This function has been modified so it doesn't go looking for
		   <a rel="lightbox"> links anymore. We are handling our own lightboxes
		   here, thank you very much. */
	initialize: function(){ 
		var hideLightbox = (function () {this.hide(); return false;}).bind(this);
		var objBody = document.getElementsByTagName("body").item(0);
		var objOverlay = this.objOverlay = new Element("div");
		
		this.visibility = 0;
		
		objOverlay.setAttribute('id','overlay');
		objOverlay.style.position = 'absolute';
		//objOverlay.style.display = 'none';	// This breaks the animation
		objOverlay.style.top = '0';
		objOverlay.style.left = '0';
		objOverlay.style.zIndex = '90';
	 	objOverlay.style.width = '100%';
		objOverlay.onclick = hideLightbox;
	 	
		objBody.appendChild(objOverlay);
		
		var arrayPageSize = this.getPageSize();
		//var arrayPageScroll = this.getPageScroll();
		
		// create overlay animation and hide it (Added my Sean McB)
		objOverlay.anim = new fx.Opacity(objOverlay, {duration: this.transitionDuration});
		objOverlay.anim.hide();
		

		
		var objLightbox = this.objLightbox = new Element("div");
		objLightbox.setAttribute('id','pyxylight');
		objLightbox.style.position = 'absolute';
		objLightbox.style.zIndex = '100';
		objBody.appendChild(objLightbox);
		
		// create lightbox animation and initially hide
		objLightbox.anim = new fx.Height(
			objLightbox, {
				duration: this.transitionDuration,
				onComplete: (function(){ 
					
					if(this.visibility == 0 && this._onComplete)
						this._onComplete(); 
					}).bind(this)
			}
			);
		objLightbox.anim.hide();
		
		var objLink = new Element("a");
		objLink.setAttribute('href','#');
		objLink.setAttribute('id','pyxylightPhotoLink');
		objLink.setAttribute('title',this.t['close']);
		objLink.onclick = hideLightbox;
		objLightbox.appendChild(objLink);
		
		// create image
		var objImage = this.objImage = new Element("img");
		objImage.setAttribute('id','pyxylightPhoto');
		objLink.appendChild(objImage);
		
	},
	t: {
		'close': 'Click to close'
	}
	
});