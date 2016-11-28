!function($,window,document){"use strict";var BROWSER_IS_IE7,BROWSER_SCROLLBAR_WIDTH,DOMSCROLL,DOWN,DRAG,ENTER,KEYDOWN,KEYUP,MOUSEDOWN,MOUSEENTER,MOUSEMOVE,MOUSEUP,MOUSEWHEEL,NanoScroll,PANEDOWN,RESIZE,SCROLL,SCROLLBAR,TOUCHMOVE,UP,WHEEL,_elementStyle,_prefixStyle,_vendor,cAF,defaults,getBrowserScrollbarWidth,hasTransform,isFFWithBuggyScrollbar,rAF,transform;defaults={paneClass:"nano-pane",sliderClass:"nano-slider",contentClass:"nano-content",iOSNativeScrolling:!1,preventPageScrolling:!1,disableResize:!1,alwaysVisible:!1,flashDelay:1500,sliderMinHeight:20,sliderMaxHeight:null,documentContext:null,windowContext:null},SCROLLBAR="scrollbar",SCROLL="scroll",MOUSEDOWN="mousedown",MOUSEENTER="mouseenter",MOUSEMOVE="mousemove",MOUSEWHEEL="mousewheel",MOUSEUP="mouseup",RESIZE="resize",DRAG="drag",ENTER="enter",UP="up",PANEDOWN="panedown",DOMSCROLL="DOMMouseScroll",DOWN="down",WHEEL="wheel",KEYDOWN="keydown",KEYUP="keyup",TOUCHMOVE="touchmove",BROWSER_IS_IE7="Microsoft Internet Explorer"===window.navigator.appName&&/msie 7./i.test(window.navigator.appVersion)&&window.ActiveXObject,BROWSER_SCROLLBAR_WIDTH=null,rAF=window.requestAnimationFrame,cAF=window.cancelAnimationFrame,_elementStyle=document.createElement("div").style,_vendor=function(){var i,j,len,transform,vendor,vendors;for(vendors=["t","webkitT","MozT","msT","OT"],i=j=0,len=vendors.length;len>j;i=++j)if(vendor=vendors[i],transform=vendors[i]+"ransform",transform in _elementStyle)return vendors[i].substr(0,vendors[i].length-1);return!1}(),_prefixStyle=function(style){return _vendor===!1?!1:""===_vendor?style:_vendor+style.charAt(0).toUpperCase()+style.substr(1)},transform=_prefixStyle("transform"),hasTransform=transform!==!1,getBrowserScrollbarWidth=function(){var outer,outerStyle,scrollbarWidth;return outer=document.createElement("div"),outerStyle=outer.style,outerStyle.position="absolute",outerStyle.width="100px",outerStyle.height="100px",outerStyle.overflow=SCROLL,outerStyle.top="-9999px",document.body.appendChild(outer),scrollbarWidth=outer.offsetWidth-outer.clientWidth,document.body.removeChild(outer),scrollbarWidth},isFFWithBuggyScrollbar=function(){var isOSXFF,ua,version;return ua=window.navigator.userAgent,(isOSXFF=/(?=.+Mac OS X)(?=.+Firefox)/.test(ua))?(version=/Firefox\/\d{2}\./.exec(ua),version&&(version=version[0].replace(/\D+/g,"")),isOSXFF&&+version>23):!1},NanoScroll=function(){function NanoScroll(el,options1){this.el=el,this.options=options1,BROWSER_SCROLLBAR_WIDTH||(BROWSER_SCROLLBAR_WIDTH=getBrowserScrollbarWidth()),this.$el=$(this.el),this.doc=$(this.options.documentContext||document),this.win=$(this.options.windowContext||window),this.body=this.doc.find("body"),this.$content=this.$el.children("."+options.contentClass),this.$content.attr("tabindex",this.options.tabIndex||0),this.content=this.$content[0],this.previousPosition=0,this.options.iOSNativeScrolling&&null!=this.el.style.WebkitOverflowScrolling?this.nativeScrolling():this.generate(),this.createEvents(),this.addEvents(),this.reset()}return NanoScroll.prototype.preventScrolling=function(e,direction){if(this.isActive)if(e.type===DOMSCROLL)(direction===DOWN&&e.originalEvent.detail>0||direction===UP&&e.originalEvent.detail<0)&&e.preventDefault();else if(e.type===MOUSEWHEEL){if(!e.originalEvent||!e.originalEvent.wheelDelta)return;(direction===DOWN&&e.originalEvent.wheelDelta<0||direction===UP&&e.originalEvent.wheelDelta>0)&&e.preventDefault()}},NanoScroll.prototype.nativeScrolling=function(){this.$content.css({WebkitOverflowScrolling:"touch"}),this.iOSNativeScrolling=!0,this.isActive=!0},NanoScroll.prototype.updateScrollValues=function(){var content,direction;content=this.content,this.maxScrollTop=content.scrollHeight-content.clientHeight,this.prevScrollTop=this.contentScrollTop||0,this.contentScrollTop=content.scrollTop,direction=this.contentScrollTop>this.previousPosition?"down":this.contentScrollTop<this.previousPosition?"up":"same",this.previousPosition=this.contentScrollTop,"same"!==direction&&this.$el.trigger("update",{position:this.contentScrollTop,maximum:this.maxScrollTop,direction:direction}),this.iOSNativeScrolling||(this.maxSliderTop=this.paneHeight-this.sliderHeight,this.sliderTop=0===this.maxScrollTop?0:this.contentScrollTop*this.maxSliderTop/this.maxScrollTop)},NanoScroll.prototype.setOnScrollStyles=function(){var cssValue;hasTransform?(cssValue={},cssValue[transform]="translate(0, "+this.sliderTop+"px)"):cssValue={top:this.sliderTop},rAF?(cAF&&this.scrollRAF&&cAF(this.scrollRAF),this.scrollRAF=rAF(function(_this){return function(){return _this.scrollRAF=null,_this.slider.css(cssValue)}}(this))):this.slider.css(cssValue)},NanoScroll.prototype.createEvents=function(){this.events={down:function(_this){return function(e){return _this.isBeingDragged=!0,_this.offsetY=e.pageY-_this.slider.offset().top,_this.slider.is(e.target)||(_this.offsetY=0),_this.pane.addClass("active"),_this.doc.bind(MOUSEMOVE,_this.events[DRAG]).bind(MOUSEUP,_this.events[UP]),_this.body.bind(MOUSEENTER,_this.events[ENTER]),!1}}(this),drag:function(_this){return function(e){return _this.sliderY=e.pageY-_this.$el.offset().top-_this.paneTop-(_this.offsetY||.5*_this.sliderHeight),_this.scroll(),_this.contentScrollTop>=_this.maxScrollTop&&_this.prevScrollTop!==_this.maxScrollTop?_this.$el.trigger("scrollend"):0===_this.contentScrollTop&&0!==_this.prevScrollTop&&_this.$el.trigger("scrolltop"),!1}}(this),up:function(_this){return function(e){return _this.isBeingDragged=!1,_this.pane.removeClass("active"),_this.doc.unbind(MOUSEMOVE,_this.events[DRAG]).unbind(MOUSEUP,_this.events[UP]),_this.body.unbind(MOUSEENTER,_this.events[ENTER]),!1}}(this),resize:function(_this){return function(e){_this.reset()}}(this),panedown:function(_this){return function(e){return _this.sliderY=(e.offsetY||e.originalEvent.layerY)-.5*_this.sliderHeight,_this.scroll(),_this.events.down(e),!1}}(this),scroll:function(_this){return function(e){_this.updateScrollValues(),_this.isBeingDragged||(_this.iOSNativeScrolling||(_this.sliderY=_this.sliderTop,_this.setOnScrollStyles()),null!=e&&(_this.contentScrollTop>=_this.maxScrollTop?(_this.options.preventPageScrolling&&_this.preventScrolling(e,DOWN),_this.prevScrollTop!==_this.maxScrollTop&&_this.$el.trigger("scrollend")):0===_this.contentScrollTop&&(_this.options.preventPageScrolling&&_this.preventScrolling(e,UP),0!==_this.prevScrollTop&&_this.$el.trigger("scrolltop"))))}}(this),wheel:function(_this){return function(e){var delta;if(null!=e)return delta=e.delta||e.wheelDelta||e.originalEvent&&e.originalEvent.wheelDelta||-e.detail||e.originalEvent&&-e.originalEvent.detail,delta&&(_this.sliderY+=-delta/3),_this.scroll(),!1}}(this),enter:function(_this){return function(e){var ref;if(_this.isBeingDragged)return 1!==(e.buttons||e.which)?(ref=_this.events)[UP].apply(ref,arguments):void 0}}(this)}},NanoScroll.prototype.addEvents=function(){var events;this.removeEvents(),events=this.events,this.options.disableResize||this.win.bind(RESIZE,events[RESIZE]),this.iOSNativeScrolling||(this.slider.bind(MOUSEDOWN,events[DOWN]),this.pane.bind(MOUSEDOWN,events[PANEDOWN]).bind(MOUSEWHEEL+" "+DOMSCROLL,events[WHEEL])),this.$content.bind(SCROLL+" "+MOUSEWHEEL+" "+DOMSCROLL+" "+TOUCHMOVE,events[SCROLL])},NanoScroll.prototype.removeEvents=function(){var events;events=this.events,this.win.unbind(RESIZE,events[RESIZE]),this.iOSNativeScrolling||(this.slider.unbind(),this.pane.unbind()),this.$content.unbind(SCROLL+" "+MOUSEWHEEL+" "+DOMSCROLL+" "+TOUCHMOVE,events[SCROLL])},NanoScroll.prototype.generate=function(){var contentClass,cssRule,currentPadding,options,pane,paneClass,sliderClass;return options=this.options,paneClass=options.paneClass,sliderClass=options.sliderClass,contentClass=options.contentClass,(pane=this.$el.children("."+paneClass)).length||pane.children("."+sliderClass).length||this.$el.append('<div class="'+paneClass+'"><div class="'+sliderClass+'" /></div>'),this.pane=this.$el.children("."+paneClass),this.slider=this.pane.find("."+sliderClass),0===BROWSER_SCROLLBAR_WIDTH&&isFFWithBuggyScrollbar()?(currentPadding=window.getComputedStyle(this.content,null).getPropertyValue("padding-right").replace(/[^0-9.]+/g,""),cssRule={right:-14,paddingRight:+currentPadding+14}):BROWSER_SCROLLBAR_WIDTH&&(cssRule={right:-BROWSER_SCROLLBAR_WIDTH},this.$el.addClass("has-scrollbar")),null!=cssRule&&this.$content.css(cssRule),this},NanoScroll.prototype.restore=function(){this.stopped=!1,this.iOSNativeScrolling||this.pane.show(),this.addEvents()},NanoScroll.prototype.reset=function(){var content,contentHeight,contentPosition,contentStyle,contentStyleOverflowY,paneBottom,paneHeight,paneOuterHeight,paneTop,parentMaxHeight,right,sliderHeight;return this.iOSNativeScrolling?void(this.contentHeight=this.content.scrollHeight):(this.$el.find("."+this.options.paneClass).length||this.generate().stop(),this.stopped&&this.restore(),content=this.content,contentStyle=content.style,contentStyleOverflowY=contentStyle.overflowY,BROWSER_IS_IE7&&this.$content.css({height:this.$content.height()}),contentHeight=content.scrollHeight+BROWSER_SCROLLBAR_WIDTH,parentMaxHeight=parseInt(this.$el.css("max-height"),10),parentMaxHeight>0&&(this.$el.height(""),this.$el.height(content.scrollHeight>parentMaxHeight?parentMaxHeight:content.scrollHeight)),paneHeight=this.pane.outerHeight(!1),paneTop=parseInt(this.pane.css("top"),10),paneBottom=parseInt(this.pane.css("bottom"),10),paneOuterHeight=paneHeight+paneTop+paneBottom,sliderHeight=Math.round(paneOuterHeight/contentHeight*paneOuterHeight),sliderHeight<this.options.sliderMinHeight?sliderHeight=this.options.sliderMinHeight:null!=this.options.sliderMaxHeight&&sliderHeight>this.options.sliderMaxHeight&&(sliderHeight=this.options.sliderMaxHeight),contentStyleOverflowY===SCROLL&&contentStyle.overflowX!==SCROLL&&(sliderHeight+=BROWSER_SCROLLBAR_WIDTH),this.maxSliderTop=paneOuterHeight-sliderHeight,this.contentHeight=contentHeight,this.paneHeight=paneHeight,this.paneOuterHeight=paneOuterHeight,this.sliderHeight=sliderHeight,this.paneTop=paneTop,this.slider.height(sliderHeight),this.events.scroll(),this.pane.show(),this.isActive=!0,content.scrollHeight===content.clientHeight||this.pane.outerHeight(!0)>=content.scrollHeight&&contentStyleOverflowY!==SCROLL?(this.pane.hide(),this.isActive=!1):this.el.clientHeight===content.scrollHeight&&contentStyleOverflowY===SCROLL?this.slider.hide():this.slider.show(),this.pane.css({opacity:this.options.alwaysVisible?1:"",visibility:this.options.alwaysVisible?"visible":""}),contentPosition=this.$content.css("position"),("static"===contentPosition||"relative"===contentPosition)&&(right=parseInt(this.$content.css("right"),10),right&&this.$content.css({right:"",marginRight:right})),this)},NanoScroll.prototype.scroll=function(){return this.isActive?(this.sliderY=Math.max(0,this.sliderY),this.sliderY=Math.min(this.maxSliderTop,this.sliderY),this.$content.scrollTop(this.maxScrollTop*this.sliderY/this.maxSliderTop),this.iOSNativeScrolling||(this.updateScrollValues(),this.setOnScrollStyles()),this):void 0},NanoScroll.prototype.scrollBottom=function(offsetY){return this.isActive?(this.$content.scrollTop(this.contentHeight-this.$content.height()-offsetY).trigger(MOUSEWHEEL),this.stop().restore(),this):void 0},NanoScroll.prototype.scrollTop=function(offsetY){return this.isActive?(this.$content.scrollTop(+offsetY).trigger(MOUSEWHEEL),this.stop().restore(),this):void 0},NanoScroll.prototype.scrollTo=function(node){return this.isActive?(this.scrollTop(this.$el.find(node).get(0).offsetTop),this):void 0},NanoScroll.prototype.stop=function(){return cAF&&this.scrollRAF&&(cAF(this.scrollRAF),this.scrollRAF=null),this.stopped=!0,this.removeEvents(),this.iOSNativeScrolling||this.pane.hide(),this},NanoScroll.prototype.destroy=function(){return this.stopped||this.stop(),!this.iOSNativeScrolling&&this.pane.length&&this.pane.remove(),BROWSER_IS_IE7&&this.$content.height(""),this.$content.removeAttr("tabindex"),this.$el.hasClass("has-scrollbar")&&(this.$el.removeClass("has-scrollbar"),this.$content.css({right:""})),this},NanoScroll.prototype.flash=function(){return!this.iOSNativeScrolling&&this.isActive?(this.reset(),this.pane.addClass("flashed"),setTimeout(function(_this){return function(){_this.pane.removeClass("flashed")}}(this),this.options.flashDelay),this):void 0},NanoScroll}(),$.fn.nanoScroller=function(settings){return this.each(function(){var options,scrollbar;if((scrollbar=this.nanoscroller)||(options=$.extend({},defaults,settings),this.nanoscroller=scrollbar=new NanoScroll(this,options)),settings&&"object"==typeof settings){if($.extend(scrollbar.options,settings),null!=settings.scrollBottom)return scrollbar.scrollBottom(settings.scrollBottom);if(null!=settings.scrollTop)return scrollbar.scrollTop(settings.scrollTop);if(settings.scrollTo)return scrollbar.scrollTo(settings.scrollTo);if("bottom"===settings.scroll)return scrollbar.scrollBottom(0);if("top"===settings.scroll)return scrollbar.scrollTop(0);if(settings.scroll&&settings.scroll instanceof $)return scrollbar.scrollTo(settings.scroll);if(settings.stop)return scrollbar.stop();if(settings.destroy)return scrollbar.destroy();if(settings.flash)return scrollbar.flash()}return scrollbar.reset()})},$.fn.nanoScroller.Constructor=NanoScroll}(jQuery,window,document);