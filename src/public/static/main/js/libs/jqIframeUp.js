!function($){var methods;methods={init:function(opts){var d,debug,ifrm;return debug="none",opts.debug===!0&&(debug="block"),opts.id="frm_"+Math.floor(99999*Math.random()),d=$("<div/>").html('<iframe style="display:'+debug+'" src="about:blank" id="'+opts.id+'" name="'+opts.id+"\" onload=\"$.fn.iframeUp('load','"+opts.id+"')\" onunload=\"$.fn.iframeUp('unload','"+opts.id+"')\"></iframe>"),$("body").append(d),ifrm=document.getElementById(opts.id),opts&&"function"==typeof opts.onSuccess&&(ifrm.onSuccess=opts.onSuccess),opts&&"function"==typeof opts.onComplete&&(ifrm.onComplete=opts.onComplete),opts.id},form:function(frm,name){$("#"+frm).attr("target",name)},submit:function(options){var defaults,opts;defaults={frm:"frm_add",debug:!1,loader:!0,submit:!0,loading:function(opts){},afterSend:function(opts){},onSuccess:function(html,ifrm,id){setTimeout(function(){$("#"+id).parent().remove(),$.fn.iframeUp("unload",id,html)},50)}},opts=$.extend({},defaults,options),opts.isIE=!$.support.opacity&&!$.support.style,opts.isIE6=opts.isIE&&!window.XMLHttpRequest,$.fn.iframeUp("form",opts.frm,$.fn.iframeUp("init",opts)),opts&&"function"==typeof opts.beforeSend&&opts.beforeSend(opts),opts.submit===!0&&document.getElementById(opts.frm).submit(),opts&&"function"==typeof opts.afterSend&&opts.afterSend(opts),opts.loader===!0&&opts&&"function"==typeof opts.loading&&opts.loading(opts)},load:function(id){var doc,err,error,ifrm;if(ifrm=document.getElementById(id)||$("#"+id),doc=ifrm.contentDocument?ifrm.contentDocument:ifrm.contentWindow?ifrm.contentWindow.document:window.frames[id].document,"about:blank"!==doc.location.href&&("function"==typeof ifrm.onSuccess&&ifrm.onSuccess(doc.body.innerHTML,ifrm,id),"function"==typeof ifrm.onComplete))try{ifrm.onComplete(doc.body.innerHTML,ifrm)}catch(error){err=error}},unload:function(id){}},$.fn.iframeUp=function(method){return methods[method]?methods[method].apply(this,Array.prototype.slice.call(arguments,1)):"object"!=typeof method&&method?void $.error("Method "+method+" does not exist on jQuery.iframeUp"):methods.init.apply(this,arguments)}}(jQuery);