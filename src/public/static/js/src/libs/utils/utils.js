/* Utils.js */

var browser = (function () {
	var a = (function (d) {
		d = d.toLowerCase();
		var e = /(chrome)[ \/]([\w.]+)/.exec(d),
			g = /(webkit)[ \/]([\w.]+)/.exec(d),
			f = /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(d),
			i = /(msie) ([\w.]+)/.exec(d),
			c = /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(d),
			h = e || g || f || i || d.indexOf("compatible") < 0 && c || [];
		return {
			brw: h[1] || "",
			ver: h[2] || "0"
		}
	})(navigator.userAgent),
		b = {};
	if (a.brw) {
		b[a.brw] = true;
		b.version = a.ver
	}
	if (b.chrome) {
		b.webkit = true
	} else {
		if (b.webkit) {
			b.safari = true
		}
	}
	return b
})();

if (browser.msie) {
	switch (browser.version) {
	case '10.0':
		$('html').addClass('lt-ie11');
		break;
	case '11.0':
		$('html').addClass('lt-ie12');
		break;
	default:

		break;
	}
};

/* Log */

window.log = (typeof (log) != "undefined") ? log : function () {
	var a = function () {
		return /(local\.|dev\.)/gi.test(document.domain)
	};
	if (typeof (console) != "undefined" && a()) {
		if (typeof (console.log.apply) != "undefined") {
			console.log.apply(console, arguments)
		} else {
			console.log(Array.prototype.slice.call(arguments))
		}
	}
};


var Cookie = {
	create: function (c, d, e) {
		if (e) {
			var b = new Date();
			b.setTime(b.getTime() + (e * 24 * 60 * 60 * 1000));
			var a = "; expires=" + b.toGMTString()
		} else {
			var a = ""
		}
		//document.cookie = c + "=" + d + a + "; path=/application/busqueda/";
		document.cookie = c + "=" + d + a + "; path=/";
		return this
	},
	read: function (b) {
		var e = b + "=";
		var a = document.cookie.split(";");
		for (var d = 0; d < a.length; d++) {
			var f = a[d];
			while (f.charAt(0) == " ") {
				f = f.substring(1, f.length)
			}
			if (f.indexOf(e) == 0) {
				return f.substring(e.length, f.length)
			}
		}
		return null
	},
	del: function (a) {
		return this.create(a, "", -1)
	}
};


/*utils*/

var utils = {
	//Author: Jhonnatan
	orderJsonBy : function(json){   
		var sortable = [];   
		for (var key in json){        
			sortable.push({id:key,name:json[key]});        
		}    
		sorter = sortable.sort(function(a, b){
			if(typeof a.name == 'string') {
				var nameA=a.name.toLowerCase(), 
					nameB=b.name.toLowerCase();
				if (nameA < nameB) //sort string ascending
					return -1 
				if (nameA > nameB)
					return 1
				return 0
			}else{
				return a.name-b.name //default return value (no sorting)
			}
		})
		return sorter;    
	},

	concat: function (c, b) {
		var a = "",
			e, f;
		f = (c.length <= b) ? c.length : b;
		for (var d = 0; d < f; d++) {
			e = ((f - 1) == d) ? "" : " ";
			if (typeof c[d] !== "undefined") {
				a = a + c[d] + e
			} else {
				break
			}
		}
		return a
	},
	unique: function () {
		return Math.random().toString(36).substr(2)
	},
	vLetter: function (a, b) {
		var c = {
			type: 1,
			len: 5,
			expr: null
		}, f = utils.unique();
		c = $.extend(c, b);
		window[f] = c.len;
		var d = function () {
			var k = $(this),
				i = $.trim(k.val()),
				h = "",
				j, g;
			if (i == "") {
				g = []
			} else {
				g = (c.expr == null) ? i.split(/\s+/g) : i.match(c.expr)
			} if (g.length > window[f]) {
				k.val(utils.concat(g, window[f]))
			} else {
				if (g.length == window[f] && i.charAt(i.length - 1) == " ") {
					k.val(utils.concat(g, window[f]))
				}
			}
			c.callback && c.callback(g.length, utils.concat(g, window[f]), f)
		}, e = function () {
				var g = $(this);
				setTimeout(function () {
					var j = $.trim(g.val()),
						i = "",
						k, h;
					if (j == "") {
						h = []
					} else {
						h = (c.expr == null) ? j.split(/\s+/g) : j.match(c.expr)
					} if (h.length > window[f]) {
						g.val(utils.concat(h, window[f]))
					} else {
						if (h.length == window[f] && j.charAt(j.length - 1) == " ") {
							g.val(utils.concat(h, window[f]))
						}
					}
					c.callback && c.callback(h.length, utils.concat(h, window[f]), f)
				}, 300)
			};
		if (c.type) {
			$(a).bind("keyup click", d);
			$(a).bind("paste cut", e)
		} else {
			$(a).unbind("keyup click", d);
			$(a).unbind("paste cut", e)
		}
		return f
	},
	vLength: function (b, a, c) {
		$(b).bind("keyup", function (f) {
			var g = $(this),
				d = g.val();
			c && c(d.length);
			if (d.length > a) {
				g.val(d.substr(0, a))
			}
		}).bind("paste", function () {
			var d = $(this);
			setTimeout(function () {
				var e = d.val();
				c && c(e.length);
				if (e.length > a) {
					d.val(e.substr(0, a))
				}
			}, 300)
		})
	},
	validBlanks: function (e, f) {
		var b = e.split(" "),
			d, a = "";
		acu = 0;
		for (var c = 0; c < b.length; c++) {
			d = ((b.length - 1) == c) ? "" : " ";
			if (b[c] != "") {
				acu = 0;
				b[c] = f && f(b[c]) || b[c];
				a = a + b[c] + d;
				acu = acu + 1
			} else {
				acu = acu + 1;
				a = (acu <= 1) ? a + " " : a
			}
		}
		return a
	},
	blockInpLen: function (b, a, d, e) {
		var c = function (h) {
			if (typeof d != "undefined" && d == false) {
				var f = h.split(" ");
				for (var g = 0; g < f.length; g++) {
					f[g] = (f[g] != "") ? f[g].substr(0, a) : f[g]
				}
				return utils.concat(f, f.length)
			} else {
				return utils.validBlanks(h, function (i) {
					return i.substr(0, a)
				})
			}
		};
		$(b).bind("keyup", function (f) {
			if ($.trim(this.value) == "") {
				this.value = ""
			} else {
				this.value = c(this.value)
			}
			e && e()
		})
	},
	validAjax: function (a, d, c) {
		var b = {
			success: null,
			error: null
		};
		b = $.extend(b, d);
		c = c || false;
		if (parseFloat(a.session.state) || c) {
			if (parseFloat(a.data.state)) {
				b.success && b.success(a)
			} else {
				b.error && b.error(a)
			}
		} else {
			window.location = yOSON.baseHost + a.session.href
		}
	},
	ajax: function (b, d) {
		var a = {
			url: "",
			dataType: "json"
		}, c = {
				success: b.success,
				error: b.error
			};
		a = $.extend(a, b);
		d = d || false;

		a.success = function (f, e, g) {
			if (parseFloat(f.session.state) || d) {
				if (parseFloat(f.data.state)) {
					c.success && c.success(f.data, f, e, g)
				} else {
					c.error && c.error(f.data, f, g, e, c.error)
				}
			} else {
				window.location = yOSON.baseHost + f.session.href
			}
		};

		//console.log(a);

		$.ajax(a);
	},
	loader: function (tag, isShow, a, newClass) {
		if (isShow) {
			var d = (a) ? " fix" : "";
			var newClass = (newClass) ? newClass : "";
			tag.append("<div class='load-wrap" + d + ' ' + newClass + "'><span></span></div>")
		} else {
			tag.find(".load-wrap").remove()
		}
	},
	responseParsley: function (type, exist, tag, msn){
		switch (type){
			case 'error' :
				if (exist){
					tag.prepend('<div class="parsley_error more filled"><div>' + msn + '</div></div>');
				}else{
					tag.parent().find('.parsley_error.more').remove();
				}
				break;
			case 'success' :
				if (exist){
					if (typeof(msn) === 'undefined'){
						msn = 'Correcto';
					}
					tag.prepend('<div class="parsley_good more"><i class="icon icon_check"></i>' + msn + '</div>');
				}else{
					tag.parent().find('.parsley_good.more').remove();
				}
				break;
		}
	},
	resetForm: function (a) {
		$(":input", a).each(function () {
			var c = this.type;
			var b = this.tagName.toLowerCase();
			if (c == "text" || c == "password" || b == "textarea") {
				this.value = ""
			} else {
				if (c == "checkbox" || c == "radio") {
					this.checked = false
				} else {
					if (b == "select") {
						this.selectedIndex = 0
					}
				}
			}
		})
	},
	detectFrmClean: function (d) {
		var b = true,
			c, a;
		d.find(":input").each(function () {
			c = this.type, a = this.tagName.toLowerCase();
			if (c == "text" || c == "password" || a == "textarea") {
				b = (this.value != "") ? false : true
			} else {
				if (c == "checkbox" || c == "radio") {
					b = (this.checked) ? false : true
				} else {
					if (a == "select") {
						b = (this.selectedIndex != 0) ? false : true
					}
				}
			}
			return b
		});
		return b
	},
	boxMessage: function (content, type, text, time){
		var textHTML = '<div class="box-message">'+ text +'</div>',
			time = time || 1000
		switch (type){
			case 'prepend' :
				content.prepend(textHTML);
			break;
			case 'append' :
				content.append(textHTML);
			break;
			case 'insertAfter' :
				$(textHTML).insertAfter(content);
				content = content.parent()
			break;
		}
		setTimeout(function(){
			content.find(".box-message").fadeOut(function(){
				$(this).remove();
			})
		}, time)
	}
};


(function (a) {
	a.fn.serializeFormJSON = function (e) {
		var d = {}, c = e || [],
			b = this.serializeArray();
		a.each(b, function () {
			if (a.inArray(this.name, c) == -1) {
				if (d[this.name]) {
					if (!d[this.name].push) {
						d[this.name] = [d[this.name]]
					}
					d[this.name].push(this.value || "")
				} else {
					d[this.name] = this.value || ""
				}
			}
		});
		return d
	}
})(jQuery);


/* Tipsy Tooltip validate */
var tip = null;
(function ($) {
	$.fn.tipsy = function (options) {
		options = $.extend({}, $.fn.tipsy.defaults, options);
		return this.each(function () {
			var opts = $.fn.tipsy.elementOptions(this, options);
			$(this).hover(function () {
				$.data(this, "cancel.tipsy", true);
				var tip = $.data(this, "active.tipsy");
				if (!tip) {
					tip = $('<div class="tipsy"><div class="tipsy-inner"/></div>');
					tip.css({
						position: "absolute",
						zIndex: 100000
					});
					$.data(this, "active.tipsy", tip)
				}
				if ($(this).attr("title") || typeof ($(this).attr("original-title")) != "string") {
					$(this).attr("original-title", $(this).attr("title") || "").removeAttr("title")
				}
				var title;
				if (typeof opts.title == "string") {
					title = $(this).attr(opts.title == "title" ? "original-title" : opts.title)
				} else {
					if (typeof opts.title == "function") {
						title = opts.title.call(this)
					}
				}
				tip.find(".tipsy-inner")[opts.html ? "html" : "text"](title || opts.fallback);
				var pos = $.extend({}, $(this).offset(), {
					width: this.offsetWidth,
					height: this.offsetHeight
				});
				tip.get(0).className = "tipsy";
				tip.remove().css({
					top: 0,
					left: 0,
					visibility: "hidden",
					display: "block"
				}).appendTo(document.body);
				var actualWidth = tip[0].offsetWidth,
					actualHeight = tip[0].offsetHeight;
				var gravity = (typeof opts.gravity == "function") ? opts.gravity.call(this) : opts.gravity;
				switch (gravity.charAt(0)) {
				case "n":
					tip.css({
						top: pos.top + pos.height,
						left: pos.left + pos.width / 2 - actualWidth / 2
					}).addClass("tipsy-north");
					break;
				case "s":
					tip.css({
						top: pos.top - actualHeight,
						left: pos.left + pos.width / 2 - actualWidth / 2
					}).addClass("tipsy-south");
					break;
				case "e":
					tip.css({
						top: pos.top + pos.height / 2 - actualHeight / 2,
						left: pos.left - actualWidth
					}).addClass("tipsy-east");
					break;
				case "w":
					tip.css({
						top: pos.top + pos.height / 2 - actualHeight / 2,
						left: pos.left + pos.width
					}).addClass("tipsy-west");
					break
				}
				if (opts.fade) {
					tip.css({
						opacity: 0,
						display: "block",
						visibility: "visible"
					}).animate({
						opacity: 0.8
					})
				} else {
					tip.css({
						visibility: "visible"
					})
				}
			}, function () {
				$.data(this, "cancel.tipsy", false);
				var self = this;
				setTimeout(function () {
					if ($.data(this, "cancel.tipsy")) {
						return
					}
					var tip = $.data(self, "active.tipsy");
					if (opts.fade) {
						tip.stop().fadeOut(function () {
							$(this).remove()
						})
					} else {
						if (tip) {
							tip.remove()
						}
					}
				}, 100)
			})
		})
	};
	$.fn.tipsy.elementOptions = function (ele, options) {
		return $.metadata ? $.extend({}, options, $(ele).metadata()) : options
	};
	$.fn.tipsy.defaults = {
		fade: false,
		fallback: "",
		gravity: "n",
		html: false,
		title: "title"
	};
	$.fn.tipsy.autoNS = function () {
		return $(this).offset().top > ($(document).scrollTop() + $(window).height() / 2) ? "s" : "n"
	};
	$.fn.tipsy.autoWE = function () {
		return $(this).offset().left > ($(document).scrollLeft() + $(window).width() / 2) ? "e" : "w"
	}
})(jQuery);


/*multiple scripts enhance for $.getScript*/
var getScript = jQuery.getScript;
jQuery.getScript = function (resources, callback) {
	var length = resources.length,
		handler = function () {
			counter++;
		},
		deferreds = [],
		counter = 0;

	for (var idx = 0; idx < length; idx++) {
		deferreds.push(
			getScript(resources[idx], handler)
		);
	}

	jQuery.when.apply(null, deferreds).then(function () {
		callback && callback();
	});
};


/*scrol top hash*/
$.fn.scrollWindow = function (e) {
	var t = {
		duration: "slow",
		easing: "swing",
		lowPosition: 0,
		hashLocation: false
	};
	var n = $.extend({}, t, e);
	var r = function (e) {
		var t = $(e).offset().top - n.lowPosition;
		$("html,body").animate({
			scrollTop: t
		}, n.duration, n.easing, function () {
			if (n.hashLocation) {
				location.hash = e
			}
		});
		return false
	};
	return this.each(function () {
		$(this).on("click", function (e) {
			var t = $(this).attr("href");
			r(t);
			e.preventDefault()
		})
	})
}


/*
 * Realiza el TrackEvent de google analytics
 */
var executeAnalytics = function (p1,p2,p3) {
	var pr1 = p1.removeAccents().replace(/ /g,'_').replace(/___/g,'_');
	var pr2 = p2.removeAccents().replace(/ /g,'_').replace(/___/g,'_');
	var pr3 = p3.removeAccents().replace(/ /g,'_').replace(/___/g,'_');
	//echo('_trackEvent,' + pr1 + ',' + pr2 + ',' + pr3);

	//console.log('_trackEvent,' + pr1 + ',' + pr2 + ',' + pr3);
	//alert('_trackEvent,' + pr1 + ',' + pr2 + ',' + pr3)
	_gaq.push(['_trackEvent',pr1,pr2, pr3]);
};


//Array.prototype.indexOf
if(!Array.prototype.indexOf) {
	Array.prototype.indexOf = function(needle) {
		for(var i = 0; i < this.length; i++) {
			if(this[i] === needle) {
				return i;
			}
		}
		return -1;
	};
}

if (!Array.prototype.lastIndexOf) {
	Array.prototype.lastIndexOf = function(searchElement /*, fromIndex*/) {
		//'use strict';

		if (this == null) {
			throw new TypeError();
		}

		var n, k,
		t = Object(this),
		len = t.length >>> 0;
		if (len === 0) {
			return -1;
		}

		n = len;
		if (arguments.length > 1) {
			n = Number(arguments[1]);
			if (n != n) {
				n = 0;
			}
			else if (n != 0 && n != (1 / 0) && n != -(1 / 0)) {
				n = (n > 0 || -1) * Math.floor(Math.abs(n));
			}
		}

		for (k = n >= 0
			? Math.min(n, len - 1)
			: len - Math.abs(n); k >= 0; k--) {
				if (k in t && t[k] === searchElement) {
					return k;
				}
			}
			return -1;
	};
}



if (!Array.prototype.charAt) {
	String.prototype.charAt = function(pos){
		//CheckCoercible(this);
		var S = this.toString();
		var position = Number(pos);
		var size = S.length;
		if (position < 0 || position >= size) return '';
		//console.log(S[pos]);
		return S[pos];
	}
}




String.prototype.substrByLastSign = function(sign){
	return this.substr(this.lastIndexOf(sign)+1, this.length);
}


//format number
function NumberFormat(e,t){this.VERSION="Number Format v1.5.4";this.COMMA=",";this.PERIOD=".";this.DASH="-";this.LEFT_PAREN="(";this.RIGHT_PAREN=")";this.LEFT_OUTSIDE=0;this.LEFT_INSIDE=1;this.RIGHT_INSIDE=2;this.RIGHT_OUTSIDE=3;this.LEFT_DASH=0;this.RIGHT_DASH=1;this.PARENTHESIS=2;this.NO_ROUNDING=-1;this.num;this.numOriginal;this.hasSeparators=false;this.separatorValue;this.inputDecimalValue;this.decimalValue;this.negativeFormat;this.negativeRed;this.hasCurrency;this.currencyPosition;this.currencyValue;this.places;this.roundToPlaces;this.truncate;this.setNumber=setNumberNF;this.toUnformatted=toUnformattedNF;this.setInputDecimal=setInputDecimalNF;this.setSeparators=setSeparatorsNF;this.setCommas=setCommasNF;this.setNegativeFormat=setNegativeFormatNF;this.setNegativeRed=setNegativeRedNF;this.setCurrency=setCurrencyNF;this.setCurrencyPrefix=setCurrencyPrefixNF;this.setCurrencyValue=setCurrencyValueNF;this.setCurrencyPosition=setCurrencyPositionNF;this.setPlaces=setPlacesNF;this.toFormatted=toFormattedNF;this.toPercentage=toPercentageNF;this.getOriginal=getOriginalNF;this.moveDecimalRight=moveDecimalRightNF;this.moveDecimalLeft=moveDecimalLeftNF;this.getRounded=getRoundedNF;this.preserveZeros=preserveZerosNF;this.justNumber=justNumberNF;this.expandExponential=expandExponentialNF;this.getZeros=getZerosNF;this.moveDecimalAsString=moveDecimalAsStringNF;this.moveDecimal=moveDecimalNF;this.addSeparators=addSeparatorsNF;if(t==null){this.setNumber(e,this.PERIOD)}else{this.setNumber(e,t)}this.setCommas(true);this.setNegativeFormat(this.LEFT_DASH);this.setNegativeRed(false);this.setCurrency(false);this.setCurrencyPrefix("$");this.setPlaces(2)}function setInputDecimalNF(e){this.inputDecimalValue=e}function setNumberNF(e,t){if(t!=null){this.setInputDecimal(t)}this.numOriginal=e;this.num=this.justNumber(e)}function toUnformattedNF(){return this.num}function getOriginalNF(){return this.numOriginal}function setNegativeFormatNF(e){this.negativeFormat=e}function setNegativeRedNF(e){this.negativeRed=e}function setSeparatorsNF(e,t,n){this.hasSeparators=e;if(t==null)t=this.COMMA;if(n==null)n=this.PERIOD;if(t==n){this.decimalValue=n==this.PERIOD?this.COMMA:this.PERIOD}else{this.decimalValue=n}this.separatorValue=t}function setCommasNF(e){this.setSeparators(e,this.COMMA,this.PERIOD)}function setCurrencyNF(e){this.hasCurrency=e}function setCurrencyValueNF(e){this.currencyValue=e}function setCurrencyPrefixNF(e){this.setCurrencyValue(e);this.setCurrencyPosition(this.LEFT_OUTSIDE)}function setCurrencyPositionNF(e){this.currencyPosition=e}function setPlacesNF(e,t){this.roundToPlaces=!(e==this.NO_ROUNDING);this.truncate=t!=null&&t;this.places=e<0?0:e}function addSeparatorsNF(e,t,n,r){e+="";var i=e.indexOf(t);var s="";if(i!=-1){s=n+e.substring(i+1,e.length);e=e.substring(0,i)}var o=/(\d+)(\d{3})/;while(o.test(e)){e=e.replace(o,"$1"+r+"$2")}return e+s}function toFormattedNF(){var e;var t=this.num;var n;var r=new Array(2);if(this.roundToPlaces){t=this.getRounded(t);n=this.preserveZeros(Math.abs(t))}else{n=this.expandExponential(Math.abs(t))}if(this.hasSeparators){n=this.addSeparators(n,this.PERIOD,this.decimalValue,this.separatorValue)}else{n=n.replace(new RegExp("\\"+this.PERIOD),this.decimalValue)}var i="";var s="";var o="";var u="";var a="";var f="";var l="";var c="";var h=this.negativeFormat==this.PARENTHESIS?this.LEFT_PAREN:this.DASH;var p=this.negativeFormat==this.PARENTHESIS?this.RIGHT_PAREN:this.DASH;if(this.currencyPosition==this.LEFT_OUTSIDE){if(t<0){if(this.negativeFormat==this.LEFT_DASH||this.negativeFormat==this.PARENTHESIS)u=h;if(this.negativeFormat==this.RIGHT_DASH||this.negativeFormat==this.PARENTHESIS)a=p}if(this.hasCurrency)i=this.currencyValue}else if(this.currencyPosition==this.LEFT_INSIDE){if(t<0){if(this.negativeFormat==this.LEFT_DASH||this.negativeFormat==this.PARENTHESIS)s=h;if(this.negativeFormat==this.RIGHT_DASH||this.negativeFormat==this.PARENTHESIS)l=p}if(this.hasCurrency)o=this.currencyValue}else if(this.currencyPosition==this.RIGHT_INSIDE){if(t<0){if(this.negativeFormat==this.LEFT_DASH||this.negativeFormat==this.PARENTHESIS)s=h;if(this.negativeFormat==this.RIGHT_DASH||this.negativeFormat==this.PARENTHESIS)l=p}if(this.hasCurrency)f=this.currencyValue}else if(this.currencyPosition==this.RIGHT_OUTSIDE){if(t<0){if(this.negativeFormat==this.LEFT_DASH||this.negativeFormat==this.PARENTHESIS)u=h;if(this.negativeFormat==this.RIGHT_DASH||this.negativeFormat==this.PARENTHESIS)a=p}if(this.hasCurrency)c=this.currencyValue}n=i+s+o+u+n+a+f+l+c;if(this.negativeRed&&t<0){n='<font color="red">'+n+"</font>"}return n}function toPercentageNF(){nNum=this.num*100;nNum=this.getRounded(nNum);return nNum+"%"}function getZerosNF(e){var t="";var n;for(n=0;n<e;n++){t+="0"}return t}function expandExponentialNF(e){if(isNaN(e))return e;var t=parseFloat(e)+"";var n=t.toLowerCase().indexOf("e");if(n!=-1){var r=t.toLowerCase().indexOf("+");var i=t.toLowerCase().indexOf("-",n);var s=t.substring(0,n);if(i!=-1){var o=t.substring(i+1,t.length);s=this.moveDecimalAsString(s,true,parseInt(o))}else{if(r==-1)r=n;var o=t.substring(r+1,t.length);s=this.moveDecimalAsString(s,false,parseInt(o))}t=s}return t}function moveDecimalRightNF(e,t){var n="";if(t==null){n=this.moveDecimal(e,false)}else{n=this.moveDecimal(e,false,t)}return n}function moveDecimalLeftNF(e,t){var n="";if(t==null){n=this.moveDecimal(e,true)}else{n=this.moveDecimal(e,true,t)}return n}function moveDecimalAsStringNF(e,t,n){var r=arguments.length<3?this.places:n;if(r<=0)return e;var i=e+"";var s=this.getZeros(r);var o=new RegExp("([0-9.]+)");if(t){i=i.replace(o,s+"$1");var u=new RegExp("(-?)([0-9]*)([0-9]{"+r+"})(\\.?)");i=i.replace(u,"$1$2.$3")}else{var a=o.exec(i);if(a!=null){i=i.substring(0,a.index)+a[1]+s+i.substring(a.index+a[0].length)}var u=new RegExp("(-?)([0-9]*)(\\.?)([0-9]{"+r+"})");i=i.replace(u,"$1$2$4.")}i=i.replace(/\.$/,"");return i}function moveDecimalNF(e,t,n){var r="";if(n==null){r=this.moveDecimalAsString(e,t)}else{r=this.moveDecimalAsString(e,t,n)}return parseFloat(r)}function getRoundedNF(e){e=this.moveDecimalRight(e);if(this.truncate){e=e>=0?Math.floor(e):Math.ceil(e)}else{e=Math.round(e)}e=this.moveDecimalLeft(e);return e}function preserveZerosNF(e){var t;e=this.expandExponential(e);if(this.places<=0)return e;var n=e.indexOf(".");if(n==-1){e+=".";for(t=0;t<this.places;t++){e+="0"}}else{var r=e.length-1-n;var i=this.places-r;for(t=0;t<i;t++){e+="0"}}return e}function justNumberNF(e){newVal=e+"";var t=false;if(newVal.indexOf("%")!=-1){newVal=newVal.replace(/\%/g,"");t=true}var n=new RegExp("[^\\"+this.inputDecimalValue+"\\d\\-\\+\\(\\)eE]","g");newVal=newVal.replace(n,"");var r=new RegExp("["+this.inputDecimalValue+"]","g");var i=r.exec(newVal);if(i!=null){var s=newVal.substring(i.index+i[0].length);newVal=newVal.substring(0,i.index)+this.PERIOD+s.replace(r,"")}if(newVal.charAt(newVal.length-1)==this.DASH){newVal=newVal.substring(0,newVal.length-1);newVal="-"+newVal}else if(newVal.charAt(0)==this.LEFT_PAREN&&newVal.charAt(newVal.length-1)==this.RIGHT_PAREN){newVal=newVal.substring(1,newVal.length-1);newVal="-"+newVal}newVal=parseFloat(newVal);if(!isFinite(newVal)){newVal=0}if(t){newVal=this.moveDecimalLeft(newVal,2)}return newVal}


